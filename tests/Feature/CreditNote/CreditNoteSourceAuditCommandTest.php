<?php

namespace Tests\Feature\CreditNote;

use App\Domain\CreditNote\Models\CreditNote;
use App\Models\Department;
use App\Models\Receipt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreditNoteSourceAuditCommandTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function audit_command_returns_json_and_nonzero_for_issued_document_mismatch_without_pii(): void
    {
        $centre = Department::query()->create(['name' => 'AUD', 'code' => 'AUD', 'status' => 1]);
        $receipt = Receipt::create(['oid' => null, 'cid' => null, 'centre' => $centre->id, 'receipt_number' => 'AUD-1', 'payment_date' => now()->toDateString(), 'status' => 1, 'total_fee' => '10.00']);
        CreditNote::create(['receipt_id' => $receipt->id, 'centre_id' => $centre->id, 'status' => 'issued', 'number' => 'AUDCN1', 'currency' => 'THB', 'buyer_name' => 'PII buyer']);

        $this->withoutMockingConsoleOutput();
        $output = new \Symfony\Component\Console\Output\BufferedOutput();
        $exit_code = Artisan::call('credit-notes:audit-sources', ['--batch' => 10, '--json' => true], $output);
        $payload = $output->fetch();
        $result = json_decode(trim($payload), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame(1, $exit_code);
        $this->assertGreaterThan(0, $result['mismatch_count']);
        $this->assertStringNotContainsString('PII buyer', $payload);
        $this->assertSame(1, Artisan::call('credit-notes:audit-sources', ['--batch' => 0, '--json' => true]));
    }

    #[Test]
    public function audit_command_counts_historical_receipt_and_line_metadata_gaps(): void
    {
        $centre = Department::query()->create(['name' => 'AUD2', 'code' => 'AUD2', 'status' => 1]);
        $receipt = Receipt::create([
            'oid' => null,
            'cid' => null,
            'centre' => $centre->id,
            'receipt_number' => null,
            'payment_date' => null,
            'billing_details' => null,
            'status' => 1,
            'total_fee' => '10.00',
        ]);
        DB::table('receipt_details')->insert([
            'receipt_id' => $receipt->id,
            'des' => 'Historical line',
            'quantity' => '1.00',
            'price' => '10.00',
            'discount' => '0.00',
            'line_type' => 'unknown',
            'tax_treatment' => null,
            'vat_rate_basis_points' => null,
        ]);

        $this->withoutMockingConsoleOutput();
        $output = new \Symfony\Component\Console\Output\BufferedOutput();
        $exit_code = Artisan::call('credit-notes:audit-sources', ['--batch' => 10, '--json' => true], $output);
        $raw_output = $output->fetch();
        $result = json_decode(trim($raw_output), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(1, $exit_code);
        $this->assertSame(1, $result['checked_receipt_count']);
        $this->assertGreaterThan(0, $result['unknown_line_type_count']);
        $this->assertGreaterThan(0, $result['missing_metadata_count']);
        $this->assertGreaterThan(0, $result['missing_number_count']);
        $this->assertGreaterThan(0, $result['missing_date_count']);
        $this->assertGreaterThan(0, $result['missing_details_count']);
    }
}
