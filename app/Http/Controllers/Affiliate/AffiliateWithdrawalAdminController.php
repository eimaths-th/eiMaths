<?php

namespace App\Http\Controllers\Affiliate;

use App\Domain\Affiliate\Models\AffiliateWithdrawalRequest;
use App\Domain\Affiliate\Services\AffiliateWithdrawalService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Affiliate\BulkApproveWithdrawalRequest;
use App\Http\Requests\Affiliate\PayWithdrawalRequest;
use App\Http\Requests\Affiliate\WithdrawalTransitionRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AffiliateWithdrawalAdminController extends Controller
{
    public function __construct(
        private readonly AffiliateWithdrawalService $withdrawal_service,
        private readonly \App\Domain\Affiliate\Services\AffiliatePayoutDetailsRevealService $payout_details_reveal_service,
    )
    {
    }

    public function Index(Request $request)
    {
        $withdrawals = AffiliateWithdrawalRequest::query()
            ->with(['AffiliateProfile.User', 'AffiliatePayoutAccount'])
            ->latest('id')
            ->paginate(50);

        if (!$request->expectsJson()) {
            return view('affiliate.admin.withdrawals', compact('withdrawals'));
        }

        $withdrawals->getCollection()->each(function (AffiliateWithdrawalRequest $withdrawal): void {
            $withdrawal->makeHidden(['payout_snapshot_encrypted']);
            $withdrawal->AffiliatePayoutAccount?->makeHidden(['details_encrypted']);
        });

        return response()->json($withdrawals);
    }

    public function RevealPayoutDetails(AffiliateWithdrawalRequest $withdrawal, Request $request): JsonResponse
    {
        $result = $this->payout_details_reveal_service->Reveal(
            $withdrawal,
            $request->user(),
            $this->CorrelationId($request),
        );

        return response()->json(['data' => $result])->header('Cache-Control', 'no-store, private');
    }

    public function Approve(AffiliateWithdrawalRequest $withdrawal, WithdrawalTransitionRequest $request)
    {
        $result = $this->withdrawal_service->Approve(
            $withdrawal,
            $request->user(),
            $request->validated('transition_key'),
            $this->CorrelationId($request),
        );
        if (!$request->expectsJson()) {
            return redirect()->route('affiliate.withdrawals.admin.index')->with('success', 'Withdrawal approved.');
        }

        return response()->json($this->Serialize($result));
    }

    public function Pay(AffiliateWithdrawalRequest $withdrawal, PayWithdrawalRequest $request)
    {
        $result = $this->withdrawal_service->Pay(
            $withdrawal,
            $request->user(),
            $request->validated('payment_reference'),
            $request->validated('transition_key'),
            $this->CorrelationId($request),
        );
        if (!$request->expectsJson()) {
            return redirect()->route('affiliate.withdrawals.admin.index')->with('success', 'Withdrawal marked as paid.');
        }

        return response()->json($this->Serialize($result));
    }

    public function Reject(AffiliateWithdrawalRequest $withdrawal, WithdrawalTransitionRequest $request)
    {
        $result = $this->withdrawal_service->Reject(
            $withdrawal,
            $request->user(),
            (string) ($request->validated('reason') ?? ''),
            $request->validated('transition_key'),
            $this->CorrelationId($request),
        );
        if (!$request->expectsJson()) {
            return redirect()->route('affiliate.withdrawals.admin.index')->with('success', 'Withdrawal rejected.');
        }

        return response()->json($this->Serialize($result));
    }

    public function BulkApprove(BulkApproveWithdrawalRequest $request): JsonResponse
    {
        $results = DB::transaction(function () use ($request): array {
            $rows = [];
            foreach ($request->validated('withdrawal_ids') as $withdrawal_id) {
                $withdrawal = AffiliateWithdrawalRequest::query()->findOrFail($withdrawal_id);
                $rows[] = $this->withdrawal_service->Approve(
                    $withdrawal,
                    $request->user(),
                    $request->validated('approval_key') . ':' . $withdrawal_id,
                    $this->CorrelationId($request),
                );
            }
            return $rows;
        });

        return response()->json(['data' => array_map(fn (AffiliateWithdrawalRequest $row): array => $this->Serialize($row), $results)]);
    }

    private function Serialize(AffiliateWithdrawalRequest $withdrawal): array
    {
        return [
            'id' => $withdrawal->id,
            'affiliate_profile_id' => $withdrawal->affiliate_profile_id,
            'payout_type' => $withdrawal->payout_type?->value,
            'amount_minor' => $withdrawal->amount_minor,
            'status' => $withdrawal->status?->value,
            'requested_at' => $withdrawal->requested_at?->toIso8601String(),
            'approved_by' => $withdrawal->approved_by,
            'approved_at' => $withdrawal->approved_at?->toIso8601String(),
            'paid_by' => $withdrawal->paid_by,
            'paid_at' => $withdrawal->paid_at?->toIso8601String(),
            'payment_reference' => $withdrawal->payment_reference,
        ];
    }

    private function CorrelationId($request): string
    {
        return (string) ($request->attributes->get('affiliate_correlation_id') ?: \Illuminate\Support\Str::uuid());
    }
}
