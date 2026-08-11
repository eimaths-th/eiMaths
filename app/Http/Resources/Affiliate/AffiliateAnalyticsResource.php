<?php

namespace App\Http\Resources\Affiliate;

use App\Domain\Affiliate\ValueObjects\AffiliateAnalyticsResult;
use App\Domain\Affiliate\ValueObjects\Money;
use Illuminate\Http\Resources\Json\JsonResource;

class AffiliateAnalyticsResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var AffiliateAnalyticsResult $result */
        $result = $this->resource;
        $kpis = $result->kpis;

        return [
            'range' => $result->range->ToArray(),
            'kpis' => [
                'total_clicks' => (int) ($kpis['total_clicks'] ?? 0),
                'unique_clicks' => (int) ($kpis['unique_clicks'] ?? 0),
                'confirmed_sales' => (int) ($kpis['confirmed_sales'] ?? 0),
                'eligible_sales_value' => Money::FromMinor((int) ($kpis['eligible_sales_value_minor'] ?? 0))->Decimal(),
                'net_commission_income' => Money::FromMinor((int) ($kpis['net_commission_income_minor'] ?? 0))->Decimal(),
                'conversion_rate' => (string) ($kpis['conversion_rate'] ?? '0.00'),
            ],
            'daily_series' => array_map(fn (array $day): array => [
                'metric_date' => $day['metric_date'],
                'total_clicks' => (int) ($day['total_clicks'] ?? 0),
                'unique_clicks' => (int) ($day['unique_clicks'] ?? 0),
                'confirmed_sales' => (int) ($day['confirmed_sales'] ?? 0),
                'net_commission_income' => Money::FromMinor((int) ($day['net_commission_income_minor'] ?? 0))->Decimal(),
            ], $result->daily_series),
            'recent_sales' => array_map(fn (array $sale): array => [
                'commission_id' => (int) $sale['commission_id'],
                'confirmed_at' => $sale['confirmed_at'],
                'status' => $sale['status'],
                'reversal_status' => $sale['reversal_status'],
                'eligible_sales_value' => Money::FromMinor((int) $sale['eligible_tuition_minor'])->Decimal(),
                'gross_commission' => Money::FromMinor((int) $sale['commission_minor'])->Decimal(),
                'reversed_commission' => Money::FromMinor((int) $sale['reversed_minor'])->Decimal(),
                'net_commission' => Money::FromMinor((int) $sale['net_commission_minor'])->Decimal(),
                'campaign_code' => $sale['campaign_code'],
            ], $result->recent_sales),
            'sales_pagination' => $result->sales_pagination,
            'top_media' => array_map(fn (array $media): array => [
                'campaign_code' => $media['campaign_code'],
                'title' => $media['title'],
                'total_clicks' => (int) $media['total_clicks'],
                'unique_clicks' => (int) $media['unique_clicks'],
                'confirmed_sales' => (int) $media['confirmed_sales'],
                'net_commission_income' => Money::FromMinor((int) $media['net_commission_income_minor'])->Decimal(),
            ], $result->top_media),
            'generated_at' => $result->generated_at->toISOString(),
        ];
    }
}
