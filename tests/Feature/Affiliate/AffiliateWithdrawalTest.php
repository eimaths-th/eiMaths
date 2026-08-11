<?php

namespace Tests\Feature\Affiliate;

use App\Domain\Affiliate\Enums\AffiliateLedgerEntryType;
use App\Domain\Affiliate\Enums\AffiliatePayoutType;
use App\Domain\Affiliate\Enums\AffiliateWithdrawalStatus;
use App\Domain\Affiliate\Models\AffiliatePayoutAccount;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Domain\Affiliate\Models\AffiliateWalletBalance;
use App\Domain\Affiliate\Services\AffiliatePayoutAccountService;
use App\Domain\Affiliate\Services\AffiliateWalletService;
use App\Domain\Affiliate\Services\AffiliateWithdrawalService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AffiliateWithdrawalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('affiliate.features.wallet_withdrawals', true);
    }

    public function test_request_reserves_immediately_and_pay_requires_different_actor(): void
    {
        [$owner, $profile] = $this->createProfile();
        $account = app(AffiliatePayoutAccountService::class)->UpsertPromptPay($owner, ['promptpay_identifier' => '0812345678'], 'corr-withdraw-setup');
        $payout_account = AffiliatePayoutAccount::findOrFail($account['id']);
        app(AffiliateWalletService::class)->ApplyEntry($profile, \App\Domain\Affiliate\Enums\AffiliateLedgerEntryType::OPENING_BALANCE, 5000, 0, 'opening', 1, 'withdrawal:opening:1', null, 'OPENING_BALANCE', 'corr-withdraw-opening');

        $service = app(AffiliateWithdrawalService::class);
        $withdrawal = $service->RequestWithdrawal($owner, '10.00', $payout_account->id, 'withdrawal-request-001', 'corr-withdraw-001');
        $this->assertSame(AffiliateWithdrawalStatus::REQUESTED, $withdrawal->status);
        $this->assertSame(4000, AffiliateWalletBalance::where('affiliate_profile_id', $profile->id)->value('available_balance_minor'));
        $this->assertSame(1000, AffiliateWalletBalance::where('affiliate_profile_id', $profile->id)->value('reserved_balance_minor'));

        $admin = User::factory()->create(['password' => Hash::make('secret')]);
        $service->Approve($withdrawal, $admin, 'approval-key-001', 'corr-withdraw-002');
        $this->expectException(\App\Domain\Affiliate\Exceptions\AffiliateDomainException::class);
        $service->Pay($withdrawal, $admin, 'bank-ref-001', 'payment-key-001', 'corr-withdraw-003');
    }

    public function test_cancellation_releases_reservation_once_and_duplicate_request_is_idempotent(): void
    {
        [$owner, $profile] = $this->createProfile();
        $account = app(AffiliatePayoutAccountService::class)->UpsertPromptPay($owner, ['promptpay_identifier' => '0812345678'], 'corr-withdraw-setup-2');
        $payout_account = AffiliatePayoutAccount::findOrFail($account['id']);
        app(AffiliateWalletService::class)->ApplyEntry($profile, \App\Domain\Affiliate\Enums\AffiliateLedgerEntryType::OPENING_BALANCE, 5000, 0, 'opening', 2, 'withdrawal:opening:2', null, 'OPENING_BALANCE', 'corr-withdraw-opening-2');
        $service = app(AffiliateWithdrawalService::class);
        $first = $service->RequestWithdrawal($owner, '10.00', $payout_account->id, 'withdrawal-request-002', 'corr-withdraw-004');
        $second = $service->RequestWithdrawal($owner, '10.00', $payout_account->id, 'withdrawal-request-002', 'corr-withdraw-005');
        $this->assertSame($first->id, $second->id);
        $service->Cancel($first, $owner, 'No longer needed', 'closure-key-001', 'corr-withdraw-006');
        $this->assertSame(5000, AffiliateWalletBalance::where('affiliate_profile_id', $profile->id)->value('available_balance_minor'));
        $this->assertSame(0, AffiliateWalletBalance::where('affiliate_profile_id', $profile->id)->value('reserved_balance_minor'));
    }

    public function test_other_affiliate_cannot_cancel_a_withdrawal(): void
    {
        [$owner, $profile] = $this->createProfile();
        [$other_owner] = $this->createProfile();
        $account = app(AffiliatePayoutAccountService::class)->UpsertPromptPay($owner, ['promptpay_identifier' => '0812345678'], 'corr-withdraw-setup-3');
        $payout_account = AffiliatePayoutAccount::findOrFail($account['id']);
        app(AffiliateWalletService::class)->ApplyEntry($profile, AffiliateLedgerEntryType::OPENING_BALANCE, 5000, 0, 'opening', 3, 'withdrawal:opening:3', null, 'OPENING_BALANCE', 'corr-withdraw-opening-3');

        $service = app(AffiliateWithdrawalService::class);
        $withdrawal = $service->RequestWithdrawal($owner, '10.00', $payout_account->id, 'withdrawal-request-003', 'corr-withdraw-007');

        try {
            $service->Cancel($withdrawal, $other_owner, 'Unauthorized cancellation', 'closure-key-002', 'corr-withdraw-008');
            $this->fail('An Affiliate owner must not cancel another owner\'s withdrawal.');
        } catch (\App\Domain\Affiliate\Exceptions\AffiliateDomainException $exception) {
            $this->assertSame('AFFILIATE_OWNERSHIP_DENIED', $exception->ErrorCode());
        }

        $this->assertSame(AffiliateWithdrawalStatus::REQUESTED, $withdrawal->fresh()->status);
        $this->assertSame(4000, AffiliateWalletBalance::where('affiliate_profile_id', $profile->id)->value('available_balance_minor'));
        $this->assertSame(1000, AffiliateWalletBalance::where('affiliate_profile_id', $profile->id)->value('reserved_balance_minor'));
    }

    public function test_negative_available_balance_blocks_withdrawal_and_opens_reconciliation_issue(): void
    {
        [$owner, $profile] = $this->createProfile();
        $account = app(AffiliatePayoutAccountService::class)->UpsertPromptPay($owner, ['promptpay_identifier' => '0812345678'], 'corr-withdraw-negative-setup');
        $payout_account = AffiliatePayoutAccount::findOrFail($account['id']);
        app(AffiliateWalletService::class)->ApplyEntry($profile, AffiliateLedgerEntryType::COMMISSION_REVERSAL, -500, 0, 'affiliate_commission_reversal', 4, 'withdrawal:negative-balance', null, 'COMMISSION_REVERSAL', 'corr-withdraw-negative-opening');

        try {
            app(AffiliateWithdrawalService::class)->RequestWithdrawal($owner, '10.00', $payout_account->id, 'withdrawal-negative-balance', 'corr-withdraw-negative-request');
            $this->fail('A negative available balance must block withdrawal requests.');
        } catch (\App\Domain\Affiliate\Exceptions\AffiliateDomainException $exception) {
            $this->assertSame('AFFILIATE_NEGATIVE_BALANCE_WITHDRAWAL', $exception->ErrorCode());
        }

        $this->assertDatabaseHas('affiliate_reconciliation_issues', [
            'source_table' => 'affiliate_wallet_balances',
            'source_id' => $profile->id,
            'issue_code' => 'NEGATIVE_AVAILABLE_BALANCE',
            'severity' => 'HIGH',
        ]);
    }

    private function createProfile(): array
    {
        $owner = User::factory()->create(['password' => Hash::make('secret')]);
        $profile = AffiliateProfile::create(['user_id' => $owner->id, 'referral_code' => 'AFF-WITHDRAW-' . $owner->id, 'status' => 'ACTIVE']);
        return [$owner, $profile];
    }
}
