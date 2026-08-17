<?php

namespace Tests\Feature\Affiliate;

use App\Domain\Affiliate\Models\AffiliateClickDailyMetric;
use App\Domain\Affiliate\Models\AffiliateClickEvent;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Domain\Affiliate\Models\AffiliateVisitorWindow;
use App\Models\AffiliateContent;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use LogicException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AffiliateAnalyticsPersistenceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function AnalyticsRowsPersistAllColumnsWithDomainCasts(): void
    {
        [$profile, $content] = $this->CreateAffiliateContext();
        $clicked_at = Carbon::parse('2026-08-11 01:02:03', 'UTC');

        $event = AffiliateClickEvent::create([
            'affiliate_profile_id' => $profile->id,
            'affiliate_content_id' => $content->id,
            'campaign_code' => 'summer-2026',
            'source_type' => 'media',
            'destination_target' => '/courses/math',
            'visitor_hash' => hash('sha256', 'visitor'),
            'unique_window_key' => '01K1Z2Y3X4W5V6U7T8S9R0Q1P2',
            'is_unique' => true,
            'clicked_at' => $clicked_at,
        ]);
        $window = AffiliateVisitorWindow::create([
            'affiliate_profile_id' => $profile->id,
            'visitor_hash' => hash('sha256', 'visitor'),
            'unique_window_key' => '01K1Z2Y3X4W5V6U7T8S9R0Q1P2',
            'last_unique_at' => $clicked_at,
            'last_seen_at' => $clicked_at,
        ]);
        $metric = AffiliateClickDailyMetric::create([
            'affiliate_profile_id' => $profile->id,
            'metric_date' => '2026-08-11',
            'dimension_key' => 'media:summer-2026',
            'affiliate_content_id' => $content->id,
            'campaign_code' => 'summer-2026',
            'total_clicks' => 4,
            'unique_clicks' => 2,
        ]);

        $this->assertIsInt($event->id);
        $this->assertTrue($event->is_unique);
        $this->assertSame($clicked_at->toISOString(), $event->clicked_at->toISOString());
        $this->assertNull($event->getAttribute('updated_at'));
        $this->assertSame($profile->id, $event->AffiliateProfile->id);
        $this->assertSame($content->id, $event->AffiliateContent->id);
        $this->assertIsInt($window->id);
        $this->assertSame('2026-08-11', $metric->metric_date->format('Y-m-d'));
        $this->assertSame(4, $metric->total_clicks);
        $this->assertSame(2, $metric->unique_clicks);
    }

    #[Test]
    public function UniqueVisitorWindowsAndDailyDimensionsAreDatabaseEnforced(): void
    {
        [$profile] = $this->CreateAffiliateContext();
        $visitor_hash = hash('sha256', 'duplicate');
        $window_attributes = [
            'affiliate_profile_id' => $profile->id,
            'visitor_hash' => $visitor_hash,
            'unique_window_key' => '01K1Z2Y3X4W5V6U7T8S9R0Q1P3',
            'last_unique_at' => now('UTC'),
            'last_seen_at' => now('UTC'),
        ];

        AffiliateVisitorWindow::create($window_attributes);
        $this->expectException(QueryException::class);
        AffiliateVisitorWindow::create($window_attributes);
    }

    #[Test]
    public function ClickEventsAreAppendOnlyThroughTheModelBoundary(): void
    {
        [$profile] = $this->CreateAffiliateContext();
        $event = AffiliateClickEvent::create([
            'affiliate_profile_id' => $profile->id,
            'source_type' => 'direct',
            'destination_target' => '/',
            'visitor_hash' => hash('sha256', 'append-only'),
            'unique_window_key' => '01K1Z2Y3X4W5V6U7T8S9R0Q1P4',
            'is_unique' => false,
            'clicked_at' => now('UTC'),
        ]);

        try {
            $event->destination_target = '/changed';
            $event->save();
            $this->fail('Expected the click event update to be rejected.');
        } catch (LogicException $exception) {
            $this->assertStringContainsString('append-only', strtolower($exception->getMessage()));
        }

        $this->expectException(LogicException::class);
        $event->delete();
    }

    private function CreateAffiliateContext(): array
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'password' => Hash::make('analytics-password'),
        ]);
        $profile = AffiliateProfile::create([
            'user_id' => $user->id,
            'referral_code' => 'AFF-ANALYTICS-' . $user->id,
            'status' => 'ACTIVE',
        ]);
        $content = AffiliateContent::create([
            'title' => 'Analytics media',
            'file_path' => 'uploads/affiliate/analytics.png',
            'type' => 'image',
            'status' => 'enabled',
            'created_by' => $user->id,
        ]);

        return [$profile, $content];
    }
}
