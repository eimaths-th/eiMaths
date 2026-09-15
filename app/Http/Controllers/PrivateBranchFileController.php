<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\CoursePending;
use App\Models\Feedback;
use App\Models\FileStore;
use App\Models\FileUpload;
use App\Models\HistoryStudentImg;
use App\Models\Histrories;
use App\Models\Parents;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PrivateBranchFileController extends Controller
{
    public function show(Request $request, string $folder, string $filename)
    {
        abort_unless($filename !== '' && basename($filename) === $filename && !str_contains($filename, '..'), 404);
        $folders = [
            'file_upload' => 'file_upload', 'file_store' => 'file_store',
            'payment_slips' => 'img/payment_slips', 'attachments' => 'storage/attachments',
            'feedback_images' => 'feedback_images',
        ];
        abort_unless(isset($folders[$folder]), 404);
        $actor = $request->user();
        $parent = Auth::guard('sanctum')->user();
        $actor = $actor instanceof User ? $actor : ($parent instanceof User ? $parent : null);
        if ($actor instanceof User) {
            abort_unless((int) $actor->disable !== 1 && \Illuminate\Support\Facades\DB::table('departments')
                ->where('id', $actor->department_id)->where('status', 1)->exists(), 404);
            $context = app(\App\Support\Branch\BranchContext::class);
            if ($context->actor?->id !== $actor->id) {
                return $context->runAs($actor, fn () => $this->show($request, $folder, $filename));
            }
        }
        $allowed = false;
        if ($actor instanceof User && (int) $actor->disable !== 1) {
            $allowed = match ($folder) {
                'file_upload' => FileUpload::where('filename', $filename)->exists()
                    || ($actor->can('history-list') && (HistoryStudentImg::where('img', $filename)->exists()
                        || Histrories::whereIn('signature', [$filename, '/file_upload/'.$filename])->exists())),
                'file_store' => $actor->can('order-show') && (FileStore::where('filename', $filename)->exists()
                    || \App\Models\Order::where('order_number', pathinfo($filename, PATHINFO_FILENAME))->exists())
                    || ($actor->can('receipt-show') && (\App\Models\Receipt::where('receipt_number', pathinfo($filename, PATHINFO_FILENAME))->exists()
                        || FileStore::where('filename', $filename)->whereIn('module_id', \App\Models\Receipt::select('oid'))->exists()))
                    || ($actor->can('invoice-show') && \App\Models\Invoice::where('invoice_number', pathinfo($filename, PATHINFO_FILENAME))->exists()),
                'payment_slips' => $actor->can('affiliate-course-application-list') && CoursePending::whereIn('slip', ['img/payment_slips/'.$filename, '/img/payment_slips/'.$filename])->exists(),
                'attachments' => Feedback::where('attachment_path', 'attachments/'.$filename)->exists(),
                'feedback_images' => Feedback::whereIn('image_feedback', [$filename, 'feedback_images/'.$filename, '/feedback_images/'.$filename])->exists(),
                default => false,
            };
        } elseif ($folder === 'file_upload' && ($parent instanceof Parents || $request->hasValidSignature())) {
            if (!$parent instanceof Parents) {
                $parent = Parents::find($request->query('parent'));
            }
            if ($parent) {
                $ids = $parent->students()->pluck('id')->all();
                $allowed = HistoryStudentImg::whereIn('student_id', $ids)->where('img', $filename)->exists()
                    || Histrories::whereIn('student_id', $ids)->whereIn('signature', [$filename, '/file_upload/'.$filename])->exists();
            }
        }
        abort_unless($allowed, 404);
        $path = public_path($folders[$folder].'/'.$filename);
        abort_unless(is_file($path), 404);
        return response()->file($path, [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "sandbox; default-src 'none';",
        ]);
    }
}
