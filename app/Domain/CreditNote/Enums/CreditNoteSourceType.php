<?php

namespace App\Domain\CreditNote\Enums;

enum CreditNoteSourceType: string
{
    case RECEIPT_DETAIL = 'receipt_detail';
    case RECEIPT_SERVICE_CHARGE = 'receipt_service_charge';
}
