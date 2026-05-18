<?php

namespace App\Http\Controllers;

use App\Models\MarketingCampaign;
use Illuminate\Http\Request;

class MarketingCampaignController extends Controller
{
    public function index()
    {
        return view('marketing.campaigns.index');
    }

    public function create()
    {
        return view('marketing.campaigns.create');
    }

    public function store(Request $request)
    {
        //
    }

    public function show(MarketingCampaign $campaign)
    {
        return view('marketing.campaigns.show', compact('campaign'));
    }

    public function edit(MarketingCampaign $campaign)
    {
        return view('marketing.campaigns.edit', compact('campaign'));
    }

    public function update(Request $request, MarketingCampaign $campaign)
    {
        //
    }

    public function destroy(MarketingCampaign $campaign)
    {
        //
    }
}
