<?php

namespace App\Domain\CreditNote\ValueObjects;

final class CreditNoteEligibility
{
    public function __construct(
        private readonly bool $eligible,
        public readonly ?string $blocking_code,
        public readonly array $remaining_lines,
        public readonly int $remaining_grand_total_minor,
    ) {
    }

    public function IsEligible(): bool
    {
        return $this->eligible;
    }
}
