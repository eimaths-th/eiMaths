<?php

namespace App\Domain\Affiliate\Services;

use App\Domain\Affiliate\Enums\AffiliateReconciliationStatus;
use App\Domain\Affiliate\Models\AffiliateAuditLog;
use App\Domain\Affiliate\Models\AffiliateReconciliationIssue;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AffiliateAuditService
{
    public function Record(
        string $operation,
        string $subject_type,
        ?int $subject_id,
        ?User $actor,
        array $prior_state,
        array $resulting_state,
        ?int $amount_minor,
        string $outcome,
        ?string $error_code,
        string $correlation_id,
    ): AffiliateAuditLog {
        return AffiliateAuditLog::create([
            'correlation_id' => $correlation_id,
            'operation' => $operation,
            'subject_type' => $subject_type,
            'subject_id' => $subject_id,
            'actor_id' => $actor?->id,
            'actor_name_snapshot' => $actor?->name,
            'prior_state' => $this->Sanitize($prior_state),
            'resulting_state' => $this->Sanitize($resulting_state),
            'amount_minor' => $amount_minor,
            'outcome' => $outcome,
            'error_code' => $error_code,
            'occurred_at' => now(),
        ]);
    }

    public function OpenIssue(
        string $source_table,
        int $source_id,
        string $issue_code,
        string $severity,
        array $evidence = [],
    ): AffiliateReconciliationIssue {
        return AffiliateReconciliationIssue::firstOrCreate(
            [
                'source_table' => $source_table,
                'source_id' => $source_id,
                'issue_code' => $issue_code,
            ],
            [
                'severity' => $severity,
                'evidence' => $this->Sanitize($evidence),
                'status' => AffiliateReconciliationStatus::OPEN,
            ],
        );
    }

    public function EmitSignal(string $signal, string $correlation_id, array $context = []): void
    {
        Log::warning('affiliate.' . $signal, $this->Sanitize($context) + [
            'correlation_id' => $correlation_id,
            'signal' => $signal,
        ]);
    }

    private function Sanitize(array $value): array
    {
        $sanitized = [];
        foreach ($value as $key => $item) {
            $normalized_key = strtolower((string) $key);
            if (preg_match('/password|token|cookie|secret|account[_-]?number|promptpay[_-]?identifier|details[_-]?encrypted|payout[_-]?snapshot|national[_-]?id|identification|card|document|address|postal/', $normalized_key)) {
                continue;
            }
            $sanitized[$key] = is_array($item) ? $this->Sanitize($item) : $item;
        }

        return $sanitized;
    }
}
