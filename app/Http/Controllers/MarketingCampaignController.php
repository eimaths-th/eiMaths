<?php

namespace App\Http\Controllers;

use App\Models\MarketingCampaign;
use Illuminate\Http\Request;

class MarketingCampaignController extends Controller
{
    public function index()
    {
        $campaigns = MarketingCampaign::latest()->paginate(20);

        return view('marketing.campaigns.index', compact('campaigns'));
    }

    public function create()
    {
        return view('marketing.campaigns.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'image_url' => 'nullable|url',

            'segment_level' => 'nullable|string|max:50',
            'segment_interest' => 'nullable|string|max:100',
            'segment_status' => 'nullable|string|max:100',

            'status' => 'required|in:draft,scheduled,cancelled',
            'schedule_at' => 'required_if:status,scheduled|nullable|date',
        ]);

        $data['created_by'] = auth()->id();

        MarketingCampaign::create($data);

        return redirect()
            ->route('marketing.campaigns.index')
            ->with('success', 'สร้าง Campaign สำเร็จ');
    }

    public function edit(MarketingCampaign $campaign)
    {
        return view('marketing.campaigns.edit', compact('campaign'));
    }

    public function update(Request $request, MarketingCampaign $campaign)
    {
        if (in_array($campaign->status, ['processing', 'completed'])) {
            return back()->with('error', 'ไม่สามารถแก้ไข Campaign ที่กำลังส่งหรือส่งเสร็จแล้วได้');
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'image_url' => 'nullable|url',

            'segment_level' => 'nullable|string|max:50',
            'segment_interest' => 'nullable|string|max:100',
            'segment_status' => 'nullable|string|max:100',

            'status' => 'required|in:draft,scheduled,cancelled',
            'schedule_at' => 'required_if:status,scheduled|nullable|date',
        ]);

        $campaign->update($data);

        return redirect()
            ->route('marketing.campaigns.index')
            ->with('success', 'อัปเดต Campaign สำเร็จ');
    }

    public function destroy(MarketingCampaign $campaign)
    {
        if (in_array($campaign->status, ['processing', 'completed'])) {
            return back()->with('error', 'ไม่สามารถลบ Campaign ที่กำลังส่งหรือส่งเสร็จแล้วได้');
        }

        $campaign->delete();

        return redirect()
            ->route('marketing.campaigns.index')
            ->with('success', 'ลบ Campaign สำเร็จ');
    }

    public function pendingCampaigns()
    {
        $campaigns = MarketingCampaign::query()
            ->where('status', 'scheduled')
            ->where('schedule_at', '<=', now())
            ->orderBy('schedule_at')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'count' => $campaigns->count(),
            'campaigns' => $campaigns,
        ]);
    }

    public function markAsProcessing(MarketingCampaign $campaign)
    {
        $campaign->update([
            'status' => 'processing',
        ]);

        return response()->json([
            'success' => true,
        ]);
    }

    public function markAsSent(MarketingCampaign $campaign)
    {
        $campaign->update([
            'status' => 'completed',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Campaign marked as completed',
        ]);
    }
}
