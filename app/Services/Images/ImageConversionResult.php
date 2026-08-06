<?php

namespace App\Services\Images;

final class ImageConversionResult
{
    public readonly string $selected_path;
    public readonly string $extension;
    public readonly string $mime_type;
    public readonly int $size_bytes;
    public readonly int $width_pixels;
    public readonly int $height_pixels;
    public readonly bool $was_converted;
    public readonly ?ImageFallbackReason $fallback_reason;
    public readonly string $conversion_id;

    public function __construct(
        string $selected_path,
        string $extension,
        string $mime_type,
        int $size_bytes,
        int $width_pixels,
        int $height_pixels,
        bool $was_converted,
        ?ImageFallbackReason $fallback_reason,
        string $conversion_id,
    ) {
        $this->selected_path = $selected_path;
        $this->extension = $extension;
        $this->mime_type = $mime_type;
        $this->size_bytes = $size_bytes;
        $this->width_pixels = $width_pixels;
        $this->height_pixels = $height_pixels;
        $this->was_converted = $was_converted;
        $this->fallback_reason = $fallback_reason;
        $this->conversion_id = $conversion_id;
    }
}
