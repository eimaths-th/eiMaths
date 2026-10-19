<?php

namespace Tests\Feature\Receipt;

use App\Models\Contact;
use App\Models\Department;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Receipt;
use App\Models\User;
use App\Models\bookuse;
use App\Models\receipt_details;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Mockery;
use PDF;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class FinancialHistoricalStudentPrintTest extends TestCase
{
    use RefreshDatabase;

    private Department $branch_a;
    private Department $branch_b;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        Http::preventStrayRequests();
        Mail::fake();
        Notification::fake();
        Department::create(['id' => 1, 'name' => 'HQ', 'code' => 'HQ', 'status' => 1]);
        $this->branch_a = Department::create(['name' => 'Branch A', 'code' => 'AA', 'status' => 1]);
        $this->branch_b = Department::create(['name' => 'Branch B', 'code' => 'BB', 'status' => 1]);
    }

    #[Test]
    #[DataProvider('DocumentFormats')]
    public function DeletedStudentReceiptRetainsItsIssuedIdentityWhenPrinted(int $print_format): void
    {
        [$student, $order, $receipt] = $this->CreateHistoricalDocuments();
        DB::table('contacts')->where('id', $student->id)->delete();
        $actor = $this->Actor($this->branch_a);
        $this->ExpectPdfRenderer($print_format, ['Issued Student', 'AA-ISSUED']);

        $response = $this->actingAs($actor)->get(route('receipts.show', [$receipt->id, $print_format]));
        $response->assertOk();
        if ($print_format === 0) {
            $this->assertStringContainsString('Issued Student', $response->json('html'));
            $this->assertStringContainsString('AA-ISSUED', $response->json('html'));
            $this->assertStringContainsString('100.00', $response->json('html'));
        } else {
            $response->assertHeader('Content-Type', 'application/pdf');
            $this->assertSame('%PDF-Historical document', $response->getContent());
        }
        $this->assertSame(100, (int) $receipt->fresh()->total_fee);
        $this->assertSame(300, (int) $order->fresh()->total_price);
    }

    #[Test]
    #[DataProvider('DocumentFormats')]
    public function DeletedStudentOrderUsesItsOwnIssuedReceiptIdentity(int $print_format): void
    {
        [$student, $order, $receipt] = $this->CreateHistoricalDocuments();
        DB::table('contacts')->where('id', $student->id)->delete();
        $actor = $this->Actor($this->branch_a);
        $this->ExpectPdfRenderer($print_format, ['Issued Student', 'AA-ISSUED', 'Historical course item']);

        $response = $this->actingAs($actor)->get(route('orsders.show', [$order->id, $print_format]));
        $response->assertOk();
        if ($print_format === 0) {
            $this->assertStringContainsString('Issued Student', $response->json('html'));
            $this->assertStringContainsString('AA-ISSUED', $response->json('html'));
            $this->assertStringContainsString('300.00', $response->json('html'));
        } else {
            $response->assertHeader('Content-Type', 'application/pdf');
            $this->assertSame('%PDF-Historical document', $response->getContent());
        }
        $this->assertSame(300, (int) $order->fresh()->total_price);
        $this->assertSame(100, (int) $receipt->fresh()->total_fee);
    }

    #[Test]
    public function OrderWithoutAStudentSnapshotShowsAnExplicitUnavailableIdentity(): void
    {
        [$student, $order, $receipt] = $this->CreateHistoricalDocuments();
        DB::table('receipts')->where('id', $receipt->id)->delete();
        DB::table('contacts')->where('id', $student->id)->delete();

        $response = $this->actingAs($this->Actor($this->branch_a))->get(route('orsders.show', [$order->id, 0]));

        $response->assertOk();
        $this->assertStringContainsString('Student record unavailable.', $response->json('html'));
        $this->assertStringContainsString('300.00', $response->json('html'));
        $this->assertSame(300, (int) $order->fresh()->total_price);
    }

    #[Test]
    #[DataProvider('HistoricalBillingCases')]
    public function HqPrintDoesNotFillHistoricalBillingFromAMovedStudentsCurrentBranch(?string $historical_billing): void
    {
        [$student, $order, $receipt] = $this->CreateHistoricalDocuments();
        DB::table('receipts')->where('id', $receipt->id)->update(['billing_details' => $historical_billing]);
        DB::table('contacts')->where('id', $student->id)->update([
            'centre' => $this->branch_b->id, 'code' => 'BB-CURRENT',
            'name' => 'Current other-branch Student', 'father_name' => 'Private other-branch parent',
            'address' => 'Private other-branch address',
        ]);
        $owner = $this->Actor(Department::findOrFail(1));
        $owner->assignRole(\Spatie\Permission\Models\Role::findOrCreate('SystemAdmin', 'web'));

        $response = $this->actingAs($owner)->get(route('receipts.show', [$receipt->id, 0]));

        $response->assertOk();
        $this->assertStringContainsString('Issued Student', $response->json('html'));
        $this->assertStringContainsString('AA-ISSUED', $response->json('html'));
        $this->assertStringNotContainsString('Private other-branch parent', $response->json('html'));
        $this->assertStringNotContainsString('Private other-branch address', $response->json('html'));
        $this->assertStringNotContainsString('BB-CURRENT', $response->json('html'));
        if ($historical_billing !== null) {
            $this->assertStringContainsString('Historical issued billing', $response->json('html'));
        }
        $this->assertSame($historical_billing, $receipt->fresh()->billing_details);
    }

    #[Test]
    public function AnotherBranchCannotPrintTheOrphanedFinancialDocuments(): void
    {
        [$student, $order, $receipt] = $this->CreateHistoricalDocuments();
        DB::table('contacts')->where('id', $student->id)->delete();
        $actor = $this->Actor($this->branch_b);

        $this->actingAs($actor)->get(route('orsders.show', [$order->id, 0]))->assertForbidden();
        $this->actingAs($actor)->get(route('receipts.show', [$receipt->id, 0]))->assertForbidden();
    }

    private function Actor(Department $branch): User
    {
        $actor = User::factory()->create(['department_id' => $branch->id, 'disable' => 0, 'password' => bcrypt('test-password')]);
        foreach (['order-show', 'receipt-show'] as $permission_name) {
            $actor->givePermissionTo(Permission::findOrCreate($permission_name, 'web'));
        }
        return $actor;
    }

    private function CreateHistoricalDocuments(): array
    {
        $student = Contact::create(['centre' => $this->branch_a->id, 'code' => 'AA-CURRENT', 'name' => 'Current Student']);
        $order = Order::create(['centre' => $this->branch_a->id, 'cid' => $student->id, 'order_number' => 'ORDER-1', 'total_price' => 300, 'orther_fee' => 0]);
        $book = bookuse::forceCreate(['name' => 'Historical course item']);
        OrderDetail::create(['order_id' => $order->id, 'product_id' => $book->id, 'pname' => 'Historical course item', 'quantity' => 3, 'price' => 100]);
        $receipt = Receipt::create([
            'centre' => $this->branch_a->id, 'cid' => $student->id, 'oid' => $order->id,
            'receipt_number' => 'RECEIPT-1', 'student_name' => 'Issued Student', 'student_no' => 'AA-ISSUED',
            'payment_date' => '2026-10-06', 'total_fee' => 100,
        ]);
        receipt_details::forceCreate(['receipt_id' => $receipt->id, 'des' => 'Historical tuition', 'quantity' => 1, 'price' => 100, 'discount' => 0, 'tax' => 1]);
        return [$student, $order, $receipt];
    }

    private function ExpectPdfRenderer(int $print_format, array $expected_text): void
    {
        if ($print_format !== 1) {
            return;
        }
        $pdf_renderer = Mockery::mock(\Barryvdh\DomPDF\PDF::class);
        $pdf_renderer->shouldReceive('output')->andReturn('%PDF-Historical document');
        $receipt_renderer = Mockery::mock(\App\Services\ReceiptPdfRenderer::class)->makePartial();
        $receipt_renderer->shouldReceive('Render')->andReturnUsing(function (string $html_contents) use ($expected_text) {
            foreach ($expected_text as $expected_content) {
                $this->assertStringContainsString($expected_content, $html_contents);
            }
            return '%PDF-Historical document';
        });
        $this->app->instance(\App\Services\ReceiptPdfRenderer::class, $receipt_renderer);
        PDF::shouldReceive('loadHTML')->andReturnUsing(function (string $html_contents) use ($pdf_renderer, $expected_text) {
            foreach ($expected_text as $expected_content) {
                $this->assertStringContainsString($expected_content, $html_contents);
            }
            return $pdf_renderer;
        });
    }

    public static function DocumentFormats(): array
    {
        return ['html' => [0], 'pdf' => [1]];
    }

    public static function HistoricalBillingCases(): array
    {
        return ['no snapshot' => [null], 'issued snapshot' => ['Historical issued billing']];
    }
}
