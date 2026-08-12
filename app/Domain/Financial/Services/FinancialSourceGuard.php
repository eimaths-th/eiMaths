<?php

namespace App\Domain\Financial\Services;

use App\Domain\Affiliate\Services\AffiliateFinancialSourceGuard;
use App\Domain\CreditNote\Services\CreditNoteFinancialSourceGuard;
use App\Models\Contact;
use App\Models\Order;
use App\Models\Receipt;
use Illuminate\Support\Collection;

final class FinancialSourceGuard
{
    public function __construct(
        private readonly AffiliateFinancialSourceGuard $affiliate_guard,
        private readonly CreditNoteFinancialSourceGuard $credit_note_guard,
    ) {
    }

    public function AssertOrderUpdateAllowed(Order $order, array $intended_changes): void
    {
        $this->affiliate_guard->AssertOrderUpdateAllowed($order, $intended_changes);
        $this->credit_note_guard->AssertOrderUpdateAllowed($order, $intended_changes);
    }

    public function AssertOrderCancellationAllowed(Order $order): void
    {
        $this->affiliate_guard->AssertOrderCancellationAllowed($order);
        $this->credit_note_guard->AssertOrderCancellationAllowed($order);
    }

    public function AssertTuitionReplacementAllowed(Order $order, Collection $proposed_rows): void
    {
        $this->affiliate_guard->AssertTuitionReplacementAllowed($order, $proposed_rows);
        $this->credit_note_guard->AssertTuitionReplacementAllowed($order, $proposed_rows);
    }

    public function AssertReceiptUpdateAllowed(Receipt $receipt, array $intended_changes, bool $replaces_details): void
    {
        $this->affiliate_guard->AssertReceiptUpdateAllowed($receipt, $intended_changes, $replaces_details);
        $this->credit_note_guard->AssertReceiptUpdateAllowed($receipt, $intended_changes, $replaces_details);
    }

    public function AssertContactDeletionAllowed(Contact $contact): void
    {
        $this->affiliate_guard->AssertContactDeletionAllowed($contact);
        $this->credit_note_guard->AssertContactDeletionAllowed($contact);
    }

    public function IsOrderLocked(Order $order): bool
    {
        return $this->affiliate_guard->IsOrderLocked($order) || $this->credit_note_guard->IsOrderLocked($order);
    }

    public function IsReceiptLocked(Receipt $receipt): bool
    {
        return $this->affiliate_guard->IsReceiptLocked($receipt) || $this->credit_note_guard->IsReceiptLocked($receipt);
    }
}
