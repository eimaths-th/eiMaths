<?php

namespace App\Domain\Affiliate\Queries;

use App\Domain\Affiliate\Models\AffiliatePayoutAccount;
use App\Domain\Affiliate\Models\AffiliateWalletBalance;
use App\Domain\Affiliate\Models\AffiliateWithdrawalRequest;
use App\Domain\Affiliate\Services\AffiliateConfigurationService;
use App\Models\User;

class AffiliateWithdrawalPageQuery
{
    public function __construct(
        private readonly AffiliateActiveProfileQuery $active_profile_query,
        private readonly AffiliateConfigurationService $configuration_service,
    ) {
    }

    public function ForUser(User $user): array
    {
        $profile = $this->active_profile_query->ForUser($user);
        $wallet = AffiliateWalletBalance::query()->whereKey($profile->id)->first()
            ?? new AffiliateWalletBalance([
                'affiliate_profile_id' => $profile->id,
                'available_balance_minor' => 0,
                'reserved_balance_minor' => 0,
            ]);

        return [
            'profile' => $profile,
            'wallet' => $wallet,
            'payout_accounts' => AffiliatePayoutAccount::query()
                ->where('affiliate_profile_id', $profile->id)
                ->where('is_active', true)
                ->orderBy('payout_type')
                ->get(),
            'minimum_withdrawal_minor' => $this->configuration_service->MinimumWithdrawalMinor(),
            'withdrawals' => AffiliateWithdrawalRequest::query()
                ->where('affiliate_profile_id', $profile->id)
                ->latest('id')
                ->paginate(25, ['*'], 'withdrawals_page'),
        ];
    }
}
