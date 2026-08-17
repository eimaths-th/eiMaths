<?php

namespace Tests\Feature\Affiliate;

use App\Domain\Affiliate\Enums\AffiliateLedgerEntryType;
use App\Domain\Affiliate\Exceptions\AffiliateDomainException;
use App\Domain\Affiliate\Models\AffiliateAuditLog;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Domain\Affiliate\Models\AffiliateWalletBalance;
use App\Domain\Affiliate\Services\AffiliateWalletService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AffiliateWalletServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_apply_entry_updates_projection_and_is_idempotent_for_same_payload(): void
    {
        $profile = $this->createProfile();
        $service = app(AffiliateWalletService::class);
        $first = $service->ApplyEntry($profile, AffiliateLedgerEntryType::COMMISSION_RELEASE, 2000, 0, 'commission', 1, 'ledger:test:1', null, 'COMMISSION_RELEASE', 'corr-wallet-001');
        $second = $service->ApplyEntry($profile, AffiliateLedgerEntryType::COMMISSION_RELEASE, 2000, 0, 'commission', 1, 'ledger:test:1', null, 'COMMISSION_RELEASE', 'corr-wallet-002');

        $this->assertSame($first->id, $second->id);
        $this->assertSame(2000, AffiliateWalletBalance::where('affiliate_profile_id', $profile->id)->value('available_balance_minor'));
        $this->assertSame(1, AffiliateAuditLog::where('operation', 'wallet.ledger.append')->count());
    }

    public function test_same_idempotency_key_with_different_payload_is_rejected(): void
    {
        $profile = $this->createProfile();
        $service = app(AffiliateWalletService::class);
        $service->ApplyEntry($profile, AffiliateLedgerEntryType::COMMISSION_RELEASE, 2000, 0, 'commission', 1, 'ledger:test:collision', null, 'COMMISSION_RELEASE', 'corr-wallet-003');

        $this->expectException(AffiliateDomainException::class);
        $service->ApplyEntry($profile, AffiliateLedgerEntryType::COMMISSION_RELEASE, 3000, 0, 'commission', 1, 'ledger:test:collision', null, 'COMMISSION_RELEASE', 'corr-wallet-004');
    }

    public function test_reserved_balance_cannot_become_negative_and_only_reversal_allows_negative_available(): void
    {
        $profile = $this->createProfile();
        $service = app(AffiliateWalletService::class);
        $service->ApplyEntry($profile, AffiliateLedgerEntryType::COMMISSION_RELEASE, 1000, 1000, 'commission', 2, 'ledger:test:reserve', null, 'COMMISSION_RELEASE', 'corr-wallet-005');

        $this->expectException(AffiliateDomainException::class);
        $service->ApplyEntry($profile, AffiliateLedgerEntryType::WITHDRAWAL_RELEASE, 0, -2000, 'withdrawal', 2, 'ledger:test:negative-reserved', null, 'WITHDRAWAL_RELEASE', 'corr-wallet-006');
    }

    public function test_commission_reversal_may_make_available_balance_negative(): void
    {
        $profile = $this->createProfile();
        $service = app(AffiliateWalletService::class);
        $entry = $service->ApplyEntry($profile, AffiliateLedgerEntryType::COMMISSION_REVERSAL, -500, 0, 'reversal', 2, 'ledger:test:negative-available', null, 'COMMISSION_REVERSAL', 'corr-wallet-007');

        $this->assertSame(-500, $entry->balance_available_after_minor);
        $offset = $service->ApplyEntry($profile, AffiliateLedgerEntryType::COMMISSION_RELEASE, 200, 0, 'commission', 3, 'ledger:test:negative-offset', null, 'COMMISSION_RELEASE', 'corr-wallet-008');
        $this->assertSame(-300, $offset->balance_available_after_minor);
        $cleared = $service->ApplyEntry($profile, AffiliateLedgerEntryType::COMMISSION_RELEASE, 300, 0, 'commission', 4, 'ledger:test:negative-offset-cleared', null, 'COMMISSION_RELEASE', 'corr-wallet-009');
        $this->assertSame(0, $cleared->balance_available_after_minor);
    }

    private function createProfile(): AffiliateProfile
    {
        $user = User::factory()->create(['password' => Hash::make('secret')]);
        return AffiliateProfile::create([
            'user_id' => $user->id,
            'referral_code' => 'AFF-WALLET-SVC-' . $user->id,
            'status' => 'ACTIVE',
        ]);
    }
}
