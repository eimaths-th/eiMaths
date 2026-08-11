<?php

namespace Tests\Feature\Affiliate;

use App\Domain\Affiliate\Enums\AffiliateLedgerEntryType;
use App\Domain\Affiliate\Models\AffiliateLedgerEntry;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AffiliateLegacyMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_wallet_becomes_one_opening_balance_without_recalculation(): void
    {
        $user = User::factory()->create(['affiliate_request' => 1, 'password' => Hash::make('secret')]);
        $wallet_id = DB::table('affiliate_user_wallets')->insertGetId(['user_id' => $user->id, 'amount' => '12.34', 'created_at' => now(), 'updated_at' => now()]);

        $migration = require base_path('database/migrations/2026_08_06_000010_migrate_legacy_affiliate_data.php');
        $migration->up();

        $profile = AffiliateProfile::where('user_id', $user->id)->firstOrFail();
        $entry = AffiliateLedgerEntry::where('source_type', 'legacy_affiliate_user_wallets')->where('source_id', $wallet_id)->firstOrFail();
        $this->assertSame(AffiliateLedgerEntryType::OPENING_BALANCE, $entry->entry_type);
        $this->assertSame(1234, $entry->available_delta_minor);
        $this->assertSame('MIGRATED', $profile->migration_status);
    }
}
