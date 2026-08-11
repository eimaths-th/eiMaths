<?php

namespace Tests\Feature\Affiliate;

use App\Domain\Affiliate\Enums\AffiliateProfileStatus;
use App\Domain\Affiliate\Models\AffiliateClickEvent;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AffiliateClickMetricRebuildTest extends TestCase
{
    use RefreshDatabase;

    public function test_rebuild_requires_confirmation_and_disabled_tracking(): void
    {
        $profile = $this->CreateProfile();
        Config::set('affiliate.features.click_tracking', true);
        $this->artisan('affiliate:rebuild-click-metrics', ['affiliate_profile_id' => $profile->id, 'from' => '2026-08-01', 'to' => '2026-08-01'])
            ->assertExitCode(1);

        Config::set('affiliate.features.click_tracking', false);
        $this->artisan('affiliate:rebuild-click-metrics', ['affiliate_profile_id' => $profile->id, 'from' => '2026-08-01', 'to' => '2026-08-01'])
            ->assertExitCode(1);
    }

    public function test_rebuild_replaces_daily_dimensions_idempotently(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-08-04 12:00:00', 'Asia/Bangkok'));
        Config::set('affiliate.features.click_tracking', false);
        $profile = $this->CreateProfile();
        AffiliateClickEvent::create([
            'affiliate_profile_id' => $profile->id,
            'campaign_code' => 'rebuild-campaign',
            'source_type' => 'media',
            'destination_target' => '/courses',
            'visitor_hash' => str_repeat('d', 64),
            'unique_window_key' => '01J7Y0K3QK6J9Z4W6Z8W9F1F0E',
            'is_unique' => true,
            'clicked_at' => '2026-08-01 04:00:00',
        ]);
        DB::table('affiliate_click_events')->where('unique_window_key', '01J7Y0K3QK6J9Z4W6Z8W9F1F0E')->update(['clicked_at' => '2026-08-01 04:00:00']);
        DB::table('affiliate_click_daily_metrics')->insert([
            'affiliate_profile_id' => $profile->id,
            'metric_date' => '2026-08-01',
            'dimension_key' => 'direct',
            'total_clicks' => 99,
            'unique_clicks' => 99,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->artisan('affiliate:rebuild-click-metrics', ['affiliate_profile_id' => $profile->id, 'from' => '2026-08-01', 'to' => '2026-08-01', '--confirm' => true])
            ->assertExitCode(0);
        $this->artisan('affiliate:rebuild-click-metrics', ['affiliate_profile_id' => $profile->id, 'from' => '2026-08-01', 'to' => '2026-08-01', '--confirm' => true])
            ->assertExitCode(0);

        $this->assertDatabaseHas('affiliate_click_daily_metrics', [
            'affiliate_profile_id' => $profile->id,
            'metric_date' => '2026-08-01',
            'dimension_key' => 'media:rebuild-campaign',
            'total_clicks' => 1,
            'unique_clicks' => 1,
        ]);
        $this->assertDatabaseMissing('affiliate_click_daily_metrics', [
            'affiliate_profile_id' => $profile->id,
            'dimension_key' => 'direct',
            'total_clicks' => 99,
        ]);
    }

    private function CreateProfile(): AffiliateProfile
    {
        $user = User::factory()->create(['password' => Hash::make('secret')]);

        return AffiliateProfile::create([
            'user_id' => $user->id,
            'referral_code' => 'AFF-REBUILD-' . fake()->unique()->numerify('####'),
            'status' => AffiliateProfileStatus::ACTIVE,
        ]);
    }
}
