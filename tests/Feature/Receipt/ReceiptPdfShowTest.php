<?php

namespace Tests\Feature\Receipt;

use App\Services\ReceiptPdfRenderer;
use App\Models\Contact;
use App\Models\Department;
use App\Models\Order;
use App\Models\Receipt;
use App\Models\receipt_details;
use App\Models\FileStore;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ReceiptPdfShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_receipt_pdf_is_streamed_without_a_public_file(): void
    {
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        Http::preventStrayRequests();
        Mail::fake();
        Notification::fake();
        // Rendering must not depend on the application URL being reachable.
        URL::forceRootUrl('http://127.0.0.1:9');
        $department = Department::create(['code' => 'PDF', 'name' => 'PDF Branch', 'status' => 1]);
        $contact = Contact::create([
            'centre' => $department->id,
            'code' => 'PDF-STUDENT',
            'name' => 'นักเรียน ทดสอบ PDF Student',
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
            'des' => 'ค่าเรียน Tuition และหนังสือ G11-G20',
            'unit' => 'item',
            'quantity' => 1,
            'price' => '100.00',
            'discount' => '0.00',
            'tax' => 1,
        ])->save();

        $publicPath = public_path('file_store/PDF-STREAM-TEST.pdf');
        $this->assertFileDoesNotExist($publicPath);
        $public_font_cache = public_path('fonts/installed-fonts.json');
        $public_font_cache_hash = is_file($public_font_cache) ? hash_file('sha256', $public_font_cache) : null;

        $actor = User::factory()->create(['department_id' => $department->id, 'disable' => 0, 'password' => bcrypt('test-password')]);
        $actor->givePermissionTo(Permission::findOrCreate('receipt-show', 'web'));
        $filename = 'receipt-pdf-test-'.bin2hex(random_bytes(6)).'.png';
        $attachment_path = public_path('file_store/'.$filename);
        copy(public_path('images/check.png'), $attachment_path);
        foreach ([$filename, 'missing-'.$filename, '../../.env'] as $attachment) {
            FileStore::forceCreate(['module' => 'orders', 'module_id' => $order->id, 'filename' => $attachment]);
        }
        $renderer = new class extends ReceiptPdfRenderer {
            public string $html = '';
            public function Render(string $html): string
            {
                $this->html = $html;
                return parent::Render($html);
            }
        };
        $this->app->instance(ReceiptPdfRenderer::class, $renderer);
        try {
            $started = microtime(true);
            $response = $this->actingAs($actor)->get(route('receipts.show', [$receipt->id, 1]));
            $elapsed = microtime(true) - $started;
        } finally {
            unlink($attachment_path);
            URL::forceRootUrl(null);
        }

        $response->assertOk();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
        $this->assertStringContainsString('/Subtype /Image', $response->getContent());
        $this->assertStringContainsString('ReceiptSarabun', $renderer->html);
        // Verify the embedded fonts, not just the CSS family alias.
        $this->assertStringContainsString('+Sarabun-Regular', $response->getContent());
        $this->assertStringContainsString('+Sarabun-Bold', $response->getContent());
        $this->assertStringNotContainsString('THSarabunNew', $response->getContent());
        $this->assertStringContainsString($filename, $renderer->html);
        $this->assertStringNotContainsString('missing-'.$filename, $renderer->html);
        $this->assertStringNotContainsString('../../.env', $renderer->html);
        $this->assertStringNotContainsString('fonts.googleapis.com', $renderer->html);
        $this->assertDoesNotMatchRegularExpression('/(?:src|href)=["\']https?:/i', $renderer->html);
        $this->assertLessThan(20, $elapsed, 'Receipt rendering must finish below the web request timeout.');
        $this->assertFileDoesNotExist($publicPath);
        $this->assertSame($public_font_cache_hash,
            is_file($public_font_cache) ? hash_file('sha256', $public_font_cache) : null,
            'Rendering must never write the deployment-owned public font cache.');
        $this->assertFileExists(storage_path('framework/cache/receipt-pdf-fonts/installed-fonts.json'));
        // Optional local artifact for visual QA; normal test runs do not export files.
        if ($output = getenv('RECEIPT_PDF_QA_OUTPUT')) {
            file_put_contents($output, $response->getContent());
        }
        fwrite(STDERR, sprintf("\nReal receipt PDF: %.2fs, %d bytes\n", $elapsed, strlen($response->getContent())));
    }
}
