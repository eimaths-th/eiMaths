<?php

namespace App\Console\Commands\Affiliate;

use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Domain\Affiliate\Services\AffiliateAuditService;
use App\Domain\Affiliate\ValueObjects\AffiliateAnalyticsRange;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

class RebuildAffiliateClickMetricsCommand extends Command
{
    protected $signature = 'affiliate:rebuild-click-metrics {affiliate_profile_id} {from} {to} {--confirm}';

    protected $description = 'Rebuild Affiliate daily click aggregates from retained raw events while tracking is disabled.';

    public function handle(AffiliateAuditService $audit_service): int
    {
        $profile_id = (int) $this->argument('affiliate_profile_id');
        $profile = AffiliateProfile::query()->find($profile_id);
        if ($profile === null) {
            $this->error('Affiliate profile was not found.');
            return self::FAILURE;
        }
        if (! (bool) $this->option('confirm')) {
            $this->error('Pass --confirm to rebuild aggregate evidence.');
            return self::FAILURE;
        }
        if ((bool) config('affiliate.features.click_tracking', false)) {
            $this->error('Disable click tracking before rebuilding aggregate evidence.');
            return self::FAILURE;
        }

        try {
            $range = AffiliateAnalyticsRange::FromStrings(
                (string) $this->argument('from'),
                (string) $this->argument('to'),
            );
        } catch (InvalidArgumentException $exception) {
            $this->error($exception->getMessage());
            return self::INVALID;
        }

        if (! $this->IsRetainedRange($profile_id, $range)) {
            $this->error('The requested range is outside retained raw click history.');
            return self::FAILURE;
        }

        $started_at = microtime(true);
        $rows_written = 0;
        foreach ($range->Dates() as $date) {
            $rows_written += $this->RebuildDay($profile_id, $date, $range);
        }
        $correlation_id = 'affiliate-metrics-rebuild-' . CarbonImmutable::now('UTC')->format('YmdHis');
        $audit_service->Record(
            'click.metrics.rebuild',
            'affiliate_profile',
            $profile_id,
            null,
            [],
            [
                'from' => $range->FromDate()->format('Y-m-d'),
                'to' => $range->ToDate()->format('Y-m-d'),
                'row_count' => $rows_written,
            ],
            null,
            'SUCCESS',
            null,
            $correlation_id,
        );
        $this->info("Rebuilt {$rows_written} Affiliate click metric rows.");

        return self::SUCCESS;
    }

    private function IsRetainedRange(int $profile_id, AffiliateAnalyticsRange $range): bool
    {
        $min_clicked_at = DB::table('affiliate_click_events')->where('affiliate_profile_id', $profile_id)->min('clicked_at');
        $max_clicked_at = DB::table('affiliate_click_events')->where('affiliate_profile_id', $profile_id)->max('clicked_at');
        if ($min_clicked_at === null || $max_clicked_at === null) {
            return false;
        }

        $minimum_local_date = CarbonImmutable::parse($min_clicked_at, 'UTC')->setTimezone('Asia/Bangkok')->format('Y-m-d');
        $maximum_local_date = CarbonImmutable::parse($max_clicked_at, 'UTC')->setTimezone('Asia/Bangkok')->format('Y-m-d');

        return $range->FromDate()->format('Y-m-d') >= $minimum_local_date
            && $range->ToDate()->format('Y-m-d') <= $maximum_local_date;
    }

    private function RebuildDay(int $profile_id, string $date, AffiliateAnalyticsRange $range): int
    {
        $day = CarbonImmutable::createFromFormat('!Y-m-d', $date, 'Asia/Bangkok');
        $from_utc = $day->setTimezone('UTC')->format('Y-m-d H:i:s');
        $to_utc = $day->addDay()->setTimezone('UTC')->format('Y-m-d H:i:s');

        return (int) DB::transaction(function () use ($profile_id, $date, $from_utc, $to_utc): int {
            DB::table('affiliate_click_daily_metrics')
                ->where('affiliate_profile_id', $profile_id)
                ->where('metric_date', $date)
                ->delete();
            $aggregates = DB::table('affiliate_click_events')
                ->where('affiliate_profile_id', $profile_id)
                ->where('clicked_at', '>=', $from_utc)
                ->where('clicked_at', '<', $to_utc)
                ->selectRaw("CASE WHEN source_type = 'direct' OR campaign_code IS NULL THEN 'direct' ELSE 'media:' || campaign_code END AS dimension_key, campaign_code, affiliate_content_id, COUNT(*) AS total_clicks, SUM(CASE WHEN is_unique = 1 THEN 1 ELSE 0 END) AS unique_clicks")
                ->groupBy('dimension_key', 'campaign_code', 'affiliate_content_id')
                ->get();
            foreach ($aggregates as $aggregate) {
                DB::table('affiliate_click_daily_metrics')->insert([
                    'affiliate_profile_id' => $profile_id,
                    'metric_date' => $date,
                    'dimension_key' => $aggregate->dimension_key,
                    'affiliate_content_id' => $aggregate->affiliate_content_id,
                    'campaign_code' => $aggregate->campaign_code,
                    'total_clicks' => (int) $aggregate->total_clicks,
                    'unique_clicks' => (int) $aggregate->unique_clicks,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return $aggregates->count();
        });
    }
}
