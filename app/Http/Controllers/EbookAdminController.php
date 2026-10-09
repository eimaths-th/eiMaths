<?php

namespace App\Http\Controllers;

use App\Domain\Ebook\BookCatalog;
use App\Jobs\ConvertEbookPdf;
use App\Support\Branch\BranchAccess;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class EbookAdminController extends Controller
{
    private function canManage(Request $request): bool
    {
        if (\App\Http\Middleware\EnsureLocalEbookDemo::allowed($request)) { return true; }
        return $request->user() && BranchAccess::isCentral($request->user()) && $request->user()->can('ebook-manage');
    }

    private function canViewActivity(Request $request): bool
    {
        return \App\Http\Middleware\EnsureLocalEbookDemo::allowed($request)
            || ($request->user()?->can('ebook-activity-list') ?? false);
    }

    private function actor(Request $request): array
    {
        return ['id' => $request->user()?->id, 'name' => $request->user()?->name ?? 'Local Demo'];
    }

    public function index(Request $request, BookCatalog $catalog)
    {
        abort_unless(config('ebook.enabled'), 404);
        $manage = $this->canManage($request);
        $viewActivity = $this->canViewActivity($request);
        abort_unless($manage || $viewActivity, 403);
        $section = $request->query('section', $manage ? 'library' : 'activity');
        abort_unless(in_array($section, ['library', 'import', 'history', 'activity'], true), 404);
        abort_unless($section === 'activity' ? $viewActivity : $manage, 403);
        $request->validate(['from' => 'nullable|date_format:Y-m-d', 'to' => array_filter(['nullable', 'date_format:Y-m-d', $request->filled('from') ? 'after_or_equal:from' : null]),
            'event_action' => 'nullable|string|max:60', 'student' => 'nullable|string|max:60', 'search' => 'nullable|string|max:40'], __('ebooks.validation'), __('ebooks.attributes'));
        $events = $section === 'activity' ? $this->eventsQuery($request)->orderByDesc('e.occurred_at')->orderByDesc('e.id')->paginate(50)->withQueryString() : null;
        $sourceIds = ($events?->getCollection() ?? collect())->map(fn ($e) => (json_decode($e->metadata ?? '{}', true) ?: [])['source_page_id'] ?? null)->filter()->unique();
        $pageNumbers = DB::table('ebook_pages')->whereIn('id', $sourceIds)->pluck('number', 'id')->all();
        $books = DB::table('ebook_books')->orderBy('code')->get();
        $versions = $manage ? DB::table('ebook_versions')->orderByDesc('number')->get()->groupBy('book_id') : collect();
        $volumes = $manage ? $catalog->volumes() : collect();
        $indexedBooks = $books->keyBy('code');
        $allVolumes = $volumes->map(function ($volume) use ($indexedBooks, $versions) {
            $book = $indexedBooks->get($volume['code']);
            $latest = $book ? $versions->get($book->id, collect())->first() : null;
            return $volume + ['book' => $book, 'latest' => $latest,
                'status' => $book?->published_version_id ? 'published' : ($latest?->status ?? 'missing')];
        });
        $filtered = $allVolumes->filter(fn ($v) =>
            (!$request->filled('level') || $v['level_id'] === $request->integer('level')) &&
            (!$request->filled('term') || $v['term_id'] === $request->integer('term')) &&
            (!$request->filled('search') || str_contains($v['code'], strtoupper($request->string('search')->trim()->toString()))) &&
            (!$request->filled('status') || $v['status'] === $request->query('status')))->values();
        $library = new LengthAwarePaginator($filtered->forPage(max(1, $request->integer('page', 1)), 20)->values(), $filtered->count(), 20,
            max(1, $request->integer('page', 1)), ['path' => $request->url(), 'query' => $request->query()]);
        $history = $manage ? DB::table('ebook_versions as v')->join('ebook_books as b', 'b.id', '=', 'v.book_id')
            ->when($request->filled('book'), fn ($q) => $q->where('v.book_id', $request->integer('book')))
            ->orderByDesc('v.id')->select('v.*', 'b.code', 'b.published_version_id')->paginate(20)->withQueryString() : null;
        $adminEvents = $manage && $section === 'history' ? DB::table('ebook_admin_events as e')
            ->join('ebook_books as b', 'b.id', '=', 'e.book_id')->join('ebook_versions as v', 'v.id', '=', 'e.version_id')
            ->when($request->filled('book'), fn ($q) => $q->where('e.book_id', $request->integer('book')))
            ->orderByDesc('e.id')->select('e.*', 'b.code', 'v.number')->paginate(20, ['*'], 'audit_page')->withQueryString() : null;
        $layout = config('ebook.demo') ? 'ebooks.demo-layout' : 'layouts.app';
        $routePrefix = config('ebook.demo') ? 'ebook-demo' : 'ebooks';
        return view('ebooks.index', compact('manage', 'viewActivity', 'section', 'events', 'pageNumbers', 'books', 'versions', 'volumes', 'allVolumes', 'library', 'history', 'adminEvents', 'layout', 'routePrefix'));
    }

    private function eventsQuery(Request $request)
    {
        $query = DB::table('ebook_events as e')->join('contacts as c', 'c.id', '=', 'e.student_id')
            ->leftJoin('ebook_books as b', 'b.id', '=', 'e.book_id')->leftJoin('ebook_pages as p', 'p.id', '=', 'e.page_id')
            ->select('e.*', 'c.code as student_code', 'c.name as student_name', 'b.code as book_code', 'p.number as page_number');
        if (!\App\Http\Middleware\EnsureLocalEbookDemo::allowed($request) && !BranchAccess::canCrossBranches($request->user())) { $query->where('c.centre', $request->user()->department_id); }
        if ($request->filled('student')) { $query->where('c.code', $request->string('student')->toString()); }
        if ($request->filled('book')) { $query->where('e.book_id', $request->integer('book')); }
        if ($request->filled('event_action')) { $query->where('e.action', $request->string('event_action')->toString()); }
        if ($request->filled('from')) { $query->where('e.occurred_at', '>=', Carbon::parse($request->query('from'), 'Asia/Bangkok')->startOfDay()->utc()); }
        if ($request->filled('to')) { $query->where('e.occurred_at', '<', Carbon::parse($request->query('to'), 'Asia/Bangkok')->addDay()->startOfDay()->utc()); }
        return $query;
    }

    public function upload(Request $request, BookCatalog $catalog)
    {
        abort_unless(config('ebook.enabled') && $this->canManage($request), 403);
        $data = $request->validate(['catalog_key' => 'required|string|max:80', 'pdf' => 'required|file|mimes:pdf|max:20480'], __('ebooks.validation'), __('ebooks.attributes'));
        $volume = $catalog->selected($data['catalog_key']);
        $actor = $this->actor($request);
        $file = $request->file('pdf');
        $filename = mb_substr(basename(str_replace('\\', '/', $file->getClientOriginalName())), 0, 255);
        $size = $file->getSize();
        $root = config('ebook.root'); $relative = 'imports/'.Str::uuid().'.pdf';
        if (!is_dir($root.'/imports')) { mkdir($root.'/imports', 0700, true); }
        $file->move($root.'/imports', basename($relative));
        try {
            [$bookId, $versionId] = DB::transaction(function () use ($volume, $actor, $relative, $filename, $size) {
                DB::table('ebook_books')->insertOrIgnore(['code' => $volume['code'], 'title' => 'หนังสือ '.$volume['code'],
                    'description' => $volume['level'].' · เทอม '.$volume['term'], 'created_at' => now(), 'updated_at' => now()]);
                $book = DB::table('ebook_books')->where('code', $volume['code'])->lockForUpdate()->first();
                $existing = DB::table('ebook_catalog')->where('book_id', $book->id)->pluck('bookuse_id')->all();
                if (array_diff($existing, $volume['catalog_ids'])) {
                    throw ValidationException::withMessages(['catalog_key' => __('ebooks.messages.catalog_conflict')]);
                }
                foreach ($volume['catalog_ids'] as $catalogId) {
                    DB::table('ebook_catalog')->insertOrIgnore(['book_id' => $book->id, 'bookuse_id' => $catalogId]);
                }
                $number = (int) DB::table('ebook_versions')->where('book_id', $book->id)->max('number') + 1;
                $versionId = DB::table('ebook_versions')->insertGetId(['book_id' => $book->id, 'number' => $number,
                    'source_path' => $relative, 'original_filename' => $filename, 'original_size' => $size,
                    'uploaded_by' => $actor['id'], 'uploaded_by_name' => $actor['name'],
                    'status' => 'queued', 'created_at' => now(), 'updated_at' => now()]);
                $this->adminEvent($actor, $book->id, $versionId, 'imported', ['filename' => $filename]);
                return [$book->id, $versionId];
            });
        } catch (\Throwable $e) { @unlink($root.'/'.$relative); throw $e; }
        $message = __('ebooks.messages.import_received');
        try { ConvertEbookPdf::dispatch($versionId); }
        catch (\Throwable $e) { report($e); $message = __('ebooks.messages.import_failed'); }
        return redirect()->route(config('ebook.demo') ? 'ebook-demo.index' : 'ebooks.index', ['section' => 'history', 'book' => $bookId])->with('success', $message);
    }

    private function adminEvent(array $actor, int $book, int $version, string $action, array $metadata = []): void
    {
        DB::table('ebook_admin_events')->insert(['actor_id' => $actor['id'], 'actor_name' => $actor['name'],
            'book_id' => $book, 'version_id' => $version, 'action' => $action, 'metadata' => json_encode($metadata), 'occurred_at' => now('UTC')]);
    }

    public function publish(Request $request, int $version)
    {
        abort_unless(config('ebook.enabled') && $this->canManage($request), 403);
        DB::transaction(function () use ($request, $version) {
            $item = DB::table('ebook_versions')->where('id', $version)->where('status', 'ready')->first();
            abort_unless($item, 422, __('ebooks.messages.not_ready'));
            $book = DB::table('ebook_books')->where('id', $item->book_id)->lockForUpdate()->first();
            if ((int) $book->published_version_id === $version) { return; }
            $actor = $this->actor($request);
            if (!$item->published_at) {
                DB::table('ebook_versions')->where('id', $item->id)->update(['published_at' => now(),
                    'published_by' => $actor['id'], 'published_by_name' => $actor['name'], 'updated_at' => now()]);
            }
            DB::table('ebook_books')->where('id', $item->book_id)->update(['published_version_id' => $item->id, 'updated_at' => now()]);
            $this->adminEvent($actor, $item->book_id, $item->id, 'published');
        });
        return back()->with('success', __('ebooks.messages.published'));
    }

    public function preview(Request $request, int $version)
    {
        abort_unless(config('ebook.enabled') && $this->canManage($request), 403);
        $edition = DB::table('ebook_versions')->where('id', $version)->where('status', 'ready')->first();
        abort_unless($edition, 404);
        $book = DB::table('ebook_books')->find($edition->book_id);
        $number = min((int) $edition->page_count, max(1, $request->integer('page', 1)));
        $layout = config('ebook.demo') ? 'ebooks.demo-layout' : 'layouts.app';
        $routePrefix = config('ebook.demo') ? 'ebook-demo' : 'ebooks';
        return response()->view('ebooks.preview', compact('edition', 'book', 'number', 'layout', 'routePrefix'))->header('Cache-Control', 'private, no-store');
    }

    public function previewImage(Request $request, int $version)
    {
        abort_unless(config('ebook.enabled') && $this->canManage($request), 403);
        $page = DB::table('ebook_pages')->where('version_id', $version)->where('number', max(1, $request->integer('page', 1)))->first();
        abort_unless($page, 404);
        return response()->file(config('ebook.root').'/'.$page->image_path, ['Cache-Control' => 'private, no-store']);
    }
}
