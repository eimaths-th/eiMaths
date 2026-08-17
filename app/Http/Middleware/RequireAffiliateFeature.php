<?php

namespace App\Http\Middleware;

use App\Domain\Affiliate\Exceptions\AffiliateDomainException;
use App\Domain\Affiliate\Services\AffiliateConfigurationService;
use App\Domain\Affiliate\Services\AffiliateFeatureGate;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireAffiliateFeature
{
    public function __construct(
        private readonly AffiliateFeatureGate $feature_gate,
        private readonly AffiliateConfigurationService $configuration_service,
    ) {
    }

    public function handle(Request $request, Closure $next, string $feature_name): Response
    {
        try {
            $this->feature_gate->AssertEnabled($feature_name);
            if (!$request->isMethodSafe()) {
                $this->configuration_service->AssertAffiliateWritesAvailable();
            }

            return $next($request);
        } catch (AffiliateDomainException $exception) {
            return response()->json([
                'error_code' => $exception->ErrorCode(),
                'message' => __($exception->SafeTranslationKey()),
            ], $exception->StatusCode());
        }
    }
}
