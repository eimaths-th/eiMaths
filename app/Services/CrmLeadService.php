<?php

namespace App\Services;

use App\Models\CrmLead;
use App\Models\TrackingLink;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CrmLeadService
{
    public function __construct(
        private CrmLeadNotificationService $notificationService,
    ) {
    }

    public function createFromRegistration(array $data): CrmLead
    {
        $data = $this->applyTrackingLinkAttribution($data);
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
        $data['is_active'] = $data['is_active'] ?? true;

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

    /**
     * @return array<int, CrmLead>
     */
    public function createManyFromRegistration(array $data): array
    {
        $data = $this->applyTrackingLinkAttribution($data);
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
        $data['registration_batch_id'] = $data['registration_batch_id']
            ?? $this->makeRegistrationBatchId($data['line_user_id'] ?? null);
        $data['is_active'] = true;

        $students = array_values($data['students'] ?? []);
        unset($data['students'], $data['student_count']);
        $isRestartFlow = filter_var($data['is_restart_flow'] ?? false, FILTER_VALIDATE_BOOL);
        unset($data['is_restart_flow']);

        if (empty($students)) {
            return [$this->createFromRegistration($data)];
        }

        $leads = DB::transaction(function () use ($data, $students, $isRestartFlow) {
            $pendingLead = null;
            $attributionSource = null;

            if (! empty($data['line_user_id'])) {
                if ($isRestartFlow) {
                    $attributionSource = $this->findLatestActiveLineLead($data['line_user_id']);
                    $this->cancelPreviousRestartLeads(
                        $data['line_user_id'],
                        $data['registration_batch_id']
                    );
                } else {
                    $pendingLead = $this->findLatestPendingLineLead($data['line_user_id']);
                    $attributionSource = $pendingLead;
                }
            }

            $createdLeads = [];

            foreach ($students as $index => $student) {
                $studentData = array_merge(
                    $data,
                    Arr::only($student, ['lead_id', 'student_name', 'student_grade', 'student_school', 'student_age', 'pain_point'])
                );

                if ($isRestartFlow) {
                    unset($studentData['lead_id']);
                }

                if (! $isRestartFlow && ! empty($studentData['lead_id'])) {
                    $explicitLeadQuery = CrmLead::query()->whereKey($studentData['lead_id']);
                    if (! empty($studentData['line_user_id'])) {
                        $explicitLeadQuery->where('line_user_id', $studentData['line_user_id']);
                    }

                    $explicitLead = $explicitLeadQuery->lockForUpdate()->first();

                    if ($explicitLead) {
                        $previousStatus = $explicitLead->lead_status;
                        $explicitLead->fill($this->mergeLeadData($explicitLead, $studentData));
                        $explicitLead->save();

                        $lead = $explicitLead->fresh();
                        $lead->setAttribute('was_merged', true);
                        $lead->setAttribute('merge_action', 'updated_by_lead_id');
                        $lead->setAttribute('previous_lead_status', $previousStatus);
                        $createdLeads[] = $lead;
                        $attributionSource = $lead;

                        continue;
                    }
                }

                if (! $isRestartFlow && $index === 0 && $pendingLead) {
                    $previousStatus = $pendingLead->lead_status;
                    $pendingLead->fill($this->mergeLeadData($pendingLead, $studentData));
                    $pendingLead->save();

                    $lead = $pendingLead->fresh();
                    $lead->setAttribute('was_merged', true);
                    $lead->setAttribute('merge_action', 'merged');
                    $lead->setAttribute('previous_lead_status', $previousStatus);
                    $createdLeads[] = $lead;
                    $attributionSource = $lead;

                    continue;
                }

                if ($attributionSource) {
                    $studentData = $this->copyTrackedAttributionFromLead($attributionSource, $studentData);
                }

                $lead = CrmLead::create(Arr::only($studentData, (new CrmLead())->getFillable()))->fresh();
                $lead->setAttribute('was_merged', false);
                $lead->setAttribute('merge_action', 'created');
                $lead->setAttribute('previous_lead_status', null);
                $createdLeads[] = $lead;
            }

            return $createdLeads;
        });

        foreach ($leads as $lead) {
            $previousStatus = $lead->getAttribute('previous_lead_status');
            if (
                ! $lead->getAttribute('was_merged')
                || ($previousStatus === 'line_login_pending' && $lead->lead_status === 'new_lead')
            ) {
                $this->notificationService->notifyStaffNewLead($lead);
            }
            $this->notificationService->sendCustomerConfirmation($lead);
        }

        return $leads;
    }

    public function createOrUpdateFromLiffLogin(array $data): CrmLead
    {
        $data = $this->applyTrackingLinkAttribution($data);
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
            $lead = $this->findLatestPendingLineLead($data['line_user_id']);

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
            $lineLead = $this->findLatestPendingLineLead($data['line_user_id']);

            if ($lineLead && (int) $lineLead->branch_id === (int) ($data['branch_id'] ?? null)) {
                return $lineLead;
            }
        }

        if (! empty($data['lead_id'])) {
            return CrmLead::query()->where('branch_id', $data['branch_id'] ?? null)
                ->whereKey($data['lead_id'])
                ->lockForUpdate()
                ->first();
        }

        if (empty($data['line_user_id'])) {
            return CrmLead::query()->where('branch_id', $data['branch_id'] ?? null)
                ->where('phone', $data['phone'])
                ->latest()
                ->lockForUpdate()
                ->first();
        }

        return CrmLead::query()->where('branch_id', $data['branch_id'] ?? null)
            ->where('phone', $data['phone'])
            ->where(function ($query) {
                $query->whereIn('lead_status', ['line_login_pending', 'line_chatting'])
                    ->orWhere(function ($subQuery) {
                        $subQuery->whereNotNull('line_user_id')
                            ->where(function ($lineQuery) {
                                $lineQuery->whereNotNull('line_conversation_state')
                                    ->where('line_conversation_state', '!=', 'completed');
                            });
                    });
            })
            ->latest()
            ->lockForUpdate()
            ->first();
    }

    private function findLatestPendingLineLead(string $lineUserId): ?CrmLead
    {
        return CrmLead::query()
            ->where('line_user_id', $lineUserId)
            ->where(function ($query) {
                $query->whereIn('lead_status', ['line_login_pending', 'line_chatting'])
                    ->orWhere(function ($subQuery) {
                        $subQuery->whereNotNull('line_conversation_state')
                            ->where('line_conversation_state', '!=', 'completed');
                    });
            })
            ->latest()
            ->lockForUpdate()
            ->first();
    }

    private function findLatestActiveLineLead(string $lineUserId): ?CrmLead
    {
        return CrmLead::query()
            ->where('line_user_id', $lineUserId)
            ->where('is_active', true)
            ->latest()
            ->lockForUpdate()
            ->first();
    }

    private function cancelPreviousRestartLeads(string $lineUserId, string $registrationBatchId): int
    {
        return CrmLead::query()
            ->where('line_user_id', $lineUserId)
            ->where('is_active', true)
            ->whereNotIn('lead_status', $this->restartProtectedStatuses())
            ->update([
                'lead_status' => 'cancelled_by_restart',
                'is_active' => false,
                'cancelled_at' => now(),
                'superseded_at' => now(),
                'cancel_reason' => 'restart_registration',
                'superseded_by_batch_id' => $registrationBatchId,
                'updated_at' => now(),
            ]);
    }

    /**
     * These statuses represent real enrollment/won outcomes and must survive a chat restart.
     */
    private function restartProtectedStatuses(): array
    {
        return [
            'enrolled',
            'paid',
            'won',
            'closed_won',
        ];
    }

    private function makeRegistrationBatchId(?string $lineUserId): string
    {
        $prefix = $lineUserId ? 'line_' . substr(hash('sha256', $lineUserId), 0, 12) : 'lead';

        return $prefix . '_' . now()->format('YmdHis') . '_' . Str::lower(Str::random(8));
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

        if (! empty($data['lead_id'])) {
            $alwaysRefresh = array_merge($alwaysRefresh, [
                'parent_name',
                'phone',
                'preferred_contact_time',
                'student_name',
                'student_grade',
                'student_school',
                'student_age',
                'pain_point',
            ]);
        }

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

    private function copyTrackedAttributionFromLead(CrmLead $lead, array $data): array
    {
        foreach ([
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
            'tracking_link_id',
            'tracking_link_code',
            'tracking_link_name',
            'tracking_link_creator_id',
            'tracking_link_creator_name',
        ] as $field) {
            if (filled($lead->{$field})) {
                $data[$field] = $lead->{$field};
            }
        }

        return $data;
    }

    private function applyTrackingLinkAttribution(array $data): array
    {
        $trackingCode = trim((string) ($data['tracking_code'] ?? ''));
        $trackingLinkQuery = TrackingLink::withTrashed()->with('creator:id,name');

        if ($trackingCode !== '') {
            $trackingLink = $trackingLinkQuery
                ->where('tracking_code', $trackingCode)
                ->first();
        } else {
            $trackingLink = $this->findUniqueLegacyTrackingLink($trackingLinkQuery, $data);
        }

        if (! $trackingLink) {
            return $data;
        }

        return $this->applyResolvedTrackingLink($data, $trackingLink);
    }

    private function applyResolvedTrackingLink(array $data, TrackingLink $trackingLink): array
    {

        foreach (['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term'] as $field) {
            $data[$field] = $trackingLink->{$field};
        }

        $data['branch_id'] = $data['branch_id'] ?? $trackingLink->branch_id;
        if (!empty($data['branch_id']) && !\App\Support\Branch\BranchAccess::moduleEnabled((int) $data['branch_id'], 'crm')) {
            throw \Illuminate\Validation\ValidationException::withMessages(['branch_id' => 'This branch is not accepting trial registrations.']);
        }
        $data['tracking_link_id'] = $trackingLink->id;
        $data['tracking_link_code'] = $trackingLink->tracking_code;
        $data['tracking_link_name'] = $trackingLink->name;
        $data['tracking_link_creator_id'] = $trackingLink->created_by;
        $data['tracking_link_creator_name'] = $trackingLink->creator?->name
            ?: $trackingLink->created_by_name
            ?: 'System';
        $data['first_touch_source'] = $trackingLink->utm_source;

        return $data;
    }

    private function findUniqueLegacyTrackingLink($query, array $data): ?TrackingLink
    {
        foreach (['utm_source', 'utm_medium', 'utm_campaign'] as $requiredField) {
            if (blank($data[$requiredField] ?? null)) {
                return null;
            }

            $query->where($requiredField, $data[$requiredField]);
        }

        foreach (['utm_content', 'utm_term'] as $optionalField) {
            $value = $data[$optionalField] ?? null;
            $query->where(function ($subQuery) use ($optionalField, $value) {
                if (blank($value)) {
                    $subQuery->whereNull($optionalField)->orWhere($optionalField, '');
                } else {
                    $subQuery->where($optionalField, $value);
                }
            });
        }

        $matches = $query->limit(2)->get();

        return $matches->count() === 1 ? $matches->first() : null;
    }

    private function preserveTrackedAttribution(CrmLead $lead, array $incoming): array
    {
        $hasExistingCampaignAttribution = filled($lead->utm_source)
            && strtolower((string) $lead->utm_source) !== 'line';

        $incomingLooksLikeLineFallback = strtolower((string) ($incoming['utm_source'] ?? '')) === 'line'
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
