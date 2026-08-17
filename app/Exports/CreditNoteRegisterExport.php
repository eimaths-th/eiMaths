<?php

namespace App\Exports;

use App\Domain\CreditNote\Models\CreditNote;
use App\Domain\CreditNote\Queries\CreditNoteQuery;
use App\Domain\CreditNote\ValueObjects\CreditNoteMoney;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

final class CreditNoteRegisterExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(private readonly User $actor, private readonly array $filters, private readonly CreditNoteQuery $credit_note_query)
    {
    }

    public function query(): Builder
    {
        return $this->credit_note_query->ForList($this->actor, $this->filters);
    }

    public function headings(): array
    {
        return [
            'Credit Note number', 'Status', 'Issue date', 'Cancellation date', 'Adjustment event date', 'Centre',
            'Original receipt number', 'Original receipt date', 'Buyer name', 'Buyer tax ID', 'Reason',
            'Original value', 'Corrected value', 'Difference', 'Taxable-base difference', 'VAT difference',
            'Exempt difference', 'Out-of-scope difference', 'Affiliate adjustment', 'Created actor ID',
            'Issued actor ID', 'Cancelled actor ID',
        ];
    }

    public function map($credit_note): array
    {
        $credit_note = $credit_note instanceof CreditNote ? $credit_note : CreditNote::query()->findOrFail($credit_note->id);
        return [
            $credit_note->number,
            $credit_note->status?->value,
            optional($credit_note->issue_date)->format('Y-m-d'),
            optional($credit_note->cancellation_effective_date)->format('Y-m-d'),
            optional($credit_note->adjustment_event_date)->format('Y-m-d'),
            $credit_note->centre_id,
            $credit_note->original_receipt_number,
            optional($credit_note->original_receipt_date)->format('Y-m-d'),
            $credit_note->buyer_name,
            $credit_note->buyer_tax_id,
            $credit_note->reason_code?->value,
            $this->Money($credit_note->original_grand_total_minor),
            $this->Money($credit_note->corrected_grand_total_minor),
            $this->Money($credit_note->difference_grand_total_minor),
            $this->Money($credit_note->difference_taxable_base_minor),
            $this->Money($credit_note->difference_vat_minor),
            $this->Money($credit_note->difference_exempt_minor),
            $this->Money($credit_note->difference_out_of_scope_minor),
            $this->Money($credit_note->AffiliateAdjustments->sum('commission_reversal_delta_minor')),
            $credit_note->created_by,
            $credit_note->issued_by,
            $credit_note->cancelled_by,
        ];
    }

    private function Money(?int $minor): string
    {
        return CreditNoteMoney::FromMinor((int) $minor)->Decimal();
    }
}
