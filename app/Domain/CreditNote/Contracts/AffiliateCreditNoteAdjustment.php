<?php

namespace App\Domain\CreditNote\Contracts;

use App\Domain\CreditNote\Models\CreditNote;
use App\Domain\CreditNote\ValueObjects\AffiliateCreditNoteAdjustmentResult;
use App\Models\User;

interface AffiliateCreditNoteAdjustment
{
    public function ApplyIssue(CreditNote $credit_note, User $actor, string $correlation_id): AffiliateCreditNoteAdjustmentResult;

    public function ApplyCancellation(CreditNote $credit_note, User $actor, string $correlation_id): AffiliateCreditNoteAdjustmentResult;
}
