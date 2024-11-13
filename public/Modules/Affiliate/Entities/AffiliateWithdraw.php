<?php

namespace Modules\Affiliate\Entities;


use App\Traits\Tenantable;
use App\User;
use Illuminate\Database\Eloquent\Model;

class AffiliateWithdraw extends Model
{
    use Tenantable;

    protected $fillable = [
        'user_id',
        'withdraw_amount',
        'payment_type',
        'status',
        'request_date',
        'confirmed_by',
        'confirm_date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function confirmedUser()
    {
        return $this->belongsTo(User::class, 'confirmed_by', 'id')->withDefault(['name' => null]);
    }

}
