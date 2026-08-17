<?php

namespace App\Domain\CreditNote\Models;

use App\Domain\CreditNote\Enums\CreditNotePostingType;
use Illuminate\Database\Eloquent\Model;

class CreditNotePosting extends Model
{
    protected $fillable = [
        'credit_note_id', 'centre_id', 'posting_type', 'effective_date', 'taxable_base_delta_minor',
        'vat_delta_minor', 'exempt_delta_minor', 'out_of_scope_delta_minor', 'grand_total_delta_minor',
        'created_by', 'correlation_id', 'occurred_at',
    ];

    protected $casts = [
        'posting_type' => CreditNotePostingType::class,
        'effective_date' => 'date:Y-m-d',
        'occurred_at' => 'datetime',
        'taxable_base_delta_minor' => 'integer',
        'vat_delta_minor' => 'integer',
        'exempt_delta_minor' => 'integer',
        'out_of_scope_delta_minor' => 'integer',
        'grand_total_delta_minor' => 'integer',
    ];

    protected static function booted(): void
    {
        static::updating(function (): void {
            throw new \LogicException('Credit Note postings are append-only.');
        });
        static::deleting(function (): void {
            throw new \LogicException('Credit Note postings are append-only.');
        });
    }

    public function CreditNote()
    {
        return $this->belongsTo(CreditNote::class, 'credit_note_id');
    }
}
