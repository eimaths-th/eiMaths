<?php

namespace App\Http\Middleware;

use App\Domain\Affiliate\Exceptions\AffiliateDomainException;
use App\Domain\Affiliate\Services\AffiliateEmployeeEligibility;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureAffiliateEmployeeEligibility
{
    public function __construct(
        private readonly AffiliateEmployeeEligibility $eligibility,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user !== null && $this->eligibility->IsEligible($user)) {
            return $next($request);
        }

        $exception = AffiliateDomainException::EmployeeEnrollmentDenied();
        $message = __($exception->SafeTranslationKey());
        if ($request->expectsJson()) {
            return response()->json([
                'error_code' => $exception->ErrorCode(),
                'message' => $message,
            ], $exception->StatusCode());
        }

        return response($message, $exception->StatusCode());
    }
}
