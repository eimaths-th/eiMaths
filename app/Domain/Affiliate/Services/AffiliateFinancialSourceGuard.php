<?php

namespace App\Domain\Affiliate\Services;

use App\Domain\Affiliate\Exceptions\AffiliateDomainException;
use App\Domain\Affiliate\Models\AffiliateCommission;
use App\Domain\Affiliate\Models\AffiliateAttribution;
use App\Models\Contact;
use App\Models\Order;
use App\Models\OrderDetailList;
use App\Models\Receipt;
use Illuminate\Support\Collection;

class AffiliateFinancialSourceGuard
{
    public function AssertOrderUpdateAllowed(Order $order, array $intended_changes): void
    {
        if (!$this->IsOrderLocked($order)) {
            return;
        }

        $protected_fields = ['cid', 'discount', 'status', 'payment', 'total_price', 'refund', 'register_fee', 'access_fee', 'orther_fee', 'discount_book', 'affiliate_attribution_id', 'affiliate_commission_rate_basis_points'];
        $allowed_metadata = ['detail', 'free_course', 'free_course_reason', 'updated_by'];
        foreach ($intended_changes as $field => $value) {
            if (in_array($field, $protected_fields, true)) {
                if (!$this->SameValue($order->getAttribute($field), $value)) {
                    throw AffiliateDomainException::FinancialSourceLocked();
                }
                continue;
            }
            if (!in_array($field, $allowed_metadata, true)) {
                throw AffiliateDomainException::FinancialSourceLocked();
            }
        }
    }

    public function AssertOrderCancellationAllowed(Order $order): void
    {
        if ($this->IsOrderLocked($order)) {
            throw AffiliateDomainException::FinancialSourceLocked();
        }
    }

    public function AssertTuitionReplacementAllowed(Order $order, Collection $proposed_rows): void
    {
        if (!$this->IsOrderLocked($order)) {
            return;
        }

        $persisted = $order->orderDetailLists()->get()->keyBy('id');
        if ($persisted->count() !== $proposed_rows->count()) {
            throw AffiliateDomainException::FinancialSourceLocked();
        }
        foreach ($proposed_rows as $row) {
            $original = $persisted->get((int) ($row->id ?? 0));
            if ($original === null || (int) $original->order_id !== (int) $order->id || !$this->SameValue($original->price, $row->price)) {
                throw AffiliateDomainException::FinancialSourceLocked();
            }
        }
    }

    public function AssertReceiptUpdateAllowed(Receipt $receipt, array $intended_changes, bool $replaces_details): void
    {
        if (!$this->IsReceiptLocked($receipt)) {
            return;
        }
        if ($replaces_details) {
            throw AffiliateDomainException::FinancialSourceLocked();
        }

        $protected_fields = ['oid', 'total_fee', 'payment', 'payment_date', 'status'];
        $allowed_metadata = ['billing_details', 'student_name'];
        foreach ($intended_changes as $field => $value) {
            if (in_array($field, $protected_fields, true) && !$this->SameValue($receipt->getAttribute($field), $value)) {
                throw AffiliateDomainException::FinancialSourceLocked();
            }
            if (!in_array($field, $protected_fields, true) && !in_array($field, $allowed_metadata, true)) {
                throw AffiliateDomainException::FinancialSourceLocked();
            }
        }
    }

    public function AssertContactDeletionAllowed(Contact $contact): void
    {
        if (AffiliateAttribution::query()->where('contact_id', $contact->id)->exists()
            || AffiliateCommission::query()->where('contact_id', $contact->id)->exists()
            || AffiliateCommission::query()->whereIn('order_id', $contact->order()->pluck('id'))->exists()) {
            throw AffiliateDomainException::FinancialSourceLocked();
        }
    }

    public function IsOrderLocked(Order $order): bool
    {
        return AffiliateCommission::query()->where('order_id', $order->id)->exists();
    }

    public function IsReceiptLocked(Receipt $receipt): bool
    {
        return AffiliateCommission::query()->where('receipt_id', $receipt->id)->exists();
    }

    private function SameValue(mixed $left, mixed $right): bool
    {
        if ($left === null || $right === null) {
            return $left === $right;
        }
        return (string) $left === (string) $right;
    }
}
