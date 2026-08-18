<?php

namespace App\Http\Controllers\Affiliate;

use App\Domain\Affiliate\Models\AffiliateConfiguration;
use App\Domain\Affiliate\Services\AffiliateCommissionService;
use App\Domain\Affiliate\Services\AffiliateConfigurationService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Affiliate\AffiliateConfigurationUpdateRequest;
use App\Http\Requests\Affiliate\ConfirmedRefundRequest;
use App\Models\Receipt;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AffiliateFinanceAdminController extends Controller
{
    public function __construct(
        private readonly AffiliateConfigurationService $configuration_service,
        private readonly AffiliateCommissionService $commission_service,
    ) {
    }

    public function Configuration(): JsonResponse
    {
        return response()->json(AffiliateConfiguration::query()->orderBy('key')->get(['key', 'value']));
    }

    public function UpdateConfiguration(AffiliateConfigurationUpdateRequest $request): JsonResponse
    {
        $values = $request->validated();
        $this->configuration_service->UpdateApprovedSettings($values, $request->user(), $this->CorrelationId($request));
        return $this->Configuration();
    }

    public function RecordRefund(Receipt $receipt, ConfirmedRefundRequest $request): JsonResponse
    {
        $reversal = $this->commission_service->RecordConfirmedExternalRefund(
            $receipt,
            $request->validated('refunded_tuition_amount'),
            $request->validated('external_refund_reference'),
            $request->validated('reason'),
            $request->validated('idempotency_key'),
            $request->user(),
            $this->CorrelationId($request),
        );
        return response()->json(['id' => $reversal->id, 'affiliate_commission_id' => $reversal->affiliate_commission_id, 'reversal_minor' => $reversal->reversal_minor], 201);
    }

    private function CorrelationId(Request $request): string
    {
        return (string) ($request->attributes->get('affiliate_correlation_id') ?: \Illuminate\Support\Str::uuid());
    }
}
