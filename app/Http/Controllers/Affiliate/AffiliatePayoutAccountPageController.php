<?php

namespace App\Http\Controllers\Affiliate;

use App\Domain\Affiliate\Queries\AffiliatePayoutAccountPageQuery;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AffiliatePayoutAccountPageController extends Controller
{
    public function __construct(private readonly AffiliatePayoutAccountPageQuery $page_query)
    {
    }

    public function Index(Request $request)
    {
        return view('affiliate.payout_accounts.index', $this->page_query->ForUser($request->user()));
    }
}
