<?php

namespace App\Domain\CreditNote\Services;

use App\Domain\CreditNote\Enums\CreditNoteEventOperation;
use App\Domain\CreditNote\Enums\CreditNoteReasonCode;
use App\Domain\CreditNote\Enums\CreditNoteStatus;
use App\Domain\CreditNote\Models\CreditNote;
use App\Domain\CreditNote\Models\CreditNoteEvent;
use App\Models\User;

final class CreditNoteEventRecorder
{
    public function Record(
        CreditNote $credit_note,
        CreditNoteEventOperation $operation,
        ?CreditNoteStatus $status_before,
        CreditNoteStatus $status_after,
        int $amount_minor,
        ?User $actor,
        ?CreditNoteReasonCode $reason_code,
        string $correlation_id,
        array $metadata = [],
    ): CreditNoteEvent {
        return CreditNoteEvent::create([
            'credit_note_id' => $credit_note->id,
            'operation' => $operation,
            'status_before' => $status_before,
            'status_after' => $status_after,
            'amount_minor' => $amount_minor,
            'actor_id' => $actor?->id,
            'actor_name_snapshot' => $actor?->name,
            'reason_code' => $reason_code,
            'correlation_id' => $correlation_id,
            'metadata' => $this->SanitizeMetadata($metadata),
            'occurred_at' => now(),
            'created_at' => now(),
        ]);
    }

    private function SanitizeMetadata(array $metadata): array
    {
        $sanitized = [];
        foreach ($metadata as $key => $value) {
            if (preg_match('/password|token|secret|buyer|contact|tax|address|telephone|billing|description|snapshot/i', (string) $key)) {
                continue;
            }

            $sanitized[$key] = is_array($value) ? $this->SanitizeMetadata($value) : $value;
        }

        return $sanitized;
    }
}
