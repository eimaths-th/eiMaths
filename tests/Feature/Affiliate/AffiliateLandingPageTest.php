<?php

namespace Tests\Feature\Affiliate;

use App\Domain\Affiliate\Models\AffiliateConfiguration;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        $response->assertSeeText('7 วันเต็ม');
        $response->assertSeeText('90 วัน');
        $response->assertSeeText('12.34 บาท');
    }
}
