<?php

namespace App\Services\Images;

final class ImageConversionOptions
{
    public readonly string $source_context;
    public readonly bool $should_resize;
    public readonly ?int $resize_height;
    public readonly ?int $resize_width;

    private function __construct(
        string $source_context,
        bool $should_resize,
        ?int $resize_height,
        ?int $resize_width,
    ) {
        if (trim($source_context) === '') {
            throw new \InvalidArgumentException('Image conversion source context is required.');
        }

        if ($should_resize && ($resize_height === null || $resize_width === null || $resize_height <= 0 || $resize_width <= 0)) {
            throw new \InvalidArgumentException('Image conversion resize dimensions must be positive.');
        }

        if (!$should_resize && ($resize_height !== null || $resize_width !== null)) {
            throw new \InvalidArgumentException('Original-dimension image conversion cannot include resize dimensions.');
        }

        $this->source_context = $source_context;
        $this->should_resize = $should_resize;
        $this->resize_height = $resize_height;
        $this->resize_width = $resize_width;
    }

    public static function ForOriginalDimensions(string $source_context): self
    {
        return new self($source_context, false, null, null);
    }

    public static function ForImageStoreResize(string $source_context, int $resize_height, int $resize_width): self
    {
        return new self($source_context, true, $resize_height, $resize_width);
    }
}
