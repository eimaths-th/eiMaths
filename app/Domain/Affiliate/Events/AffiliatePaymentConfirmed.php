<?php

namespace App\Domain\Affiliate\Events;

use Carbon\CarbonImmutable;
use DateTimeInterface;

class AffiliatePaymentConfirmed
{
    public function __construct(
        public readonly int $receipt_id,
        public readonly DateTimeInterface $confirmed_at,
        public readonly string $correlation_id,
    ) {
    }
}
