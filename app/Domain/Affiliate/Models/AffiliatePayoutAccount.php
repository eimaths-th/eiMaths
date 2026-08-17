<?php

namespace App\Domain\Affiliate\Models;

use App\Domain\Affiliate\Enums\AffiliatePayoutType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliatePayoutAccount extends Model
{
    protected $table = 'affiliate_payout_accounts';

    protected $fillable = [
        'affiliate_profile_id',
        'payout_type',
        'details_encrypted',
        'masked_label',
        'is_active',
        'payout_cooldown_until',
    ];

    protected $casts = [
        'payout_type' => AffiliatePayoutType::class,
        'details_encrypted' => 'encrypted:array',
        'is_active' => 'boolean',
        'payout_cooldown_until' => 'immutable_datetime',
    ];

    public function AffiliateProfile(): BelongsTo
    {
        return $this->belongsTo(AffiliateProfile::class, 'affiliate_profile_id');
    }
}
