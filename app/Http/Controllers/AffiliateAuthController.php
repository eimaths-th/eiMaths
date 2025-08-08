<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Exception;

// use Brian2694\Toastr\Toastr;
use Brian2694\Toastr\Facades\Toastr;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Affiliate\Entities\AffiliateLink;
use Modules\Affiliate\Repositories\AffiliateRepository;

class AffiliateAuthController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public $page = [];
    public $custom_field = [];
    protected $affiliateRepo;

    // Constructor or method to initialize the $page property
    public function __construct(AffiliateRepository $affiliateRepo)
    {
        $this->page = [
            "id" => 1,
            "title" => json_decode('{"en":"ยินดีต้อนรับสู่ระบบจัดการการเรียนรู้ eiMaths","th":"Welcome to Infix Learning Management System"}', true),
            "banner" => "",
            "slogans1" => json_decode('{"en":"ความเป็นเลิศ.","th":"Excellence."}', true),
            "slogans2" => json_decode('{"en":"ชุมชน.","th":"Community."}', true),
            "slogans3" => json_decode('{"en":"ความหลากหลาย.","th":"Diversity."}', true),
            "created_at" => "2024-08-08 14:56:32",
            "updated_at" => "2024-09-16 10:47:34",
            "reg_title" => json_decode('{"en":"ยินดีต้อนรับสู่ระบบจัดการการเรียนรู้ eiMaths","th":"Welcome to Infix Learning Management System"}', true),
            "reg_banner" => "",
            "reg_slogans1" => json_decode('{"en":"ความเป็นเลิศ.","th":"Excellence"}', true),
            "reg_slogans2" => json_decode('{"en":"ชุมชน.","th":"Community"}', true),
            "reg_slogans3" => json_decode('{"en":"ความหลากหลาย.","th":"Excellence"}', true),
            "forget_title" => json_decode('{"en":"ยินดีต้อนรับสู่ระบบจัดการการเรียนรู้ eiMaths","th":"Welcome to Infix Learning Management System"}', true),
            "forget_banner" => "",
            "forget_slogans1" => json_decode('{"en":"ความเป็นเลิศ.","th":"Excellence"}', true),
            "forget_slogans2" => json_decode('{"en":"ชุมชน.","th":"Community"}', true),
            "forget_slogans3" => json_decode('{"en":"ความหลากหลาย.","th":"Excellence"}', true),
            "lms_id" => 1
        ];
        $this->custom_field = [
            "id" => 1,
            "show_company" => 0,
            "show_gender" => 0,
            "show_student_type" => 0,
            "show_identification_number" => 0,
            "show_job_title" => 0,
            "show_dob" => 0,
            "show_name" => 1,
            "required_company" => 0,
            "required_gender" => 0,
            "required_student_type" => 0,
            "required_identification_number" => 0,
            "required_job_title" => 0,
            "required_dob" => 0,
            "required_name" => 1,
            "editable_company" => 1,
            "editable_gender" => 1,
            "editable_student_type" => 1,
            "editable_identification_number" => 1,
            "editable_job_title" => 1,
            "editable_dob" => 1,
            "editable_name" => 1,
            "created_at" => "2024-08-08 14:56:34",
            "updated_at" => "2024-08-08 14:56:34",
            "show_phone" => 1,
            "required_phone" => 0,
            "editable_phone" => 1,
            "lms_id" => 1,
            "show_institute" => 0,
            "required_institute" => 0,
            "editable_institute" => 1
        ];
        $this->affiliateRepo = $affiliateRepo;
    }

    public function showRegistrationFrom()
    {
        //
        $page = $this->page;
        $custom_field = $this->custom_field;

        return view('affiliate.auth.registration', compact('page', 'custom_field'));
    }
    public function generateUrl($dataGenerate)
    {
        //
        return $this->affiliateRepo->firstCreate($dataGenerate);
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|regex:/^[0-9]{10,15}$/|unique:users,phone',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'affiliate_request' => 1,
            'language_code' => 'en',
            'department_id' => 5,
            'referral' => generateUniqueId(),
            'position_id' => 13,
            'password' => bcrypt($validated['password']),
        ]);


        $user->assignRole('Affiliate-user');

        $referral = $user->referral;
        $user_id = $user->id;
        // gennerate url
        if ($referral) {
            # code...
            $user_name = $referral;
            $url = route('affiliate.courses');
            $affiliate_link =  $url  . '?ref=' . $referral;
            // dd($affiliate_link, $url, $user_name);
            $dataGenerate = [
                '_token' => csrf_token(),
                'user_id' => $user_id,
                'user_name' => $user_name,
                'url' => $url,
                'affiliate_link' => $affiliate_link
            ];
            // dd($dataGenerate);
            $this->generateUrl($dataGenerate);
        }

        auth()->login($user);

        // Redirect or return response
        return redirect()->route('my_affiliate.index')->with('success', 'Registration successful!');
    }

    public function login()
    {
        // if (Storage::has('.app_resetting')) {
        //     return new response(view('reset'));
        // }
        // $token = request('token');
        // if ($token && Storage::exists($token)) {
        //     $content = Storage::get($token);
        //     $data = explode('|', $content);
        //     if ($data && count($data) == 2) {
        //         $email = $data[0];
        //         $password = $data[1];
        //         $user = User::where('email', $email)->where('lms_id', app('institute')->id)->first();

        //         if ($user && Hash::check($password, $user->password)) {
        //             Auth::login($user);
        //             Storage::delete($token);
        //             return redirect()->route('home');
        //         }
        //     }
        // }
        $page = $this->page;
        if (Auth::check()) {
            return redirect()->route('welcome');
        }
        return view('affiliate.auth.login', compact('page'));
    }
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function addOrUpdatePaypalAccount(Request $request)
    {
        // dd($request->all());
        $validate_rules = [
            'paypal_account' => 'required',
        ];
        $request->validate($validate_rules, validationMessage($validate_rules));
        try {
            $this->affiliateRepo->addOrUpdatePaypalAccountUser($request->all());
            $this->affiliateRepo->addOrUpdateBankAccount($request->all());
            Toastr::success('Operation successful', 'Success');
            return back();
        } catch (Exception $e) {
            Toastr::error($e->getMessage(), trans('common.Error'));
            return response()->json(['error' => $e->getMessage()], 503);
        }
    }

    public function create()
    {
        //
    }
    public function logout(Request $request)
    {
        //
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('affiliate.login')->with('success', 'You have successfully logged out.');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
