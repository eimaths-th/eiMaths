<?php

namespace App\Domain\Affiliate\Models;

use App\Domain\Affiliate\Enums\AffiliateLedgerEntryType;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use LogicException;

class AffiliateLedgerEntry extends Model
{
    protected $table = 'affiliate_ledger_entries';

    public $timestamps = false;

    protected $fillable = [
        'affiliate_profile_id',
        'entry_type',
        'available_delta_minor',
        'reserved_delta_minor',
        'balance_available_after_minor',
        'balance_reserved_after_minor',
        'source_type',
        'source_id',
        'idempotency_key',
        'actor_id',
        'reason_code',
        'occurred_at',
    ];

    protected $casts = [
        'entry_type' => AffiliateLedgerEntryType::class,
        'available_delta_minor' => 'integer',
        'reserved_delta_minor' => 'integer',
        'balance_available_after_minor' => 'integer',
        'balance_reserved_after_minor' => 'integer',
        'source_id' => 'integer',
        'occurred_at' => 'immutable_datetime',
    ];

    protected static function booted(): void
    {
        static::updating(function (): void {
            throw new LogicException('Affiliate ledger entries are append-only evidence.');
        });
        static::deleting(function (): void {
            throw new LogicException('Affiliate ledger entries are append-only evidence.');
        });
    }

    public function AffiliateProfile(): BelongsTo
    {
        return $this->belongsTo(AffiliateProfile::class, 'affiliate_profile_id');
    }

    public function Actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function Source(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'source_type', 'source_id');
    }
}
