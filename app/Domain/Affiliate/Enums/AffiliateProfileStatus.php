<?php

namespace App\Domain\Affiliate\Enums;

enum AffiliateProfileStatus: string
{
    case PENDING_EMAIL = 'PENDING_EMAIL';
    case PENDING_IDENTITY = 'PENDING_IDENTITY';
    case PENDING_APPROVAL = 'PENDING_APPROVAL';
    case ACTIVE = 'ACTIVE';
    case SUSPENDED = 'SUSPENDED';
    case DEACTIVATED = 'DEACTIVATED';
}
