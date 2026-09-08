<?php

namespace Tests\Feature\Affiliate;

use App\Domain\Affiliate\Enums\AffiliateProfileStatus;
use App\Domain\Affiliate\Exceptions\AffiliateDomainException;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Domain\Affiliate\Services\AffiliateAccountService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AffiliateAccountLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_approval_suspend_reactivate_and_deactivate_are_server_owned_transitions(): void
    {
        $user = User::factory()->create(['password' => Hash::make('secret')]);
        $actor = User::factory()->create(['password' => Hash::make('secret')]);
        $profile = AffiliateProfile::create([
            'user_id' => $user->id,
            'referral_code' => 'AFF-LIFECYCLE-' . $user->id,
            'status' => AffiliateProfileStatus::PENDING_APPROVAL,
        ]);
        $service = app(AffiliateAccountService::class);

        $service->Approve($profile, $actor);
        $this->assertSame(AffiliateProfileStatus::ACTIVE, $profile->fresh()->status);
        $service->Suspend($profile, $actor, 'Policy review');
        $this->assertSame(AffiliateProfileStatus::SUSPENDED, $profile->fresh()->status);
        $service->Reactivate($profile, $actor, 'Review complete');
        $this->assertSame(AffiliateProfileStatus::ACTIVE, $profile->fresh()->status);
        $service->Deactivate($profile, $actor, 'Requested closure');
        $this->assertSame(AffiliateProfileStatus::DEACTIVATED, $profile->fresh()->status);
    }

    public function test_terminal_deactivation_cannot_be_reactivated(): void
    {
        $user = User::factory()->create(['password' => Hash::make('secret')]);
        $actor = User::factory()->create(['password' => Hash::make('secret')]);
        $profile = AffiliateProfile::create([
            'user_id' => $user->id,
            'referral_code' => 'AFF-TERMINAL-' . $user->id,
            'status' => AffiliateProfileStatus::DEACTIVATED,
        ]);

        $this->expectException(AffiliateDomainException::class);
        app(AffiliateAccountService::class)->Reactivate($profile, $actor, 'No');
    }

    #[Test]
    public function EmployeeLifecycleTransitionsDoNotAssignAffiliateRole(): void
    {
        $employee = User::factory()->create(['password' => Hash::make('secret')]);
        $role = Role::findOrCreate('Teacher', 'web');
        $role->givePermissionTo(\Spatie\Permission\Models\Permission::findOrCreate('affiliate-user', 'web'));
        $employee->assignRole($role);
        $actor = User::factory()->create(['password' => Hash::make('secret')]);
        $profile = AffiliateProfile::create([
            'user_id' => $employee->id,
            'referral_code' => 'AFF-EMPLOYEE-LIFECYCLE-' . $employee->id,
            'status' => AffiliateProfileStatus::PENDING_APPROVAL,
        ]);

        app(AffiliateAccountService::class)->Approve($profile, $actor);

        $this->assertTrue($employee->fresh()->hasRole('Teacher'));
        $this->assertFalse($employee->fresh()->hasRole('Affiliate-user'));
        $this->assertSame(AffiliateProfileStatus::ACTIVE, $profile->fresh()->status);
    }
}
