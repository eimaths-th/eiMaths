<?php

namespace App\Domain\CreditNote\Enums;

enum CreditNoteEventOperation: string
{
    case CREATED = 'created';
    case UPDATED = 'updated';
    case ISSUED = 'issued';
    case CANCELLED = 'cancelled';
}
