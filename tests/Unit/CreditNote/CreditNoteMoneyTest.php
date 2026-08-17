<?php

namespace Tests\Unit\CreditNote;

use App\Domain\CreditNote\ValueObjects\CreditNoteMoney;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreditNoteMoneyTest extends TestCase
{
    #[Test]
    public function ParsesSignedDecimalStringsToExactSatang(): void
    {
        $this->assertSame(0, CreditNoteMoney::FromDecimal('0')->Minor());
        $this->assertSame(5, CreditNoteMoney::FromDecimal('0.05')->Minor());
        $this->assertSame(1234, CreditNoteMoney::FromDecimal('12.34')->Minor());
        $this->assertSame(-125, CreditNoteMoney::FromDecimal('-1.25')->Minor());
        $this->assertSame(PHP_INT_MIN, CreditNoteMoney::FromDecimal('-92233720368547758.08')->Minor());
        $this->assertSame('12.34', CreditNoteMoney::FromMinor(1234)->Decimal());
        $this->assertSame('-1.25', CreditNoteMoney::FromMinor(-125)->Decimal());
        $this->assertSame('-92233720368547758.08', CreditNoteMoney::FromMinor(PHP_INT_MIN)->Decimal());
    }

    #[Test]
    public function RejectsInvalidScaleSignsAndOverflow(): void
    {
        foreach (['', '1.234', '+1.00', '1.', 'abc', ' 1.00'] as $amount) {
            try {
                CreditNoteMoney::FromDecimal($amount);
                $this->fail("Expected invalid amount to be rejected: {$amount}");
            } catch (\InvalidArgumentException) {
                $this->addToAssertionCount(1);
            }
        }

        $this->expectException(\InvalidArgumentException::class);
        CreditNoteMoney::FromDecimal('-0.01', true);
    }

    #[Test]
    public function RejectsValuesOutsideSigned64BitMinorUnits(): void
    {
        $this->expectException(\OverflowException::class);
        CreditNoteMoney::FromDecimal('92233720368547758.08');
    }
}
