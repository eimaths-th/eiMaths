<?php
namespace App\Services\Faq;
use App\Models\Faq;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
final class FaqReadReport
{
    public function Build(FaqReadRange $range, string $locale): LengthAwarePaginator
    {
        $totals = DB::table('faq_daily_reads')->select('faq_reference_id')
            ->selectRaw('SUM(read_count) AS read_count')
            ->whereBetween('read_date', [$range->start_date, $range->end_date])->groupBy('faq_reference_id');
        $candidates = DB::table('faqs')->select('id AS faq_reference_id')->union(
            DB::table('faq_daily_reads')->select('faq_reference_id')
                ->whereBetween('read_date', [$range->start_date, $range->end_date])->where('read_count', '>', 0)
        );
        $rows = DB::query()->fromSub($candidates, 'faq_candidates')
            ->leftJoinSub($totals, 'totals', 'totals.faq_reference_id', '=', 'faq_candidates.faq_reference_id')
            ->select('faq_candidates.faq_reference_id')->selectRaw('COALESCE(totals.read_count, 0) AS read_count')
            ->orderByDesc('read_count')->orderBy('faq_candidates.faq_reference_id')
            ->paginate(20, ['*'], 'page', $range->page);
        $faq_ids = $rows->getCollection()->pluck('faq_reference_id')->map(fn ($faq_id): int => (int) $faq_id)->all();
        $current = Faq::with('category')->whereKey($faq_ids)->get()->keyBy('id');
        $deleted_ids = array_values(array_diff($faq_ids, $current->keys()->all()));
        $snapshots = collect();
        if ($deleted_ids !== []) {
            $latest_dates = DB::table('faq_daily_reads')->select('faq_reference_id')
                ->selectRaw('MAX(read_date) AS latest_read_date')->whereIn('faq_reference_id', $deleted_ids)->groupBy('faq_reference_id');
            $snapshots = DB::table('faq_daily_reads AS history')->joinSub($latest_dates, 'latest', function ($join): void {
                $join->on('history.faq_reference_id', '=', 'latest.faq_reference_id')
                    ->on('history.read_date', '=', 'latest.latest_read_date');
            })->select('history.*')->get()->keyBy('faq_reference_id');
        }
        return $rows->through(function ($row) use ($current, $snapshots, $locale): array {
            $faq = $current->get((int) $row->faq_reference_id);
            $snapshot = $snapshots->get((int) $row->faq_reference_id);
            if ($faq === null && $snapshot === null) {
                throw new \RuntimeException('Missing FAQ historical label');
            }
            return ['faq_reference_id' => (int) $row->faq_reference_id, 'read_count' => (int) $row->read_count,
                'question' => $faq !== null ? $faq->LocalizedQuestion($locale)
                    : ($locale === 'en' && filled($snapshot->question_en) ? $snapshot->question_en : $snapshot->question_th),
                'category' => $faq !== null ? ($faq->category?->LocalizedName($locale) ?? '-')
                    : ($locale === 'en' && filled($snapshot->category_en) ? $snapshot->category_en : $snapshot->category_th),
                'is_deleted' => $faq === null];
        });
    }
}
