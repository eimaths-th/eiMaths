<?php

namespace Tests\Feature\Branch;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserAssignableRoleTest extends TestCase
{
    use RefreshDatabase;

    private Department $hq;
    private Department $branch;
    private Department $otherBranch;
    private User $actor;
    private Role $globalTeacher;
    private Role $localTeacher;
    private Role $foreignTeacher;

    protected function beforeRefreshingDatabase(): void
    {
        $this->assertTrue(app()->environment('testing'));
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
    }

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        Mail::fake();
        Notification::fake();
        $this->hq = Department::forceCreate(['id' => Department::HQ_ID, 'name' => 'HQ', 'code' => 'HQ', 'status' => 1, 'branch_type' => 'hq']);
        $this->branch = Department::create(['name' => 'Own centre', 'code' => 'OWN', 'status' => 1, 'enabled_modules' => ['users', 'students']]);
        $this->otherBranch = Department::create(['name' => 'Other centre', 'code' => 'OTH', 'status' => 1]);
        foreach (['user-create', 'user-edit', 'user-list', 'student-list', 'quiz-list', 'centre-edit', 'all-centre'] as $name) {
            Permission::findOrCreate($name, 'web');
        }
        $this->globalTeacher = Role::findOrCreate('Teacher', 'web');
        $this->globalTeacher->syncPermissions(['student-list', 'quiz-list']);
        $this->localTeacher = Role::create([
            'name' => 'branch-'.$this->branch->id.'-teacher', 'display_name' => 'Teacher for own centre',
            'branch_id' => $this->branch->id, 'guard_name' => 'web',
        ]);
        $this->localTeacher->syncPermissions(['student-list']);
        $this->foreignTeacher = Role::create([
            'name' => 'branch-'.$this->otherBranch->id.'-teacher', 'display_name' => 'Teacher for other centre',
            'branch_id' => $this->otherBranch->id, 'guard_name' => 'web',
        ]);
        $this->foreignTeacher->syncPermissions(['student-list']);
        $this->actor = $this->makeUser(Role::findOrCreate('BranchAdmin', 'web'));
        $this->actor->givePermissionTo(['user-create', 'user-edit', 'student-list']);
    }

    public function test_role_options_return_only_assignable_roles_without_mutating_permissions_or_users(): void
    {
        $before = $this->authorizationSnapshot();
        $roles = $this->actingAs($this->actor)->getJson($this->roleOptionsUrl($this->branch))
            ->assertOk()->assertJsonStructure(['roles' => [['value', 'label']]])->json('roles');
        $byName = array_column($roles, 'label', 'value');

        $this->assertSame('Teacher for own centre', $byName[$this->localTeacher->name]);
        $this->assertArrayNotHasKey('Teacher', $byName);
        $this->assertArrayNotHasKey($this->foreignTeacher->name, $byName);
        $this->assertArrayNotHasKey('Admin', $byName);
        $this->assertArrayNotHasKey('SystemAdmin', $byName);
        $this->assertSame($before, $this->authorizationSnapshot(), 'Looking up roles must not provision roles or change any grants.');
    }

    /** @dataProvider roleLookupPermissions */
    public function test_role_options_require_user_create_or_user_edit_permission(array $permissions, int $status): void
    {
        $actor = $this->makeUser(Role::findOrCreate('Restricted staff', 'web'));
        $actor->syncPermissions($permissions);
        $this->actingAs($actor)->getJson($this->roleOptionsUrl($this->branch))->assertStatus($status);
    }

    public static function roleLookupPermissions(): array
    {
        return [
            'no user management permission' => [[], 403],
            'create only' => [['user-create'], 200],
            'edit only' => [['user-edit'], 200],
        ];
    }

    public function test_role_options_reject_foreign_and_inactive_destination_branches(): void
    {
        $this->actingAs($this->actor)->getJson($this->roleOptionsUrl($this->otherBranch))->assertForbidden();
        $this->otherBranch->update(['status' => 0]);
        $owner = $this->makeUser(Role::findOrCreate('SystemAdmin', 'web'), $this->hq);
        $this->actingAs($owner)->getJson($this->roleOptionsUrl($this->otherBranch))->assertUnprocessable();
    }

    public function test_forging_unassignable_or_foreign_role_in_create_request_is_still_rejected(): void
    {
        $before = $this->authorizationSnapshot();
        foreach ([$this->globalTeacher, $this->foreignTeacher] as $role) {
            $this->actingAs($this->actor)->postJson('/users', $this->newUserPayload($role))
                ->assertForbidden();
            $this->assertDatabaseMissing('users', ['email' => 'new-teacher@example.test']);
        }
        $this->assertSame($before, $this->authorizationSnapshot());
    }

    public function test_branch_admin_can_create_teacher_with_assignable_branch_role_without_changing_global_teacher(): void
    {
        $globalPermissions = $this->globalTeacher->permissions()->orderBy('name')->pluck('name')->all();
        $actorPermissions = $this->actor->getAllPermissions()->pluck('name')->sort()->values()->all();

        $this->actingAs($this->actor)->postJson('/users', $this->newUserPayload($this->localTeacher))
            ->assertOk()->assertJsonStructure(['success']);

        $teacher = User::where('email', 'new-teacher@example.test')->sole();
        $this->assertSame($this->branch->id, (int) $teacher->department_id);
        $this->assertSame([$this->localTeacher->name], $teacher->getRoleNames()->all());
        $this->assertTrue(Hash::check('safe-teacher-password', $teacher->password));
        $this->assertSame(['student-list'], $teacher->getAllPermissions()->pluck('name')->all());
        $this->assertSame($globalPermissions, $this->globalTeacher->fresh()->permissions()->orderBy('name')->pluck('name')->all());
        $this->assertSame($actorPermissions, $this->actor->fresh()->getAllPermissions()->pluck('name')->sort()->values()->all());
    }

    public function test_disabled_module_does_not_make_unowned_permission_assignable(): void
    {
        $this->assertFalse($this->actor->can('quiz-list'));
        $before = $this->actingAs($this->actor)->getJson($this->roleOptionsUrl($this->branch))->assertOk()->json('roles');
        $this->assertNotContains('Teacher', array_column($before, 'value'));

        // Raw assignment authority is distinct from the branch's currently enabled modules.
        $this->actor->givePermissionTo('quiz-list');
        $actor = $this->actor->fresh();
        $this->assertFalse($actor->can('quiz-list'));
        $after = $this->actingAs($actor)->getJson($this->roleOptionsUrl($this->branch))->assertOk()->json('roles');
        $this->assertContains('Teacher', array_column($after, 'value'));
    }

    public function test_owner_sees_central_roles_only_for_hq_and_branch_roles_only_for_their_destination(): void
    {
        $owner = $this->makeUser(Role::findOrCreate('SystemAdmin', 'web'), $this->hq);
        $hqRoles = $this->actingAs($owner)->getJson($this->roleOptionsUrl($this->hq))->assertOk()->json('roles');
        $this->assertContains('SystemAdmin', array_column($hqRoles, 'value'));
        $this->assertContains('Admin', array_column($hqRoles, 'value'));
        $this->assertNotContains($this->localTeacher->name, array_column($hqRoles, 'value'));

        $branchRoles = $this->getJson($this->roleOptionsUrl($this->branch))->assertOk()->json('roles');
        $this->assertNotContains('SystemAdmin', array_column($branchRoles, 'value'));
        $this->assertNotContains('Admin', array_column($branchRoles, 'value'));
        $this->assertContains($this->localTeacher->name, array_column($branchRoles, 'value'));
        $this->assertNotContains($this->foreignTeacher->name, array_column($branchRoles, 'value'));
    }

    public function test_edit_keeps_current_unassignable_single_role_without_making_it_available_for_add(): void
    {
        $target = $this->makeUser($this->globalTeacher);
        $before = $this->authorizationSnapshot();
        $response = $this->actingAs($this->actor)->getJson('/users/edit/'.$target->id)->assertOk();
        $options = $this->parsedRoleOptions($response->json('html'));

        $this->assertTrue($options['Teacher']['selected']);
        $this->assertArrayHasKey($this->localTeacher->name, $options);
        $this->assertArrayNotHasKey($this->foreignTeacher->name, $options);
        $this->assertSame($before, $this->authorizationSnapshot());
        $createOptions = $this->getJson($this->roleOptionsUrl($this->branch).'&target_id='.$target->id.'&role=Teacher')
            ->assertOk()->json('roles');
        $this->assertNotContains('Teacher', array_column($createOptions, 'value'), 'Add lookup must not accept a forged keep-current-role exception.');

        $this->putJson('/users/save/'.$target->id, [
            'name' => 'Updated teacher profile', 'email' => $target->email,
            'department' => $this->branch->id, 'position' => null, 'role' => 'Teacher', 'disable' => 0,
        ])->assertOk();
        $this->assertSame(['Teacher'], $target->fresh()->getRoleNames()->all());
        $this->assertDatabaseHas('users', ['id' => $target->id, 'name' => 'Updated teacher profile']);
        $this->assertDatabaseMissing('branch_audit_logs', ['subject_type' => User::class, 'subject_id' => $target->id, 'action' => 'roles.changed']);
    }

    public function test_edit_does_not_inherit_another_users_unassignable_role_or_expose_foreign_user(): void
    {
        $this->makeUser($this->globalTeacher);
        $target = $this->makeUser($this->localTeacher);
        $foreign = $this->makeUser($this->foreignTeacher, $this->otherBranch);
        $options = $this->parsedRoleOptions($this->actingAs($this->actor)
            ->getJson('/users/edit/'.$target->id)->assertOk()->json('html'));
        $this->assertArrayNotHasKey('Teacher', $options);
        $this->assertTrue($options[$this->localTeacher->name]['selected']);
        $this->getJson('/users/edit/'.$foreign->id)->assertNotFound();
    }

    public function test_edit_does_not_keep_unassignable_role_when_target_has_multiple_roles(): void
    {
        $target = $this->makeUser($this->localTeacher);
        $target->assignRole($this->globalTeacher);
        $options = $this->parsedRoleOptions($this->actingAs($this->actor)
            ->getJson('/users/edit/'.$target->id)->assertOk()->json('html'));
        $this->assertArrayNotHasKey('Teacher', $options);
        $this->assertArrayHasKey($this->localTeacher->name, $options);
        $this->assertEqualsCanonicalizing(['Teacher', $this->localTeacher->name], $target->fresh()->getRoleNames()->all());
    }

    public function test_teacher_datatable_filter_includes_shared_and_scoped_teachers_once_within_actor_branch(): void
    {
        $shared = $this->makeUser($this->globalTeacher);
        $scoped = $this->makeUser($this->localTeacher);
        $both = $this->makeUser($this->localTeacher);
        $both->assignRole($this->globalTeacher);
        $foreign = $this->makeUser($this->foreignTeacher, $this->otherBranch);
        $lookalikeRole = Role::create([
            'name' => $this->localTeacher->name.'-copy', 'display_name' => 'Teacher',
            'branch_id' => $this->branch->id, 'guard_name' => 'web',
        ]);
        $lookalike = $this->makeUser($lookalikeRole);

        $response = $this->actingAs($this->actor)->getJson('/users?role=Teacher', [
            'X-Requested-With' => 'XMLHttpRequest',
        ])->assertOk()->assertJsonPath('recordsTotal', 3)->assertJsonPath('recordsFiltered', 3)
            ->assertJsonCount(3, 'data');
        $ids = array_map('intval', array_column($response->json('data'), 'id'));

        $this->assertEqualsCanonicalizing([$shared->id, $scoped->id, $both->id], $ids);
        $this->assertCount(3, array_unique($ids), 'A user holding both Teacher identities must appear only once.');
        $this->assertNotContains($foreign->id, $ids);
        $this->assertNotContains($lookalike->id, $ids);
    }

    private function makeUser(Role $role, ?Department $branch = null): User
    {
        $user = User::factory()->create([
            'department_id' => ($branch ?? $this->branch)->id, 'disable' => 0,
            'language_code' => 'en', 'password' => Hash::make('safe-teacher-password'),
        ]);
        $user->assignRole($role);
        return $user;
    }

    private function roleOptionsUrl(Department $branch): string
    {
        return '/users/role-options?department_id='.$branch->id;
    }

    private function newUserPayload(Role $role): array
    {
        return [
            'name' => 'New branch teacher', 'email' => 'new-teacher@example.test',
            'department_id' => $this->branch->id, 'position_id' => null, 'role' => $role->name,
            'password' => 'safe-teacher-password', 'password_confirmation' => 'safe-teacher-password',
        ];
    }

    private function authorizationSnapshot(): array
    {
        $snapshot = [];
        foreach (['roles', 'permissions', 'users'] as $table) {
            $snapshot[$table] = DB::table($table)->orderBy('id')->get()->map(fn ($row) => (array) $row)->all();
        }
        foreach (['role_has_permissions' => ['role_id', 'permission_id'], 'model_has_permissions' => ['model_type', 'model_id', 'permission_id'], 'model_has_roles' => ['model_type', 'model_id', 'role_id']] as $table => $columns) {
            $query = DB::table($table);
            foreach ($columns as $column) { $query->orderBy($column); }
            $snapshot[$table] = $query->get()->map(fn ($row) => (array) $row)->all();
        }
        return $snapshot;
    }

    private function parsedRoleOptions(string $html): array
    {
        $document = new \DOMDocument;
        $document->loadHTML('<select>'.$html.'</select>');
        $options = [];
        foreach ($document->getElementsByTagName('option') as $option) {
            if ($option->getAttribute('value') !== '') {
                $options[$option->getAttribute('value')] = ['label' => $option->textContent, 'selected' => $option->hasAttribute('selected')];
            }
        }
        return $options;
    }
}
