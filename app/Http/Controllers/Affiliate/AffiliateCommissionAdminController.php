<?php

namespace App\Http\Controllers\Affiliate;

use App\Domain\Affiliate\Models\AffiliateCommission;
use App\Domain\Affiliate\Services\AffiliateCommissionService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Affiliate\CommissionHoldRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AffiliateCommissionAdminController extends Controller
{
    public function __construct(private readonly AffiliateCommissionService $commission_service)
    {
    }

    public function Index()
    {
        return response()->json(AffiliateCommission::query()->with('AffiliateProfile')->latest('id')->paginate(50));
    }

    public function Hold(AffiliateCommission $commission, CommissionHoldRequest $request): JsonResponse
    {
        $result = $this->commission_service->Hold($commission, $request->user(), $request->validated('reason'), $this->CorrelationId($request));
        return response()->json($this->Serialize($result));
    }

    public function ReleaseHold(AffiliateCommission $commission, Request $request): JsonResponse
    {
        $result = $this->commission_service->ReleaseHold($commission, $request->user(), $this->CorrelationId($request));
        return response()->json($this->Serialize($result));
    }

    private function Serialize(AffiliateCommission $commission): array
    {
        return [
            'id' => $commission->id,
            'affiliate_profile_id' => $commission->affiliate_profile_id,
            'order_id' => $commission->order_id,
            'receipt_id' => $commission->receipt_id,
            'commission_minor' => $commission->commission_minor,
            'reversed_minor' => $commission->reversed_minor,
            'status' => $commission->status?->value,
            'release_at' => $commission->release_at?->toIso8601String(),
            'held_at' => $commission->held_at?->toIso8601String(),
        ];
    }

    private function CorrelationId(Request $request): string
    {
        return (string) ($request->attributes->get('affiliate_correlation_id') ?: \Illuminate\Support\Str::uuid());
    }
}
