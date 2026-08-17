<?php

namespace Tests\Feature\Affiliate;

use App\Domain\Affiliate\Enums\AffiliateProfileStatus;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AffiliateEmployeeRedirectTest extends TestCase
{
    use RefreshDatabase;

    private int $employee_department_id;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('affiliate.features.employee_enrollment', true);
        $this->employee_department_id = Department::create([
            'name' => 'Employee Redirect Test',
            'code' => 'EMP-REDIRECT',
            'status' => 1,
        ])->id;
    }

    #[Test]
    public function InternalOnlyEmployeeReachesStaffHome(): void
    {
        $employee = $this->CreateUser(['Teacher']);

        $this->actingAs($employee)
            ->get('/home')
            ->assertOk();
    }

    #[Test]
    public function EmployeeWithLegacyAffiliateRoleStillReachesStaffHome(): void
    {
        $employee = $this->CreateUser(['Teacher', 'Affiliate-user']);

        $this->actingAs($employee)
            ->get('/home')
            ->assertOk();
    }

    #[Test]
    public function ActiveExternalAffiliateReachesAffiliateDashboardFromRoot(): void
    {
        $affiliate_user = $this->CreateUser(['Affiliate-user'], AffiliateProfileStatus::ACTIVE, now());

        $this->actingAs($affiliate_user)
            ->get('/')
            ->assertRedirect(route('affiliate.dashboard'));
    }

    #[Test]
    public function PendingExternalAffiliateReachesAffiliateWaitingFromRoot(): void
    {
        $affiliate_user = $this->CreateUser(['Affiliate-user'], AffiliateProfileStatus::PENDING_APPROVAL, now());

        $this->actingAs($affiliate_user)
            ->get('/')
            ->assertRedirect(route('affiliate.waiting'));
    }

    #[Test]
    public function DisabledEmployeeStillCannotLogIn(): void
    {
        $employee = $this->CreateUser(['Teacher'], null, null, ['disable' => 1]);

        $this->post(route('login'), [
            'email' => $employee->email,
            'password' => 'secret',
        ])
            ->assertRedirect(route('login'))
            ->assertSessionHas('login_error');

        $this->assertGuest();
    }

    #[Test]
    public function AuthenticatedEmployeeVisitingAffiliateLoginReturnsToStaffHome(): void
    {
        $employee = $this->CreateUser(['Teacher']);

        $this->actingAs($employee)
            ->get(route('affiliate.login'))
            ->assertRedirect(route('home'));
    }

    #[Test]
    public function EmployeeVisitingAffiliateWaitingReturnsToEmployeeStatus(): void
    {
        $employee = $this->CreateUser(['Teacher'], AffiliateProfileStatus::PENDING_APPROVAL);

        $this->actingAs($employee)
            ->get(route('affiliate.waiting'))
            ->assertRedirect(route('affiliate.employee-access.show'));
    }

    private function CreateUser(
        array $roles,
        ?AffiliateProfileStatus $profile_status = null,
        mixed $email_verified_at = null,
        array $attributes = [],
    ): User {
        $user = User::factory()->create(array_merge([
            'email_verified_at' => $email_verified_at,
            'password' => Hash::make('secret'),
            'department_id' => $this->employee_department_id,
        ], $attributes));

        foreach ($roles as $role_name) {
            $user->assignRole(Role::findOrCreate($role_name, 'web'));
        }

        if ($profile_status !== null) {
            AffiliateProfile::create([
                'user_id' => $user->id,
                'referral_code' => 'AFF-REDIRECT-' . $user->id,
                'status' => $profile_status,
            ]);
        }

        return $user->fresh();
    }
}
