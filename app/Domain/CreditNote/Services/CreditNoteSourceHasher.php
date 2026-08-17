<?php

namespace App\Domain\CreditNote\Services;

use App\Domain\CreditNote\ValueObjects\CreditNoteMoney;
use App\Domain\CreditNote\ValueObjects\CreditNoteSourceSnapshot;

final class CreditNoteSourceHasher
{
    public function Hash(CreditNoteSourceSnapshot $snapshot): string
    {
        $lines = [];
        foreach ($snapshot->lines as $line) {
            $lines[] = [
                'source_type' => $line->source_type->value,
                'source_id' => $line->source_id,
                'line_type' => $line->line_type?->value,
                'description' => $line->description,
                'unit' => $line->unit,
                'quantity' => $line->original_quantity->Decimal(),
                'unit_price' => CreditNoteMoney::FromMinor($line->original_unit_price_minor)->Decimal(),
                'discount' => CreditNoteMoney::FromMinor($line->original_discount_minor)->Decimal(),
                'line_total' => CreditNoteMoney::FromMinor($line->original_line_total_minor)->Decimal(),
                'tax_treatment' => $line->tax_treatment?->value,
                'vat_rate_basis_points' => $line->vat_rate_basis_points,
            ];
        }

        $payload = [
            'receipt_id' => $snapshot->receipt_id,
            'centre_id' => $snapshot->centre_id,
            'order_id' => $snapshot->order_id,
            'receipt_number' => $snapshot->receipt_number,
            'receipt_date' => $snapshot->receipt_date,
            'billing_details' => $snapshot->billing_details,
            'receipt_updated_at' => $snapshot->receipt_updated_at,
            'header_total' => CreditNoteMoney::FromMinor($snapshot->header_total_minor)->Decimal(),
            'detail_total' => CreditNoteMoney::FromMinor($snapshot->detail_total_minor)->Decimal(),
            'service_charge' => CreditNoteMoney::FromMinor($snapshot->service_charge_minor)->Decimal(),
            'receipt_status' => $snapshot->receipt_status,
            'order_status' => $snapshot->order_status,
            'payment_date' => $snapshot->payment_date,
            'lines' => $lines,
        ];

        return hash('sha256', json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
        ));
    }
}
