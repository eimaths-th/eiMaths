<?php

namespace Tests\Feature\CreditNote;

use App\Domain\CreditNote\Services\CreditNoteNumberGenerator;
use App\Models\Department;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreditNoteNumberGeneratorTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function GeneratesIndependentCentreMonthNumbersWithMinimumThreeDigitPadding(): void
    {
        $centre_id = DB::table('departments')->insertGetId(['code' => 'bkk', 'name' => 'Bangkok']);
        $generator = app(CreditNoteNumberGenerator::class);
        $date = CarbonImmutable::parse('2026-08-12', 'Asia/Bangkok');

        $this->assertSame('BKKCN2608001', $generator->Next($centre_id, $date));
        $this->assertSame('BKKCN2608002', $generator->Next($centre_id, $date));

        $this->assertSame('BKKCN2609001', $generator->Next($centre_id, $date->addMonth()));
        $other_centre_id = DB::table('departments')->insertGetId(['code' => 'cnx', 'name' => 'Chiang Mai']);
        $this->assertSame('CNXCN2608001', $generator->Next($other_centre_id, $date));
    }

    #[Test]
    public function AllowsNumbersAbove999AndRestoresSequenceOnOuterRollback(): void
    {
        $centre_id = DB::table('departments')->insertGetId(['code' => 'BKK', 'name' => 'Bangkok']);
        DB::table('credit_note_running_numbers')->insert([
            'centre_id' => $centre_id,
            'year' => 2026,
            'month' => 8,
            'number' => 999,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $generator = app(CreditNoteNumberGenerator::class);

        $this->assertSame('BKKCN26081000', $generator->Next($centre_id, CarbonImmutable::parse('2026-08-12', 'Asia/Bangkok')));

        try {
            DB::transaction(function () use ($generator, $centre_id): void {
                $generator->Next($centre_id, CarbonImmutable::parse('2026-09-12', 'Asia/Bangkok'));
                throw new \RuntimeException('rollback');
            });
        } catch (\RuntimeException) {
            $this->addToAssertionCount(1);
        }

        $this->assertFalse(DB::table('credit_note_running_numbers')->where('centre_id', $centre_id)->where('month', 9)->exists());
    }

    #[Test]
    public function RejectsMissingOrUnsafeCentreCodes(): void
    {
        $invalid_id = DB::table('departments')->insertGetId(['code' => 'BKK/1', 'name' => 'Invalid']);
        $generator = app(CreditNoteNumberGenerator::class);

        $this->expectException(\InvalidArgumentException::class);
        $generator->Next($invalid_id, CarbonImmutable::parse('2026-08-12', 'Asia/Bangkok'));
    }
}
