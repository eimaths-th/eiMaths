<?php

namespace App\Http\Controllers\Affiliate;

use App\Domain\Affiliate\Models\AffiliateWithdrawalRequest;
use App\Domain\Affiliate\Services\AffiliateWithdrawalService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Affiliate\CreateWithdrawalRequest;
use App\Http\Requests\Affiliate\WithdrawalTransitionRequest;

class AffiliateWithdrawalController extends Controller
{
    public function __construct(private readonly AffiliateWithdrawalService $withdrawal_service)
    {
    }

    public function Store(CreateWithdrawalRequest $request)
    {
        $withdrawal = $this->withdrawal_service->RequestWithdrawal(
            $request->user(),
            $request->validated('amount'),
            (int) $request->validated('payout_account_id'),
            $request->validated('request_key'),
            $this->CorrelationId($request),
        );

        if (!$request->expectsJson()) {
            return redirect()->route('affiliate.withdrawals.index')->with('success', 'Withdrawal request submitted.');
        }

        return response()->json($this->Serialize($withdrawal), 201);
    }

    public function Cancel(AffiliateWithdrawalRequest $withdrawal, WithdrawalTransitionRequest $request)
    {
        $result = $this->withdrawal_service->Cancel(
            $withdrawal,
            $request->user(),
            (string) ($request->validated('reason') ?? ''),
            $request->validated('transition_key'),
            $this->CorrelationId($request),
        );

        if (!$request->expectsJson()) {
            return redirect()->route('affiliate.withdrawals.index')->with('success', 'Withdrawal request cancelled.');
        }

        return response()->json($this->Serialize($result));
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
            'approved_at' => $withdrawal->approved_at?->toIso8601String(),
            'paid_at' => $withdrawal->paid_at?->toIso8601String(),
        ];
    }

    private function CorrelationId($request): string
    {
        return (string) ($request->attributes->get('affiliate_correlation_id') ?: \Illuminate\Support\Str::uuid());
    }
}
