<?php

namespace App\Domain\CreditNote\Services;

use App\Domain\CreditNote\Enums\CreditNoteSourceType;
use App\Domain\CreditNote\Enums\ReceiptLineType;
use App\Domain\CreditNote\Enums\TaxTreatment;
use App\Domain\CreditNote\ValueObjects\CreditNoteSourceLine;
use App\Domain\CreditNote\ValueObjects\CreditNoteSourceSnapshot;
use App\Domain\CreditNote\ValueObjects\CreditNoteMoney;
use App\Domain\CreditNote\ValueObjects\FixedQuantity;
use App\Domain\CreditNote\ValueObjects\ExactRatio;
use App\Models\Order;
use App\Models\Receipt;
use App\Models\receipt_details;

final class CreditNoteSourceReader
{
    public function Read(Receipt $receipt): CreditNoteSourceSnapshot
    {
        $order = Order::find($receipt->oid);
        $details = receipt_details::query()->where('receipt_id', $receipt->id)->orderBy('id')->get();
        $lines = [];
        $detail_total_minor = 0;

        foreach ($details as $detail) {
            $quantity = FixedQuantity::FromDecimal((string) ($detail->quantity ?? '0.00'));
            $unit_price_minor = CreditNoteMoney::FromDecimal((string) ($detail->price ?? '0.00'), true)->Minor();
            $discount_minor = CreditNoteMoney::FromDecimal((string) ($detail->discount ?? '0.00'), true)->Minor();
            $line_total_minor = ExactRatio::MultiplyDivideHalfUp(
                $quantity->Hundredths(),
                $unit_price_minor,
                100,
            ) - $discount_minor;
            $has_complete_source_values = $detail->quantity !== null
                && $detail->discount !== null
                && ($detail->price !== null || $line_total_minor === 0);
            $detail_total_minor += $line_total_minor;

            $line = new CreditNoteSourceLine(
                CreditNoteSourceType::RECEIPT_DETAIL,
                (int) $detail->id,
                $detail->line_type instanceof ReceiptLineType ? $detail->line_type : null,
                (string) ($detail->des ?? ''),
                $detail->unit === null ? null : (string) $detail->unit,
                $quantity,
                $unit_price_minor,
                $discount_minor,
                $line_total_minor,
                $detail->tax_treatment instanceof TaxTreatment ? $detail->tax_treatment : null,
                $detail->vat_rate_basis_points === null ? null : (int) $detail->vat_rate_basis_points,
                $has_complete_source_values,
            );
            $lines[] = $line;
        }

        $service_charge_minor = 0;
        if ($order !== null && $order->orther_fee !== null) {
            $configured_service_charge_minor = CreditNoteMoney::FromDecimal((string) $order->orther_fee, true)->Minor();
            if ($configured_service_charge_minor > 0) {
                $service_charge_minor = $configured_service_charge_minor;
            }
        }
        if ($service_charge_minor > 0) {
            $lines[] = new CreditNoteSourceLine(
                CreditNoteSourceType::RECEIPT_SERVICE_CHARGE,
                (int) $order->id,
                ReceiptLineType::SERVICE_CHARGE,
                'Service Charge',
                null,
                FixedQuantity::FromDecimal('1.00'),
                $service_charge_minor,
                0,
                $service_charge_minor,
                TaxTreatment::OUT_OF_SCOPE,
                0,
            );
        }

        return new CreditNoteSourceSnapshot(
            (int) $receipt->id,
            (int) $receipt->centre,
            (int) ($order?->id ?? $receipt->oid ?? 0),
            (string) ($receipt->receipt_number ?? ''),
            (string) ($receipt->payment_date ?? ''),
            (string) ($receipt->billing_details ?? ''),
            optional($receipt->updated_at)->toIso8601String() ?? '',
            CreditNoteMoney::FromDecimal((string) ($receipt->total_fee ?? '0.00'))->Minor(),
            $detail_total_minor,
            $service_charge_minor,
            $detail_total_minor + $service_charge_minor,
            (int) ($receipt->status ?? 0),
            (int) ($order?->status ?? 0),
            (string) ($receipt->payment_date ?? ''),
            $lines,
        );
    }
}
