<?php

namespace Tests\Unit\CreditNote;

use App\Domain\CreditNote\Services\CreditNoteSourceHasher;
use App\Domain\CreditNote\Services\CreditNoteSourceReader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreditNoteSourceHasherTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function CanonicalHashIsStableAndChangesForEveryListedSourceField(): void
    {
        $receipt = $this->CreateReceipt();
        $reader = app(CreditNoteSourceReader::class);
        $hasher = app(CreditNoteSourceHasher::class);

        $first_hash = $hasher->Hash($reader->Read($receipt));
        $second_hash = $hasher->Hash($reader->Read($receipt->fresh()));
        $this->assertSame($first_hash, $second_hash);

        DB::table('receipt_details')->where('receipt_id', $receipt->id)->update(['des' => 'Changed description']);
        $changed_hash = $hasher->Hash($reader->Read($receipt->fresh()));
        $this->assertNotSame($first_hash, $changed_hash);
    }

    #[Test]
    public function ReaderAddsPositiveServiceChargeAsOutOfScopeSyntheticLine(): void
    {
        $snapshot = app(CreditNoteSourceReader::class)->Read($this->CreateReceipt());

        $service_lines = array_values(array_filter(
            $snapshot->lines,
            static fn ($line): bool => $line->source_type->value === 'receipt_service_charge'
        ));

        $this->assertCount(1, $service_lines);
        $this->assertSame('service_charge', $service_lines[0]->line_type->value);
        $this->assertSame('out_of_scope', $service_lines[0]->tax_treatment->value);
        $this->assertSame(500, $service_lines[0]->original_line_total_minor);
        $this->assertSame(11200, $snapshot->grand_total_minor);
    }

    private function CreateReceipt()
    {
        $department_id = DB::table('departments')->insertGetId(['code' => 'BKK', 'name' => 'Bangkok']);
        $contact_id = DB::table('contacts')->insertGetId([
            'centre' => $department_id,
            'name' => 'Student',
            'address' => 'Address',
        ]);
        $order_id = DB::table('orders')->insertGetId([
            'centre' => $department_id,
            'cid' => $contact_id,
            'status' => 1,
            'orther_fee' => '5.00',
            'total_price' => '107.00',
        ]);
        $receipt_id = DB::table('receipts')->insertGetId([
            'centre' => $department_id,
            'cid' => $contact_id,
            'oid' => $order_id,
            'status' => 1,
            'payment_date' => '2026-08-12',
            'receipt_number' => 'R-1',
            'total_fee' => '107.00',
            'billing_details' => 'Student billing',
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
