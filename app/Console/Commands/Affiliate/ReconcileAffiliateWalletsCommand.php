<?php

namespace App\Console\Commands\Affiliate;

use App\Domain\Affiliate\Services\AffiliateReconciliationService;
use Illuminate\Console\Command;

class ReconcileAffiliateWalletsCommand extends Command
{
    protected $signature = 'affiliate:reconcile-wallets {--batch=100}';
    protected $description = 'Reconcile Affiliate wallet projections against append-only ledger entries.';

    public function handle(AffiliateReconciliationService $reconciliation_service): int
    {
        $batch = (int) $this->option('batch');
        if ($batch < 1 || $batch > 1000) {
            $this->error('Batch must be between 1 and 1000.');
            return self::INVALID;
        }
        $processed = $reconciliation_service->ReconcileAll($batch);
        $this->info("Reconciled {$processed} Affiliate wallets.");
        return self::SUCCESS;
    }
}
