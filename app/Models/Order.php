<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [
        'centre',
        'cid',
        'order_number',
        'detail',
        'total_price',
        'refund',
        'register_fee',
        'promotion',
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'cid', 'id');
    }
}
