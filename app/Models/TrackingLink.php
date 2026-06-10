<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrackingLink extends Model
{
    protected $fillable = [
        'name',
        'base_url',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_content',
        'utm_term',
        'generated_url',
        'created_by',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
