<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Affiliate\Models\AffiliateProfile;

class CoursePending extends Model
{
    use HasFactory;

    // Define the table name if it's not the plural form of the model name
    protected $table = 'courses_pennding';  // Make sure the table name matches your database table

    // Specify the fillable fields for mass assignment
    protected $fillable = [
        'ref',
        'name',
        'email',
        'telp',
        'appointment_date',
        'student_name',
        'student_nickname',
        'grade',
        'type_parent',
        'course_name',
        'start_course',
        'status',
        'to_course',
        'start_term',
        'to_term',
        'course_id',
        'department_id',
        'day',
        'period',
        'price',
        'slip',
    ];

    protected $casts = [
        'affiliate_captured_at' => 'immutable_datetime',
        'affiliate_expires_at' => 'immutable_datetime',
    ];

    public function AffiliateProfile()
    {
        return $this->belongsTo(AffiliateProfile::class, 'affiliate_profile_id');
    }
    public function affiliateCommissions()
    {
        return $this->hasMany(AffiliateCommissionList::class, 'courses_pending_id');
    }
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
