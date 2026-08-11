<?php

namespace App\Http\Controllers;

use App\Domain\Affiliate\Enums\AffiliateProfileStatus;
use App\Models\User;
use App\Domain\Affiliate\Services\AffiliateAccountService;
use App\Http\Requests\Affiliate\RegisterAffiliateRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class AffiliateAuthController extends Controller
{
    private const PENDING_USER_ID_SESSION_KEY = 'affiliate_pending_user_id';

    /**
     * Display a listing of the resource.
     */
    public $page = [];
    public $custom_field = [];
    protected AffiliateAccountService $account_service;

    // Constructor or method to initialize the $page property
    public function __construct(AffiliateAccountService $account_service)
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
        $this->account_service = $account_service;
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
        abort(410, 'Legacy Affiliate link generation is no longer available.');
    }

    public function register(RegisterAffiliateRequest $request)
    {
        $affiliate_user = $this->account_service->Register($request->validated());
        $request->session()->put(self::PENDING_USER_ID_SESSION_KEY, $affiliate_user->getKey());

        return redirect()->route('affiliate.waiting')->with('success', 'Registration received. Verify your email before approval.');
    }

    public function Waiting(Request $request): RedirectResponse|View
    {
        $affiliate_user = $request->user();

        if (
            $affiliate_user instanceof User
            && $affiliate_user->hasRole('Affiliate-user')
            && $affiliate_user->AffiliateProfile?->status === AffiliateProfileStatus::ACTIVE
        ) {
            $request->session()->forget(self::PENDING_USER_ID_SESSION_KEY);

            return redirect()->route('affiliate.dashboard');
        }

        $session_affiliate_user = $this->PendingSessionUser($request);
        if (
            $request->user() === null
            && $session_affiliate_user?->AffiliateProfile?->status === AffiliateProfileStatus::ACTIVE
        ) {
            $request->session()->forget(self::PENDING_USER_ID_SESSION_KEY);

            return redirect()->route('affiliate.login')->with('affiliate_account_approved', true);
        }

        $pending_email_user = $this->PendingEmailUser($request);

        return view('affiliate.auth.waiting', [
            'show_resend_verification' => $pending_email_user instanceof User,
            'show_email_resend_fallback' => !$pending_email_user instanceof User
                && $request->user() === null
                && !$request->session()->has(self::PENDING_USER_ID_SESSION_KEY),
            'show_verified_waiting_message' => $session_affiliate_user?->email_verified_at !== null
                && $session_affiliate_user?->AffiliateProfile?->status === AffiliateProfileStatus::PENDING_APPROVAL,
        ]);
    }

    public function ResendVerification(Request $request): RedirectResponse
    {
        $pending_user = $this->PendingEmailUser($request);

        if (
            !$pending_user instanceof User
            && $request->user() === null
            && !$request->session()->has(self::PENDING_USER_ID_SESSION_KEY)
        ) {
            $validated = $request->validate([
                'email' => ['nullable', 'email', 'max:255'],
            ]);
            $email = strtolower(trim((string) ($validated['email'] ?? '')));

            if ($email !== '') {
                $pending_user = User::query()
                    ->whereRaw('LOWER(email) = ?', [$email])
                    ->whereNull('email_verified_at')
                    ->whereHas('AffiliateProfile', function ($query): void {
                        $query->where('status', AffiliateProfileStatus::PENDING_EMAIL->value);
                    })
                    ->first();
            }
        }

        if ($pending_user instanceof User) {
            $pending_user->sendEmailVerificationNotification();
        }

        return redirect()->route('affiliate.waiting')->with('affiliate_verification_sent', true);
    }

    private function PendingEmailUser(Request $request): ?User
    {
        $authenticated_user = $request->user();

        if (
            $authenticated_user instanceof User
            && $authenticated_user->email_verified_at === null
            && $authenticated_user->AffiliateProfile?->status === AffiliateProfileStatus::PENDING_EMAIL
        ) {
            return $authenticated_user;
        }

        $pending_user = $this->PendingSessionUser($request);
        if (
            !$pending_user instanceof User
            || $pending_user->email_verified_at !== null
            || $pending_user->AffiliateProfile?->status !== AffiliateProfileStatus::PENDING_EMAIL
        ) {
            return null;
        }

        return $pending_user;
    }

    private function PendingSessionUser(Request $request): ?User
    {
        $pending_user_id = $request->session()->get(self::PENDING_USER_ID_SESSION_KEY);
        if (is_int($pending_user_id)) {
            $normalized_user_id = $pending_user_id;
        } elseif (is_string($pending_user_id) && ctype_digit($pending_user_id)) {
            $normalized_user_id = (int) $pending_user_id;
        } else {
            return null;
        }

        return User::query()->whereKey($normalized_user_id)->first();
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
            return Auth::user()->hasRole('Affiliate-user')
                ? (Auth::user()->AffiliateProfile?->status?->value === 'ACTIVE'
                    ? redirect()->route('affiliate.dashboard')
                    : redirect()->route('affiliate.waiting'))
                : redirect()->route('welcome');
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
        abort(410, 'Legacy payout account endpoint has been replaced.');
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
