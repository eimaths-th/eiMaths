<?php

namespace App\Domain\CreditNote\Enums;

enum TaxTreatment: string
{
    case VAT_INCLUSIVE = 'vat_inclusive';
    case VAT_EXEMPT = 'vat_exempt';
    case OUT_OF_SCOPE = 'out_of_scope';
}
