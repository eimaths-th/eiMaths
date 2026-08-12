<?php

namespace App\Console\Commands\CreditNote;

use App\Domain\CreditNote\Services\CreditNoteSourceAuditService;
use Illuminate\Console\Command;

final class AuditCreditNoteSourcesCommand extends Command
{
    protected $signature = 'credit-notes:audit-sources {--batch=500} {--json}';
    protected $description = 'Audit Credit Note source metadata without changing financial data.';

    public function handle(CreditNoteSourceAuditService $service): int
    {
        $batch = (int) $this->option('batch');
        if ($batch < 1 || $batch > 1000) {
            $this->error('Batch must be between 1 and 1000.');
            return self::FAILURE;
        }
        $result = $service->Audit($batch);
        if ($this->option('json')) {
            $this->line(json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        } else {
            $this->table(['Metric', 'Value'], collect($result)->except('mismatches')->map(fn ($value, $key): array => [$key, is_scalar($value) ? $value : json_encode($value)])->values()->all());
        }
        return (int) ($result['mismatch_count'] ?? 0) > 0 ? self::FAILURE : self::SUCCESS;
    }
}
