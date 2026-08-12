<?php

namespace App\Domain\CreditNote\Enums;

enum AffiliateCreditNoteEventType: string
{
    case ISSUE = 'issue';
    case CANCELLATION = 'cancellation';
}
