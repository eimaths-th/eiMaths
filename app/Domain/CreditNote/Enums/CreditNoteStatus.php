<?php

namespace App\Domain\CreditNote\Enums;

enum CreditNoteStatus: string
{
    case DRAFT = 'draft';
    case ISSUED = 'issued';
    case CANCELLED = 'cancelled';
}
