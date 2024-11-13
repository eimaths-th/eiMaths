<?php

namespace Modules\Affiliate\Entities;

use App\Traits\Tenantable;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Modules\CourseSetting\Entities\Course;

class AffiliateReferralPayment extends Model
{
    use Tenantable;

    protected $fillable = [
        'payment_to',
        'amount',
        'affiliate_link_id',
        'payment_from',
        'course_id',
        'date',
        'status',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function incomeFrom()
    {
        return $this->belongsTo(User::class, 'payment_from');
    }
}
