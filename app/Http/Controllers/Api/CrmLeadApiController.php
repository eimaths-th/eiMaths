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
        $data = $request->validate($this->leadValidationRules());

        $lead = $leadService->createFromRegistration($data);

        return response()->json([
            'success' => true,
            'message' => 'Lead registration saved successfully.',
            'lead_id' => $lead->id,
            'status' => $lead->lead_status,
            'is_duplicate' => ! empty($lead->duplicate_of_lead_id),
        ], 201);
    }

    public function followUpDue()
    {
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
            'phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+()\\-\\s]{8,30}$/'],
            'line_id' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'preferred_contact_time' => ['nullable', 'string', 'max:255'],
            'student_name' => ['required', 'string', 'max:255'],
            'student_grade' => ['required', 'string', 'max:100'],
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
            'created_from' => ['nullable', 'string', 'max:100'],
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
}
