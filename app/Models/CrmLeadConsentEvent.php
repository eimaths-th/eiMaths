<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmLeadConsentEvent extends Model
{
    protected $fillable = ['marketing_consent', 'recorded_at', 'context'];

    protected $casts = [
        'marketing_consent' => 'boolean',
        'recorded_at' => 'datetime',
        'context' => 'array',
    ];
}
