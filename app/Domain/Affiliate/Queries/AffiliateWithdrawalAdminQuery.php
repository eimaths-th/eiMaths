<?php

namespace App\Domain\Affiliate\Queries;

use App\Domain\Affiliate\Enums\AffiliateWithdrawalStatus;
use App\Domain\Affiliate\Models\AffiliateWithdrawalRequest;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class AffiliateWithdrawalAdminQuery
{
    private const ACTIONABLE_STATUSES = [
        AffiliateWithdrawalStatus::REQUESTED->value,
        AffiliateWithdrawalStatus::APPROVED->value,
    ];

    public function PendingSummary(User $actor): array
    {
        $this->AssertCanReadPendingSummary($actor);

        $actionable_query = AffiliateWithdrawalRequest::query()
            ->whereIn('status', self::ACTIONABLE_STATUSES);
        $requested_count = (clone $actionable_query)
            ->where('status', AffiliateWithdrawalStatus::REQUESTED->value)
            ->count();
        $approved_count = (clone $actionable_query)
            ->where('status', AffiliateWithdrawalStatus::APPROVED->value)
            ->count();
        $latest_actionable_withdrawal = (clone $actionable_query)
            ->with('AffiliateProfile.User')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();
        $latest_event = AffiliateWithdrawalRequest::query()
            ->select(['id', 'status', 'updated_at'])
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();

        return [
            'actionable_count' => $requested_count + $approved_count,
            'requested_count' => $requested_count,
            'approved_count' => $approved_count,
            'latest_event_cursor' => $latest_event === null || $latest_event->updated_at === null
                ? null
                : $latest_event->updated_at->toIso8601String().'|'.$latest_event->id,
            'latest_event_status' => $latest_event?->status?->value,
            'latest_actionable_withdrawal' => $this->MapActionableWithdrawal($latest_actionable_withdrawal),
        ];
    }

    private function AssertCanReadPendingSummary(User $actor): void
    {
        if ((int) $actor->disable !== 0 || ! $actor->can('affiliate-withdrawal-list')) {
            throw new AuthorizationException;
        }
    }

    private function MapActionableWithdrawal(?AffiliateWithdrawalRequest $withdrawal): ?array
    {
        if ($withdrawal === null || $withdrawal->requested_at === null) {
            return null;
        }

        return [
            'id' => (int) $withdrawal->id,
            'affiliate_name' => trim((string) $withdrawal->AffiliateProfile?->User?->name) ?: 'Affiliate',
            'amount_minor' => (int) $withdrawal->amount_minor,
            'status' => $withdrawal->status->value,
            'requested_at' => $withdrawal->requested_at->toIso8601String(),
        ];
    }
}
