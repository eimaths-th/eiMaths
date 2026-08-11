<?php

namespace App\Domain\Affiliate\Services;

use App\Domain\Affiliate\ValueObjects\StoredAffiliateMedia;
use App\Services\Images\ImageSourceContext;
use App\Services\Images\ImageUploadStorageService;
use App\Services\Images\StoredImageFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use InvalidArgumentException;

class AffiliateMediaStorageService
{
    private const IMAGE_MIME_EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];

    private const VIDEO_MIME_EXTENSIONS = [
        'video/mp4' => 'mp4',
        'video/quicktime' => 'mov',
        'video/x-msvideo' => 'avi',
        'video/x-ms-wmv' => 'wmv',
    ];

    public function __construct(private readonly ImageUploadStorageService $image_upload_storage_service)
    {
    }

    public function Store(UploadedFile $file, string $type): StoredAffiliateMedia
    {
        $type = strtolower(trim($type));
        $mime_type = strtolower((string) $file->getMimeType());
        $extension_map = $type === 'image' ? self::IMAGE_MIME_EXTENSIONS : ($type === 'video' ? self::VIDEO_MIME_EXTENSIONS : []);
        if (! isset($extension_map[$mime_type])) {
            throw new InvalidArgumentException('Affiliate media type does not match the server-observed MIME type.');
        }

        if ($type === 'image') {
            $stored_image = $this->image_upload_storage_service->StoreUploadedImage(
                $file,
                'uploads/affiliate/image',
                ImageSourceContext::AFFILIATE_CONTENT_IMAGE,
            );

            return new StoredAffiliateMedia(
                type: 'image',
                relative_path: $stored_image->relative_path,
                absolute_path: $stored_image->absolute_path,
                mime_type: $mime_type,
                extension: pathinfo($stored_image->relative_path, PATHINFO_EXTENSION),
            );
        }

        $directory = public_path('uploads/affiliate/video');
        File::ensureDirectoryExists($directory);
        $extension = $extension_map[$mime_type];
        $filename = Str::uuid()->toString() . '.' . $extension;
        $absolute_path = $directory . DIRECTORY_SEPARATOR . $filename;
        $file->move($directory, $filename);

        return new StoredAffiliateMedia(
            type: 'video',
            relative_path: 'uploads/affiliate/video/' . $filename,
            absolute_path: $absolute_path,
            mime_type: $mime_type,
            extension: $extension,
        );
    }

    public function Delete(StoredAffiliateMedia $media): void
    {
        if ($media->type === 'image') {
            $this->image_upload_storage_service->DeleteStoredImage($media->absolute_path);
            return;
        }

        if (is_file($media->absolute_path)) {
            @unlink($media->absolute_path);
        }
    }
}
