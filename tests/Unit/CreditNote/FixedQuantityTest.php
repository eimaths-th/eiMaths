<?php

namespace Tests\Unit\CreditNote;

use App\Domain\CreditNote\ValueObjects\FixedQuantity;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FixedQuantityTest extends TestCase
{
    #[Test]
    public function ParsesQuantitiesAtHundredthsWithoutFloatingPoint(): void
    {
        $this->assertSame(0, FixedQuantity::FromDecimal('0')->Hundredths());
        $this->assertSame(5, FixedQuantity::FromDecimal('0.05')->Hundredths());
        $this->assertSame(125, FixedQuantity::FromDecimal('1.25')->Hundredths());
        $this->assertSame('1.25', FixedQuantity::FromDecimal('1.25')->Decimal());
    }

    #[Test]
    public function RejectsNegativeThreeDecimalAndOverflowQuantities(): void
    {
        foreach (['-1.00', '1.234', '1.', ''] as $quantity) {
            $this->expectException(\InvalidArgumentException::class);
            FixedQuantity::FromDecimal($quantity);
        }
    }
}
