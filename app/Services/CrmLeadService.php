<?php

namespace App\Services;

use App\Models\CrmLead;
use Illuminate\Support\Arr;

class CrmLeadService
{
    public function __construct(
        private CrmLeadNotificationService $notificationService,
    ) {
    }

    public function createFromRegistration(array $data): CrmLead
    {
        $data['created_from'] = $data['created_from'] ?? 'lead_registration_website';
        $data['lead_status'] = $data['lead_status'] ?? 'new_lead';
        $data['duplicate_checked_at'] = now();

        $existingLead = CrmLead::query()
            ->where('phone', $data['phone'])
            ->where('created_at', '>=', now()->subDay())
            ->latest()
            ->first();

        if ($existingLead) {
            $data['duplicate_of_lead_id'] = $existingLead->id;
            $data['first_touch_source'] = $existingLead->first_touch_source ?: ($data['first_touch_source'] ?? null);
        }

        $lead = CrmLead::create(Arr::only($data, (new CrmLead())->getFillable()));

        $this->notificationService->notifyStaffNewLead($lead);
        $this->notificationService->sendCustomerConfirmation($lead);

        return $lead;
    }
}
