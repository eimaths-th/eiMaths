<?php

namespace App\Domain\Affiliate\Models;

use App\Models\AffiliateContent;
use App\Models\Contact;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class AffiliateAttribution extends Model
{
    protected $table = 'affiliate_attributions';

    protected $fillable = [
        'affiliate_profile_id',
        'contact_id',
        'captured_at',
        'expires_at',
        'ended_at',
        'source_context',
    ];

    protected $casts = [
        'affiliate_profile_id' => 'integer',
        'contact_id' => 'integer',
        'affiliate_click_event_id' => 'integer',
        'affiliate_content_id' => 'integer',
        'captured_at' => 'immutable_datetime',
        'expires_at' => 'immutable_datetime',
        'ended_at' => 'immutable_datetime',
    ];

    private const IMMUTABLE_CONTEXT_FIELDS = [
        'affiliate_click_event_id',
        'affiliate_content_id',
        'affiliate_campaign_code',
        'affiliate_source_type',
        'affiliate_unique_window_key',
        'affiliate_captured_destination',
    ];

    protected static function booted(): void
    {
        static::updating(function (self $attribution): void {
            if (array_intersect(array_keys($attribution->getDirty()), self::IMMUTABLE_CONTEXT_FIELDS) !== []) {
                throw new LogicException('Affiliate attribution click context is immutable.');
            }
        });
    }

    public function AffiliateProfile(): BelongsTo
    {
        return $this->belongsTo(AffiliateProfile::class, 'affiliate_profile_id');
    }

    public function Contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function AffiliateClickEvent(): BelongsTo
    {
        return $this->belongsTo(AffiliateClickEvent::class, 'affiliate_click_event_id');
    }

    public function AffiliateContent(): BelongsTo
    {
        return $this->belongsTo(AffiliateContent::class, 'affiliate_content_id');
    }
}
