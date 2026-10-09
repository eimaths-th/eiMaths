<?php

namespace App\Domain\Ebook;

use Illuminate\Support\Facades\DB;

final class DemoEnvironment
{
    public static function configure(): void
    {
        $database = storage_path('app/ebook-demo.sqlite');
        if (!is_file($database)) { throw new \RuntimeException('Run .scripts/ebook-demo-setup.php first.'); }
        config([
            'app.env' => 'local', 'app.debug' => false, 'app.url' => 'http://127.0.0.1:8787',
            'app.key' => 'base64:'.base64_encode(hash('sha256', 'eimaths-isolated-local-demo-key', true)),
            'database.default' => 'sqlite', 'database.connections.sqlite.database' => $database,
            'database.connections.sqlite.foreign_key_constraints' => true,
            'session.driver' => 'file', 'session.cookie' => 'eimaths_ebook_demo', 'session.secure' => false,
            'cache.default' => 'file', 'cache.prefix' => 'eimaths_ebook_demo_',
            'queue.default' => 'sync', 'ebook.enabled' => true, 'ebook.demo' => true,
            'ebook.root' => storage_path('app/ebook-demo-private'),
            'sanctum.stateful' => [],
        ]);
        DB::purge('sqlite');
        DB::connection()->statement('PRAGMA busy_timeout=5000');
    }
}
