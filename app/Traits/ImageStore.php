<?php

namespace App\Traits;

use App\Services\Images\ImageConversionOptions;
use App\Services\Images\ImageConversionService;
use App\Services\Images\ImageSourceContext;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Throwable;

trait ImageStore
{
    public function saveImage($image, $height = null, $lenght = null)
    {
        if (config('app.demo_mode')) {
            Toastr::warning('For Demo mode, Some feature are disabled');
            return 'public/assets/course/no_image.png';
        }

        if (!isset($image)) {
            return null;
        }

        try {
            if ($image instanceof UploadedFile && strtolower($image->extension()) === 'svg') {
                $domain = SaasDomain();
                $current_date = Carbon::now()->format('d-m-Y');
                $directory = 'public/uploads/' . $domain . '/images/' . $current_date;
                if (!File::isDirectory($directory)) {
                    File::makeDirectory($directory, 0777, true, true);
                }

                $file_name = md5(rand(0, 9999) . '_' . time()) . '.' . $image->clientExtension();
                $image_name = 'public/uploads/' . $domain . '/images/' . $file_name;
                $image->move(public_path('uploads/images'), $file_name);

                return $image_name;
            }

            if (self::IsConvertibleUpload($image)) {
                $domain = SaasDomain();
                $current_date = Carbon::now()->format('d-m-Y');
                $destination_directory = 'public/uploads/' . $domain . '/images/' . $current_date;
                $conversion_options = ($height !== null && $lenght !== null)
                    ? ImageConversionOptions::ForImageStoreResize(ImageSourceContext::IMAGE_STORE, (int) $height, (int) $lenght)
                    : ImageConversionOptions::ForOriginalDimensions(ImageSourceContext::IMAGE_STORE);

                return self::StorePreparedImage(
                    $image,
                    $conversion_options,
                    $destination_directory,
                );
            }

            $image_extention = str_replace('image/', '', Image::make($image)->mime());
            $img = Image::make($image);
            if ($height != null && $lenght != null) {
                $img_size = getimagesize($image);
                $original_width = $img_size[0];
                $original_height = $img_size[1];
                if ($original_width > $original_height) {
                    $img->resize($lenght, null, function ($constraint) {
                        $constraint->aspectRatio();
                    });
                } elseif ($original_width < $original_height) {
                    $img->resize(null, $height, function ($constraint) {
                        $constraint->aspectRatio();
                    });
                } else {
                    if ($lenght > $height) {
                        $img->resize(null, $lenght, function ($constraint) {
                            $constraint->aspectRatio();
                        });
                    } elseif ($lenght < $height) {
                        $img->resize($height, null, function ($constraint) {
                            $constraint->aspectRatio();
                        });
                    } else {
                        $img->resize($height, null, function ($constraint) {
                            $constraint->aspectRatio();
                        });
                    }
                }
            }

            $domain = SaasDomain();
            $current_date = Carbon::now()->format('d-m-Y');
            $destination_directory = 'public/uploads/' . $domain . '/images/' . $current_date;
            if (!File::isDirectory($destination_directory)) {
                File::makeDirectory($destination_directory, 0777, true, true);
            }

            $image_name = $destination_directory . '/' . uniqid() . '.' . $image_extention;
            $img->save($image_name);

            return $image_name;
        } catch (Throwable) {
            return null;
        }
    }

    public function deleteImage($url)
    {
        if (isset($url)) {
            if (File::exists($url)) {
                File::delete($url);
                return true;
            }

            return false;
        }

        return null;
    }

    public function saveAvatar($image, $height = null, $lenght = null)
    {
        if (!isset($image)) {
            return null;
        }

        try {
            $current_date = Carbon::now()->format('d-m-Y');
            $destination_directory = 'uploads/avatar/' . $current_date;

            if (self::IsConvertibleUpload($image)) {
                if ($height !== null && $lenght !== null) {
                    [$resized_upload, $temporary_path] = self::CreateExactResizeUpload($image, (int) $height, (int) $lenght);

                    try {
                        return self::StorePreparedImage(
                            $resized_upload,
                            ImageConversionOptions::ForOriginalDimensions(ImageSourceContext::IMAGE_STORE_AVATAR),
                            $destination_directory,
                        );
                    } finally {
                        if (is_file($temporary_path)) {
                            @unlink($temporary_path);
                        }
                    }
                }

                return self::StorePreparedImage(
                    $image,
                    ImageConversionOptions::ForOriginalDimensions(ImageSourceContext::IMAGE_STORE_AVATAR),
                    $destination_directory,
                );
            }

            if (!File::isDirectory($destination_directory)) {
                File::makeDirectory($destination_directory, 0777, true, true);
            }

            $image_extension = str_replace('image/', '', Image::make($image)->mime());
            $img = Image::make($image);
            if ($height != null && $lenght != null) {
                $img = $img->resize($height, $lenght);
            }

            $image_name = $destination_directory . '/' . uniqid() . '.' . $image_extension;
            $img->save($image_name);

            return $image_name;
        } catch (Throwable) {
            return null;
        }
    }

