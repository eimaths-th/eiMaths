<?php

namespace App\Domain\CreditNote\Enums;

enum BuyerBranchType: string
{
    case HEAD_OFFICE = 'head_office';
    case BRANCH = 'branch';
    case NOT_APPLICABLE = 'not_applicable';
}
