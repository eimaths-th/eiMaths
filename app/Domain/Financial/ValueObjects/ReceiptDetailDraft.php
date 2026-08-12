<?php

namespace App\Domain\Financial\ValueObjects;

use OverflowException;

final readonly class ReceiptDetailDraft
{
    public function __construct(
        public string $description,
        public string $unit,
        public int $quantity,
        public int $unit_price_minor,
        public int $discount_minor,
        public int $legacy_tax,
    ) {
    }

    public function GrossMinor(): int
    {
        if ($this->quantity !== 0 && $this->unit_price_minor > intdiv(PHP_INT_MAX, $this->quantity)) {
            throw new OverflowException('Receipt detail gross amount exceeds the supported range.');
        }

        return $this->quantity * $this->unit_price_minor;
    }

    public function NetMinor(): int
    {
        $gross_minor = $this->GrossMinor();
        if ($this->discount_minor > $gross_minor) {
            throw new OverflowException('Receipt detail discount exceeds the gross amount.');
        }

        return $gross_minor - $this->discount_minor;
    }

    public function UnitPriceDecimal(): string
    {
        return self::FormatMinor($this->unit_price_minor);
    }

    public function DiscountDecimal(): string
    {
        return self::FormatMinor($this->discount_minor);
    }

    private static function FormatMinor(int $amount_minor): string
    {
        if ($amount_minor < 0) {
            throw new OverflowException('Receipt detail monetary values must not be negative.');
        }

        $whole_part = intdiv($amount_minor, 100);
        $fraction_part = str_pad((string) ($amount_minor % 100), 2, '0', STR_PAD_LEFT);

        return $whole_part . '.' . $fraction_part;
    }
}
