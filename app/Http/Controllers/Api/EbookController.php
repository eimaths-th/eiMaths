<?php

namespace App\Http\Controllers\Api;

use App\Domain\Ebook\Audit;
use App\Domain\Ebook\BookAccess;
use App\Domain\Ebook\DocumentValidator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class EbookController extends Controller
{
    public function __construct(private BookAccess $access) {}

    public function index(Request $request)
    {
        $studentId = $request->attributes->get('ebook_student')->id;
        $books = $this->access->books($studentId)->orderBy('code')->get()->map(function ($book) use ($studentId) {
            $version = DB::table('ebook_versions')->find($book->published_version_id);
            $work = DB::table('ebook_work as w')->join('ebook_pages as p', 'p.id', '=', 'w.page_id')
                ->join('ebook_versions as v', 'v.id', '=', 'p.version_id')->where('v.book_id', $book->id)
                ->where('w.student_id', $studentId)->orderByDesc('w.updated_at')->first(['p.number', 'w.updated_at']);
            return ['id' => $book->id, 'code' => $book->code, 'title' => $book->title,
                'description' => $book->description, 'accent' => $book->accent,
                'page_count' => $version->page_count, 'last_page' => $work?->number ?? 1,
                'last_saved_at' => $work?->updated_at];
        });
        return response()->json(['books' => $books, 'demo' => (bool) config('ebook.demo')]);
    }

    public function open(Request $request, int $book)
    {
        $session = $request->attributes->get('ebook_session');
        $item = $this->access->book($session->student_id, $book);
        // Keep annotations on their original edition when a newer PDF is published.
        $last = DB::table('ebook_work as w')->join('ebook_pages as p', 'p.id', '=', 'w.page_id')
            ->join('ebook_versions as v', 'v.id', '=', 'p.version_id')->where('v.book_id', $book)
            ->where('w.student_id', $session->student_id)->whereNotNull('v.published_at')
            ->orderByDesc('w.updated_at')->first(['p.version_id', 'p.number']);
        $versionId = $last?->version_id ?? $item->published_version_id;
        $version = DB::table('ebook_versions')->find($versionId);
        $pages = DB::table('ebook_pages')->where('version_id', $versionId)->orderBy('number')->get(['id', 'number', 'width', 'height']);
        Audit::record($session, 'book_open', $book, metadata: ['version' => $version->number]);
        return response()->json(['book' => ['id' => $item->id, 'title' => $item->title, 'code' => $item->code],
            'version_id' => $versionId, 'version' => $version->number, 'latest_version_id' => $item->published_version_id,
            'last_page' => $last?->number ?? 1, 'pages' => $pages]);
    }

    public function image(Request $request, int $page)
    {
        $session = $request->attributes->get('ebook_session');
        $item = $this->access->page($session->student_id, $page);
        $path = config('ebook.root').DIRECTORY_SEPARATOR.$item->image_path;
        abort_unless(is_file($path), 404);
        return response()->file($path, ['Content-Type' => 'image/png', 'Cache-Control' => 'private, no-store']);
    }

    public function work(Request $request, int $page)
    {
        $session = $request->attributes->get('ebook_session');
        $item = $this->access->page($session->student_id, $page);
        $work = DB::table('ebook_work')->where('student_id', $session->student_id)->where('page_id', $page)->first();
        Audit::record($session, 'page_open', $item->book_id, $page, ['number' => $item->number]);
        return response()->json(['revision' => $work?->revision ?? 0, 'document' => $work ? json_decode($work->document) : ['schema' => 1, 'objects' => []]]);
    }

    public function save(Request $request, int $page, DocumentValidator $validator)
    {
        $data = $request->validate(['revision' => 'required|integer|min:0', 'mutation_id' => 'required|uuid', 'document' => 'required|array:schema,objects']);
        $session = $request->attributes->get('ebook_session');
        $item = $this->access->page($session->student_id, $page);
        $document = $validator->validate($data['document'], $session->student_id, $item);
        return DB::transaction(function () use ($data, $session, $item, $document, $page) {
            DB::table('contacts')->where('id', $session->student_id)->lockForUpdate()->first(['id']);
            $query = DB::table('ebook_work')->where('student_id', $session->student_id)->where('page_id', $page);
            $work = $query->lockForUpdate()->first();
            if ($work && $work->last_mutation_id === $data['mutation_id']) {
                return response()->json(['revision' => $work->revision, 'saved_at' => $work->updated_at]);
            }
            if (($work?->revision ?? 0) !== $data['revision']) {
                return response()->json(['message' => 'หน้านี้มีข้อมูลใหม่กว่า กรุณาเปิดหน้าอีกครั้งก่อนแก้ไข', 'code' => 'revision_conflict'], 409);
            }
            $revision = $data['revision'] + 1;
            $values = ['document' => json_encode($document), 'revision' => $revision, 'last_mutation_id' => $data['mutation_id'], 'updated_at' => now()];
            if ($work) { $query->update($values); }
            else { DB::table('ebook_work')->insert($values + ['student_id' => $session->student_id, 'page_id' => $page, 'created_at' => now()]); }
            Audit::record($session, 'work_saved', $item->book_id, $page, ['revision' => $revision, 'objects' => count($document['objects'])]);
            return response()->json(['revision' => $revision, 'saved_at' => now()->toIso8601String()]);
        });
    }

    public function events(Request $request, \App\Domain\Ebook\EventIngestor $ingestor)
    {
        $data = $request->validate(['events' => 'required|array|min:1|max:25']);
        return response()->json($ingestor->ingest($request->attributes->get('ebook_session'), $data['events']));
    }
}
