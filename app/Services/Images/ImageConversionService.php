<?php

namespace App\Services\Images;

use App\Services\Images\Contracts\ImageConversionDriver;
use App\Services\Images\Exceptions\InvalidUploadedImageException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

final class ImageConversionService
{
    private const JPEG_MIME_TYPE = 'image/jpeg';
    private const PNG_MIME_TYPE = 'image/png';
    private const WEBP_MIME_TYPE = 'image/webp';

    private ImageConversionDriver $conversion_driver;
    private AnimatedPngDetector $animated_png_detector;
    private int $webp_quality;
    private int $max_pixels;
    private string $temporary_directory;

    public function __construct(
        ImageConversionDriver $conversion_driver,
        ?AnimatedPngDetector $animated_png_detector = null,
    ) {
        $this->conversion_driver = $conversion_driver;
        $this->animated_png_detector = $animated_png_detector ?? new AnimatedPngDetector();
        $this->webp_quality = $this->PositiveIntegerConfig('image-conversion.webp_quality', 1, 100);
        $this->max_pixels = $this->PositiveIntegerConfig('image-conversion.max_pixels', 1, PHP_INT_MAX);
        $temporary_directory = config('image-conversion.temporary_directory');

        if (!is_string($temporary_directory) || trim($temporary_directory) === '') {
            throw new \InvalidArgumentException('Image conversion temporary directory is invalid.');
        }

        $this->temporary_directory = $temporary_directory;
    }

