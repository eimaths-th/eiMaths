<?php

namespace App\Http\Controllers;

use App\Models\QRCodePayMent;
use Illuminate\Http\Request;

class QrCodePaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //

        $qr = QRCodePayMent::first();
        $amount = null;
        $promptpay_number = $qr->account_numbers;

        // $qrCodeImage = generateQrCode($amount);
        // $promptpay_number = $this->maskAccountNumber($promptpay_number, 3, 4);

        // dd($qrCodeImage,$promptpay_number);
        return view('qrcode_payment.index', compact('qr'));
    }

    // public function maskAccountNumber($accountNumber, $startVisible, $endVisible)
    // {
    //     // Extract parts for masking
    //     $start = str_repeat('X', $startVisible); // Mask first 'n' characters with 'x'
    //     $middleMasked = '-X-'; // Use 'x' in the middle
    //     $end = substr($accountNumber, -$endVisible); // Get the last 'n' characters

    //     // Format with hyphens
    //     return $start . $middleMasked . $end . '-X';
    // }

   

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
    public function update(Request $request, string $id)
    {
        $qr = QRCodePayMent::find($id);
        $qr->update($request->all());
        return response()->json(['success' => 'QR Code Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
