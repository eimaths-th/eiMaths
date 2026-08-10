<?php

namespace App\Console\Commands\Affiliate;

use App\Domain\Affiliate\Services\AffiliatePreflightService;
use Illuminate\Console\Command;

class AffiliatePreflightCommand extends Command
{
    protected $signature = 'affiliate:preflight {--json : Print machine-readable JSON output.}';

    protected $description = 'Check Affiliate launch configuration and report external evidence gaps without changing data.';

    public function handle(AffiliatePreflightService $preflight_service): int
    {
        $checks = $preflight_service->Checks();
        $blocked = count(array_filter($checks, fn (array $check): bool => $check['status'] === 'BLOCKED'));
        $payload = [
            'blocked' => $blocked,
            'checks' => $checks,
        ];

        if ((bool) $this->option('json')) {
            $this->line((string) json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        } else {
            foreach ($checks as $check) {
                $this->line("[{$check['status']}] {$check['name']}: {$check['message']}");
            }
            $this->newLine();
            $this->line("Blocked checks: {$blocked}");
        }

        return $blocked > 0 ? self::FAILURE : self::SUCCESS;
    }
}
