<?php

namespace App\Domain\Affiliate\Enums;

enum AffiliateReconciliationStatus: string
{
    case OPEN = 'OPEN';
    case RESOLVED = 'RESOLVED';
}
