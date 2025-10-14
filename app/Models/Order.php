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
        'courses_pending_id',
        'ref',
        'order_number',
        'detail',
        'total_price',
        'refund',
        'register_fee',
        'access_fee',
        'orther_fee',
        'discount',
        'discount_book',
        'status',
        'payment',
        'free_course',
        'free_course_reason'
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'cid', 'id');
    }

    public function affiliateCommissions()
    {
        return $this->hasMany(AffiliateCommissionList::class, 'order_id');
    }
}
