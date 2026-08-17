<?php

namespace Tests\Feature\Affiliate;

use App\Domain\Affiliate\Enums\AffiliateCommissionStatus;
use App\Domain\Affiliate\Enums\AffiliateProfileStatus;
use App\Domain\Affiliate\Models\AffiliateCommission;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Domain\Affiliate\Queries\AffiliateAnalyticsQuery;
use App\Domain\Affiliate\ValueObjects\AffiliateAnalyticsRange;
use App\Models\AffiliateContent;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AffiliateAnalyticsQueryTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_scoped_analytics_fills_days_and_calculates_reversals(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-08-04 12:00:00', 'Asia/Bangkok'));
        $owner = User::factory()->create(['password' => Hash::make('secret')]);
        $other_owner = User::factory()->create(['password' => Hash::make('secret')]);
        $profile = $this->CreateProfile($owner, 'AFF-ANALYTICS-OWNER');
        $other_profile = $this->CreateProfile($other_owner, 'AFF-ANALYTICS-OTHER');

        DB::table('affiliate_click_daily_metrics')->insert([
            [
                'affiliate_profile_id' => $profile->id,
                'metric_date' => '2026-08-01',
                'dimension_key' => 'media:spring-sale',
                'campaign_code' => 'spring-sale',
                'total_clicks' => 4,
                'unique_clicks' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'affiliate_profile_id' => $other_profile->id,
                'metric_date' => '2026-08-01',
                'dimension_key' => 'direct',
                'campaign_code' => null,
                'total_clicks' => 99,
                'unique_clicks' => 99,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $contact_id = DB::table('contacts')->insertGetId(['name' => 'Analytics student']);
        $event = DB::table('affiliate_click_events')->insertGetId([
            'affiliate_profile_id' => $profile->id,
            'campaign_code' => 'spring-sale',
            'source_type' => 'media',
            'destination_target' => '/courses',
            'visitor_hash' => str_repeat('a', 64),
            'unique_window_key' => '01J7Y0K3QK6J9Z4W6Z8W9F1F0A',
            'is_unique' => true,
            'clicked_at' => '2026-08-01 10:00:00',
            'created_at' => '2026-08-01 10:00:00',
        ]);
        $attribution_id = DB::table('affiliate_attributions')->insertGetId([
            'affiliate_profile_id' => $profile->id,
            'contact_id' => $contact_id,
            'captured_at' => '2026-08-01 10:00:00',
            'expires_at' => '2027-01-01 00:00:00',
            'source_context' => 'student_identified',
            'affiliate_click_event_id' => $event,
            'affiliate_campaign_code' => 'spring-sale',
            'affiliate_source_type' => 'media',
            'affiliate_unique_window_key' => '01J7Y0K3QK6J9Z4W6Z8W9F1F0A',
            'affiliate_captured_destination' => '/courses',
            'created_at' => '2026-08-01 10:00:00',
            'updated_at' => '2026-08-01 10:00:00',
        ]);
        $order_id = DB::table('orders')->insertGetId(['cid' => $contact_id]);
        $receipt_id = DB::table('receipts')->insertGetId(['cid' => $contact_id, 'oid' => $order_id]);
        $commission = AffiliateCommission::create([
            'affiliate_profile_id' => $profile->id,
            'affiliate_attribution_id' => $attribution_id,
            'contact_id' => $contact_id,
            'order_id' => $order_id,
            'receipt_id' => $receipt_id,
            'eligible_tuition_minor' => 10000,
            'rate_basis_points' => 1500,
            'commission_minor' => 1500,
            'reversed_minor' => 500,
            'status' => AffiliateCommissionStatus::AVAILABLE,
            'release_at' => '2026-08-11 00:00:00',
            'source_key' => 'test:analytics:commission',
        ]);
        $commission->forceFill([
            'created_at' => '2026-08-02 10:00:00',
            'updated_at' => '2026-08-02 10:00:00',
        ])->saveQuietly();
        DB::table('affiliate_commission_reversals')->insert([
            'affiliate_commission_id' => $commission->id,
            'external_refund_reference' => 'refund:test:analytics',
            'refunded_tuition_minor' => 3333,
            'reversal_minor' => 500,
            'idempotency_key' => 'idempotent:test:analytics',
            'reason' => 'test',
            'occurred_at' => '2026-08-03 10:00:00',
            'created_at' => '2026-08-03 10:00:00',
            'updated_at' => '2026-08-03 10:00:00',
        ]);

        $range = AffiliateAnalyticsRange::FromStrings('2026-08-01', '2026-08-03', CarbonImmutable::parse('2026-08-04', 'Asia/Bangkok'));
        $result = app(AffiliateAnalyticsQuery::class)->ForUser($owner, $range);

        $this->assertSame(4, $result->kpis['total_clicks']);
        $this->assertSame(2, $result->kpis['unique_clicks']);
        $this->assertSame(1, $result->kpis['confirmed_sales']);
        $this->assertSame(10000, $result->kpis['eligible_sales_value_minor']);
        $this->assertSame(1000, $result->kpis['net_commission_income_minor']);
        $this->assertCount(3, $result->daily_series);
        $this->assertSame(1500, $result->recent_sales[0]['commission_minor']);
        $this->assertSame(500, $result->recent_sales[0]['reversed_minor']);
        $this->assertSame('PARTIAL', $result->recent_sales[0]['reversal_status']);
        $this->assertSame(1, $result->sales_pagination['total']);
    }

    #[Test]
    public function CommissionAtTheExclusiveRangeEndIsNotCounted(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-08-04 12:00:00', 'Asia/Bangkok'));
        $owner = User::factory()->create(['password' => Hash::make('secret')]);
        $profile = $this->CreateProfile($owner, 'AFF-ANALYTICS-END');
        $contact_id = DB::table('contacts')->insertGetId(['name' => 'Boundary student']);
        $order_id = DB::table('orders')->insertGetId(['cid' => $contact_id]);
        $receipt_id = DB::table('receipts')->insertGetId(['cid' => $contact_id, 'oid' => $order_id]);
        $attribution_id = DB::table('affiliate_attributions')->insertGetId([
            'affiliate_profile_id' => $profile->id,
            'contact_id' => $contact_id,
            'captured_at' => '2026-08-01 00:00:00',
            'expires_at' => '2027-01-01 00:00:00',
            'source_context' => 'boundary-test',
            'created_at' => '2026-08-01 00:00:00',
            'updated_at' => '2026-08-01 00:00:00',
        ]);

        $commission = AffiliateCommission::create([
            'affiliate_profile_id' => $profile->id,
            'affiliate_attribution_id' => $attribution_id,
            'contact_id' => $contact_id,
            'order_id' => $order_id,
            'receipt_id' => $receipt_id,
            'eligible_tuition_minor' => 10000,
            'rate_basis_points' => 1500,
            'commission_minor' => 1500,
            'reversed_minor' => 0,
            'status' => AffiliateCommissionStatus::AVAILABLE,
            'release_at' => '2026-08-10 00:00:00',
            'source_key' => 'test:analytics:exclusive-end',
        ]);
        $commission->forceFill([
            'created_at' => '2026-08-04 00:00:00',
            'updated_at' => '2026-08-04 00:00:00',
        ])->saveQuietly();

        $range = AffiliateAnalyticsRange::FromStrings(
            '2026-08-01',
            '2026-08-03',
            CarbonImmutable::parse('2026-08-04', 'Asia/Bangkok'),
        );
        $result = app(AffiliateAnalyticsQuery::class)->ForUser($owner, $range);

        $this->assertSame(0, $result->kpis['confirmed_sales']);
        $this->assertSame(0, $result->kpis['eligible_sales_value_minor']);
    }

    #[Test]
    public function ArchivedMediaTitleRemainsAvailableForHistoricalAnalytics(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-08-04 12:00:00', 'Asia/Bangkok'));
        $owner = User::factory()->create(['password' => Hash::make('secret')]);
        $profile = $this->CreateProfile($owner, 'AFF-ANALYTICS-ARCHIVED');
        $content = AffiliateContent::create([
            'title' => 'Archived campaign title',
            'file_path' => 'uploads/affiliate/archived.webp',
            'type' => 'image',
            'description' => 'Historical media',
            'status' => 'enabled',
        ]);
        $content->forceFill(['campaign_code' => 'archived-campaign'])->save();
        DB::table('affiliate_contents')->where('id', $content->id)->update(['deleted_at' => '2026-08-02 00:00:00']);
        DB::table('affiliate_click_events')->insert([
            'affiliate_profile_id' => $profile->id,
            'affiliate_content_id' => $content->id,
            'campaign_code' => 'archived-campaign',
            'source_type' => 'media',
            'destination_target' => '/courses',
            'visitor_hash' => str_repeat('b', 64),
            'unique_window_key' => '01J7Y0K3QK6J9Z4W6Z8W9F1F0B',
            'is_unique' => true,
            'clicked_at' => '2026-08-02 10:00:00',
            'created_at' => '2026-08-02 10:00:00',
        ]);

        $range = AffiliateAnalyticsRange::FromStrings('2026-08-01', '2026-08-03', CarbonImmutable::parse('2026-08-04', 'Asia/Bangkok'));
        $result = app(AffiliateAnalyticsQuery::class)->ForUser($owner, $range);

        $archived_row = collect($result->top_media)->firstWhere('campaign_code', 'archived-campaign');
        $this->assertSame('Archived campaign title', $archived_row['title']);
    }

    private function CreateProfile(User $user, string $referral_code): AffiliateProfile
    {
        return AffiliateProfile::create([
            'user_id' => $user->id,
            'referral_code' => $referral_code,
            'status' => AffiliateProfileStatus::ACTIVE,
        ]);
    }
}
