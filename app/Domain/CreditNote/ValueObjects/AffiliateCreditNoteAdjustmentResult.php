<?php

namespace App\Domain\CreditNote\ValueObjects;

final class AffiliateCreditNoteAdjustmentResult
{
    private function __construct(
        public readonly bool $applicable,
        public readonly int $tuition_reversal_delta_minor,
        public readonly int $commission_reversal_delta_minor,
    ) {
    }

    public static function NotApplicable(): self
    {
        return new self(false, 0, 0);
    }

    public static function Applied(int $tuition_reversal_delta_minor, int $commission_reversal_delta_minor): self
    {
        return new self(true, $tuition_reversal_delta_minor, $commission_reversal_delta_minor);
    }
}
