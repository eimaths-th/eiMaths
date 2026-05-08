<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PromotionImage;
use Illuminate\Support\Facades\Auth;
use Modules\Affiliate\Entities\AffiliateLink;

class AffiliateContentController extends Controller
{
    public function index()
    {
        $contents = PromotionImage::where('is_for_affiliate', 1)
            ->where('status', 'enabled')
            ->orderBy('created_at', 'desc')
            ->get();
            
        $user = Auth::user();
        
        // Fetch the first existing affiliate link for this user
        $existing_link = AffiliateLink::where('owner_id', $user->id)->first();
        
        $referral_link = $existing_link 
            ? $existing_link->affiliate_link 
            : url('/') . '/?ref=' . ($user->referral ?? $user->id);
            
        return view('affiliate.backend.content', compact('contents', 'user', 'referral_link'));
    }
}
