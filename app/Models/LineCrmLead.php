<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LineCrmLead extends Model
{
    protected $fillable = [
        'line_user_id',
        'display_name',
        'level',
        'interest',
        'step',
        'source',
        'status',
        'last_message',
        'last_interaction_at',
    ];
}
