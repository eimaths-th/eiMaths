<?php

namespace Tests\Integration;

use App\Services\Faq\FaqSlugWriter;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Process\Process;
use Tests\Support\FaqSlugDatabaseProfile;
use Tests\TestCase;

final class FaqSlugConcurrencyTest extends TestCase
{
    use FaqSlugDatabaseProfile;

    public function createApplication(): \Illuminate\Foundation\Application
    {
        if (is_file(dirname(__DIR__, 2).'/bootstrap/cache/config.php')) {
            throw new \RuntimeException('Refusing cached application configuration.');
        }
        return parent::createApplication();
    }

    #[Test]
    public function ConcurrentLocalizedEditsPreserveEveryCommittedRevision(): void
    {
        $original_connection = config('database.default');
        $migrations = [];
        try {
            $this->ConfigureSlugDatabase();
            $schema = DB::connection()->getSchemaBuilder();
            $this->assertSame([], $schema->getTableListing(), 'Dedicated schema must be empty.');
            $this->assertSame([], $schema->getViews(), 'Dedicated schema must have no views.');
            foreach ([
                '2026_05_14_000000_create_faqs_table.php',
                '2026_08_27_000000_add_categories_and_english_to_faqs.php',
                '2026_10_20_000001_add_slug_to_faqs.php',
                '2026_10_20_000002_create_faq_read_analytics_tables.php',
                '2026_10_20_000003_create_faq_localized_slug_tables.php',
            ] as $filename) {
                $migration = require database_path('migrations/'.$filename);
                $migration->up();
                $migrations[] = $migration;
            }
            foreach (DB::select('SHOW TABLE STATUS') as $table) {
                $this->assertSame('InnoDB', $table->Engine);
            }
            $runtime = DB::selectOne('SELECT VERSION() AS version, @@SESSION.sql_mode AS sql_mode');
            $this->assertTrue(str_contains($runtime->sql_mode, 'STRICT_TRANS_TABLES')
                || str_contains($runtime->sql_mode, 'STRICT_ALL_TABLES'));
            $isolation = DB::select("SHOW SESSION VARIABLES WHERE Variable_name IN ('transaction_isolation', 'tx_isolation')");
            fwrite(STDERR, json_encode(['operation' => 'faq.urls.test_environment',
                'database_version' => $runtime->version, 'isolation' => collect($isolation)->pluck('Value')->first(),
                'rounds' => 3, 'workers_per_round' => 8], JSON_THROW_ON_ERROR).PHP_EOL);
            $faq = app(FaqSlugWriter::class)->Create([
                'category_id' => DB::table('faq_categories')->value('id'),
                'question' => 'Primary question', 'question_en' => 'English question',
                'answer' => 'Answer', 'position' => 0, 'is_published' => true,
            ]);
            $english_before = DB::table('faq_slug_routes')->where('faq_id', $faq->id)
                ->where('locale', 'en')->where('current_marker', 1)->value('slug');
            for ($round = 1; $round <= 3; $round++) {
                $this->RunWorkers((int) $faq->id, $round);
                $this->assertSame(1 + 8 * $round, (int) DB::table('faq_localized_slug_states')
                    ->where('faq_id', $faq->id)->where('locale', 'th')->value('last_revision'));
                $this->assertSame(8 * $round, DB::table('faq_slug_routes')->where('faq_id', $faq->id)
                    ->where('locale', 'th')->whereNotNull('revision')->whereNull('current_marker')->count());
                $this->assertSame(2, DB::table('faq_slug_routes')->where('faq_id', $faq->id)
                    ->where('current_marker', 1)->count());
                $this->assertSame($english_before, DB::table('faq_slug_routes')->where('faq_id', $faq->id)
                    ->where('locale', 'en')->where('current_marker', 1)->value('slug'));
            }
        } catch (\Throwable $exception) {
            if ($exception instanceof \PHPUnit\Framework\AssertionFailedError) throw $exception;
            $this->fail('Isolated slug concurrency check failed ('.get_class($exception).'); raw details withheld.');
        } finally {
            try {
                foreach (array_reverse($migrations) as $migration) $migration->down();
            } finally {
                if (config('database.connections.faq_slug_test') !== null) DB::disconnect('faq_slug_test');
                DB::setDefaultConnection($original_connection);
                \Illuminate\Support\Facades\Schema::clearResolvedInstance('db.schema');
            }
        }
    }

    private function RunWorkers(int $faq_id, int $round): void
    {
        $directory = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR
            .'faq-slug-barrier-'.bin2hex(random_bytes(16));
        $this->assertTrue(mkdir($directory, 0700));
        $barrier = $directory.DIRECTORY_SEPARATOR.'go';
        $processes = [];
        $ready_paths = [];
        $environment = ['APP_ENV' => 'testing', 'DB_CONNECTION' => 'sqlite',
            'DB_DATABASE' => ':memory:', 'SESSION_DRIVER' => 'array', 'CACHE_DRIVER' => 'array'];
        foreach (['ALLOW_SCHEMA', 'DATABASE', 'HOST', 'PORT', 'USERNAME', 'PASSWORD'] as $suffix) {
            $environment['FAQ_SLUG_TEST_'.$suffix] = getenv('FAQ_SLUG_TEST_'.$suffix);
        }
        try {
            for ($number = 0; $number < 8; $number++) {
                $ready = $directory.DIRECTORY_SEPARATOR.'ready-'.$number;
                $ready_paths[] = $ready;
                $process = new Process([PHP_BINARY, 'tests/Support/faq-slug-worker.php'], base_path(),
                    array_merge($environment, ['FAQ_SLUG_WORKER_FAQ_ID' => (string) $faq_id,
                        'FAQ_SLUG_WORKER_NUMBER' => (string) $number, 'FAQ_SLUG_WORKER_ROUND' => (string) $round,
                        'FAQ_SLUG_WORKER_BARRIER' => $barrier, 'FAQ_SLUG_WORKER_READY' => $ready]), null, 30);
                $processes[] = $process;
                $process->start();
            }
            $deadline = microtime(true) + 20;
            while (count(array_filter($ready_paths, 'is_file')) !== 8) {
                clearstatcache();
                foreach ($processes as $process) {
                    $process->checkTimeout();
                    if (!$process->isRunning() && !$process->isSuccessful()) {
                        $this->fail('A worker failed before the barrier; raw output withheld.');
                    }
                }
                $this->assertLessThan($deadline, microtime(true), 'Worker startup timed out.');
                usleep(10000);
            }
            file_put_contents($barrier, 'go');
            foreach ($processes as $process) {
                $this->assertSame(0, $process->wait(), 'Worker failed; raw output withheld.');
            }
        } finally {
            foreach ($processes as $process) if ($process->isRunning()) $process->stop(1);
            foreach (array_merge($ready_paths, [$barrier]) as $path) if (is_file($path)) unlink($path);
            $this->assertSame(realpath(sys_get_temp_dir()), realpath(dirname($directory)));
            $this->assertMatchesRegularExpression('/^faq-slug-barrier-[a-f0-9]{32}$/D', basename($directory));
            rmdir($directory);
        }
    }
}
