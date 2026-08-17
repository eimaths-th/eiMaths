<?php

namespace Tests\Feature\CreditNote;

use App\Domain\CreditNote\Enums\CreditNoteEventOperation;
use App\Domain\CreditNote\Enums\CreditNotePostingType;
use App\Domain\CreditNote\Enums\CreditNoteStatus;
use App\Domain\CreditNote\Models\CreditNote;
use App\Domain\CreditNote\Models\CreditNoteEvent;
use App\Domain\CreditNote\Models\CreditNotePosting;
use App\Domain\CreditNote\Models\CreditNoteRunningNumber;
use App\Domain\CreditNote\Services\CreditNoteEventRecorder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreditNoteEvidencePersistenceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function PersistsNumberPostingAndSanitizedAppendOnlyEventEvidence(): void
    {
        $department_id = DB::table('departments')->insertGetId(['code' => 'BKK', 'name' => 'Bangkok']);
        $receipt_id = DB::table('receipts')->insertGetId(['centre' => $department_id, 'total_fee' => '100.00']);
        $credit_note = CreditNote::create([
            'receipt_id' => $receipt_id,
            'centre_id' => $department_id,
            'status' => CreditNoteStatus::ISSUED,
            'currency' => 'THB',
            'number' => 'BKCN2608001',
        ]);

        $sequence = CreditNoteRunningNumber::create([
            'centre_id' => $department_id,
            'year' => 2026,
            'month' => 8,
            'number' => 1,
        ]);
        $this->assertSame(1, $sequence->number);
        $this->assertTrue(Schema::hasIndex('credit_note_running_numbers', 'credit_note_running_numbers_centre_id_year_month_unique'));

        $posting = CreditNotePosting::create([
            'credit_note_id' => $credit_note->id,
            'centre_id' => $department_id,
            'posting_type' => CreditNotePostingType::ISSUE,
            'effective_date' => '2026-08-12',
            'taxable_base_delta_minor' => -10000,
            'vat_delta_minor' => -700,
            'exempt_delta_minor' => 0,
            'out_of_scope_delta_minor' => 0,
            'grand_total_delta_minor' => -10700,
            'correlation_id' => 'corr-1',
            'occurred_at' => now(),
        ]);
        $this->assertSame(-10700, $posting->grand_total_delta_minor);

        $event = app(CreditNoteEventRecorder::class)->Record(
            $credit_note,
            CreditNoteEventOperation::ISSUED,
            CreditNoteStatus::DRAFT,
            CreditNoteStatus::ISSUED,
            -10700,
            null,
            null,
            'corr-1',
            [
                'field_names' => ['reason_code'],
                'version' => 1,
                'buyer_name' => 'must-not-persist',
                'nested' => ['tax_id' => 'must-not-persist', 'item_ids' => [10]],
            ],
        );

        $this->assertSame(['field_names' => ['reason_code'], 'version' => 1, 'nested' => ['item_ids' => [10]]], $event->metadata);
        $this->assertSame(CreditNoteEventOperation::ISSUED, $event->operation);
        $this->assertSame(CreditNoteStatus::DRAFT, $event->status_before);
        $this->assertSame(CreditNoteStatus::ISSUED, $event->status_after);

        try {
            $posting->grand_total_delta_minor = -1;
            $posting->save();
            $this->fail('Posting updates must be rejected.');
        } catch (\LogicException) {
            $this->addToAssertionCount(1);
        }

        try {
            $event->amount_minor = 0;
            $event->save();
            $this->fail('Event updates must be rejected.');
        } catch (\LogicException) {
            $this->addToAssertionCount(1);
        }

        $this->expectException(\LogicException::class);
        $posting->delete();
    }
}
