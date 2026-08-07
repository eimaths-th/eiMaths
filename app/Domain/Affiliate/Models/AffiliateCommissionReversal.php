<?php

namespace App\Domain\Affiliate\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class AffiliateCommissionReversal extends Model
{
    protected $table = 'affiliate_commission_reversals';

    protected $fillable = [
        'affiliate_commission_id',
        'external_refund_reference',
        'refunded_tuition_minor',
        'reversal_minor',
        'idempotency_key',
        'recorded_by',
        'reason',
        'occurred_at',
    ];

    protected $casts = [
        'refunded_tuition_minor' => 'integer',
        'reversal_minor' => 'integer',
        'occurred_at' => 'immutable_datetime',
    ];

    protected static function booted(): void
    {
        static::updating(function (): void {
            throw new LogicException('Affiliate commission reversals are append-only evidence.');
        });
        static::deleting(function (): void {
            throw new LogicException('Affiliate commission reversals are append-only evidence.');
        });
    }

    public function AffiliateCommission(): BelongsTo
    {
        return $this->belongsTo(AffiliateCommission::class, 'affiliate_commission_id');
    }

    public function RecordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
