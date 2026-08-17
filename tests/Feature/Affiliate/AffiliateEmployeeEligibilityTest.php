<?php

namespace Tests\Feature\Affiliate;

use App\Domain\Affiliate\Services\AffiliateEmployeeEligibility;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AffiliateEmployeeEligibilityTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function EachApprovedEmployeeRoleIsEligible(): void
    {
        foreach (['Admin', 'SystemAdmin', 'BranchAdmin', 'Teacher', 'blog'] as $role_name) {
            $user = $this->CreateUser();
            $user->assignRole(Role::findOrCreate($role_name, 'web'));

            $this->assertTrue(
                app(AffiliateEmployeeEligibility::class)->IsEligible($user),
                "Expected role {$role_name} to be eligible."
            );
        }
    }

    #[Test]
    public function UserRoleIsNotEligible(): void
    {
        $user = $this->CreateUser();
        $user->assignRole(Role::findOrCreate('User', 'web'));

        $this->assertFalse(app(AffiliateEmployeeEligibility::class)->IsEligible($user));
    }

    #[Test]
    public function AffiliateOnlyUserIsNotEligibleAndIsClassifiedAsAffiliateOnly(): void
    {
        $user = $this->CreateUser();
        $user->assignRole(Role::findOrCreate('Affiliate-user', 'web'));

        $eligibility = app(AffiliateEmployeeEligibility::class);

        $this->assertFalse($eligibility->IsEligible($user));
        $this->assertTrue($eligibility->IsAffiliateOnly($user));
    }

    #[Test]
    public function UnknownRoleIsNotEligible(): void
    {
        $user = $this->CreateUser();
        $user->assignRole(Role::findOrCreate('Unknown-role', 'web'));

        $this->assertFalse(app(AffiliateEmployeeEligibility::class)->IsEligible($user));
    }

    #[Test]
    public function RolelessUserIsNotEligible(): void
    {
        $this->assertFalse(
            app(AffiliateEmployeeEligibility::class)->IsEligible($this->CreateUser())
        );
    }

    #[Test]
    public function EmptyEmployeeRoleConfigurationFailsClosed(): void
    {
        Config::set('affiliate.employee_roles', []);
        $user = $this->CreateUser();
        $user->assignRole(Role::findOrCreate('Admin', 'web'));

        $eligibility = app(AffiliateEmployeeEligibility::class);

        $this->assertFalse($eligibility->IsEligible($user));
        $this->assertFalse($eligibility->IsAffiliateOnly($user));
    }

    #[Test]
    public function EligibleEmployeeWithAffiliateUserRoleIsNotAffiliateOnly(): void
    {
        $user = $this->CreateUser();
        $user->assignRole([
            Role::findOrCreate('Teacher', 'web'),
            Role::findOrCreate('Affiliate-user', 'web'),
        ]);

        $eligibility = app(AffiliateEmployeeEligibility::class);

        $this->assertTrue($eligibility->IsEligible($user));
        $this->assertFalse($eligibility->IsAffiliateOnly($user));
    }

    #[Test]
    public function RoleDecisionsUseCurrentConfigurationForEachCall(): void
    {
        $user = $this->CreateUser();
        $user->assignRole(Role::findOrCreate('Teacher', 'web'));
        $eligibility = app(AffiliateEmployeeEligibility::class);

        Config::set('affiliate.employee_roles', ['Teacher']);
        $this->assertTrue($eligibility->IsEligible($user));

        Config::set('affiliate.employee_roles', []);
        $this->assertFalse($eligibility->IsEligible($user));
    }

    private function CreateUser(): User
    {
        return User::factory()->create(['password' => Hash::make('secret')]);
    }
}
