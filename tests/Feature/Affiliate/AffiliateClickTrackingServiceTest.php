<?php

namespace Tests\Feature\Affiliate;

use App\Domain\Affiliate\Enums\AffiliateProfileStatus;
use App\Domain\Affiliate\Models\AffiliateClickDailyMetric;
use App\Domain\Affiliate\Models\AffiliateClickEvent;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Domain\Affiliate\Services\AffiliateClickTrackingService;
use App\Domain\Affiliate\ValueObjects\AffiliateTrackingTarget;
use App\Models\AffiliateContent;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AffiliateClickTrackingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('affiliate.features.click_tracking', true);
        Config::set('affiliate.visitor_hash_key', 'click-tracking-test-key-with-at-least-32-chars');
        Config::set('affiliate.allowed_destination_hosts', ['example.com']);
    }

    #[Test]
    public function SequentialAcceptedClicksAggregateAndAppendRawEvents(): void
    {
        $profile = $this->CreateProfile('AFF-CLICK-001');
        $service = app(AffiliateClickTrackingService::class);
        $target = $service->ResolveDirect($profile->referral_code, '/courses/math');
        $request = Request::create('/r/' . $profile->referral_code, 'GET', [], [], [], [
            'REMOTE_ADDR' => '203.0.113.40',
            'HTTP_USER_AGENT' => 'Test Browser',
        ]);

        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-08-11 02:00:00', 'UTC'));
        $first = $service->Record($request, $target, 'click-correlation');
        $second = $service->Record($request, $target, 'click-correlation');
        CarbonImmutable::setTestNow();

        $this->assertNotNull($first);
        $this->assertNotNull($second);
        $this->assertNotSame($first->click_event_id, $second->click_event_id);
        $this->assertSame(2, AffiliateClickEvent::query()->count());
        $this->assertSame(2, AffiliateClickDailyMetric::query()->sum('total_clicks'));
        $this->assertSame(1, AffiliateClickDailyMetric::query()->sum('unique_clicks'));
        $this->assertSame('direct', $first->source_type);
        $this->assertSame('/courses/math', $target->destination_target);
    }

    #[Test]
    public function MediaResolutionUsesEnabledPublishedContentDestinationAndCampaignDimension(): void
    {
        $profile = $this->CreateProfile('AFF-CLICK-002');
        $content = AffiliateContent::create([
            'title' => 'Published media',
            'file_path' => 'uploads/affiliate/media.png',
            'type' => 'image',
            'status' => 'enabled',
        ]);
        $content->forceFill([
            'destination_target' => 'https://example.com/landing',
            'campaign_code' => 'spring-campaign',
            'publish_starts_at' => '2026-08-01 00:00:00',
            'publish_ends_at' => '2026-08-31 23:59:59',
        ])->save();
        $service = app(AffiliateClickTrackingService::class);
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-08-11 02:00:00', 'UTC'));
        $target = $service->ResolveMedia($profile->referral_code, 'spring-campaign');
        CarbonImmutable::setTestNow();

        $this->assertInstanceOf(AffiliateTrackingTarget::class, $target);
        $this->assertSame('media', $target->source_type);
        $this->assertSame('spring-campaign', $target->campaign_code);
        $this->assertSame('https://example.com/landing', $target->destination_target);
    }

    #[Test]
    public function DisabledTrackingAndRejectedOwnerOrBotTrafficWriteNothing(): void
    {
        $profile = $this->CreateProfile('AFF-CLICK-003');
        $service = app(AffiliateClickTrackingService::class);
        $target = $service->ResolveDirect($profile->referral_code, '/');
        $request = Request::create('/r/' . $profile->referral_code, 'GET', [], [], [], [
            'REMOTE_ADDR' => '203.0.113.41',
            'HTTP_USER_AGENT' => 'KnownCrawler',
        ]);

        Config::set('affiliate.features.click_tracking', false);
        $this->assertNull($service->Record($request, $target, 'click-disabled'));
        Config::set('affiliate.features.click_tracking', true);
        Config::set('affiliate.abuse.known_bot_tokens', ['crawler']);
        $this->assertNull($service->Record($request, $target, 'click-bot'));
        $this->actingAs($profile->User);
        $request->server->set('HTTP_USER_AGENT', 'Human Browser');
        $this->assertNull($service->Record($request, $target, 'click-owner'));

        $this->assertDatabaseCount('affiliate_click_events', 0);
    }

    private function CreateProfile(string $referral_code): AffiliateProfile
    {
        $user = User::factory()->create(['password' => Hash::make('click-password')]);

        return AffiliateProfile::create([
            'user_id' => $user->id,
            'referral_code' => $referral_code,
            'status' => AffiliateProfileStatus::ACTIVE,
        ]);
    }
}
