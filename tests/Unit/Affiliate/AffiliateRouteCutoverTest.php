<?php

namespace Tests\Unit\Affiliate;

use Tests\TestCase;

class AffiliateRouteCutoverTest extends TestCase
{
    public function test_legacy_financial_routes_are_not_registered(): void
    {
        $routes = file_get_contents(base_path('routes/web2.php'));

        $this->assertStringNotContainsString("Route::resource('/affiliate/my_affiliate/withdraw'", $routes);
        $this->assertStringNotContainsString("balance-transfer-to-wallet", $routes);
        $this->assertStringNotContainsString("destroy/withdraw_request", $routes);
        $this->assertStringContainsString("affiliate.withdrawals.store", file_get_contents(base_path('routes/affiliate.php')));
    }
}
