<?php

namespace App\Domain\Affiliate\Services;

use App\Domain\Affiliate\Enums\AffiliateReconciliationStatus;
use App\Domain\Affiliate\Models\AffiliateLedgerEntry;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Domain\Affiliate\Models\AffiliateReconciliationIssue;
use App\Domain\Affiliate\Models\AffiliateWalletBalance;
use App\Models\User;

class AffiliateReconciliationService
{
    public function __construct(private readonly AffiliateAuditService $audit_service)
    {
    }

    public function OpenIssue(
        string $source_table,
        int $source_id,
        string $issue_code,
        string $severity,
        array $evidence = [],
    ): AffiliateReconciliationIssue {
        return $this->audit_service->OpenIssue($source_table, $source_id, $issue_code, $severity, $evidence);
    }

    public function ReconcileProfile(AffiliateProfile $profile): array
    {
        $wallet = AffiliateWalletBalance::query()->whereKey($profile->id)->first();
        $ledger_available = (int) AffiliateLedgerEntry::query()->where('affiliate_profile_id', $profile->id)->sum('available_delta_minor');
        $ledger_reserved = (int) AffiliateLedgerEntry::query()->where('affiliate_profile_id', $profile->id)->sum('reserved_delta_minor');
        $issues = [];

        if ($wallet === null) {
            $issues[] = $this->OpenIssue('affiliate_wallet_balances', $profile->id, 'WALLET_PROJECTION_MISSING', 'HIGH', ['expected_available_minor' => $ledger_available, 'expected_reserved_minor' => $ledger_reserved]);
        } else {
            if ((int) $wallet->available_balance_minor !== $ledger_available) {
                $issues[] = $this->OpenIssue('affiliate_wallet_balances', $profile->id, 'AVAILABLE_BALANCE_MISMATCH', 'HIGH', ['projection' => $wallet->available_balance_minor, 'ledger' => $ledger_available]);
            }
            if ((int) $wallet->reserved_balance_minor !== $ledger_reserved) {
                $issues[] = $this->OpenIssue('affiliate_wallet_balances', $profile->id, 'RESERVED_BALANCE_MISMATCH', 'HIGH', ['projection' => $wallet->reserved_balance_minor, 'ledger' => $ledger_reserved]);
            }
        }

        return ['affiliate_profile_id' => $profile->id, 'ledger_available_minor' => $ledger_available, 'ledger_reserved_minor' => $ledger_reserved, 'issues' => $issues];
    }

    public function ReconcileAll(int $batch = 100): int
    {
        $count = 0;
        AffiliateProfile::query()->orderBy('id')->limit(min(max($batch, 1), 1000))->each(function (AffiliateProfile $profile) use (&$count): void {
            $this->ReconcileProfile($profile);
            $count++;
        });
        return $count;
    }

    public function ResolveIssue(AffiliateReconciliationIssue $issue, User $actor, string $note, ?int $adjustment_ledger_entry_id = null): AffiliateReconciliationIssue
    {
        if (trim($note) === '') {
            throw \App\Domain\Affiliate\Exceptions\AffiliateDomainException::StateConflict('A reconciliation resolution note is required.');
        }
        if ($issue->status !== AffiliateReconciliationStatus::OPEN) {
            throw \App\Domain\Affiliate\Exceptions\AffiliateDomainException::StateConflict('The reconciliation issue is already resolved.');
        }
        $issue->forceFill(['status' => AffiliateReconciliationStatus::RESOLVED, 'resolved_by' => $actor->id, 'resolved_at' => now(), 'resolution_note' => trim($note), 'adjustment_ledger_entry_id' => $adjustment_ledger_entry_id])->save();
        return $issue->fresh();
    }
}
