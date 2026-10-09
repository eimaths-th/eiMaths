<?php

namespace App\Domain\Ebook;

use Illuminate\Support\Facades\DB;

final class BookAccess
{
    public function catalogIds(int $studentId): array
    {
        // Registration stores an expanded, named snapshot for multi-term enrollments.
        // Match its level/term/name, never infer entitlement from consecutive database IDs.
        $snapshots = DB::table('order_detail_lists as d')
            ->join('orders as o', 'o.id', '=', 'd.order_id')
            ->join('bookuses as b', 'b.name', '=', 'd.book')
            ->join('levels as l', function ($j) { $j->on('l.id', '=', 'b.level_id')->on('l.name', '=', 'd.level'); })
            ->join('terms as t', function ($j) { $j->on('t.id', '=', 'b.term_id')->on('t.name', '=', 'd.term'); })
            ->where('o.cid', $studentId)->where('b.status', 1)->pluck('b.id');

        $direct = DB::table('order_details as d')->join('orders as o', 'o.id', '=', 'd.order_id')
            ->join('bookuses as b', 'b.id', '=', 'd.product_id')
            ->where('o.cid', $studentId)->where('b.status', 1)->pluck('b.id');
        $student = DB::table('contacts')->where('id', $studentId)->first(['bookuse', 'bookuse2']);
        $fallback = DB::table('bookuses')->where('status', 1)
            ->whereIn('id', array_filter([$student?->bookuse, $student?->bookuse2]))->pluck('id');

        return $snapshots->merge($direct)->merge($fallback)->unique()->map(fn ($id) => (int) $id)->values()->all();
    }

    public function books(int $studentId)
    {
        return DB::table('ebook_books')->whereNotNull('published_version_id')
            ->whereIn('id', DB::table('ebook_catalog')->select('book_id')->whereIn('bookuse_id', $this->catalogIds($studentId)));
    }

    public function book(int $studentId, int $bookId): object
    {
        $book = $this->books($studentId)->where('id', $bookId)->first();
        abort_unless($book, 404, 'ไม่พบหนังสือในรายการลงทะเบียน');
        return $book;
    }

    public function page(int $studentId, int $pageId): object
    {
        $page = DB::table('ebook_pages as p')->join('ebook_versions as v', 'v.id', '=', 'p.version_id')
            ->where('p.id', $pageId)->whereNotNull('v.published_at')
            ->select('p.*', 'v.book_id')->first();
        abort_unless($page, 404);
        $this->book($studentId, (int) $page->book_id);
        return $page;
    }
}
