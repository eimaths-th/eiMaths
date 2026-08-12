<?php

namespace App\Domain\CreditNote\ValueObjects;

final class CreditNoteMoney
{
    private function __construct(private readonly int $minor)
    {
    }

    public static function FromDecimal(string $amount, bool $require_non_negative = false): self
    {
        if (!preg_match('/\A-?(?:0|[0-9]+)(?:\.[0-9]{1,2})?\z/D', $amount)) {
            throw new \InvalidArgumentException('Money must be a decimal string with at most two fractional digits.');
        }

        $is_negative = str_starts_with($amount, '-');
        if ($require_non_negative && $is_negative) {
            throw new \InvalidArgumentException('Money must not be negative.');
        }

        $unsigned_amount = $is_negative ? substr($amount, 1) : $amount;
        [$whole_part, $fraction_part] = array_pad(explode('.', $unsigned_amount, 2), 2, '');
        $whole_part = ltrim($whole_part, '0');
        $whole_part = $whole_part === '' ? '0' : $whole_part;
        $fraction_part = str_pad($fraction_part, 2, '0');

        $maximum_whole_part = '92233720368547758';
        $maximum_fraction_part = $is_negative ? '08' : '07';
        self::AssertWithinRange($whole_part, $fraction_part, $maximum_whole_part, $maximum_fraction_part);

        if ($is_negative && $whole_part === $maximum_whole_part && $fraction_part === '08') {
            return new self(PHP_INT_MIN);
        }

        $whole_minor = (int) $whole_part * 100;
        $minor = $whole_minor + (int) $fraction_part;

        if ($is_negative) {
            return new self(-$minor);
        }

        return new self($minor);
    }

    public static function FromMinor(int $amount_minor): self
    {
        return new self($amount_minor);
    }

    public function Minor(): int
    {
        return $this->minor;
    }

    public function Decimal(): string
    {
        if ($this->minor === PHP_INT_MIN) {
            return '-92233720368547758.08';
        }

        $is_negative = $this->minor < 0;
        $absolute_minor = abs($this->minor);
        $whole_part = intdiv($absolute_minor, 100);
        $fraction_part = str_pad((string) ($absolute_minor % 100), 2, '0', STR_PAD_LEFT);

        return ($is_negative ? '-' : '') . $whole_part . '.' . $fraction_part;
    }

    private static function AssertWithinRange(
        string $whole_part,
        string $fraction_part,
        string $maximum_whole_part,
        string $maximum_fraction_part,
    ): void {
        if (
            strlen($whole_part) > strlen($maximum_whole_part)
            || (
                strlen($whole_part) === strlen($maximum_whole_part)
                && ($whole_part > $maximum_whole_part
                    || ($whole_part === $maximum_whole_part && $fraction_part > $maximum_fraction_part))
            )
        ) {
            throw new \OverflowException('Money exceeds signed 64-bit minor-unit range.');
        }
    }
}
