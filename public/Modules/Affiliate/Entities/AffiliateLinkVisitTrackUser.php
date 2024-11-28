<?php

namespace Modules\Affiliate\Entities;

use App\Traits\Tenantable;
use Illuminate\Database\Eloquent\Model;

class AffiliateLinkVisitTrackUser extends Model
{
    use Tenantable;

    protected $fillable = [
        'affiliate_link_id',
        'ip',
        'agent',
        'date',
    ];
}
