<?php

namespace App\Domain\Affiliate\ValueObjects;

use InvalidArgumentException;

final class Money
{
    private const MAX_POSITIVE_MAJOR = 92233720368547758;
    private const MAX_POSITIVE_MINOR_REMAINDER = 7;
    private const MAX_NEGATIVE_MINOR_REMAINDER = 8;

    public function __construct(private readonly int $amount_minor)
    {
    }

    public static function FromMinor(int $amount_minor): self
    {
        return new self($amount_minor);
    }

    public static function FromDecimal(string $amount, bool $require_non_negative = false): self
    {
        if (!preg_match('/\A(-?)(\d+)(?:\.(\d{1,2}))?\z/D', $amount, $matches)) {
            throw new InvalidArgumentException('Money must be a decimal string with at most two fractional digits.');
        }

        $is_negative = $matches[1] === '-';
        if ($is_negative && $require_non_negative) {
            throw new InvalidArgumentException('Money must not be negative.');
        }

        $major_digits = ltrim($matches[2], '0');
        $major_digits = $major_digits === '' ? '0' : $major_digits;
        if (strlen($major_digits) > strlen((string) self::MAX_POSITIVE_MAJOR)
            || (strlen($major_digits) === strlen((string) self::MAX_POSITIVE_MAJOR)
                && strcmp($major_digits, (string) self::MAX_POSITIVE_MAJOR) > 0)) {
            throw new InvalidArgumentException('Money exceeds the supported integer range.');
        }

        $fractional_digits = str_pad($matches[3] ?? '', 2, '0');
        $fractional_minor = (int) $fractional_digits;
        $major_minor = ((int) $major_digits) * 100;

        if ($is_negative && (int) $major_digits === self::MAX_POSITIVE_MAJOR
            && $fractional_minor > self::MAX_NEGATIVE_MINOR_REMAINDER) {
            throw new InvalidArgumentException('Money exceeds the supported integer range.');
        }

        if (!$is_negative && (int) $major_digits === self::MAX_POSITIVE_MAJOR
            && $fractional_minor > self::MAX_POSITIVE_MINOR_REMAINDER) {
            throw new InvalidArgumentException('Money exceeds the supported integer range.');
        }

        $minor_abs = $major_minor + $fractional_minor;
        if ($is_negative && (int) $major_digits === self::MAX_POSITIVE_MAJOR
            && $fractional_minor === self::MAX_NEGATIVE_MINOR_REMAINDER) {
            return new self(PHP_INT_MIN);
        }

        return new self($is_negative ? -$minor_abs : $minor_abs);
    }

    public function Minor(): int
    {
        return $this->amount_minor;
    }

    public function Decimal(): string
    {
        if ($this->amount_minor === PHP_INT_MIN) {
            return '-92233720368547758.08';
        }

        $is_negative = $this->amount_minor < 0;
        $minor_abs = $is_negative ? -$this->amount_minor : $this->amount_minor;
        $major = intdiv($minor_abs, 100);
        $fraction = str_pad((string) ($minor_abs % 100), 2, '0', STR_PAD_LEFT);

        return ($is_negative ? '-' : '') . $major . '.' . $fraction;
    }

    public function __toString(): string
    {
        return $this->Decimal();
    }
}
