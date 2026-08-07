<?php

namespace App\Domain\Affiliate\Enums;

enum AffiliateCommissionStatus: string
{
    case PENDING = 'PENDING';
    case HELD = 'HELD';
    case AVAILABLE = 'AVAILABLE';
    case REVERSED = 'REVERSED';
}
