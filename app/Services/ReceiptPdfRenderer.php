<?php

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\File;

class ReceiptPdfRenderer
{
    public function Render(string $html): string
    {
        // Runtime font metrics must not use the machine-specific cache in public/fonts.
        $font_cache = storage_path('framework/cache/receipt-pdf-fonts');
        File::ensureDirectoryExists($font_cache);
        $options = new Options(array_replace(config('dompdf.options', []), [
            'font_dir' => $font_cache,
            'font_cache' => $font_cache,
            'chroot' => [public_path(), $font_cache],
            'enable_remote' => false,
            'enable_php' => false,
            'enable_javascript' => false,
            'enable_font_subsetting' => true,
            'default_paper_size' => 'a4',
        ]));
        $pdf = new Dompdf($options);
        $pdf->setBasePath(public_path());
        $pdf->loadHtml($html, 'UTF-8');
        $pdf->render();

        return $pdf->output();
    }

    public function AssetUrl(string $relative_path): string
    {
        $root = realpath(public_path());
        $path = realpath(public_path($relative_path));
        if ($root === false || $path === false || !is_file($path) || !is_readable($path)
            || !str_starts_with($path, $root.DIRECTORY_SEPARATOR)) {
            return '';
        }

        // Dompdf accepts file://C:/... on Windows and file:///... on Linux.
        return 'file://'.str_replace('\\', '/', $path);
    }
}
