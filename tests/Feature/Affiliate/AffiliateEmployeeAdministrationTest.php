<?php

namespace Tests\Feature\Affiliate;

use App\Domain\Affiliate\Enums\AffiliateProfileStatus;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AffiliateEmployeeAdministrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \App\Models\Department::create(['id' => 1, 'name' => 'HQ test', 'code' => 'HQ', 'status' => 1, 'branch_type' => 'hq']);
        \App\Models\Department::forceCreate(['id' => 5, 'name' => 'Employee branch', 'code' => 'EMP', 'status' => 1]);
        Config::set('affiliate.features.administration', true);
    }

    #[Test]
    public function NewEmployeeProfileAppearsInExistingApprovalList(): void
    {
        [$employee, $profile] = $this->CreateEmployee(AffiliateProfileStatus::PENDING_APPROVAL, 'employee-approval-list');
        $admin = $this->CreateAdmin('affiliate-list');

        $this->actingAs($admin)
            ->get(route('affiliate.admin.index'))
            ->assertOk()
            ->assertViewIs('affiliate.admin.index')
            ->assertSee($employee->name)
            ->assertSee($employee->email)
            ->assertSee($profile->referral_code)
            ->assertSee(AffiliateProfileStatus::PENDING_APPROVAL->value);
    }

    #[Test]
    public function ApprovingEmployeeProfileDoesNotAssignAffiliateRoleOrChangeStaffIdentity(): void
    {
        [$employee, $profile] = $this->CreateEmployee(AffiliateProfileStatus::PENDING_APPROVAL, 'employee-approve');
        $before = $this->SnapshotStaffIdentity($employee);
        $admin = $this->CreateAdmin('affiliate-approve');

        $this->actingAs($admin)
            ->post(route('affiliate.admin.approve', $profile))
            ->assertRedirect();

        $this->assertSame(AffiliateProfileStatus::ACTIVE, $profile->fresh()->status);
        $this->assertTrue($employee->fresh()->hasRole('Teacher'));
        $this->assertFalse($employee->fresh()->hasRole('Affiliate-user'));
        $this->assertSame($before, $this->SnapshotStaffIdentity($employee->fresh()));
    }

    #[Test]
    public function SuspendingAndDeactivatingEmployeeProfilePreserveStaffIdentity(): void
    {
        [$employee, $profile] = $this->CreateEmployee(AffiliateProfileStatus::ACTIVE, 'employee-lifecycle');
        $before = $this->SnapshotStaffIdentity($employee);
        $admin = $this->CreateAdmin('affiliate-suspend');

        $this->actingAs($admin)
            ->post(route('affiliate.admin.suspend', $profile), ['reason' => 'Policy review'])
            ->assertRedirect();
        $this->assertSame(AffiliateProfileStatus::SUSPENDED, $profile->fresh()->status);
        $this->assertSame($before, $this->SnapshotStaffIdentity($employee->fresh()));

        $this->actingAs($admin)
            ->post(route('affiliate.admin.deactivate', $profile), ['reason' => 'Requested closure'])
            ->assertRedirect();
        $this->assertSame(AffiliateProfileStatus::DEACTIVATED, $profile->fresh()->status);
        $this->assertSame($before, $this->SnapshotStaffIdentity($employee->fresh()));
        $this->assertFalse($employee->fresh()->hasRole('Affiliate-user'));
    }

    #[Test]
    public function DeactivatedEmployeeProfileRemainsTerminal(): void
    {
        [$employee, $profile] = $this->CreateEmployee(AffiliateProfileStatus::DEACTIVATED, 'employee-terminal');
        $admin = $this->CreateAdmin('affiliate-suspend');

        $this->actingAs($admin)
            ->post(route('affiliate.admin.reactivate', $profile), ['reason' => 'Attempted reactivation'])
            ->assertRedirect()
            ->assertSessionHasErrors('affiliate');

        $this->assertSame(AffiliateProfileStatus::DEACTIVATED, $profile->fresh()->status);
        $this->assertTrue($employee->fresh()->hasRole('Teacher'));
    }

    #[Test]
    public function ExistingAdminPaginationLinksRemainAvailableAfterFiftyProfiles(): void
    {
        for ($index = 1; $index <= 51; $index++) {
            $this->CreateEmployee(AffiliateProfileStatus::PENDING_APPROVAL, 'employee-page-' . $index);
        }
        $admin = $this->CreateAdmin('affiliate-list');

        $this->actingAs($admin)
            ->get(route('affiliate.admin.index'))
            ->assertOk()
            ->assertSee('page=2', false);
    }

    private function CreateEmployee(AffiliateProfileStatus $status, string $name_suffix): array
    {
        $employee = User::factory()->create([
            'name' => $name_suffix,
            'email' => $name_suffix . '@example.test',
            'email_verified_at' => null,
            'password' => Hash::make('secret'),
            'phone' => '0812345678',
            'department_id' => 5,
            'position_id' => 13,
            'affiliate_request' => 0,
            'disable' => 0,
            'referral' => null,
        ]);
        $employee->assignRole(Role::findOrCreate('Teacher', 'web'));
        $profile = AffiliateProfile::create([
            'user_id' => $employee->id,
            'referral_code' => 'AFF-ADMIN-' . $employee->id,
            'status' => $status,
        ]);

        return [$employee->fresh(), $profile->fresh()];
    }

    private function CreateAdmin(string $permission_name): User
    {
        $admin = User::factory()->create([
            'department_id' => 1,
            'email_verified_at' => now(),
            'password' => Hash::make('secret'),
        ]);
        $admin->assignRole(Role::findOrCreate('Admin', 'web'));
        $admin->givePermissionTo(Permission::findOrCreate('all-centre', 'web'));
        $admin->givePermissionTo(Permission::findOrCreate($permission_name, 'web'));

        return $admin->fresh();
    }

    private function SnapshotStaffIdentity(User $user): array
    {
        return [
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at,
            'password' => $user->password,
            'phone' => $user->phone,
            'department_id' => $user->department_id,
            'position_id' => $user->position_id,
            'affiliate_request' => $user->affiliate_request,
            'disable' => $user->disable,
            'referral' => $user->referral,
            'roles' => $user->getRoleNames()->sort()->values()->all(),
        ];
    }
}
