<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    use HasFactory;

    protected $fillable = [
        'question',
        'answer',
        'position',
        'is_published',
        'published_at',
    ];

    protected $casts = [
        'position' => 'integer',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];
}
