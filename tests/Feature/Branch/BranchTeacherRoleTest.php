<?php

namespace Tests\Feature\Branch;

use App\Domain\Affiliate\Services\AffiliateEmployeeEligibility;
use App\Http\Controllers\DashboardController;
use App\Models\bookuse;
use App\Models\Contact;
use App\Models\Department;
use App\Models\level;
use App\Models\Order;
use App\Models\OrderDetailList;
use App\Models\term;
use App\Models\User;
use App\Support\Branch\TeacherRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BranchTeacherRoleTest extends TestCase
{
    use RefreshDatabase;

    private Department $branch;
    private Department $other;
    private User $admin;
    private Role $template;

    protected function beforeRefreshingDatabase(): void
    {
        $this->assertTrue(app()->environment('testing'));
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMockingConsoleOutput();
        Http::preventStrayRequests();
        Mail::fake();
        Notification::fake();
        Department::forceCreate(['id' => 1, 'name' => 'HQ', 'code' => 'HQ', 'status' => 1]);
        $this->branch = Department::create(['name' => 'Teacher test', 'code' => 'SB', 'status' => 1,
            'enabled_modules' => ['users', 'students', 'teaching']]);
        $this->other = Department::create(['name' => 'Other branch', 'code' => 'RP', 'status' => 1]);
        foreach (['user-create', 'student-list', 'history-list', 'quiz-manage', 'course-manage', 'affiliate-user'] as $name) {
            Permission::findOrCreate($name, 'web');
        }
        $this->template = Role::findOrCreate('Teacher', 'web');
        $this->template->syncPermissions(['student-list', 'history-list', 'quiz-manage', 'course-manage']);
        $administrator = Role::findOrCreate('BranchAdmin', 'web');
        // Quiz exists in raw authority but is disabled for this branch. Course is HQ-only.
        $administrator->syncPermissions(['user-create', 'student-list', 'quiz-manage', 'course-manage']);
        $this->admin = User::factory()->state(['password' => 'teacher-test-password'])->create(['department_id' => $this->branch->id, 'disable' => 0]);
        $this->admin->assignRole($administrator);
    }

    /** @dataProvider retiredModes */
    public function test_retired_command_preserves_existing_roles_permissions_and_assigned_users(bool $apply): void
    {
        $legacy = $this->scopedRole();
        $legacy->syncPermissions(['student-list', 'history-list']);
        $teacher = User::factory()->state(['password' => 'teacher-test-password'])->create([
            'department_id' => $this->branch->id, 'disable' => 0,
        ]);
        $teacher->assignRole($legacy);
        $before = $this->roleAndUserSnapshot();

        $this->assertRetiredWithoutQueries(['--apply' => $apply]);

        $this->assertSame($before, $this->roleAndUserSnapshot());
        $this->assertSame([$legacy->name], $teacher->fresh()->getRoleNames()->all());
        $this->assertSame(['history-list', 'student-list'], $legacy->fresh()->permissions->pluck('name')->sort()->values()->all());
        $this->assertDatabaseCount('branch_audit_logs', 0);
    }

    public static function retiredModes(): array
    {
        return ['legacy default mode' => [false], 'legacy --apply mode' => [true]];
    }

    public function test_retired_apply_cannot_create_a_trimmed_role_or_provisioning_audit(): void
    {
        $name = TeacherRole::scopedName($this->branch->id);
        $this->assertDatabaseMissing('roles', ['name' => $name]);
        $before = $this->roleAndUserSnapshot();

        $this->assertRetiredWithoutQueries(['--apply' => true, '--admin' => $this->admin->email]);

        $this->assertSame($before, $this->roleAndUserSnapshot());
        $this->assertDatabaseMissing('roles', ['name' => $name]);
        $this->assertDatabaseCount('branch_audit_logs', 0);
    }

    public function test_retirement_is_reported_without_looking_up_legacy_arguments(): void
    {
        $before = $this->roleAndUserSnapshot();

        $this->assertRetiredWithoutQueries(['centreCode' => 'NONEXISTENT', '--admin' => '', '--apply' => true]);

        $this->assertSame($before, $this->roleAndUserSnapshot());
        $this->assertDatabaseCount('branch_audit_logs', 0);
    }

    private function roleAndUserSnapshot(): array
    {
        $snapshot = [];
        foreach (['roles', 'permissions', 'role_has_permissions', 'users', 'model_has_roles', 'model_has_permissions', 'branch_audit_logs'] as $table) {
            $snapshot[$table] = DB::table($table)->get()->toJson();
        }

        return $snapshot;
    }

    private function assertRetiredWithoutQueries(array $arguments): void
    {
        $queries = [];
        $recording = true;
        DB::listen(function ($query) use (&$queries, &$recording): void {
            if ($recording) {
                $queries[] = $query->sql;
            }
        });
        try {
            $this->assertSame(1, $this->command($arguments));
        } finally {
            $recording = false;
        }

        $this->assertSame([], $queries, 'The retired command must not query or mutate application data.');
        $this->assertStringContainsString("This legacy command is retired. Assign the existing Teacher role from Users; roles are no longer trimmed to the creator's permissions. Existing roles/users unchanged.", Artisan::output());
    }

    public function test_teacher_identity_uses_canonical_name_guard_and_matching_role_branch(): void
    {
        $scoped = $this->scopedRole();
        $custom = Role::create(['name' => 'custom-teacher', 'display_name' => 'Teacher', 'guard_name' => 'web', 'branch_id' => $this->branch->id]);
        $mismatch = Role::create(['name' => TeacherRole::scopedName($this->other->id), 'guard_name' => 'web', 'branch_id' => $this->branch->id]);
        $otherGuard = Role::create(['name' => 'Teacher', 'guard_name' => 'api']);
        $this->assertTrue(TeacherRole::isTeacherRole($this->template));
        $this->assertTrue(TeacherRole::isTeacherRole($scoped));
        foreach ([$custom, $mismatch, $otherGuard] as $invalid) {
            $this->assertFalse(TeacherRole::isTeacherRole($invalid));
        }
        $this->assertEqualsCanonicalizing(['Teacher', $scoped->name], TeacherRole::teacherRoleNames());
    }

    public function test_scoped_teacher_affiliate_identity_preserves_explicit_access_and_branch_checks(): void
    {
        config(['affiliate.employee_roles' => ['Teacher']]);
        $user = User::factory()->state(['password' => 'teacher-test-password'])->create(['department_id' => $this->branch->id, 'disable' => 0]);
        $role = $this->scopedRole();
        $user->assignRole($role);
        $eligibility = app(AffiliateEmployeeEligibility::class);
        $this->assertFalse($eligibility->IsEligible($user));
        $role->givePermissionTo('affiliate-user');
        $this->assertTrue($eligibility->IsEligible($user->fresh()));
        config(['affiliate.employee_roles' => ['BranchAdmin']]);
        $this->assertFalse($eligibility->IsEligible($user->fresh()));
        config(['affiliate.employee_roles' => ['Teacher']]);
        $user->update(['department_id' => $this->other->id]);
        $this->assertFalse($eligibility->IsEligible($user->fresh()));
    }

    public function test_dashboard_teacher_count_includes_scoped_and_shared_teacher_only_in_selected_branch(): void
    {
        $role = $this->scopedRole();
        User::factory()->state(['password' => 'teacher-test-password'])->create(['department_id' => $this->branch->id])->assignRole($role);
        User::factory()->state(['password' => 'teacher-test-password'])->create(['department_id' => $this->branch->id])->assignRole($this->template);
        User::factory()->state(['password' => 'teacher-test-password'])->create(['department_id' => $this->other->id])->assignRole($this->template);
        $this->actingAs($this->admin);
        $response = app(DashboardController::class)->dashboard_teacher_by_centre(Request::create('/'));
        $this->assertSame(2, $response->getData(true)['total_teacher']);
    }

    public function test_scoped_teacher_is_available_on_real_histories_page(): void
    {
        [$scopedTeacher, $sharedTeacher] = $this->teacherCandidates();
        $this->admin->givePermissionTo('history-list');

        $this->actingAs($this->admin)->get(route('histories.index'))->assertOk()
            ->assertViewHas('lteacher', function ($teachers) use ($scopedTeacher, $sharedTeacher): bool {
                $this->assertEqualsCanonicalizing([$scopedTeacher->id, $sharedTeacher->id], $teachers->pluck('id')->all());
                return true;
            });
    }

    public function test_scoped_teacher_is_available_in_real_fast_history_preview(): void
    {
        [$scopedTeacher, $sharedTeacher] = $this->teacherCandidates();
        $disabledTeacher = User::factory()->state(['password' => 'teacher-test-password'])->create([
            'department_id' => $this->branch->id, 'disable' => 1,
        ]);
        $disabledTeacher->assignRole(TeacherRole::scopedName($this->branch->id));
        $this->admin->givePermissionTo(Permission::findOrCreate('student-edit', 'web'));

        $level = level::forceCreate(['id' => 1, 'name' => 'K1', 'status' => 1]);
        $term = term::forceCreate(['id' => 1, 'level_id' => $level->id, 'name' => '1', 'status' => 1]);
        $book = bookuse::forceCreate(['name' => 'K1 Term 1', 'type' => 1, 'qty' => 10,
            'level_id' => $level->id, 'term_id' => $term->id, 'status' => 1]);
        $student = Contact::forceCreate([
            'centre' => $this->branch->id, 'type' => 'student', 'code' => 'SB-TEACHER-TEST',
            'name' => 'Teacher selector student', 'level' => $level->id, 'term' => $term->id,
            'bookuse' => $book->id, 'level_name' => $level->name, 'term_name' => '1', 'bookuse_name' => $book->name,
            'level2' => $level->id, 'term2' => $term->id, 'bookuse2' => $book->id,
            'level2_name' => $level->name, 'term2_name' => '1', 'bookuse2_name' => $book->name,
        ]);
        $order = Order::forceCreate(['centre' => $this->branch->id, 'cid' => $student->id, 'status' => 1]);
        OrderDetailList::forceCreate(['order_id' => $order->id, 'level' => $level->name, 'term' => '1', 'book' => $book->name]);

        $response = $this->actingAs($this->admin)->getJson(route('contacts.fastHistory.preview', $student->id))->assertOk();
        $this->assertEqualsCanonicalizing([$scopedTeacher->id, $sharedTeacher->id, $this->admin->id],
            array_column($response->json('data.teacher_options'), 'id'));
        $this->assertDatabaseCount('histrories', 0);
    }

    private function teacherCandidates(): array
    {
        $scopedTeacher = User::factory()->state(['password' => 'teacher-test-password'])->create([
            'department_id' => $this->branch->id, 'disable' => 0,
        ]);
        $scopedTeacher->assignRole($this->scopedRole());
        $sharedTeacher = User::factory()->state(['password' => 'teacher-test-password'])->create([
            'department_id' => $this->branch->id, 'disable' => 0,
        ]);
        $sharedTeacher->assignRole($this->template);
        $otherTeacher = User::factory()->state(['password' => 'teacher-test-password'])->create([
            'department_id' => $this->other->id, 'disable' => 0,
        ]);
        $otherTeacher->assignRole(Role::create(['name' => TeacherRole::scopedName($this->other->id),
            'branch_id' => $this->other->id, 'display_name' => 'Teacher (RP)', 'guard_name' => 'web']));
        $unrelated = User::factory()->state(['password' => 'teacher-test-password'])->create([
            'department_id' => $this->branch->id, 'disable' => 0,
        ]);
        $unrelated->assignRole(Role::create(['name' => 'custom-label-only', 'display_name' => 'Teacher',
            'branch_id' => $this->branch->id, 'guard_name' => 'web']));

        return [$scopedTeacher, $sharedTeacher];
    }

    private function scopedRole(): Role
    {
        return Role::create(['name' => TeacherRole::scopedName($this->branch->id),
            'display_name' => 'Teacher (SB)', 'branch_id' => $this->branch->id, 'guard_name' => 'web']);
    }

    private function command(array $arguments = []): int
    {
        return Artisan::call('branch:teacher-role', array_replace([
            'centreCode' => 'sb', '--admin' => (string) $this->admin->id,
        ], $arguments));
    }
}
