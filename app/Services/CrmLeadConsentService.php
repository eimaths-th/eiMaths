<?php

namespace App\Services;

use App\Models\CrmLead;

class CrmLeadConsentService
{
    public function Record(CrmLead $lead, array $registration): void
    {
        if (! array_key_exists('marketing_consent', $registration)) {
            return;
        }

        $recorded_at = now();
        $marketing_consent = (bool) $registration['marketing_consent'];
        $context = $registration['marketing_consent_context'];

        $lead->marketing_consent = $marketing_consent;
        $lead->marketing_consent_recorded_at = $recorded_at;
        $lead->marketing_consent_context = $context;
        $lead->save();
        $lead->ConsentEvents()->create([
            'marketing_consent' => $marketing_consent,
            'recorded_at' => $recorded_at,
            'context' => $context,
        ]);
    }
}
