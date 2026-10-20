<?php

namespace App\Console\Commands;

use App\Models\Faq;
use App\Services\Faq\FaqLocalizedSlugs;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class BackfillFaqSlugs extends Command
{
    protected $signature = 'faq:backfill-slugs';
    protected $description = 'Initialize missing localized FAQ URLs without changing content or timestamps';

    public function handle(FaqLocalizedSlugs $localized_slugs): int
    {
        $initialized_count = 0;
        $assigned_count = 0;
        try {
            foreach (Faq::query()->orderBy('id')->lazyById(200) as $faq_row) {
                $result = DB::transaction(function () use ($faq_row, $localized_slugs): array {
                    $faq = Faq::query()->lockForUpdate()->findOrFail($faq_row->id);
                    $was_missing = !is_string($faq->slug) || $faq->slug === '';
                    $initialized = $localized_slugs->Initialize($faq);
                    return ['initialized' => $initialized, 'assigned' => $was_missing];
                }, 3);
                if ($result['initialized']) $initialized_count++;
                if ($result['assigned']) $assigned_count++;
            }
        } catch (\Throwable $exception) {
            Log::error('faq.urls.backfill_failed', ['error_code' => 'FAQ_CONTENT_UNAVAILABLE',
                'exception_class' => get_class($exception), 'initialized_count' => $initialized_count]);
            $this->error('FAQ URL initialization failed. Inspect the feature diagnostic before retrying.');
            return self::FAILURE;
        }
        $this->info('Assigned FAQ slugs: '.$assigned_count);
        $this->info('Initialized FAQ localizations: '.$initialized_count);
        return self::SUCCESS;
    }
}
