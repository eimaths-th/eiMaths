<?php

namespace App\Domain\Affiliate\Services;

use App\Domain\Affiliate\Exceptions\AffiliateDomainException;
use App\Domain\Affiliate\ValueObjects\CommissionCalculation;
use App\Domain\Affiliate\ValueObjects\Money;
use App\Models\Order;

class AffiliateCommissionCalculator
{
    public function Calculate(Order $order): CommissionCalculation
    {
        $rate_basis_points = $order->affiliate_commission_rate_basis_points;
        if ($rate_basis_points === null) {
            throw AffiliateDomainException::ConfigurationInvalid('Order commission rate snapshot is missing.');
        }

        $gross_tuition_minor = 0;
        foreach ($order->orderDetailLists as $detail) {
            $price_minor = Money::FromDecimal((string) ($detail->price ?? '0'), true)->Minor();
            if (PHP_INT_MAX - $gross_tuition_minor < $price_minor) {
                throw AffiliateDomainException::ConfigurationInvalid('Eligible tuition exceeds the supported range.');
            }
            $gross_tuition_minor += $price_minor;
        }

        $discount_minor = Money::FromDecimal((string) ($order->discount ?? '0'), true)->Minor();
        $eligible_tuition_minor = max($gross_tuition_minor - $discount_minor, 0);

        return CommissionCalculation::FromValues(
            gross_tuition_minor: $gross_tuition_minor,
            tuition_discount_minor: $discount_minor,
            eligible_tuition_minor: $eligible_tuition_minor,
            rate_basis_points: (int) $rate_basis_points,
        );
    }
}
