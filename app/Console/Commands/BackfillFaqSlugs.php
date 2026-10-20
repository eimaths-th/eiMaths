<?php
namespace App\Console\Commands;
use App\Services\Faq\FaqSlugWriter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
final class BackfillFaqSlugs extends Command
{
    protected $signature = 'faq:backfill-slugs';
    protected $description = 'Assign missing FAQ slugs without changing content or timestamps';
    public function handle(FaqSlugWriter $slug_writer): int
    {
        $assigned_count = 0;
        foreach (DB::table('faqs')->where(function ($query): void {
            $query->whereNull('slug')->orWhere('slug', '');
        })->orderBy('id')->lazyById(200, 'id') as $faq_row) {
            $slug_writer->Assign((int) $faq_row->id);
            $assigned_count++;
        }
        $this->info('Assigned FAQ slugs: '.$assigned_count);
        return self::SUCCESS;
    }
}
