<?php

namespace App\Http\Controllers;

use App\Models\MarketingCampaign;
use Illuminate\Http\Request;

class MarketingCampaignController extends Controller
{
    public function index()
    {
        $campaigns = MarketingCampaign::orderBy('created_at', 'desc')->get();
        return view('marketing_campaigns.index', compact('campaigns'));
    }

    public function create()
    {
        return view('marketing_campaigns.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'image_url' => 'nullable|url',
            'segment_level' => 'nullable|string',
            'segment_interest' => 'nullable|string',
            'segment_status' => 'nullable|string',
            'schedule_at' => 'nullable|date',
            'status' => 'required|in:draft,scheduled,processing,completed,cancelled',
        ]);

        $validated['created_by'] = auth()->id();

        MarketingCampaign::create($validated);

        return redirect()->route('marketing-campaigns.index')->with('success', 'Campaign created successfully.');
    }

    public function edit(MarketingCampaign $marketingCampaign)
    {
        return view('marketing_campaigns.edit', compact('marketingCampaign'));
    }

    public function update(Request $request, MarketingCampaign $marketingCampaign)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'image_url' => 'nullable|url',
            'segment_level' => 'nullable|string',
            'segment_interest' => 'nullable|string',
            'segment_status' => 'nullable|string',
            'schedule_at' => 'nullable|date',
            'status' => 'required|in:draft,scheduled,processing,completed,cancelled',
        ]);

        $marketingCampaign->update($validated);

        return redirect()->route('marketing-campaigns.index')->with('success', 'Campaign updated successfully.');
    }

    public function destroy(MarketingCampaign $marketingCampaign)
    {
        $marketingCampaign->delete();
        return redirect()->route('marketing-campaigns.index')->with('success', 'Campaign deleted successfully.');
    }
}
