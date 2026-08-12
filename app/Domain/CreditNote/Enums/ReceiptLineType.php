<?php

namespace App\Domain\CreditNote\Enums;

enum ReceiptLineType: string
{
    case TUITION = 'tuition';
    case BOOK = 'book';
    case DEPOSIT = 'deposit';
    case REGISTRATION_FEE = 'registration_fee';
    case EQUIPMENT = 'equipment';
    case OTHER = 'other';
    case SERVICE_CHARGE = 'service_charge';
    case UNKNOWN = 'unknown';
}
