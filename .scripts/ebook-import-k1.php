<?php

// Local import of the supplied K1 PDFs. No student registration is modified.
// php -d extension=pdo_sqlite -d extension=sqlite3 .scripts/ebook-import-k1.php --demo [--publish]
// php .scripts/ebook-import-k1.php --local [--publish]
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Domain\Ebook\DemoEnvironment;
use App\Domain\Ebook\PdfConverter;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

$demo = in_array('--demo', $argv, true);
$local = in_array('--local', $argv, true);
if ($demo === $local) { throw new RuntimeException('Choose exactly one target: --demo or --local'); }
if ($demo) {
    DemoEnvironment::configure();
    if (DB::connection()->getDriverName() !== 'sqlite'
        || realpath(DB::connection()->getDatabaseName()) !== realpath(storage_path('app/ebook-demo.sqlite'))) {
        throw new RuntimeException('Expected the isolated Demo SQLite database.');
    }
} elseif (!app()->environment('local') || DB::connection()->getDriverName() !== 'mysql'
    || !in_array(config('database.connections.mysql.host'), ['127.0.0.1', 'localhost', '::1'], true)) {
    throw new RuntimeException('--local requires the local MySQL development database.');
}

$publish = in_array('--publish', $argv, true);
$sourceRoot = dirname(base_path()).DIRECTORY_SEPARATOR.'PDF';
$catalog = DB::table('bookuses as b')->join('levels as l', 'l.id', '=', 'b.level_id')
    ->join('terms as t', 't.id', '=', 'b.term_id')->where('b.status', 1)
    ->where('l.name', $demo ? 'Level 1' : 'K1')->where('t.name', $demo ? 'Term 1' : '1')
    ->whereIn('b.name', ['G11-G20', 'G11-G15', 'G16-G20'])->pluck('b.id', 'b.name')->all();
if (count($catalog) !== 3) { throw new RuntimeException('The existing full/half-term K1 book sets must exist first.'); }

$inputs = [];
foreach (range(11, 20) as $number) {
    $stem = sprintf('%03d', $number);
    $source = $sourceRoot.DIRECTORY_SEPARATOR.$stem.'.pdf';
    if (!is_file($source) || filesize($source) > 20 * 1024 * 1024) {
        throw new RuntimeException('Missing PDF or file exceeds 20 MB: '.$source);
    }
    $info = new Process([config('ebook.pdfinfo'), $source]);
    $info->setTimeout(30)->mustRun();
    if (!preg_match('/^Pages:\s+(\d+)/m', $info->getOutput(), $match)
        || (int) $match[1] < 1 || (int) $match[1] > config('ebook.max_pages')
        || preg_match('/^Encrypted:\s+yes/im', $info->getOutput())) {
        throw new RuntimeException('Unsupported sample PDF: '.$stem);
    }
    $inputs[] = ['number' => $number, 'stem' => $stem, 'code' => 'G'.$number,
        'source' => $source, 'hash' => hash_file('sha256', $source), 'count' => (int) $match[1]];
}

// Preserve earlier synthetic Demo worksheets and their work under separate codes.
// This lets the confirmed G11/G16 identifiers display the supplied books instead.
if ($demo) {
    DB::transaction(function () {
        foreach (['G11', 'G16'] as $code) {
            $synthetic = DB::table('ebook_books as b')->join('ebook_versions as v', 'v.book_id', '=', 'b.id')
                ->where('b.code', $code)->where('v.source_path', 'imports/'.$code.'-demo.pdf')->first(['b.id']);
            if ($synthetic) {
                DB::table('ebook_books')->where('id', $synthetic->id)->update([
                    'code' => 'DEMO-'.$code, 'published_version_id' => null, 'updated_at' => now(),
                ]);
            }
        }
    });
}

foreach ($inputs as $input) {
    $code = $input['code'];
    $relative = 'imports/K1-'.$input['stem'].'-'.substr($input['hash'], 0, 16).'.pdf';
    $destination = config('ebook.root').DIRECTORY_SEPARATOR.$relative;
    if (!is_dir(dirname($destination))) { mkdir(dirname($destination), 0700, true); }
    if (!is_file($destination) && !copy($input['source'], $destination)) {
        throw new RuntimeException('Cannot copy PDF to private storage: '.$code);
    }
    if (hash_file('sha256', $destination) !== $input['hash']) { throw new RuntimeException('Source checksum mismatch: '.$code); }
    [$bookId, $versionId] = DB::transaction(function () use ($code, $input, $relative, $catalog, $demo) {
        $book = DB::table('ebook_books')->where('code', $code)->first();
        // Reuse the unexposed 011-013 drafts prepared before the code mapping was confirmed.
        if (!$book && $demo) {
            $draft = DB::table('ebook_books as b')->join('ebook_versions as v', 'v.book_id', '=', 'b.id')
                ->where('b.code', $input['stem'])->whereNull('b.published_version_id')->whereNull('v.published_at')
                ->where('v.source_path', $relative)->first(['b.id']);
            if ($draft) {
                DB::table('ebook_books')->where('id', $draft->id)->update(['code' => $code]);
                $book = DB::table('ebook_books')->find($draft->id);
            }
        }
        $bookId = $book?->id ?? DB::table('ebook_books')->insertGetId([
            'code' => $code, 'title' => 'หนังสือ '.$code, 'description' => 'K1 · เทอม 1',
            'accent' => '#F77A26', 'created_at' => now(), 'updated_at' => now(),
        ]);
        // These sample records use the confirmed catalog code, not the source filename.
        if ($demo && $book && str_starts_with($book->title, 'K1 · เล่ม ')) {
            DB::table('ebook_books')->where('id', $bookId)->update(['title' => 'หนังสือ '.$code, 'description' => 'K1 · เทอม 1', 'updated_at' => now()]);
        }
        $sets = [$catalog['G11-G20'], $catalog[$input['number'] <= 15 ? 'G11-G15' : 'G16-G20']];
        foreach ($sets as $set) { DB::table('ebook_catalog')->insertOrIgnore(['book_id' => $bookId, 'bookuse_id' => $set]); }
        $version = DB::table('ebook_versions')->where('book_id', $bookId)->where('source_path', $relative)->first();
        $versionId = $version?->id ?? DB::table('ebook_versions')->insertGetId([
            'book_id' => $bookId, 'number' => (int) DB::table('ebook_versions')->where('book_id', $bookId)->max('number') + 1,
            'source_path' => $relative, 'status' => 'queued', 'created_at' => now(), 'updated_at' => now(),
        ]);
        return [$bookId, $versionId];
    });
    app(PdfConverter::class)->convert($versionId);
    $version = DB::table('ebook_versions')->find($versionId);
    if ($version->status !== 'ready' || (int) $version->page_count !== $input['count']) {
        throw new RuntimeException('Conversion incomplete: '.$code);
    }
    if ($publish) {
        DB::transaction(function () use ($bookId, $versionId, $version) {
            DB::table('ebook_versions')->where('id', $versionId)->update(['published_at' => $version->published_at ?? now(), 'updated_at' => now()]);
            DB::table('ebook_books')->where('id', $bookId)->update(['published_version_id' => $versionId, 'updated_at' => now()]);
        });
    }
    echo json_encode(['target' => $demo ? 'demo' : 'local', 'file' => $input['stem'].'.pdf', 'code' => $code,
        'book_id' => $bookId, 'version_id' => $versionId, 'pages' => $version->page_count,
        'published' => $publish || $version->published_at !== null], JSON_UNESCAPED_SLASHES).PHP_EOL;
}
