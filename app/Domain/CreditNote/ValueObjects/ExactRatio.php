<?php

namespace App\Domain\CreditNote\ValueObjects;

final class ExactRatio
{
    public static function MultiplyDivideHalfUp(int $left, int $right, int $divisor): int
    {
        if ($divisor <= 0) {
            throw new \InvalidArgumentException('The ratio divisor must be positive.');
        }

        $is_negative = ($left < 0) xor ($right < 0);
        $product = self::MultiplyUnsigned(self::Magnitude($left), self::Magnitude($right));
        [$quotient, $remainder] = self::DivideUnsigned($product, (string) $divisor);

        if (self::CompareUnsigned(self::AddUnsigned($remainder, $remainder), (string) $divisor) >= 0) {
            $quotient = self::AddUnsigned($quotient, '1');
        }

        $maximum_result = $is_negative ? '9223372036854775808' : '9223372036854775807';
        if (self::CompareUnsigned($quotient, $maximum_result) > 0) {
            throw new \OverflowException('The ratio result exceeds signed 64-bit range.');
        }

        if ($is_negative && $quotient === '9223372036854775808') {
            return PHP_INT_MIN;
        }

        $result = (int) $quotient;

        return $is_negative ? -$result : $result;
    }

    private static function Magnitude(int $value): string
    {
        return $value === PHP_INT_MIN ? '9223372036854775808' : (string) abs($value);
    }

    private static function MultiplyUnsigned(string $left, string $right): string
    {
        if ($left === '0' || $right === '0') {
            return '0';
        }

        $left_digits = array_map('intval', str_split($left));
        $right_digits = array_map('intval', str_split($right));
        $result = array_fill(0, count($left_digits) + count($right_digits), 0);

        for ($left_index = count($left_digits) - 1; $left_index >= 0; $left_index--) {
            for ($right_index = count($right_digits) - 1; $right_index >= 0; $right_index--) {
                $result[$left_index + $right_index + 1] +=
                    $left_digits[$left_index] * $right_digits[$right_index];
            }
        }

        for ($index = count($result) - 1; $index > 0; $index--) {
            $carry = intdiv($result[$index], 10);
            $result[$index] %= 10;
            $result[$index - 1] += $carry;
        }

        while (count($result) > 1 && $result[0] >= 10) {
            array_unshift($result, intdiv($result[0], 10));
            $result[1] %= 10;
        }

        return self::NormalizeUnsigned(implode('', $result));
    }

    private static function DivideUnsigned(string $dividend, string $divisor): array
    {
        $quotient = '';
        $remainder = '0';
        foreach (str_split($dividend) as $digit) {
            $remainder = self::NormalizeUnsigned($remainder . $digit);
            $quotient_digit = 0;
            while (self::CompareUnsigned($remainder, $divisor) >= 0) {
                $remainder = self::SubtractUnsigned($remainder, $divisor);
                $quotient_digit++;
            }
            $quotient .= (string) $quotient_digit;
        }

        return [self::NormalizeUnsigned($quotient), self::NormalizeUnsigned($remainder)];
    }

    private static function AddUnsigned(string $left, string $right): string
    {
        $left_index = strlen($left) - 1;
        $right_index = strlen($right) - 1;
        $carry = 0;
        $digits = [];
        while ($left_index >= 0 || $right_index >= 0 || $carry > 0) {
            $sum = $carry;
            if ($left_index >= 0) {
                $sum += (int) $left[$left_index--];
            }
            if ($right_index >= 0) {
                $sum += (int) $right[$right_index--];
            }
            $digits[] = (string) ($sum % 10);
            $carry = intdiv($sum, 10);
        }

        return strrev(implode('', $digits));
    }

    private static function SubtractUnsigned(string $left, string $right): string
    {
        $left_index = strlen($left) - 1;
        $right_index = strlen($right) - 1;
        $borrow = 0;
        $digits = [];
        while ($left_index >= 0) {
            $difference = (int) $left[$left_index--] - $borrow;
            if ($right_index >= 0) {
                $difference -= (int) $right[$right_index--];
            }
            if ($difference < 0) {
                $difference += 10;
                $borrow = 1;
            } else {
                $borrow = 0;
            }
            $digits[] = (string) $difference;
        }

        return self::NormalizeUnsigned(strrev(implode('', $digits)));
    }

    private static function CompareUnsigned(string $left, string $right): int
    {
        $left = self::NormalizeUnsigned($left);
        $right = self::NormalizeUnsigned($right);
        if (strlen($left) !== strlen($right)) {
            return strlen($left) <=> strlen($right);
        }

        return $left <=> $right;
    }

    private static function NormalizeUnsigned(string $value): string
    {
        $normalized = ltrim($value, '0');

        return $normalized === '' ? '0' : $normalized;
    }
}
