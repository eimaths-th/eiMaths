<?php

namespace App\Domain\CreditNote\ValueObjects;

final class CreditNoteTotals
{
    public function __construct(
        public readonly int $taxable_base_minor,
        public readonly int $vat_minor,
        public readonly int $exempt_minor,
        public readonly int $out_of_scope_minor,
        public readonly int $grand_total_minor,
    ) {
    }

    public static function Zero(): self
    {
        return new self(0, 0, 0, 0, 0);
    }
}
