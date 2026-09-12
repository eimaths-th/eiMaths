<?php

namespace App\Exceptions;

use App\Domain\Affiliate\Enums\AffiliateProfileStatus;
use App\Domain\Affiliate\Exceptions\AffiliateDomainException;
use App\Domain\CreditNote\Exceptions\CreditNoteDomainException;
use App\Models\User;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Support\Facades\URL;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->renderable(function (InvalidSignatureException $exception, Request $request) {
            $context = $this->ExpiredEmailVerificationContext($request);

            if ($context === null) {
                return null;
            }

            app()->setLocale($context['locale']);

            return response()->view('auth.verification-expired', $context, 403);
        });

        $this->renderable(function (AffiliateDomainException $exception, Request $request) {
            $payload = [
                'error_code' => $exception->ErrorCode(),
                'message' => __($exception->SafeTranslationKey()),
            ];

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json($payload, $exception->StatusCode());
            }

            return back()->withErrors(['affiliate' => $payload['message']]);
        });

        $this->renderable(function (CreditNoteDomainException $exception, Request $request) {
            $message = __($exception->SafeTranslationKey());
            if ($request->expectsJson() || $request->isJson() || $request->wantsJson()) {
                return response()->json([
                    'error_code' => $exception->ErrorCode(),
                    'message' => $message,
                    'details' => [],
                    'correlation_id' => $request->attributes->get('credit_note_correlation_id'),
                ], $exception->StatusCode());
            }

            return back()
                ->withErrors([$message])
                ->with('credit_note_error_code', $exception->ErrorCode());
        });

        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Build a safe recovery path only for a genuine verification URL whose
     * signature is still correct and whose expiry timestamp has passed.
     * Altered verification URLs continue to use the generic 403 response.
     *
     * @return array{locale: string, resend_route: ?string, expiration_minutes: int}|null
     */
    private function ExpiredEmailVerificationContext(Request $request): ?array
    {
        if (! $request->routeIs('verification.verify')) {
            return null;
        }

        $expires = filter_var($request->query('expires'), FILTER_VALIDATE_INT);
        if (
            $expires === false
            || $expires >= now()->getTimestamp()
            || ! URL::hasCorrectSignature($request)
        ) {
            return null;
        }

        $user_id = filter_var($request->route('id'), FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);
        $verification_user = $user_id === false
            ? null
            : User::query()->with('AffiliateProfile')->find($user_id);
        $hash_matches = $verification_user instanceof User
            && hash_equals(
                sha1($verification_user->getEmailForVerification()),
                (string) $request->route('hash'),
            );

        $resend_route = null;
        if (
            $hash_matches
            && $verification_user->email_verified_at === null
            && $verification_user->AffiliateProfile?->status === AffiliateProfileStatus::PENDING_EMAIL
        ) {
            $request->session()->put('affiliate_pending_user_id', $verification_user->getKey());
            $resend_route = route('affiliate.verification.resend');
        } elseif (
            $hash_matches
            && $verification_user->email_verified_at === null
            && $request->user() instanceof User
            && $request->user()->is($verification_user)
        ) {
            $resend_route = route('verification.resend');
        }

        return [
            'locale' => $this->VerificationLocale($verification_user),
            'resend_route' => $resend_route,
            'expiration_minutes' => (int) config('auth.verification.expire', 60),
        ];
    }

    private function VerificationLocale(?User $user): string
    {
        $supported_locales = array_values(array_filter(array_map(
            static fn (mixed $locale): string => strtolower(trim((string) $locale)),
            (array) config('affiliate.supported_locales', ['th', 'en']),
        )));
        $default_locale = strtolower(trim((string) config('affiliate.default_locale', 'th')));
        if (! in_array($default_locale, $supported_locales, true)) {
            $default_locale = 'th';
        }

        $user_locale = strtolower(trim((string) ($user?->language_code ?? '')));

        return in_array($user_locale, $supported_locales, true)
            ? $user_locale
            : $default_locale;
    }
}
