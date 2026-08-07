<?php

namespace App\Domain\Affiliate\Enums;

enum AffiliatePayoutType: string
{
    case PROMPTPAY = 'PROMPTPAY';
    case BANK = 'BANK';
}
