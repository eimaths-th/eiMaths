<?php

namespace Tests\Feature\Affiliate;

use App\Domain\Affiliate\Enums\AffiliateCommissionStatus;
use App\Domain\Affiliate\Models\AffiliateAttribution;
use App\Domain\Affiliate\Models\AffiliateCommission;
use App\Domain\Affiliate\Models\AffiliateCommissionReversal;
use App\Domain\Affiliate\Models\AffiliateCommissionSourceItem;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Models\Contact;
use App\Models\Order;
use App\Models\OrderDetailList;
use App\Models\Receipt;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use LogicException;
use Tests\TestCase;

class AffiliateCommissionPersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_commission_source_and_reversal_evidence_persist_with_exact_casts(): void
    {
        [$profile, $attribution, $order, $receipt, $tuition] = $this->createSources();
        $commission = AffiliateCommission::create([
            'affiliate_profile_id' => $profile->id,
            'affiliate_attribution_id' => $attribution->id,
            'contact_id' => $attribution->contact_id,
            'order_id' => $order->id,
            'receipt_id' => $receipt->id,
            'eligible_tuition_minor' => 10000,
            'rate_basis_points' => 2000,
            'commission_minor' => 2000,
            'reversed_minor' => 0,
            'status' => AffiliateCommissionStatus::PENDING,
            'release_at' => now()->addDays(10),
            'source_key' => 'affiliate:commission:receipt:' . $receipt->id,
        ]);
        $source_item = AffiliateCommissionSourceItem::create([
            'affiliate_commission_id' => $commission->id,
            'order_detail_list_id' => $tuition->id,
            'tuition_minor' => 10000,
        ]);
        $reversal = AffiliateCommissionReversal::create([
            'affiliate_commission_id' => $commission->id,
            'external_refund_reference' => 'EXT-REF-001',
            'refunded_tuition_minor' => 1000,
            'reversal_minor' => 200,
            'idempotency_key' => 'refund:EXT-REF-001',
            'reason' => 'External refund recorded',
            'occurred_at' => now(),
        ]);

        $fresh = $commission->fresh();
        $this->assertSame(AffiliateCommissionStatus::PENDING, $fresh->status);
        $this->assertSame(10000, $fresh->eligible_tuition_minor);
        $this->assertSame($tuition->id, $fresh->SourceItems->first()->order_detail_list_id);
        $this->assertSame($commission->id, $source_item->fresh()->AffiliateCommission->id);
        $this->assertSame($commission->id, $reversal->fresh()->AffiliateCommission->id);
    }

    public function test_original_commission_sources_and_reversal_keys_are_unique(): void
    {
        [$profile, $attribution, $order, $receipt] = $this->createSources();
        $attributes = [
            'affiliate_profile_id' => $profile->id,
            'affiliate_attribution_id' => $attribution->id,
            'contact_id' => $attribution->contact_id,
            'order_id' => $order->id,
            'receipt_id' => $receipt->id,
            'eligible_tuition_minor' => 10000,
            'rate_basis_points' => 2000,
            'commission_minor' => 2000,
            'status' => AffiliateCommissionStatus::PENDING,
            'release_at' => now()->addDays(10),
            'source_key' => 'source-one',
        ];
        AffiliateCommission::create($attributes);

        $this->expectException(QueryException::class);
        AffiliateCommission::create($attributes + ['source_key' => 'source-two']);
    }

    public function test_financial_source_models_reject_immutable_updates_and_deletes(): void
    {
        [$profile, $attribution, $order, $receipt, $tuition] = $this->createSources();
        $commission = AffiliateCommission::create([
            'affiliate_profile_id' => $profile->id,
            'affiliate_attribution_id' => $attribution->id,
            'contact_id' => $attribution->contact_id,
            'order_id' => $order->id,
            'receipt_id' => $receipt->id,
            'eligible_tuition_minor' => 10000,
            'rate_basis_points' => 2000,
            'commission_minor' => 2000,
            'status' => AffiliateCommissionStatus::PENDING,
            'release_at' => now()->addDays(10),
            'source_key' => 'source-immutable',
        ]);

        $this->expectException(LogicException::class);
        $commission->commission_minor = 1;
        $commission->save();
    }

    public function test_referenced_order_cannot_be_deleted(): void
    {
        [, $attribution, $order, $receipt] = $this->createSources();
        AffiliateCommission::create([
            'affiliate_profile_id' => $attribution->affiliate_profile_id,
            'affiliate_attribution_id' => $attribution->id,
            'contact_id' => $attribution->contact_id,
            'order_id' => $order->id,
            'receipt_id' => $receipt->id,
            'eligible_tuition_minor' => 10000,
            'rate_basis_points' => 2000,
            'commission_minor' => 2000,
            'status' => AffiliateCommissionStatus::PENDING,
            'release_at' => now()->addDays(10),
            'source_key' => 'source-order-lock',
        ]);

        $this->expectException(QueryException::class);
        $order->delete();
    }

    private function createSources(): array
    {
        $user = User::factory()->create(['password' => Hash::make('secret')]);
        $profile = AffiliateProfile::create([
            'user_id' => $user->id,
            'referral_code' => 'AFF-SOURCE-' . $user->id,
            'status' => 'ACTIVE',
        ]);
        $contact = Contact::create(['name' => 'Student', 'centre' => 1]);
        $attribution = AffiliateAttribution::create([
            'affiliate_profile_id' => $profile->id,
            'contact_id' => $contact->id,
            'captured_at' => now(),
            'expires_at' => now()->addDays(180),
            'source_context' => 'course_referral',
        ]);
        $order = Order::create(['cid' => $contact->id, 'status' => 1, 'payment' => 1]);
        $tuition = OrderDetailList::create(['order_id' => $order->id, 'price' => 100.00]);
        $receipt = Receipt::create([
            'oid' => $order->id,
            'cid' => $contact->id,
            'receipt_number' => 'RCP-' . $order->id,
            'payment_date' => now()->toDateString(),
            'status' => 1,
            'payment' => 1,
        ]);

        return [$profile, $attribution, $order, $receipt, $tuition];
    }
}
