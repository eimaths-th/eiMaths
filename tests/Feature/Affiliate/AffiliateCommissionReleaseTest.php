<?php

namespace Tests\Feature\Affiliate;

use App\Domain\Affiliate\Enums\AffiliateCommissionStatus;
use App\Domain\Affiliate\Models\AffiliateAttribution;
use App\Domain\Affiliate\Models\AffiliateCommission;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Domain\Affiliate\Services\AffiliateCommissionService;
use App\Domain\Affiliate\Models\AffiliateWalletBalance;
use App\Models\Contact;
use App\Models\Order;
use App\Models\Receipt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AffiliateCommissionReleaseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('affiliate.features.commission_release', true);
    }

    public function test_matured_pending_commission_releases_once_to_wallet(): void
    {
        $profile = $this->createProfile();
        $contact = Contact::create(['name' => 'Student', 'centre' => 1]);
        $attribution = AffiliateAttribution::create([
            'affiliate_profile_id' => $profile->id,
            'contact_id' => $contact->id,
            'captured_at' => now()->subDays(20),
            'expires_at' => now()->addDays(160),
            'source_context' => 'course_referral',
        ]);
        $order = Order::create(['cid' => $contact->id, 'status' => 1]);
        $receipt = Receipt::create(['oid' => $order->id, 'cid' => $contact->id, 'receipt_number' => 'R-REL', 'payment_date' => now()->toDateString(), 'status' => 1]);
        $commission = AffiliateCommission::create([
            'affiliate_profile_id' => $profile->id,
            'affiliate_attribution_id' => $attribution->id,
            'contact_id' => $contact->id,
            'order_id' => $order->id,
            'receipt_id' => $receipt->id,
            'eligible_tuition_minor' => 10000,
            'rate_basis_points' => 2000,
            'commission_minor' => 2000,
            'status' => AffiliateCommissionStatus::PENDING,
            'release_at' => now()->subSecond(),
            'source_key' => 'release:commission:1',
        ]);

        $this->assertSame(1, app(AffiliateCommissionService::class)->ReleaseMatured(100));
        $this->assertSame(AffiliateCommissionStatus::AVAILABLE, $commission->fresh()->status);
        $this->assertSame(2000, AffiliateWalletBalance::where('affiliate_profile_id', $profile->id)->value('available_balance_minor'));
        $this->assertSame(0, app(AffiliateCommissionService::class)->ReleaseMatured(100));
    }

    private function createProfile(): AffiliateProfile
    {
        $user = User::factory()->create(['password' => Hash::make('secret')]);
        return AffiliateProfile::create(['user_id' => $user->id, 'referral_code' => 'AFF-REL-' . $user->id, 'status' => 'ACTIVE']);
    }
}
