<?php

namespace App\Http\Controllers\Affiliate;

use App\Domain\Affiliate\Models\AffiliateReconciliationIssue;
use App\Domain\Affiliate\Services\AffiliateReconciliationService;
use App\Domain\Affiliate\Services\AffiliateWalletService;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Domain\Affiliate\Enums\AffiliateLedgerEntryType;
use App\Domain\Affiliate\ValueObjects\Money;
use App\Http\Controllers\Controller;
use App\Http\Requests\Affiliate\AffiliateAdjustmentRequest;
use App\Http\Requests\Affiliate\ReconciliationResolutionRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AffiliateReconciliationController extends Controller
{
    public function __construct(
        private readonly AffiliateReconciliationService $reconciliation_service,
        private readonly AffiliateWalletService $wallet_service,
    )
    {
    }

    public function Index()
    {
        return response()->json(AffiliateReconciliationIssue::query()->latest('id')->paginate(50));
    }

    public function Resolve(AffiliateReconciliationIssue $issue, ReconciliationResolutionRequest $request): JsonResponse
    {
        $resolved = $this->reconciliation_service->ResolveIssue($issue, $request->user(), $request->validated('resolution_note'), $request->validated('adjustment_ledger_entry_id'));
        return response()->json(['id' => $resolved->id, 'status' => $resolved->status?->value, 'resolved_at' => $resolved->resolved_at?->toIso8601String()]);
    }

    public function Adjust(AffiliateReconciliationIssue $issue, AffiliateAdjustmentRequest $request): JsonResponse
    {
        if ($issue->source_table !== 'affiliate_wallet_balances') {
            throw \App\Domain\Affiliate\Exceptions\AffiliateDomainException::StateConflict('Only wallet reconciliation issues can receive an adjustment.');
        }
        $profile = AffiliateProfile::query()->findOrFail($issue->source_id);
        $entry = $this->wallet_service->ApplyEntry(
            $profile,
            AffiliateLedgerEntryType::ADJUSTMENT,
            Money::FromDecimal($request->validated('available_amount'))->Minor(),
            0,
            'affiliate_reconciliation_issue',
            $issue->id,
            $request->validated('idempotency_key'),
            $request->user(),
            'ADJUSTMENT:' . $request->validated('reason'),
            $this->CorrelationId($request),
        );
        return response()->json(['ledger_entry_id' => $entry->id, 'affiliate_profile_id' => $profile->id, 'available_delta_minor' => $entry->available_delta_minor], 201);
    }

    private function CorrelationId(Request $request): string
    {
        return (string) ($request->attributes->get('affiliate_correlation_id') ?: \Illuminate\Support\Str::uuid());
    }
}
