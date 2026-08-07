<?php

namespace App\Http\Controllers\Affiliate;

use App\Domain\Affiliate\Queries\AffiliateDashboardQuery;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AffiliateDashboardController extends Controller
{
    public function __construct(private readonly AffiliateDashboardQuery $dashboard_query)
    {
    }

    public function Index(Request $request)
    {
        return view('affiliate.dashboard', $this->dashboard_query->ForUser($request->user()));
    }
}
