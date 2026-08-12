<?php

namespace Tests\Feature\CreditNote;

use App\Domain\CreditNote\Enums\CreditNotePostingType;
use App\Domain\CreditNote\Models\CreditNote;
use App\Domain\CreditNote\Models\CreditNotePosting;
use App\Domain\CreditNote\Queries\CreditNoteRevenueQuery;
use App\Models\Department;
use App\Models\Receipt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreditNoteRevenueQueryTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function revenue_query_merges_gross_issue_cancellation_and_net_by_period_and_branch(): void
    {
        $centre = Department::query()->create(['name' => 'REV', 'code' => 'REV', 'status' => 1]);
        $receipt = Receipt::create(['oid' => null, 'cid' => null, 'centre' => $centre->id, 'receipt_number' => 'REV-1', 'payment_date' => '2026-08-10', 'status' => 1, 'total_fee' => '100.00']);
        $note = CreditNote::create(['receipt_id' => $receipt->id, 'centre_id' => $centre->id, 'status' => 'cancelled', 'number' => 'REVCN1', 'currency' => 'THB']);
        CreditNotePosting::create(['credit_note_id' => $note->id, 'centre_id' => $centre->id, 'posting_type' => CreditNotePostingType::ISSUE, 'effective_date' => '2026-08-10', 'taxable_base_delta_minor' => -9000, 'vat_delta_minor' => -1000, 'exempt_delta_minor' => 0, 'out_of_scope_delta_minor' => 0, 'grand_total_delta_minor' => -10000, 'occurred_at' => now()]);
        CreditNotePosting::create(['credit_note_id' => $note->id, 'centre_id' => $centre->id, 'posting_type' => CreditNotePostingType::CANCELLATION, 'effective_date' => '2026-08-12', 'taxable_base_delta_minor' => 9000, 'vat_delta_minor' => 1000, 'exempt_delta_minor' => 0, 'out_of_scope_delta_minor' => 0, 'grand_total_delta_minor' => 10000, 'occurred_at' => now()]);

        $query = app(CreditNoteRevenueQuery::class);
        $daily = $query->ByDate('2026-08-10', '2026-08-12', $centre->id);
        $this->assertSame(10000, $daily['2026-08-10']['gross_minor']);
        $this->assertSame(10000, $daily['2026-08-10']['issued_minor']);
        $this->assertSame(-10000, $daily['2026-08-10']['net_minor']);
        $this->assertSame(10000, $daily['2026-08-12']['cancelled_minor']);
        $this->assertSame(10000, $daily['2026-08-12']['net_minor']);
        $this->assertSame(10000, $query->ByMonth('2026-08-01', '2026-08-31', $centre->id)['2026-08']['issued_minor']);
        $this->assertSame(10000, $query->ByYear('2026-01-01', '2026-12-31', $centre->id)['2026']['cancelled_minor']);
        $this->assertSame(10000, $query->ByBranch('2026-08-01', '2026-08-31')[$centre->id]['issued_minor']);
        $this->assertSame(0, $query->Totals('2026-08-10', '2026-08-12', $centre->id)['net_minor']);
    }
}
