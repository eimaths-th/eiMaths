<?php

namespace Tests\Feature\Affiliate;

use App\Domain\Affiliate\Enums\AffiliateProfileStatus;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Domain\Affiliate\Services\AffiliateAttributionService;
use App\Domain\Affiliate\ValueObjects\AffiliateClickContext;
use App\Domain\Affiliate\ValueObjects\AffiliateReferralCookie;
use App\Http\Middleware\CaptureAffiliateReferral;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class AffiliateReferralCaptureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('affiliate.features.referral_capture', true);
    }

    public function test_first_valid_referral_creates_a_protected_context_and_cookie(): void
    {
        $profile = $this->createProfile(AffiliateProfileStatus::ACTIVE, 'AFF-FIRST-001');
        $request = Request::create('/courses?ref=AFF-FIRST-001', 'GET', ['ref' => 'AFF-FIRST-001']);

        $context = app(AffiliateAttributionService::class)->CaptureReferral($request);

        $this->assertSame($profile->id, $context->affiliate_profile_id);
        $this->assertTrue($context->expires_at->isAfter($context->captured_at));
        $this->assertTrue(collect(Cookie::getQueuedCookies())->contains(
            fn ($cookie): bool => $cookie->getName() === config('affiliate.cookie_name')
        ));
    }

    public function test_existing_unexpired_first_touch_is_not_replaced_and_expiry_is_exclusive(): void
    {
        $first = $this->createProfile(AffiliateProfileStatus::ACTIVE, 'AFF-FIRST-002');
        $second = $this->createProfile(AffiliateProfileStatus::ACTIVE, 'AFF-SECOND-002');
        $captured_at = now()->subDay()->toIso8601String();
        $expires_at = now()->addDays(179)->toIso8601String();
        $request = Request::create('/courses?ref=AFF-SECOND-002', 'GET', ['ref' => 'AFF-SECOND-002']);
        $request->cookies->set(config('affiliate.cookie_name'), json_encode([
            'affiliate_profile_id' => $first->id,
            'captured_at' => $captured_at,
            'expires_at' => $expires_at,
        ]));

        $context = app(AffiliateAttributionService::class)->CaptureReferral($request);

        $this->assertSame($first->id, $context->affiliate_profile_id);
        $expired_request = Request::create('/courses', 'GET');
        $expired_request->cookies->set(config('affiliate.cookie_name'), json_encode([
            'affiliate_profile_id' => $first->id,
            'captured_at' => now()->subDays(180)->toIso8601String(),
            'expires_at' => now()->toIso8601String(),
        ]));
        $this->assertNull(app(AffiliateAttributionService::class)->CaptureReferral($expired_request));
    }

    public function test_suspended_or_deactivated_profiles_are_not_captured(): void
    {
        $this->createProfile(AffiliateProfileStatus::SUSPENDED, 'AFF-SUSPENDED-001');
        $request = Request::create('/courses?ref=AFF-SUSPENDED-001', 'GET', ['ref' => 'AFF-SUSPENDED-001']);
        $this->assertNull(app(AffiliateAttributionService::class)->CaptureReferral($request));
    }

    public function test_old_cookie_shape_remains_readable(): void
    {
        $profile = $this->createProfile(AffiliateProfileStatus::ACTIVE, 'AFF-OLD-COOKIE');
        $cookie = app(AffiliateAttributionService::class)->ReadCookie(json_encode([
            'affiliate_profile_id' => $profile->id,
            'captured_at' => now()->subDay()->toIso8601String(),
            'expires_at' => now()->addDay()->toIso8601String(),
        ]));

        $this->assertInstanceOf(AffiliateReferralCookie::class, $cookie);
        $this->assertNull($cookie->click_event_id);
    }

    public function test_new_cookie_round_trips_click_context_and_malformed_context_is_rejected(): void
    {
        $cookie = new AffiliateReferralCookie(
            1,
            CarbonImmutable::now('UTC'),
            CarbonImmutable::now('UTC')->addDays(180),
            click_event_id: 4,
            content_id: 5,
            campaign_code: 'spring-campaign',
            source_type: 'media',
            unique_window_key: (string) Str::ulid(),
            destination_target: '/courses/math',
        );
        $round_trip = AffiliateReferralCookie::FromArray($cookie->ToArray());

        $this->assertSame(4, $round_trip->click_event_id);
        $this->assertSame('spring-campaign', $round_trip->campaign_code);
        $this->assertSame('/courses/math', $round_trip->destination_target);

        $this->expectException(\InvalidArgumentException::class);
        AffiliateReferralCookie::FromArray(array_merge($cookie->ToArray(), [
            'source_type' => 'media',
            'content_id' => null,
        ]));
    }

    public function test_later_click_context_does_not_replace_first_touch_cookie(): void
    {
        $first = $this->createProfile(AffiliateProfileStatus::ACTIVE, 'AFF-FIRST-CONTEXT');
        $second = $this->createProfile(AffiliateProfileStatus::ACTIVE, 'AFF-SECOND-CONTEXT');
        $first_cookie_time = \Carbon\CarbonImmutable::now('UTC');
        $first_cookie = new AffiliateReferralCookie(1, $first_cookie_time, $first_cookie_time->addDays(180));
        $request = Request::create('/r/AFF-SECOND-CONTEXT', 'GET');
        $request->cookies->set(config('affiliate.cookie_name'), json_encode($first_cookie->ToArray()));
        $context = new AffiliateClickContext(9, null, null, 'direct', (string) Str::ulid(), '/');

        $resolved = app(AffiliateAttributionService::class)->CaptureResolvedReferral($request, $second, $context);

        $this->assertSame(1, $resolved->affiliate_profile_id);
    }

    private function createProfile(AffiliateProfileStatus $status, string $referral_code): AffiliateProfile
    {
        $user = User::factory()->create(['password' => Hash::make('secret')]);
        return AffiliateProfile::create([
            'user_id' => $user->id,
            'referral_code' => $referral_code,
            'status' => $status,
        ]);
    }
}
