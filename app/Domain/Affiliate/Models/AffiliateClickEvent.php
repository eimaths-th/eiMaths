<?php

namespace App\Domain\Affiliate\Models;

use App\Domain\Affiliate\Casts\UtcImmutableDateTime;
use App\Models\AffiliateContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class AffiliateClickEvent extends Model
{
    protected $table = 'affiliate_click_events';

    public const UPDATED_AT = null;

    protected $fillable = [
        'affiliate_profile_id',
        'affiliate_content_id',
        'campaign_code',
        'source_type',
        'destination_target',
        'visitor_hash',
        'unique_window_key',
        'is_unique',
        'clicked_at',
    ];

    protected $casts = [
        'affiliate_profile_id' => 'integer',
        'affiliate_content_id' => 'integer',
        'is_unique' => 'boolean',
        'clicked_at' => UtcImmutableDateTime::class,
        'created_at' => UtcImmutableDateTime::class,
    ];

    protected static function booted(): void
    {
        static::updating(function (): void {
            throw new LogicException('Affiliate click events are append-only evidence.');
        });
        static::deleting(function (): void {
            throw new LogicException('Affiliate click events are append-only evidence.');
        });
    }

    public function AffiliateProfile(): BelongsTo
    {
        return $this->belongsTo(AffiliateProfile::class, 'affiliate_profile_id');
    }

    public function AffiliateContent(): BelongsTo
    {
        return $this->belongsTo(AffiliateContent::class, 'affiliate_content_id');
    }
}
