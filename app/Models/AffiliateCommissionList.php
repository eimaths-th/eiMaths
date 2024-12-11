<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $courses_pending_id
 * @property integer $order_id
 * @property integer $receipt_id
 * @property string $status
 * @property float $commission_amount
 * @property string $created_at
 * @property string $updated_at
 */
class AffiliateCommissionList extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'affiliate_commission_list';

    /**
     * @var array
     */
    protected $fillable = ['payment_to','courses_pending_id', 'order_id', 'reciept_id', 'status', 'commission_amount', 'created_at', 'updated_at'];

    public function coursePending()
    {
        return $this->belongsTo(CoursePending::class, 'courses_pending_id');
    }

    /**
     * Define the relationship with the Order model.
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * Define the relationship with the Receipt model.
     */
    public function receipt()
    {
        return $this->belongsTo(Receipt::class, 'reciept_id');
    }
}
