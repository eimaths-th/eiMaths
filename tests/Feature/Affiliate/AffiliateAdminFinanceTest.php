<?php

namespace Tests\Feature\Affiliate;

use App\Domain\Affiliate\Enums\AffiliateCommissionStatus;
use App\Domain\Affiliate\Enums\AffiliateLedgerEntryType;
use App\Domain\Affiliate\Models\AffiliateAttribution;
use App\Domain\Affiliate\Models\AffiliateCommission;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Domain\Affiliate\Models\AffiliateWalletBalance;
use App\Domain\Affiliate\Services\AffiliateCommissionService;
use App\Domain\Affiliate\Services\AffiliateReconciliationService;
use App\Domain\Affiliate\Services\AffiliateWalletService;
use App\Models\Contact;
use App\Models\Order;
use App\Models\Receipt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AffiliateAdminFinanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('affiliate.features.commission_release', true);
        Config::set('affiliate.features.confirmed_refunds', true);
    }

    public function test_pending_commission_can_be_held_and_released_with_audit(): void
    {
        [$profile, $commission] = $this->Commission();
        $actor = User::factory()->create(['password' => Hash::make('secret')]);
        $service = app(AffiliateCommissionService::class);

        $held = $service->Hold($commission, $actor, 'Manual evidence review', 'corr-hold-1');
        $this->assertSame(AffiliateCommissionStatus::HELD, $held->status);
        $released = $service->ReleaseHold($held, $actor, 'corr-hold-2');
        $this->assertSame(AffiliateCommissionStatus::PENDING, $released->status);
        $this->assertDatabaseHas('affiliate_audit_logs', ['operation' => 'commission.hold', 'subject_id' => $commission->id]);
    }

    public function test_reconciliation_detects_projection_mismatch(): void
    {
        [$profile] = $this->Commission();
        app(AffiliateWalletService::class)->ApplyEntry($profile, AffiliateLedgerEntryType::OPENING_BALANCE, 1000, 0, 'test', 1, 'reconcile-opening', null, 'OPENING_BALANCE', 'corr-rec-1');
        AffiliateWalletBalance::whereKey($profile->id)->update(['available_balance_minor' => 999]);

        $result = app(AffiliateReconciliationService::class)->ReconcileProfile($profile);
        $this->assertCount(1, $result['issues']);
        $this->assertSame('AVAILABLE_BALANCE_MISMATCH', $result['issues'][0]->issue_code);
    }

    public function test_reconciliation_uses_batch_as_chunk_size_and_covers_all_profiles(): void
    {
        $this->CreateReconciliationProfile('first');
        $this->CreateReconciliationProfile('second');
        $this->CreateReconciliationProfile('third');

        DB::enableQueryLog();
        try {
            $processed = app(AffiliateReconciliationService::class)->ReconcileAll(2);
            $queries = DB::getQueryLog();
        } finally {
            DB::disableQueryLog();
        }

        $profile_queries = collect($queries)->filter(
            fn (array $query): bool => str_contains(strtolower($query['query']), 'affiliate_profiles'),
        )->values();

        $this->assertSame(3, $processed);
        $this->assertCount(2, $profile_queries);
        $this->assertStringContainsString('limit 2', strtolower($profile_queries->first()['query']));
    }

    private function Commission(): array
    {
        $user = User::factory()->create(['password' => Hash::make('secret')]);
        $profile = AffiliateProfile::create(['user_id' => $user->id, 'referral_code' => 'AFF-ADMIN-' . $user->id, 'status' => 'ACTIVE']);
        $contact = Contact::create(['name' => 'Admin Student', 'centre' => 1]);
        $attribution = AffiliateAttribution::create(['affiliate_profile_id' => $profile->id, 'contact_id' => $contact->id, 'captured_at' => now()->subDay(), 'expires_at' => now()->addDays(179), 'source_context' => 'test']);
        $order = Order::create(['cid' => $contact->id, 'status' => 1]);
        $receipt = Receipt::create(['oid' => $order->id, 'cid' => $contact->id, 'receipt_number' => 'R-ADMIN-' . $user->id, 'payment_date' => now()->toDateString(), 'status' => 1]);
        $commission = AffiliateCommission::create(['affiliate_profile_id' => $profile->id, 'affiliate_attribution_id' => $attribution->id, 'contact_id' => $contact->id, 'order_id' => $order->id, 'receipt_id' => $receipt->id, 'eligible_tuition_minor' => 1000, 'rate_basis_points' => 2000, 'commission_minor' => 200, 'status' => AffiliateCommissionStatus::PENDING, 'release_at' => now()->addDay(), 'source_key' => 'admin-commission-' . $user->id]);
        return [$profile, $commission];
    }

    private function CreateReconciliationProfile(string $suffix): AffiliateProfile
    {
        $user = User::factory()->create(['password' => Hash::make('secret')]);

        return AffiliateProfile::create([
            'user_id' => $user->id,
            'referral_code' => 'AFF-RECON-' . $suffix . '-' . $user->id,
            'status' => 'ACTIVE',
        ]);
    }
}