    public static function saveImageStatic($image, $height = null, $lenght = null)
    {
        if (!isset($image)) {
            return null;
        }

        try {
            $current_date = Carbon::now()->format('d-m-Y');
            $destination_directory = 'uploads/images/' . $current_date;

            if (self::IsConvertibleUpload($image)) {
                if ($height !== null && $lenght !== null) {
                    [$resized_upload, $temporary_path] = self::CreateExactResizeUpload($image, (int) $height, (int) $lenght);

                    try {
                        return self::StorePreparedImage(
                            $resized_upload,
                            ImageConversionOptions::ForOriginalDimensions(ImageSourceContext::IMAGE_STORE_STATIC),
                            $destination_directory,
                        );
                    } finally {
                        if (is_file($temporary_path)) {
                            @unlink($temporary_path);
                        }
                    }
                }

                return self::StorePreparedImage(
                    $image,
                    ImageConversionOptions::ForOriginalDimensions(ImageSourceContext::IMAGE_STORE_STATIC),
                    $destination_directory,
                );
            }

            if (!File::isDirectory($destination_directory)) {
                File::makeDirectory($destination_directory, 0777, true, true);
            }

            $image_extension = str_replace('image/', '', Image::make($image)->mime());
            $img = Image::make($image);
            if ($height != null && $lenght != null) {
                $img = $img->resize($height, $lenght);
            }

            $image_name = $destination_directory . '/' . uniqid() . '.' . $image_extension;
            $img->save($image_name);

            return $image_name;
        } catch (Throwable) {
            return null;
        }
    }

    public function saveFile(UploadedFile $file)
    {
        if (isset($file)) {
            $igonreFiles = ['php'];
            if (in_array($file->clientExtension(), $igonreFiles)) {
                return null;
            }

            $current_date = Carbon::now()->format('d-m-Y');
            $path = 'public/uploads/file/' . $current_date;
            if (!File::isDirectory($path)) {
                File::makeDirectory($path, 0777, true, true);
            }

            $file_name = md5(rand(0, 9999) . '_' . time()) . '.' . $file->clientExtension();
            $stored_path = $path . '/' . $file_name;
            $file->move(public_path(str_replace('public/', '', $path)), $file_name);

            return $stored_path;
        }

        return null;
    }

    private static function IsConvertibleUpload($image): bool
    {
        if (!$image instanceof UploadedFile) {
            return false;
        }

        return in_array(strtolower($image->clientExtension()), ['jpg', 'jpeg', 'png'], true);
    }

    private static function StorePreparedImage(
        UploadedFile $image,
        ImageConversionOptions $conversion_options,
        string $destination_directory,
    ): string {
        $conversion_service = app(ImageConversionService::class);
        $conversion_result = null;

        try {
            $conversion_result = $conversion_service->PrepareUploadedImage($image, $conversion_options);
            if (!File::isDirectory($destination_directory)) {
                File::makeDirectory($destination_directory, 0777, true, true);
            }

            $stored_basename = Str::uuid()->toString() . '.' . $conversion_result->extension;
            $stored_path = $destination_directory . '/' . $stored_basename;
            if (!File::copy($conversion_result->selected_path, base_path($stored_path))) {
                throw new \RuntimeException('The prepared image could not be stored.');
            }

            return $stored_path;
        } finally {
            if ($conversion_result !== null) {
                $conversion_service->CleanupPreparedImage($conversion_result);
            }
        }
    }

    /** @return array{0: UploadedFile, 1: string} */
    private static function CreateExactResizeUpload(UploadedFile $image, int $resize_width, int $resize_height): array
    {
        $normalized_image = Image::make($image);
        if ($normalized_image->mime() === 'image/jpeg') {
            $normalized_image->orientate();
        }

        $source_extension = strtolower($image->clientExtension());
        $source_mime_type = $normalized_image->mime();
        $temporary_directory = config('image-conversion.temporary_directory');
        if (!File::isDirectory($temporary_directory)) {
            File::makeDirectory($temporary_directory, 0700, true, true);
        }

        $temporary_path = rtrim($temporary_directory, DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR . 'image-store-compat-' . Str::uuid()->toString() . '.' . $source_extension;
        $normalized_image->resize($resize_width, $resize_height);
        $normalized_image->save($temporary_path, null, $source_mime_type === 'image/png' ? 'png' : 'jpg');

        return [
            new UploadedFile($temporary_path, basename($temporary_path), $source_mime_type, null, true),
            $temporary_path,
        ];
    }
}
