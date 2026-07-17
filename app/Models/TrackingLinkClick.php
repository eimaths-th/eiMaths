<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrackingLinkClick extends Model
{
    protected $fillable = [
        'tracking_link_id',
        'ip_hash',
        'user_agent',
        'referrer',
        'landing_page',
    ];

    public function trackingLink()
    {
        return $this->belongsTo(TrackingLink::class);
    }
}
