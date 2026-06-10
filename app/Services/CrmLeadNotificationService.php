<?php

namespace App\Services;

use App\Models\CrmLead;
use Illuminate\Support\Facades\Log;

class CrmLeadNotificationService
{
    public function notifyStaffNewLead(CrmLead $lead): void
    {
        Log::info('CRM new lead notification placeholder', [
            'lead_id' => $lead->id,
            'source' => $lead->utm_source ?: $lead->last_touch_source,
            'campaign' => $lead->utm_campaign,
            'parent_name' => $lead->parent_name,
            'phone' => $lead->phone,
            'student_name' => $lead->student_name,
            'student_grade' => $lead->student_grade,
        ]);
    }

    public function sendCustomerConfirmation(CrmLead $lead): void
    {
        Log::info('CRM customer confirmation placeholder', [
            'lead_id' => $lead->id,
            'email' => $lead->email,
            'line_id' => $lead->line_id,
            'phone' => $lead->phone,
        ]);
    }
}
