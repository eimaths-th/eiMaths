<?php

namespace App\Domain\Affiliate\Listeners;

use App\Domain\Affiliate\Events\AffiliatePaymentConfirmed;
use App\Domain\Affiliate\Services\AffiliateCommissionService;
use App\Models\Receipt;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;

class CreateAffiliateCommission implements ShouldQueue, ShouldQueueAfterCommit
{
    public int $tries = 5;

    public function backoff(): array
    {
        return [10, 30, 60, 120, 300];
    }

    public function __construct(private readonly AffiliateCommissionService $commission_service)
    {
    }

    public function handle(AffiliatePaymentConfirmed $event): void
    {
        $receipt = Receipt::findOrFail($event->receipt_id);
        $this->commission_service->CreateForConfirmedPayment($receipt, $event->confirmed_at, $event->correlation_id);
    }
}
