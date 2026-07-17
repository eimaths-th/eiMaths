<?php

namespace App\Http\Controllers;

use App\Models\CrmLead;
use App\Models\TrackingLink;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TrackingLinkController extends Controller
{
    public function index()
    {
        return view('tracking-links.index', [
            'baseUrl' => config('tracking.free_trial_base_url'),
            'links' => TrackingLink::query()
                ->with('creator:id,name')
                ->withCount('leads')
                ->latest()
                ->paginate(20),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'base_url' => ['required', 'url', 'max:2000'],
            'utm_source' => ['required', 'string', 'max:100'],
            'utm_medium' => ['required', 'string', 'max:100'],
            'utm_campaign' => ['required', 'string', 'max:255'],
            'utm_content' => ['nullable', 'string', 'max:255'],
            'utm_term' => ['nullable', 'string', 'max:255'],
        ]);

        foreach (['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term'] as $field) {
            $data[$field] = $this->normalizeTag($data[$field] ?? null);
        }

        $data['tracking_code'] = Str::lower(Str::random(32));
        $query = array_filter([
            'utm_source' => $data['utm_source'],
            'utm_medium' => $data['utm_medium'],
            'utm_campaign' => $data['utm_campaign'],
            'utm_content' => $data['utm_content'],
            'utm_term' => $data['utm_term'],
            'tracking_code' => $data['tracking_code'],
        ], fn ($value) => $value !== null && $value !== '');

        $data['generated_url'] = $this->buildGeneratedUrl($data['base_url'], $query);
        $data['created_by'] = auth()->id();
        $data['created_by_name'] = auth()->user()?->name;

        $trackingLink = TrackingLink::create($data);

        return redirect()
            ->route('tracking-links.index')
            ->with('success', 'Tracking link created successfully.')
            ->with('generated_url', $trackingLink->generated_url);
    }

    public function destroy(TrackingLink $trackingLink)
    {
        $trackingLink->delete();

        return redirect()
            ->route('tracking-links.index')
            ->with('success', 'Tracking link deleted successfully.');
    }

    public function report(Request $request)
    {
        $data = $request->validate([
            'creator_id' => ['nullable', 'integer', 'exists:users,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $creatorId = $data['creator_id'] ?? null;
        $dateFrom = $data['date_from'] ?? null;
        $dateTo = $data['date_to'] ?? null;
        $applyLeadFilters = static function ($query) use ($creatorId, $dateFrom, $dateTo) {
            $query
                ->when($creatorId, fn ($leadQuery) => $leadQuery->where('tracking_link_creator_id', $creatorId))
                ->when($dateFrom, fn ($leadQuery) => $leadQuery->whereDate('created_at', '>=', $dateFrom))
                ->when($dateTo, fn ($leadQuery) => $leadQuery->whereDate('created_at', '<=', $dateTo));
        };

        $links = TrackingLink::query()
            ->withTrashed()
            ->with('creator:id,name')
            ->when($creatorId, fn ($query) => $query->where('created_by', $creatorId))
            ->withCount(['leads as leads_count' => $applyLeadFilters])
            ->withCount(['leads as active_leads_count' => function ($query) use ($applyLeadFilters) {
                $applyLeadFilters($query);
                $query->where('is_active', true);
            }])
            ->withCount(['leads as enrolled_leads_count' => function ($query) use ($applyLeadFilters) {
                $applyLeadFilters($query);
                $query->where('lead_status', 'enrolled');
            }])
            ->withMax(['leads as latest_lead_at' => $applyLeadFilters], 'created_at')
            ->orderByDesc('leads_count')
            ->latest('id')
            ->paginate(30)
            ->withQueryString();

        $allLeads = CrmLead::query()
            ->when($dateFrom, fn ($query) => $query->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->whereDate('created_at', '<=', $dateTo));
        $allTrackedLeads = CrmLead::query()
            ->whereNotNull('tracking_link_id')
            ->when($dateFrom, fn ($query) => $query->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->whereDate('created_at', '<=', $dateTo));
        $trackedLeads = CrmLead::query()->whereNotNull('tracking_link_id');
        $applyLeadFilters($trackedLeads);

        $totalLeads = (clone $allLeads)->count();
        $totalTrackedLeads = (clone $trackedLeads)->count();

        return view('tracking-links.report', [
            'links' => $links,
            'creators' => User::query()
                ->whereIn('id', TrackingLink::withTrashed()->whereNotNull('created_by')->select('created_by'))
                ->orderBy('name')
                ->get(['id', 'name']),
            'summary' => [
                'links' => TrackingLink::withTrashed()
                    ->when($creatorId, fn ($query) => $query->where('created_by', $creatorId))
                    ->count(),
                'tracked_leads' => $totalTrackedLeads,
                'active_leads' => (clone $trackedLeads)->where('is_active', true)->count(),
                'enrolled_leads' => (clone $trackedLeads)->where('lead_status', 'enrolled')->count(),
                'attribution_rate' => $totalLeads > 0
                    ? round(((clone $allTrackedLeads)->count() / $totalLeads) * 100, 1)
                    : 0,
            ],
        ]);
    }

    private function buildGeneratedUrl(string $baseUrl, array $query): string
    {
        $separator = str_contains($baseUrl, '?') ? '&' : '?';

        return rtrim($baseUrl, '?&')
            . $separator
            . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
    }

    private function normalizeTag(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        return strtolower(preg_replace('/[^a-zA-Z0-9ก-๙._-]+/u', '_', trim($value)));
    }
}
