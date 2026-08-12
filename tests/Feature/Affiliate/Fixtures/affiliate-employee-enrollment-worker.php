<?php

declare(strict_types=1);

use App\Domain\Affiliate\Services\AffiliateEmployeeEnrollmentService;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

$base_path = dirname(__DIR__, 4);

try {
    require $base_path . '/vendor/autoload.php';
    $app = require_once $base_path . '/bootstrap/app.php';
    $app->make(Kernel::class)->bootstrap();

    config([
        'database.default' => 'affiliate_concurrency_worker',
        'database.connections.affiliate_concurrency_worker' => [
            'driver' => 'mysql',
            'host' => (string) getenv('AFFILIATE_CONCURRENCY_DB_HOST'),
            'port' => (string) getenv('AFFILIATE_CONCURRENCY_DB_PORT'),
            'database' => (string) getenv('AFFILIATE_CONCURRENCY_DB_DATABASE'),
            'username' => (string) getenv('AFFILIATE_CONCURRENCY_DB_USERNAME'),
            'password' => (string) getenv('AFFILIATE_CONCURRENCY_DB_PASSWORD'),
            'unix_socket' => (string) getenv('AFFILIATE_CONCURRENCY_DB_SOCKET'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => [],
        ],
        'affiliate.features.employee_enrollment' => true,
        'affiliate.maintenance_mode' => false,
    ]);
    DB::purge('affiliate_concurrency_worker');

    $barrier_path = (string) getenv('AFFILIATE_CONCURRENCY_BARRIER');
    $user_id = (int) getenv('AFFILIATE_CONCURRENCY_USER_ID');
    $correlation_id = (string) getenv('AFFILIATE_CONCURRENCY_CORRELATION_ID');
    User::query()->findOrFail($user_id);
    DB::connection()->getPdo();

    fwrite(STDOUT, "started\n");
    fflush(STDOUT);
    while ($barrier_path !== '' && file_exists($barrier_path)) {
        usleep(10000);
    }

    $profile = app(AffiliateEmployeeEnrollmentService::class)->Enroll(
        User::query()->findOrFail($user_id),
        $correlation_id,
    );
    fwrite(STDOUT, json_encode([
        'status' => 'success',
        'profile_id' => (int) $profile->getKey(),
    ], JSON_THROW_ON_ERROR) . "\n");
    fwrite(STDOUT, "completed\n");
    exit(0);
} catch (Throwable $exception) {
    fwrite(STDERR, json_encode([
        'status' => 'error',
        'error_class' => get_class($exception),
        'error_message' => $exception->getMessage(),
    ], JSON_THROW_ON_ERROR) . "\n");
    exit(1);
}
