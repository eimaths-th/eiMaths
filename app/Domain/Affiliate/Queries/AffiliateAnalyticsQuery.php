<?php

namespace App\Domain\Affiliate\Queries;

use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Domain\Affiliate\ValueObjects\AffiliateAnalyticsRange;
use App\Domain\Affiliate\ValueObjects\AffiliateAnalyticsResult;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class AffiliateAnalyticsQuery
{
    public function __construct(
        private readonly AffiliateActiveProfileQuery $active_profile_query,
        private readonly AffiliateClickAnalyticsQuery $click_query,
        private readonly AffiliateCommissionAnalyticsQuery $commission_query,
    ) {
    }

    public function ForUser(User $user, AffiliateAnalyticsRange $range, int $sales_page = 1): AffiliateAnalyticsResult
    {
        $profile = $this->active_profile_query->ForUser($user);
        return $this->ForProfile($profile, $range, $sales_page);
    }

    public function ForProfile(AffiliateProfile $profile, AffiliateAnalyticsRange $range, int $sales_page = 1): AffiliateAnalyticsResult
    {
        $generated_at = CarbonImmutable::now('UTC');
        $clicks = $this->click_query->ForProfile($profile, $range, $generated_at);
        $commissions = $this->commission_query->ForProfile($profile, $range, $sales_page);
        $daily = $this->MergeDailySeries($range, $clicks['daily'], $commissions['daily']);

        return new AffiliateAnalyticsResult(
            range: $range,
            kpis: [
                'total_clicks' => $clicks['total_clicks'],
                'unique_clicks' => $clicks['unique_clicks'],
                'confirmed_sales' => $commissions['confirmed_sales'],
                'eligible_sales_value_minor' => $commissions['eligible_sales_value_minor'],
                'net_commission_income_minor' => $commissions['net_commission_income_minor'],
                'conversion_rate' => $clicks['conversion_rate'],
                'conversion_denominator' => $clicks['conversion_denominator'],
                'conversion_numerator' => $clicks['conversion_numerator'],
            ],
            daily_series: $daily,
            recent_sales: $commissions['recent_sales'],
            sales_pagination: $commissions['sales_pagination'],
            top_media: $this->TopMedia($profile, $range),
            generated_at: $generated_at,
        );
    }

    private function MergeDailySeries(AffiliateAnalyticsRange $range, array $clicks, array $sales): array
    {
        $series = [];
        foreach ($range->Dates() as $date) {
            $series[$date] = [
                'metric_date' => $date,
                'total_clicks' => 0,
                'unique_clicks' => 0,
                'confirmed_sales' => 0,
                'net_commission_income_minor' => 0,
            ];
        }
        foreach ($clicks as $day) {
            if (isset($series[$day['metric_date']])) {
                $series[$day['metric_date']]['total_clicks'] = (int) $day['total_clicks'];
                $series[$day['metric_date']]['unique_clicks'] = (int) $day['unique_clicks'];
            }
        }
        foreach ($sales as $day) {
            if (isset($series[$day['metric_date']])) {
                $series[$day['metric_date']]['confirmed_sales'] = (int) $day['confirmed_sales'];
                $series[$day['metric_date']]['net_commission_income_minor'] = (int) $day['net_commission_income_minor'];
            }
        }

        return array_values($series);
    }

    private function TopMedia(AffiliateProfile $profile, AffiliateAnalyticsRange $range): array
    {
        $from_utc = $range->EventFromUtc()->format('Y-m-d H:i:s');
        $to_utc = $range->EventToUtcExclusive()->format('Y-m-d H:i:s');
        $from_local = $range->CommissionFromLocal()->format('Y-m-d H:i:s');
        $to_local = $range->CommissionToLocalExclusive()->format('Y-m-d H:i:s');

        $campaign_codes = DB::query()
            ->fromSub(
                DB::table('affiliate_click_events')
                    ->where('affiliate_profile_id', $profile->id)
                    ->whereNotNull('campaign_code')
                    ->where('clicked_at', '>=', $from_utc)
                    ->where('clicked_at', '<', $to_utc)
                    ->select('campaign_code')
                    ->distinct()
                    ->union(
                        DB::table('affiliate_attributions')
                            ->where('affiliate_profile_id', $profile->id)
                            ->whereNotNull('affiliate_campaign_code')
                            ->where('captured_at', '>=', $from_local)
                            ->where('captured_at', '<', $to_local)
                            ->selectRaw('affiliate_campaign_code AS campaign_code')
                            ->distinct(),
                    ),
                'campaign_codes',
            )
            ->pluck('campaign_code')
            ->filter()
            ->values();

        $click_rows = DB::table('affiliate_click_events')
            ->where('affiliate_profile_id', $profile->id)
            ->whereNotNull('campaign_code')
            ->where('clicked_at', '>=', $from_utc)
            ->where('clicked_at', '<', $to_utc)
            ->selectRaw('campaign_code, COUNT(*) AS total_clicks, SUM(CASE WHEN is_unique = 1 THEN 1 ELSE 0 END) AS unique_clicks')
            ->groupBy('campaign_code')
            ->get()
            ->keyBy('campaign_code');
        $sales_rows = DB::table('affiliate_commissions AS commissions')
            ->join('affiliate_attributions AS attributions', 'attributions.id', '=', 'commissions.affiliate_attribution_id')
            ->where('commissions.affiliate_profile_id', $profile->id)
            ->whereNotNull('commissions.receipt_id')
            ->whereNotNull('attributions.affiliate_campaign_code')
            ->where('commissions.created_at', '>=', $from_local)
            ->where('commissions.created_at', '<', $to_local)
            ->selectRaw('attributions.affiliate_campaign_code AS campaign_code, COUNT(*) AS confirmed_sales, SUM(commissions.commission_minor - commissions.reversed_minor) AS net_commission_income_minor')
            ->groupBy('attributions.affiliate_campaign_code')
            ->get()
            ->keyBy('campaign_code');
        $titles = DB::table('affiliate_contents')
            ->where('affiliate_contents.deleted_at', null)
            ->whereIn('campaign_code', $campaign_codes->all())
            ->pluck('title', 'campaign_code');

        $rows = [];
        foreach ($campaign_codes as $campaign_code) {
            $click = $click_rows->get($campaign_code);
            $sale = $sales_rows->get($campaign_code);
            $rows[] = [
                'campaign_code' => (string) $campaign_code,
                'title' => $titles->get($campaign_code),
                'total_clicks' => (int) ($click->total_clicks ?? 0),
                'unique_clicks' => (int) ($click->unique_clicks ?? 0),
                'confirmed_sales' => (int) ($sale->confirmed_sales ?? 0),
                'net_commission_income_minor' => (int) ($sale->net_commission_income_minor ?? 0),
            ];
        }

        $direct_clicks = DB::table('affiliate_click_events')
            ->where('affiliate_profile_id', $profile->id)
            ->whereNull('campaign_code')
            ->where('clicked_at', '>=', $from_utc)
            ->where('clicked_at', '<', $to_utc)
            ->selectRaw('COUNT(*) AS total_clicks, SUM(CASE WHEN is_unique = 1 THEN 1 ELSE 0 END) AS unique_clicks')
            ->first();
        $direct_sales = DB::table('affiliate_commissions AS commissions')
            ->leftJoin('affiliate_attributions AS attributions', 'attributions.id', '=', 'commissions.affiliate_attribution_id')
            ->where('commissions.affiliate_profile_id', $profile->id)
            ->whereNotNull('commissions.receipt_id')
            ->whereNull('attributions.affiliate_campaign_code')
            ->where('commissions.created_at', '>=', $from_local)
            ->where('commissions.created_at', '<', $to_local)
            ->selectRaw('COUNT(*) AS confirmed_sales, COALESCE(SUM(commissions.commission_minor - commissions.reversed_minor), 0) AS net_commission_income_minor')
            ->first();
        $direct_row = [
            'campaign_code' => 'DIRECT/UNKNOWN',
            'title' => null,
            'total_clicks' => (int) ($direct_clicks->total_clicks ?? 0),
            'unique_clicks' => (int) ($direct_clicks->unique_clicks ?? 0),
            'confirmed_sales' => (int) ($direct_sales->confirmed_sales ?? 0),
            'net_commission_income_minor' => (int) ($direct_sales->net_commission_income_minor ?? 0),
        ];
        if ($direct_row['total_clicks'] > 0 || $direct_row['confirmed_sales'] > 0 || $direct_row['net_commission_income_minor'] > 0) {
            $rows[] = $direct_row;
        }

        usort($rows, static function (array $left, array $right): int {
            return [$right['net_commission_income_minor'], $right['confirmed_sales'], $left['campaign_code']]
                <=> [$left['net_commission_income_minor'], $left['confirmed_sales'], $right['campaign_code']];
        });

        return array_slice($rows, 0, 10);
    }
}
