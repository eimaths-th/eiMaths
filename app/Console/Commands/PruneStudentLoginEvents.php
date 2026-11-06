<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class PruneStudentLoginEvents extends Command
{
    private const BATCH_SIZE = 1000;

    protected $signature = 'student-activity:prune-login-events';
    protected $description = 'Delete student login events older than the retention period';

    public function handle(): int
    {
        $cutoff = now()->subDays((int) config('student-activity.login_retention_days'));
        $deleted_count = 0;

        try {
            while (true) {
                $expired_event_ids = DB::table('student_login_events')
                    ->where('occurred_at', '<', $cutoff)
                    ->orderBy('id')
                    ->limit(self::BATCH_SIZE)
                    ->pluck('id');
                if ($expired_event_ids->isEmpty()) {
                    break;
                }
                $deleted_count += DB::table('student_login_events')->whereIn('id', $expired_event_ids)->delete();
            }
        } catch (\Throwable) {
            Log::error('student_activity.login_events_prune_failed', [
                'error_code' => 'STUDENT_LOGIN_EVENT_PRUNE_FAILED',
            ]);

            return self::FAILURE;
        }

        Log::info('student_activity.login_events_pruned', ['deleted_count' => $deleted_count]);

        return self::SUCCESS;
    }
}
