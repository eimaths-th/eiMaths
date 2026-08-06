<?php

namespace App\Http\Controllers;

use App\Models\AffiliateContent;
use App\Services\Images\ImageSourceContext;
use App\Services\Images\ImageUploadStorageService;
use App\Services\Images\StoredImageFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Throwable;

/**
 * Controller สำหรับ Admin จัดการสื่อการตลาด Affiliate
 * CRUD: แสดงรายการ, อัปโหลด, ลบ, เปลี่ยนสถานะ
 */
class AffiliateContentManageController extends Controller
{
    private ImageUploadStorageService $image_upload_storage_service;

    public function __construct(ImageUploadStorageService $image_upload_storage_service)
    {
        $this->image_upload_storage_service = $image_upload_storage_service;
    }
    /**
     * แสดงรายการสื่อการตลาดทั้งหมด
     */
    public function index()
    {
        $contents = AffiliateContent::orderBy('created_at', 'desc')->get();
        return view('affiliate_content.index', compact('contents'));
    }

    /**
     * บันทึกสื่อการตลาดใหม่
     */
    public function Store(Request $request)
    {
        // ตรวจสอบข้อมูลก่อนบันทึก
        $request->validate([
            'file'        => 'required|file|mimes:jpeg,png,jpg,gif,webp,mp4,mov,avi,wmv|max:51200',
            'title'       => 'required|string|max:255',
            'type'        => 'required|in:image,video',
            'description' => 'nullable|string|max:1000',
        ]);

        $stored_image = null;
        $stored_video_path = null;

        // จัดการอัปโหลดไฟล์
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            if ($request->type === 'image') {
                $stored_image = $this->image_upload_storage_service->StoreUploadedImage(
                    $file,
                    'uploads/affiliate/image',
                    ImageSourceContext::AFFILIATE_CONTENT_IMAGE,
                );
                $file_path = $stored_image->relative_path;
            } else {
                $random = Str::random(10);
                $file_name = time() . '-' . $random . '_' . $file->getClientOriginalName();
                $directory = 'uploads/affiliate/video';
                $stored_video_path = public_path($directory . '/' . $file_name);
                $file->move(public_path($directory), $file_name);
                $file_path = $directory . '/' . $file_name;
            }
        } else {
            return back()->with('error', 'กรุณาเลือกไฟล์ที่ต้องการอัปโหลด');
        }

        try {
            // บันทึกลงฐานข้อมูล
            AffiliateContent::create([
                'title'       => $request->title,
                'file_path'   => $file_path,
                'type'        => $request->type,
                'description' => $request->description,
                'status'      => 'enabled',
                'created_by'  => Auth::id(),
            ]);
        } catch (Throwable $exception) {
            if ($stored_image instanceof StoredImageFile) {
                $this->image_upload_storage_service->DeleteStoredImage($stored_image->absolute_path);
            }
            if ($stored_video_path !== null) {
                @unlink($stored_video_path);
            }

            throw $exception;
        }

        return back()->with('success', 'เพิ่มสื่อการตลาดสำเร็จ!');
    }

    /**
     * ลบสื่อการตลาด
     */
    public function Destroy($id)
    {
        $content = AffiliateContent::findOrFail($id);

        // ลบไฟล์จากเซิร์ฟเวอร์ (ถ้ามี)
        $full_path = public_path($content->file_path);
        if (file_exists($full_path)) {
            unlink($full_path);
        }

        $content->delete();
        return back()->with('success', 'ลบสื่อการตลาดสำเร็จ!');
    }

    /**
     * เปลี่ยนสถานะ enabled/disabled
     */
    public function ToggleStatus(Request $request)
    {
        $content = AffiliateContent::findOrFail($request->id);
        $content->status = $content->status === 'enabled' ? 'disabled' : 'enabled';
        $content->save();

        return response()->json(['message' => 'เปลี่ยนสถานะสำเร็จ!']);
    }
}
