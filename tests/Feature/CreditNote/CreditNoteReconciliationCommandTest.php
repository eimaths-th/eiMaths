<?php

namespace Tests\Feature\CreditNote;

use App\Domain\CreditNote\Models\CreditNote;
use App\Domain\CreditNote\Models\CreditNotePosting;
use App\Models\Department;
use App\Models\Receipt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreditNoteReconciliationCommandTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function reconciliation_command_reports_mismatch_and_does_not_repair_postings(): void
    {
        $centre = Department::query()->create(['name' => 'REC', 'code' => 'REC', 'status' => 1]);
        $receipt = Receipt::create(['oid' => null, 'cid' => null, 'centre' => $centre->id, 'receipt_number' => 'REC-1', 'payment_date' => now()->toDateString(), 'status' => 1, 'total_fee' => '10.00']);
        $note = CreditNote::create(['receipt_id' => $receipt->id, 'centre_id' => $centre->id, 'status' => 'issued', 'number' => 'RECCN1', 'currency' => 'THB', 'difference_grand_total_minor' => 1000]);
        $before = CreditNotePosting::query()->count();

        $this->withoutMockingConsoleOutput();
        $output = new \Symfony\Component\Console\Output\BufferedOutput();
        $exit_code = Artisan::call('credit-notes:reconcile', ['--batch' => 100, '--json' => true], $output);
        $result = json_decode(trim($output->fetch()), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame(1, $exit_code);
        $this->assertSame(1, $result['checked_count']);
        $this->assertGreaterThan(0, $result['mismatch_count']);
        $this->assertSame($before, CreditNotePosting::query()->count());
        $this->assertSame(1, Artisan::call('credit-notes:reconcile', ['--batch' => 1001, '--json' => true]));
    }
}
