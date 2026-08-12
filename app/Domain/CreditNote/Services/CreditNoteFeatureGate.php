<?php

namespace App\Domain\CreditNote\Services;

use App\Domain\CreditNote\Exceptions\CreditNoteDomainException;

final class CreditNoteFeatureGate
{
    public function IsEnabled(): bool
    {
        return (bool) config('financial-documents.credit_note.enabled', false);
    }

    public function AreFinancialActionsEnabled(): bool
    {
        return (bool) config('financial-documents.credit_note.financial_actions_enabled', false);
    }

    public function AssertEnabled(): void
    {
        if (!$this->IsEnabled()) {
            throw CreditNoteDomainException::Disabled();
        }
    }

    public function AssertFinancialActionsEnabled(): void
    {
        $this->AssertEnabled();

        if (!$this->AreFinancialActionsEnabled()) {
            throw CreditNoteDomainException::FinancialActionsDisabled();
        }
    }
}
