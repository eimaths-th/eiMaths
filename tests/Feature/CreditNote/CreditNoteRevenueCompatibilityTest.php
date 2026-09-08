<?php

namespace Tests\Feature\CreditNote;

use App\Domain\CreditNote\Enums\CreditNotePostingType;
use App\Domain\CreditNote\Models\CreditNote;
use App\Domain\CreditNote\Models\CreditNotePosting;
use App\Models\Department;
use App\Models\Order;
use App\Models\Receipt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreditNoteRevenueCompatibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('financial-documents.credit_note.enabled', true);
    }

    #[Test]
    public function existing_gross_api_fields_remain_and_additive_credit_note_fields_are_present(): void
    {
        $department = Department::query()->create(['name' => 'COMP', 'code' => 'CMP', 'status' => 1]);
        $order = Order::create(['cid' => null, 'centre' => $department->id, 'status' => 1]);
        $receipt = Receipt::create(['oid' => $order->id, 'cid' => null, 'centre' => $department->id, 'type' => 0, 'receipt_number' => 'COMP-1', 'payment_date' => '2026-08-12', 'status' => 1, 'total_fee' => '100.00']);
        $note = CreditNote::create(['receipt_id' => $receipt->id, 'centre_id' => $department->id, 'status' => 'issued', 'number' => 'COMPCN1', 'currency' => 'THB']);
        CreditNotePosting::create(['credit_note_id' => $note->id, 'centre_id' => $department->id, 'posting_type' => CreditNotePostingType::ISSUE, 'effective_date' => '2026-08-12', 'taxable_base_delta_minor' => -9000, 'vat_delta_minor' => -1000, 'exempt_delta_minor' => 0, 'out_of_scope_delta_minor' => 0, 'grand_total_delta_minor' => -10000, 'occurred_at' => now()]);

        $this->getJson('/api/revenue-summary-by-branch')->assertUnauthorized();
        $actor = User::factory()->create(['department_id' => $department->id, 'password' => Hash::make('secret')]);
        $actor->givePermissionTo(\Spatie\Permission\Models\Permission::findOrCreate('receipt-list', 'web'));
        $api = $this->withToken($actor->createToken('test')->plainTextToken)->getJson('/api/revenue-summary-by-branch');
        $api->assertOk()->assertJsonStructure(['data' => ['grand_total', 'total_receipts', 'branches']]);
        $this->assertArrayHasKey('issued_credit_note_total', $api->json('data'));
        $this->assertArrayHasKey('cancelled_credit_note_total', $api->json('data'));
        $this->assertArrayHasKey('net_revenue', $api->json('data'));
        $this->assertArrayHasKey('total_credit_notes_issued', $api->json('data'));
        $this->assertArrayHasKey('total_credit_notes_cancelled', $api->json('data'));
    }

    #[Test]
    public function dashboard_daily_response_adds_net_fields_without_replacing_gross_field(): void
    {
        $department = Department::query()->create(['name' => 'COMP2', 'code' => 'C2', 'status' => 1]);
        $actor = User::factory()->create(['department_id' => $department->id, 'password' => Hash::make('secret')]);
        $order = Order::create(['cid' => null, 'centre' => $department->id, 'status' => 1]);
        $receipt = Receipt::create(['oid' => $order->id, 'cid' => null, 'centre' => $department->id, 'type' => 0, 'receipt_number' => 'COMP-2', 'payment_date' => '2026-08-12', 'status' => 1, 'total_fee' => '100.00']);
        $note = CreditNote::create(['receipt_id' => $receipt->id, 'centre_id' => $department->id, 'status' => 'issued', 'number' => 'COMPCN2', 'currency' => 'THB']);
        CreditNotePosting::create(['credit_note_id' => $note->id, 'centre_id' => $department->id, 'posting_type' => CreditNotePostingType::ISSUE, 'effective_date' => '2026-08-12', 'taxable_base_delta_minor' => -9000, 'vat_delta_minor' => -1000, 'exempt_delta_minor' => 0, 'out_of_scope_delta_minor' => 0, 'grand_total_delta_minor' => -10000, 'occurred_at' => now()]);

        $actor->givePermissionTo(\Spatie\Permission\Models\Permission::findOrCreate('dashboard', 'web'));
        $response = $this->actingAs($actor)->postJson('/receipt_sum_by_date', ['date_range' => '2026-08-12 - 2026-08-12']);
        $response->assertOk()->assertJsonStructure(['total_income', 'net_revenue', 'receipt_sum_by_date']);
        $this->assertArrayHasKey('issued_credit_note_total', $response->json());
        $this->assertArrayHasKey('issued_credit_note_total', $response->json('receipt_sum_by_date.0'));
    }
}
