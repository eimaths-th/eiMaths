<?php

namespace App\Console\Commands\Affiliate;

use App\Domain\Affiliate\Services\AffiliateAuditService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PruneAffiliateClickEventsCommand extends Command
{
    protected $signature = 'affiliate:prune-click-events {--batch=1000}';

    protected $description = 'Prune retained Affiliate click identity records without touching aggregate evidence.';

    public function handle(AffiliateAuditService $audit_service): int
    {
        $batch = (int) $this->option('batch');
        if ($batch < 1 || $batch > 10000) {
            $this->error('Batch must be between 1 and 10000.');
            return self::INVALID;
        }

        $started_at = microtime(true);
        $event_boundary = CarbonImmutable::now('UTC')->subMonthsNoOverflow(13)->format('Y-m-d H:i:s');
        $window_boundary = CarbonImmutable::now('UTC')->subHours(48)->format('Y-m-d H:i:s');
        $event_count = $this->DeleteEvents($event_boundary, $batch);
        $window_count = $this->DeleteWindows($window_boundary, $batch);

        $audit_service->EmitSignal('click.retention_completed', 'affiliate-retention-' . CarbonImmutable::now('UTC')->format('YmdHis'), [
            'operation' => 'click.retention',
            'outcome' => 'SUCCESS',
            'event_count' => $event_count,
            'window_count' => $window_count,
            'duration_ms' => (int) round((microtime(true) - $started_at) * 1000),
        ]);
        $this->info("Pruned {$event_count} click events and {$window_count} visitor windows.");

        return self::SUCCESS;
    }

    private function DeleteEvents(string $boundary, int $batch): int
    {
        $deleted = 0;
        do {
            $ids = DB::table('affiliate_click_events')
                ->where('clicked_at', '<', $boundary)
                ->orderBy('id')
                ->limit($batch)
                ->pluck('id')
                ->all();
            if ($ids === []) {
                break;
            }
            $deleted += DB::table('affiliate_click_events')->whereIn('id', $ids)->delete();
        } while (count($ids) === $batch);

        return $deleted;
    }

    private function DeleteWindows(string $boundary, int $batch): int
    {
        $deleted = 0;
        do {
            $ids = DB::table('affiliate_visitor_windows')
                ->where('last_seen_at', '<', $boundary)
                ->orderBy('id')
                ->limit($batch)
                ->pluck('id')
                ->all();
            if ($ids === []) {
                break;
            }
            $deleted += DB::table('affiliate_visitor_windows')->whereIn('id', $ids)->delete();
        } while (count($ids) === $batch);

        return $deleted;
    }
}
