<?php
namespace App\Services\Faq;
use Carbon\CarbonImmutable;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
final class FaqReadRecorder
{
    public function Record(int $faq_id, string $event_key): void
    {
        try {
            DB::transaction(function () use ($faq_id, $event_key): void {
                $faq = DB::table('faqs')->where('id', $faq_id)->lockForUpdate()->first();
                abort_unless($faq && (bool) $faq->is_published, 404);
                $category = DB::table('faq_categories')->where('id', $faq->category_id)->lockForUpdate()->first();
                abort_unless($category && (bool) $category->is_published, 404);
                $accepted_at = CarbonImmutable::now('Asia/Bangkok');
                $read_date = $accepted_at->toDateString();
                $snapshots = ['question_th' => $faq->question, 'question_en' => $faq->question_en,
                    'category_th' => $category->name, 'category_en' => $category->name_en];
                $daily = DB::table('faq_daily_reads')->where('faq_reference_id', $faq_id)
                    ->where('read_date', $read_date)->first();
                $daily_id = $daily?->id ?? DB::table('faq_daily_reads')->insertGetId(array_merge(
                    ['faq_reference_id' => $faq_id, 'read_date' => $read_date, 'read_count' => 0], $snapshots
                ));
                try {
                    DB::table('faq_read_receipts')->insert(['event_key' => $event_key, 'daily_read_id' => $daily_id,
                        'first_recorded_at' => $accepted_at->utc()->format('Y-m-d H:i:s.u')]);
                } catch (UniqueConstraintViolationException $exception) {
                    throw new DuplicateFaqRead($exception);
                }
                DB::table('faq_daily_reads')->where('id', $daily_id)->increment('read_count', 1, $snapshots);
            }, 3);
        } catch (DuplicateFaqRead $exception) {
            // The outer transaction has rolled back before inspecting the committed receipt.
            $existing = DB::table('faq_read_receipts')->join('faq_daily_reads',
                'faq_daily_reads.id', '=', 'faq_read_receipts.daily_read_id')
                ->where('event_key', $event_key)->first(['faq_reference_id']);
            if ($existing === null) {
                throw $exception;
            }
            abort_unless((int) $existing->faq_reference_id === $faq_id, 409);
        }
    }
}
