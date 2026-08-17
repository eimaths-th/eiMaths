<?php

namespace App\Http\Controllers\Affiliate;

use App\Domain\Affiliate\Services\AffiliateAttributionService;
use App\Domain\Affiliate\Services\AffiliateAuditService;
use App\Domain\Affiliate\Services\AffiliateClickTrackingService;
use App\Domain\Affiliate\ValueObjects\AffiliateTrackingTarget;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

class AffiliateTrackingController
{
    public function __construct(
        private readonly AffiliateClickTrackingService $tracking_service,
        private readonly AffiliateAttributionService $attribution_service,
        private readonly AffiliateAuditService $audit_service,
    ) {
    }

    public function Direct(Request $request, string $referral_code): RedirectResponse
    {
        try {
            $target = $this->tracking_service->ResolveDirect($referral_code, '/');
        } catch (InvalidArgumentException) {
            $target = null;
        }

        return $this->RedirectForTarget($request, $target, 'direct');
    }

    public function Media(Request $request, string $referral_code, string $campaign_code): RedirectResponse
    {
        try {
            $target = $this->tracking_service->ResolveMedia($referral_code, $campaign_code);
        } catch (InvalidArgumentException) {
            $target = null;
        }

        return $this->RedirectForTarget($request, $target, 'media');
    }

    private function RedirectForTarget(
        Request $request,
        ?AffiliateTrackingTarget $target,
        string $operation,
    ): RedirectResponse {
        if ($target === null) {
            return redirect()->to('/', 302);
        }

        $correlation_id = (string) ($request->attributes->get('affiliate_correlation_id') ?: Str::uuid());
        $click_context = null;
        try {
            $click_context = $this->tracking_service->Record($request, $target, $correlation_id);
        } catch (Throwable $exception) {
            $this->audit_service->EmitSignal('click.failed', $correlation_id, [
                'operation' => $operation . '.record',
                'outcome' => 'FAILED',
                'affiliate_profile_id' => $target->affiliate_profile->id,
                'campaign_code' => $target->campaign_code,
                'exception_class_code' => get_class($exception),
                'occurred_at' => now()->toIso8601String(),
            ]);
        }

        $this->attribution_service->CaptureResolvedReferral(
            $request,
            $target->affiliate_profile,
            $click_context,
        );

        return redirect()->to($target->destination_target, 302);
    }
}
