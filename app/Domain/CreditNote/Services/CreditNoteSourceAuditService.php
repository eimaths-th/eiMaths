<?php

namespace App\Domain\CreditNote\Services;

use App\Domain\CreditNote\Models\CreditNote;
use App\Models\Receipt;
use App\Models\receipt_details;
use App\Domain\CreditNote\ValueObjects\ThaiTaxIdentifier;

final class CreditNoteSourceAuditService
{
    public function __construct(private readonly CreditNoteSourceReader $source_reader)
    {
    }

    public function Audit(int $batch = 500): array
    {
        $batch = min(max($batch, 1), 1000);
        $counts = [
            'checked_receipt_count' => 0,
            'checked_count' => 0,
            'unknown_line_type_count' => 0,
            'missing_metadata_count' => 0,
            'missing_number_count' => 0,
            'missing_date_count' => 0,
            'missing_details_count' => 0,
            'source_total_mismatch_count' => 0,
            'issuer_configuration_invalid_count' => 0,
            'mismatch_count' => 0,
        ];
        $mismatches = [];
        $receipt_ids = Receipt::query()->orderBy('id')->limit($batch)->pluck('id');
        $receipt_details = receipt_details::query()->whereIn('receipt_id', $receipt_ids)->orderBy('id')->get()->groupBy('receipt_id');
        Receipt::query()->whereIn('id', $receipt_ids)->orderBy('id')->get()->each(function (Receipt $receipt) use (&$counts, &$mismatches, $receipt_details): void {
            $counts['checked_receipt_count'] = ($counts['checked_receipt_count'] ?? 0) + 1;
            $codes = [];
            if (trim((string) $receipt->receipt_number) === '') {
                $counts['missing_number_count']++;
                $codes[] = 'MISSING_RECEIPT_NUMBER';
            }
            if (trim((string) $receipt->payment_date) === '') {
                $counts['missing_date_count']++;
                $codes[] = 'MISSING_RECEIPT_DATE';
            }
            if (trim((string) $receipt->billing_details) === '') {
                $counts['missing_details_count']++;
                $codes[] = 'MISSING_BILLING_DETAILS';
            }
            foreach ($receipt_details->get($receipt->id, collect()) as $detail) {
                $line_type_value = $detail->line_type?->value;
                if ($line_type_value === null || $line_type_value === 'unknown') {
                    $counts['unknown_line_type_count']++;
                    $codes[] = 'UNKNOWN_LINE_TYPE';
                }
                if ($detail->line_type === null || $detail->tax_treatment === null || $detail->vat_rate_basis_points === null) {
                    $counts['missing_metadata_count']++;
                    $codes[] = 'MISSING_LINE_METADATA';
                }
            }
            try {
                $snapshot = $this->source_reader->Read($receipt);
                if (abs($snapshot->header_total_minor - $snapshot->detail_total_minor) > 1) {
                    $counts['source_total_mismatch_count']++;
                    $codes[] = 'SOURCE_TOTAL_MISMATCH';
                }
            } catch (\Throwable) {
                $counts['source_total_mismatch_count']++;
                $codes[] = 'SOURCE_TOTAL_UNREADABLE';
            }
            if ($codes !== []) {
                $counts['mismatch_count']++;
                $mismatches[] = ['receipt_id' => $receipt->id, 'codes' => array_values(array_unique($codes))];
            }
        });
        CreditNote::query()->with(['Items', 'Receipt'])->orderBy('id')->limit($batch)->get()->each(function (CreditNote $note) use (&$counts, &$mismatches): void {
            $counts['checked_count']++;
            $codes = [];
            $receipt = $note->Receipt ?? Receipt::query()->find($note->receipt_id);
            if ($receipt !== null) {
                try {
                    $snapshot = $this->source_reader->Read($receipt);
                    if (abs($snapshot->header_total_minor - $snapshot->detail_total_minor) > 1) {
                        $counts['source_total_mismatch_count']++;
                        $codes[] = 'SOURCE_TOTAL_MISMATCH';
                    }
                } catch (\Throwable) {
                    $counts['source_total_mismatch_count']++;
                    $codes[] = 'SOURCE_TOTAL_UNREADABLE';
                }
            }
            foreach ($note->Items as $item) {
                if ($item->line_type === null || $item->line_type->value === 'unknown') {
                    $counts['unknown_line_type_count']++;
                    $codes[] = 'UNKNOWN_LINE_TYPE';
                }
                if ($item->tax_treatment === null || $item->vat_rate_basis_points === null) {
                    $counts['missing_metadata_count']++;
                    $codes[] = 'MISSING_LINE_METADATA';
                }
            }
            if ($note->number === null && $note->status?->value !== 'draft') {
                $counts['missing_number_count']++;
                $codes[] = 'MISSING_NUMBER';
            }
            if ($note->status?->value !== 'draft' && $note->issue_date === null) {
                $counts['missing_date_count']++;
                $codes[] = 'MISSING_ISSUE_DATE';
            }
            if ($note->status?->value !== 'draft' && trim((string) $note->reason_details) === '') {
                $counts['missing_details_count']++;
                $codes[] = 'MISSING_REASON_DETAILS';
            }
            if ($note->status?->value !== 'draft') {
                $issuer_is_invalid = trim((string) ($note->issuer_name ?? '')) === ''
                    || trim((string) ($note->issuer_address ?? '')) === ''
                    || (string) ($note->issuer_branch_type ?? '') !== 'head_office'
                    || (string) ($note->issuer_branch_code ?? '') !== '00000';
                try {
                    ThaiTaxIdentifier::FromInput((string) ($note->issuer_tax_id ?? ''));
                } catch (\Throwable) {
                    $issuer_is_invalid = true;
                }
                if ($issuer_is_invalid) {
                    $counts['issuer_configuration_invalid_count']++;
                    $codes[] = 'INVALID_ISSUER_CONFIGURATION';
                }
            }
            if ($codes !== []) {
                $counts['mismatch_count']++;
                $mismatches[] = ['credit_note_id' => $note->id, 'codes' => array_values(array_unique($codes))];
            }
        });
        return $counts + ['mismatches' => $mismatches];
    }
}
