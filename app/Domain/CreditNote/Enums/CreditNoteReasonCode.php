<?php

namespace App\Domain\CreditNote\Enums;

enum CreditNoteReasonCode: string
{
    case PRODUCT_PRICE_OVERSTATED = 'product_price_overstated';
    case SERVICE_PRICE_OVERSTATED = 'service_price_overstated';
    case PRODUCT_DEFECT_DAMAGE_OR_SHORTAGE = 'product_defect_damage_or_shortage';
    case SERVICE_NONCONFORMITY_OR_SHORTAGE = 'service_nonconformity_or_shortage';
    case PRODUCT_RETURN = 'product_return';
    case STATUTORY_COMPENSATION = 'statutory_compensation';
    case ADVANCE_DEPOSIT_OR_RESERVATION_RETURN = 'advance_deposit_or_reservation_return';
    case COMMERCIAL_RETURN_OR_EXCHANGE = 'commercial_return_or_exchange';
    case DEFECTIVE_SERVICE_TERMINATION = 'defective_service_termination';
    case SERVICE_NOT_PROVIDED_TERMINATION = 'service_not_provided_termination';
    case OTHER = 'other';
}
