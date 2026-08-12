<?php

namespace App\Domain\Affiliate\Services;

use App\Domain\Affiliate\Enums\AffiliateCommissionStatus;
use App\Domain\Affiliate\Enums\AffiliateLedgerEntryType;
use App\Domain\Affiliate\Models\AffiliateCommission;
use App\Domain\Affiliate\Models\AffiliateCommissionCreditNoteAdjustment;
use App\Domain\Affiliate\ValueObjects\CommissionCalculation;
use App\Domain\CreditNote\Contracts\AffiliateCreditNoteAdjustment;
use App\Domain\CreditNote\Enums\AffiliateCreditNoteEventType;
use App\Domain\CreditNote\Models\CreditNote;
use App\Domain\CreditNote\ValueObjects\AffiliateCreditNoteAdjustmentResult;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class AffiliateCreditNoteAdjustmentService implements AffiliateCreditNoteAdjustment
{
    public function __construct(
        private readonly AffiliateWalletService $wallet_service,
    ) {
    }

    public function ApplyIssue(CreditNote $credit_note, User $actor, string $correlation_id): AffiliateCreditNoteAdjustmentResult
    {
        return DB::transaction(function () use ($credit_note, $actor, $correlation_id): AffiliateCreditNoteAdjustmentResult {
            $existing = AffiliateCommissionCreditNoteAdjustment::query()
                ->where('credit_note_id', $credit_note->id)
                ->where('event_type', AffiliateCreditNoteEventType::ISSUE)
                ->first();
            if ($existing !== null) {
                return AffiliateCreditNoteAdjustmentResult::Applied(
                    (int) $existing->tuition_reversal_delta_minor,
                    (int) $existing->commission_reversal_delta_minor,
                );
            }

            $commission = AffiliateCommission::query()
                ->where('receipt_id', $credit_note->receipt_id)
                ->lockForUpdate()
                ->first();
            if ($commission === null) {
                return AffiliateCreditNoteAdjustmentResult::NotApplicable();
            }

            $tuition_minor = $this->CreditNoteTuitionMinor($credit_note);
            if ($tuition_minor < 1) {
                return AffiliateCreditNoteAdjustmentResult::NotApplicable();
            }

            $tuition_reversed_before = (int) $commission->Reversals()->sum('refunded_tuition_minor')
                + (int) $commission->CreditNoteAdjustments()->sum('tuition_reversal_delta_minor');
            $remaining_tuition = max((int) $commission->eligible_tuition_minor - $tuition_reversed_before, 0);
            $tuition_delta = min($tuition_minor, $remaining_tuition);
            if ($tuition_delta < 1) {
                return AffiliateCreditNoteAdjustmentResult::NotApplicable();
            }

            $commission_delta = CommissionCalculation::FromValues(
                $tuition_delta,
                0,
                $tuition_delta,
                (int) $commission->rate_basis_points,
            )->commission_minor;
            $status_before = $commission->status;
            $new_reversed_minor = (int) $commission->reversed_minor + $commission_delta;
            $status_after = $new_reversed_minor >= (int) $commission->commission_minor
                ? AffiliateCommissionStatus::REVERSED
                : $status_before;
            $ledger_entry_id = null;

            if ($status_before === AffiliateCommissionStatus::AVAILABLE && $commission_delta > 0) {
                $ledger = $this->wallet_service->ApplyEntry(
                    $commission->AffiliateProfile()->firstOrFail(),
                    AffiliateLedgerEntryType::CREDIT_NOTE_COMMISSION_REVERSAL,
                    -$commission_delta,
                    0,
                    'affiliate_credit_note',
                    $credit_note->id,
                    'credit-note:affiliate:issue:' . $credit_note->id,
                    $actor,
                    'CREDIT_NOTE_COMMISSION_REVERSAL',
                    $correlation_id,
                );
                $ledger_entry_id = $ledger->id;
            }

            $adjustment = AffiliateCommissionCreditNoteAdjustment::create([
                'affiliate_commission_id' => $commission->id,
                'credit_note_id' => $credit_note->id,
                'event_type' => AffiliateCreditNoteEventType::ISSUE,
                'tuition_reversal_delta_minor' => $tuition_delta,
                'commission_reversal_delta_minor' => $commission_delta,
                'status_before' => $status_before,
                'status_after' => $status_after,
                'idempotency_key' => 'credit-note:affiliate:issue:adjustment:' . $credit_note->id,
                'recorded_by' => $actor->id,
                'ledger_entry_id' => $ledger_entry_id,
                'occurred_at' => now(),
            ]);

            $commission->forceFill([
                'reversed_minor' => $new_reversed_minor,
                'status' => $status_after,
            ])->save();

            return AffiliateCreditNoteAdjustmentResult::Applied($tuition_delta, $commission_delta);
        });
    }

    public function ApplyCancellation(CreditNote $credit_note, User $actor, string $correlation_id): AffiliateCreditNoteAdjustmentResult
    {
        return DB::transaction(function () use ($credit_note, $actor, $correlation_id): AffiliateCreditNoteAdjustmentResult {
            $existing = AffiliateCommissionCreditNoteAdjustment::query()
                ->where('credit_note_id', $credit_note->id)
                ->where('event_type', AffiliateCreditNoteEventType::CANCELLATION)
                ->first();
            if ($existing !== null) {
                return AffiliateCreditNoteAdjustmentResult::Applied(
                    (int) $existing->tuition_reversal_delta_minor,
                    (int) $existing->commission_reversal_delta_minor,
                );
            }

            $issue = AffiliateCommissionCreditNoteAdjustment::query()
                ->where('credit_note_id', $credit_note->id)
                ->where('event_type', AffiliateCreditNoteEventType::ISSUE)
                ->first();
            if ($issue === null) {
                return AffiliateCreditNoteAdjustmentResult::NotApplicable();
            }

            $commission = AffiliateCommission::query()->whereKey($issue->affiliate_commission_id)->lockForUpdate()->firstOrFail();
            $tuition_delta = -((int) $issue->tuition_reversal_delta_minor);
            $commission_delta = -((int) $issue->commission_reversal_delta_minor);
            $status_before = $commission->status;
            $restored_status = $issue->status_before ?? $status_before;
            $status_after = $restored_status;
            $ledger_entry_id = null;

            if ($restored_status === AffiliateCommissionStatus::AVAILABLE && $commission_delta < 0) {
                $ledger = $this->wallet_service->ApplyEntry(
                    $commission->AffiliateProfile()->firstOrFail(),
                    AffiliateLedgerEntryType::CREDIT_NOTE_COMMISSION_RESTORATION,
                    -$commission_delta,
                    0,
                    'affiliate_credit_note',
                    $credit_note->id,
                    'credit-note:affiliate:cancellation:' . $credit_note->id,
                    $actor,
                    'CREDIT_NOTE_COMMISSION_RESTORATION',
                    $correlation_id,
                );
                $ledger_entry_id = $ledger->id;
            }

            $adjustment = AffiliateCommissionCreditNoteAdjustment::create([
                'affiliate_commission_id' => $commission->id,
                'credit_note_id' => $credit_note->id,
                'event_type' => AffiliateCreditNoteEventType::CANCELLATION,
                'tuition_reversal_delta_minor' => $tuition_delta,
                'commission_reversal_delta_minor' => $commission_delta,
                'status_before' => $status_before,
                'status_after' => $status_after,
                'idempotency_key' => 'credit-note:affiliate:cancellation:adjustment:' . $credit_note->id,
                'recorded_by' => $actor->id,
                'ledger_entry_id' => $ledger_entry_id,
                'occurred_at' => now(),
            ]);

            $commission->forceFill([
                'reversed_minor' => max((int) $commission->reversed_minor + $commission_delta, 0),
                'status' => $status_after,
            ])->save();

            return AffiliateCreditNoteAdjustmentResult::Applied($tuition_delta, $commission_delta);
        });
    }

    private function CreditNoteTuitionMinor(CreditNote $credit_note): int
    {
        return (int) $credit_note->Items()
            ->where('line_type', 'tuition')
            ->sum('credit_amount_minor');
    }
}
