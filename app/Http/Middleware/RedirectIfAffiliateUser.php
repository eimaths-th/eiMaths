<?php

namespace App\Http\Middleware;

use App\Domain\Affiliate\Services\AffiliateEmployeeEligibility;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAffiliateUser
{
    public function __construct(
        private readonly AffiliateEmployeeEligibility $eligibility,
    ) {
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user instanceof User && $this->eligibility->IsAffiliateOnly($user)) {
            return $user->AffiliateProfile?->status?->value === 'ACTIVE'
                ? redirect()->route('affiliate.dashboard')
                : redirect()->route('affiliate.waiting');
        }

        return $next($request);
    }
}
