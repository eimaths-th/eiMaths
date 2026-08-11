<?php

namespace Tests\Feature\Affiliate;

use App\Domain\Affiliate\Enums\AffiliateCommissionStatus;
use App\Domain\Affiliate\Exceptions\AffiliateDomainException;
use App\Domain\Affiliate\Models\AffiliateAttribution;
use App\Domain\Affiliate\Models\AffiliateCommission;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Domain\Affiliate\Services\AffiliateFinancialSourceGuard;
use App\Models\Contact;
use App\Models\Order;
use App\Models\OrderDetailList;
use App\Models\Receipt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AffiliateFinancialSourceLockTest extends TestCase
{
    use RefreshDatabase;

    public function test_locked_financial_fields_and_deletion_are_rejected_but_non_financial_metadata_is_allowed(): void
    {
        [$contact, $order, $receipt] = $this->createLockedSource();
        $guard = app(AffiliateFinancialSourceGuard::class);
        $guard->AssertOrderUpdateAllowed($order, ['detail' => 'Updated note']);
        $this->expectException(AffiliateDomainException::class);
        $guard->AssertOrderUpdateAllowed($order, ['discount' => '1.00']);
    }

    public function test_receipt_detail_replacement_and_contact_deletion_are_rejected(): void
    {
        [$contact, $order, $receipt] = $this->createLockedSource();
        $guard = app(AffiliateFinancialSourceGuard::class);
        $this->expectException(AffiliateDomainException::class);
        $guard->AssertReceiptUpdateAllowed($receipt, ['billing_details' => 'Updated address'], true);
    }

    private function createLockedSource(): array
    {
        $user = User::factory()->create(['password' => Hash::make('secret')]);
        $profile = AffiliateProfile::create(['user_id' => $user->id, 'referral_code' => 'AFF-LOCK-' . $user->id, 'status' => 'ACTIVE']);
        $contact = Contact::create(['name' => 'Student', 'centre' => 1]);
        $attribution = AffiliateAttribution::create(['affiliate_profile_id' => $profile->id, 'contact_id' => $contact->id, 'captured_at' => now()->subDay(), 'expires_at' => now()->addDays(179), 'source_context' => 'course_referral']);
        $order = Order::create(['cid' => $contact->id, 'status' => 1, 'discount' => '0.00']);
        $order->forceFill(['affiliate_attribution_id' => $attribution->id, 'affiliate_commission_rate_basis_points' => 2000])->save();
        OrderDetailList::create(['order_id' => $order->id, 'price' => '100.00']);
        $receipt = Receipt::create(['oid' => $order->id, 'cid' => $contact->id, 'receipt_number' => 'R-LOCK-' . $order->id, 'payment_date' => now()->toDateString(), 'status' => 1]);
        AffiliateCommission::create(['affiliate_profile_id' => $profile->id, 'affiliate_attribution_id' => $attribution->id, 'contact_id' => $contact->id, 'order_id' => $order->id, 'receipt_id' => $receipt->id, 'eligible_tuition_minor' => 10000, 'rate_basis_points' => 2000, 'commission_minor' => 2000, 'status' => AffiliateCommissionStatus::PENDING, 'release_at' => now()->addDays(10), 'source_key' => 'lock:commission:' . $order->id]);
        return [$contact, $order, $receipt];
    }
}
