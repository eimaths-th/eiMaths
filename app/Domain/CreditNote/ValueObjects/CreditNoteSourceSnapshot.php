<?php

namespace App\Domain\CreditNote\ValueObjects;

final class CreditNoteSourceSnapshot
{
    public function __construct(
        public readonly int $receipt_id,
        public readonly int $centre_id,
        public readonly int $order_id,
        public readonly string $receipt_number,
        public readonly string $receipt_date,
        public readonly string $billing_details,
        public readonly string $receipt_updated_at,
        public readonly int $header_total_minor,
        public readonly int $detail_total_minor,
        public readonly int $service_charge_minor,
        public readonly int $grand_total_minor,
        public readonly int $receipt_status,
        public readonly int $order_status,
        public readonly string $payment_date,
        public readonly array $lines,
    ) {
    }
}
