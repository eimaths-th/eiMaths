<?php

namespace App\Http\Controllers\Affiliate;

use App\Http\Controllers\Controller;

class AffiliateLegalController extends Controller
{
    public function Terms()
    {
        return view('affiliate.legal.terms');
    }

    public function Privacy()
    {
        return view('affiliate.legal.privacy');
    }
}
