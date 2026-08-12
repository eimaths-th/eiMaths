<?php

namespace Tests\Feature\CreditNote;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreditNoteConcurrencyTest extends TestCase
{
    #[Test]
    public function mysql_concurrency_scenarios_are_explicitly_skipped_without_opt_in(): void
    {
        $run = filter_var((string) env('CREDIT_NOTE_RUN_CONCURRENCY_TESTS', 'false'), FILTER_VALIDATE_BOOLEAN);
        $database = (string) env('CREDIT_NOTE_CONCURRENCY_DATABASE', '');
        $configured_database = (string) config('database.connections.' . config('database.default') . '.database');
        if (!$run || (string) config('database.default') !== 'mysql' || app()->environment('production') || $database === '' || $database !== $configured_database) {
            $this->markTestSkipped('Credit Note concurrency tests require explicit isolated MySQL opt-in.');
        }

        $this->fail('The isolated MySQL worker scenarios require an authorized staging run.');
    }
}
