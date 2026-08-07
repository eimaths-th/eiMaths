<?php

namespace App\Http\Middleware;

use App\Domain\Affiliate\Services\AffiliateAttributionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CaptureAffiliateReferral
{
    public function __construct(private readonly AffiliateAttributionService $attribution_service)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $context = $this->attribution_service->CaptureReferral($request);
        if ($context !== null) {
            $request->attributes->set('affiliate_referral_context', $context);
        }

        return $next($request);
    }
}
