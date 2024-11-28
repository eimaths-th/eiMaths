<?php

namespace Modules\Affiliate\Entities;

use App\Models\User;
use App\Traits\Tenantable;
use Illuminate\Database\Eloquent\Model;

class AffiliateLink extends Model
{
    use Tenantable;

    protected $fillable = [
        'affiliate_link',
        'owner_id',
        'visits',
        'registered',
        'purchased',
        'commissions',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function registerUser()
    {
        return $this->hasMany(ReferralUser::class, 'affiliate_link_id');
    }

    public function payment()
    {
        return $this->hasMany(AffiliateReferralPayment::class, 'affiliate_link_id');
    }
}
