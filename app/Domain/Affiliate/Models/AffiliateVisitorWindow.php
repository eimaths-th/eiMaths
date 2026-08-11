<?php

namespace App\Domain\Affiliate\Models;

use App\Domain\Affiliate\Casts\UtcImmutableDateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliateVisitorWindow extends Model
{
    protected $table = 'affiliate_visitor_windows';

    protected $fillable = [
        'affiliate_profile_id',
        'visitor_hash',
        'unique_window_key',
        'last_unique_at',
        'last_seen_at',
    ];

    protected $casts = [
        'affiliate_profile_id' => 'integer',
        'last_unique_at' => UtcImmutableDateTime::class,
        'last_seen_at' => UtcImmutableDateTime::class,
    ];

    public function AffiliateProfile(): BelongsTo
    {
        return $this->belongsTo(AffiliateProfile::class, 'affiliate_profile_id');
    }
}
