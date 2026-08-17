<?php

namespace Tests\Feature\Affiliate;

use App\Domain\Affiliate\Enums\AffiliateProfileStatus;
use App\Domain\Affiliate\Models\AffiliateClickDailyMetric;
use App\Domain\Affiliate\Models\AffiliateClickEvent;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AffiliateClickConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    private const SQLITE_SKIP_REASON = 'SQLite cannot verify deployed row-lock semantics.';

    #[Test]
    public function ConcurrentFirstClicksProduceTwoEventsOneUniqueWindowAndAnAtomicDailyTotal(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            $this->markTestSkipped(self::SQLITE_SKIP_REASON);
        }
        if (! filter_var(env('AFFILIATE_RUN_CONCURRENCY_TESTS', false), FILTER_VALIDATE_BOOLEAN)) {
            $this->markTestSkipped('Set AFFILIATE_RUN_CONCURRENCY_TESTS=true to run the MySQL concurrency check.');
        }

        $connection_name = DB::getDefaultConnection();
        $base_config = (array) config('database.connections.' . $connection_name);
        $database_name = (string) ($base_config['database'] ?? '');
        if ($database_name === '' || (string) env('AFFILIATE_CONCURRENCY_DATABASE') !== $database_name) {
            $this->markTestSkipped('Point AFFILIATE_CONCURRENCY_DATABASE at the isolated MySQL test database before opting in.');
        }

        config([
            'database.connections.affiliate_concurrency_a' => $base_config,
            'database.connections.affiliate_concurrency_b' => $base_config,
        ]);
        DB::purge('affiliate_concurrency_a');
        DB::purge('affiliate_concurrency_b');
        $connection_a = DB::connection('affiliate_concurrency_a');
        $connection_b = DB::connection('affiliate_concurrency_b');
        $profile = $this->CreateProfile();
        $visitor_hash = str_repeat('c', 64);
        $metric_date = CarbonImmutable::now('Asia/Bangkok')->format('Y-m-d');
        $window_key = (string) Str::ulid();
        $now = CarbonImmutable::now('UTC')->format('Y-m-d H:i:s');

        $worker_process = null;
        $worker_pipes = [];
        try {
            $connection_a->beginTransaction();
            $connection_a->table('affiliate_visitor_windows')->insert([
                'affiliate_profile_id' => $profile->id,
                'visitor_hash' => $visitor_hash,
                'unique_window_key' => $window_key,
                'last_unique_at' => $now,
                'last_seen_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $connection_a->table('affiliate_visitor_windows')->where('affiliate_profile_id', $profile->id)->where('visitor_hash', $visitor_hash)->lockForUpdate()->firstOrFail();
            $this->InsertEventAndMetric($connection_a, $profile->id, $visitor_hash, $window_key, $metric_date, $now, true);

            [$worker_process, $worker_pipes] = $this->StartWorker($profile->id, $visitor_hash, $metric_date, $base_config);
            $this->WaitForWorkerSignal($worker_pipes[1], 'started');
            $connection_a->commit();
            $this->WaitForWorkerSignal($worker_pipes[1], 'committed');
            $worker_output = stream_get_contents($worker_pipes[1]);
            $worker_exit_code = proc_close($worker_process);
            $worker_process = null;

            $this->assertSame(0, $worker_exit_code, $worker_output);
            $this->assertSame(2, AffiliateClickEvent::query()->where('affiliate_profile_id', $profile->id)->count());
            $this->assertSame(1, AffiliateClickEvent::query()->where('affiliate_profile_id', $profile->id)->where('is_unique', true)->count());
            $this->assertSame(2, AffiliateClickDailyMetric::query()->where('affiliate_profile_id', $profile->id)->value('total_clicks'));
            $this->assertSame(1, AffiliateClickDailyMetric::query()->where('affiliate_profile_id', $profile->id)->value('unique_clicks'));

            $event_count_before_rollback = AffiliateClickEvent::query()->where('affiliate_profile_id', $profile->id)->count();
            $metric_count_before_rollback = AffiliateClickDailyMetric::query()->where('affiliate_profile_id', $profile->id)->value('total_clicks');
            $connection_b->beginTransaction();
            try {
                $this->InsertEventAndMetric($connection_b, $profile->id, $visitor_hash, $window_key, $metric_date, $now, false);
                throw new \RuntimeException('rollback test');
            } catch (\RuntimeException) {
                $connection_b->rollBack();
            }
            $this->assertSame($event_count_before_rollback, AffiliateClickEvent::query()->where('affiliate_profile_id', $profile->id)->count());
            $this->assertSame($metric_count_before_rollback, AffiliateClickDailyMetric::query()->where('affiliate_profile_id', $profile->id)->value('total_clicks'));
        } finally {
            if ($connection_a->transactionLevel() > 0) $connection_a->rollBack();
            if ($connection_b->transactionLevel() > 0) $connection_b->rollBack();
            foreach ($worker_pipes as $worker_pipe) fclose($worker_pipe);
            if (is_resource($worker_process)) proc_terminate($worker_process);
        }
    }

    private function CreateProfile(): AffiliateProfile
    {
        $user = User::factory()->create();
        return AffiliateProfile::create([
            'user_id' => $user->id,
            'referral_code' => 'AFF-CONCURRENCY-' . $user->id,
            'status' => AffiliateProfileStatus::ACTIVE,
        ]);
    }

    private function InsertEventAndMetric($connection, int $profile_id, string $visitor_hash, string $window_key, string $metric_date, string $now, bool $is_unique): void
    {
        $connection->table('affiliate_click_events')->insert([
            'affiliate_profile_id' => $profile_id,
            'campaign_code' => null,
            'source_type' => 'direct',
            'destination_target' => '/',
            'visitor_hash' => $visitor_hash,
            'unique_window_key' => $window_key,
            'is_unique' => $is_unique,
            'clicked_at' => $now,
            'created_at' => $now,
        ]);
        $connection->table('affiliate_click_daily_metrics')->insertOrIgnore([
            'affiliate_profile_id' => $profile_id,
            'metric_date' => $metric_date,
            'dimension_key' => 'direct',
            'campaign_code' => null,
            'total_clicks' => 0,
            'unique_clicks' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $connection->table('affiliate_click_daily_metrics')->where('affiliate_profile_id', $profile_id)->where('metric_date', $metric_date)->where('dimension_key', 'direct')->update([
            'total_clicks' => DB::raw('total_clicks + 1'),
            'unique_clicks' => DB::raw('unique_clicks + ' . ($is_unique ? 1 : 0)),
            'updated_at' => $now,
        ]);
    }

    private function StartWorker(int $profile_id, string $visitor_hash, string $metric_date, array $database_config): array
    {
        $descriptor_specification = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
        $environment = [
            'AFFILIATE_CONCURRENCY_DB_HOST' => (string) ($database_config['host'] ?? '127.0.0.1'),
            'AFFILIATE_CONCURRENCY_DB_PORT' => (string) ($database_config['port'] ?? '3306'),
            'AFFILIATE_CONCURRENCY_DB_DATABASE' => (string) $database_config['database'],
            'AFFILIATE_CONCURRENCY_DB_USERNAME' => (string) ($database_config['username'] ?? ''),
            'AFFILIATE_CONCURRENCY_DB_PASSWORD' => (string) ($database_config['password'] ?? ''),
            'AFFILIATE_CONCURRENCY_PROFILE_ID' => (string) $profile_id,
            'AFFILIATE_CONCURRENCY_VISITOR_HASH' => $visitor_hash,
            'AFFILIATE_CONCURRENCY_METRIC_DATE' => $metric_date,
        ];
        $worker_path = base_path('tests/Feature/Affiliate/Fixtures/affiliate-concurrency-worker.php');
        $command = '"' . PHP_BINARY . '" "' . $worker_path . '"';
        $pipes = [];
        $process = proc_open($command, $descriptor_specification, $pipes, base_path(), $environment);
        if (! is_resource($process)) {
            $this->fail('The MySQL concurrency worker could not be started.');
        }
        stream_set_blocking($pipes[1], false);
        return [$process, $pipes];
    }

    private function WaitForWorkerSignal($output_pipe, string $expected_signal): void
    {
        $deadline = microtime(true) + 10;
        $output = '';
        while (microtime(true) < $deadline) {
            $output .= (string) stream_get_contents($output_pipe);
            if (str_contains($output, $expected_signal)) return;
            usleep(10000);
        }
        $this->fail('Timed out waiting for MySQL concurrency worker signal: ' . $expected_signal . '. Output: ' . $output);
    }
}
