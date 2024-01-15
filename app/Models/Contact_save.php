<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactSave extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'nickname',
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
        'level2',
        'term2',
        'bookuse2',
        'level2_name',
        'term2_name',
        'bookuse2_name',
        'discontinued',
        'discontinued_date',
        'discontinued_reason',
    ];

    public function order()
    {
        return $this->hasOne(Order::class, 'cid', 'id');
    }

    public function histrories()
    {
        return $this->hasMany(Histrories::class, 'centre');
    }

    public function getStudentCodeAttribute()
    {

        return $this->code . ' ' . $this->name;
    }
}
