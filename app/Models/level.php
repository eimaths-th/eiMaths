<?php

namespace App\Models;

use FontLib\Table\Type\name;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class level extends Model
{
    use HasFactory;
    protected $fillable = [
        'price',
        'half_price',
        'book_price',
        'book_half_price',
        'name',
        'full_name',
        'full_name_th',
        'status'
    ];
}
