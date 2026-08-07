<?php

namespace App\Domain\Affiliate\Queries;

use App\Domain\Affiliate\Models\AffiliateCommission;
use App\Domain\Affiliate\Models\AffiliateWalletBalance;
use App\Domain\Affiliate\Models\AffiliateWithdrawalRequest;
use App\Models\User;

class AffiliateDashboardQuery
{
    public function __construct(private readonly AffiliateActiveProfileQuery $active_profile_query)
    {
    }

    public function ForUser(User $user): array
    {
        $profile = $this->active_profile_query->ForUser($user);

        $wallet = AffiliateWalletBalance::query()->whereKey($profile->id)->first()
            ?? new AffiliateWalletBalance(['affiliate_profile_id' => $profile->id, 'available_balance_minor' => 0, 'reserved_balance_minor' => 0]);

        return [
            'profile' => $profile,
            'wallet' => $wallet,
            'commissions' => AffiliateCommission::query()
                ->where('affiliate_profile_id', $profile->id)
                ->with(['Order', 'Receipt'])
                ->latest('id')
                ->paginate(25, ['*'], 'commissions_page'),
            'withdrawals' => AffiliateWithdrawalRequest::query()
                ->where('affiliate_profile_id', $profile->id)
                ->latest('id')
                ->paginate(25, ['*'], 'withdrawals_page'),
        ];
    }
}
