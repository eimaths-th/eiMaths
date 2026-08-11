<?php

namespace Tests\Feature\Affiliate;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Symfony\Component\Console\Output\BufferedOutput;
use Tests\TestCase;

class AffiliatePreflightCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_preflight_reports_production_configuration_blockers_as_json(): void
    {
        Config::set('app.env', 'production');
        Config::set('app.debug', true);
        Config::set('mail.default', 'log');
        Config::set('queue.default', 'sync');

        $this->withoutMockingConsoleOutput();
        $output = new BufferedOutput();
        $exit_code = Artisan::call('affiliate:preflight', ['--json' => true], $output);
        $raw_output = $output->fetch();
        $payload = json_decode($raw_output, true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(1, $exit_code);
        $this->assertSame(2, $payload['blocked']);
        $this->assertSame('BLOCKED', $this->Check($payload, 'production_debug')['status']);
        $this->assertSame('BLOCKED', $this->Check($payload, 'registration_mail_driver')['status']);
        $this->assertSame('WARN', $this->Check($payload, 'queue_driver')['status']);
    }

    public function test_preflight_keeps_local_configuration_non_blocking_but_marks_external_evidence_unknown(): void
    {
        Config::set('app.env', 'local');
        Config::set('app.debug', true);
        Config::set('mail.default', 'log');
        Config::set('queue.default', 'sync');

        $this->withoutMockingConsoleOutput();
        $output = new BufferedOutput();
        $exit_code = Artisan::call('affiliate:preflight', ['--json' => true], $output);
        $raw_output = $output->fetch();
        $payload = json_decode($raw_output, true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(0, $exit_code);
        $this->assertSame(0, $payload['blocked']);
        $this->assertSame('WARN', $this->Check($payload, 'registration_mail_driver')['status']);
        $this->assertSame('UNKNOWN', $this->Check($payload, 'mail_delivery')['status']);
        $this->assertSame('UNKNOWN', $this->Check($payload, 'scheduler_host')['status']);
    }

    private function Check(array $payload, string $name): array
    {
        foreach ($payload['checks'] as $check) {
            if ($check['name'] === $name) {
                return $check;
            }
        }

        $this->fail("Missing preflight check: {$name}");
    }
}
