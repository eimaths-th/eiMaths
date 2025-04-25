<?php

namespace Modules\Affiliate\Entities;

use App\Models\User;
use App\Traits\Tenantable;
// use App\User;
use Illuminate\Database\Eloquent\Model;


/**
 * @property integer $id
 * @property integer $pv_number,
 * @property integer $user_id
 * @property string $withdraw_amount
 * @property string $payment_type
 * @property string $status
 * @property string $request_date
 * @property string $confirmed_by
 * @property string $confirm_date
 * @property string $rejected_by
 * @property string $reject_date
 */
class AffiliateWithdraw extends Model
{
    use Tenantable;

    protected $fillable = [
        'user_id',
        'pv_number', // pv_running_number
        'withdraw_amount',
        'payment_type',
        'status',
        'request_date',
        'confirmed_by',
        'confirm_date',
        'rejected_by',
        'reject_date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    
    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by', 'id');
    }
    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by', 'id');
    }

    public function confirmedUser()
    {
        return $this->belongsTo(User::class, 'confirmed_by', 'id')->withDefault(['name' => null]);
    }

}
