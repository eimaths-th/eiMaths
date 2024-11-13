<?php

namespace Modules\Affiliate\Entities;

use App\Traits\Tenantable;
use Illuminate\Database\Eloquent\Model;

class AffiliateUserWallet extends Model
{
    use Tenantable;

    protected $fillable = [
        'user_id',
        'amount',
        'paypal_account',
    ];
}
