<?php

namespace Tests\Feature\CreditNote;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReceiptLineMetadataMigrationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function AddsMetadataAndBackfillsOnlyTheApprovedExactMappings(): void
    {
        $receipt_id = DB::table('receipts')->insertGetId(['total_fee' => '100.00']);
        $source_rows = [
            ['des' => 'ค่าคอร์สเรียน Kindergarten', 'tax' => 1, 'line_type' => 'tuition'],
            ['des' => 'ค่าหนังสือ', 'tax' => 0, 'line_type' => 'book'],
            ['des' => 'ค่ามัดจำ', 'tax' => 1, 'line_type' => 'deposit'],
            ['des' => 'ค่าลงทะเบียนแรกเข้า', 'tax' => 1, 'line_type' => 'registration_fee'],
            ['des' => 'ค่าอุปกรณ์การเรียน', 'tax' => 1, 'line_type' => 'equipment'],
            ['des' => 'ค่าคอร์สเรียนX', 'tax' => 1, 'line_type' => 'unknown'],
            ['des' => 'อื่นๆ', 'tax' => 0, 'line_type' => 'unknown'],
        ];

        $ids = [];
        foreach ($source_rows as $index => $source_row) {
            $ids[] = DB::table('receipt_details')->insertGetId([
                'receipt_id' => $receipt_id,
                'des' => $source_row['des'],
                'unit' => 'unit-' . $index,
                'quantity' => '1.25',
                'price' => '10.00',
                'discount' => '0.50',
                'tax' => $source_row['tax'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $migration = require base_path('database/migrations/2026_08_12_000000_add_credit_note_metadata_to_receipt_details.php');
        $migration->down();
        $migration->up();

        $this->assertTrue(Schema::hasColumn('receipt_details', 'line_type'));
        $this->assertTrue(Schema::hasColumn('receipt_details', 'tax_treatment'));
        $this->assertTrue(Schema::hasColumn('receipt_details', 'vat_rate_basis_points'));

        $rows = DB::table('receipt_details')->whereIn('id', $ids)->orderBy('id')->get();
        foreach ($rows as $index => $row) {
            $expected = $source_rows[$index];
            $this->assertSame($expected['line_type'], $row->line_type);
            $this->assertSame($expected['tax'] === 1 ? 'vat_inclusive' : 'vat_exempt', $row->tax_treatment);
            $this->assertSame($expected['tax'] === 1 ? 700 : 0, (int) $row->vat_rate_basis_points);
            $this->assertSame(1.25, (float) $row->quantity);
            $this->assertSame(10.0, (float) $row->price);
            $this->assertSame(0.5, (float) $row->discount);
            $this->assertSame($expected['des'], $row->des);
        }
    }
}
