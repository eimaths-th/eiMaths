<?php

namespace App\Domain\CreditNote\Services;

use App\Domain\CreditNote\Exceptions\CreditNoteDomainException;
use App\Domain\CreditNote\Models\CreditNote;
use App\Models\Contact;
use App\Models\Order;
use App\Models\Receipt;
use Illuminate\Support\Collection;

final class CreditNoteFinancialSourceGuard
{
    public function AssertOrderUpdateAllowed(Order $order, array $intended_changes): void
    {
        if (!$this->IsOrderLocked($order)) {
            return;
        }
        $protected_fields = ['cid', 'discount', 'status', 'payment', 'total_price', 'refund', 'register_fee', 'access_fee', 'orther_fee', 'discount_book', 'affiliate_attribution_id', 'affiliate_commission_rate_basis_points'];
        $allowed_metadata = ['detail', 'free_course', 'free_course_reason', 'updated_by'];
        foreach ($intended_changes as $field => $value) {
            if (in_array($field, $protected_fields, true) && $this->SameValue($order->getAttribute($field), $value)) {
                continue;
            }
            if (!in_array($field, $allowed_metadata, true)) {
                throw CreditNoteDomainException::FinancialSourceLocked();
            }
        }
    }

    public function AssertOrderCancellationAllowed(Order $order): void
    {
        if ($this->IsOrderLocked($order)) {
            throw CreditNoteDomainException::FinancialSourceLocked();
        }
    }

    public function AssertTuitionReplacementAllowed(Order $order, Collection $proposed_rows): void
    {
        if ($this->IsOrderLocked($order)) {
            throw CreditNoteDomainException::FinancialSourceLocked();
        }
    }

    public function AssertReceiptUpdateAllowed(Receipt $receipt, array $intended_changes, bool $replaces_details): void
    {
        if (!$this->IsReceiptLocked($receipt)) {
            return;
        }
        if ($replaces_details) {
            throw CreditNoteDomainException::FinancialSourceLocked();
        }
        $protected_fields = ['oid', 'total_fee', 'payment', 'payment_date', 'status'];
        $allowed_metadata = ['billing_details', 'student_name'];
        foreach ($intended_changes as $field => $value) {
            if (in_array($field, $protected_fields, true) && $this->SameValue($receipt->getAttribute($field), $value)) {
                continue;
            }
            if (!in_array($field, $allowed_metadata, true)) {
                throw CreditNoteDomainException::FinancialSourceLocked();
            }
        }
    }

    public function AssertContactDeletionAllowed(Contact $contact): void
    {
        $receipt_ids = Receipt::query()->where('cid', $contact->id)->pluck('id');
        if (CreditNote::query()->whereIn('receipt_id', $receipt_ids)->whereNotNull('number')->exists()) {
            throw CreditNoteDomainException::FinancialSourceLocked();
        }
    }

    public function IsOrderLocked(Order $order): bool
    {
        $receipt_ids = Receipt::query()->where('oid', $order->id)->pluck('id');

        return CreditNote::query()->whereIn('receipt_id', $receipt_ids)->whereNotNull('number')->exists();
    }

    public function IsReceiptLocked(Receipt $receipt): bool
    {
        return CreditNote::query()->where('receipt_id', $receipt->id)->whereNotNull('number')->exists();
    }

    private function SameValue(mixed $left, mixed $right): bool
    {
        if ($left === null || $right === null) {
            return $left === $right;
        }

        return (string) $left === (string) $right;
    }
}
