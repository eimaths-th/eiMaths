<?php

namespace App\Domain\Affiliate\Services;

use App\Domain\Affiliate\Enums\AffiliateLedgerEntryType;
use App\Domain\Affiliate\Exceptions\AffiliateDomainException;
use App\Domain\Affiliate\Models\AffiliateLedgerEntry;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Domain\Affiliate\Models\AffiliateWalletBalance;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AffiliateWalletService
{
    public function __construct(private readonly AffiliateAuditService $audit_service)
    {
    }

    public function ApplyEntry(
        AffiliateProfile $affiliate_profile,
        AffiliateLedgerEntryType $entry_type,
        int $available_delta_minor,
        int $reserved_delta_minor,
        string $source_type,
        int $source_id,
        string $idempotency_key,
        ?User $actor,
        string $reason_code,
        string $correlation_id,
    ): AffiliateLedgerEntry {
        return DB::transaction(function () use (
            $affiliate_profile,
            $entry_type,
            $available_delta_minor,
            $reserved_delta_minor,
            $source_type,
            $source_id,
            $idempotency_key,
            $actor,
            $reason_code,
            $correlation_id,
        ): AffiliateLedgerEntry {
            $existing = AffiliateLedgerEntry::query()->where('idempotency_key', $idempotency_key)->first();
            if ($existing !== null) {
                if (!$this->MatchesPayload($existing, $affiliate_profile, $entry_type, $available_delta_minor, $reserved_delta_minor, $source_type, $source_id, $reason_code)) {
                    throw AffiliateDomainException::IdempotencyConflict();
                }

                return $existing;
            }

            $wallet = AffiliateWalletBalance::query()
                ->where('affiliate_profile_id', $affiliate_profile->id)
                ->lockForUpdate()
                ->first();
            if ($wallet === null) {
                AffiliateWalletBalance::create([
                    'affiliate_profile_id' => $affiliate_profile->id,
                    'available_balance_minor' => 0,
                    'reserved_balance_minor' => 0,
                ]);
                $wallet = AffiliateWalletBalance::query()
                    ->where('affiliate_profile_id', $affiliate_profile->id)
                    ->lockForUpdate()
                    ->firstOrFail();
            }

            $available_after = $wallet->available_balance_minor + $available_delta_minor;
            $reserved_after = $wallet->reserved_balance_minor + $reserved_delta_minor;
            if ($reserved_after < 0) {
                throw AffiliateDomainException::InsufficientBalance();
            }
            if ($available_after < 0 && !in_array($entry_type, [AffiliateLedgerEntryType::COMMISSION_REVERSAL, AffiliateLedgerEntryType::ADJUSTMENT], true)) {
                throw AffiliateDomainException::StateConflict('Available balance cannot become negative for this ledger entry.');
            }

            $entry = AffiliateLedgerEntry::create([
                'affiliate_profile_id' => $affiliate_profile->id,
                'entry_type' => $entry_type,
                'available_delta_minor' => $available_delta_minor,
                'reserved_delta_minor' => $reserved_delta_minor,
                'balance_available_after_minor' => $available_after,
                'balance_reserved_after_minor' => $reserved_after,
                'source_type' => $source_type,
                'source_id' => $source_id,
                'idempotency_key' => $idempotency_key,
                'actor_id' => $actor?->id,
                'reason_code' => $reason_code,
                'occurred_at' => now(),
            ]);

            $wallet->forceFill([
                'available_balance_minor' => $available_after,
                'reserved_balance_minor' => $reserved_after,
                'last_ledger_entry_id' => $entry->id,
            ])->save();

            $this->audit_service->Record(
                operation: 'wallet.ledger.append',
                subject_type: 'affiliate_profile',
                subject_id: $affiliate_profile->id,
                actor: $actor,
                prior_state: [
                    'available_balance_minor' => $available_after - $available_delta_minor,
                    'reserved_balance_minor' => $reserved_after - $reserved_delta_minor,
                ],
                resulting_state: [
                    'available_balance_minor' => $available_after,
                    'reserved_balance_minor' => $reserved_after,
                    'entry_type' => $entry_type->value,
                ],
                amount_minor: $available_delta_minor,
                outcome: 'SUCCESS',
                error_code: null,
                correlation_id: $correlation_id,
            );

            return $entry;
        });
    }

    private function MatchesPayload(
        AffiliateLedgerEntry $entry,
        AffiliateProfile $affiliate_profile,
        AffiliateLedgerEntryType $entry_type,
        int $available_delta_minor,
        int $reserved_delta_minor,
        string $source_type,
        int $source_id,
        string $reason_code,
    ): bool {
        return (int) $entry->affiliate_profile_id === (int) $affiliate_profile->id
            && $entry->entry_type === $entry_type
            && (int) $entry->available_delta_minor === $available_delta_minor
            && (int) $entry->reserved_delta_minor === $reserved_delta_minor
            && $entry->source_type === $source_type
            && (int) $entry->source_id === $source_id
            && $entry->reason_code === $reason_code;
    }
}
