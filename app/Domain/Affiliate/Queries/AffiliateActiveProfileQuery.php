<?php

namespace App\Domain\Affiliate\Queries;

use App\Domain\Affiliate\Enums\AffiliateProfileStatus;
use App\Domain\Affiliate\Exceptions\AffiliateDomainException;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Models\User;

class AffiliateActiveProfileQuery
{
    public function ForUser(User $user): AffiliateProfile
    {
        $profile = $user->AffiliateProfile()->first();
        if (!$profile instanceof AffiliateProfile || $profile->status !== AffiliateProfileStatus::ACTIVE) {
            throw AffiliateDomainException::OwnershipDenied();
        }

        return $profile;
    }
}
