<?php

namespace App\Jobs;

use App\Domain\Ebook\PdfConverter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class ConvertEbookPdf implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 1200;

    public function __construct(public int $versionId) { $this->onQueue('ebooks'); }

    public function handle(PdfConverter $converter): void { $converter->convert($this->versionId); }
}
