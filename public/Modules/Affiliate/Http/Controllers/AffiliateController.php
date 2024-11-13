<?php

namespace Modules\Affiliate\Http\Controllers;


use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Affiliate\Http\Requests\AffiliateConfigurationRequest;
use Modules\Affiliate\Http\Requests\AffiliateLinkRequest;
use Modules\Affiliate\Repositories\AffiliateRepository;
use Modules\Affiliate\Repositories\AffiliateTransactionRepository;
use Modules\FrontendManage\Entities\FrontPage;


class AffiliateController extends Controller
{
    protected $affiliateRepo;

    public function __construct(AffiliateRepository $affiliateRepo)
    {
        $this->affiliateRepo = $affiliateRepo;
    }

    public function index(Request $request)
    {
        if (isAffiliateUser() && !hasAffiliateAccess()) {
            abort('403', trans('affiliate.The request is now pending for admin approval'));
        } elseif (!hasAffiliateAccess() && !isAffiliateUser()) {
            abort('403',trans('affiliate.You are not affiliate user. You need to join affiliate program first.'));
        }
        try {

            $data['start_date'] = isset($request->startDate) ? $request->startDate : '';
            $data['end_date'] = isset($request->endDate) ? $request->endDate : '';
            $data['user'] = Auth::user();
            $affiliate_wallet = $data['user']->affiliateWallet;
            if ($affiliate_wallet && $affiliate_wallet->paypal_account) {
                $data['paypal_account'] = $affiliate_wallet->paypal_account;
            }
            $data['data'] = $this->affiliateRepo->all();
            $affiliateTransactionRepo = new AffiliateTransactionRepository();
            $data['user_transaction_data'] = $affiliateTransactionRepo->userWiseWithdraw($data['start_date'], $data['end_date']);
            $data['user_income_data'] = $affiliateTransactionRepo->userWiseIncome($data['start_date'], $data['end_date']);
            if ($data['user']->role_id == 3) {
                return view('affiliate::student.student_index', $data);
            }
            return view('affiliate::affiliate.index', $data);
        } catch (Exception $e) {
            Toastr::error($e->getMessage(), trans('common.Error'));
            return redirect()->to('/');
        }
    }

    public function store(AffiliateLinkRequest $request)
    {
        try {
            $this->affiliateRepo->create($request->validated());
            Toastr::success(trans('affiliate.Affiliate Link Generated Successfully'));
            return back();
        } catch (Exception $e) {
            Toastr::error($e->getMessage(), trans('common.Error'));
            return response()->json(['error' => $e->getMessage()], 503);
        }
    }

    public function configurationIndex()
    {
        try {
            return view('affiliate::affiliate.configuration');
        } catch (Exception $e) {
            Toastr::error($e->getMessage(), trans('common.Error'));
            return response()->json(['error' => $e->getMessage()], 503);
        }
    }

    public function configurationUpdate(AffiliateConfigurationRequest $request)
    {
        try {
            $this->affiliateRepo->configuration($request->validated());
            Toastr::success(trans('affiliate.Affiliate Configuration Updated Successfully'));

            return back();
        } catch (Exception $e) {
            Toastr::error($e->getMessage(), trans('common.Error'));
            return response()->json(['error' => $e->getMessage()], 503);
        }
    }

    public function addOrUpdatePaypalAccount(Request $request)
    {
        $validate_rules = [
            'paypal_account' => 'required',
        ];
        $request->validate($validate_rules, validationMessage($validate_rules));
        try {
            $this->affiliateRepo->addOrUpdatePaypalAccount($request->all());
            Toastr::success(trans('common.Operation successful'), trans('common.Success'));
            return back();
        } catch (Exception $e) {
            Toastr::error($e->getMessage(), trans('common.Error'));
            return response()->json(['error' => $e->getMessage()], 503);
        }
    }

    public function frontendEdit()
    {

        try {
            $data['row'] = FrontPage::where('slug', '/affiliate')->first();
            $data['details'] = $data['row']->details;
            $active = request('lang', auth()->user()->language_code);
            app()->setLocale($active);
            return view('aorapagebuilder::pages.design', $data, compact('active'));
        } catch (Exception $e) {
            Toastr::error($e->getMessage(), trans('common.Error'));
            return response()->json(['error' => $e->getMessage()], 503);
        }
    }

    public function frontend()
    {
        $data['row'] = FrontPage::where('slug', '/affiliate')->first();
        $data['details'] = '';
        $details = DB::table('front_pages')->where('slug', '/affiliate')->first()->details ?? '';
        $pos = strpos($details, '{"');
        if ($pos === false) {
            $data['details'] = $details;
        } else {
            $details = (array)json_decode($details);
            if ($details) {
                $data['details'] = $details[array_key_first($details)];
            }
        }
        // dd($data);
        return view('aorapagebuilder::pages.show', $data);
        return view('aorapagebuilder::pages.show');
    }
}
