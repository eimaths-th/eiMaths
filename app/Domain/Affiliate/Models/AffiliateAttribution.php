<?php

namespace App\Domain\Affiliate\Models;

use App\Models\Contact;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'captured_at' => 'immutable_datetime',
        'expires_at' => 'immutable_datetime',
        'ended_at' => 'immutable_datetime',
    ];

    public function AffiliateProfile(): BelongsTo
    {
        return $this->belongsTo(AffiliateProfile::class, 'affiliate_profile_id');
    }

    public function Contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }
}
