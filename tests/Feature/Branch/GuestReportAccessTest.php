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

    public function test_branch_staff_cannot_list_or_delete_unowned_guest_records(): void
    {
        $branch = Department::create(['name' => 'Branch A', 'code' => 'AA', 'status' => 1]);
        $actor = $this->actor($branch, 'BranchAdmin', ['student-list', 'student-delete']);
        $guest = $this->guest();

        $this->actingAs($actor)->get('/guest-report')->assertForbidden();
        $this->getJson('/guest-report', ['X-Requested-With' => 'XMLHttpRequest'])->assertForbidden();
        $this->postJson('/guest-report/destroy', ['id' => $guest->id])->assertForbidden();
        $this->assertDatabaseHas('guest_quizzers', ['id' => $guest->id]);

        $this->get('/contacts')
            ->assertOk()
            ->assertDontSee('href="'.route('guest.report').'"', false);
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

    private function guest(): GuestQuizzer
    {
        return GuestQuizzer::create([
            'first_name' => 'Private',
            'last_name' => 'Guest',
            'grade' => 'P1',
            'email' => 'private-guest@example.test',
            'phone' => '0800000000',
            'password' => bcrypt('secret'),
            'expires_at' => now()->addDay(),
        ]);
    }
}
