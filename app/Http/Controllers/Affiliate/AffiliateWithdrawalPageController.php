<?php

namespace App\Http\Controllers\Affiliate;

use App\Domain\Affiliate\Queries\AffiliateWithdrawalPageQuery;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AffiliateWithdrawalPageController extends Controller
{
    public function __construct(private readonly AffiliateWithdrawalPageQuery $page_query)
    {
    }

    public function Index(Request $request)
    {
        return view('affiliate.withdrawals.index', $this->page_query->ForUser($request->user()));
    }
}
