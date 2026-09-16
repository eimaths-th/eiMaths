<?php

namespace Tests\Feature\Affiliate;

use App\Models\CoursePending;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AffiliateCourseApplicationPendingAlertTest extends TestCase
{
    use RefreshDatabase;

    private Department $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Department::query()->create([
            'name' => 'Course Alert Branch',
            'code' => 'CAB',
            'status' => 1,
        ]);
    }

    #[Test]
    public function authorized_branch_user_can_read_current_pending_course_application_summary(): void
    {
        $pending = $this->CreateCourseApplicationRecord($this->branch, 'Parent Pending', 'Math Starter', '1');
        $this->CreateCourseApplicationRecord($this->branch, 'Parent Accepted', 'Math Advanced', '2');

        $other_branch = Department::query()->create([
            'name' => 'Other Course Alert Branch',
            'code' => 'OCA',
            'status' => 1,
        ]);
        $this->CreateCourseApplicationRecord($other_branch, 'Other Parent', 'Other Course', '1');

        $admin = $this->CreateAdminWithPermission();

        $this->actingAs($admin)
            ->getJson(route('affiliate.course-applications.pending-summary'))
            ->assertOk()
            ->assertJsonPath('data.pending_count', 1)
            ->assertJsonPath('data.latest_pending_application.id', $pending->id)
            ->assertJsonPath('data.latest_pending_application.applicant_name', 'Parent Pending')
            ->assertJsonPath('data.latest_pending_application.course_name', 'Math Starter')
            ->assertJsonPath('data.latest_pending_application.branch_name', 'Course Alert Branch');
    }

    #[Test]
    public function authorized_user_sees_course_application_alert_hooks_in_the_backend_layout(): void
    {
        $admin = $this->CreateAdminWithPermission();

        $this->actingAs($admin)
            ->get(route('courses.pending.index'))
            ->assertOk()
            ->assertSee('data-affiliate-course-application-alert', false)
            ->assertSee('data-pending-work-item="course_application"', false)
            ->assertSee('data-affiliate-course-application-badge', false)
            ->assertSee('data-affiliate-management-pending-badge', false)
            ->assertSee(route('affiliate.course-applications.pending-summary'), false);
    }

    #[Test]
    public function user_without_course_application_list_permission_cannot_read_pending_summary(): void
    {
        $user = User::factory()->create([
            'department_id' => $this->branch->id,
            'password' => Hash::make('secret'),
            'disable' => 0,
        ]);

        $this->actingAs($user)
            ->getJson(route('affiliate.course-applications.pending-summary'))
            ->assertForbidden();
    }

    private function CreateAdminWithPermission(): User
    {
        $admin = User::factory()->create([
            'department_id' => $this->branch->id,
            'email_verified_at' => now(),
            'password' => Hash::make('secret'),
            'disable' => 0,
        ]);
        $admin->givePermissionTo(Permission::findOrCreate('affiliate-course-application-list', 'web'));

        return $admin->fresh();
    }

    private function CreateCourseApplicationRecord(
        Department $department,
        string $applicant_name,
        string $course_name,
        string $status,
    ): CoursePending {
        return CoursePending::query()->create([
            'name' => $applicant_name,
            'email' => strtolower(str_replace(' ', '.', $applicant_name)).'@example.test',
            'course_id' => 1,
            'course_name' => $course_name,
            'department_id' => $department->id,
            'status' => $status,
        ]);
    }
}
