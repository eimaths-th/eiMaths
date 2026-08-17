<?php

namespace App\Domain\CreditNote\Services;

use App\Domain\CreditNote\Enums\CreditNoteStatus;
use App\Domain\CreditNote\Enums\CreditNotePostingType;
use App\Domain\CreditNote\Enums\ReceiptLineType;
use App\Domain\CreditNote\Exceptions\CreditNoteDomainException;
use App\Domain\CreditNote\Models\CreditNote;
use App\Domain\CreditNote\ValueObjects\CreditNoteEligibility;
use App\Domain\CreditNote\ValueObjects\CreditNoteSourceSnapshot;
use App\Domain\CreditNote\ValueObjects\CreditNoteMoney;
use App\Models\Receipt;
use Illuminate\Support\Facades\DB;

final class CreditNoteEligibilityService
{
    public function Evaluate(Receipt $receipt, CreditNoteSourceSnapshot $snapshot): CreditNoteEligibility
    {
        $prior_net_credit = $this->PriorNetCreditBySource($receipt->id);
        $remaining_lines = [];
        $remaining_grand_total_minor = 0;
        foreach ($snapshot->lines as $line) {
            $source_key = $line->source_type->value . ':' . $line->source_id;
            $remaining_amount_minor = $line->original_line_total_minor + ($prior_net_credit[$source_key] ?? 0);
            $remaining_grand_total_minor += max($remaining_amount_minor, 0);
            $remaining_lines[] = [
                'source_type' => $line->source_type->value,
                'source_id' => $line->source_id,
                'line_type' => $line->line_type?->value,
                'remaining_amount_minor' => max($remaining_amount_minor, 0),
                'remaining_quantity' => $line->original_quantity->Decimal(),
            ];

            if (!$line->has_complete_source_values || $line->line_type === null || $line->tax_treatment === null || $line->vat_rate_basis_points === null) {
                return new CreditNoteEligibility(false, 'CREDIT_NOTE_SOURCE_INCOMPLETE', $remaining_lines, $snapshot->grand_total_minor);
            }
        }

        $difference_minor = abs($snapshot->detail_total_minor - $snapshot->header_total_minor);
        if ($difference_minor > 1) {
            return new CreditNoteEligibility(false, 'CREDIT_NOTE_SOURCE_TOTAL_MISMATCH', $remaining_lines, $snapshot->grand_total_minor);
        }

        if (!$this->IsPaid($snapshot)) {
            return new CreditNoteEligibility(false, 'CREDIT_NOTE_SOURCE_INCOMPLETE', $remaining_lines, $snapshot->grand_total_minor);
        }

        if (CreditNote::query()->where('receipt_id', $receipt->id)->where('status', CreditNoteStatus::DRAFT->value)->exists()) {
            return new CreditNoteEligibility(false, 'CREDIT_NOTE_DRAFT_EXISTS', $remaining_lines, $snapshot->grand_total_minor);
        }

        if ($remaining_grand_total_minor <= 0) {
            return new CreditNoteEligibility(false, 'CREDIT_NOTE_FULLY_CREDITED', $remaining_lines, 0);
        }

        $unknown_is_financial = collect($snapshot->lines)->contains(
            static fn ($line): bool => $line->original_line_total_minor > 0 && $line->line_type === ReceiptLineType::UNKNOWN
        );
        if ($unknown_is_financial && $receipt->AffiliateCommissionRecords()->where('commission_minor', '>', 0)->exists()) {
            return new CreditNoteEligibility(false, 'CREDIT_NOTE_UNKNOWN_LINE_TYPE', $remaining_lines, $snapshot->grand_total_minor);
        }

        return new CreditNoteEligibility(true, null, $remaining_lines, $remaining_grand_total_minor);
    }

    private function PriorNetCreditBySource(int $receipt_id): array
    {
        $rows = DB::table('credit_note_items as items')
            ->join('credit_notes as notes', 'notes.id', '=', 'items.credit_note_id')
            ->join('credit_note_postings as postings', 'postings.credit_note_id', '=', 'notes.id')
            ->where('notes.receipt_id', $receipt_id)
            ->select('items.source_type', 'items.source_id', 'postings.posting_type', DB::raw('SUM(items.credit_amount_minor) as amount_minor'))
            ->groupBy('items.source_type', 'items.source_id', 'postings.posting_type')
            ->get();
        $totals = [];
        foreach ($rows as $row) {
            $key = $row->source_type . ':' . $row->source_id;
            $sign = $row->posting_type === CreditNotePostingType::ISSUE->value ? 1 : -1;
            $totals[$key] = ($totals[$key] ?? 0) + $sign * (int) $row->amount_minor;
        }

        return $totals;
    }

    public function AssertCanCreateDraft(Receipt $receipt, CreditNoteSourceSnapshot $snapshot): CreditNoteEligibility
    {
        $eligibility = $this->Evaluate($receipt, $snapshot);
        if ($eligibility->IsEligible()) {
            return $eligibility;
        }

        $this->ThrowForBlockingCode($eligibility->blocking_code);
    }

    public function AssertCanIssue(Receipt $receipt, CreditNoteSourceSnapshot $snapshot): CreditNoteEligibility
    {
        $eligibility = $this->Evaluate($receipt, $snapshot);
        if ($eligibility->IsEligible() || $eligibility->blocking_code === 'CREDIT_NOTE_DRAFT_EXISTS') {
            return $eligibility;
        }

        $this->ThrowForBlockingCode($eligibility->blocking_code);
    }

    private function ThrowForBlockingCode(?string $blocking_code): never
    {
        throw match ($blocking_code) {
            'CREDIT_NOTE_SOURCE_TOTAL_MISMATCH' => CreditNoteDomainException::SourceTotalMismatch(),
            'CREDIT_NOTE_DRAFT_EXISTS' => CreditNoteDomainException::DraftExists(),
            'CREDIT_NOTE_FULLY_CREDITED' => CreditNoteDomainException::FullyCredited(),
            'CREDIT_NOTE_UNKNOWN_LINE_TYPE' => CreditNoteDomainException::UnknownLineType(),
            default => CreditNoteDomainException::SourceIncomplete(),
        };
    }

    private function IsPaid(CreditNoteSourceSnapshot $snapshot): bool
    {
        return (
            $snapshot->receipt_status === 1
            || $snapshot->order_status === 1
        ) && trim($snapshot->payment_date) !== '';
    }
}
