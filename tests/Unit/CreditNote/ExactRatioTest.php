<?php

namespace Tests\Unit\CreditNote;

use App\Domain\CreditNote\ValueObjects\ExactRatio;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExactRatioTest extends TestCase
{
    #[Test]
    public function MultipliesDividesAndRoundsHalfUpWithSignedInputs(): void
    {
        $this->assertSame(1, ExactRatio::MultiplyDivideHalfUp(1, 1, 2));
        $this->assertSame(2, ExactRatio::MultiplyDivideHalfUp(3, 1, 2));
        $this->assertSame(7, ExactRatio::MultiplyDivideHalfUp(100, 700, 10700));
        $this->assertSame(-2, ExactRatio::MultiplyDivideHalfUp(-3, 1, 2));
    }

    #[Test]
    public function RejectsInvalidDivisor(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        ExactRatio::MultiplyDivideHalfUp(1, 1, 0);
    }

    #[Test]
    public function RejectsMultiplicationOverflow(): void
    {
        $this->expectException(\OverflowException::class);
        ExactRatio::MultiplyDivideHalfUp(PHP_INT_MAX, 2, 1);
    }

    #[Test]
    public function DividesBeforeConvertingAProductThatWouldExceedSigned64Bit(): void
    {
        $this->assertSame(
            603398170635359165,
            ExactRatio::MultiplyDivideHalfUp(PHP_INT_MAX, 700, 10700)
        );
    }
}
