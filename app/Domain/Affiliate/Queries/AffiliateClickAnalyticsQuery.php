<?php

namespace App\Domain\Affiliate\Queries;

use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Domain\Affiliate\ValueObjects\AffiliateAnalyticsRange;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class AffiliateClickAnalyticsQuery
{
    public function ForProfile(
        AffiliateProfile $profile,
        AffiliateAnalyticsRange $range,
        ?CarbonImmutable $generated_at = null,
    ): array {
        $metric_totals = DB::table('affiliate_click_daily_metrics')
            ->where('affiliate_profile_id', $profile->id)
            ->whereBetween('metric_date', [$range->FromDate()->format('Y-m-d'), $range->ToDate()->format('Y-m-d')])
            ->selectRaw('COALESCE(SUM(total_clicks), 0) AS total_clicks, COALESCE(SUM(unique_clicks), 0) AS unique_clicks')
            ->first();

        $event_window_query = DB::table('affiliate_click_events')
            ->where('affiliate_profile_id', $profile->id)
            ->where('is_unique', true)
            ->where('clicked_at', '>=', $range->EventFromUtc()->format('Y-m-d H:i:s'))
            ->where('clicked_at', '<', $range->EventToUtcExclusive()->format('Y-m-d H:i:s'));
        $unique_window_count = (int) (clone $event_window_query)->distinct()->count('unique_window_key');
        $window_keys = (clone $event_window_query)->select('unique_window_key')->distinct();

        $generation_boundary = ($generated_at ?? CarbonImmutable::now('UTC'))->format('Y-m-d H:i:s');
        $converted_contact_count = (int) DB::table('affiliate_attributions AS attributions')
            ->where('attributions.affiliate_profile_id', $profile->id)
            ->whereNotNull('attributions.contact_id')
            ->whereIn('attributions.affiliate_unique_window_key', $window_keys)
            ->whereExists(function ($query) use ($generation_boundary): void {
                $query->selectRaw('1')
                    ->from('affiliate_commissions AS commissions')
                    ->whereColumn('commissions.affiliate_attribution_id', 'attributions.id')
                    ->whereNotNull('commissions.receipt_id')
                    ->where('commissions.created_at', '<=', $generation_boundary);
            })
            ->distinct()
            ->count('attributions.contact_id');

        $conversion_rate = $unique_window_count === 0
            ? '0.00'
            : number_format(($converted_contact_count / $unique_window_count) * 100, 2, '.', '');

        return [
            'total_clicks' => (int) ($metric_totals->total_clicks ?? 0),
            'unique_clicks' => (int) ($metric_totals->unique_clicks ?? 0),
            'conversion_denominator' => $unique_window_count,
            'conversion_numerator' => $converted_contact_count,
            'conversion_rate' => $conversion_rate,
            'daily' => $this->DailySeries($profile, $range),
        ];
    }

    private function DailySeries(AffiliateProfile $profile, AffiliateAnalyticsRange $range): array
    {
        return DB::table('affiliate_click_daily_metrics')
            ->where('affiliate_profile_id', $profile->id)
            ->whereBetween('metric_date', [$range->FromDate()->format('Y-m-d'), $range->ToDate()->format('Y-m-d')])
            ->selectRaw('metric_date, SUM(total_clicks) AS total_clicks, SUM(unique_clicks) AS unique_clicks')
            ->groupBy('metric_date')
            ->orderBy('metric_date')
            ->get()
            ->map(static function ($row): array {
                return [
                    'metric_date' => (string) $row->metric_date,
                    'total_clicks' => (int) $row->total_clicks,
                    'unique_clicks' => (int) $row->unique_clicks,
                ];
            })
            ->values()
            ->all();
    }
}
