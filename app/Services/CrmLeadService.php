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

        [$lead, $wasMerged] = DB::transaction(function () use ($data) {
            $existingLead = $this->findExistingLead($data);

            if (! $existingLead) {
                return [
                    CrmLead::create(Arr::only($data, (new CrmLead())->getFillable())),
                    false,
                ];
            }

            $existingLead->fill($this->mergeLeadData($existingLead, $data));
            $existingLead->save();

            return [$existingLead->fresh(), true];
        });

        $lead->setAttribute('was_merged', $wasMerged);
        $lead->setAttribute('merge_action', $wasMerged ? 'merged' : 'created');

        if (! $wasMerged) {
            $this->notificationService->notifyStaffNewLead($lead);
        }
        $this->notificationService->sendCustomerConfirmation($lead);

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

        if ($lead->lead_status === 'enrolled') {
            unset($merged['lead_status']);
        }

        return $merged;
    }
}
