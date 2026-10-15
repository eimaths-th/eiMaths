<?php

namespace Tests\Feature\Branch;

use App\Models\Department;
use App\Models\Position;
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

class UserDepartmentUpdateTest extends TestCase
{
    use RefreshDatabase;

    private Department $hq;
    private Department $branch;
    private Department $otherBranch;
    private Role $branchRole;
    private User $actor;

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
        $this->hq = Department::forceCreate(['id' => 1, 'name' => 'HQ', 'code' => 'HQ', 'status' => 1]);
        $this->branch = Department::create(['name' => 'Own centre', 'code' => 'OWN', 'status' => 1]);
        $this->otherBranch = Department::create(['name' => 'Other centre', 'code' => 'OTH', 'status' => 1]);
        $this->branchRole = Role::findOrCreate('BranchAdmin', 'web');
        $this->branchRole->syncPermissions(array_map(
            fn (string $name) => Permission::findOrCreate($name, 'web'),
            ['user-list', 'user-create', 'user-edit', 'centre-edit', 'all-centre']
        ));
        $this->actor = $this->user($this->branchRole);
    }

    public function test_branch_admin_can_change_own_department_without_reassigning_existing_role(): void
    {
        $position = $this->position('New department');
        $this->assertFalse($this->actor->can('centre-edit'));
        $this->assertFalse($this->actor->can('all-centre'));
        $pivotWrites = [];
        DB::listen(function ($query) use (&$pivotWrites) {
            if (str_contains($query->sql, 'model_has_roles') && preg_match('/^\s*(insert|update|delete)\b/i', $query->sql)) {
                $pivotWrites[] = $query->sql;
            }
        });

        $this->actingAs($this->actor)->putJson('/users/save/'.$this->actor->id,
            $this->payload($this->actor, ['position' => $position->id]))->assertOk();

        $this->assertDatabaseHas('users', ['id' => $this->actor->id, 'position_id' => $position->id]);
        $this->assertSame(['BranchAdmin'], $this->actor->fresh()->getRoleNames()->all());
        $this->assertSame([], $pivotWrites, 'A department change must not rewrite role assignments.');
        $this->assertDatabaseMissing('branch_audit_logs', ['subject_id' => $this->actor->id, 'action' => 'roles.changed']);
        $this->assertDatabaseHas('branch_audit_logs', ['subject_id' => $this->actor->id, 'subject_type' => User::class, 'action' => 'updated']);
    }

    public function test_same_branch_colleague_can_keep_existing_role_while_department_changes(): void
    {
        $target = $this->user($this->branchRole);
        $position = $this->position('Colleague department');

        $this->actingAs($this->actor)->putJson('/users/save/'.$target->id,
            $this->payload($target, ['position' => $position->id]))->assertOk();

        $this->assertDatabaseHas('users', ['id' => $target->id, 'position_id' => $position->id]);
        $this->assertSame(['BranchAdmin'], $target->fresh()->getRoleNames()->all());
        $this->assertDatabaseMissing('branch_audit_logs', ['subject_id' => $target->id, 'action' => 'roles.changed']);
    }

    public function test_changing_to_role_beyond_actor_authority_is_rejected_without_partial_update(): void
    {
        $target = $this->user(Role::findOrCreate('Teacher', 'web'));
        $before = $target->fresh()->getAttributes();
        $position = $this->position('Attempted department');

        $this->actingAs($this->actor)->putJson('/users/save/'.$target->id,
            $this->payload($target, ['name' => 'Must not save', 'position' => $position->id, 'role' => 'BranchAdmin']))->assertForbidden();

        $this->assertSame($before, $target->fresh()->getAttributes());
        $this->assertSame(['Teacher'], $target->fresh()->getRoleNames()->all());
    }

    public function test_allowed_role_change_still_saves_and_records_role_assignment(): void
    {
        $target = $this->user(Role::findOrCreate('Teacher', 'web'));
        $helper = Role::findOrCreate('BranchHelper', 'web');
        $helper->syncPermissions([Permission::findOrCreate('user-list', 'web')]);
        $position = $this->position('Support');

        $this->actingAs($this->actor)->putJson('/users/save/'.$target->id,
            $this->payload($target, ['position' => $position->id, 'role' => 'BranchHelper']))->assertOk();

        $this->assertDatabaseHas('users', ['id' => $target->id, 'position_id' => $position->id]);
        $this->assertSame(['BranchHelper'], $target->fresh()->getRoleNames()->all());
        $log = DB::table('branch_audit_logs')->where('subject_id', $target->id)->where('action', 'roles.changed')->sole();
        $this->assertSame(['roles' => ['Teacher']], json_decode($log->before_values, true));
        $this->assertSame(['roles' => ['BranchHelper']], json_decode($log->after_values, true));
    }

    public function test_selecting_one_existing_role_from_multiple_roles_still_requires_assignment_authority(): void
    {
        $target = $this->user($this->branchRole);
        $target->assignRole(Role::findOrCreate('Teacher', 'web'));
        $before = $target->fresh()->getAttributes();

        $this->actingAs($this->actor)->putJson('/users/save/'.$target->id,
            $this->payload($target, ['name' => 'Must not save', 'role' => 'BranchAdmin']))->assertForbidden();

        $this->assertSame($before, $target->fresh()->getAttributes());
        $this->assertEqualsCanonicalizing(['BranchAdmin', 'Teacher'], $target->fresh()->getRoleNames()->all());
    }

    public function test_other_branch_department_is_rejected_without_partial_update(): void
    {
        $before = $this->actor->fresh()->getAttributes();
        $foreign = $this->position('Private department', $this->otherBranch);

        $this->actingAs($this->actor)->putJson('/users/save/'.$this->actor->id,
            $this->payload($this->actor, ['name' => 'Must not save', 'position' => $foreign->id]))->assertUnprocessable();

        $this->assertSame($before, $this->actor->fresh()->getAttributes());
        $this->assertSame(['BranchAdmin'], $this->actor->fresh()->getRoleNames()->all());
    }

    public function test_new_assignment_to_inactive_department_is_rejected(): void
    {
        $before = $this->actor->fresh()->getAttributes();
        $inactive = $this->position('Archived department', status: 0);

        $this->actingAs($this->actor)->putJson('/users/save/'.$this->actor->id,
            $this->payload($this->actor, ['position' => $inactive->id]))->assertUnprocessable();

        $this->assertSame($before, $this->actor->fresh()->getAttributes());
    }

    public function test_unchanged_role_does_not_allow_editing_user_in_another_branch(): void
    {
        $target = $this->user($this->branchRole, $this->otherBranch);
        $before = $target->fresh()->getAttributes();
        $this->actingAs($this->actor)->putJson('/users/save/'.$target->id,
            $this->payload($target, ['name' => 'Must not save']))->assertForbidden();
        $this->assertSame($before, $target->fresh()->getAttributes());
        $this->getJson('/users/edit/'.$target->id)->assertNotFound();
    }

    public function test_system_admin_user_stays_hidden_even_when_its_role_is_unchanged(): void
    {
        $target = $this->user(Role::findOrCreate('SystemAdmin', 'web'), $this->hq);
        $before = $target->fresh()->getAttributes();
        $this->actingAs($this->actor)->getJson('/users/edit/'.$target->id)->assertNotFound();
        $this->putJson('/users/save/'.$target->id,
            $this->payload($target, ['name' => 'Must not save']))->assertNotFound();
        $this->assertSame($before, $target->fresh()->getAttributes());
    }

    public function test_protected_admin_account_cannot_be_edited_by_branch_admin(): void
    {
        // A legacy Admin assignment in a branch must not bypass account protection.
        $target = $this->user(Role::findOrCreate('Admin', 'web'));
        $before = $target->fresh()->getAttributes();
        $this->actingAs($this->actor)->getJson('/users/edit/'.$target->id)->assertForbidden();
        $this->putJson('/users/save/'.$target->id,
            $this->payload($target, ['name' => 'Must not save']))->assertForbidden();
        $this->assertSame($before, $target->fresh()->getAttributes());
    }

    public function test_edit_options_include_all_active_departments_in_selected_centre_and_retain_selection(): void
    {
        $selected = $this->position('Existing department');
        $new = $this->position('New & ready department');
        $inactive = $this->position('Unassigned inactive', status: 0);
        $foreign = $this->position('Other centre department', $this->otherBranch);
        $this->actor->update(['position_id' => $selected->id]);

        $response = $this->actingAs($this->actor)->getJson('/users/edit/'.$this->actor->id)->assertOk();
        $options = $this->departmentOptions($response->json('select_list_position'));

        $this->assertSame('Existing department', $options[$selected->id]['label']);
        $this->assertTrue($options[$selected->id]['selected']);
        $this->assertSame('New & ready department', $options[$new->id]['label']);
        $this->assertArrayNotHasKey($inactive->id, $options);
        $this->assertArrayNotHasKey($foreign->id, $options);
        $response->assertJsonPath('data.position_id', $selected->id);
    }

    public function test_reopening_edit_refetches_departments_added_after_first_open(): void
    {
        $first = $this->position('First department');
        $this->actingAs($this->actor);
        $initial = $this->getJson('/users/edit/'.$this->actor->id)->assertOk();
        $this->assertArrayHasKey($first->id, $this->departmentOptions($initial->json('select_list_position')));

        $new = $this->position('Created after initial open');
        $reopened = $this->getJson('/users/edit/'.$this->actor->id)->assertOk();
        $options = $this->departmentOptions($reopened->json('select_list_position'));

        $this->assertArrayHasKey($first->id, $options);
        $this->assertArrayHasKey($new->id, $options);
        $this->assertNull($reopened->json('data.position_id'));
    }

    public function test_current_inactive_department_remains_visible_and_can_be_preserved(): void
    {
        $inactive = $this->position('Previously assigned department', status: 0);
        $this->actor->update(['position_id' => $inactive->id]);
        $response = $this->actingAs($this->actor)->getJson('/users/edit/'.$this->actor->id)->assertOk();
        $options = $this->departmentOptions($response->json('select_list_position'));
        $this->assertTrue($options[$inactive->id]['selected']);

        $this->putJson('/users/save/'.$this->actor->id,
            $this->payload($this->actor, ['name' => 'Updated display name']))->assertOk();

        $this->assertDatabaseHas('users', ['id' => $this->actor->id, 'position_id' => $inactive->id, 'name' => 'Updated display name']);
    }

    public function test_omitted_department_preserves_assignment_and_explicit_null_clears_it(): void
    {
        $position = $this->position('Assigned department');
        $this->actor->update(['position_id' => $position->id]);
        $payload = $this->payload($this->actor, ['name' => 'Renamed user']);
        unset($payload['position']);

        $this->actingAs($this->actor)->putJson('/users/save/'.$this->actor->id, $payload)->assertOk();
        $this->assertDatabaseHas('users', ['id' => $this->actor->id, 'position_id' => $position->id, 'name' => 'Renamed user']);

        $this->putJson('/users/save/'.$this->actor->id,
            $this->payload($this->actor->fresh(), ['position' => null]))->assertOk();
        $this->assertDatabaseHas('users', ['id' => $this->actor->id, 'position_id' => null]);
    }

    public function test_unchanged_role_exception_does_not_permit_new_user_with_excessive_role(): void
    {
        $this->actingAs($this->actor)->postJson('/users', [
            'name' => 'Rejected new administrator', 'email' => 'rejected-new-admin@example.test',
            'department_id' => $this->branch->id, 'position_id' => null, 'role' => 'BranchAdmin',
            'password' => 'safe-test-password', 'password_confirmation' => 'safe-test-password', 'disable' => 0,
        ])->assertForbidden();
        $this->assertDatabaseMissing('users', ['email' => 'rejected-new-admin@example.test']);
    }

    /** @dataProvider sensitivePeerChanges */
    public function test_sensitive_changes_to_more_privileged_peer_require_authority_even_with_same_role(array $changes, int $initialDisable): void
    {
        $editorRole = Role::findOrCreate('NarrowUserEditor', 'web');
        $editorRole->syncPermissions([Permission::findOrCreate('user-edit', 'web')]);
        $editor = $this->user($editorRole);
        $target = $this->user($this->branchRole);
        $target->update(['disable' => $initialDisable]);
        $before = $target->fresh()->getAttributes();
        $position = $this->position('Must not be applied');
        $payload = $this->payload($target, array_replace([
            'name' => 'Must not save', 'position' => $position->id, 'disable' => $initialDisable,
        ], $changes));

        $this->actingAs($editor)->putJson('/users/save/'.$target->id, $payload)->assertForbidden();

        $this->assertSame($before, $target->fresh()->getAttributes());
        $this->assertSame(['BranchAdmin'], $target->fresh()->getRoleNames()->all());
        $this->assertDatabaseMissing('branch_audit_logs', ['subject_id' => $target->id, 'action' => 'roles.changed']);
        $this->assertDatabaseMissing('branch_audit_logs', ['subject_id' => $target->id, 'subject_type' => User::class, 'action' => 'updated']);
    }

    public static function sensitivePeerChanges(): array
    {
        return [
            'email' => [['email' => 'hijacked-account@example.test'], 0],
            'password' => [['password' => 'replacement-test-password', 'password_confirmation' => 'replacement-test-password'], 0],
            'reactivate' => [['disable' => 0], 1],
            'disable' => [['disable' => 1], 0],
        ];
    }

    public function test_branch_admin_can_update_own_credentials_while_preserving_existing_role(): void
    {
        $this->actingAs($this->actor)->putJson('/users/save/'.$this->actor->id,
            $this->payload($this->actor, [
                'email' => 'updated-own-account@example.test', 'password' => 'updated-own-password',
                'password_confirmation' => 'updated-own-password',
            ]))->assertOk();

        $updated = $this->actor->fresh();
        $this->assertSame('updated-own-account@example.test', $updated->email);
        $this->assertTrue(Hash::check('updated-own-password', $updated->password));
        $this->assertSame(['BranchAdmin'], $updated->getRoleNames()->all());
        $this->assertDatabaseMissing('branch_audit_logs', ['subject_id' => $updated->id, 'action' => 'roles.changed']);
    }

    public function test_authorized_peer_credentials_can_still_be_updated_without_role_changes(): void
    {
        $teacherRole = Role::findOrCreate('Teacher', 'web');
        $teacherRole->syncPermissions([]);
        $target = $this->user($teacherRole);

        $this->actingAs($this->actor)->putJson('/users/save/'.$target->id,
            $this->payload($target, [
                'email' => 'updated-teacher@example.test', 'password' => 'updated-teacher-password',
                'password_confirmation' => 'updated-teacher-password',
            ]))->assertOk();

        $updated = $target->fresh();
        $this->assertSame('updated-teacher@example.test', $updated->email);
        $this->assertTrue(Hash::check('updated-teacher-password', $updated->password));
        $this->assertSame(['Teacher'], $updated->getRoleNames()->all());
        $this->assertDatabaseMissing('branch_audit_logs', ['subject_id' => $updated->id, 'action' => 'roles.changed']);
    }

    private function user(Role $role, ?Department $branch = null): User
    {
        $user = User::factory()->create([
            'department_id' => ($branch ?? $this->branch)->id, 'position_id' => null,
            'disable' => 0, 'language_code' => 'en', 'password' => bcrypt('safe-test-password'),
        ]);
        $user->assignRole($role);
        return $user;
    }

    private function position(string $name, ?Department $branch = null, int $status = 1): Position
    {
        return Position::create(['department_id' => ($branch ?? $this->branch)->id, 'name' => $name, 'status' => $status]);
    }

    private function payload(User $target, array $overrides = []): array
    {
        return array_replace([
            'name' => $target->name, 'email' => $target->email, 'department' => $target->department_id,
            'position' => $target->position_id, 'role' => $target->getRoleNames()->first(), 'disable' => 0,
        ], $overrides);
    }

    private function departmentOptions(string $html): array
    {
        $document = new \DOMDocument;
        $document->loadHTML('<select>'.$html.'</select>');
        $result = [];
        foreach ($document->getElementsByTagName('option') as $option) {
            if ($option->getAttribute('value') !== '') {
                $result[(int) $option->getAttribute('value')] = [
                    'label' => $option->textContent,
                    'selected' => $option->hasAttribute('selected'),
                ];
            }
        }
        return $result;
    }
}
