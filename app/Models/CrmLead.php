<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmLead extends Model
{
    public const CREATED_FROM = [
        'unknown' => 'Unknown',
        'lead_registration_website' => 'Website registration',
        'new_customer_line_oa_chatbot' => 'New Customer LINE OA chatbot',
        'new_customer_line_oa_follow' => 'New Customer LINE OA follow',
        'line_oa_chatbot' => 'LINE OA chatbot',
        'line_oa_follow' => 'LINE OA follow',
        'line_liff_registration' => 'LINE LIFF registration',
    ];

    public const STATUSES = [
        'new_lead' => 'ลูกค้าใหม่',
        'registered' => 'ลงทะเบียนแล้ว',
        'waiting_for_contact' => 'รอติดต่อกลับ',
        'contacted' => 'ติดต่อแล้ว',
        'trial_scheduled' => 'นัดทดลองเรียนแล้ว',
        'trial_completed' => 'ทดลองเรียนแล้ว',
        'enrolled' => 'สมัครเรียนแล้ว',
        'not_interested' => 'ไม่สนใจ',
        'cannot_contact' => 'ติดต่อไม่ได้',
    ];

    protected $fillable = [
        'parent_name',
        'phone',
        'line_id',
        'line_user_id',
        'line_display_name',
        'line_picture_url',
        'line_follow_status',
        'line_followed_at',
        'line_last_message_at',
        'line_conversation_state',
        'line_ref',
        'line_source',
        'email',
        'preferred_contact_time',
        'student_name',
        'student_grade',
        'student_school',
        'student_age',
        'pain_point',
        'interest',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_content',
        'utm_term',
        'fbclid',
        'gclid',
        'ttclid',
        'referrer',
        'landing_page',
        'first_touch_source',
        'last_touch_source',
        'created_from',
        'lead_status',
        'assigned_staff_id',
        'last_contact_at',
        'next_follow_up_at',
        'trial_scheduled_at',
        'trial_completed_at',
        'enrolled_at',
        'assessment_answers',
        'assessment_level',
        'assessment_note',
        'answers',
        'test_score',
        'total_questions',
        'weak_topics',
        'test_duration_seconds',
        'duplicate_of_lead_id',
        'duplicate_checked_at',
    ];

    protected $casts = [
        'assessment_answers' => 'array',
        'answers' => 'array',
        'weak_topics' => 'array',
        'last_contact_at' => 'datetime',
        'next_follow_up_at' => 'datetime',
        'trial_scheduled_at' => 'datetime',
        'trial_completed_at' => 'datetime',
        'enrolled_at' => 'datetime',
        'duplicate_checked_at' => 'datetime',
        'line_followed_at' => 'datetime',
        'line_last_message_at' => 'datetime',
    ];

    public function assignedStaff()
    {
        return $this->belongsTo(User::class, 'assigned_staff_id');
    }

    public function notes()
    {
        return $this->hasMany(CrmLeadNote::class, 'lead_id')->latest();
    }

    public function duplicateOf()
    {
        return $this->belongsTo(self::class, 'duplicate_of_lead_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->lead_status] ?? $this->lead_status;
    }

    public function getCreatedFromLabelAttribute(): string
    {
        return self::CREATED_FROM[$this->created_from] ?? $this->created_from;
    }
}
