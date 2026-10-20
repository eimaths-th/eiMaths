<?php

namespace Tests\Support;

use Illuminate\Support\Facades\DB;

trait FaqSlugDatabaseProfile
{
    private function ConfigureSlugDatabase(): void
    {
        if (is_file(base_path('bootstrap/cache/config.php'))) {
            throw new \RuntimeException('Cached application configuration is not allowed.');
        }
        if (getenv('FAQ_SLUG_TEST_ALLOW_SCHEMA') !== 'true') {
            throw new \RuntimeException('Dedicated schema opt-in is required.');
        }
        $database_name = getenv('FAQ_SLUG_TEST_DATABASE');
        $host = getenv('FAQ_SLUG_TEST_HOST');
        $port = getenv('FAQ_SLUG_TEST_PORT');
        $username = getenv('FAQ_SLUG_TEST_USERNAME');
        $password = getenv('FAQ_SLUG_TEST_PASSWORD');
        if (!is_string($database_name) || !preg_match('/^faq_slug_test_[a-z0-9_]+$/D', $database_name)
            || !in_array($host, ['127.0.0.1', '::1'], true)
            || !is_string($port) || !preg_match('/^[1-9][0-9]{0,4}$/D', $port)
            || (int) $port > 65535 || in_array((int) $port, [3306, 33060], true)
            || !is_string($username) || trim($username) === '' || strtolower(trim($username)) === 'root'
            || !is_string($password)) {
            throw new \RuntimeException('Invalid isolated test connection profile.');
        }
        config(['database.connections.faq_slug_test' => [
            'driver' => 'mysql', 'host' => $host, 'port' => $port,
            'database' => $database_name, 'username' => $username, 'password' => $password,
            'unix_socket' => '', 'charset' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '', 'prefix_indexes' => true, 'strict' => true, 'engine' => 'InnoDB',
        ], 'faq-urls.enabled' => true]);
        DB::purge('faq_slug_test');
        DB::setDefaultConnection('faq_slug_test');
        \Illuminate\Support\Facades\Schema::clearResolvedInstance('db.schema');
    }
}
