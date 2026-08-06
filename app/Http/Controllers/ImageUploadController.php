<?php

namespace App\Http\Controllers;

use App\Services\Images\Exceptions\InvalidUploadedImageException;
use App\Services\Images\ImageConversionOptions;
use App\Services\Images\ImageConversionService;
use App\Services\Images\ImageSourceContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class ImageUploadController extends Controller
{
    private ImageConversionService $image_conversion_service;

    public function __construct(ImageConversionService $image_conversion_service)
    {
        $this->image_conversion_service = $image_conversion_service;
    }

    // อัปโหลดรูปภาพและส่งคืน URL
    public function upload(Request $request)
    {
        $conversion_result = null;
        $destination_path = null;
        $upload_succeeded = false;

        try {
            $validation_rules = [
                'image' => 'required|image|mimes:jpeg,jpg,png|max:5120',
            ];
            $request->validate($validation_rules);
            if ($request->hasFile('image')) {
                $conversion_result = $this->image_conversion_service->PrepareUploadedImage(
                    $request->file('image'),
                    ImageConversionOptions::ForOriginalDimensions(ImageSourceContext::STANDALONE_IMAGE_UPLOAD),
                );
                $file_name = Str::uuid()->toString() . '.' . $conversion_result->extension;
                $upload_path = public_path('uploads/images');
                if (!file_exists($upload_path)) {
                    mkdir($upload_path, 0755, true);
                }
                $destination_path = $upload_path . DIRECTORY_SEPARATOR . $file_name;
                if (!File::copy($conversion_result->selected_path, $destination_path)) {
                    throw new \RuntimeException('The uploaded image could not be stored.');
                }
                $upload_succeeded = true;
                $url = asset('uploads/images/' . $file_name);
                return response()->json([
                    'success' => true,
                    'url' => $url,
                    'message' => 'อัปโหลดรูปภาพสำเร็จ'
                ]);
            }
            return response()->json([
                'success' => false,
                'message' => 'ไม่พบไฟล์รูปภาพ'
            ], 400);
        } catch (\Illuminate\Validation\ValidationException $error) {
            return response()->json([
                'success' => false,
                'message' => $error->errors(),
            ], 422);
        } catch (InvalidUploadedImageException) {
            return response()->json([
                'success' => false,
                'message' => ['image' => ['The uploaded image is invalid.']],
            ], 422);
        } catch (Throwable $error) {
            Log::error('image_upload.failed', [
                'error_class' => get_class($error),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'อัปโหลดรูปภาพไม่สำเร็จ'
            ], 500);
        } finally {
            if ($conversion_result !== null) {
                $this->image_conversion_service->CleanupPreparedImage($conversion_result);
            }

            if (!$upload_succeeded && $destination_path !== null) {
                File::delete($destination_path);
            }
        }
    }
}
