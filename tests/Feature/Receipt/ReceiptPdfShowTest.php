<?php

namespace Tests\Feature\Receipt;

use App\Http\Controllers\ReceiptController;
use App\Models\Contact;
use App\Models\Department;
use App\Models\Order;
use App\Models\Receipt;
use App\Models\receipt_details;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReceiptPdfShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_receipt_pdf_is_streamed_without_a_public_file(): void
    {
        $department = Department::create(['code' => 'PDF', 'name' => 'PDF Branch', 'status' => 1]);
        $contact = Contact::create([
            'centre' => $department->id,
            'code' => 'PDF-STUDENT',
            'name' => 'PDF Student',
        ]);
        $order = Order::create([
            'centre' => $department->id,
            'cid' => $contact->id,
            'order_number' => 'PDF-ORDER',
            'total_price' => '100.00',
            'orther_fee' => '0.00',
        ]);
        $receipt = Receipt::create([
            'centre' => $department->id,
            'cid' => $contact->id,
            'oid' => $order->id,
            'student_name' => $contact->name,
            'receipt_number' => 'PDF-STREAM-TEST',
            'payment_date' => '2026-10-06',
            'payment' => 1,
            'total_fee' => '100.00',
        ]);
        (new receipt_details())->forceFill([
            'receipt_id' => $receipt->id,
            'des' => 'Tuition',
            'unit' => 'item',
            'quantity' => 1,
            'price' => '100.00',
            'discount' => '0.00',
            'tax' => 1,
        ])->save();

        $publicPath = public_path('file_store/PDF-STREAM-TEST.pdf');
        $this->assertFileDoesNotExist($publicPath);

        $response = app(ReceiptController::class)->show($receipt->id, 1);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
        $this->assertFileDoesNotExist($publicPath);
    }
}
