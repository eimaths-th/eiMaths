<?php

namespace App\Domain\CreditNote\ValueObjects;

final class CreditNoteCalculation
{
    public function __construct(
        private readonly array $line_allocations,
        private readonly CreditNoteTotals $original,
        private readonly CreditNoteTotals $corrected,
        private readonly CreditNoteTotals $difference,
    ) {
    }

    public function LineAllocations(): array
    {
        return $this->line_allocations;
    }

    public function Original(): CreditNoteTotals
    {
        return $this->original;
    }

    public function Corrected(): CreditNoteTotals
    {
        return $this->corrected;
    }

    public function Difference(): CreditNoteTotals
    {
        return $this->difference;
    }
}
