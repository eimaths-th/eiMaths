<?php
// Explicit, labelled accounts for end-to-end tests against the local main backend.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
if (!app()->environment('local') || DB::connection()->getDriverName() !== 'mysql'
    || !in_array(config('database.connections.mysql.host'), ['127.0.0.1', 'localhost', '::1'], true)) {
    throw new RuntimeException('Only local main MySQL is allowed.');
}
$dir = base_path('tmp/ebook-main-qa-20261009');
if (!is_dir($dir)) { mkdir($dir, 0700, true); }
$command = $argv[1] ?? 'summary';
$codes = ['EBOOK-QA001', 'EBOOK-QA002'];
if ($command === 'setup') {
    $set = DB::table('bookuses as b')->join('levels as l', 'l.id', '=', 'b.level_id')
        ->where('l.name', 'K1')->where('b.name', 'G11-G20')->where('b.status', 1)->select('b.*')->first();
    if (!$set) { throw new RuntimeException('Expected existing K1 registration set.'); }
    foreach ($codes as $index => $code) {
        $path = $dir.'/'.($index ? 'tablet' : 'phone').'.json';
        if (is_file($path)) { continue; }
        if (DB::table('contacts')->where('code', $code)->exists()) { throw new RuntimeException('Refusing to replace an existing account.'); }
        $password = bin2hex(random_bytes(12));
        DB::table('contacts')->insert(['type' => 'student', 'code' => $code,
            'name' => '[LOCAL QA] E-book '.($index ? 'Tablet' : 'Phone'), 'nickname' => 'ทดสอบ E-book',
            'password' => Hash::make($password), 'password_generated_at' => now(), 'centre' => 1,
            'level' => $set->level_id, 'term' => $set->term_id, 'bookuse' => $set->id,
            'level_name' => 'K1', 'term_name' => '1', 'bookuse_name' => $set->name,
            'created_at' => now(), 'updated_at' => now()]);
        file_put_contents($path, json_encode(['EBOOK_API_BASE' => 'http://127.0.0.1:8000/api/ebook/v1',
            'EBOOK_TEST_CODE' => $code, 'EBOOK_TEST_PASSWORD' => $password, 'EBOOK_TEST_BOOK' => 'G11'], JSON_PRETTY_PRINT));
    }
    echo "Local QA accounts created; test settings saved privately in ".$dir."\n";
} elseif ($command === 'release') {
    $code = $argv[2] ?? '';
    if (!in_array($code, $codes, true)) { throw new RuntimeException('Only the named QA accounts can be released.'); }
    $student = DB::table('contacts')->where('code', $code)->where('name', 'like', '[LOCAL QA]%')->first();
    if (!$student) { throw new RuntimeException('QA marker missing.'); }
    $session = DB::table('ebook_sessions')->where('student_id', $student->id)->first();
    if ($session) {
        App\Domain\Ebook\SessionHistory::close($session, 'local_qa_reset');
        DB::table('ebook_sessions')->where('id', $session->id)->delete();
    }
    echo "Released QA Session for ".$code."\n";
} elseif ($command === 'snapshot') {
    $page = DB::table('ebook_books as b')->join('ebook_versions as v', 'v.id', '=', 'b.published_version_id')
        ->join('ebook_pages as p', 'p.version_id', '=', 'v.id')->where('b.code', 'G11')->where('p.number', 5)
        ->first(['b.id as book_id', 'b.code', 'v.id as version_id', 'v.number as edition', 'v.source_path', 'p.id as page_id', 'p.number', 'p.image_path']);
    $student = DB::table('contacts')->where('code', 'EBOOK-QA001')->where('name', 'like', '[LOCAL QA]%')->first();
    if (!$page || !$student) { throw new RuntimeException('Expected local QA page and account.'); }
    $work = DB::table('ebook_work')->where('student_id', $student->id)->where('page_id', $page->page_id)->first(['revision', 'document', 'updated_at']);
    $work ??= (object) ['revision' => 0, 'document' => '{"schema":1,"objects":[]}', 'updated_at' => null];
    $work->document = json_decode($work->document);
    $events = DB::table('ebook_events')->where('student_id', $student->id)->where('page_id', $page->page_id)->orderBy('id')
        ->get(['event_id', 'action', 'source', 'metadata', 'session_id', 'received_session_id', 'occurred_at', 'received_at']);
    foreach ($events as $event) { $event->metadata = json_decode($event->metadata ?: '{}'); }
    echo json_encode(['checked_at' => now('UTC')->toIso8601String(), 'student_code' => $student->code, 'student_id' => $student->id,
        'book_code' => $page->code, 'book_id' => $page->book_id, 'page_number' => $page->number, 'page_id' => $page->page_id,
        'version_id' => $page->version_id, 'edition' => $page->edition,
        'pdf_sha256' => hash_file('sha256', config('ebook.root').'/'.$page->source_path),
        'page_image_sha256' => hash_file('sha256', config('ebook.root').'/'.$page->image_path),
        'work' => $work, 'events' => $events], JSON_UNESCAPED_UNICODE)."\n";
} else {
    $students = DB::table('contacts')->whereIn('code', $codes)->get(['id', 'code']);
    $summary = [];
    foreach ($students as $s) {
        $summary[$s->code] = ['student_id' => $s->id,
            'work' => DB::table('ebook_work as w')->join('ebook_pages as p', 'p.id', '=', 'w.page_id')->where('w.student_id', $s->id)->get(['p.number', 'p.version_id', 'w.revision', 'w.updated_at']),
            'events' => DB::table('ebook_events')->where('student_id', $s->id)->select('action', DB::raw('COUNT(*) as count'))->groupBy('action')->get(),
            'total_events' => DB::table('ebook_events')->where('student_id', $s->id)->count()];
    }
    file_put_contents($dir.'/summary.json', json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."\n";
}
