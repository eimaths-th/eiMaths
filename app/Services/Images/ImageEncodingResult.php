<?php

namespace App\Services\Images;

final class ImageEncodingResult
{
    public readonly int $width_pixels;
    public readonly int $height_pixels;
    public readonly bool $baseline_created;
    public readonly bool $webp_encoder_available;
    public readonly bool $webp_created;

    public function __construct(
        int $width_pixels,
        int $height_pixels,
        bool $baseline_created,
        bool $webp_encoder_available,
        bool $webp_created,
    ) {
        $this->width_pixels = $width_pixels;
        $this->height_pixels = $height_pixels;
        $this->baseline_created = $baseline_created;
        $this->webp_encoder_available = $webp_encoder_available;
        $this->webp_created = $webp_created;
    }
}
