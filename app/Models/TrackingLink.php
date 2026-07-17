<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrackingLink extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tracking_code',
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
}
