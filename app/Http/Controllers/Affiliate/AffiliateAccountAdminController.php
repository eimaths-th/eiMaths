<?php

namespace App\Http\Controllers\Affiliate;

use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Domain\Affiliate\Queries\AffiliateAccountAdminQuery;
use App\Domain\Affiliate\Services\AffiliateAccountService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Affiliate\ChangeAffiliateStatusRequest;

class AffiliateAccountAdminController extends Controller
{
    public function __construct(
        private readonly AffiliateAccountService $account_service,
        private readonly AffiliateAccountAdminQuery $account_query,
    ) {
    }

    public function index()
    {
        return view('affiliate.admin.index', ['affiliate_profiles' => $this->account_query->Paginate()]);
    }

    public function approve(AffiliateProfile $affiliate_profile, ChangeAffiliateStatusRequest $request)
    {
        $this->account_service->Approve($affiliate_profile, $request->user(), $request->validated('reason'));
        return back()->with('success', 'Affiliate approved.');
    }

    public function suspend(AffiliateProfile $affiliate_profile, ChangeAffiliateStatusRequest $request)
    {
        $this->account_service->Suspend($affiliate_profile, $request->user(), (string) $request->validated('reason'));
        return back()->with('success', 'Affiliate suspended.');
    }

    public function reactivate(AffiliateProfile $affiliate_profile, ChangeAffiliateStatusRequest $request)
    {
        $this->account_service->Reactivate($affiliate_profile, $request->user(), $request->validated('reason'));
        return back()->with('success', 'Affiliate reactivated.');
    }

    public function deactivate(AffiliateProfile $affiliate_profile, ChangeAffiliateStatusRequest $request)
    {
        $this->account_service->Deactivate($affiliate_profile, $request->user(), (string) $request->validated('reason'));
        return back()->with('success', 'Affiliate deactivated.');
    }
}
