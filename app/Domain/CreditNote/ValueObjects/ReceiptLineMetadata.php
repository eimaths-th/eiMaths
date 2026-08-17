<?php

namespace App\Domain\CreditNote\ValueObjects;

use App\Domain\CreditNote\Enums\ReceiptLineType;
use App\Domain\CreditNote\Enums\TaxTreatment;

final class ReceiptLineMetadata
{
    public function __construct(
        public readonly ReceiptLineType $line_type,
        public readonly TaxTreatment $tax_treatment,
        public readonly int $vat_rate_basis_points,
    ) {
    }
}
