<?php

namespace Tests\Feature\Affiliate;

use App\Domain\Affiliate\Models\AffiliateAttribution;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Domain\Affiliate\Services\AffiliateAttributionService;
use App\Domain\Affiliate\ValueObjects\AffiliateReferralCookie;
use App\Domain\Affiliate\ValueObjects\AffiliateClickContext;
use App\Models\Contact;
use App\Models\CoursePending;
use App\Models\Order;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class AffiliateOrderAttributionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('affiliate.features.referral_capture', true);
    }

    public function test_contact_binding_is_first_touch_and_order_snapshots_rate_from_server_config(): void
    {
        $profile = $this->createProfile('AFF-ORDER-001');
        $contact = Contact::create(['name' => 'Student', 'centre' => 1]);
        $captured_at = CarbonImmutable::parse('2026-08-01 00:00:00', 'UTC');
        $context = new AffiliateReferralCookie($profile->id, $captured_at, $captured_at->addDays(180));
        $service = app(AffiliateAttributionService::class);
        $attribution = $service->BindToContact($contact, $context, 'course_referral');
        $order = Order::create(['cid' => $contact->id, 'status' => 0, 'payment' => 0]);
        $service->SnapshotOrder($contact, $order);

        $this->assertSame($attribution->id, $order->fresh()->affiliate_attribution_id);
        $this->assertSame(1500, $order->fresh()->affiliate_commission_rate_basis_points);
        $this->assertSame($attribution->id, $service->BindToContact($contact, null)->id);
    }

    public function test_expired_attribution_is_not_snapshotted(): void
    {
        $profile = $this->createProfile('AFF-ORDER-002');
        $contact = Contact::create(['name' => 'Student', 'centre' => 1]);
        $attribution = AffiliateAttribution::create([
            'affiliate_profile_id' => $profile->id,
            'contact_id' => $contact->id,
            'captured_at' => now()->subDays(181),
            'expires_at' => now()->subDay(),
            'source_context' => 'course_referral',
        ]);
        $contact->forceFill(['current_affiliate_attribution_id' => $attribution->id])->save();
        $order = Order::create(['cid' => $contact->id, 'status' => 0, 'payment' => 0]);

        $this->assertNull(app(AffiliateAttributionService::class)->SnapshotOrder($contact, $order));
        $this->assertNull($order->fresh()->affiliate_attribution_id);
    }

    public function test_course_pending_bridge_binds_contact_without_an_admin_referral_cookie(): void
    {
        $profile = $this->createProfile('AFF-PENDING-BRIDGE-001');
        $captured_at = CarbonImmutable::parse('2026-08-06 02:00:00', 'UTC');
        $course_id = DB::table('courses')->insertGetId([
            'user_id' => $profile->user_id,
            'lang_id' => 1,
            'title' => 'Bridge Course',
            'slug' => 'bridge-course',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $pending = CoursePending::create([
            'name' => 'Parent',
            'email' => 'parent@example.test',
            'status' => '2',
            'course_id' => $course_id,
        ]);
        $pending->forceFill([
            'affiliate_profile_id' => $profile->id,
            'affiliate_captured_at' => $captured_at,
            'affiliate_expires_at' => $captured_at->addDays(180),
        ])->save();
        $contact = Contact::create(['name' => 'Student', 'centre' => 1]);

        $attribution = app(AffiliateAttributionService::class)->BindPendingToContact(
            $pending,
            $contact,
            'course_pending_new_student',
        );
        $order = Order::create(['cid' => $contact->id, 'status' => 0, 'payment' => 0]);
        app(AffiliateAttributionService::class)->SnapshotOrder($contact, $order);

        $this->assertNotNull($attribution);
        $this->assertSame($profile->id, $attribution->affiliate_profile_id);
        $this->assertSame('course_pending_new_student', $attribution->source_context);
        $this->assertSame($attribution->id, $order->fresh()->affiliate_attribution_id);
        $this->assertSame(1500, $order->fresh()->affiliate_commission_rate_basis_points);
    }

    public function test_pending_bridge_preserves_campaign_context(): void
    {
        $profile = $this->createProfile('AFF-PENDING-CONTEXT-001');
        $pending = CoursePending::create(['name' => 'Parent', 'email' => 'context@example.test', 'course_id' => 1]);
        $captured_at = CarbonImmutable::now('UTC');
        $cookie = new AffiliateReferralCookie(
            $profile->id,
            $captured_at,
            $captured_at->addDays(180),
            click_event_id: 11,
            content_id: 12,
            campaign_code: 'bridge-campaign',
            source_type: 'media',
            unique_window_key: (string) Str::ulid(),
            destination_target: '/courses/math',
        );

        $persisted = app(AffiliateAttributionService::class)->PersistPendingBridge($pending, $cookie);
        $this->assertSame('bridge-campaign', $persisted->affiliate_campaign_code);
        $this->assertSame(11, $persisted->affiliate_click_event_id);
        $this->assertSame('/courses/math', $persisted->affiliate_captured_destination);
    }

    private function createProfile(string $referral_code): AffiliateProfile
    {
        $user = User::factory()->create(['password' => Hash::make('secret')]);
        return AffiliateProfile::create(['user_id' => $user->id, 'referral_code' => $referral_code, 'status' => 'ACTIVE']);
    }
}
