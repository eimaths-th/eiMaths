<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmLeadNote extends Model
{
    protected $fillable = [
        'lead_id',
        'note',
        'status_before',
        'status_after',
        'created_by',
    ];

    public function lead()
    {
        return $this->belongsTo(CrmLead::class, 'lead_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
