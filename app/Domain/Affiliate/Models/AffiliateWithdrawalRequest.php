<?php

namespace App\Domain\Affiliate\Models;

use App\Domain\Affiliate\Enums\AffiliatePayoutType;
use App\Domain\Affiliate\Enums\AffiliateWithdrawalStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class AffiliateWithdrawalRequest extends Model
{
    protected $table = 'affiliate_withdrawal_requests';

    protected $fillable = [
        'affiliate_profile_id',
        'affiliate_payout_account_id',
        'payout_type',
        'amount_minor',
        'status',
        'payout_snapshot_encrypted',
        'requested_at',
        'approved_by',
        'approved_at',
        'paid_by',
        'paid_at',
        'closed_by',
        'closed_at',
        'decision_reason',
        'request_key',
        'approval_key',
        'payment_key',
        'closure_key',
    ];

    protected $casts = [
        'payout_type' => AffiliatePayoutType::class,
        'amount_minor' => 'integer',
        'status' => AffiliateWithdrawalStatus::class,
        'payout_snapshot_encrypted' => 'encrypted:array',
        'requested_at' => 'immutable_datetime',
        'approved_at' => 'immutable_datetime',
        'paid_at' => 'immutable_datetime',
        'closed_at' => 'immutable_datetime',
    ];

    private const IMMUTABLE_FIELDS = [
        'affiliate_profile_id',
        'affiliate_payout_account_id',
        'payout_type',
        'amount_minor',
        'payout_snapshot_encrypted',
        'requested_at',
        'request_key',
    ];

    protected static function booted(): void
    {
        static::updating(function (self $withdrawal): void {
            if (array_intersect(array_keys($withdrawal->getDirty()), self::IMMUTABLE_FIELDS) !== []) {
                throw new LogicException('Affiliate withdrawal source fields are immutable.');
            }
        });
    }

    public function AffiliateProfile(): BelongsTo
    {
        return $this->belongsTo(AffiliateProfile::class, 'affiliate_profile_id');
    }

    public function AffiliatePayoutAccount(): BelongsTo
    {
        return $this->belongsTo(AffiliatePayoutAccount::class, 'affiliate_payout_account_id');
    }

    public function ApprovedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function PaidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function ClosedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }
}
