<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'email',
        'address',
        'postcode',
        'telephone',
        'ctype',
        'centre',
        'code',
        'start_date',
        'start_term',
        'school',
        'gender',
        'birth_date',
        'father_name',
        'father_email',
        'father_mobile',
        'mother_name',
        'mother_email',
        'mother_mobile',
        'order',
        'level',
        'term',
        'bookuse',
        'level_name',
        'term_name',
        'bookuse_name',
        'discontinued',
        'discontinued_date',
        'discontinued_reason',
    ];

    public function order()
    {
        return $this->hasOne(Order::class, 'cid', 'id');
    }
}
