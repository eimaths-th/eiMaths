<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketingCampaign extends Model
{
    protected $fillable = [

        'title',
        'message',
        'image_url',

        'segment_level',
        'segment_interest',
        'segment_status',

        'schedule_at',

        'status',

        'target_count',
        'sent_count',
        'failed_count',

        'created_by',
    ];
}
