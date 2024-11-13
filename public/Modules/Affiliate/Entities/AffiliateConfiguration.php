<?php

namespace Modules\Affiliate\Entities;

use App\Traits\Tenantable;
use Illuminate\Database\Eloquent\Model;

class AffiliateConfiguration extends Model
{
    use Tenantable;

    protected $fillable = [
        'key',
        'value',
    ];
}
