<?php

namespace App\Policies;

use App\Domain\CreditNote\Models\CreditNote;
use App\Models\Receipt;
use App\Models\User;

class CreditNotePolicy
{
    public function list(User $user): bool
    {
        return $user->can('credit-note-list');
    }

    public function show(User $user, CreditNote $credit_note): bool
    {
        return $user->can('credit-note-show') && $this->CentreAllowed($user, (int) $credit_note->centre_id);
    }

    public function create(User $user, Receipt $receipt): bool
    {
        return $user->can('credit-note-create') && $this->CentreAllowed($user, (int) $receipt->centre);
    }

    public function edit(User $user, CreditNote $credit_note): bool
    {
        return $user->can('credit-note-edit') && $credit_note->status?->value === 'draft'
            && $this->CentreAllowed($user, (int) $credit_note->centre_id);
    }

    public function issue(User $user, CreditNote $credit_note): bool
    {
        return $user->can('credit-note-issue') && $credit_note->status?->value === 'draft'
            && $this->CentreAllowed($user, (int) $credit_note->centre_id);
    }

    public function cancel(User $user, CreditNote $credit_note): bool
    {
        return $user->can('credit-note-cancel')
            && in_array($credit_note->status?->value, ['draft', 'issued'], true)
            && $this->CentreAllowed($user, (int) $credit_note->centre_id);
    }

    public function export(User $user): bool
    {
        return $user->can('credit-note-list');
    }

    private function CentreAllowed(User $user, int $centre_id): bool
    {
        if ($user->can('all-centre')) {
            return true;
        }

        return $user->department_id !== null && (int) $user->department_id === $centre_id;
    }
}
