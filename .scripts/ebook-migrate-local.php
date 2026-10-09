<?php
// Additive upgrade for the local main database and the existing isolated demo.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (!app()->environment('local') || !in_array(config('database.connections.mysql.host'), ['127.0.0.1', 'localhost', '::1'], true)) {
    throw new RuntimeException('Only the local development database is allowed.');
}
$backup = storage_path('app/private/ebook-upgrade-backup-'.date('Ymd-His'));
Illuminate\Support\Facades\File::makeDirectory($backup, 0700, true);
$tables = ['ebook_books', 'ebook_catalog', 'ebook_versions', 'ebook_pages', 'ebook_work', 'ebook_sessions', 'ebook_events'];
foreach ($tables as $table) {
    file_put_contents($backup.'/'.$table.'.json', Illuminate\Support\Facades\DB::table($table)->get()->toJson(JSON_UNESCAPED_UNICODE));
}
$path = 'database/migrations/2026_10_09_000001_improve_ebook_import_and_activity.php';
Illuminate\Support\Facades\Artisan::call('migrate', ['--path' => $path, '--force' => true]);
echo Illuminate\Support\Facades\Artisan::output();
$demo = storage_path('app/ebook-demo.sqlite');
if (is_file($demo)) {
    copy($demo, $backup.'/ebook-demo.sqlite');
    App\Domain\Ebook\DemoEnvironment::configure();
    if (!Illuminate\Support\Facades\Schema::hasColumn('ebook_versions', 'original_filename')) {
        (require base_path($path))->up();
    }
    echo "Existing Demo schema upgraded without removing work.\n";
}
echo "Local backup: ".$backup."\n";
