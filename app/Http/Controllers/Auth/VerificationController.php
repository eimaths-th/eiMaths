<?php

namespace App\Http\Controllers\Auth;

use App\Domain\Affiliate\Enums\AffiliateProfileStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\VerifiesEmails;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Email Verification Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling email verification for any
    | user that recently registered with the application. Emails may also
    | be re-sent if the user didn't receive the original email message.
    |
    */

    use VerifiesEmails {
        verify as protected VerifyAuthenticatedEmail;
    }

    private const PENDING_USER_ID_SESSION_KEY = 'affiliate_pending_user_id';

    /**
     * Where to redirect users after verification.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth')->only('show', 'resend');
        $this->middleware('signed')->only('verify');
        $this->middleware('throttle:6,1')->only('verify', 'resend');
    }

    public function verify(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->user() instanceof User) {
            return $this->VerifyAuthenticatedEmail($request);
        }

        $affiliate_user = User::query()->find($request->route('id'));
        if (!$affiliate_user instanceof User || !$this->IsGuestAffiliateVerification($affiliate_user)) {
            return redirect()->guest(route('login'));
        }

        if (!hash_equals((string) $request->route('hash'), sha1($affiliate_user->getEmailForVerification()))) {
            throw new AuthorizationException;
        }

        if (!$affiliate_user->hasVerifiedEmail()) {
            if ($affiliate_user->markEmailAsVerified()) {
                event(new Verified($affiliate_user));
            }
        }

        $request->session()->put(self::PENDING_USER_ID_SESSION_KEY, $affiliate_user->getKey());

        return redirect()->route('affiliate.waiting')->with('affiliate_email_verified', true);
    }

    private function IsGuestAffiliateVerification(User $user): bool
    {
        $profile_status = $user->AffiliateProfile?->status;

        return in_array(
            $profile_status,
            [
                AffiliateProfileStatus::PENDING_EMAIL,
                AffiliateProfileStatus::PENDING_APPROVAL,
            ],
            true,
        );
    }
}
