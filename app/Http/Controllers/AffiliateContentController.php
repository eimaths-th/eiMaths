<?php

namespace App\Http\Controllers;

use App\Models\AffiliateContent;
use Illuminate\Support\Facades\Auth;
use Modules\Affiliate\Entities\AffiliateLink;

/**
 * Controller สำหรับแสดงสื่อการตลาดฝั่ง Affiliate (Frontend)
 * ดึงข้อมูลจากตาราง affiliate_contents แทน promotion_img
 */
class AffiliateContentController extends Controller
{
    public function index()
    {
        // ดึงสื่อที่เปิดใช้งานจากตาราง affiliate_contents
        $contents = AffiliateContent::where('status', 'enabled')
            ->orderBy('created_at', 'desc')
            ->get();

        $user = Auth::user();

        // ดึง Affiliate Link ที่มีอยู่จริงจากระบบ
        $existing_link = AffiliateLink::where('owner_id', $user->id)->first();

        $referral_link = $existing_link
            ? $existing_link->affiliate_link
            : url('/') . '/?ref=' . ($user->referral ?? $user->id);

        return view('affiliate.backend.content', compact('contents', 'user', 'referral_link'));
    }
}
