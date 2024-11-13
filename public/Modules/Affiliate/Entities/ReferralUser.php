<?php

namespace Modules\Affiliate\Entities;

use App\Traits\Tenantable;
use Illuminate\Database\Eloquent\Model;

class ReferralUser extends Model
{
    use Tenantable;

    protected $fillable = [
        'user_id',
        'affiliate_link_id',
        'validity_start_date',
    ];

    public function affiliateLink()
    {
        return $this->belongsTo(AffiliateLink::class, 'affiliate_link_id');
    }
}
