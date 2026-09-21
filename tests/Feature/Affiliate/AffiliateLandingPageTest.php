<?php

namespace Tests\Feature\Affiliate;

use App\Domain\Affiliate\Models\AffiliateConfiguration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AffiliateLandingPageTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function LandingPageUsesThePublicFifteenPercentRateWithoutPilotNotice(): void
    {
        $response = $this->get(route('affiliate.index'));

        $response->assertOk();
        $response->assertSeeText('15%');
        $response->assertDontSeeText('Limited Pilot');
        $response->assertDontSeeText('ทดลองแบบจำกัด');
    }

    #[Test]
    public function LandingPageDisplaysTheCurrentAffiliatePolicy(): void
    {
        AffiliateConfiguration::query()
            ->where('key', 'commission_rate_basis_points')
            ->update(['value' => '1750']);
        AffiliateConfiguration::query()
            ->where('key', 'commission_holding_days')
            ->update(['value' => '7']);
        AffiliateConfiguration::query()
            ->where('key', 'attribution_duration_days')
            ->update(['value' => '90']);
        AffiliateConfiguration::query()
            ->where('key', 'minimum_withdrawal_minor')
            ->update(['value' => '1234']);

        $response = $this->get(route('affiliate.index'));

        $response->assertOk();
        $response->assertSeeText('17.5%');
        $response->assertSeeText('7 full days');
        $response->assertSeeText('90 days');
        $response->assertSeeText('12.34 baht');
    }

    #[Test]
    public function LandingPageDoesNotAdvertiseDisabledCapabilities(): void
    {
        Config::set('affiliate.features.analytics', false);
        Config::set('affiliate.features.media_library', false);

        $response = $this->get(route('affiliate.index'));

        $response->assertDontSeeText('Filtered click activity and confirmed receipt-backed sales and income analytics update near real time every 60 seconds.');
        $response->assertDontSeeText('Approved image and video assets, captions, downloads, and server-generated campaign links are available.');
        $response->assertSeeText('Do not treat unavailable analytics as confirmed evidence.');
    }

    #[Test]
    public function LandingPageAdvertisesOnlyEnabledAnalytics(): void
    {
        Config::set('affiliate.features.analytics', true);
        Config::set('affiliate.features.media_library', false);

        $response = $this->get(route('affiliate.index'));

        $response->assertSeeText('Filtered click activity and confirmed receipt-backed sales and income analytics update near real time every 60 seconds.');
        $response->assertDontSeeText('Approved image and video assets, captions, downloads, and server-generated campaign links are available.');
    }

    #[Test]
    public function LandingPageAdvertisesOnlyEnabledMedia(): void
    {
        Config::set('affiliate.features.analytics', false);
        Config::set('affiliate.features.media_library', true);

        $response = $this->get(route('affiliate.index'));

        $response->assertDontSeeText('Filtered click activity and confirmed receipt-backed sales and income analytics update near real time every 60 seconds.');
        $response->assertSeeText('Approved image and video assets, captions, downloads, and server-generated campaign links are available.');
    }

    #[Test]
    public function LandingPageAdvertisesBothEnabledCapabilities(): void
    {
        Config::set('affiliate.features.analytics', true);
        Config::set('affiliate.features.media_library', true);

        $response = $this->get(route('affiliate.index'));

        $response->assertSeeText('Filtered click activity and confirmed receipt-backed sales and income analytics update near real time every 60 seconds.');
        $response->assertSeeText('Approved image and video assets, captions, downloads, and server-generated campaign links are available.');
        $response->assertDontSeeText('Do not treat unavailable analytics as confirmed evidence.');
    }
}
