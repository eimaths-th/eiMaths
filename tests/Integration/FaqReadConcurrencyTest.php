<?php
namespace Tests\Integration;
use App\Services\Faq\FaqReadRecorder;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Process\Process;
use Tests\TestCase;
final class FaqReadConcurrencyTest extends TestCase
{
    public function createApplication(): \Illuminate\Foundation\Application
    {
        if (is_file(dirname(__DIR__, 2).'/bootstrap/cache/config.php')) {
            throw new \RuntimeException('Refusing to bootstrap concurrency tests with cached configuration.');
        }
        return parent::createApplication();
    }
    private function ConfigureConnection(): void
    {
        $database_name = getenv('FAQ_READ_TEST_DATABASE');
        $this->assertSame('true', getenv('FAQ_READ_TEST_ALLOW_SCHEMA'), 'Dedicated test schema opt-in required.');
        $this->assertIsString($database_name);
        $this->assertTrue(preg_match('/^faq_read_test_[a-z0-9_]+$/D', $database_name) === 1, 'Invalid dedicated test database name.');
        $this->assertFileDoesNotExist(base_path('bootstrap/cache/config.php'));
        foreach (['HOST', 'PORT', 'USERNAME', 'PASSWORD'] as $suffix) {
            $this->assertNotFalse(getenv('FAQ_READ_TEST_'.$suffix), 'Dedicated test connection variable missing.');
        }
        foreach (['HOST', 'PORT', 'USERNAME'] as $suffix) {
            $this->assertNotSame('', trim(getenv('FAQ_READ_TEST_'.$suffix)), 'Dedicated connection field must not be empty.');
        }
        $this->assertNotSame('root', strtolower(trim(getenv('FAQ_READ_TEST_USERNAME'))), 'Use a dedicated test-only account.');
        $this->assertTrue(preg_match('/^[1-9][0-9]{0,4}$/D', getenv('FAQ_READ_TEST_PORT')) === 1, 'Invalid dedicated port.');
        $this->assertTrue((int) getenv('FAQ_READ_TEST_PORT') <= 65535, 'Dedicated port out of range.');
        config(['database.connections.faq_read_test' => [
            'driver' => 'mysql', 'host' => getenv('FAQ_READ_TEST_HOST'), 'port' => getenv('FAQ_READ_TEST_PORT'),
            'database' => $database_name, 'username' => getenv('FAQ_READ_TEST_USERNAME'),
            'password' => getenv('FAQ_READ_TEST_PASSWORD'), 'unix_socket' => '', 'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci', 'prefix' => '', 'prefix_indexes' => true, 'strict' => true, 'engine' => 'InnoDB',
        ]]);
        DB::purge('faq_read_test');
        DB::setDefaultConnection('faq_read_test');
        \Illuminate\Support\Facades\Schema::clearResolvedInstance('db.schema');
    }
    private function RunWorkers(int $faq_id, array $event_keys, string $now, string $mode = 'read'): void
    {
        $barrier_directory = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR
            .'faq-read-barrier-'.bin2hex(random_bytes(16));
        $this->assertDirectoryDoesNotExist($barrier_directory);
        $this->assertTrue(mkdir($barrier_directory, 0700));
        $barrier_path = $barrier_directory.DIRECTORY_SEPARATOR.'go';
        $connection_environment = [];
        foreach (['DATABASE', 'HOST', 'PORT', 'USERNAME', 'PASSWORD', 'ALLOW_SCHEMA'] as $suffix) {
            $connection_environment['FAQ_READ_TEST_'.$suffix] = getenv('FAQ_READ_TEST_'.$suffix);
        }
        $processes = [];
        $ready_paths = [];
        $started_at = microtime(true);
        try {
            foreach ($event_keys as $worker_number => $event_key) {
                $ready_path = $barrier_directory.DIRECTORY_SEPARATOR.'ready-'.$worker_number;
                $ready_paths[] = $ready_path;
                $process = new Process([PHP_BINARY, 'vendor/bin/phpunit', '--do-not-cache-result',
                    '--filter', 'FaqReadConcurrencyTest::Worker', 'tests/Integration/FaqReadConcurrencyTest.php'],
                    base_path(), array_merge($connection_environment, ['FAQ_READ_WORKER_ID' => (string) $faq_id, 'FAQ_READ_WORKER_KEY' => $event_key,
                        'FAQ_READ_WORKER_NOW' => $now, 'FAQ_READ_WORKER_MODE' => $mode, 'FAQ_READ_WORKER_BARRIER' => $barrier_path,
                        'FAQ_READ_WORKER_READY' => $ready_path]), null, 30);
                $processes[] = $process;
                $process->start();
            }
            $deadline = microtime(true) + 20;
            while (count(array_filter($ready_paths, 'is_file')) !== count($ready_paths)) {
                clearstatcache();
                foreach ($processes as $worker_number => $process) {
                    $process->checkTimeout();
                    if (!$process->isRunning() && !$process->isSuccessful()) {
                        $this->WorkerFailure($process, $worker_number, 'before barrier');
                    }
                }
                $this->assertLessThan($deadline, microtime(true), 'Workers failed to become ready.');
                usleep(10000);
            }
            file_put_contents($barrier_path, 'go');
            foreach ($processes as $worker_number => $process) {
                if ($process->wait() !== 0) { $this->WorkerFailure($process, $worker_number, 'execution'); }
            }
            fwrite(STDERR, json_encode(['operation' => 'faq.test.workers', 'mode' => $mode,
                'worker_count' => count($processes), 'successful_workers' => count($processes),
                'elapsed_seconds' => round(microtime(true) - $started_at, 3)], JSON_THROW_ON_ERROR).PHP_EOL);
        } finally {
            foreach ($processes as $process) { if ($process->isRunning()) $process->stop(1); }
            foreach (array_merge($ready_paths, [$barrier_path]) as $owned_path) {
                if (is_file($owned_path)) unlink($owned_path);
            }
            $this->assertSame(realpath(sys_get_temp_dir()), realpath(dirname($barrier_directory)));
            $this->assertMatchesRegularExpression('/^faq-read-barrier-[a-f0-9]{32}$/D', basename($barrier_directory));
            rmdir($barrier_directory);
        }
    }
    #[Test] public function ConcurrentReceipts(): void
    {
        $original_connection = config('database.default');
        $applied_migrations = [];
        try {
            $this->ConfigureConnection();
            $schema = DB::connection()->getSchemaBuilder();
            $this->assertTrue($schema->getTableListing() === [] && $schema->getViews() === [], 'Test database must be empty and exclusive, including views.');
            $runtime = DB::selectOne('SELECT VERSION() AS version, @@SESSION.sql_mode AS sql_mode');
            $database_version = $runtime->version;
            $this->assertTrue(str_contains($runtime->sql_mode, 'STRICT_TRANS_TABLES') || str_contains($runtime->sql_mode, 'STRICT_ALL_TABLES'), 'Strict numeric enforcement is required.');
            $isolation_rows = DB::select("SHOW SESSION VARIABLES WHERE Variable_name IN ('transaction_isolation', 'tx_isolation')");
            $isolation_level = collect($isolation_rows)->pluck('Value')->first();
            $this->assertTrue(is_string($isolation_level) && $isolation_level !== '', 'Isolation level must be reported.');
            fwrite(STDERR, json_encode(['operation' => 'faq.test.environment', 'database_version' => $database_version,
                'isolation_level' => $isolation_level, 'sql_mode' => $runtime->sql_mode, 'duplicate_rounds' => 10, 'workers_per_round' => 8], JSON_THROW_ON_ERROR).PHP_EOL);
            foreach (['2026_05_14_000000_create_faqs_table.php', '2026_08_27_000000_add_categories_and_english_to_faqs.php',
                '2026_10_20_000001_add_slug_to_faqs.php', '2026_10_20_000002_create_faq_read_analytics_tables.php'] as $migration_file) {
                $migration = require database_path('migrations/'.$migration_file);
                $migration->up();
                $applied_migrations[] = $migration;
            }
            $table_status = DB::select('SHOW TABLE STATUS');
            $this->assertCount(4, $table_status);
            foreach ($table_status as $table) { $this->assertSame('InnoDB', $table->Engine); }
            $category_id = DB::table('faq_categories')->value('id');
            $faq_id = DB::table('faqs')->insertGetId(['category_id' => $category_id, 'question' => 'Concurrent question',
                'question_en' => 'Concurrent question', 'answer' => 'Answer', 'is_published' => true]);
            for ($round = 1; $round <= 10; $round++) {
                $event_key = str_pad(dechex($round), 64, '0', STR_PAD_LEFT);
                $this->RunWorkers($faq_id, array_fill(0, 8, $event_key), '2026-10-20T16:59:59Z');
            }
            $this->assertSame(10, DB::table('faq_read_receipts')->count());
            $this->assertSame(10, (int) DB::table('faq_daily_reads')->sum('read_count'));
            $independent_keys = array_map(fn ($number): string => str_pad(dechex($number), 64, '0', STR_PAD_LEFT), range(100, 107));
            $this->RunWorkers($faq_id, $independent_keys, '2026-10-20T16:59:59Z');
            $this->RunWorkers($faq_id, array_fill(0, 8, str_pad('1', 64, '0', STR_PAD_LEFT)), '2026-10-20T17:00:01Z');
            $this->assertSame(18, DB::table('faq_read_receipts')->count());
            $this->assertSame(18, (int) DB::table('faq_daily_reads')->sum('read_count'));
            $this->assertSame(1, DB::table('faq_daily_reads')->count());
            $this->RunWorkers($faq_id, [str_repeat('f', 64)], '2026-10-20T17:00:01Z');
            $this->assertSame(1, (int) DB::table('faq_daily_reads')->where('read_date', '2026-10-21')->value('read_count'));
            $this->RunWorkers($faq_id, array_fill(0, 8, str_repeat('e', 64)), '2026-10-21T01:00:00Z', 'slug');
            $slugs = DB::table('faqs')->whereNotNull('slug')->pluck('slug');
            $this->assertCount(8, $slugs);
            $this->assertCount(8, $slugs->unique());
            $this->assertContains('concurrent-slug-question', $slugs->all());
            $this->assertSame(0, $slugs->filter(static fn ($slug): bool => $slug === '')->count());
            $this->assertSame(8, DB::table('faqs')->whereNotNull('slug')->where('question', 'Concurrent slug question')
                ->where('question_en', 'Concurrent slug question')->where('answer', 'Answer')->where('is_published', true)->count());
            $daily_id = DB::table('faq_daily_reads')->value('id');
            $negative_rejected = false;
            try { DB::table('faq_daily_reads')->where('id', $daily_id)->update(['read_count' => -1]); }
            catch (\Illuminate\Database\QueryException $exception) { $negative_rejected = ($exception->errorInfo[0] ?? null) === '22003' && (int) ($exception->errorInfo[1] ?? 0) === 1264; }
            $this->assertTrue($negative_rejected, 'The intended engine must reject negative counts.');
            $deletion_rejected = false;
            try { DB::table('faq_daily_reads')->where('id', $daily_id)->delete(); }
            catch (\Illuminate\Database\QueryException $exception) { $deletion_rejected = ($exception->errorInfo[0] ?? null) === '23000' && (int) ($exception->errorInfo[1] ?? 0) === 1451; }
            $this->assertTrue($deletion_rejected, 'A referenced aggregate must not be deleted.');
            $this->assertSame(19, DB::table('faq_read_receipts')->count());
            $this->assertSame(19, (int) DB::table('faq_daily_reads')->sum('read_count'));
        } catch (\Throwable $exception) {
            $this->FailSafely($exception, 'parent');
        } finally {
            try {
                foreach (array_reverse($applied_migrations) as $migration) { $migration->down(); }
            } catch (\Throwable $exception) {
                $this->FailSafely($exception, 'owned-schema cleanup; inspect the four named migration objects before reuse');
            } finally {
                DB::disconnect('faq_read_test');
                DB::setDefaultConnection($original_connection);
                \Illuminate\Support\Facades\Schema::clearResolvedInstance('db.schema');
            }
        }
    }
    #[Test] public function Worker(): void
    {
        $this->assertNotFalse(getenv('FAQ_READ_WORKER_KEY'), 'Run the ConcurrentReceipts entry point, not Worker directly.');
        $this->ConfigureConnection();
        $barrier_path = getenv('FAQ_READ_WORKER_BARRIER');
        $ready_path = getenv('FAQ_READ_WORKER_READY');
        $this->assertIsString($barrier_path);
        $this->assertIsString($ready_path);
        $this->assertSame(dirname($barrier_path), dirname($ready_path));
        $this->assertSame(realpath(sys_get_temp_dir()), realpath(dirname(dirname($barrier_path))));
        $this->assertMatchesRegularExpression('/^faq-read-barrier-[a-f0-9]{32}$/D', basename(dirname($barrier_path)));
        $this->assertSame('go', basename($barrier_path));
        $this->assertMatchesRegularExpression('/^ready-[0-7]$/D', basename($ready_path));
        DB::connection()->getPdo();
        file_put_contents($ready_path, 'ready');
        $deadline = microtime(true) + 25;
        while (!is_file($barrier_path)) {
            clearstatcache();
            $this->assertLessThan($deadline, microtime(true), 'Barrier timed out.');
            usleep(10000);
        }
        CarbonImmutable::setTestNow(CarbonImmutable::parse(getenv('FAQ_READ_WORKER_NOW')));
        try {
            $worker_mode = getenv('FAQ_READ_WORKER_MODE');
            $this->assertContains($worker_mode, ['read', 'slug']);
            if ($worker_mode === 'slug') {
                $category_id = DB::table('faqs')->where('id', (int) getenv('FAQ_READ_WORKER_ID'))->value('category_id');
                app(\App\Services\Faq\FaqSlugWriter::class)->Create(['category_id' => $category_id,
                    'question' => 'Concurrent slug question', 'question_en' => 'Concurrent slug question',
                    'answer' => 'Answer', 'is_published' => true]);
            } else {
                app(FaqReadRecorder::class)->Record((int) getenv('FAQ_READ_WORKER_ID'), getenv('FAQ_READ_WORKER_KEY'));
            }
            $this->addToAssertionCount(1);
        } catch (\Throwable $exception) {
            $this->FailSafely($exception, 'worker execution');
        } finally { CarbonImmutable::setTestNow(); DB::disconnect('faq_read_test'); }
    }
    private function WorkerFailure(Process $process, int $worker_number, string $phase): never
    {
        preg_match_all('/FaqReadConcurrencyTest\.php:([0-9]+)/', $process->getOutput().$process->getErrorOutput(), $line_matches);
        $source_lines = implode(',', array_unique($line_matches[1] ?? []));
        $this->fail('Worker '.$worker_number.' failed '.$phase.'; exit '.$process->getExitCode()
            .'; source lines '.$source_lines.'; raw output withheld.');
    }
    private function FailSafely(\Throwable $exception, string $phase): never
    {
        if ($exception instanceof \PHPUnit\Framework\AssertionFailedError) { throw $exception; }
        $this->fail('Concurrency harness failure during '.$phase.' ('.get_class($exception).'); raw details withheld.');
    }
}
