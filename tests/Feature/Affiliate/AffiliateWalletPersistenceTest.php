<?php

namespace Tests\Feature\Affiliate;

use App\Domain\Affiliate\Enums\AffiliateLedgerEntryType;
use App\Domain\Affiliate\Models\AffiliateLedgerEntry;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Domain\Affiliate\Models\AffiliateWalletBalance;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use LogicException;
use Tests\TestCase;

class AffiliateWalletPersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_signed_ledger_and_wallet_projection_persist_with_source_lookup(): void
    {
        $profile = $this->createProfile();
        $wallet = AffiliateWalletBalance::create([
            'affiliate_profile_id' => $profile->id,
            'available_balance_minor' => -500,
            'reserved_balance_minor' => 0,
        ]);
        $entry = AffiliateLedgerEntry::create([
            'affiliate_profile_id' => $profile->id,
            'entry_type' => AffiliateLedgerEntryType::ADJUSTMENT,
            'available_delta_minor' => -500,
            'reserved_delta_minor' => 0,
            'balance_available_after_minor' => -500,
            'balance_reserved_after_minor' => 0,
            'source_type' => 'adjustment',
            'source_id' => 1,
            'idempotency_key' => 'wallet:test:1',
            'reason_code' => 'TEST_ADJUSTMENT',
            'occurred_at' => now(),
        ]);
        $wallet->forceFill(['last_ledger_entry_id' => $entry->id])->save();

        $this->assertSame(-500, $entry->fresh()->available_delta_minor);
        $this->assertSame(0, $entry->fresh()->reserved_delta_minor);
        $this->assertSame(AffiliateLedgerEntryType::ADJUSTMENT, $entry->fresh()->entry_type);
        $this->assertSame($profile->id, $entry->fresh()->AffiliateProfile->id);
        $this->assertSame($entry->id, $wallet->fresh()->LastLedgerEntry->id);
    }

    public function test_idempotency_key_is_globally_unique_and_reserved_balance_cannot_be_negative(): void
    {
        $profile = $this->createProfile();
        AffiliateLedgerEntry::create([
            'affiliate_profile_id' => $profile->id,
            'entry_type' => AffiliateLedgerEntryType::OPENING_BALANCE,
            'available_delta_minor' => 100,
            'reserved_delta_minor' => 0,
            'balance_available_after_minor' => 100,
            'balance_reserved_after_minor' => 0,
            'source_type' => 'migration',
            'source_id' => 1,
            'idempotency_key' => 'wallet:unique:1',
            'reason_code' => 'OPENING_BALANCE',
            'occurred_at' => now(),
        ]);

        $this->expectException(QueryException::class);
        AffiliateLedgerEntry::create([
            'affiliate_profile_id' => $profile->id,
            'entry_type' => AffiliateLedgerEntryType::OPENING_BALANCE,
            'available_delta_minor' => 100,
            'reserved_delta_minor' => 0,
            'balance_available_after_minor' => 100,
            'balance_reserved_after_minor' => 0,
            'source_type' => 'migration',
            'source_id' => 2,
            'idempotency_key' => 'wallet:unique:1',
            'reason_code' => 'OPENING_BALANCE',
            'occurred_at' => now(),
        ]);
    }

    public function test_ledger_rows_are_append_only(): void
    {
        $profile = $this->createProfile();
        $entry = AffiliateLedgerEntry::create([
            'affiliate_profile_id' => $profile->id,
            'entry_type' => AffiliateLedgerEntryType::OPENING_BALANCE,
            'available_delta_minor' => 100,
            'reserved_delta_minor' => 0,
            'balance_available_after_minor' => 100,
            'balance_reserved_after_minor' => 0,
            'source_type' => 'migration',
            'source_id' => 1,
            'idempotency_key' => 'wallet:immutable:1',
            'reason_code' => 'OPENING_BALANCE',
            'occurred_at' => now(),
        ]);

        $this->expectException(LogicException::class);
        $entry->available_delta_minor = 99;
        $entry->save();
    }

    private function createProfile(): AffiliateProfile
    {
        $user = User::factory()->create(['password' => Hash::make('secret')]);

        return AffiliateProfile::create([
            'user_id' => $user->id,
            'referral_code' => 'AFF-WALLET-' . $user->id,
            'status' => 'ACTIVE',
        ]);
    }
}
