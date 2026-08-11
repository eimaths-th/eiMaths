<?php

namespace App\Http\Controllers;

use App\Domain\Affiliate\Services\AffiliateContentService;
use App\Http\Requests\Affiliate\StoreAffiliateContentRequest;
use App\Http\Requests\Affiliate\ToggleAffiliateContentStatusRequest;
use App\Http\Requests\Affiliate\UpdateAffiliateContentRequest;
use App\Models\AffiliateContent;
use App\Services\Images\ImageSourceContext;
use App\Services\Images\ImageUploadStorageService;
use App\Services\Images\StoredImageFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

/**
 * Controller สำหรับ Admin จัดการสื่อการตลาด Affiliate
 * CRUD: แสดงรายการ, อัปโหลด, ลบ, เปลี่ยนสถานะ
 */
class AffiliateContentManageController extends Controller
{
    private ImageUploadStorageService $image_upload_storage_service;
    private AffiliateContentService $affiliate_content_service;

    public function __construct(
        ImageUploadStorageService $image_upload_storage_service,
        AffiliateContentService $affiliate_content_service,
    )
    {
        $this->image_upload_storage_service = $image_upload_storage_service;
        $this->affiliate_content_service = $affiliate_content_service;
    }
    /**
     * แสดงรายการสื่อการตลาดทั้งหมด
     */
    public function Index()
    {
        $contents = AffiliateContent::withTrashed()->orderBy('created_at', 'desc')->get();
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

    public function StoreValidated(StoreAffiliateContentRequest $request)
    {
        $actor = $request->user();
        abort_unless($actor !== null, 403);
        $this->affiliate_content_service->Create(
            $request->validated(),
            $request->file('file'),
            $actor,
            (string) $request->attributes->get('affiliate_correlation_id', request()->header('X-Correlation-ID', 'content')),
        );

        return back()->with('success', __('affiliate.content.created'));
    }

    /**
     * ลบสื่อการตลาด
     */
    public function Destroy(AffiliateContent $affiliate_content)
    {
        $content = $affiliate_content;
        $actor = Auth::user();
        abort_unless($actor !== null, 403);
        if (Schema::hasColumn('affiliate_contents', 'archived_at')) {
            $this->affiliate_content_service->Archive($content, $actor, (string) request()->attributes->get('affiliate_correlation_id', request()->header('X-Correlation-ID', 'content')));
            return back()->with('success', __('affiliate.content.archived'));
        }

        $content->delete();
        return back()->with('success', __('affiliate.content.archived'));
    }

    public function UpdateValidated(UpdateAffiliateContentRequest $request, AffiliateContent $affiliate_content)
    {
        $actor = $request->user();
        abort_unless($actor !== null, 403);
        $this->affiliate_content_service->Update(
            $affiliate_content,
            $request->validated(),
            $actor,
            (string) $request->attributes->get('affiliate_correlation_id', request()->header('X-Correlation-ID', 'content')),
        );

        return back()->with('success', __('affiliate.content.updated'));
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

    public function ToggleStatusValidated(ToggleAffiliateContentStatusRequest $request)
    {
        $actor = $request->user();
        abort_unless($actor !== null, 403);
        $content = AffiliateContent::findOrFail((int) $request->validated('id'));
        $updated = $this->affiliate_content_service->ToggleStatus($content, $actor, (string) $request->attributes->get('affiliate_correlation_id', request()->header('X-Correlation-ID', 'content')));

        return response()->json(['status' => $updated->status]);
    }
}
