<?php

namespace Tests\Feature\Affiliate;

use App\Domain\Affiliate\Enums\AffiliateLedgerEntryType;
use App\Domain\Affiliate\Enums\AffiliateProfileStatus;
use App\Domain\Affiliate\Models\AffiliatePayoutAccount;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Domain\Affiliate\Services\AffiliateWalletService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AffiliatePortalAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('affiliate.features.employee_enrollment', true);
        Config::set('affiliate.features.analytics', true);
        Config::set('affiliate.features.media_library', true);
        Config::set('affiliate.features.wallet_withdrawals', true);
        Config::set('affiliate.features.administration', true);
    }

    #[Test]
    public function ActiveUnverifiedEmployeeCanOpenDashboard(): void
    {
        [$user] = $this->CreateEmployee(AffiliateProfileStatus::ACTIVE);

        $this->actingAs($user)
            ->get(route('affiliate.dashboard'))
            ->assertOk()
            ->assertViewIs('affiliate.dashboard');
    }

    #[Test]
    public function ActiveVerifiedExternalCanOpenDashboard(): void
    {
        [$user] = $this->CreateExternalAffiliate(AffiliateProfileStatus::ACTIVE, true);

        $this->actingAs($user)
            ->get(route('affiliate.dashboard'))
            ->assertOk()
            ->assertViewIs('affiliate.dashboard');
    }

    #[Test]
    public function ActiveUnverifiedExternalIsRedirectedToWaiting(): void
    {
        [$user] = $this->CreateExternalAffiliate(AffiliateProfileStatus::ACTIVE, false);

        $this->actingAs($user)
            ->get(route('affiliate.dashboard'))
            ->assertRedirect(route('affiliate.waiting'));
    }

    #[Test]
    public function NonActiveEmployeeIsRedirectedToEmployeeStatusPage(): void
    {
        foreach ([
            AffiliateProfileStatus::PENDING_APPROVAL,
            AffiliateProfileStatus::SUSPENDED,
            AffiliateProfileStatus::DEACTIVATED,
        ] as $status) {
            [$user] = $this->CreateEmployee($status);

            $this->actingAs($user)
                ->get(route('affiliate.dashboard'))
                ->assertRedirect(route('affiliate.employee-access.show'));
        }
    }

    #[Test]
    public function IneligibleUserReceivesPlainForbiddenResponse(): void
    {
        [$user] = $this->CreateUserWithProfile(AffiliateProfileStatus::ACTIVE);

        $this->actingAs($user)
            ->get(route('affiliate.dashboard'))
            ->assertStatus(403)
            ->assertSee(__('affiliate.errors.ownership_denied'));
    }

    #[Test]
    public function IneligibleJsonRequestReceivesLocalizedForbiddenResponse(): void
    {
        [$user] = $this->CreateUserWithProfile(AffiliateProfileStatus::ACTIVE);

        $this->actingAs($user)
            ->getJson(route('affiliate.dashboard'))
            ->assertStatus(403)
            ->assertJsonPath('error_code', 'AFFILIATE_OWNERSHIP_DENIED')
            ->assertJsonPath('message', __('affiliate.errors.ownership_denied'));
    }

    #[Test]
    public function UnsafeRequestIsForbiddenInsteadOfRedirecting(): void
    {
        [$user] = $this->CreateEmployee(AffiliateProfileStatus::PENDING_APPROVAL);

        $this->actingAs($user)
            ->post(route('affiliate.withdrawals.store'), [])
            ->assertStatus(403)
            ->assertSee(__('affiliate.errors.ownership_denied'));
    }

    #[Test]
    public function AdministratorRoutesRemainUnchanged(): void
    {
        $admin = User::factory()->create([
            'email_verified_at' => null,
            'password' => Hash::make('secret'),
        ]);
        $admin->givePermissionTo(Permission::findOrCreate('affiliate-list', 'web'));

        $this->actingAs($admin)
            ->get(route('affiliate.admin.index'))
            ->assertOk()
            ->assertViewIs('affiliate.admin.index');
    }

    #[Test]
    public function EmployeePortalRequestsReachExistingDomainAndFeatureBoundaries(): void
    {
        [$user, $profile] = $this->CreateEmployee(AffiliateProfileStatus::ACTIVE);

        $this->actingAs($user)
            ->getJson(route('affiliate.analytics.data', [
                'from' => '2026-08-01',
                'to' => '2026-08-03',
            ]))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('affiliate.media.index'))
            ->assertOk()
            ->assertViewIs('affiliate.media.index');

        $this->actingAs($user)
            ->put(route('affiliate.payout-accounts.promptpay'), [
                'promptpay_identifier' => '0812345678',
                'current_password' => 'secret',
            ])
            ->assertRedirect(route('affiliate.payout-accounts.index'));

        $payout_account = AffiliatePayoutAccount::query()->where('affiliate_profile_id', $profile->id)->firstOrFail();
        app(AffiliateWalletService::class)->ApplyEntry(
            $profile,
            AffiliateLedgerEntryType::OPENING_BALANCE,
            100000,
            0,
            'affiliate_test',
            1,
            'employee-portal-opening',
            null,
            'OPENING_BALANCE',
            'employee-portal-correlation',
        );

        $this->actingAs($user)
            ->post(route('affiliate.withdrawals.store'), [
                'amount' => '10.00',
                'payout_account_id' => $payout_account->id,
                'request_key' => 'employee-portal-withdrawal',
            ])
            ->assertRedirect(route('affiliate.withdrawals.index'));
    }

    private function CreateEmployee(AffiliateProfileStatus $status): array
    {
        return $this->CreateProfiledUser($status, 'Teacher', null);
    }

    private function CreateExternalAffiliate(AffiliateProfileStatus $status, bool $verified): array
    {
        return $this->CreateProfiledUser($status, 'Affiliate-user', $verified ? now() : null);
    }

    private function CreateUserWithProfile(AffiliateProfileStatus $status): array
    {
        return $this->CreateProfiledUser($status, 'User', null);
    }

    private function CreateProfiledUser(
        AffiliateProfileStatus $status,
        string $role_name,
        mixed $email_verified_at,
    ): array {
        $user = User::factory()->create([
            'email_verified_at' => $email_verified_at,
            'password' => Hash::make('secret'),
        ]);
        $user->assignRole(Role::findOrCreate($role_name, 'web'));
        $profile = AffiliateProfile::create([
            'user_id' => $user->id,
            'referral_code' => 'AFF-PORTAL-' . $user->id,
            'status' => $status,
        ]);

        return [$user->fresh(), $profile->fresh()];
    }
}
