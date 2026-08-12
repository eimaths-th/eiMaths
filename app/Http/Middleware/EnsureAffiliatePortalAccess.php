<?php

namespace App\Http\Middleware;

use App\Domain\Affiliate\Exceptions\AffiliateDomainException;
use App\Domain\Affiliate\Services\AffiliateEmployeeEligibility;
use App\Domain\Affiliate\Services\AffiliatePortalAccess;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureAffiliatePortalAccess
{
    public function __construct(
        private readonly AffiliatePortalAccess $portal_access,
        private readonly AffiliateEmployeeEligibility $eligibility,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user instanceof User && $this->portal_access->CanAccess($user)) {
            return $next($request);
        }

        $exception = AffiliateDomainException::OwnershipDenied();
        $message = __($exception->SafeTranslationKey());

        if ($request->expectsJson()) {
            return response()->json([
                'error_code' => $exception->ErrorCode(),
                'message' => $message,
            ], $exception->StatusCode());
        }

        if ($user instanceof User && $request->isMethodSafe()) {
            if ($this->eligibility->IsEligible($user)) {
                return redirect()->route('affiliate.employee-access.show');
            }

            if ($this->eligibility->IsAffiliateOnly($user)) {
                return redirect()->route('affiliate.waiting');
            }
        }

        return response($message, $exception->StatusCode());
    }
}
