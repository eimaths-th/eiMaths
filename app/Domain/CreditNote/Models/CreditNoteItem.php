<?php

namespace App\Domain\CreditNote\Models;

use App\Domain\CreditNote\Enums\CreditNoteSourceType;
use App\Domain\CreditNote\Enums\ReceiptLineType;
use App\Domain\CreditNote\Enums\TaxTreatment;
use App\Domain\CreditNote\Enums\CreditNoteStatus;
use Illuminate\Database\Eloquent\Model;

class CreditNoteItem extends Model
{
    protected $fillable = [
        'credit_note_id', 'sort_order', 'source_type', 'source_id', 'line_type', 'description', 'unit',
        'tax_treatment', 'vat_rate_basis_points', 'original_quantity', 'original_unit_price_minor',
        'original_discount_minor', 'original_line_total_minor', 'credit_quantity', 'credit_amount_minor',
        'credit_taxable_base_minor', 'credit_vat_minor', 'credit_exempt_minor', 'credit_out_of_scope_minor',
    ];

    protected $casts = [
        'source_type' => CreditNoteSourceType::class,
        'line_type' => ReceiptLineType::class,
        'tax_treatment' => TaxTreatment::class,
        'vat_rate_basis_points' => 'integer',
        'original_quantity' => 'decimal:2',
        'credit_quantity' => 'decimal:2',
        'original_unit_price_minor' => 'integer',
        'original_discount_minor' => 'integer',
        'original_line_total_minor' => 'integer',
        'credit_amount_minor' => 'integer',
        'credit_taxable_base_minor' => 'integer',
        'credit_vat_minor' => 'integer',
        'credit_exempt_minor' => 'integer',
        'credit_out_of_scope_minor' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (CreditNoteItem $item): void {
            $item->AssertDraftParent();
        });
        static::updating(function (CreditNoteItem $item): void {
            $item->AssertDraftParent();
        });
        static::deleting(function (CreditNoteItem $item): void {
            $item->AssertDraftParent();
        });
    }

    public function CreditNote()
    {
        return $this->belongsTo(CreditNote::class, 'credit_note_id');
    }

    private function AssertDraftParent(): void
    {
        $status = CreditNote::query()->whereKey($this->credit_note_id)->value('status');
        $status = $status instanceof CreditNoteStatus ? $status : CreditNoteStatus::tryFrom((string) $status);
        if ($status !== CreditNoteStatus::DRAFT) {
            throw new \LogicException('Credit Note items are immutable after Issue.');
        }
    }
}
