<?php

namespace App\Http\Controllers;

use App\Models\EiForm;
use Illuminate\Http\Request;

class EiFormController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return view('eiform.index');
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

        // dd($request->all());
        EiForm::create($request->all());
        $thankYouMessage = "ขอบคุณสำหรับข้อมูล. <br/> เราจะติดต่อกลับโดยเร็วที่สุด";

        return response()->json([
            'html' => '<div class="et-pb-contact-message">
                            <p id="msg">' . $thankYouMessage . '</p>
                       </div>'
        ]);
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
