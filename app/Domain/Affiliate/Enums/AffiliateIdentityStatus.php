<?php

namespace App\Domain\Affiliate\Enums;

enum AffiliateIdentityStatus: string
{
    case DRAFT = 'DRAFT';
    case PENDING = 'PENDING';
    case REJECTED = 'REJECTED';
    case VERIFIED = 'VERIFIED';
}
