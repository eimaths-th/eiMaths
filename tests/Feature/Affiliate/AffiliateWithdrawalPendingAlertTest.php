<?php

namespace Tests\Feature\Affiliate;

use App\Domain\Affiliate\Enums\AffiliateProfileStatus;
use App\Domain\Affiliate\Enums\AffiliateWithdrawalStatus;
use App\Domain\Affiliate\Models\AffiliatePayoutAccount;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Domain\Affiliate\Models\AffiliateWithdrawalRequest;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AffiliateWithdrawalPendingAlertTest extends TestCase
{
    use RefreshDatabase;

    private Department $hq;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hq = Department::query()->create([
            'id' => Department::HQ_ID,
            'name' => 'Withdrawal Alert HQ',
            'code' => 'WAL-HQ',
            'status' => 1,
        ]);
        Config::set('affiliate.features.wallet_withdrawals', true);
    }

    #[Test]
    public function AuthorizedAdminReceivesActionableWithdrawalSummary(): void
    {
        [$profile, $payout_account] = $this->CreateAffiliatePayoutAccount();
        $requested = $this->CreateWithdrawal($profile, $payout_account, AffiliateWithdrawalStatus::REQUESTED, 'requested');
        $approved = $this->CreateWithdrawal($profile, $payout_account, AffiliateWithdrawalStatus::APPROVED, 'approved');
        $paid = $this->CreateWithdrawal($profile, $payout_account, AffiliateWithdrawalStatus::PAID, 'paid');
        $requested->forceFill(['updated_at' => now()->subMinutes(3)])->save();
        $approved->forceFill(['updated_at' => now()->subMinutes(2)])->save();
        $paid->forceFill(['updated_at' => now()->subMinute()])->save();

        $admin = $this->CreateAdminWithPermission();

        $this->actingAs($admin)
            ->getJson(route('affiliate.withdrawals.admin.pending-summary'))
            ->assertOk()
            ->assertJsonPath('data.actionable_count', 2)
            ->assertJsonPath('data.requested_count', 1)
            ->assertJsonPath('data.approved_count', 1)
            ->assertJsonPath('data.latest_event_status', AffiliateWithdrawalStatus::PAID->value)
            ->assertJsonPath('data.latest_actionable_withdrawal.id', $approved->id)
            ->assertJsonPath('data.latest_actionable_withdrawal.affiliate_name', 'Withdrawal Affiliate')
            ->assertJsonPath('data.latest_actionable_withdrawal.amount_minor', 250000)
            ->assertJsonPath('data.latest_actionable_withdrawal.status', AffiliateWithdrawalStatus::APPROVED->value);
    }

    #[Test]
    public function AuthorizedAdminSeesWithdrawalAlertAndMenuBadgeHooks(): void
    {
        $admin = $this->CreateAdminWithPermission();

        $this->actingAs($admin)
            ->get(route('affiliate.withdrawals.admin.index'))
            ->assertOk()
            ->assertSee('data-affiliate-withdrawal-alert', false)
            ->assertSee('data-pending-work-item="withdrawal"', false)
            ->assertSee('data-affiliate-withdrawal-badge', false)
            ->assertSee('data-affiliate-management-pending-badge', false)
            ->assertSee(route('affiliate.withdrawals.admin.pending-summary'), false);
    }

    #[Test]
    public function UserWithoutWithdrawalListPermissionCannotReadPendingSummary(): void
    {
        $user = User::factory()->create([
            'department_id' => $this->hq->id,
            'email_verified_at' => now(),
            'password' => Hash::make('secret'),
            'disable' => 0,
        ]);

        $this->actingAs($user)
            ->getJson(route('affiliate.withdrawals.admin.pending-summary'))
            ->assertForbidden();
    }

    private function CreateAdminWithPermission(): User
    {
        $admin = User::factory()->create([
            'department_id' => $this->hq->id,
            'email_verified_at' => now(),
            'password' => Hash::make('secret'),
            'disable' => 0,
        ]);
        $admin->givePermissionTo(Permission::findOrCreate('affiliate-withdrawal-list', 'web'));

        return $admin->fresh();
    }

    private function CreateAffiliatePayoutAccount(): array
    {
        $owner = User::factory()->create([
            'name' => 'Withdrawal Affiliate',
            'department_id' => $this->hq->id,
            'email_verified_at' => now(),
            'password' => Hash::make('secret'),
            'disable' => 0,
        ]);
        $profile = AffiliateProfile::query()->create([
            'user_id' => $owner->id,
            'referral_code' => 'WITHDRAWAL-ALERT',
            'status' => AffiliateProfileStatus::ACTIVE,
            'approved_at' => now(),
        ]);
        $payout_account = AffiliatePayoutAccount::query()->create([
            'affiliate_profile_id' => $profile->id,
            'payout_type' => 'BANK',
            'details_encrypted' => [
                'bank_name' => 'Test Bank',
                'account_name' => 'Withdrawal Affiliate',
                'account_number' => '1234567890',
            ],
            'masked_label' => '******7890',
            'is_active' => true,
        ]);

        return [$profile, $payout_account];
    }

    private function CreateWithdrawal(
        AffiliateProfile $profile,
        AffiliatePayoutAccount $payout_account,
        AffiliateWithdrawalStatus $status,
        string $request_suffix,
    ): AffiliateWithdrawalRequest {
        return AffiliateWithdrawalRequest::query()->create([
            'affiliate_profile_id' => $profile->id,
            'affiliate_payout_account_id' => $payout_account->id,
            'payout_type' => 'BANK',
            'amount_minor' => 250000,
            'status' => $status,
            'payout_snapshot_encrypted' => [
                'bank_name' => 'Test Bank',
                'account_name' => 'Withdrawal Affiliate',
                'account_number' => '1234567890',
            ],
            'requested_at' => now(),
            'request_key' => 'withdrawal-alert-'.$request_suffix,
        ]);
    }
}
