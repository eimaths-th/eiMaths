<?php

namespace App\Domain\CreditNote\Services;

use App\Domain\CreditNote\Enums\CreditNotePostingType;
use App\Domain\CreditNote\Enums\CreditNoteStatus;
use App\Domain\CreditNote\Models\CreditNote;
use App\Domain\CreditNote\Models\CreditNoteItem;
use App\Domain\CreditNote\Models\CreditNotePosting;
use App\Domain\Affiliate\Models\AffiliateCommission;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Domain\Affiliate\Services\AffiliateReconciliationService;

final class CreditNoteReconciliationService
{
    public function __construct(private readonly AffiliateReconciliationService $affiliate_reconciliation_service)
    {
    }

    public function Reconcile(int $batch = 100): array
    {
        $batch = min(max($batch, 1), 1000);
        $checked_count = 0;
        $mismatches = [];
        $receipt_ids = [];
        CreditNote::query()->with(['Items', 'Postings'])->orderBy('id')->limit($batch)->get()->each(function (CreditNote $note) use (&$checked_count, &$mismatches, &$receipt_ids): void {
            $checked_count++;
            $receipt_ids[] = $note->receipt_id;
            $codes = [];
            $item_total = (int) $note->Items->sum('credit_amount_minor');
            if ($note->status !== CreditNoteStatus::DRAFT && $item_total !== (int) $note->difference_grand_total_minor) {
                $codes[] = 'HEADER_ITEM_TOTAL_MISMATCH';
            }
            $issue_postings = $note->Postings->where('posting_type', CreditNotePostingType::ISSUE);
            $cancellation_postings = $note->Postings->where('posting_type', CreditNotePostingType::CANCELLATION);
            $issue_count = $issue_postings->count();
            $cancellation_count = $cancellation_postings->count();
            if ($note->status === CreditNoteStatus::ISSUED && $issue_count !== 1) {
                $codes[] = 'ISSUE_POSTING_CARDINALITY';
            }
            if ($note->status === CreditNoteStatus::CANCELLED && $issue_count !== 1) {
                $codes[] = 'ISSUE_POSTING_CARDINALITY';
            }
            if ($note->status === CreditNoteStatus::CANCELLED && $cancellation_count !== 1) {
                $codes[] = 'CANCELLATION_POSTING_CARDINALITY';
            }
            if ($note->status === CreditNoteStatus::DRAFT && ($issue_count > 0 || $cancellation_count > 0)) {
                $codes[] = 'DRAFT_POSTING_PRESENT';
            }
            $issue_posting = $issue_postings->first();
            if ($issue_posting !== null && !$this->PostingMatches($issue_posting, $note, -1)) {
                $codes[] = 'ISSUE_POSTING_VALUES';
            }
            $cancellation_posting = $cancellation_postings->first();
            if ($cancellation_posting !== null && !$this->PostingMatches($cancellation_posting, $note, 1)) {
                $codes[] = 'CANCELLATION_POSTING_VALUES';
            }
            if ($codes !== []) {
                $mismatches[] = ['credit_note_id' => $note->id, 'codes' => array_values(array_unique($codes))];
            }
        });
        $duplicate_numbers = CreditNote::query()->whereNotNull('number')->select('number')->groupBy('number')->havingRaw('COUNT(*) > 1')->pluck('number')->count();
        if ($duplicate_numbers > 0) {
            $mismatches[] = ['credit_note_id' => null, 'codes' => ['DUPLICATE_NUMBER']];
        }
        $remaining_line_mismatches = CreditNoteItem::query()
            ->join('credit_notes', 'credit_notes.id', '=', 'credit_note_items.credit_note_id')
            ->whereIn('credit_notes.status', [CreditNoteStatus::ISSUED->value, CreditNoteStatus::CANCELLED->value])
            ->where('credit_note_items.source_type', 'receipt_detail')
            ->whereNotNull('credit_note_items.source_id')
            ->join('credit_note_postings', 'credit_note_postings.credit_note_id', '=', 'credit_note_items.credit_note_id')
            ->selectRaw("credit_note_items.source_type as source_type, credit_note_items.source_id as source_id, SUM(CASE WHEN credit_note_postings.posting_type = 'issue' THEN credit_note_items.credit_amount_minor ELSE -credit_note_items.credit_amount_minor END) as credited_minor, MAX(credit_note_items.original_line_total_minor) as original_minor")
            ->groupBy('credit_note_items.source_type', 'credit_note_items.source_id')
            ->havingRaw("SUM(CASE WHEN credit_note_postings.posting_type = 'issue' THEN credit_note_items.credit_amount_minor ELSE -credit_note_items.credit_amount_minor END) > MAX(credit_note_items.original_line_total_minor)")
            ->get();
        foreach ($remaining_line_mismatches as $remaining_line_mismatch) {
            $mismatches[] = [
                'credit_note_id' => null,
                'source_id' => (int) $remaining_line_mismatch->source_id,
                'codes' => ['REMAINING_LINE_NEGATIVE'],
                'credited_minor' => (int) $remaining_line_mismatch->credited_minor,
                'original_minor' => (int) $remaining_line_mismatch->original_minor,
            ];
        }
        $affiliate_profile_count = 0;
        $affiliate_issue_count = 0;
        $affiliate_commission_receipt_ids = array_values(array_filter(array_unique($receipt_ids), static fn ($receipt_id): bool => $receipt_id !== null));
        if ($affiliate_commission_receipt_ids !== []) {
            AffiliateCommission::query()
                ->whereIn('receipt_id', $affiliate_commission_receipt_ids)
                ->whereNotNull('affiliate_profile_id')
                ->select('affiliate_profile_id')
                ->distinct()
                ->limit($batch)
                ->pluck('affiliate_profile_id')
                ->each(function ($profile_id) use (&$affiliate_profile_count, &$affiliate_issue_count, &$mismatches): void {
                    $profile = AffiliateProfile::query()->find((int) $profile_id);
                    if ($profile === null) {
                        return;
                    }
                    $affiliate_profile_count++;
                    $profile_result = $this->affiliate_reconciliation_service->ReconcileProfile($profile);
                    $profile_issues = $profile_result['issues'] ?? [];
                    $affiliate_issue_count += count($profile_issues);
                    foreach ($profile_issues as $issue) {
                        $mismatches[] = [
                            'credit_note_id' => null,
                            'affiliate_profile_id' => (int) $profile_id,
                            'codes' => ['AFFILIATE_' . (string) ($issue->issue_code ?? 'RECONCILIATION_MISMATCH')],
                        ];
                    }
                });
        }
        return [
            'checked_count' => $checked_count,
            'mismatch_count' => count($mismatches),
            'duplicate_number_count' => $duplicate_numbers,
            'affiliate_profile_count' => $affiliate_profile_count,
            'affiliate_issue_count' => $affiliate_issue_count,
            'mismatches' => $mismatches,
        ];
    }

    private function PostingMatches(CreditNotePosting $posting, CreditNote $note, int $sign): bool
    {
        $expected = [
            'taxable_base_delta_minor' => $sign * (int) $note->difference_taxable_base_minor,
            'vat_delta_minor' => $sign * (int) $note->difference_vat_minor,
            'exempt_delta_minor' => $sign * (int) $note->difference_exempt_minor,
            'out_of_scope_delta_minor' => $sign * (int) $note->difference_out_of_scope_minor,
            'grand_total_delta_minor' => $sign * (int) $note->difference_grand_total_minor,
        ];
        foreach ($expected as $field => $expected_value) {
            if ((int) $posting->{$field} !== $expected_value) {
                return false;
            }
        }
        return true;
    }
}
