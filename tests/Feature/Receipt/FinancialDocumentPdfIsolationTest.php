<?php

namespace Tests\Feature\Receipt;

use App\Models\Contact;
use App\Models\Department;
use App\Models\Order;
use App\Models\Receipt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Mockery;
use PDF;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class FinancialDocumentPdfIsolationTest extends TestCase
{
    use RefreshDatabase;

    private ?string $temporary_public_path = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        Http::preventStrayRequests();
        Mail::fake();
        Notification::fake();
        $this->temporary_public_path = storage_path('framework/testing/pdf-isolation-'.bin2hex(random_bytes(8)));
        File::makeDirectory($this->temporary_public_path.'/file_store', 0755, true);
        $this->app->usePublicPath($this->temporary_public_path);
    }

    protected function tearDown(): void
    {
        if ($this->temporary_public_path !== null) {
            $allowed_path = realpath(storage_path('framework/testing'));
            $resolved_path = realpath($this->temporary_public_path);
            if ($allowed_path !== false && $resolved_path !== false
                && str_starts_with($resolved_path, $allowed_path.DIRECTORY_SEPARATOR)
                && str_starts_with(basename($resolved_path), 'pdf-isolation-')) {
                File::deleteDirectory($resolved_path);
            }
        }
        parent::tearDown();
    }

    #[Test]
    #[DataProvider('ReceiptPdfNumberCases')]
    public function ReceiptPdfDoesNotReplaceAnExistingDocumentWithTheSameNumber(string $receipt_number): void
    {
        $this->withoutExceptionHandling();
        $branch = Department::create(['name' => 'Branch A', 'code' => 'AA', 'status' => 1]);
        $actor = User::factory()->create(['department_id' => $branch->id, 'disable' => 0, 'password' => bcrypt('test-password')]);
        $actor->givePermissionTo(Permission::findOrCreate('receipt-show', 'web'));
        $student = Contact::create(['centre' => $branch->id, 'code' => 'AA0001', 'name' => 'Receipt Student']);
        $order = Order::create(['centre' => $branch->id, 'cid' => $student->id, 'order_number' => 'ORDER-1', 'total_price' => 100, 'orther_fee' => 0]);
        $receipt = Receipt::create([
            'centre' => $branch->id, 'cid' => $student->id, 'oid' => $order->id,
            'receipt_number' => $receipt_number, 'student_name' => 'Receipt Student',
            'payment_date' => '2026-10-06', 'total_fee' => 100,
        ]);
        $shared_path = public_path('file_store/'.$receipt_number.'.pdf');
        file_put_contents($shared_path, 'Existing unrelated PDF');

        // Only replace the renderer: template data, authorization, response and filesystem remain real.
        $pdf_renderer = Mockery::mock(\App\Services\ReceiptPdfRenderer::class)->makePartial();
        $pdf_renderer->shouldReceive('Render')->once()->andReturn('%PDF-Controlled receipt bytes');
        $this->app->instance(\App\Services\ReceiptPdfRenderer::class, $pdf_renderer);

        $response = $this->actingAs($actor)->get(route('receipts.show', [$receipt->id, 1]));
        $response->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $content_disposition = $response->headers->get('Content-Disposition');
        $this->assertStringStartsWith('inline;', $content_disposition);
        if ($receipt_number === 'DUPLICATE-RECEIPT') {
            $this->assertStringContainsString('DUPLICATE-RECEIPT.pdf', $content_disposition);
        } else {
            $this->assertStringContainsString("filename*=utf-8''".rawurlencode($receipt_number.'.pdf'), $content_disposition);
        }
        $this->assertSame('%PDF-Controlled receipt bytes', $response->getContent());
        $this->assertSame('Existing unrelated PDF', file_get_contents($shared_path));
    }

    #[Test]
    #[DataProvider('OrderPdfNumberCases')]
    public function OrderPdfRendersTheOrderDocumentWithItsExistingLineItems(string $order_number): void
    {
        $branch = Department::create(['name' => 'Branch A', 'code' => 'AA', 'status' => 1]);
        $actor = User::factory()->create(['department_id' => $branch->id, 'disable' => 0, 'password' => bcrypt('test-password')]);
        $actor->givePermissionTo(Permission::findOrCreate('order-show', 'web'));
        $student = Contact::create(['centre' => $branch->id, 'code' => 'AA0001', 'name' => 'Order Student']);
        $order = Order::create(['centre' => $branch->id, 'cid' => $student->id, 'order_number' => $order_number, 'total_price' => 300]);
        $book = \App\Models\bookuse::forceCreate(['name' => 'Order course item']);
        \App\Models\OrderDetail::create(['order_id' => $order->id, 'product_id' => $book->id, 'pname' => 'Order course item', 'quantity' => 3, 'price' => 100]);
        $shared_path = public_path('file_store/'.$order_number.'.pdf');
        file_put_contents($shared_path, 'Existing unrelated order PDF');

        $pdf_renderer = Mockery::mock(\Barryvdh\DomPDF\PDF::class);
        $pdf_renderer->shouldReceive('save')->andReturnUsing(function (string $path) use ($pdf_renderer) {
            file_put_contents($path, '%PDF-Controlled order bytes');
            return $pdf_renderer;
        });
        $pdf_renderer->shouldReceive('output')->andReturn('%PDF-Controlled order bytes');
        PDF::shouldReceive('loadHTML')->andReturnUsing(function (string $html_contents) use ($pdf_renderer) {
            $this->assertStringContainsString('Order Student', $html_contents);
            $this->assertStringContainsString('Order course item', $html_contents);
            return $pdf_renderer;
        });

        $response = $this->actingAs($actor)->get(route('orsders.show', [$order->id, 1]));
        $response->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $content_disposition = $response->headers->get('Content-Disposition');
        $this->assertStringStartsWith('attachment;', $content_disposition);
        if ($order_number === 'DUPLICATE-ORDER') {
            $response->assertDownload('DUPLICATE-ORDER.pdf');
        } else {
            $this->assertStringContainsString("filename*=utf-8''".rawurlencode($order_number.'.pdf'), $content_disposition);
        }
        $this->assertSame('Existing unrelated order PDF', file_get_contents($shared_path));
        $this->assertSame('%PDF-Controlled order bytes', $response->getContent());
    }

    public static function OrderPdfNumberCases(): array
    {
        return [['DUPLICATE-ORDER'], ['ใบสั่งซื้อ-001']];
    }

    public static function ReceiptPdfNumberCases(): array
    {
        return [['DUPLICATE-RECEIPT'], ['ใบเสร็จ-001']];
    }
}
