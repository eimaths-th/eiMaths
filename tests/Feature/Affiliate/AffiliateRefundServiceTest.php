<?php

namespace Tests\Feature\Affiliate;

use App\Domain\Affiliate\Enums\AffiliateCommissionStatus;
use App\Domain\Affiliate\Models\AffiliateAttribution;
use App\Domain\Affiliate\Models\AffiliateCommission;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Domain\Affiliate\Models\AffiliateWalletBalance;
use App\Domain\Affiliate\Services\AffiliateCommissionService;
use App\Models\Contact;
use App\Models\Order;
use App\Models\Receipt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AffiliateRefundServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('affiliate.features.confirmed_refunds', true);
    }

    public function test_external_refund_records_immutable_reversal_without_customer_payment(): void
    {
        [$profile, $commission, $receipt] = $this->createCommission(AffiliateCommissionStatus::AVAILABLE);
        $actor = User::factory()->create(['password' => Hash::make('secret')]);

        $reversal = app(AffiliateCommissionService::class)->RecordConfirmedExternalRefund(
            $receipt,
            '10.00',
            'EXT-REFUND-001',
            'Tuition refunded outside application',
            'refund-key-001',
            $actor,
            'corr-refund-001',
        );

        $this->assertSame(200, $reversal->reversal_minor);
        $this->assertSame(200, $commission->fresh()->reversed_minor);
        $this->assertSame(-200, AffiliateWalletBalance::where('affiliate_profile_id', $profile->id)->value('available_balance_minor'));
        $this->assertSame(1, (int) $receipt->fresh()->status);
    }

    public function test_repeated_external_refund_is_idempotent_and_over_refund_is_rejected(): void
    {
        [, $commission, $receipt] = $this->createCommission(AffiliateCommissionStatus::PENDING);
        $actor = User::factory()->create(['password' => Hash::make('secret')]);
        $service = app(AffiliateCommissionService::class);
        $first = $service->RecordConfirmedExternalRefund($receipt, '10.00', 'EXT-REFUND-002', 'Reason', 'refund-key-002', $actor, 'corr-refund-002');
        $second = $service->RecordConfirmedExternalRefund($receipt, '10.00', 'EXT-REFUND-002', 'Reason', 'refund-key-002', $actor, 'corr-refund-003');

        $this->assertSame($first->id, $second->id);
        $this->expectException(\App\Domain\Affiliate\Exceptions\AffiliateDomainException::class);
        $service->RecordConfirmedExternalRefund($receipt, '100.00', 'EXT-REFUND-003', 'Reason', 'refund-key-003', $actor, 'corr-refund-004');
        $this->assertSame(200, $commission->fresh()->reversed_minor);
    }

    private function createCommission(AffiliateCommissionStatus $status): array
    {
        $user = User::factory()->create(['password' => Hash::make('secret')]);
        $profile = AffiliateProfile::create(['user_id' => $user->id, 'referral_code' => 'AFF-REFUND-' . $user->id, 'status' => 'ACTIVE']);
        $contact = Contact::create(['name' => 'Student', 'centre' => 1]);
        $attribution = AffiliateAttribution::create(['affiliate_profile_id' => $profile->id, 'contact_id' => $contact->id, 'captured_at' => now()->subDay(), 'expires_at' => now()->addDays(179), 'source_context' => 'course_referral']);
        $order = Order::create(['cid' => $contact->id, 'status' => 1]);
        $order->forceFill(['affiliate_attribution_id' => $attribution->id, 'affiliate_commission_rate_basis_points' => 2000])->save();
        $receipt = Receipt::create(['oid' => $order->id, 'cid' => $contact->id, 'receipt_number' => 'R-REFUND-' . $order->id, 'payment_date' => now()->toDateString(), 'status' => 1]);
        $commission = AffiliateCommission::create(['affiliate_profile_id' => $profile->id, 'affiliate_attribution_id' => $attribution->id, 'contact_id' => $contact->id, 'order_id' => $order->id, 'receipt_id' => $receipt->id, 'eligible_tuition_minor' => 10000, 'rate_basis_points' => 2000, 'commission_minor' => 2000, 'status' => $status, 'release_at' => now()->subDay(), 'source_key' => 'refund:commission:' . $order->id]);
        return [$profile, $commission, $receipt];
    }
}
