<?php

namespace App\Domain\CreditNote\ValueObjects;

use App\Domain\CreditNote\Enums\CreditNoteSourceType;
use App\Domain\CreditNote\Enums\ReceiptLineType;
use App\Domain\CreditNote\Enums\TaxTreatment;

final class CreditNoteSourceLine
{
    public function __construct(
        public readonly CreditNoteSourceType $source_type,
        public readonly int $source_id,
        public readonly ?ReceiptLineType $line_type,
        public readonly string $description,
        public readonly ?string $unit,
        public readonly FixedQuantity $original_quantity,
        public readonly int $original_unit_price_minor,
        public readonly int $original_discount_minor,
        public readonly int $original_line_total_minor,
        public readonly ?TaxTreatment $tax_treatment,
        public readonly ?int $vat_rate_basis_points,
        public readonly bool $has_complete_source_values = true,
    ) {
    }
}
