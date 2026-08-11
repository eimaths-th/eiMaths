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

class AffiliateClickRetentionTest extends TestCase
{
    use RefreshDatabase;

    public function test_prune_uses_strict_thirteen_month_and_forty_eight_hour_boundaries(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-08-11 12:00:00', 'UTC'));
        $profile = $this->CreateProfile();
        $boundary_event = '2025-07-11 12:00:00';
        $old_event = '2025-07-11 11:59:59';
        $this->InsertEvent($profile->id, $boundary_event, '01J7Y0K3QK6J9Z4W6Z8W9F1F0A');
        $this->InsertEvent($profile->id, $old_event, '01J7Y0K3QK6J9Z4W6Z8W9F1F0B');
        DB::table('affiliate_visitor_windows')->insert([
            [
                'affiliate_profile_id' => $profile->id,
                'visitor_hash' => str_repeat('a', 64),
                'unique_window_key' => '01J7Y0K3QK6J9Z4W6Z8W9F1F0C',
                'last_unique_at' => '2026-08-09 11:59:59',
                'last_seen_at' => '2026-08-09 11:59:59',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'affiliate_profile_id' => $profile->id,
                'visitor_hash' => str_repeat('b', 64),
                'unique_window_key' => '01J7Y0K3QK6J9Z4W6Z8W9F1F0D',
                'last_unique_at' => '2026-08-09 12:00:00',
                'last_seen_at' => '2026-08-09 12:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->artisan('affiliate:prune-click-events', ['--batch' => 1])->assertExitCode(0);

        $this->assertDatabaseHas('affiliate_click_events', ['clicked_at' => $boundary_event]);
        $this->assertDatabaseMissing('affiliate_click_events', ['clicked_at' => $old_event]);
        $this->assertDatabaseMissing('affiliate_visitor_windows', ['visitor_hash' => str_repeat('a', 64)]);
        $this->assertDatabaseHas('affiliate_visitor_windows', ['visitor_hash' => str_repeat('b', 64)]);
    }

    public function test_invalid_batch_is_rejected(): void
    {
        $this->artisan('affiliate:prune-click-events', ['--batch' => 0])->assertExitCode(2);
    }

    private function CreateProfile(): AffiliateProfile
    {
        $user = User::factory()->create(['password' => Hash::make('secret')]);

        return AffiliateProfile::create([
            'user_id' => $user->id,
            'referral_code' => 'AFF-RETENTION-' . fake()->unique()->numerify('####'),
            'status' => AffiliateProfileStatus::ACTIVE,
        ]);
    }

    private function InsertEvent(int $profile_id, string $clicked_at, string $window_key): void
    {
        AffiliateClickEvent::create([
            'affiliate_profile_id' => $profile_id,
            'source_type' => 'direct',
            'destination_target' => '/',
            'visitor_hash' => str_repeat('c', 64),
            'unique_window_key' => $window_key,
            'is_unique' => true,
            'clicked_at' => $clicked_at,
        ]);
        DB::table('affiliate_click_events')->where('unique_window_key', $window_key)->update(['clicked_at' => $clicked_at]);
    }
}
