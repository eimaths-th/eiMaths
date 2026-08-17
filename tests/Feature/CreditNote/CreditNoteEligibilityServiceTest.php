<?php

namespace Tests\Feature\CreditNote;

use App\Domain\CreditNote\Exceptions\CreditNoteDomainException;
use App\Domain\CreditNote\Services\CreditNoteEligibilityService;
use App\Domain\CreditNote\Services\CreditNoteSourceReader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreditNoteEligibilityServiceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function PaidReceiptWithCompleteSourceIsEligible(): void
    {
        $receipt = $this->CreateReceipt('107.00', 1, 1, '5.00');
        $eligibility = app(CreditNoteEligibilityService::class)->Evaluate(
            $receipt,
            app(CreditNoteSourceReader::class)->Read($receipt),
        );

        $this->assertTrue($eligibility->IsEligible());
        $this->assertSame(11200, $eligibility->remaining_grand_total_minor);
    }

    #[Test]
    public function OneSatangToleranceIsAllowedButTwoSatangMismatchIsBlocked(): void
    {
        $receipt = $this->CreateReceipt('107.01', 1, 1, '0.00');
        app(CreditNoteEligibilityService::class)->AssertCanCreateDraft(
            $receipt,
            app(CreditNoteSourceReader::class)->Read($receipt),
        );

        DB::table('receipts')->where('id', $receipt->id)->update(['total_fee' => '107.02']);
        $this->expectException(CreditNoteDomainException::class);
        app(CreditNoteEligibilityService::class)->AssertCanCreateDraft(
            $receipt->fresh(),
            app(CreditNoteSourceReader::class)->Read($receipt->fresh()),
        );
    }

    #[Test]
    public function MissingMetadataAndUnpaidStateAreActionableBlocks(): void
    {
        $receipt = $this->CreateReceipt('107.00', 0, 0, '0.00');
        DB::table('receipt_details')->where('receipt_id', $receipt->id)->update([
            'line_type' => null,
            'tax_treatment' => null,
            'vat_rate_basis_points' => null,
        ]);

        try {
            app(CreditNoteEligibilityService::class)->AssertCanCreateDraft(
                $receipt->fresh(),
                app(CreditNoteSourceReader::class)->Read($receipt->fresh()),
            );
            $this->fail('Incomplete and unpaid source must be blocked.');
        } catch (CreditNoteDomainException $exception) {
            $this->assertContains($exception->ErrorCode(), [
                'CREDIT_NOTE_SOURCE_INCOMPLETE',
            ]);
        }
    }

    private function CreateReceipt(string $total_fee, int $receipt_status, int $order_status, string $service_charge)
    {
        $department_id = DB::table('departments')->insertGetId(['code' => 'BKK', 'name' => 'Bangkok']);
        $contact_id = DB::table('contacts')->insertGetId(['centre' => $department_id, 'name' => 'Student']);
        $order_id = DB::table('orders')->insertGetId([
            'centre' => $department_id,
            'cid' => $contact_id,
            'status' => $order_status,
            'orther_fee' => $service_charge,
            'total_price' => '107.00',
        ]);
        $receipt_id = DB::table('receipts')->insertGetId([
            'centre' => $department_id,
            'cid' => $contact_id,
            'oid' => $order_id,
            'status' => $receipt_status,
            'payment_date' => '2026-08-12',
            'receipt_number' => 'R-1',
            'total_fee' => $total_fee,
        ]);
        DB::table('receipt_details')->insert([
            'receipt_id' => $receipt_id,
            'des' => 'ค่าคอร์สเรียน Primary',
            'unit' => 'course',
            'quantity' => '1.00',
            'price' => '107.00',
            'discount' => '0.00',
            'tax' => 1,
            'line_type' => 'tuition',
            'tax_treatment' => 'vat_inclusive',
            'vat_rate_basis_points' => 700,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return \App\Models\Receipt::findOrFail($receipt_id);
    }
}
