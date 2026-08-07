<?php

namespace App\Domain\Affiliate\Listeners;

use App\Domain\Affiliate\Services\AffiliateAccountService;
use Illuminate\Auth\Events\Verified;

class CompleteAffiliateEmailVerification
{
    public function __construct(private readonly AffiliateAccountService $account_service)
    {
    }

    public function handle(Verified $event): void
    {
        if ($event->user->AffiliateProfile()->exists()) {
            $this->account_service->MarkEmailVerified($event->user);
        }
    }
}
