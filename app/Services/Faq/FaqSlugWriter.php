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
        return $this->Commit(function () use ($attributes): Faq {
            $faq = Faq::create($attributes);
            $this->Assign((int) $faq->id);
            return $faq->refresh();
        });
    }

    public function Assign(int $faq_id): string
    {
        return $this->Commit(function () use ($faq_id): string {
            $faq = Faq::query()->lockForUpdate()->findOrFail($faq_id);
            if (config('faq-urls.enabled', false)) {
                app(FaqLocalizedSlugs::class)->Initialize($faq);
                return (string) $faq->slug;
            }
            if (is_string($faq->slug) && $faq->slug !== '') return $faq->slug;
            $source = filled($faq->question_en) ? $faq->question_en : $faq->question;
            $base = mb_strtolower(strip_tags($source), 'UTF-8');
            $base = preg_replace('/[^\p{L}\p{M}\p{N}]+/u', '-', $base) ?? '';
            $base = trim(mb_substr(trim($base, '-'), 0, self::BASE_LENGTH, 'UTF-8'), '-');
            $base = $base === '' ? 'faq-'.$faq_id : $base;
            for ($collision_number = 0; ; $collision_number++) {
                $candidate = $base.($collision_number === 0 ? '' : '-'.$faq_id);
                if ($collision_number > 1) $candidate .= '-'.($collision_number - 1);
                try {
                    DB::transaction(function () use ($faq_id, $candidate): void {
                        DB::table('faqs')->where('id', $faq_id)->update(['slug' => $candidate]);
                    });
                    return $candidate;
                } catch (UniqueConstraintViolationException $exception) {
                    // Preserve the legacy slug-only allocation behavior while disabled.
                }
            }
        });
    }

    private function Commit(\Closure $changes): mixed
    {
        try {
            return DB::transaction($changes, 3);
        } catch (\PDOException $exception) {
            if (!config('faq-urls.enabled', false)) throw $exception;
            throw new UnavailableFaqUrls('faq.urls.write');
        }
    }

    public function Update(int $faq_id, array $attributes): Faq
    {
        return $this->Commit(function () use ($faq_id, $attributes): Faq {
            $faq = Faq::query()->lockForUpdate()->findOrFail($faq_id);
            $attributes['published_at'] = $attributes['is_published']
                ? ($faq->published_at ?? now()) : null;
            $faq->fill($attributes)->save();
            if (config('faq-urls.enabled', false)) {
                app(FaqLocalizedSlugs::class)->Synchronize($faq);
            }
            return $faq->refresh();
        });
    }
}
