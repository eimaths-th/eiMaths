<?php

namespace App\Domain\Affiliate\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class AffiliateAuditLog extends Model
{
    protected $table = 'affiliate_audit_logs';

    protected $fillable = [
        'correlation_id',
        'operation',
        'subject_type',
        'subject_id',
        'actor_id',
        'actor_name_snapshot',
        'prior_state',
        'resulting_state',
        'amount_minor',
        'outcome',
        'error_code',
        'occurred_at',
    ];

    protected $casts = [
        'subject_id' => 'integer',
        'actor_id' => 'integer',
        'prior_state' => 'array',
        'resulting_state' => 'array',
        'amount_minor' => 'integer',
        'occurred_at' => 'immutable_datetime',
    ];

    protected static function booted(): void
    {
        static::updating(function (): void {
            throw new LogicException('Affiliate audit logs are append-only evidence.');
        });
        static::deleting(function (): void {
            throw new LogicException('Affiliate audit logs are append-only evidence.');
        });
    }

    public function Actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
