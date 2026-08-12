<?php

namespace Tests\Unit\CreditNote;

use App\Domain\CreditNote\Enums\TaxTreatment;
use App\Domain\CreditNote\ValueObjects\CreditNoteCalculation;
use App\Domain\CreditNote\ValueObjects\CreditNoteLineCalculationInput;
use App\Domain\CreditNote\Services\CreditNoteCalculator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreditNoteCalculatorTest extends TestCase
{
    #[Test]
    public function CalculatesVatInclusiveCreditAndCorrectedTotals(): void
    {
        $calculation = app(CreditNoteCalculator::class)->Calculate([
            new CreditNoteLineCalculationInput(10, 21400, 10700, TaxTreatment::VAT_INCLUSIVE, 700),
        ]);

        $this->assertInstanceOf(CreditNoteCalculation::class, $calculation);
        $this->assertSame(20000, $calculation->Original()->taxable_base_minor);
        $this->assertSame(1400, $calculation->Original()->vat_minor);
        $this->assertSame(21400, $calculation->Original()->grand_total_minor);
        $this->assertSame(10000, $calculation->Corrected()->taxable_base_minor);
        $this->assertSame(700, $calculation->Corrected()->vat_minor);
        $this->assertSame(10700, $calculation->Difference()->grand_total_minor);
        $this->assertSame(700, $calculation->LineAllocations()[10]['credit_vat_minor']);
    }

    #[Test]
    public function CalculatesExemptOutOfScopeAndPriorNetCredit(): void
    {
        $calculation = app(CreditNoteCalculator::class)->Calculate([
            new CreditNoteLineCalculationInput(2, 5000, 1000, TaxTreatment::VAT_EXEMPT, 0, 1000),
            new CreditNoteLineCalculationInput(3, 3000, 500, TaxTreatment::OUT_OF_SCOPE, 0),
        ]);

        $this->assertSame(8000, $calculation->Original()->grand_total_minor);
        $this->assertSame(1500, $calculation->Difference()->grand_total_minor);
        $this->assertSame(5500, $calculation->Corrected()->grand_total_minor);
        $this->assertSame(1000, $calculation->Difference()->exempt_minor);
        $this->assertSame(500, $calculation->Difference()->out_of_scope_minor);
    }

    #[Test]
    public function AllocatesVatGroupResidualInAscendingItemIdOrder(): void
    {
        $calculation = app(CreditNoteCalculator::class)->Calculate([
            new CreditNoteLineCalculationInput(20, 100, 100, TaxTreatment::VAT_INCLUSIVE, 700),
            new CreditNoteLineCalculationInput(10, 100, 100, TaxTreatment::VAT_INCLUSIVE, 700),
        ]);

        $this->assertSame(13, $calculation->Difference()->vat_minor);
        $this->assertSame(6, $calculation->LineAllocations()[10]['credit_vat_minor']);
        $this->assertSame(7, $calculation->LineAllocations()[20]['credit_vat_minor']);
    }

    #[Test]
    public function RejectsDuplicateInvalidAndOverCreditInputs(): void
    {
        $calculator = app(CreditNoteCalculator::class);

        $this->expectException(\InvalidArgumentException::class);
        $calculator->Calculate([
            new CreditNoteLineCalculationInput(1, 100, 50, TaxTreatment::VAT_EXEMPT, 700),
        ]);
    }

    #[Test]
    public function RejectsDuplicateAndOverCreditSeparately(): void
    {
        $calculator = app(CreditNoteCalculator::class);

        try {
            $calculator->Calculate([
                new CreditNoteLineCalculationInput(1, 100, 50, TaxTreatment::VAT_EXEMPT, 0),
                new CreditNoteLineCalculationInput(1, 100, 20, TaxTreatment::VAT_EXEMPT, 0),
            ]);
            $this->fail('Duplicate item IDs must be rejected.');
        } catch (\InvalidArgumentException) {
            $this->addToAssertionCount(1);
        }

        $this->expectException(\InvalidArgumentException::class);
        $calculator->Calculate([
            new CreditNoteLineCalculationInput(1, 100, 101, TaxTreatment::VAT_EXEMPT, 0),
        ]);
    }
}
