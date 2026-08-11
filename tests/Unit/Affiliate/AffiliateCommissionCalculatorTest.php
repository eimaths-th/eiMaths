<?php

namespace Tests\Unit\Affiliate;

use App\Domain\Affiliate\Services\AffiliateCommissionCalculator;
use App\Models\Order;
use App\Models\OrderDetailList;
use Tests\TestCase;

class AffiliateCommissionCalculatorTest extends TestCase
{
    public function test_calculator_uses_only_tuition_prices_clamps_discount_and_rounds_half_up(): void
    {
        $order = new Order(['discount' => '0.01']);
        $order->forceFill(['affiliate_commission_rate_basis_points' => 2000]);
        $order->setRelation('orderDetailLists', collect([
            new OrderDetailList(['price' => '100.01', 'bprice' => '900.00']),
            new OrderDetailList(['price' => '0.02', 'bprice' => '800.00']),
        ]));

        $calculation = app(AffiliateCommissionCalculator::class)->Calculate($order);

        $this->assertSame(10003, $calculation->gross_tuition_minor);
        $this->assertSame(1, $calculation->tuition_discount_minor);
        $this->assertSame(10002, $calculation->eligible_tuition_minor);
        $this->assertSame(2000, $calculation->rate_basis_points);
        $this->assertSame(2000, $calculation->commission_minor);
    }

    public function test_over_discount_produces_zero_eligible_tuition_and_zero_commission(): void
    {
        $order = new Order(['discount' => '100.00']);
        $order->forceFill(['affiliate_commission_rate_basis_points' => 2000]);
        $order->setRelation('orderDetailLists', collect([
            new OrderDetailList(['price' => '20.00']),
        ]));

        $calculation = app(AffiliateCommissionCalculator::class)->Calculate($order);

        $this->assertSame(0, $calculation->eligible_tuition_minor);
        $this->assertSame(0, $calculation->commission_minor);
    }
}
