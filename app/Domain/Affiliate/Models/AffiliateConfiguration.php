<?php

namespace App\Domain\Affiliate\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateConfiguration extends Model
{
    protected $table = 'affiliate_configurations';

    protected $fillable = ['key', 'value', 'lms_id'];

    protected $casts = ['lms_id' => 'integer'];
}