    public function PrepareUploadedImage(
        UploadedFile $uploaded_file,
        ImageConversionOptions $conversion_options,
    ): ImageConversionResult {
        $conversion_id = (string) Str::uuid();
        $started_at = microtime(true);
        $source_path = $uploaded_file->getRealPath() ?: $uploaded_file->getPathname();
        $source_extension = strtolower($uploaded_file->getClientOriginalExtension());
        $source_mime_type = $this->DetectSourceMimeType($source_path);
        $this->AssertEligibleSource($source_extension, $source_mime_type);
        [$width_pixels, $height_pixels] = $this->ReadImageDimensions($source_path);
        $original_size_bytes = $this->ReadFileSize($source_path);

        if ($this->ExceedsPixelLimit($width_pixels, $height_pixels)) {
            return $this->CreateAndLogFallbackResult(
                source_path: $source_path,
                source_extension: $source_extension,
                source_mime_type: $source_mime_type,
                width_pixels: $width_pixels,
                height_pixels: $height_pixels,
                original_size_bytes: $original_size_bytes,
                conversion_id: $conversion_id,
                source_context: $conversion_options->source_context,
                fallback_reason: ImageFallbackReason::PIXEL_LIMIT_EXCEEDED,
                started_at: $started_at,
            );
        }

        if ($source_mime_type === self::PNG_MIME_TYPE && $this->animated_png_detector->IsAnimated($source_path)) {
            return $this->CreateAndLogFallbackResult(
                source_path: $source_path,
                source_extension: $source_extension,
                source_mime_type: $source_mime_type,
                width_pixels: $width_pixels,
                height_pixels: $height_pixels,
                original_size_bytes: $original_size_bytes,
                conversion_id: $conversion_id,
                source_context: $conversion_options->source_context,
                fallback_reason: ImageFallbackReason::ANIMATED_PNG_UNSUPPORTED,
                started_at: $started_at,
            );
        }

        $this->EnsureTemporaryDirectory();
        $baseline_path = $this->temporary_directory . DIRECTORY_SEPARATOR . $conversion_id . '-baseline.' . $source_extension;
        $webp_path = $this->temporary_directory . DIRECTORY_SEPARATOR . $conversion_id . '-candidate.webp';

        try {
            $encoding_result = $this->conversion_driver->ProcessImage(
                source_path: $source_path,
                source_mime_type: $source_mime_type,
                conversion_options: $conversion_options,
                baseline_path: $baseline_path,
                webp_path: $webp_path,
                webp_quality: $this->webp_quality,
            );
        } catch (InvalidUploadedImageException $exception) {
            $this->DeletePreparedFiles($conversion_id);

            throw $exception;
        } catch (Throwable) {
            return $this->CreateAndLogFallbackResult(
                source_path: $source_path,
                source_extension: $source_extension,
                source_mime_type: $source_mime_type,
                width_pixels: $width_pixels,
                height_pixels: $height_pixels,
                original_size_bytes: $original_size_bytes,
                conversion_id: $conversion_id,
                source_context: $conversion_options->source_context,
                fallback_reason: ImageFallbackReason::CONVERSION_FAILED,
                started_at: $started_at,
            );
        }

        $baseline_path = $conversion_options->should_resize && $encoding_result->baseline_created
            ? $baseline_path
            : $source_path;
        $baseline_size_bytes = $this->ReadFileSize($baseline_path);
        $selected_width_pixels = $conversion_options->should_resize
            ? $encoding_result->width_pixels
            : $width_pixels;
        $selected_height_pixels = $conversion_options->should_resize
            ? $encoding_result->height_pixels
            : $height_pixels;

        if (!$encoding_result->webp_encoder_available) {
            return $this->CreateAndLogFallbackResult(
                source_path: $baseline_path,
                source_extension: $source_extension,
                source_mime_type: $source_mime_type,
                width_pixels: $selected_width_pixels,
                height_pixels: $selected_height_pixels,
                original_size_bytes: $original_size_bytes,
                conversion_id: $conversion_id,
                source_context: $conversion_options->source_context,
                fallback_reason: ImageFallbackReason::WEBP_ENCODER_UNAVAILABLE,
                started_at: $started_at,
                selected_size_bytes: $baseline_size_bytes,
            );
        }

        if (!$encoding_result->webp_created || !is_file($webp_path)) {
            return $this->CreateAndLogFallbackResult(
                source_path: $baseline_path,
                source_extension: $source_extension,
                source_mime_type: $source_mime_type,
                width_pixels: $selected_width_pixels,
                height_pixels: $selected_height_pixels,
                original_size_bytes: $original_size_bytes,
                conversion_id: $conversion_id,
                source_context: $conversion_options->source_context,
                fallback_reason: ImageFallbackReason::CONVERSION_FAILED,
                started_at: $started_at,
                selected_size_bytes: $baseline_size_bytes,
            );
        }

        $webp_size_bytes = $this->ReadFileSize($webp_path);
        if ($webp_size_bytes < $baseline_size_bytes) {
            return $this->CreateAndLogResult(
                selected_path: $webp_path,
                extension: 'webp',
                mime_type: self::WEBP_MIME_TYPE,
                size_bytes: $webp_size_bytes,
                width_pixels: $selected_width_pixels,
                height_pixels: $selected_height_pixels,
                was_converted: true,
                fallback_reason: null,
                conversion_id: $conversion_id,
                source_context: $conversion_options->source_context,
                original_mime_type: $source_mime_type,
                original_size_bytes: $original_size_bytes,
                started_at: $started_at,
            );
        }

        return $this->CreateAndLogFallbackResult(
            source_path: $baseline_path,
            source_extension: $source_extension,
            source_mime_type: $source_mime_type,
            width_pixels: $selected_width_pixels,
            height_pixels: $selected_height_pixels,
            original_size_bytes: $original_size_bytes,
            conversion_id: $conversion_id,
            source_context: $conversion_options->source_context,
            fallback_reason: ImageFallbackReason::WEBP_NOT_SMALLER,
            started_at: $started_at,
            selected_size_bytes: $baseline_size_bytes,
        );
    }

    public function CleanupPreparedImage(ImageConversionResult $conversion_result): void
    {
        if (!preg_match('/\A[a-f0-9-]{36}\z/', $conversion_result->conversion_id)) {
            return;
        }

        $this->DeletePreparedFiles($conversion_result->conversion_id);
    }

    private function DetectSourceMimeType(string $source_path): string
    {
        if (!is_file($source_path) || !is_readable($source_path)) {
            throw new InvalidUploadedImageException('The uploaded image is not readable.');
        }

        $mime_type = (new \finfo(FILEINFO_MIME_TYPE))->file($source_path);
        if (!is_string($mime_type) || $mime_type === '') {
            throw new InvalidUploadedImageException('The uploaded image type cannot be detected.');
        }

        return strtolower($mime_type);
    }

    private function AssertEligibleSource(string $source_extension, string $source_mime_type): void
    {
        $is_jpeg = $source_mime_type === self::JPEG_MIME_TYPE && in_array($source_extension, ['jpg', 'jpeg'], true);
        $is_png = $source_mime_type === self::PNG_MIME_TYPE && $source_extension === 'png';

        if (!$is_jpeg && !$is_png) {
            throw new InvalidUploadedImageException('Only valid JPEG and PNG uploads can be prepared.');
        }
    }

