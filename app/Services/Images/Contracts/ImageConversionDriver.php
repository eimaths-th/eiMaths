<?php

namespace App\Services\Images\Contracts;

use App\Services\Images\ImageConversionOptions;
use App\Services\Images\ImageEncodingResult;

interface ImageConversionDriver
{
    public function CanEncodeWebp(): bool;

    public function ProcessImage(
        string $source_path,
        string $source_mime_type,
        ImageConversionOptions $conversion_options,
        string $baseline_path,
        ?string $webp_path,
        int $webp_quality,
    ): ImageEncodingResult;
}
