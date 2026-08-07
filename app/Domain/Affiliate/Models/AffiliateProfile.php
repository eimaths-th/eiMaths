<?php

namespace App\Domain\Affiliate\Models;

use App\Domain\Affiliate\Enums\AffiliateProfileStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliateProfile extends Model
{
    protected $table = 'affiliate_profiles';

    protected $fillable = [
        'user_id',
        'referral_code',
        'status',
        'migration_status',
        'approved_by',
        'approved_at',
        'suspended_by',
        'suspended_at',
        'deactivated_by',
        'deactivated_at',
        'status_reason',
    ];

    protected $casts = [
        'status' => AffiliateProfileStatus::class,
        'approved_at' => 'datetime',
        'suspended_at' => 'datetime',
        'deactivated_at' => 'datetime',
    ];

    public function User(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function ApprovedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function SuspendedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'suspended_by');
    }

    public function DeactivatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deactivated_by');
    }
}
