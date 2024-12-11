<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Affiliate\Entities\AffiliateConfiguration;

class AffiliateConfigurationsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $aff_config = AffiliateConfiguration::all();
        // dd($aff_config);
        return view('affiliate.config.index', [
            'aff_config' => $aff_config
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
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

    public function update(Request $request)
    {
        // dd($request);
        $validatedData = $request->validate([
            'min_withdraw' => 'required|numeric|min:0',
            'balance_add_account_after_days' => 'required|integer|min:0',
            'commission_amount' => 'required|numeric|min:0',
        ]);

        // Update values in the database
        foreach ($validatedData as $key => $value) {
            AffiliateConfiguration::where('key', $key)
                ->update(['value' => $value]);
        }

        return response()->json(['success' => true]);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
