<?php

namespace Tests\Feature\Affiliate;

use App\Domain\Affiliate\Enums\AffiliateCommissionStatus;
use App\Domain\Affiliate\Models\AffiliateAttribution;
use App\Domain\Affiliate\Models\AffiliateCommission;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Domain\Affiliate\Services\AffiliateCommissionService;
use App\Models\Contact;
use App\Models\Order;
use App\Models\OrderDetailList;
use App\Models\Receipt;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AffiliateCommissionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('affiliate.features.commission_creation', true);
    }

    public function test_confirmed_payment_creates_one_pending_commission_and_source_snapshots(): void
    {
        [$order, $receipt] = $this->createPaidSource();
        $service = app(AffiliateCommissionService::class);
        $confirmed_at = CarbonImmutable::parse('2026-08-06 12:00:00', 'UTC');

        $commission = $service->CreateForConfirmedPayment($receipt, $confirmed_at, 'corr-commission-001');
        $retry = $service->CreateForConfirmedPayment($receipt, $confirmed_at, 'corr-commission-002');

        $this->assertNotNull($commission);
        $this->assertSame($commission->id, $retry->id);
        $this->assertSame(AffiliateCommissionStatus::PENDING, $commission->status);
        $this->assertSame(2000, $commission->commission_minor);
        $this->assertSame(2, $commission->SourceItems()->count());
        $this->assertSame(0, (int) DB::table('affiliate_ledger_entries')->count());
        $this->assertSame($order->id, $commission->order_id);
    }

    public function test_unpaid_or_unattributed_receipts_create_no_commission(): void
    {
        $contact = Contact::create(['name' => 'Student', 'centre' => 1]);
        $order = Order::create(['cid' => $contact->id, 'status' => 0, 'payment' => 0]);
        $receipt = Receipt::create(['oid' => $order->id, 'cid' => $contact->id, 'status' => 0]);

        $this->assertNull(app(AffiliateCommissionService::class)->CreateForConfirmedPayment($receipt, now(), 'corr-commission-003'));
        $this->assertSame(0, AffiliateCommission::count());
    }

    private function createPaidSource(): array
    {
        $user = User::factory()->create(['password' => Hash::make('secret')]);
        $profile = AffiliateProfile::create(['user_id' => $user->id, 'referral_code' => 'AFF-COMM-SVC-' . $user->id, 'status' => 'ACTIVE']);
        $contact = Contact::create(['name' => 'Student', 'centre' => 1]);
        $attribution = AffiliateAttribution::create([
            'affiliate_profile_id' => $profile->id,
            'contact_id' => $contact->id,
            'captured_at' => now()->subDay(),
            'expires_at' => now()->addDays(179),
            'source_context' => 'course_referral',
        ]);
        $contact->forceFill(['current_affiliate_attribution_id' => $attribution->id])->save();
        $order = Order::create(['cid' => $contact->id, 'status' => 1, 'payment' => 1, 'discount' => '0.01']);
        $order->forceFill(['affiliate_attribution_id' => $attribution->id, 'affiliate_commission_rate_basis_points' => 2000])->save();
        OrderDetailList::create(['order_id' => $order->id, 'price' => '100.01', 'bprice' => '900.00']);
        OrderDetailList::create(['order_id' => $order->id, 'price' => '0.02', 'bprice' => '800.00']);
        $receipt = Receipt::create([
            'oid' => $order->id,
            'cid' => $contact->id,
            'receipt_number' => 'RCP-COMM-' . $order->id,
            'payment_date' => now()->toDateString(),
            'status' => 1,
            'payment' => 1,
        ]);

        return [$order, $receipt];
    }
}
