<?php

namespace Tests\Feature\Affiliate;

use App\Domain\Affiliate\Models\AffiliateAuditLog;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AffiliateEmployeeEnrollmentConcurrencyTest extends TestCase
{
    use DatabaseMigrations;

    private const SQLITE_SKIP_REASON = 'SQLite cannot verify deployed row-lock semantics.';
    private const CREATED_OPERATION = 'affiliate.employee-enrollment.created';
    private const WORKER_TIMEOUT_SECONDS = 10;

    #[Test]
    public function ConcurrentFirstEnrollmentsCreateOneProfileAndOneAuditWithoutChangingStaffIdentity(): void
    {
        $this->AssertConcurrencyCheckIsAuthorized();

        config(['affiliate.features.employee_enrollment' => true]);
        $employee_role = Role::findOrCreate('Teacher', 'web');
        $employee = User::factory()->create([
            'email' => 'affiliate-concurrency@example.test',
            'email_verified_at' => null,
            'password' => Hash::make('employee-password'),
            'referral' => null,
            'disable' => 0,
        ]);
        $employee->assignRole($employee_role);
        $employee = $employee->fresh();
        $email_before = $employee->email;
        $email_verified_at_before = $employee->email_verified_at;

        $barrier_path = tempnam(sys_get_temp_dir(), 'affiliate-enrollment-barrier-');
        if ($barrier_path === false) {
            $this->fail('The temporary concurrency barrier could not be created.');
        }

        $workers = [];
        try {
            foreach (['a', 'b'] as $worker_id) {
                $workers[$worker_id] = $this->StartWorker(
                    $employee->id,
                    'affiliate-enrollment-concurrency-' . $worker_id,
                    $barrier_path,
                );
            }

            foreach ($workers as $worker) {
                $this->WaitForWorkerSignal($worker['pipes'][1], 'started');
            }

            unlink($barrier_path);
            $barrier_path = null;

            $results = [];
            foreach ($workers as $worker_id => &$worker) {
                $results[$worker_id] = $this->WaitForWorkerCompletion($worker);
            }
            unset($worker);

            foreach ($results as $worker_id => $result) {
                $this->assertSame(0, $result['exit_code'], 'Worker ' . $worker_id . ' failed: ' . $result['stderr']);
                $this->assertSame('success', $result['payload']['status'] ?? null, $result['stdout']);
            }

            $profile_ids = array_map(
                static fn (array $result): int => (int) $result['payload']['profile_id'],
                $results,
            );
            $this->assertCount(2, $profile_ids);
            $this->assertSame($profile_ids[0], $profile_ids[1]);
            $this->assertSame(1, AffiliateProfile::query()->where('user_id', $employee->id)->count());
            $this->assertSame(
                1,
                AffiliateAuditLog::query()
                    ->where('operation', self::CREATED_OPERATION)
                    ->where('subject_id', $profile_ids[0])
                    ->count(),
            );

            $employee_after = $employee->fresh();
            $this->assertSame($email_before, $employee_after->email);
            $this->assertSame($email_verified_at_before, $employee_after->email_verified_at);
            $this->assertFalse($employee_after->hasRole('Affiliate-user'));
        } finally {
            if (is_string($barrier_path) && file_exists($barrier_path)) {
                unlink($barrier_path);
            }

            foreach ($workers as &$worker) {
                $this->CleanupWorker($worker);
            }
            unset($worker);
        }
    }

    private function AssertConcurrencyCheckIsAuthorized(): void
    {
        $driver_name = strtolower((string) DB::connection()->getDriverName());
        if ($driver_name === 'sqlite') {
            $this->markTestSkipped(self::SQLITE_SKIP_REASON);
        }
        if ($driver_name !== 'mysql') {
            $this->markTestSkipped('The employee enrollment concurrency check requires MySQL row-lock semantics.');
        }
        if (! filter_var(env('AFFILIATE_RUN_CONCURRENCY_TESTS', false), FILTER_VALIDATE_BOOLEAN)) {
            $this->markTestSkipped('Set AFFILIATE_RUN_CONCURRENCY_TESTS=true to run the MySQL concurrency check.');
        }
        if (strtolower((string) config('app.env')) === 'production') {
            $this->markTestSkipped('The concurrency check is never allowed in the production environment.');
        }

        $connection_name = DB::getDefaultConnection();
        $database_name = (string) config('database.connections.' . $connection_name . '.database', '');
        $approved_database_name = (string) env('AFFILIATE_CONCURRENCY_DATABASE', '');
        if ($database_name === '' || $approved_database_name === '' || $approved_database_name !== $database_name) {
            $this->markTestSkipped('Point AFFILIATE_CONCURRENCY_DATABASE at the isolated MySQL test database before opting in.');
        }
        if (preg_match('/(^|[_-])(prod|production|live)([_-]|$)/i', $database_name) === 1) {
            $this->markTestSkipped('The concurrency check refuses production-like database names.');
        }
    }

    private function StartWorker(int $user_id, string $correlation_id, string $barrier_path): array
    {
        $descriptor_specification = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
        $database_config = (array) config('database.connections.' . DB::getDefaultConnection());
        $environment = getenv();
        if (! is_array($environment)) {
            $environment = [];
        }
        $environment = array_merge($environment, [
            'APP_BASE_PATH' => base_path(),
            'AFFILIATE_CONCURRENCY_DB_HOST' => (string) ($database_config['host'] ?? '127.0.0.1'),
            'AFFILIATE_CONCURRENCY_DB_PORT' => (string) ($database_config['port'] ?? '3306'),
            'AFFILIATE_CONCURRENCY_DB_DATABASE' => (string) ($database_config['database'] ?? ''),
            'AFFILIATE_CONCURRENCY_DB_USERNAME' => (string) ($database_config['username'] ?? ''),
            'AFFILIATE_CONCURRENCY_DB_PASSWORD' => (string) ($database_config['password'] ?? ''),
            'AFFILIATE_CONCURRENCY_DB_SOCKET' => (string) ($database_config['unix_socket'] ?? ''),
            'AFFILIATE_CONCURRENCY_USER_ID' => (string) $user_id,
            'AFFILIATE_CONCURRENCY_CORRELATION_ID' => $correlation_id,
            'AFFILIATE_CONCURRENCY_BARRIER' => $barrier_path,
        ]);
        $worker_path = base_path('tests/Feature/Affiliate/Fixtures/affiliate-employee-enrollment-worker.php');
        $command = '"' . PHP_BINARY . '" "' . $worker_path . '"';
        $pipes = [];
        $process = proc_open($command, $descriptor_specification, $pipes, base_path(), $environment);
        if (! is_resource($process)) {
            $this->fail('The MySQL employee enrollment concurrency worker could not be started.');
        }

        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        return [
            'process' => $process,
            'pipes' => $pipes,
        ];
    }

    private function WaitForWorkerSignal($output_pipe, string $expected_signal): void
    {
        $deadline = microtime(true) + self::WORKER_TIMEOUT_SECONDS;
        $output = '';
        while (microtime(true) < $deadline) {
            $output .= (string) stream_get_contents($output_pipe);
            if (str_contains($output, $expected_signal)) {
                return;
            }
            usleep(10000);
        }

        $this->fail('Timed out waiting for employee enrollment worker signal: ' . $expected_signal . '. Output: ' . $output);
    }

    private function WaitForWorkerCompletion(array &$worker): array
    {
        $deadline = microtime(true) + self::WORKER_TIMEOUT_SECONDS;
        $stdout = '';
        $stderr = '';
        while (microtime(true) < $deadline) {
            $stdout .= (string) stream_get_contents($worker['pipes'][1]);
            $stderr .= (string) stream_get_contents($worker['pipes'][2]);
            $status = proc_get_status($worker['process']);
            if (! $status['running']) {
                $stdout .= (string) stream_get_contents($worker['pipes'][1]);
                $stderr .= (string) stream_get_contents($worker['pipes'][2]);
                fclose($worker['pipes'][1]);
                fclose($worker['pipes'][2]);
                $exit_code = proc_close($worker['process']);
                $worker['process'] = null;
                $worker['pipes'] = [];

                return [
                    'exit_code' => $exit_code,
                    'stdout' => $stdout,
                    'stderr' => $stderr,
                    'payload' => $this->ParseWorkerPayload($stdout),
                ];
            }
            usleep(10000);
        }

        $this->fail('Timed out waiting for employee enrollment worker completion. Output: ' . $stdout . ' Error: ' . $stderr);
    }

    private function ParseWorkerPayload(string $stdout): array
    {
        foreach (preg_split('/\R/', $stdout) ?: [] as $line) {
            $payload = json_decode(trim($line), true);
            if (is_array($payload) && array_key_exists('status', $payload)) {
                return $payload;
            }
        }

        return [];
    }

    private function CleanupWorker(array &$worker): void
    {
        if (is_resource($worker['process'] ?? null)) {
            proc_terminate($worker['process']);
            proc_close($worker['process']);
            $worker['process'] = null;
        }
        foreach ($worker['pipes'] ?? [] as $pipe) {
            if (is_resource($pipe)) {
                fclose($pipe);
            }
        }
        $worker['pipes'] = [];
    }
}
