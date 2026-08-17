<?php

namespace App\Domain\Affiliate\Services;

use App\Domain\Affiliate\Models\AffiliateProfile;
use Illuminate\Support\Str;

final class AffiliateReferralCodeGenerator
{
    public function Generate(): string
    {
        do {
            $code = 'AFF-' . Str::upper(Str::random(10));
        } while (AffiliateProfile::where('referral_code', $code)->exists());

        return $code;
    }
}
