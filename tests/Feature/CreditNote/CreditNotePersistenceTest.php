<?php

namespace Tests\Feature\CreditNote;

use App\Domain\CreditNote\Enums\CreditNoteStatus;
use App\Domain\CreditNote\Enums\CreditNoteSourceType;
use App\Domain\CreditNote\Models\CreditNote;
use App\Domain\CreditNote\Models\CreditNoteItem;
use App\Models\Receipt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreditNotePersistenceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function CreatesHeaderAndItemWithPortableUniqueGuardsAndRelationships(): void
    {
        $department_id = DB::table('departments')->insertGetId(['code' => 'BKK', 'name' => 'Bangkok']);
        $receipt_id = DB::table('receipts')->insertGetId(['centre' => $department_id, 'total_fee' => '100.00']);

        foreach ([
            'credit_notes',
            'credit_note_items',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table));
        }

        foreach ([
            'receipt_id',
            'centre_id',
            'status',
            'number',
            'currency',
            'reason_code',
            'lock_version',
            'active_draft_receipt_id',
        ] as $column) {
            $this->assertTrue(Schema::hasColumn('credit_notes', $column));
        }

        $credit_note = CreditNote::create([
            'receipt_id' => $receipt_id,
            'centre_id' => $department_id,
            'status' => CreditNoteStatus::DRAFT,
            'currency' => 'THB',
            'lock_version' => 0,
            'reason_code' => 'product_return',
            'reason_details' => 'Approved return',
        ]);
        $item = $credit_note->Items()->create([
            'sort_order' => 1,
            'source_type' => CreditNoteSourceType::RECEIPT_DETAIL,
            'source_id' => 10,
            'line_type' => 'book',
            'description' => 'Book',
            'unit' => 'item',
            'tax_treatment' => 'vat_exempt',
            'vat_rate_basis_points' => 0,
            'original_quantity' => '1.00',
            'original_unit_price_minor' => 10000,
            'original_discount_minor' => 0,
            'original_line_total_minor' => 10000,
            'credit_quantity' => '1.00',
            'credit_amount_minor' => 10000,
            'credit_taxable_base_minor' => 0,
            'credit_vat_minor' => 0,
            'credit_exempt_minor' => 10000,
            'credit_out_of_scope_minor' => 0,
        ]);

        $this->assertSame(CreditNoteStatus::DRAFT, $credit_note->fresh()->status);
        $this->assertTrue($credit_note->Items()->whereKey($item->id)->exists());
        $this->assertTrue(Receipt::findOrFail($receipt_id)->CreditNotes()->whereKey($credit_note->id)->exists());
    }

    #[Test]
    public function IssuedAndCancelledDocumentsRejectChangesAndDeletes(): void
    {
        $department_id = DB::table('departments')->insertGetId(['code' => 'BKK', 'name' => 'Bangkok']);
        $receipt_id = DB::table('receipts')->insertGetId(['centre' => $department_id, 'total_fee' => '100.00']);
        $credit_note = CreditNote::create([
            'receipt_id' => $receipt_id,
            'centre_id' => $department_id,
            'status' => CreditNoteStatus::ISSUED,
            'currency' => 'THB',
            'number' => 'BKCN2608001',
            'lock_version' => 1,
        ]);

        try {
            $credit_note->reason_details = 'Changed';
            $credit_note->save();
            $this->fail('Issued header fields must be immutable.');
        } catch (\LogicException) {
            $this->addToAssertionCount(1);
        }

        $this->expectException(\LogicException::class);
        $credit_note->delete();
    }

    #[Test]
    public function DraftItemsCanChangeButFinalItemsCannotBeDeleted(): void
    {
        $department_id = DB::table('departments')->insertGetId(['code' => 'BKK', 'name' => 'Bangkok']);
        $receipt_id = DB::table('receipts')->insertGetId(['centre' => $department_id, 'total_fee' => '100.00']);
        $credit_note = CreditNote::create([
            'receipt_id' => $receipt_id,
            'centre_id' => $department_id,
            'status' => CreditNoteStatus::DRAFT,
            'currency' => 'THB',
        ]);
        $item = $credit_note->Items()->create([
            'sort_order' => 1,
            'source_type' => CreditNoteSourceType::RECEIPT_DETAIL,
            'source_id' => 10,
            'line_type' => 'book',
            'description' => 'Book',
            'tax_treatment' => 'vat_exempt',
            'vat_rate_basis_points' => 0,
            'original_quantity' => '1.00',
            'original_unit_price_minor' => 10000,
            'original_discount_minor' => 0,
            'original_line_total_minor' => 10000,
            'credit_quantity' => '1.00',
            'credit_amount_minor' => 10000,
            'credit_taxable_base_minor' => 0,
            'credit_vat_minor' => 0,
            'credit_exempt_minor' => 10000,
            'credit_out_of_scope_minor' => 0,
        ]);
        $item->description = 'Updated draft item';
        $item->save();

        $credit_note->status = CreditNoteStatus::ISSUED;
        $credit_note->number = 'BKCN2608002';
        $credit_note->save();

        $this->expectException(\LogicException::class);
        $item->delete();
    }
}
