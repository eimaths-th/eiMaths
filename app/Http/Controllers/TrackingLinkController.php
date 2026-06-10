<?php

namespace App\Http\Controllers;

use App\Models\TrackingLink;
use Illuminate\Http\Request;

class TrackingLinkController extends Controller
{
    public function index()
    {
        return view('tracking-links.index', [
            'baseUrl' => config('tracking.free_trial_base_url'),
            'links' => TrackingLink::query()
                ->with('creator:id,name')
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

        $query = array_filter([
            'utm_source' => $data['utm_source'],
            'utm_medium' => $data['utm_medium'],
            'utm_campaign' => $data['utm_campaign'],
            'utm_content' => $data['utm_content'],
            'utm_term' => $data['utm_term'],
        ], fn ($value) => $value !== null && $value !== '');

        $separator = str_contains($data['base_url'], '?') ? '&' : '?';
        $data['generated_url'] = rtrim($data['base_url'], '?&')
            . $separator
            . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
        $data['created_by'] = auth()->id();

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

    private function normalizeTag(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        return strtolower(preg_replace('/[^a-zA-Z0-9ก-๙._-]+/u', '_', trim($value)));
    }
}
