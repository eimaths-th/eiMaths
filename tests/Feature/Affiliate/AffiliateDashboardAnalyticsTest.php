<?php

namespace Tests\Feature\Affiliate;

use App\Domain\Affiliate\Enums\AffiliateProfileStatus;
use App\Domain\Affiliate\Models\AffiliateProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AffiliateDashboardAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_the_analytics_summary_only_when_enabled(): void
    {
        $user = User::factory()->create(['password' => Hash::make('secret')]);
        $user->assignRole(Role::findOrCreate('Affiliate-user', 'web'));
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
            ->assertSee('data-affiliate-analytics-json', false)
            ->assertSee('affiliate-analytics-page.js', false)
            ->assertSee('data-endpoint="' . route('affiliate.analytics.data') . '"', false)
            ->assertSee(route('affiliate.courses', ['ref' => 'AFF-DASHBOARD-ANALYTICS']), false)
            ->assertSee(route('affiliate.analytics.index'), false)
            ->assertSee('data-affiliate-copy-referral-code', false)
            ->assertSee('data-affiliate-copy-referral-link', false)
            ->assertSee(trans('affiliate.dashboard.copy_referral_code', [], 'th'), false);

        Config::set('affiliate.features.analytics', false);
        $hidden = $this->actingAs($user)->get(route('affiliate.dashboard'));
        $hidden->assertOk()->assertDontSee('data-affiliate-analytics-summary', false);
    }
}
