<?php
namespace App\Services\Faq;
use App\Models\Faq;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
final class FaqSlugWriter
{
    private const BASE_LENGTH = 120;
    public function Create(array $attributes): Faq
    {
        return DB::transaction(function () use ($attributes): Faq {
            $faq = Faq::create($attributes);
            $this->Assign((int) $faq->id);
            return $faq->refresh();
        }, 3);
    }
    public function Assign(int $faq_id): string
    {
        return DB::transaction(function () use ($faq_id): string {
            $faq = Faq::query()->lockForUpdate()->findOrFail($faq_id);
            if (is_string($faq->slug) && $faq->slug !== '') {
                return $faq->slug;
            }
            $source = filled($faq->question_en) ? $faq->question_en : $faq->question;
            $base = mb_strtolower(strip_tags($source), 'UTF-8');
            $base = preg_replace('/[^\p{L}\p{M}\p{N}]+/u', '-', $base) ?? '';
            $base = trim(mb_substr(trim($base, '-'), 0, self::BASE_LENGTH, 'UTF-8'), '-');
            $base = $base === '' ? 'faq-'.$faq_id : $base;
            for ($collision_number = 0; ; $collision_number++) {
                $candidate = $base.($collision_number === 0 ? '' : '-'.$faq_id);
                if ($collision_number > 1) {
                    $candidate .= '-'.($collision_number - 1);
                }
                try {
                    // A savepoint isolates a collision; only this unique column is changed.
                    DB::transaction(function () use ($faq_id, $candidate): void {
                        DB::table('faqs')->where('id', $faq_id)->update(['slug' => $candidate]);
                    });
                    return $candidate;
                } catch (UniqueConstraintViolationException $exception) {
                    // A unique failure from this slug-only update selects the next candidate.
                    // Other query failures propagate and roll back creation/backfill for this row.
                }
            }
        }, 3);
    }
}
