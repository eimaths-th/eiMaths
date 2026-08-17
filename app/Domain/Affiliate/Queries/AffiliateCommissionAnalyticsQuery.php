<?php

namespace App\Domain\Affiliate\Queries;

use App\Domain\Affiliate\Enums\AffiliateCommissionStatus;
use App\Domain\Affiliate\Models\AffiliateCommission;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Domain\Affiliate\ValueObjects\AffiliateAnalyticsRange;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AffiliateCommissionAnalyticsQuery
{
    public function ForProfile(
        AffiliateProfile $profile,
        AffiliateAnalyticsRange $range,
        int $sales_page = 1,
    ): array {
        $base = $this->BaseQuery($profile, $range);
        $aggregate = (clone $base)
            ->selectRaw('COUNT(*) AS confirmed_sales, COALESCE(SUM(eligible_tuition_minor), 0) AS eligible_sales_value_minor, COALESCE(SUM(commission_minor - reversed_minor), 0) AS net_commission_income_minor')
            ->first();

        $paginator = (clone $base)
            ->with(['AffiliateAttribution'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(25, ['*'], 'sales_page', max(1, $sales_page));

        return [
            'confirmed_sales' => (int) ($aggregate->confirmed_sales ?? 0),
            'eligible_sales_value_minor' => (int) ($aggregate->eligible_sales_value_minor ?? 0),
            'net_commission_income_minor' => (int) ($aggregate->net_commission_income_minor ?? 0),
            'daily' => $this->DailySeries($profile, $range),
            'recent_sales' => $this->MapSales($paginator->getCollection()),
            'sales_pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ];
    }

    public function CampaignAggregates(AffiliateProfile $profile, AffiliateAnalyticsRange $range): Collection
    {
        return $this->BaseQuery($profile, $range)
            ->whereHas('AffiliateAttribution', static function ($query): void {
                $query->whereNotNull('affiliate_campaign_code');
            })
            ->join('affiliate_attributions AS campaign_attributions', 'campaign_attributions.id', '=', 'affiliate_commissions.affiliate_attribution_id')
            ->selectRaw('campaign_attributions.affiliate_campaign_code AS campaign_code, COUNT(*) AS confirmed_sales, COALESCE(SUM(affiliate_commissions.commission_minor - affiliate_commissions.reversed_minor), 0) AS net_commission_income_minor')
            ->groupBy('campaign_attributions.affiliate_campaign_code')
            ->get();
    }

    public function DirectAggregate(AffiliateProfile $profile, AffiliateAnalyticsRange $range): object
    {
        return $this->BaseQuery($profile, $range)
            ->leftJoin('affiliate_attributions AS direct_attributions', 'direct_attributions.id', '=', 'affiliate_commissions.affiliate_attribution_id')
            ->whereNull('direct_attributions.affiliate_campaign_code')
            ->selectRaw('COUNT(*) AS confirmed_sales, COALESCE(SUM(affiliate_commissions.commission_minor - affiliate_commissions.reversed_minor), 0) AS net_commission_income_minor')
            ->first();
    }

    private function BaseQuery(AffiliateProfile $profile, AffiliateAnalyticsRange $range)
    {
        return AffiliateCommission::query()
            ->where('affiliate_commissions.affiliate_profile_id', $profile->id)
            ->whereNotNull('affiliate_commissions.receipt_id')
            ->where('affiliate_commissions.created_at', '>=', $range->CommissionFromLocal()->format('Y-m-d H:i:s'))
            ->where('affiliate_commissions.created_at', '<', $range->CommissionToLocalExclusive()->format('Y-m-d H:i:s'));
    }

    private function DailySeries(AffiliateProfile $profile, AffiliateAnalyticsRange $range): array
    {
        return $this->BaseQuery($profile, $range)
            ->selectRaw('DATE(affiliate_commissions.created_at) AS metric_date, COUNT(*) AS confirmed_sales, COALESCE(SUM(affiliate_commissions.commission_minor - affiliate_commissions.reversed_minor), 0) AS net_commission_income_minor')
            ->groupBy(DB::raw('DATE(affiliate_commissions.created_at)'))
            ->orderBy('metric_date')
            ->get()
            ->map(static fn ($row): array => [
                'metric_date' => (string) $row->metric_date,
                'confirmed_sales' => (int) $row->confirmed_sales,
                'net_commission_income_minor' => (int) $row->net_commission_income_minor,
            ])
            ->all();
    }

    private function MapSales(Collection $commissions): array
    {
        return $commissions->map(static function (AffiliateCommission $commission): array {
            $gross = (int) $commission->commission_minor;
            $reversed = (int) $commission->reversed_minor;
            $reversal_status = $reversed <= 0
                ? 'NONE'
                : ($reversed >= $gross ? 'FULL' : 'PARTIAL');

            return [
                'commission_id' => (int) $commission->id,
                'confirmed_at' => optional($commission->created_at)->toISOString(),
                'status' => $commission->status instanceof AffiliateCommissionStatus ? $commission->status->value : (string) $commission->status,
                'reversal_status' => $reversal_status,
                'eligible_tuition_minor' => (int) $commission->eligible_tuition_minor,
                'commission_minor' => $gross,
                'reversed_minor' => $reversed,
                'net_commission_minor' => $gross - $reversed,
                'campaign_code' => $commission->AffiliateAttribution?->affiliate_campaign_code,
            ];
        })->values()->all();
    }
}
