<?php

namespace App\Domain\Affiliate\Models;

use App\Domain\Affiliate\Enums\AffiliateCommissionStatus;
use App\Domain\CreditNote\Enums\AffiliateCreditNoteEventType;
use App\Domain\CreditNote\Models\CreditNote;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AffiliateCommissionCreditNoteAdjustment extends Model
{
    protected $fillable = [
        'affiliate_commission_id', 'credit_note_id', 'event_type', 'tuition_reversal_delta_minor',
        'commission_reversal_delta_minor', 'status_before', 'status_after', 'idempotency_key',
        'recorded_by', 'ledger_entry_id', 'occurred_at',
    ];

    protected $casts = [
        'event_type' => AffiliateCreditNoteEventType::class,
        'status_before' => AffiliateCommissionStatus::class,
        'status_after' => AffiliateCommissionStatus::class,
        'tuition_reversal_delta_minor' => 'integer',
        'commission_reversal_delta_minor' => 'integer',
        'occurred_at' => 'immutable_datetime',
    ];

    protected static function booted(): void
    {
        static::updating(function (): void {
            throw new \LogicException('Affiliate Credit Note adjustments are append-only evidence.');
        });
        static::deleting(function (): void {
            throw new \LogicException('Affiliate Credit Note adjustments are append-only evidence.');
        });
    }

    public function AffiliateCommission()
    {
        return $this->belongsTo(AffiliateCommission::class, 'affiliate_commission_id');
    }

    public function CreditNote()
    {
        return $this->belongsTo(CreditNote::class, 'credit_note_id');
    }

    public function RecordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
