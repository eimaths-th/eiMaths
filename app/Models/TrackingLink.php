<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrackingLink extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tracking_code',
        'short_code',
        'name',
        'base_url',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_content',
        'utm_term',
        'generated_url',
        'created_by',
        'created_by_name',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function leads()
    {
        return $this->hasMany(CrmLead::class, 'tracking_link_id');
    }

    public function clicks()
    {
        return $this->hasMany(TrackingLinkClick::class);
    }

    public function getShortUrlAttribute(): ?string
    {
        if (! $this->short_code) {
            return null;
        }

        return rtrim(config('tracking.public_short_base_url'), '/') . '/go/' . $this->short_code;
    }
}
