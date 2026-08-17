<?php

namespace App\Domain\CreditNote\Models;

use Illuminate\Database\Eloquent\Model;

class CreditNoteRunningNumber extends Model
{
    protected $fillable = ['centre_id', 'year', 'month', 'number'];

    protected $casts = [
        'centre_id' => 'integer',
        'year' => 'integer',
        'month' => 'integer',
        'number' => 'integer',
    ];
}
