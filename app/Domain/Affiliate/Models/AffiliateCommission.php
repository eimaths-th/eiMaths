<?php

namespace App\Domain\Affiliate\Models;

use App\Domain\Affiliate\Enums\AffiliateCommissionStatus;
use App\Models\Contact;
use App\Models\Order;
use App\Models\Receipt;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;

class AffiliateCommission extends Model
{
    protected $table = 'affiliate_commissions';

    protected $fillable = [
        'affiliate_profile_id',
        'affiliate_attribution_id',
        'contact_id',
        'order_id',
        'receipt_id',
        'eligible_tuition_minor',
        'rate_basis_points',
        'commission_minor',
        'reversed_minor',
        'status',
        'release_at',
        'held_by',
        'held_at',
        'hold_reason',
        'source_key',
    ];

    protected $casts = [
        'status' => AffiliateCommissionStatus::class,
        'eligible_tuition_minor' => 'integer',
        'rate_basis_points' => 'integer',
        'commission_minor' => 'integer',
        'reversed_minor' => 'integer',
        'release_at' => 'immutable_datetime',
        'held_at' => 'immutable_datetime',
    ];

    private const IMMUTABLE_FIELDS = [
        'affiliate_profile_id',
        'affiliate_attribution_id',
        'contact_id',
        'order_id',
        'receipt_id',
        'eligible_tuition_minor',
        'rate_basis_points',
        'commission_minor',
        'source_key',
    ];

    protected static function booted(): void
    {
        static::updating(function (self $commission): void {
            if (array_intersect(array_keys($commission->getDirty()), self::IMMUTABLE_FIELDS) !== []) {
                throw new LogicException('Affiliate commission source fields are immutable.');
            }
        });
        static::deleting(function (): void {
            throw new LogicException('Affiliate commissions are append-only evidence.');
        });
    }

    public function AffiliateProfile(): BelongsTo
    {
        return $this->belongsTo(AffiliateProfile::class, 'affiliate_profile_id');
    }

    public function AffiliateAttribution(): BelongsTo
    {
        return $this->belongsTo(AffiliateAttribution::class, 'affiliate_attribution_id');
    }

    public function Contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function Order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function Receipt(): BelongsTo
    {
        return $this->belongsTo(Receipt::class, 'receipt_id');
    }

    public function HeldBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'held_by');
    }

    public function SourceItems(): HasMany
    {
        return $this->hasMany(AffiliateCommissionSourceItem::class, 'affiliate_commission_id');
    }

    public function Reversals(): HasMany
    {
        return $this->hasMany(AffiliateCommissionReversal::class, 'affiliate_commission_id');
    }
}
