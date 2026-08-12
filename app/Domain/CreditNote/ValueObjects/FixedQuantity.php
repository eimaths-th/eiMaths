<?php

namespace App\Domain\CreditNote\ValueObjects;

final class FixedQuantity
{
    private function __construct(private readonly int $hundredths)
    {
    }

    public static function FromDecimal(string $quantity): self
    {
        $money = CreditNoteMoney::FromDecimal($quantity, true);

        return new self($money->Minor());
    }

    public function Hundredths(): int
    {
        return $this->hundredths;
    }

    public function Decimal(): string
    {
        return CreditNoteMoney::FromMinor($this->hundredths)->Decimal();
    }
}
