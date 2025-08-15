<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Receipt extends Model
{
    use HasFactory;
    // Fields that are allowed for mass assignment
    protected $fillable = [
        'centre',
        'cid',
        'courses_pending_id',
        'ref',
        'student_no',
        'student_name',
        'receipt_number',
        'payment_date',
        'oid',
        'level',
        'total_fee',
        'start_term',
        'status',
        'payment'
    ];

    // Fields that are not allowed for mass assignment
    protected $guarded = [
        'id', // Assuming 'id' is your primary key
        'created_by',
        'updated_by',
    ];

    public function affiliateCommissions()
    {
        return $this->hasMany(AffiliateCommissionList::class, 'receipt_id');
    }
    public function contact()
    {
        return $this->belongsTo(Contact::class, 'cid', 'id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'centre', 'id');
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'order_id', 'oid');
    }

    // public function bookuses()
    // {
    //     return $this->orderDetails()->pname;
    // }

    public function levels()
    {
        return $this->belongsTo(level::class, 'level', 'id');
    }
}
