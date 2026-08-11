<?php

namespace App\Domain\Affiliate\Queries;

use App\Domain\Affiliate\Models\AffiliateCommission;
use App\Domain\Affiliate\Models\AffiliateWalletBalance;
use App\Domain\Affiliate\Models\AffiliateWithdrawalRequest;
use App\Domain\Affiliate\ValueObjects\AffiliateAnalyticsRange;
use App\Http\Resources\Affiliate\AffiliateAnalyticsResource;
use App\Models\User;
use Illuminate\Http\Request;

class AffiliateDashboardQuery
{
    public function __construct(
        private readonly AffiliateActiveProfileQuery $active_profile_query,
        private readonly AffiliateAnalyticsQuery $analytics_query,
    )
    {
    }

    public function ForUser(User $user): array
    {
        $profile = $this->active_profile_query->ForUser($user);

        $wallet = AffiliateWalletBalance::query()->whereKey($profile->id)->first()
            ?? new AffiliateWalletBalance(['affiliate_profile_id' => $profile->id, 'available_balance_minor' => 0, 'reserved_balance_minor' => 0]);

        $analytics_summary = null;
        if ((bool) config('affiliate.features.analytics', false)) {
            $analytics_result = $this->analytics_query->ForProfile($profile, AffiliateAnalyticsRange::Default());
            $analytics_summary = (new AffiliateAnalyticsResource($analytics_result))->toArray(app(Request::class));
        }

        return [
            'profile' => $profile,
            'referral_url' => route('affiliate.tracking.direct', ['referral_code' => $profile->referral_code]),
            'analytics_summary' => $analytics_summary,
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
