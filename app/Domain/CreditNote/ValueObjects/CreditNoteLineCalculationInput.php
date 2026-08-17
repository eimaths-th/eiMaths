<?php

namespace App\Domain\CreditNote\ValueObjects;

use App\Domain\CreditNote\Enums\TaxTreatment;

final class CreditNoteLineCalculationInput
{
    public function __construct(
        public readonly int $item_id,
        public readonly int $original_line_total_minor,
        public readonly int $credit_amount_minor,
        public readonly TaxTreatment $tax_treatment,
        public readonly int $vat_rate_basis_points,
        public readonly int $prior_net_credit_amount_minor = 0,
    ) {
    }
}
