<?php

namespace App\Services\Images;

use App\Services\Images\Contracts\ImageConversionDriver;
use App\Services\Images\Exceptions\InvalidUploadedImageException;
use Intervention\Image\Facades\Image;
use Throwable;

final class InterventionImageConversionDriver implements ImageConversionDriver
{
    public function CanEncodeWebp(): bool
    {
        $configured_driver = strtolower((string) config('image.driver', 'gd'));

        if ($configured_driver === 'imagick') {
            if (!extension_loaded('imagick') || !class_exists('Imagick')) {
                return false;
            }

            try {
                $imagick = new \Imagick();
                $formats = array_map('strtoupper', $imagick->queryFormats('WEBP'));

                return in_array('WEBP', $formats, true);
            } catch (Throwable) {
                return false;
            }
        }

        return function_exists('imagewebp')
            && function_exists('imagetypes')
            && defined('IMG_WEBP')
            && (imagetypes() & IMG_WEBP) === IMG_WEBP;
    }

    public function ProcessImage(
        string $source_path,
        string $source_mime_type,
        ImageConversionOptions $conversion_options,
        string $baseline_path,
        ?string $webp_path,
        int $webp_quality,
    ): ImageEncodingResult {
        try {
            $image = Image::make($source_path);
            if ($source_mime_type === 'image/jpeg') {
                $image->orientate();
            }
        } catch (Throwable $exception) {
            throw new InvalidUploadedImageException('The uploaded image cannot be decoded.', 0, $exception);
        }

        if ($conversion_options->should_resize) {
            $this->ApplyImageStoreResize($image, $conversion_options);
            $image->save($baseline_path, null, $source_mime_type === 'image/png' ? 'png' : 'jpg');
        }

        $width_pixels = $image->width();
        $height_pixels = $image->height();
        $webp_encoder_available = $this->CanEncodeWebp();
        $webp_created = false;

        if ($webp_encoder_available && $webp_path !== null) {
            try {
                $image->save($webp_path, $webp_quality, 'webp');
                $webp_created = true;
            } catch (Throwable) {
                $webp_created = false;
            }
        }

        return new ImageEncodingResult(
            width_pixels: $width_pixels,
            height_pixels: $height_pixels,
            baseline_created: $conversion_options->should_resize,
            webp_encoder_available: $webp_encoder_available,
            webp_created: $webp_created,
        );
    }

    private function ApplyImageStoreResize($image, ImageConversionOptions $conversion_options): void
    {
        $original_width = $image->width();
        $original_height = $image->height();

        if ($original_width > $original_height) {
            $image->resize($conversion_options->resize_width, null, function ($constraint) {
                $constraint->aspectRatio();
            });

            return;
        }

        if ($original_width < $original_height) {
            $image->resize(null, $conversion_options->resize_height, function ($constraint) {
                $constraint->aspectRatio();
            });

            return;
        }

        if ($conversion_options->resize_width > $conversion_options->resize_height) {
            $image->resize(null, $conversion_options->resize_width, function ($constraint) {
                $constraint->aspectRatio();
            });

            return;
        }

        $image->resize($conversion_options->resize_height, null, function ($constraint) {
            $constraint->aspectRatio();
        });
    }
}
