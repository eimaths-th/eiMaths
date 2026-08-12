<?php

namespace App\Domain\CreditNote\Commands;

use App\Domain\CreditNote\Enums\CreditNoteReasonCode;
use Carbon\CarbonImmutable;

final class UpdateCreditNoteDraft
{
    public readonly CreditNoteReasonCode $reason_code;

    public function __construct(
        public readonly int $lock_version,
        public readonly array $buyer_fields,
        CreditNoteReasonCode|string $reason_code,
        public readonly string $reason_details,
        public readonly CarbonImmutable $adjustment_event_date,
        public readonly ?string $late_issue_reason,
        public readonly array $items,
    ) {
        $this->reason_code = $reason_code instanceof CreditNoteReasonCode
            ? $reason_code
            : CreditNoteReasonCode::from($reason_code);
    }
}
