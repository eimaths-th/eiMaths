<?php

namespace App\Domain\CreditNote\Models;

use App\Domain\CreditNote\Enums\CreditNoteEventOperation;
use App\Domain\CreditNote\Enums\CreditNoteReasonCode;
use App\Domain\CreditNote\Enums\CreditNoteStatus;
use Illuminate\Database\Eloquent\Model;

class CreditNoteEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'credit_note_id', 'operation', 'status_before', 'status_after', 'amount_minor', 'actor_id',
        'actor_name_snapshot', 'reason_code', 'correlation_id', 'metadata', 'occurred_at', 'created_at',
    ];

    protected $casts = [
        'operation' => CreditNoteEventOperation::class,
        'status_before' => CreditNoteStatus::class,
        'status_after' => CreditNoteStatus::class,
        'reason_code' => CreditNoteReasonCode::class,
        'metadata' => 'array',
        'occurred_at' => 'datetime',
        'created_at' => 'datetime',
        'amount_minor' => 'integer',
    ];

    protected static function booted(): void
    {
        static::updating(function (): void {
            throw new \LogicException('Credit Note events are append-only.');
        });
        static::deleting(function (): void {
            throw new \LogicException('Credit Note events are append-only.');
        });
    }

    public function CreditNote()
    {
        return $this->belongsTo(CreditNote::class, 'credit_note_id');
    }
}
