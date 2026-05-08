<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PromotionImage;
use Illuminate\Support\Facades\Auth;

class AffiliateContentController extends Controller
{
    public function index()
    {
        $contents = PromotionImage::where('is_for_affiliate', 1)
            ->where('status', 'enabled')
            ->orderBy('created_at', 'desc')
            ->get();
            
        $user = Auth::user();
        // Base referral URL: home URL + ?ref=user_id or user referral code
        // Based on previous analysis, it uses ?ref=id or a specific link.
        // I'll check how referral links are generated in this system.
        
        return view('affiliate.backend.content', compact('contents', 'user'));
    }
}
