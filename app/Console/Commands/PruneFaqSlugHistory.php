<?php

namespace App\Console\Commands;

use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class PruneFaqSlugHistory extends Command
{
    protected $signature = 'faq:prune-slug-history';
    protected $description = 'Delete expired FAQ URL aliases without changing current URLs or analytics';

    public function handle(): int
    {
        $deleted_count = 0;
        $cutoff = CarbonImmutable::now('UTC')->format('Y-m-d H:i:s.u');
        try {
            do {
                $route_ids = DB::table('faq_slug_routes')->whereNull('current_marker')
                    ->where('expires_at', '<=', $cutoff)->orderBy('expires_at')->orderBy('id')
                    ->limit(500)->pluck('id');
                if ($route_ids->isEmpty()) break;
                $deleted_count += DB::table('faq_slug_routes')->whereIn('id', $route_ids)
                    ->whereNull('current_marker')->where('expires_at', '<=', $cutoff)->delete();
            } while (true);
        } catch (\Throwable $exception) {
            Log::error('faq.urls.prune_failed', ['error_code' => 'FAQ_CONTENT_UNAVAILABLE',
                'exception_class' => get_class($exception), 'deleted_count' => $deleted_count]);
            $this->error('FAQ URL history cleanup failed. Retry after resolving the diagnostic.');
            return self::FAILURE;
        }
        $this->info('Deleted expired FAQ URL aliases: '.$deleted_count);
        return self::SUCCESS;
    }
}
