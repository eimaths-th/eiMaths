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

    public function test_preview_lists_subset_without_changing_roles_users_or_audit(): void
    {
        $rolesBefore = DB::table('roles')->get()->toJson();
        $assignmentsBefore = DB::table('model_has_roles')->get()->toJson();
        $this->assertSame(0, $this->command());
        $output = Artisan::output();
        $this->assertStringContainsString('preview only', $output);
        foreach (['INCLUDE', 'OMIT', 'student-list', 'history-list', 'quiz-manage', 'course-manage'] as $text) {
            $this->assertStringContainsString($text, $output);
        }
        $this->assertSame($rolesBefore, DB::table('roles')->get()->toJson());
        $this->assertSame($assignmentsBefore, DB::table('model_has_roles')->get()->toJson());
        $this->assertDatabaseCount('branch_audit_logs', 0);
    }

    public function test_apply_creates_only_safe_subset_and_is_idempotent_with_console_audit(): void
    {
        $templateBefore = $this->template->permissions->pluck('name')->sort()->values()->all();
        $authorityBefore = $this->admin->getAllPermissions()->pluck('name')->sort()->values()->all();
        $assignmentsBefore = DB::table('model_has_roles')->get()->toJson();
        $this->assertSame(0, $this->command(['--apply' => true]));
        $role = Role::findByName(TeacherRole::scopedName($this->branch->id), 'web');
        $this->assertSame((int) $this->branch->id, (int) $role->branch_id);
        $this->assertSame('Teacher (SB)', $role->display_name);
        $this->assertSame(['student-list'], $role->permissions->pluck('name')->all());
        $this->assertSame($templateBefore, $this->template->fresh()->permissions->pluck('name')->sort()->values()->all());
        $this->assertSame($authorityBefore, $this->admin->fresh()->getAllPermissions()->pluck('name')->sort()->values()->all());
        $this->assertSame($assignmentsBefore, DB::table('model_has_roles')->get()->toJson());
        $log = DB::table('branch_audit_logs')->where('action', 'teacher-role.provisioned')->sole();
        $this->assertNull($log->actor_id);
        $this->assertSame($this->branch->id, (int) $log->branch_id);
        $this->assertSame(['source' => 'console', 'authority_user_id' => $this->admin->id], json_decode($log->metadata, true));
        $this->assertSame(0, $this->command(['--apply' => true, '--admin' => $this->admin->email]));
        $this->assertStringContainsString('already matches; unchanged', Artisan::output());
        $this->assertSame(1, Role::where('name', $role->name)->count());
        $this->assertSame(1, DB::table('branch_audit_logs')->where('action', 'teacher-role.provisioned')->count());
    }

    public function test_changed_existing_role_is_never_overwritten(): void
    {
        $role = $this->scopedRole();
        $role->givePermissionTo('history-list');
        $this->assertSame(1, $this->command(['--apply' => true]));
        $this->assertStringContainsString('differs from this plan', Artisan::output());
        $this->assertSame(['history-list'], $role->fresh()->permissions->pluck('name')->all());
        $this->assertDatabaseCount('branch_audit_logs', 0);
    }

    public function test_apply_rechecks_authority_after_preview_and_refuses_empty_subset(): void
    {
        $this->assertSame(0, $this->command());
        Role::findByName('BranchAdmin')->syncPermissions(['user-create']);
        $this->assertSame(1, $this->command(['--apply' => true]));
        $this->assertStringContainsString('No Teacher permissions', Artisan::output());
        $this->assertDatabaseMissing('roles', ['name' => TeacherRole::scopedName($this->branch->id)]);
    }

    /** @dataProvider invalidAuthority */
    public function test_invalid_centre_or_authority_fails_without_writes(string $scenario): void
    {
        $arguments = ['--apply' => true];
        if ($scenario === 'inactive') {
            $this->branch->update(['status' => 0]);
        } elseif ($scenario === 'hq') {
            $arguments['centreCode'] = 'HQ';
        } elseif ($scenario === 'other-branch') {
            $this->admin->update(['department_id' => $this->other->id]);
        } elseif ($scenario === 'disabled') {
            $this->admin->update(['disable' => 1]);
        } elseif ($scenario === 'no-create') {
            Role::findByName('BranchAdmin')->syncPermissions(['student-list']);
        } elseif ($scenario === 'no-admin') {
            $arguments['--admin'] = '';
        } elseif ($scenario === 'users-disabled') {
            $this->branch->update(['enabled_modules' => ['students']]);
        } elseif ($scenario === 'central-role') {
            $this->admin->assignRole(Role::findOrCreate('Admin', 'web'));
        }
        $this->assertSame(1, $this->command($arguments));
        $this->assertDatabaseMissing('roles', ['name' => TeacherRole::scopedName($this->branch->id)]);
        $this->assertDatabaseCount('branch_audit_logs', 0);
    }

    public static function invalidAuthority(): array
    {
        return array_map(fn ($scenario) => [$scenario], ['inactive', 'hq', 'other-branch', 'disabled', 'no-create', 'no-admin', 'users-disabled', 'central-role']);
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
