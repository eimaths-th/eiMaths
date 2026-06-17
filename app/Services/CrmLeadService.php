<?php

namespace App\Services;

use App\Models\CrmLead;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CrmLeadService
{
    public function __construct(
        private CrmLeadNotificationService $notificationService,
    ) {
    }

    public function createFromRegistration(array $data): CrmLead
    {
        $data['created_from'] = $data['created_from'] ?? 'unknown';
        $data['lead_status'] = $data['lead_status'] ?? 'new_lead';
        if ($data['created_from'] === 'lead_registration_website' && blank($data['utm_source'] ?? null)) {
            $data['utm_source'] = 'website';
        }
        $data['interest'] = $data['interest'] ?? 'ทดลองเรียนฟรี 2 คลาส';
        $data['first_touch_source'] = $data['first_touch_source']
            ?? $data['utm_source']
            ?? $data['line_source']
            ?? $data['created_from'];
        $data['last_touch_source'] = $data['last_touch_source']
            ?? $data['utm_source']
            ?? $data['line_source']
            ?? $data['created_from'];
        $data['duplicate_checked_at'] = now();

        [$lead, $wasMerged, $previousStatus] = DB::transaction(function () use ($data) {
            $existingLead = $this->findExistingLead($data);

            if (! $existingLead) {
                return [
                    CrmLead::create(Arr::only($data, (new CrmLead())->getFillable())),
                    false,
                    null,
                ];
            }

            $previousStatus = $existingLead->lead_status;
            $existingLead->fill($this->mergeLeadData($existingLead, $data));
            $existingLead->save();

            return [$existingLead->fresh(), true, $previousStatus];
        });

        $lead->setAttribute('was_merged', $wasMerged);
        $lead->setAttribute('merge_action', $wasMerged ? 'merged' : 'created');
        $lead->setAttribute('previous_lead_status', $previousStatus);

        if (! $wasMerged || ($previousStatus === 'line_login_pending' && $lead->lead_status === 'new_lead')) {
            $this->notificationService->notifyStaffNewLead($lead);
        }
        $this->notificationService->sendCustomerConfirmation($lead);

        return $lead;
    }

    public function createOrUpdateFromLiffLogin(array $data): CrmLead
    {
        $data['line_follow_status'] = $data['line_follow_status'] ?? 'unknown';
        $data['line_source'] = $data['line_source'] ?? 'new_customer_line_oa';
        $data['utm_source'] = $data['utm_source'] ?: 'website';
        $data['utm_medium'] = $data['utm_medium'] ?: 'line_login_button';
        $data['utm_campaign'] = $data['utm_campaign'] ?: 'unknown';
        $data['first_touch_source'] = $data['first_touch_source'] ?? $data['utm_source'];
        $data['last_touch_source'] = $data['last_touch_source'] ?? 'new_customer_line_oa';
        $data['created_from'] = $data['created_from'] ?? 'free_trial_liff_login';
        $data['lead_status'] = $data['lead_status'] ?? 'line_login_pending';
        $data['interest'] = $data['interest'] ?? 'ทดลองเรียนฟรี 2 คลาส';
        $data['duplicate_checked_at'] = now();

        [$lead, $wasMerged] = DB::transaction(function () use ($data) {
            $lead = CrmLead::query()
                ->where('line_user_id', $data['line_user_id'])
                ->lockForUpdate()
                ->first();

            if (! $lead) {
                return [
                    CrmLead::create(Arr::only($data, (new CrmLead())->getFillable())),
                    false,
                ];
            }

            $lead->fill($this->mergeLeadData($lead, $data));
            $lead->save();

            return [$lead->fresh(), true];
        });

        $lead->setAttribute('was_merged', $wasMerged);
        $lead->setAttribute('merge_action', $wasMerged ? 'merged' : 'created');

        return $lead;
    }

    private function findExistingLead(array $data): ?CrmLead
    {
        if (! empty($data['line_user_id'])) {
            $lineLead = CrmLead::query()
                ->where('line_user_id', $data['line_user_id'])
                ->lockForUpdate()
                ->first();

            if ($lineLead) {
                return $lineLead;
            }
        }

        return CrmLead::query()
            ->where('phone', $data['phone'])
            ->latest()
            ->lockForUpdate()
            ->first();
    }

    private function mergeLeadData(CrmLead $lead, array $data): array
    {
        $fillable = (new CrmLead())->getFillable();
        $incoming = Arr::only($data, $fillable);
        $incoming = $this->preserveTrackedAttribution($lead, $incoming);
        $alwaysRefresh = [
            'last_touch_source',
            'utm_source',
            'utm_medium',
            'utm_campaign',
            'utm_content',
            'utm_term',
            'line_display_name',
            'line_picture_url',
            'line_follow_status',
            'line_last_message_at',
            'line_conversation_state',
            'line_ref',
            'line_source',
            'duplicate_checked_at',
        ];

        $merged = [];
        foreach ($incoming as $field => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            if (in_array($field, $alwaysRefresh, true) || blank($lead->{$field})) {
                $merged[$field] = $value;
            }
        }

        $merged['first_touch_source'] = $lead->first_touch_source
            ?: ($data['first_touch_source'] ?? $data['utm_source'] ?? null);

        if (
            $lead->lead_status === 'line_login_pending'
            && filled($incoming['lead_status'] ?? null)
            && $incoming['lead_status'] !== 'line_login_pending'
        ) {
            $merged['lead_status'] = $incoming['lead_status'];
        }

        if ($lead->lead_status === 'enrolled') {
            unset($merged['lead_status']);
        }

        return $merged;
    }

    private function preserveTrackedAttribution(CrmLead $lead, array $incoming): array
    {
        $hasExistingCampaignAttribution = filled($lead->utm_source)
            && $lead->utm_source !== 'line'
            && filled($lead->utm_campaign)
            && $lead->utm_campaign !== 'unknown';

        $incomingLooksLikeLineFallback = ($incoming['utm_source'] ?? null) === 'line'
            && (($incoming['utm_campaign'] ?? null) === 'unknown' || blank($incoming['utm_campaign'] ?? null));

        if (! $hasExistingCampaignAttribution || ! $incomingLooksLikeLineFallback) {
            return $incoming;
        }

        foreach (['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'first_touch_source'] as $field) {
            unset($incoming[$field]);
        }

        return $incoming;
    }
}
