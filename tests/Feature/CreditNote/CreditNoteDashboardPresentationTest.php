<?php

namespace Tests\Feature\CreditNote;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreditNoteDashboardPresentationTest extends TestCase
{
    #[Test]
    public function dashboard_keeps_existing_receipt_ids_and_adds_explicit_credit_note_revenue_cards(): void
    {
        $home = file_get_contents(base_path('resources/views/home.blade.php'));
        $script = file_get_contents(base_path('resources/views/script.blade.php'));
        $this->assertStringContainsString('id="receipt_sum_by_date"', $home);
        $this->assertStringContainsString('id="credit-note-issued"', $home);
        $this->assertStringContainsString('Gross Revenue', $home);
        $this->assertStringContainsString('Cancelled Credit Notes', $home);
        $this->assertStringContainsString("response.net_revenue", $script);
        $this->assertStringContainsString("name: 'Net Revenue'", $script);
    }
}
