<?php

namespace App\Domain\Affiliate\Services;

use App\Domain\Affiliate\Enums\AffiliateProfileStatus;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Models\User;

final class AffiliatePortalAccess
{
    public function __construct(
        private readonly AffiliateEmployeeEligibility $eligibility,
    ) {
    }

    public function CanAccess(User $user): bool
    {
        $profile = $user->AffiliateProfile()->first();
        if (!$profile instanceof AffiliateProfile || $profile->status !== AffiliateProfileStatus::ACTIVE) {
            return false;
        }

        if ($this->eligibility->IsEligible($user)) {
            return true;
        }

        return $this->eligibility->IsAffiliateOnly($user) && $user->hasVerifiedEmail();
    }
}
