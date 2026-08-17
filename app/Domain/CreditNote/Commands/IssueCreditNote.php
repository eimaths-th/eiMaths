<?php

namespace App\Domain\CreditNote\Commands;

final class IssueCreditNote
{
    public function __construct(
        public readonly int $lock_version,
        public readonly string $idempotency_key,
    ) {
    }
}
