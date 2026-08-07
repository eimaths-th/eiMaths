<?php

namespace App\Domain\Affiliate\Enums;

enum AffiliateWithdrawalStatus: string
{
    case REQUESTED = 'REQUESTED';
    case APPROVED = 'APPROVED';
    case PAID = 'PAID';
    case REJECTED = 'REJECTED';
    case CANCELLED = 'CANCELLED';
}
