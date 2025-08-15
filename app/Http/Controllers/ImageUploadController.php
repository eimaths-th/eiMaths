<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ImageUploadController extends Controller
{
    // อัปโหลดรูปภาพและส่งคืน URL
    public function upload(Request $request)
    {
        try {
            $Validation_rules = [
                'image' => 'required|image|mimes:jpeg,jpg,png|max:5120',
            ];
            $request->validate($Validation_rules);
            if ($request->hasFile('image')) {
                $file_name = Str::uuid() . '.' . $request->file('image')->getClientOriginalExtension();
                $upload_path = public_path('uploads/images');
                if (!file_exists($upload_path)) {
                    mkdir($upload_path, 0755, true);
                }
                $request->file('image')->move($upload_path, $file_name);
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
        } catch (\Exception $error) {
            return response()->json([
                'success' => false,
                'message' => $error->getMessage()
            ], 500);
        }
    }
}
