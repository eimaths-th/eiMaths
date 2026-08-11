<?php

namespace Tests\Feature\Affiliate;

use App\Domain\Affiliate\Enums\AffiliateProfileStatus;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AffiliateAnalyticsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_renders_default_evidence_controls_and_accessible_table(): void
    {
        Config::set('affiliate.features.analytics', true);
        $user = User::factory()->create(['password' => Hash::make('secret')]);
        AffiliateProfile::create([
            'user_id' => $user->id,
            'referral_code' => 'AFF-ANALYTICS-PAGE',
            'status' => AffiliateProfileStatus::ACTIVE,
        ]);

        $response = $this->actingAs($user)->get(route('affiliate.analytics.index'));

        $response->assertOk()
            ->assertViewIs('affiliate.analytics.index')
            ->assertSee('data-affiliate-analytics-page', false)
            ->assertSee('type="application/json"', false)
            ->assertSee('7 days', false)
            ->assertSee('30 days', false)
            ->assertSee('90 days', false)
            ->assertSee('Daily evidence', false)
            ->assertSee('aria-live="polite"', false)
            ->assertSee('affiliate-analytics-page.js', false)
            ->assertSee('Chart.min.js', false);
    }
}
