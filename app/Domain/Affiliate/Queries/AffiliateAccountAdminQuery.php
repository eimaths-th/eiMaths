<?php

namespace App\Domain\Affiliate\Queries;

use App\Domain\Affiliate\Enums\AffiliateProfileStatus;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AffiliateAccountAdminQuery
{
    public function Paginate(int $per_page = 50): LengthAwarePaginator
    {
        return AffiliateProfile::query()->with(['User', 'IdentityProfile'])->latest('id')->paginate(min(max($per_page, 1), 100));
    }

    public function PendingApprovalSummary(User $actor): array
    {
        $this->AssertCanReadPendingApprovalSummary($actor);

        $pending_query = AffiliateProfile::query()
            ->where('status', AffiliateProfileStatus::PENDING_APPROVAL->value);
        $pending_count = (clone $pending_query)->count();
        $latest_pending_profile = (clone $pending_query)
            ->with('User')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();
        $latest_event = AffiliateProfile::query()
            ->select(['id', 'status', 'updated_at'])
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();

        return [
            'pending_count' => $pending_count,
            'latest_event_cursor' => $latest_event === null || $latest_event->updated_at === null
                ? null
                : $latest_event->updated_at->toIso8601String().'|'.$latest_event->id,
            'latest_event_status' => $latest_event?->status?->value,
            'latest_pending_affiliate' => $this->MapPendingApprovalProfile($latest_pending_profile),
        ];
    }

    private function AssertCanReadPendingApprovalSummary(User $actor): void
    {
        if ((int) $actor->disable !== 0 || ! $actor->can('affiliate-list')) {
            throw new AuthorizationException;
        }
    }

    private function MapPendingApprovalProfile(?AffiliateProfile $profile): ?array
    {
        if ($profile === null || $profile->User === null || $profile->updated_at === null) {
            return null;
        }

        return [
            'id' => (int) $profile->id,
            'name' => trim((string) $profile->User->name) ?: 'Affiliate',
            'pending_at' => $profile->updated_at->toIso8601String(),
        ];
    }
}
