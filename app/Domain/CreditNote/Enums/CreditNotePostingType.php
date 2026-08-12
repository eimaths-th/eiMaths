<?php

namespace App\Domain\CreditNote\Enums;

enum CreditNotePostingType: string
{
    case ISSUE = 'issue';
    case CANCELLATION = 'cancellation';
}
