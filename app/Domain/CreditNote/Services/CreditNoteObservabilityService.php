<?php

namespace App\Domain\CreditNote\Services;

use Illuminate\Support\Facades\Log;

final class CreditNoteObservabilityService
{
    public function Record(string $operation, string $outcome, string $correlation_id, array $context = []): void
    {
        Log::info('credit_note.' . $operation, $this->Sanitize($context) + [
            'operation' => $operation,
            'outcome' => $outcome,
            'correlation_id' => $correlation_id,
        ]);
    }

    public function Signal(string $signal, string $correlation_id, array $context = []): void
    {
        Log::warning('credit_note.' . $signal, $this->Sanitize($context) + [
            'signal' => $signal,
            'correlation_id' => $correlation_id,
        ]);
    }

    private function Sanitize(array $context): array
    {
        $result = [];
        foreach ($context as $key => $value) {
            $normalized = strtolower((string) $key);
            if (preg_match('/password|token|cookie|secret|tax[_-]?id|buyer|address|telephone|phone|billing|snapshot|promptpay|bank/', $normalized)) {
                continue;
            }
            $result[$key] = is_array($value) ? $this->Sanitize($value) : $value;
        }
        return $result;
    }
}
