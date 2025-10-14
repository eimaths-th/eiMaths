<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetailList extends Model
{
    use HasFactory;
    protected $fillable = ['order_id', 'level', 'term','book', 'price', 'bprice','bqty'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
