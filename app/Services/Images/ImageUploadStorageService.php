<?php

namespace App\Services\Images;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Throwable;

final class ImageUploadStorageService
{
    private ImageConversionService $image_conversion_service;

    public function __construct(ImageConversionService $image_conversion_service)
    {
        $this->image_conversion_service = $image_conversion_service;
    }

    public function StoreUploadedImage(
        UploadedFile $uploaded_file,
        string $directory,
        string $source_context,
    ): StoredImageFile {
        $conversion_result = null;
        $stored_absolute_path = null;

        try {
            $is_convertible_upload = in_array(
                strtolower($uploaded_file->getClientOriginalExtension()),
                ['jpg', 'jpeg', 'png'],
                true,
            );
            $source_path = $uploaded_file->getRealPath() ?: $uploaded_file->getPathname();
            $stored_extension = strtolower($uploaded_file->getClientOriginalExtension());
            $was_converted = false;

            if ($is_convertible_upload) {
                $conversion_result = $this->image_conversion_service->PrepareUploadedImage(
                    $uploaded_file,
                    ImageConversionOptions::ForOriginalDimensions($source_context),
                );
                $source_path = $conversion_result->selected_path;
                $stored_extension = $conversion_result->extension;
                $was_converted = $conversion_result->was_converted;
            }

            $destination_directory = public_path(trim($directory, '/\\'));
            File::ensureDirectoryExists($destination_directory);
            $filename = Str::uuid()->toString() . '.' . $stored_extension;
            $stored_absolute_path = $destination_directory . DIRECTORY_SEPARATOR . $filename;
            if (!File::copy($source_path, $stored_absolute_path)) {
                throw new \RuntimeException('The uploaded image could not be stored.');
            }

            return new StoredImageFile(
                filename: $filename,
                absolute_path: $stored_absolute_path,
                relative_path: trim($directory, '/\\') . '/' . $filename,
                was_converted: $was_converted,
            );
        } catch (Throwable $exception) {
            if ($stored_absolute_path !== null && is_file($stored_absolute_path)) {
                @unlink($stored_absolute_path);
            }

            throw $exception;
        } finally {
            if ($conversion_result instanceof ImageConversionResult) {
                $this->image_conversion_service->CleanupPreparedImage($conversion_result);
            }
        }
    }

    public function DeleteStoredImage(?string $absolute_path): void
    {
        if ($absolute_path !== null && is_file($absolute_path)) {
            @unlink($absolute_path);
        }
    }
}
