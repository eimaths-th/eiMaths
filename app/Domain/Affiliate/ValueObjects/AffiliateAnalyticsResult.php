<?php

namespace App\Domain\Affiliate\ValueObjects;

use Carbon\CarbonImmutable;
use DateTimeInterface;

final readonly class AffiliateAnalyticsResult
{
    public function __construct(
        public AffiliateAnalyticsRange $range,
        public array $kpis,
        public array $daily_series,
        public array $recent_sales,
        public array $sales_pagination,
        public array $top_media,
        DateTimeInterface $generated_at,
    ) {
        $this->generated_at = CarbonImmutable::instance($generated_at);
    }

    public readonly CarbonImmutable $generated_at;

    public function ToArray(): array
    {
        return [
            'range' => $this->range->ToArray(),
            'kpis' => $this->kpis,
            'daily_series' => $this->daily_series,
            'recent_sales' => $this->recent_sales,
            'sales_pagination' => $this->sales_pagination,
            'top_media' => $this->top_media,
            'generated_at' => $this->generated_at->toISOString(),
        ];
    }
}
