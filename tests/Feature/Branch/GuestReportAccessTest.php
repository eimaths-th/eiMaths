<?php

namespace Tests\Feature\Branch;

use App\Models\Department;
use App\Models\GuestQuizzer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GuestReportAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_branch_staff_with_student_list_can_open_the_shared_guest_report(): void
    {
        $branch = Department::create(['name' => 'Branch A', 'code' => 'AA', 'status' => 1]);
        $actor = $this->actor($branch, 'BranchAdmin', ['student-list']);
        $this->guest();

        $this->actingAs($actor)->get('/guest-report')->assertOk();
        $this->getJson('/guest-report', ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()
            ->assertJsonPath('recordsTotal', 1)
            ->assertJsonPath('data.0.email', 'private-guest@example.test');

        $this->get('/contacts')
            ->assertOk()
            ->assertSee('href="'.route('guest.report').'"', false);
    }

    public function test_branch_staff_cannot_change_shared_guest_records(): void
    {
        $branch = Department::create(['name' => 'Branch A', 'code' => 'AA', 'status' => 1]);
        $actor = $this->actor($branch, 'BranchAdmin', ['student-list', 'student-edit', 'student-delete']);
        $guest = $this->guest(['marketing_consent' => true]);

        $this->actingAs($actor)->getJson('/guest-report', ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()
            ->assertJsonPath('data.0.action', '')
            ->assertJsonPath('data.0.checkbox', '');

        $this->postJson('/guest-report/destroy', ['id' => $guest->id])->assertForbidden();
        $this->postJson('/guest-report/marketing-consent/withdraw', ['id' => $guest->id])->assertForbidden();
        $this->assertDatabaseHas('guest_quizzers', ['id' => $guest->id, 'marketing_consent' => true]);
    }

    public function test_branch_staff_without_access_cannot_open_the_guest_report(): void
    {
        $branch = Department::create(['name' => 'Branch A', 'code' => 'AA', 'status' => 1]);
        $without_student_list = $this->actor($branch, 'BranchAdmin', ['student-edit']);
        $module_disabled_branch = Department::create([
            'name' => 'Branch B', 'code' => 'BB', 'status' => 1, 'enabled_modules' => ['dashboard'],
        ]);
        $without_students_module = $this->actor($module_disabled_branch, 'BranchAdmin', ['student-list']);

        $this->actingAs($without_student_list)->get('/guest-report')->assertForbidden();
        $this->actingAs($without_students_module)->get('/guest-report')->assertForbidden();
    }

    public function test_central_staff_can_view_but_cannot_delete_without_student_delete_permission(): void
    {
        $hq = Department::create(['id' => 1, 'name' => 'HQ', 'code' => 'HQ', 'status' => 1]);
        $actor = $this->actor($hq, 'Admin', ['student-list']);
        $guest = $this->guest();

        $this->actingAs($actor)->get('/guest-report')->assertOk();
        $this->getJson('/guest-report', ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()
            ->assertJsonPath('recordsTotal', 1)
            ->assertJsonPath('data.0.action', '');
        $this->postJson('/guest-report/destroy', ['id' => $guest->id])->assertForbidden();
        $this->assertDatabaseHas('guest_quizzers', ['id' => $guest->id]);
    }

    private function actor(Department $branch, string $role, array $permissions): User
    {
        $actor = User::factory()->create(['department_id' => $branch->id, 'disable' => 0, 'password' => bcrypt('secret')]);
        $actor->assignRole(Role::findOrCreate($role, 'web'));
        foreach ($permissions as $permission) {
            $actor->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        }

        return $actor;
    }

    private function guest(array $overrides = []): GuestQuizzer
    {
        return GuestQuizzer::create(array_merge([
            'first_name' => 'Private',
            'last_name' => 'Guest',
            'grade' => 'P1',
            'email' => 'private-guest@example.test',
            'phone' => '0800000000',
            'password' => bcrypt('secret'),
            'expires_at' => now()->addDay(),
        ], $overrides));
    }
}
