<?php

namespace App\Domain\Affiliate\ValueObjects;

use InvalidArgumentException;

final class CommissionCalculation
{
    public readonly int $gross_tuition_minor;
    public readonly int $tuition_discount_minor;
    public readonly int $eligible_tuition_minor;
    public readonly int $rate_basis_points;
    public readonly int $commission_minor;

    public function __construct(
        int $gross_tuition_minor,
        int $tuition_discount_minor,
        int $eligible_tuition_minor,
        int $rate_basis_points,
        int $commission_minor,
    ) {
        foreach ([
            $gross_tuition_minor,
            $tuition_discount_minor,
            $eligible_tuition_minor,
            $rate_basis_points,
            $commission_minor,
        ] as $value) {
            if ($value < 0) {
                throw new InvalidArgumentException('Commission values cannot be negative.');
            }
        }

        if ($rate_basis_points > 10000) {
            throw new InvalidArgumentException('Commission rate must be between 0 and 10,000 basis points.');
        }

        if ($eligible_tuition_minor !== max($gross_tuition_minor - $tuition_discount_minor, 0)) {
            throw new InvalidArgumentException('Eligible tuition does not match gross tuition and discount.');
        }

        if ($commission_minor !== self::CalculateCommissionMinor($eligible_tuition_minor, $rate_basis_points)) {
            throw new InvalidArgumentException('Commission amount does not match the approved rate calculation.');
        }

        $this->gross_tuition_minor = $gross_tuition_minor;
        $this->tuition_discount_minor = $tuition_discount_minor;
        $this->eligible_tuition_minor = $eligible_tuition_minor;
        $this->rate_basis_points = $rate_basis_points;
        $this->commission_minor = $commission_minor;
    }

    public static function FromValues(
        int $gross_tuition_minor,
        int $tuition_discount_minor,
        int $eligible_tuition_minor,
        int $rate_basis_points,
    ): self {
        return new self(
            $gross_tuition_minor,
            $tuition_discount_minor,
            $eligible_tuition_minor,
            $rate_basis_points,
            self::CalculateCommissionMinor($eligible_tuition_minor, $rate_basis_points),
        );
    }

    private static function CalculateCommissionMinor(int $eligible_tuition_minor, int $rate_basis_points): int
    {
        if ($eligible_tuition_minor < 0 || $rate_basis_points < 0 || $rate_basis_points > 10000) {
            throw new InvalidArgumentException('Commission inputs are out of range.');
        }

        $whole_units = intdiv($eligible_tuition_minor, 10000);
        $remainder = $eligible_tuition_minor % 10000;
        $whole_commission = $whole_units * $rate_basis_points;
        $rounded_remainder = intdiv(($remainder * $rate_basis_points) + 5000, 10000);

        return $whole_commission + $rounded_remainder;
    }
}
