<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Affiliate\Repositories\AffiliateRepository;
use Modules\Affiliate\Repositories\AffiliateTransactionRepository;
use Modules\Affiliate\Http\Requests\AffiliateLinkRequest;
use Brian2694\Toastr\Toastr;

// use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Modules\CourseSetting\Entities\Category;
use Modules\Localization\Entities\Language;

class AffiliateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected $affiliateRepo;

    public function __construct(AffiliateRepository $affiliateRepo)
    {
        $this->affiliateRepo = $affiliateRepo;
    }
    public function fontend()
    {
        //
        return view('affiliate.index');
    }
    public function index(Request $request)
    {
        // echo "hi its dashboard";
        // dd($request);
        // if (isAffiliateUser() && !hasAffiliateAccess()) {
        //     abort('403', trans('affiliate.The request is now pending for admin approval'));
        // } elseif (!hasAffiliateAccess() && !isAffiliateUser()) {
        //     abort('403',trans('affiliate.You are not affiliate user. You need to join affiliate program first.'));
        // }
        // try {

        if (Auth::user()) {
            // Code when the user is authenticated
        } else {
            return "It's not login";  // Return a string if the user is not authenticated
        }
        $data['start_date'] = isset($request->startDate) ? $request->startDate : '';
        $data['end_date'] = isset($request->endDate) ? $request->endDate : '';
        $data['user'] = Auth::user();
        $affiliate_wallet = $data['user']->affiliateWallet;
        if ($affiliate_wallet && $affiliate_wallet->paypal_account) {
            $data['paypal_account'] = $affiliate_wallet->paypal_account;
        } else {
            $data['paypal_account'] = ' ';
        }
        $data['data'] = $this->affiliateRepo->all();
        $affiliateTransactionRepo = new AffiliateTransactionRepository();
        $data['user_transaction_data'] = $affiliateTransactionRepo->userWiseWithdraw($data['start_date'], $data['end_date']);
        $data['user_income_data'] = $affiliateTransactionRepo->userWiseIncome($data['start_date'], $data['end_date']);
        if ($data['user']->role_id == 3) {
            return view('affiliate::student.student_index', $data);
        }
        // echo Auth::user()->id . Auth::user()->name;
        // dd($data);
        return view('affiliate.backend.my_affiliate', [
            'data' => $data,
            'paypal_account' => $data['paypal_account'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date']
        ]);
        // } catch (Exception $e) {
        //     Toastr::error($e->getMessage(), trans('common.Error'));
        //     return redirect()->to('/');
        // }
    }

    // public function my_affiliate(Request $request)
    // {
    //     try {
    //         $data = [
    //             'start_date' => $request->startDate ?? '',
    //             'end_date' => $request->endDate ?? '',
    //             'user' => Auth::user()
    //         ];

    //         if ($data['user']->affiliateWallet && $data['user']->affiliateWallet->paypal_account) {
    //             $data['paypal_account'] = $data['user']->affiliateWallet->paypal_account;
    //         }

    //         if ($data['user']->role_id == 3) {
    //             return view('affiliate.backend.my_affiliate', $data);
    //         }

    //         // return view('affiliate.backend.my_affiliate', $data);
    //         dd($data);

    //         return view('affiliate.backend.my_affiliate', compact('data'));
    //     } catch (Exception $e) {
    //         Toastr::error($e->getMessage(), trans('common.Error'));
    //         return redirect()->to('/');
    //     }
    // }


    /**
     * Show the form for creating a new resource.
     */
    public function courses(Request $request)
    {
        // Retrieve categories
        $categories = Category::all();

        // Retrieve languages (assuming you have a Language model or method to fetch languages)
        $languages = Language::where('status', 1)->select('id', 'code', 'name', 'native', 'rtl')->get(); // Adjust this to your actual query for languages

        return view('affiliate.courses', compact('request', 'categories', 'languages'));
    }
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AffiliateLinkRequest $request)
    {
        //
        try {
            $this->affiliateRepo->create($request->validated());
            Toastr::success(trans('affiliate.Affiliate Link Generated Successfully'));
            return back();
        } catch (Exception $e) {
            Toastr::error($e->getMessage(), trans('common.Error'));
            return response()->json(['error' => $e->getMessage()], 503);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
