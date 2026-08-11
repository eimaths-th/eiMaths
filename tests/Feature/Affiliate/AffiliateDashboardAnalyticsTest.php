<?php

namespace Tests\Feature\Affiliate;

use App\Domain\Affiliate\Enums\AffiliateProfileStatus;
use App\Domain\Affiliate\Models\AffiliateProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Tests\TestCase;

class AffiliateDashboardAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_the_analytics_summary_only_when_enabled(): void
    {
        $user = User::factory()->create(['password' => Hash::make('secret')]);
        AffiliateProfile::create([
            'user_id' => $user->id,
            'referral_code' => 'AFF-DASHBOARD-ANALYTICS',
            'status' => AffiliateProfileStatus::ACTIVE,
        ]);
        Config::set('affiliate.features.wallet_withdrawals', true);
        Config::set('affiliate.features.analytics', true);

        $response = $this->actingAs($user)->get(route('affiliate.dashboard'));

        $response->assertOk()
            ->assertSee('data-affiliate-analytics-summary', false)
            ->assertSee(route('affiliate.tracking.direct', 'AFF-DASHBOARD-ANALYTICS'), false)
            ->assertSee(route('affiliate.analytics.index'), false);

        Config::set('affiliate.features.analytics', false);
        $hidden = $this->actingAs($user)->get(route('affiliate.dashboard'));
        $hidden->assertOk()->assertDontSee('data-affiliate-analytics-summary', false);
    }
}
