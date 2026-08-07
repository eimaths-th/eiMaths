<?php

namespace App\Console\Commands\Affiliate;

use App\Domain\Affiliate\Services\AffiliateCommissionService;
use Illuminate\Console\Command;

class ReleaseAffiliateCommissionsCommand extends Command
{
    protected $signature = 'affiliate:release-commissions {--batch=100}';

    protected $description = 'Release matured Affiliate commissions into wallet ledgers.';

    public function handle(AffiliateCommissionService $commission_service): int
    {
        $batch = (int) $this->option('batch');
        if ($batch < 1 || $batch > 1000) {
            $this->error('Batch must be between 1 and 1000.');
            return self::INVALID;
        }

        $processed = $commission_service->ReleaseMatured($batch);
        $this->info("Processed {$processed} matured Affiliate commissions.");
        return self::SUCCESS;
    }
}
