<?php

namespace App\Http\Controllers;

use App\Models\FooterSetting;
use Illuminate\Http\Request;

class FooterSettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $footer = FooterSetting::first();
        return view(
            'footer_setting.index',
            compact('footer')
        );
    }

    /**
     * Show the form for creating a new resource.
     */

    public function createOrUpdate(Request $request)
    {
        // dd($request->all());
        $footer = FooterSetting::first();
        if ($footer) {
            $footer->update($request->all());
        } else {
            FooterSetting::create($request->all());
        }
        return redirect()->route('footer-setting.index');
    }
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
