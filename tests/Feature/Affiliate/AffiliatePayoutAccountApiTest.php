<?php

namespace Tests\Feature\Affiliate;

use App\Domain\Affiliate\Enums\AffiliatePayoutType;
use App\Domain\Affiliate\Models\AffiliatePayoutAccount;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Domain\Affiliate\Services\AffiliatePayoutAccountService;
use App\Domain\Affiliate\Services\AffiliateWalletService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AffiliatePayoutAccountApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('affiliate.features.wallet_withdrawals', true);
    }

    public function test_promptpay_and_bank_values_are_normalized_masked_and_encrypted(): void
    {
        [$user, $profile] = $this->createProfile();
        $service = app(AffiliatePayoutAccountService::class);
        $promptpay = $service->UpsertPromptPay($user, ['promptpay_identifier' => '081-234-5678'], 'corr-payout-001');
        $bank = $service->UpsertBank($user, ['bank_name' => ' Demo Bank ', 'account_number' => '123-456-7890', 'account_name' => ' Demo User '], 'corr-payout-002');

        $this->assertSame('******5678', $promptpay['masked_label']);
        $this->assertSame('******7890', $bank['masked_label']);
        $raw = DB::table('affiliate_payout_accounts')->where('affiliate_profile_id', $profile->id)->pluck('details_encrypted')->implode('|');
        $this->assertStringNotContainsString('0812345678', $raw);
        $this->assertStringNotContainsString('1234567890', $raw);
        $this->assertSame(2, AffiliatePayoutAccount::where('affiliate_profile_id', $profile->id)->count());
    }

    public function test_inactive_profile_cannot_update_payout_account(): void
    {
        [$user] = $this->createProfile('SUSPENDED');
        $this->expectException(\App\Domain\Affiliate\Exceptions\AffiliateDomainException::class);
        app(AffiliatePayoutAccountService::class)->UpsertPromptPay($user, ['promptpay_identifier' => '0812345678'], 'corr-payout-003');
    }

    public function test_changed_payout_account_requires_password_and_applies_cooling_period(): void
    {
        [$user, $profile] = $this->createProfile();
        $service = app(AffiliatePayoutAccountService::class);
        $initial = $service->UpsertPromptPay($user, ['promptpay_identifier' => '0812345678'], 'corr-payout-004');

        try {
            $service->UpsertPromptPay($user, ['promptpay_identifier' => '0898765432'], 'corr-payout-005');
            $this->fail('Changing payout details without a current password must be rejected.');
        } catch (\App\Domain\Affiliate\Exceptions\AffiliateDomainException $exception) {
            $this->assertSame('AFFILIATE_PASSWORD_CONFIRMATION_REQUIRED', $exception->ErrorCode());
        }

        $changed = $service->UpsertPromptPay($user, [
            'promptpay_identifier' => '0898765432',
            'current_password' => 'secret',
        ], 'corr-payout-006');

        $this->assertNotNull($changed['payout_cooldown_until']);
        $this->assertSame(1, DB::table('notifications')->where('notifiable_id', $user->id)->count());
        $this->assertSame('0898765432', AffiliatePayoutAccount::query()->findOrFail($initial['id'])->fresh()->details_encrypted['promptpay_identifier']);

        app(AffiliateWalletService::class)->ApplyEntry($profile, \App\Domain\Affiliate\Enums\AffiliateLedgerEntryType::OPENING_BALANCE, 100000, 0, 'test', 1, 'payout-cooldown-opening', null, 'OPENING_BALANCE', 'corr-payout-007');
        $this->expectException(\App\Domain\Affiliate\Exceptions\AffiliateDomainException::class);
        app(\App\Domain\Affiliate\Services\AffiliateWithdrawalService::class)->RequestWithdrawal($user, '10.00', $changed['id'], 'payout-cooldown-withdrawal', 'corr-payout-008');
    }

    private function createProfile(string $status = 'ACTIVE'): array
    {
        $user = User::factory()->create(['password' => Hash::make('secret')]);
        $profile = AffiliateProfile::create(['user_id' => $user->id, 'referral_code' => 'AFF-PAYOUT-API-' . $user->id, 'status' => $status]);
        return [$user, $profile];
    }
}
