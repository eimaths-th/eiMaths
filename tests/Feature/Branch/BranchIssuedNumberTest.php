<?php

namespace Tests\Feature\Branch;

use App\Models\Department;
use App\Models\InvoiceRunningNumber;
use App\Models\OrderRunningNumber;
use App\Models\ProductRunningNumber;
use App\Models\ReceiptRunningNumber;
use App\Models\studentRunningNumber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BranchIssuedNumberTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        Http::preventStrayRequests();
        Mail::fake();
        Notification::fake();
    }

    #[DataProvider('IssuedNumberCases')]
    #[Test]
    public function ImportedIssuedNumbersAreSkippedWithoutChangingPreviewCounters(
        string $running_model,
        string $source_table,
        string $issued_column,
        string $prefix,
        int $width,
        bool $monthly,
        array $extra_fields,
    ): void {
        $branch = Department::create(['name' => 'Branch A', 'code' => 'AA', 'status' => 1]);
        $number_prefix = $prefix.'AA'.($monthly ? date('ym') : '');
        DB::table($source_table)->insert(array_merge($extra_fields, [
            'centre' => $branch->id,
            $issued_column => $number_prefix.str_pad('1', $width, '0', STR_PAD_LEFT),
        ]));
        DB::table($source_table)->insert(array_merge($extra_fields, [
            'centre' => $branch->id,
            $issued_column => $number_prefix.str_pad('2', $width, '0', STR_PAD_LEFT),
        ]));

        $expected_number = $number_prefix.str_pad('3', $width, '0', STR_PAD_LEFT);
        $this->assertSame($expected_number, $running_model::pre_generate((string) $branch->id));
        $this->assertSame(0, $running_model::count(), 'Preview must not reserve an issued number.');
        $this->assertSame($expected_number, $running_model::generate((string) $branch->id));
        $this->assertSame(3, (int) $running_model::sole()->number);
        $this->assertSame($number_prefix.str_pad('4', $width, '0', STR_PAD_LEFT), $running_model::generate((string) $branch->id));
        $this->assertSame(2, DB::table($source_table)->count(), 'Existing imported records must be preserved.');
    }

    public static function IssuedNumberCases(): array
    {
        return [
            'student' => [studentRunningNumber::class, 'contacts', 'code', '', 4, false, []],
            'product' => [ProductRunningNumber::class, 'products', 'code', 'AC-', 4, false, ['amount' => 0]],
            'order' => [OrderRunningNumber::class, 'orders', 'order_number', 'Or-', 4, true, []],
            'receipt' => [ReceiptRunningNumber::class, 'receipts', 'receipt_number', '', 3, true, []],
            'invoice' => [InvoiceRunningNumber::class, 'invoices', 'invoice_number', 'Inv-', 4, true, []],
        ];
    }

    #[Test]
    public function ExistingCaseVariantsDoNotReissueTheSameDocumentNumber(): void
    {
        $branch = Department::create(['name' => 'Branch A', 'code' => 'AA', 'status' => 1]);
        DB::table('orders')->insert(['centre' => $branch->id, 'order_number' => 'or-aa'.date('ym').'0001']);

        $this->assertSame('Or-AA'.date('ym').'0002', OrderRunningNumber::generate((string) $branch->id));
        $this->assertSame(2, (int) OrderRunningNumber::sole()->number);
    }

    #[Test]
    #[DataProvider('CurrentYearCounterCases')]
    public function OlderYearNumbersArePreservedWhileOnlyTheCurrentYearCounterAdvances(bool $has_current_counter): void
    {
        $branch = Department::create(['name' => 'Branch A', 'code' => 'AA', 'status' => 1]);
        $older_counter = studentRunningNumber::create(['department' => 'AA', 'year' => (int) date('Y') - 1, 'number' => 9]);
        $current_counter = $has_current_counter
            ? studentRunningNumber::create(['department' => 'AA', 'year' => date('Y'), 'number' => 2]) : null;

        $this->assertSame('AA0010', studentRunningNumber::pre_generate((string) $branch->id));
        $this->assertSame($has_current_counter ? 2 : 1, studentRunningNumber::count());
        $this->assertSame('AA0010', studentRunningNumber::generate((string) $branch->id));
        $this->assertSame(9, (int) $older_counter->fresh()->number);
        $this->assertSame(2, studentRunningNumber::count());
        $updated_counter = studentRunningNumber::where('year', date('Y'))->sole();
        $this->assertSame(10, (int) $updated_counter->number);
        if ($current_counter) {
            $this->assertSame($current_counter->id, $updated_counter->id);
        }
    }

    public static function CurrentYearCounterCases(): array
    {
        return ['existing current row' => [true], 'new current row' => [false]];
    }
}
