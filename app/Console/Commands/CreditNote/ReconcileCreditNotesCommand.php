<?php

namespace App\Console\Commands\CreditNote;

use App\Domain\CreditNote\Services\CreditNoteReconciliationService;
use Illuminate\Console\Command;

final class ReconcileCreditNotesCommand extends Command
{
    protected $signature = 'credit-notes:reconcile {--batch=100} {--json}';
    protected $description = 'Reconcile Credit Note evidence without repairing it.';

    public function handle(CreditNoteReconciliationService $service): int
    {
        $batch = (int) $this->option('batch');
        if ($batch < 1 || $batch > 1000) {
            $this->error('Batch must be between 1 and 1000.');
            return self::FAILURE;
        }
        $result = $service->Reconcile($batch);
        if ($this->option('json')) {
            $this->line(json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        } else {
            $this->table(['Metric', 'Value'], collect($result)->except('mismatches')->map(fn ($value, $key): array => [$key, is_scalar($value) ? $value : json_encode($value)])->values()->all());
        }
        return (int) ($result['mismatch_count'] ?? 0) > 0 ? self::FAILURE : self::SUCCESS;
    }
}
