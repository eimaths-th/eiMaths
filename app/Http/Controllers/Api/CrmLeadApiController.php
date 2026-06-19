<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CrmLead;
use App\Services\CrmLeadService;
use App\Services\CrmLeadStatusService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CrmLeadApiController extends Controller
{
    public function store(Request $request, CrmLeadService $leadService)
    {
        $this->authorizeIntegration($request);
        $request->merge([
            'phone' => preg_replace('/[^0-9+]/', '', (string) $request->input('phone')),
        ]);
        $data = $request->validate($this->leadValidationRules());

        if (! empty($data['students'])) {
            $leads = $leadService->createManyFromRegistration($data);
            $leadIds = collect($leads)->pluck('id')->values();
            $actions = collect($leads)->map(fn ($lead) => $lead->getAttribute('merge_action') ?: 'created')->values();
            $primaryLead = $leads[0];

            return response()->json([
                'success' => true,
                'message' => 'Lead registrations saved successfully.',
                'action' => $actions->contains('created')
                    ? ($actions->contains('merged') || $actions->contains('updated_by_lead_id') ? 'updated_and_created_many' : 'created_many')
                    : 'updated_many',
                'lead_id' => $primaryLead->id,
                'id' => $primaryLead->id,
                'lead_ids' => $leadIds,
                'count' => count($leads),
                'registration_batch_id' => $primaryLead->registration_batch_id,
                'status' => $primaryLead->lead_status,
                'is_duplicate' => false,
                'was_merged' => $actions->contains('merged') || $actions->contains('updated_by_lead_id'),
                'leads' => collect($leads)->map(fn ($lead) => [
                    'id' => $lead->id,
                    'parent_name' => $lead->parent_name,
                    'phone' => $lead->phone,
                    'student_name' => $lead->student_name,
                    'student_grade' => $lead->student_grade,
                    'lead_status' => $lead->lead_status,
                    'registration_batch_id' => $lead->registration_batch_id,
                    'created_from' => $lead->created_from,
                    'line_source' => $lead->line_source,
                    'utm_source' => $lead->utm_source,
                    'utm_campaign' => $lead->utm_campaign,
                    'action' => $lead->getAttribute('merge_action') ?: 'created',
                ])->values(),
                'lead_detail_url' => route('admin.crm.leads.show', $primaryLead),
                'lead_list_url' => route('admin.crm.leads.index'),
            ], $actions->contains('created') ? 201 : 200);
        }

        $lead = $leadService->createFromRegistration($data);
        $action = $lead->getAttribute('merge_action') ?: 'created';

        return response()->json([
            'success' => true,
            'message' => 'Lead registration saved successfully.',
            'action' => $action,
            'lead_id' => $lead->id,
            'id' => $lead->id,
            'status' => $lead->lead_status,
            'is_duplicate' => false,
            'was_merged' => (bool) $lead->getAttribute('was_merged'),
            'lead' => [
                'id' => $lead->id,
                'parent_name' => $lead->parent_name,
                'phone' => $lead->phone,
                'student_name' => $lead->student_name,
                'student_grade' => $lead->student_grade,
                'lead_status' => $lead->lead_status,
                'created_from' => $lead->created_from,
                'line_source' => $lead->line_source,
                'utm_source' => $lead->utm_source,
                'utm_campaign' => $lead->utm_campaign,
            ],
            'lead_detail_url' => route('admin.crm.leads.show', $lead),
        ], $action === 'merged' ? 200 : 201);
    }

    public function storeLiffLead(Request $request, CrmLeadService $leadService)
    {
        $this->authorizeIntegration($request);

        $data = $request->validate([
            'line_user_id' => ['required', 'string', 'max:100'],
            'line_display_name' => ['nullable', 'string', 'max:255'],
            'line_picture_url' => ['nullable', 'url', 'max:2000'],
            'utm_source' => ['nullable', 'string', 'max:255'],
            'utm_medium' => ['nullable', 'string', 'max:255'],
            'utm_campaign' => ['nullable', 'string', 'max:255'],
            'utm_content' => ['nullable', 'string', 'max:255'],
            'utm_term' => ['nullable', 'string', 'max:255'],
            'referrer' => ['nullable', 'string', 'max:2000'],
            'landing_page' => ['nullable', 'string', 'max:2000'],
            'first_touch_source' => ['nullable', 'string', 'max:255'],
            'last_touch_source' => ['nullable', 'string', 'max:255'],
        ]);

        $lead = $leadService->createOrUpdateFromLiffLogin($data);
        $action = $lead->getAttribute('merge_action') ?: 'created';

        return response()->json([
            'success' => true,
            'lead_id' => $lead->id,
            'id' => $lead->id,
            'action' => $action === 'merged' ? 'updated_pending' : 'created',
            'line_user_id' => $lead->line_user_id,
            'status' => $lead->lead_status,
        ], $action === 'merged' ? 200 : 201);
    }

    public function followUpDue(Request $request)
    {
        $this->authorizeIntegration($request);
        $leads = CrmLead::query()
            ->with('assignedStaff:id,name')
            ->where(function ($query) {
                $query->whereIn('lead_status', ['new_lead', 'waiting_for_contact'])
                    ->where('created_at', '<=', now()->subHour());
            })
            ->orWhere(function ($query) {
                $query->where('lead_status', 'contacted')
                    ->whereNull('trial_scheduled_at')
                    ->where('updated_at', '<=', now()->subDay());
            })
            ->orWhere(function ($query) {
                $query->whereNotNull('trial_scheduled_at')
                    ->whereBetween('trial_scheduled_at', [now(), now()->addDay()]);
            })
            ->orWhere(function ($query) {
                $query->where('lead_status', 'trial_completed')
                    ->where(function ($subQuery) {
                        $subQuery->whereNull('next_follow_up_at')
                            ->orWhere('next_follow_up_at', '<=', now());
                    });
            })
            ->orderByRaw('COALESCE(next_follow_up_at, created_at) asc')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'count' => $leads->count(),
            'leads' => $leads,
        ]);
    }

    public function updateStatus(Request $request, CrmLead $lead, CrmLeadStatusService $statusService)
    {
        $this->authorizeIntegration($request);
        $data = $request->validate([
            'lead_status' => ['required', Rule::in(array_keys(CrmLead::STATUSES))],
            'assigned_staff_id' => ['nullable', 'exists:users,id'],
            'last_contact_at' => ['nullable', 'date'],
            'next_follow_up_at' => ['nullable', 'date'],
            'trial_scheduled_at' => ['nullable', 'date'],
            'trial_completed_at' => ['nullable', 'date'],
            'enrolled_at' => ['nullable', 'date'],
        ]);

        $statusService->applyStatus($lead, $data['lead_status'], $data);

        return response()->json([
            'success' => true,
            'lead' => $lead->fresh('assignedStaff:id,name'),
        ]);
    }

    public function addNote(Request $request, CrmLead $lead)
    {
        $this->authorizeIntegration($request);
        $data = $request->validate([
            'note' => ['required', 'string', 'max:5000'],
            'status_after' => ['nullable', Rule::in(array_keys(CrmLead::STATUSES))],
            'created_by' => ['nullable', 'exists:users,id'],
        ]);

        $statusBefore = $lead->lead_status;

        if (! empty($data['status_after']) && $data['status_after'] !== $lead->lead_status) {
            app(CrmLeadStatusService::class)->applyStatus($lead, $data['status_after']);
        }

        $note = $lead->notes()->create([
            'note' => $data['note'],
            'status_before' => $statusBefore,
            'status_after' => $data['status_after'] ?? $lead->lead_status,
            'created_by' => $data['created_by'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'note' => $note,
        ], 201);
    }

    private function leadValidationRules(): array
    {
        return [
            'parent_name' => ['required', 'string', 'max:255'],
            'lead_id' => ['nullable', 'integer', 'exists:crm_leads,id'],
            'phone' => ['required', 'string', 'regex:/^[0-9+]{8,15}$/'],
            'line_id' => ['nullable', 'string', 'max:100'],
            'line_user_id' => ['nullable', 'string', 'max:100'],
            'line_display_name' => ['nullable', 'string', 'max:255'],
            'line_picture_url' => ['nullable', 'url', 'max:2000'],
            'line_follow_status' => ['nullable', Rule::in(['followed', 'unfollowed', 'unknown'])],
            'line_followed_at' => ['nullable', 'date'],
            'line_last_message_at' => ['nullable', 'date'],
            'line_conversation_state' => ['nullable', 'string', 'max:100'],
            'line_ref' => ['nullable', 'string', 'max:255'],
            'line_source' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'preferred_contact_time' => ['nullable', 'string', 'max:255'],
            'is_restart_flow' => ['nullable', 'boolean'],
            'registration_batch_id' => ['nullable', 'string', 'max:100'],
            'student_count' => ['nullable', 'integer', 'min:1', 'max:4'],
            'students' => ['nullable', 'array', 'min:1', 'max:4'],
            'students.*.lead_id' => ['nullable', 'integer', 'exists:crm_leads,id'],
            'students.*.student_name' => ['required_with:students', 'string', 'max:255'],
            'students.*.student_grade' => ['required_with:students', 'string', 'max:100'],
            'students.*.student_school' => ['nullable', 'string', 'max:255'],
            'students.*.student_age' => ['nullable', 'integer', 'min:1', 'max:30'],
            'students.*.pain_point' => ['nullable', 'string', 'max:3000'],
            'student_name' => ['required_without:students', 'string', 'max:255'],
            'student_grade' => ['required_without:students', 'string', 'max:100'],
            'student_school' => ['nullable', 'string', 'max:255'],
            'student_age' => ['nullable', 'integer', 'min:1', 'max:30'],
            'pain_point' => ['nullable', 'string', 'max:3000'],
            'interest' => ['nullable', 'string', 'max:255'],
            'utm_source' => ['nullable', 'string', 'max:255'],
            'utm_medium' => ['nullable', 'string', 'max:255'],
            'utm_campaign' => ['nullable', 'string', 'max:255'],
            'utm_content' => ['nullable', 'string', 'max:255'],
            'utm_term' => ['nullable', 'string', 'max:255'],
            'fbclid' => ['nullable', 'string', 'max:500'],
            'gclid' => ['nullable', 'string', 'max:500'],
            'ttclid' => ['nullable', 'string', 'max:500'],
            'referrer' => ['nullable', 'string', 'max:2000'],
            'landing_page' => ['nullable', 'string', 'max:2000'],
            'first_touch_source' => ['nullable', 'string', 'max:255'],
            'last_touch_source' => ['nullable', 'string', 'max:255'],
            'created_from' => ['nullable', Rule::in(array_keys(CrmLead::CREATED_FROM))],
            'lead_status' => ['nullable', Rule::in(array_keys(CrmLead::STATUSES))],
            'assessment_answers' => ['nullable', 'array'],
            'assessment_level' => ['nullable', 'string', 'max:100'],
            'assessment_note' => ['nullable', 'string', 'max:3000'],
            'answers' => ['nullable', 'array'],
            'test_score' => ['nullable', 'integer'],
            'total_questions' => ['nullable', 'integer'],
            'weak_topics' => ['nullable', 'array'],
            'test_duration_seconds' => ['nullable', 'integer'],
        ];
    }

    private function authorizeIntegration(Request $request): void
    {
        $configuredToken = (string) config('services.crm_integrations.token');
        $providedToken = (string) ($request->bearerToken() ?: $request->header('X-EIMATHS-TOKEN'));

        abort_unless(
            $configuredToken !== ''
                && $providedToken !== ''
                && hash_equals($configuredToken, $providedToken),
            401,
            'Invalid integration token.'
        );
    }
}
