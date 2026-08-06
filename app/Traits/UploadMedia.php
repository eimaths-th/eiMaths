<?php

namespace App\Traits;

use App\Services\Images\ImageConversionOptions;
use App\Services\Images\ImageConversionService;
use App\Services\Images\ImageSourceContext;
use Carbon\Carbon;
use Google_Client;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Modules\AmazonS3\Http\Controllers\AmazonS3Controller;
use Modules\BunnyStorage\Service\BunnyVideoStream;
use Modules\Setting\Entities\MediaManager;
use Modules\Setting\Entities\UsedMedia;
use Modules\Setting\Http\Controllers\GoogleDriveController;
use Modules\Storage\Services\StorageUpload;

trait UploadMedia
{

    public static function deleteFile($url)
    {
        if (strpos($url, 'amazonaws.com') != false) {

            return (new AmazonS3Controller())->deleteFile($url);

        } elseif (strpos($url, 'digitaloceanspaces.com') != false && isModuleActive('Storage')) {

            return StorageUpload::deleteFromStorage('do', $url);

        } elseif (strpos($url, 'drive.google.com') != false) {

            return (new GoogleDriveController(new Google_Client()))->deleteFile($url);

        } elseif (strpos($url, 'wasabisys.com') != false && isModuleActive('Storage')) {

            return StorageUpload::deleteFromStorage('Wasabi', $url);

        } elseif (strpos($url, 'backblazeb2.com') != false && isModuleActive('Storage')) {

            return StorageUpload::deleteFromStorage('b2', $url);

        } elseif (strpos($url, 'dropboxusercontent.com') != false && isModuleActive('Storage')) {

            return StorageUpload::deleteFromStorage('dropbox', $url);

        } elseif (strpos($url, 'b-cdn.net') != false && isModuleActive('Storage')) {

            return (new BunnyVideoStream())->deleteFile($url);

        } elseif (strpos($url, 'contabostorage.com') != false && isModuleActive('Storage')) {

            return StorageUpload::deleteFromStorage('contabo', $url);

        } else {
            if (File::exists($url)) {
                File::delete($url);
                return true;
            } else {
                return false;
            }
        }
    }

    public static function mediaUpload($file = null)
    {
        if (!isset($file)) {
            abort('404');
        }

        $originalName = $file->getClientOriginalName();
        $fileExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $ignoreType = [
            'php',
            'js',
            'css',
            'md',
            'sh',
            'exe',
            'bin',
            'bat'
        ];

        if (in_array(strtolower($fileExtension), $ignoreType)) {
            abort('403');

        }
        $conversion_service = app(ImageConversionService::class);
        $conversion_result = null;
        $is_convertible_image = $file instanceof UploadedFile
            && in_array($fileExtension, ['jpg', 'jpeg', 'png'], true);

        try {
            if ($is_convertible_image) {
                $conversion_result = $conversion_service->PrepareUploadedImage(
                    $file,
                    ImageConversionOptions::ForOriginalDimensions(ImageSourceContext::MEDIA_MANAGER),
                );
            }

            $selected_file_path = $conversion_result?->selected_path ?? $file->getRealPath() ?: $file->getPathname();
            $selected_extension = $conversion_result?->extension ?? $fileExtension;
            $selected_size_bytes = $conversion_result?->size_bytes ?? self::ReadMediaFileSize($selected_file_path);
            $stored_name = $conversion_result !== null
                ? Str::uuid()->toString() . '.' . $selected_extension
                : $originalName;
            $host = Settings('active_storage') ?: 'LocalStorage';
            $domain = SaasDomain();
            $current_date = Carbon::now()->format('d-m-Y');
            $dir = 'public/uploads/' . $domain . '/files/' . $current_date;
            $data = [
                'original_name' => $originalName,
                'extension' => $selected_extension,
                'type' => self::extToType($selected_extension),
                'size' => round($selected_size_bytes / 1024),
            ];

            if (in_array($host, ['AmazonS3', 'GoogleDrive', 'BunnyCDN', 'DigitalOcean', 'Wasabi', 'Backblaze', 'Dropbox', 'Contabo'])) {
                $file_contents = file_get_contents($selected_file_path);
                if ($file_contents === false) {
                    throw new \RuntimeException('The media content could not be read.');
                }

                $file_contents_base64 = base64_encode($file_contents);
                switch ($host) {
                    case 'GoogleDrive':
                        $drive = new GoogleDriveController(new Google_Client());
                        $file_url = $drive->storeContentInGDrive(base64_decode($file_contents_base64), $stored_name);
                        break;

                    case 'AmazonS3':
                        $file_url = AmazonS3Controller::s3Upload('s3', $file_contents_base64, $selected_extension);
                        break;

                    case 'BunnyCDN':
                        $file_url = self::uploadToStorage('google', $file_contents_base64, $selected_extension);
                        break;

                    default:
                        $file_url = null;
                        break;
                }

                if ($file_url === null) {
                    throw new \RuntimeException('The remote media provider did not return a file URL.');
                }

                $data['file_name'] = $file_url;
                $data['storage'] = $host;
            } else {
                if (!File::isDirectory($dir)) {
                    File::makeDirectory($dir, 0777, true, true);
                }

                $destination_path = $dir . '/' . $stored_name;
                if ($conversion_result !== null) {
                    if (!File::copy($selected_file_path, $destination_path)) {
                        throw new \RuntimeException('The converted media could not be stored.');
                    }
                } else {
                    $file->move($dir, $stored_name);
                }

                $data['file_name'] = $destination_path;
                $data['storage'] = 'local';
            }

            return $data;
        } finally {
            if ($conversion_result !== null) {
                $conversion_service->CleanupPreparedImage($conversion_result);
            }
        }
    }

    private static function extToType($ext)
    {
        $ext = strtolower($ext);
        if (in_array($ext, ['png', 'jpeg', 'jpg'])) {
            $type = 'image';
        } elseif ($ext === 'webp') {
            $type = 'image';
        } elseif (in_array($ext, ['mp4'])) {
            $type = 'video';
        } elseif (in_array($ext, ['pdf'])) {
            $type = 'pdf';
        } elseif (in_array($ext, ['ttf'])) {
            $type = 'font';
        } else {
            $type = 'invalid';
        }
        return $type;
    }

    private static function ReadMediaFileSize(string $path): int
    {
        $size_bytes = @filesize($path);
        if ($size_bytes === false) {
            throw new \RuntimeException('The media size could not be read.');
        }

        return (int) $size_bytes;
    }

    public function generateLink($media_id, $usable_id, $class, $used_for)
    {
        $link = null;
        $media = MediaManager::find($media_id);
        if ($media) {
            UsedMedia::create([
                'media_id' => $media_id,
                'usable_id' => $usable_id,
                'usable_type' => $class,
                'used_for' => $used_for
            ]);
            $link = $media->file_name;
        }
        return $link;
    }

    public function removeLink($usable_id, $usable_type)
    {
        UsedMedia::where([
            'usable_id' => $usable_id,
            'usable_type' => $usable_type,
        ])->delete();
    }
}
