<?php
// Only this dedicated SQLite database is touched. The existing .env is never edited.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$path = storage_path('app/ebook-demo.sqlite');
if (!is_file($path)) { touch($path); }
App\Domain\Ebook\DemoEnvironment::configure();
if (!Illuminate\Support\Facades\Schema::hasTable('ebook_books')) {
    App\Domain\Ebook\DemoData::schema();
    App\Domain\Ebook\DemoData::seed();
}
if (!Illuminate\Support\Facades\Schema::hasColumn('ebook_versions', 'original_filename')) {
    (require database_path('migrations/2026_10_09_000001_improve_ebook_import_and_activity.php'))->up();
}
foreach ([1, 2] as $id) {
    $version = Illuminate\Support\Facades\DB::table('ebook_versions')->find($id);
    if ($version->status !== 'ready') {
        App\Domain\Ebook\DemoData::createPdf($id);
        app(App\Domain\Ebook\PdfConverter::class)->convert($id);
        Illuminate\Support\Facades\DB::table('ebook_versions')->where('id', $id)->update(['published_at' => now()]);
        Illuminate\Support\Facades\DB::table('ebook_books')->where('id', $id)->update(['published_version_id' => $id]);
    }
}
echo "Local demo ready: DEMO001 / DEMO002. Books converted from sample PDFs.\n";
