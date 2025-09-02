<?php

namespace App\Http\Controllers;

use App\Models\AffiliateCommissionList;
use App\Models\affiliateUserCourseCount;
use App\Models\CoursePending;
use App\Models\Department;
use App\Models\FooterSetting;
use App\Models\PromotionImage;
use App\Models\QRCodePayMent;
use App\Models\TeachingPeriod;
use Modules\CourseSetting\Entities\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Affiliate\Repositories\AffiliateRepository;
use Modules\Affiliate\Repositories\AffiliateTransactionRepository;
use Modules\Affiliate\Http\Requests\AffiliateLinkRequest;
use Brian2694\Toastr\Facades\Toastr;
// use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Modules\Affiliate\Entities\AffiliateLink;
use Modules\Affiliate\Entities\AffiliateWithdraw;
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
        $departments = Department::whereNotIn('id', [1, 4, 5])->get();
        // dd($teaching_periods);
        $days = ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Thursday', 'Friday'];
        $qr_payment = QRCodePayMent::first();

        return view('affiliate.index', compact('departments', 'days', 'qr_payment'));
    }
    public function index(Request $request)
    {


        if (Auth::user()) {
            // Code when the user is authenticated
        } else {
            return "It's not login";  // Return a string if the user is not authenticated
        }
        $data['start_date'] = isset($request->startDate) ? $request->startDate : '';
        $data['end_date'] = isset($request->endDate) ? $request->endDate : '';
        $data['user'] = Auth::user();
        $affiliate_wallet = $data['user']->affiliateWallet;
        $affiliate_bank = $data['user']->affiliateBank;

        if ($affiliate_wallet && $affiliate_wallet->paypal_account) {
            $data['paypal_account'] = $affiliate_wallet->paypal_account;
            $data['amount'] = $affiliate_wallet->amount;
        } else {
            $data['paypal_account'] = ' ';
            $data['paypal_account'] = 0;
        }

        if ($affiliate_bank && $affiliate_bank->account_number) {
            $data['account_number'] = $affiliate_bank->account_number;
            $data['bank_name'] = $affiliate_bank->bank_name;
            $data['account_name'] = $affiliate_bank->account_name;
        } else {
            $data['account_number'] = 'N/A'; // Clearer default value
            $data['bank_name'] = 'N/A'; // To handle missing bank name
            $data['account_name'] = 'N/A'; // To handle missing account name
        }

        $data['data'] = $this->affiliateRepo->all();
        $affiliateTransactionRepo = new AffiliateTransactionRepository();
        $data['user_transaction_data'] = AffiliateWithdraw::where('user_id', Auth::user()->id)
            ->orderBy('id', 'desc')
            ->get();
        $data['courses_pennding'] = CoursePending::where('ref', Auth::user()->referral)
            ->orderBy('id', 'desc')
            ->get();
        $data['user_income_data'] = AffiliateCommissionList::where('payment_to', Auth::user()->id)
            ->orderBy('id', 'desc')
            ->get();

        if ($data['user']->role_id == 3) {
            return view('affiliate::student.student_index', $data);
        }
        $month = date('m');
        $year = date('Y');
        $count_course = affiliateUserCourseCount::where('user_id', Auth::user()->id)
            ->where('month', $month)
            ->where('year', $year)
            ->first();
        // echo Auth::user()->id . Auth::user()->name;
        // dd($data,Auth::user()->id);

        // dd(Auth::user()->referral);



        return view('affiliate.backend.my_affiliate', [
            'data' => $data,
            'amount' => $data['amount'] ?? '0',
            'bank_name' => $data['bank_name'],
            'account_number' => $data['account_number'],
            'account_name' => $data['account_name'],
            'paypal_account' => $data['paypal_account'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'count_course' => $count_course->count ?? 0,
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
        $departments = Department::whereNotIn('id', [1, 4, 5])->get();
        // dd($teaching_periods);
        $days = ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Thursday', 'Friday'];
        // Retrieve languages (assuming you have a Language model or method to fetch languages)
        $languages = Language::where('status', 1)->select('id', 'code', 'name', 'native', 'rtl')->get(); // Adjust this to your actual query for languages
        $qr_payment = QRCodePayMent::first();

        $footer = FooterSetting::first();
        $promotion = PromotionImage::where('status', 'enabled')->get();

        // Retrieve courses with pagination and preserve query string
        $courses = Course::where('status', 1)
            ->orderByDesc('id')
            ->paginate(12);
        $courses->appends($request->query());

        return view('affiliate.courses', compact(
            'request',
            'categories',
            'languages',
            'departments',
            'days',
            'qr_payment',
            'footer',
            'promotion',
            'courses'
        ));
    }

    public function findPeriodOptions(Request $request, $department_id, $day)
    {
        // Retrieve teaching periods for the selected department and day
        $teachingPeriods = TeachingPeriod::where('department_id', $department_id)
            ->where('day', $day)
            ->get();

        $html = '';
        foreach ($teachingPeriods as $teachingPeriod) {
            $html .= '
            
                <option value="' . $teachingPeriod->period . '">' . $teachingPeriod->period . '</option>
            
            ';
        }
        // Return the data as a JSON response
        return response()->json(['html' => $html]);
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
        // dd($request->all());
        try {
            $this->affiliateRepo->create($request->validated());
            Toastr::success('Affiliate Link Generated Successfully');
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
