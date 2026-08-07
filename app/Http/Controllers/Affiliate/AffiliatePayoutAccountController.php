<?php

namespace App\Http\Controllers\Affiliate;

use App\Domain\Affiliate\Services\AffiliatePayoutAccountService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Affiliate\UpsertBankAccountRequest;
use App\Http\Requests\Affiliate\UpsertPromptPayAccountRequest;

class AffiliatePayoutAccountController extends Controller
{
    public function __construct(private readonly AffiliatePayoutAccountService $account_service)
    {
    }

    public function PromptPay(UpsertPromptPayAccountRequest $request)
    {
        $result = $this->account_service->UpsertPromptPay($request->user(), $request->validated(), (string) ($request->attributes->get('affiliate_correlation_id') ?: \Illuminate\Support\Str::uuid()));

        if (!$request->expectsJson()) {
            return redirect()->route('affiliate.payout-accounts.index')->with('success', 'PromptPay payout account saved.');
        }

        return response()->json($result);
    }

    public function Bank(UpsertBankAccountRequest $request)
    {
        $result = $this->account_service->UpsertBank($request->user(), $request->validated(), (string) ($request->attributes->get('affiliate_correlation_id') ?: \Illuminate\Support\Str::uuid()));

        if (!$request->expectsJson()) {
            return redirect()->route('affiliate.payout-accounts.index')->with('success', 'Bank payout account saved.');
        }

        return response()->json($result);
    }
}