    /** @return array{0: int, 1: int} */
    private function ReadImageDimensions(string $source_path): array
    {
        $image_size = @getimagesize($source_path);
        if (!is_array($image_size) || !isset($image_size[0], $image_size[1]) || $image_size[0] <= 0 || $image_size[1] <= 0) {
            throw new InvalidUploadedImageException('The uploaded image header is invalid.');
        }

        return [(int) $image_size[0], (int) $image_size[1]];
    }

    private function ExceedsPixelLimit(int $width_pixels, int $height_pixels): bool
    {
        return $width_pixels > intdiv($this->max_pixels, $height_pixels);
    }

    private function EnsureTemporaryDirectory(): void
    {
        if (!File::isDirectory($this->temporary_directory)) {
            File::makeDirectory($this->temporary_directory, 0700, true, true);
        }

        @chmod($this->temporary_directory, 0700);
    }

    private function DeletePreparedFiles(string $conversion_id): void
    {
        if (!preg_match('/\A[a-f0-9-]{36}\z/', $conversion_id) || !File::isDirectory($this->temporary_directory)) {
            return;
        }

        foreach (glob($this->temporary_directory . DIRECTORY_SEPARATOR . $conversion_id . '-*') ?: [] as $prepared_path) {
            if (is_file($prepared_path) || is_link($prepared_path)) {
                @unlink($prepared_path);
            }
        }
    }

    private function PositiveIntegerConfig(string $key, int $minimum, int $maximum): int
    {
        $value = config($key);
        if (is_int($value)) {
            $integer_value = $value;
        } elseif (is_string($value) && ctype_digit($value)) {
            $integer_value = (int) $value;
        } else {
            throw new \InvalidArgumentException("{$key} must be an integer.");
        }

        if ($integer_value < $minimum || $integer_value > $maximum) {
            throw new \InvalidArgumentException("{$key} is outside its allowed range.");
        }

        return $integer_value;
    }

    private function ReadFileSize(string $path): int
    {
        $size_bytes = @filesize($path);
        if ($size_bytes === false) {
            throw new \RuntimeException('The prepared image size cannot be read.');
        }

        return (int) $size_bytes;
    }

    private function CreateAndLogFallbackResult(
        string $source_path,
        string $source_extension,
        string $source_mime_type,
        int $width_pixels,
        int $height_pixels,
        int $original_size_bytes,
        string $conversion_id,
        string $source_context,
        ImageFallbackReason $fallback_reason,
        float $started_at,
        ?int $selected_size_bytes = null,
    ): ImageConversionResult {
        return $this->CreateAndLogResult(
            selected_path: $source_path,
            extension: $source_extension,
            mime_type: $source_mime_type,
            size_bytes: $selected_size_bytes ?? $original_size_bytes,
            width_pixels: $width_pixels,
            height_pixels: $height_pixels,
            was_converted: false,
            fallback_reason: $fallback_reason,
            conversion_id: $conversion_id,
            source_context: $source_context,
            original_mime_type: $source_mime_type,
            original_size_bytes: $original_size_bytes,
            started_at: $started_at,
        );
    }

    private function CreateAndLogResult(
        string $selected_path,
        string $extension,
        string $mime_type,
        int $size_bytes,
        int $width_pixels,
        int $height_pixels,
        bool $was_converted,
        ?ImageFallbackReason $fallback_reason,
        string $conversion_id,
        string $source_context,
        string $original_mime_type,
        int $original_size_bytes,
        float $started_at,
    ): ImageConversionResult {
        Log::info('image_conversion.completed', [
            'conversion_id' => $conversion_id,
            'source_context' => $source_context,
            'outcome' => $was_converted ? 'converted' : 'fallback',
            'original_mime_type' => $original_mime_type,
            'selected_mime_type' => $mime_type,
            'original_size_bytes' => $original_size_bytes,
            'selected_size_bytes' => $size_bytes,
            'width_pixels' => $width_pixels,
            'height_pixels' => $height_pixels,
            'fallback_reason' => $fallback_reason?->value,
            'duration_ms' => round((microtime(true) - $started_at) * 1000, 2),
        ]);

        return new ImageConversionResult(
            selected_path: $selected_path,
            extension: $extension,
            mime_type: $mime_type,
            size_bytes: $size_bytes,
            width_pixels: $width_pixels,
            height_pixels: $height_pixels,
            was_converted: $was_converted,
            fallback_reason: $fallback_reason,
            conversion_id: $conversion_id,
        );
    }
}
