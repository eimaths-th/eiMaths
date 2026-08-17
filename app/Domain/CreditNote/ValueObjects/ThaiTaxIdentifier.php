<?php

namespace App\Domain\CreditNote\ValueObjects;

final class ThaiTaxIdentifier
{
    private function __construct(private readonly string $digits)
    {
    }

    public static function FromInput(string $tax_id): self
    {
        $digits = str_replace([' ', '-'], '', $tax_id);
        if (!preg_match('/\A[0-9]{13}\z/D', $digits)) {
            throw new \InvalidArgumentException('Thai tax identifier must contain exactly thirteen ASCII digits.');
        }

        $sum = 0;
        for ($index = 0; $index < 12; $index++) {
            $sum += ((int) $digits[$index]) * (13 - $index);
        }

        $expected_check_digit = (11 - ($sum % 11)) % 10;
        if ($expected_check_digit !== (int) $digits[12]) {
            throw new \InvalidArgumentException('Thai tax identifier checksum is invalid.');
        }

        return new self($digits);
    }

    public function Digits(): string
    {
        return $this->digits;
    }
}
