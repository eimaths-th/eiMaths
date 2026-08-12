<?php

namespace App\Domain\CreditNote\Commands;

final class CancelCreditNote
{
    public function __construct(
        public readonly string $reason,
        public readonly string $idempotency_key,
    ) {
    }
}
