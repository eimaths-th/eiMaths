<?php

namespace App\Http\Controllers;

use App\Models\CrmLead;
use App\Models\User;
use App\Services\CrmLeadStatusService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CrmLeadController extends Controller
{
    public function index(Request $request)
    {
        $archivedStatuses = ['superseded', 'cancelled', 'cancelled_by_restart'];

        $leads = CrmLead::query()
            ->with('assignedStaff:id,name')
            ->when($request->filled('status'), fn ($query) => $query->where('lead_status', $request->status))
            ->when(! $request->filled('status'), fn ($query) => $query
                ->where('is_active', true)
                ->whereNotIn('lead_status', $archivedStatuses))
            ->when($request->filled('source'), fn ($query) => $query->where('utm_source', $request->source))
            ->when($request->filled('line_source'), fn ($query) => $query->where('line_source', $request->line_source))
            ->when($request->filled('campaign'), fn ($query) => $query->where('utm_campaign', $request->campaign))
            ->when($request->filled('created_from'), fn ($query) => $query->where('created_from', $request->created_from))
            ->when($request->filled('staff_id'), fn ($query) => $query->where('assigned_staff_id', $request->staff_id))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('created_at', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('created_at', '<=', $request->date_to))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%' . $request->search . '%';
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('parent_name', 'like', $search)
                        ->orWhere('phone', 'like', $search)
                        ->orWhere('line_id', 'like', $search)
                        ->orWhere('line_display_name', 'like', $search)
                        ->orWhere('line_user_id', 'like', $search)
                        ->orWhere('student_name', 'like', $search);
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('crm.leads.index', [
            'leads' => $leads,
            'statuses' => CrmLead::STATUSES,
            'staffUsers' => User::query()->orderBy('name')->get(['id', 'name']),
            'sources' => CrmLead::query()->whereNotNull('utm_source')->distinct()->orderBy('utm_source')->pluck('utm_source'),
            'lineSources' => CrmLead::query()->whereNotNull('line_source')->distinct()->orderBy('line_source')->pluck('line_source'),
            'campaigns' => CrmLead::query()->whereNotNull('utm_campaign')->distinct()->orderBy('utm_campaign')->pluck('utm_campaign'),
            'createdFromOptions' => CrmLead::CREATED_FROM,
        ]);
    }

    public function show(CrmLead $lead)
    {
        $lead->load(['assignedStaff:id,name', 'notes.creator:id,name', 'duplicateOf:id,parent_name,phone']);

        return view('crm.leads.show', [
            'lead' => $lead,
            'statuses' => CrmLead::STATUSES,
            'staffUsers' => User::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, CrmLead $lead, CrmLeadStatusService $statusService)
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

        $statusBefore = $lead->lead_status;
        $statusService->applyStatus($lead, $data['lead_status'], $data);

        if ($statusBefore !== $lead->lead_status) {
            $lead->notes()->create([
                'note' => 'Status changed by staff.',
                'status_before' => $statusBefore,
                'status_after' => $lead->lead_status,
                'created_by' => auth()->id(),
            ]);
        }

        return redirect()->route('crm.leads.show', $lead)->with('success', 'Lead updated successfully.');
    }

    public function storeNote(Request $request, CrmLead $lead)
    {
        $data = $request->validate([
            'note' => ['required', 'string', 'max:5000'],
            'status_after' => ['nullable', Rule::in(array_keys(CrmLead::STATUSES))],
        ]);

        $statusBefore = $lead->lead_status;

        if (! empty($data['status_after']) && $data['status_after'] !== $lead->lead_status) {
            app(CrmLeadStatusService::class)->applyStatus($lead, $data['status_after']);
        }

        $lead->notes()->create([
            'note' => $data['note'],
            'status_before' => $statusBefore,
            'status_after' => $data['status_after'] ?? $lead->lead_status,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('crm.leads.show', $lead)->with('success', 'Note saved successfully.');
    }
}
