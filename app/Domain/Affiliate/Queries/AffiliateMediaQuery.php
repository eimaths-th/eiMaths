<?php

namespace App\Domain\Affiliate\Queries;

use App\Models\AffiliateContent;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AffiliateMediaQuery
{
    public function __construct(private readonly AffiliateActiveProfileQuery $active_profile_query)
    {
    }

    public function ForUser(User $user, ?string $search = null, ?string $type = null): LengthAwarePaginator
    {
        $profile = $this->active_profile_query->ForUser($user);
        $now = CarbonImmutable::now((string) config('app.timezone', 'Asia/Bangkok'));
        $query = AffiliateContent::query()
            ->where('status', 'enabled')
            ->whereNull('archived_at')
            ->where(function ($builder) use ($now): void {
                $builder->whereNull('publish_starts_at')->orWhere('publish_starts_at', '<=', $now->format('Y-m-d H:i:s'));
            })
            ->where(function ($builder) use ($now): void {
                $builder->whereNull('publish_ends_at')->orWhere('publish_ends_at', '>', $now->format('Y-m-d H:i:s'));
            });
        if ($search !== null && $search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('title', 'like', '%' . addcslashes($search, '%_') . '%')
                    ->orWhere('description', 'like', '%' . addcslashes($search, '%_') . '%');
            });
        }
        if ($type !== null) $query->where('type', $type);

        $paginator = $query->orderByDesc('created_at')->orderByDesc('id')->paginate(24);
        $paginator->getCollection()->each(function (AffiliateContent $content) use ($profile): void {
            $content->setAttribute('tracking_url', route('affiliate.tracking.media', [
                'referral_code' => $profile->referral_code,
                'campaign_code' => $content->campaign_code,
            ]));
        });

        return $paginator;
    }
}
