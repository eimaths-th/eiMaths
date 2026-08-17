<?php

namespace App\Http\Middleware;

use App\Domain\Affiliate\Services\AffiliateAttributionService;
use App\Domain\Affiliate\Services\AffiliateAuditService;
use App\Domain\Affiliate\Services\AffiliateClickTrackingService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class CaptureAffiliateReferral
{
    public function __construct(
        private readonly AffiliateAttributionService $attribution_service,
        private readonly AffiliateClickTrackingService $tracking_service,
        private readonly AffiliateAuditService $audit_service,
    )
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $referral_code = trim((string) $request->query('ref', ''));
        if ($referral_code !== '') {
            $target = $this->tracking_service->ResolveDirect($referral_code, $request->getPathInfo());
            if ($target !== null) {
                $correlation_id = (string) ($request->attributes->get('affiliate_correlation_id') ?: Str::uuid());
                $click_context = null;
                try {
                    $click_context = $this->tracking_service->Record($request, $target, $correlation_id);
                } catch (Throwable $exception) {
                    $this->audit_service->EmitSignal('click.failed', $correlation_id, [
                        'operation' => 'referral-middleware.record',
                        'outcome' => 'FAILED',
                        'affiliate_profile_id' => $target->affiliate_profile->id,
                        'campaign_code' => null,
                        'exception_class_code' => get_class($exception),
                        'occurred_at' => now()->toIso8601String(),
                    ]);
                }
                $context = $this->attribution_service->CaptureResolvedReferral(
                    $request,
                    $target->affiliate_profile,
                    $click_context,
                );
                if ($context !== null) {
                    $request->attributes->set('affiliate_referral_context', $context);
                }

                return $next($request);
            }
        }

        $context = $this->attribution_service->CaptureReferral($request);
        if ($context !== null) {
            $request->attributes->set('affiliate_referral_context', $context);
        }

        return $next($request);
    }
}
