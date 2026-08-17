<?php

namespace Tests\Feature\CreditNote;

use App\Models\Contact;
use App\Models\Department;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Receipt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ReceiptCreditNoteActionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('financial-documents.credit_note.enabled', true);
    }

    #[Test]
    public function paid_receipt_actions_include_credit_note_create_and_pending_receipts_do_not(): void
    {
        $department = Department::query()->create(['name' => 'CN-ACTIONS', 'code' => 'CNA', 'status' => 1]);
        $actor = User::factory()->create([
            'department_id' => $department->id,
            'password' => Hash::make('secret'),
        ]);
        foreach (['receipt-list', 'credit-note-create'] as $permission) {
            $actor->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        }

        $paid_receipt = $this->CreateReceipt($department->id, true, 'PAID-CN-ACTION');
        $pending_receipt = $this->CreateReceipt($department->id, false, 'PENDING-CN-ACTION');

        $response = $this->actingAs($actor)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest', 'Accept' => 'application/json'])
            ->get('/receipts?type=0');

        $response->assertOk();
        $rows = collect($response->json('data'));
        $this->assertArrayNotHasKey('order_record', (array) $rows->first());
        $this->assertStringContainsString(
            route('credit-notes.store', $paid_receipt),
            (string) $rows->firstWhere('id', $paid_receipt->id)['action'],
        );
        $this->assertStringNotContainsString(
            route('credit-notes.store', $pending_receipt),
            (string) $rows->firstWhere('id', $pending_receipt->id)['action'],
        );
    }

    #[Test]
    public function authorised_user_can_create_a_draft_from_a_paid_receipt_action(): void
    {
        $department = Department::query()->create(['name' => 'CN-STORE', 'code' => 'CNS', 'status' => 1]);
        $actor = User::factory()->create([
            'department_id' => $department->id,
            'password' => Hash::make('secret'),
        ]);
        foreach (['receipt-list', 'credit-note-create'] as $permission) {
            $actor->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        }

        $receipt = $this->CreateReceipt($department->id, true, 'PAID-CN-STORE');
        \Illuminate\Support\Facades\DB::table('receipt_details')->insert([
            'receipt_id' => $receipt->id,
            'des' => 'Tuition',
            'quantity' => '1.00',
            'price' => '100.00',
            'discount' => '0.00',
            'line_type' => 'tuition',
            'tax_treatment' => 'vat_inclusive',
            'vat_rate_basis_points' => 700,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($actor)->post(route('credit-notes.store', $receipt));

        $response->assertRedirect();
        $this->assertDatabaseHas('credit_notes', [
            'receipt_id' => $receipt->id,
            'status' => 'draft',
        ]);
    }

    #[Test]
    public function AuthorisedUserCanCreateADraftWhenZeroAmountLinesHaveNoUnitPrice(): void
    {
        $department = Department::query()->create(['name' => 'CN-ZERO-LINE', 'code' => 'CNZ', 'status' => 1]);
        $actor = User::factory()->create([
            'department_id' => $department->id,
            'password' => Hash::make('secret'),
        ]);
        $actor->givePermissionTo(Permission::findOrCreate('credit-note-create', 'web'));

        $receipt = $this->CreateReceipt($department->id, true, 'PAID-CN-ZERO-LINE');
        \Illuminate\Support\Facades\DB::table('receipt_details')->insert([
            [
                'receipt_id' => $receipt->id,
                'des' => 'Tuition',
                'quantity' => '1.00',
                'price' => '100.00',
                'discount' => '0.00',
                'line_type' => 'tuition',
                'tax_treatment' => 'vat_inclusive',
                'vat_rate_basis_points' => 700,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'receipt_id' => $receipt->id,
                'des' => 'Free registration',
                'quantity' => '1.00',
                'price' => null,
                'discount' => '0.00',
                'line_type' => 'registration_fee',
                'tax_treatment' => 'vat_inclusive',
                'vat_rate_basis_points' => 700,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->actingAs($actor)->post(route('credit-notes.store', $receipt));

        $response->assertRedirect();
        $this->assertDatabaseHas('credit_notes', [
            'receipt_id' => $receipt->id,
            'status' => 'draft',
        ]);
    }

    #[Test]
    public function CreditNoteErrorIsRenderedAsAToastOnTheReceiptPage(): void
    {
        $department = Department::query()->create(['name' => 'CN-TOAST', 'code' => 'CNT', 'status' => 1]);
        $actor = User::factory()->create([
            'department_id' => $department->id,
            'password' => Hash::make('secret'),
        ]);
        foreach (['receipt-list', 'credit-note-create'] as $permission) {
            $actor->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        }

        $receipt = $this->CreateReceipt($department->id, true, 'MISMATCH-CN-TOAST');
        \Illuminate\Support\Facades\DB::table('receipt_details')->insert([
            'receipt_id' => $receipt->id,
            'des' => 'Mismatched tuition',
            'quantity' => '1.00',
            'price' => '90.00',
            'discount' => '0.00',
            'line_type' => 'tuition',
            'tax_treatment' => 'vat_inclusive',
            'vat_rate_basis_points' => 700,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($actor)
            ->from('/receipts?type=0')
            ->post(route('credit-notes.store', $receipt));

        $response->assertRedirect()->assertSessionHasErrors();

        $receipt_page = $this->actingAs($actor)
            ->withSession([
                'errors' => (new ViewErrorBag())->put(
                    'default',
                    new MessageBag(['credit_note' => 'ยอดรวมต้นทางใบเสร็จไม่ตรงกัน']),
                ),
            ])
            ->get('/receipts?type=0');

        $receipt_page->assertOk()
            ->assertSee('toastr.error', false)
            ->assertSee('ยอดรวมต้นทางใบเสร็จไม่ตรงกัน');
    }

    #[Test]
    public function source_total_mismatch_returns_a_friendly_redirect_instead_of_a_debug_page(): void
    {
        $department = Department::query()->create(['name' => 'CN-MISMATCH', 'code' => 'CNM', 'status' => 1]);
        $actor = User::factory()->create([
            'department_id' => $department->id,
            'password' => Hash::make('secret'),
        ]);
        $actor->givePermissionTo(Permission::findOrCreate('credit-note-create', 'web'));

        $receipt = $this->CreateReceipt($department->id, true, 'MISMATCH-CN-ACTION');
        \Illuminate\Support\Facades\DB::table('receipt_details')->insert([
            'receipt_id' => $receipt->id,
            'des' => 'Mismatched tuition',
            'quantity' => '1.00',
            'price' => '90.00',
            'discount' => '0.00',
            'line_type' => 'tuition',
            'tax_treatment' => 'vat_inclusive',
            'vat_rate_basis_points' => 700,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($actor)->post(route('credit-notes.store', $receipt));

        $response->assertRedirect()->assertSessionHasErrors();
        $this->assertDatabaseMissing('credit_notes', ['receipt_id' => $receipt->id]);
    }

    #[Test]
    public function application_returns_a_friendly_response_for_credit_note_domain_exceptions(): void
    {
        Route::middleware(['web', \App\Http\Middleware\HandleCreditNoteResponses::class])->get('/testing-credit-note-response', static function (): never {
            throw \App\Domain\CreditNote\Exceptions\CreditNoteDomainException::SourceTotalMismatch();
        });

        $response = $this->get('/testing-credit-note-response');

        $response->assertRedirect()
            ->assertSessionHasErrors()
            ->assertSessionHas('credit_note_error_code', 'CREDIT_NOTE_SOURCE_TOTAL_MISMATCH');
    }

    private function CreateReceipt(int $department_id, bool $paid, string $receipt_number): Receipt
    {
        $contact = Contact::query()->create([
            'centre' => $department_id,
            'code' => $receipt_number,
            'name' => 'Credit Note Action Buyer',
            'nickname' => 'CN',
        ]);
        $order = Order::query()->create([
            'centre' => $department_id,
            'cid' => $contact->id,
            'status' => $paid ? 1 : 0,
            'total_price' => '100.00',
        ]);
        $level_id = \Illuminate\Support\Facades\DB::table('levels')->insertGetId([
            'name' => 'CN Level',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $bookuse_id = \Illuminate\Support\Facades\DB::table('bookuses')->insertGetId([
            'level_id' => $level_id,
            'name' => 'CN Book',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        OrderDetail::query()->create([
            'order_id' => $order->id,
            'product_id' => $bookuse_id,
            'pname' => 'CN Book',
            'quantity' => '1.00',
            'price' => '100.00',
        ]);

        return Receipt::query()->create([
            'type' => 0,
            'centre' => $department_id,
            'cid' => $contact->id,
            'oid' => $order->id,
            'receipt_number' => $receipt_number,
            'payment_date' => now()->toDateString(),
            'status' => $paid ? 1 : 0,
            'total_fee' => '100.00',
        ]);
    }
}
