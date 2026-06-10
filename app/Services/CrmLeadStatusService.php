<?php

namespace App\Services;

use App\Models\CrmLead;

class CrmLeadStatusService
{
    public function applyStatus(CrmLead $lead, string $status, array $attributes = []): CrmLead
    {
        $lead->lead_status = $status;

        if ($status === 'contacted' && empty($attributes['last_contact_at'])) {
            $lead->last_contact_at = now();
        }

        if ($status === 'trial_scheduled' && empty($attributes['trial_scheduled_at'])) {
            $lead->trial_scheduled_at = now();
        }

        if ($status === 'trial_completed' && empty($attributes['trial_completed_at'])) {
            $lead->trial_completed_at = now();
        }

        if ($status === 'enrolled' && empty($attributes['enrolled_at'])) {
            $lead->enrolled_at = now();
        }

        foreach ([
            'assigned_staff_id',
            'last_contact_at',
            'next_follow_up_at',
            'trial_scheduled_at',
            'trial_completed_at',
            'enrolled_at',
        ] as $field) {
            if (array_key_exists($field, $attributes)) {
                $lead->{$field} = $attributes[$field] ?: null;
            }
        }

        $lead->save();

        return $lead;
    }
}
