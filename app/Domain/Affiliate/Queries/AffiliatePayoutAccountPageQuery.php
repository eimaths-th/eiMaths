<?php

namespace App\Domain\Affiliate\Queries;

use App\Domain\Affiliate\Models\AffiliatePayoutAccount;
use App\Models\User;

class AffiliatePayoutAccountPageQuery
{
    public function __construct(private readonly AffiliateActiveProfileQuery $active_profile_query)
    {
    }

    public function ForUser(User $user): array
    {
        $profile = $this->active_profile_query->ForUser($user);

        return [
            'profile' => $profile,
            'payout_accounts' => AffiliatePayoutAccount::query()
                ->where('affiliate_profile_id', $profile->id)
                ->where('is_active', true)
                ->orderBy('payout_type')
                ->get(),
        ];
    }
}
