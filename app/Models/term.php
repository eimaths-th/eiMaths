<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class term extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'level_id',
        'status',
    ];
}
