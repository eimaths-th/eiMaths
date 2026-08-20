<?php

namespace App\Domain\CreditNote\Queries;

use App\Domain\CreditNote\Enums\CreditNoteStatus;
use App\Domain\CreditNote\Models\CreditNote;
use App\Domain\CreditNote\Services\CreditNoteSourceReader;
use App\Domain\CreditNote\ValueObjects\CreditNoteSourceSnapshot;
use App\Models\Receipt;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

final class CreditNoteQuery
{
    public function __construct(private readonly CreditNoteSourceReader $source_reader)
    {
    }

    public function ForList(User $actor, array $filters = []): Builder
    {
        $query = CreditNote::query()->with(['Receipt', 'Department', 'Items', 'AffiliateAdjustments']);
        $this->ApplyCentreScope($query, $actor);

        if (($number = trim((string) ($filters['number'] ?? ''))) !== '') {
            $query->where('number', 'like', '%' . addcslashes($number, '%_') . '%');
        }
        if (($original_number = trim((string) ($filters['original_number'] ?? ''))) !== '') {
            $query->where('original_receipt_number', 'like', '%' . addcslashes($original_number, '%_') . '%');
        }
        $status_value = $filters['status'] ?? null;
        if ($status_value !== null && ($status = CreditNoteStatus::tryFrom((string) $status_value)) !== null) {
            $query->where('status', $status->value);
        }
        if (($reason_code = trim((string) ($filters['reason_code'] ?? ''))) !== '') {
            $query->where('reason_code', $reason_code);
        }
        if (($centre_id = (int) ($filters['centre_id'] ?? 0)) > 0) {
            $query->where('centre_id', $centre_id);
        }
        if (($from = $filters['date_from'] ?? null) !== null && $from !== '') {
            $query->whereDate('adjustment_event_date', '>=', $from);
        }
        if (($to = $filters['date_to'] ?? null) !== null && $to !== '') {
            $query->whereDate('adjustment_event_date', '<=', $to);
        }
        if (($ids = $filters['ids'] ?? null) !== null) {
            $query->whereIn('credit_notes.id', array_slice(array_values(array_filter(array_map('intval', (array) $ids))), 0, 500));
        }

        return $query->orderByDesc('id');
    }

    public function FindVisibleOrFail(User $actor, int $credit_note_id): CreditNote
    {
        $note = $this->ForList($actor)->whereKey($credit_note_id)->first();
        if ($note === null) {
            throw (new ModelNotFoundException())->setModel(CreditNote::class, [$credit_note_id]);
        }

        return $note;
    }

    public function FindReceiptVisibleOrFail(User $actor, int $receipt_id): Receipt
    {
        $receipt = Receipt::query()->whereKey($receipt_id)->first();
        if ($receipt === null || !$this->CentreIsVisible($actor, (int) $receipt->centre)) {
            throw (new ModelNotFoundException())->setModel(Receipt::class, [$receipt_id]);
        }

        return $receipt;
    }

    public function Document(User $actor, int $credit_note_id): CreditNote
    {
        return $this->FindVisibleOrFail($actor, $credit_note_id)->load([
            'CreatedBy:id,name',
            'IssuedBy:id,name',
            'CancelledBy:id,name',
        ]);
    }

    public function EditorSource(CreditNote $credit_note): CreditNoteSourceSnapshot
    {
        $receipt = Receipt::query()->findOrFail($credit_note->receipt_id);

        return $this->source_reader->Read($receipt);
    }

    public function ReceiptSummaries(User $actor, array $receipt_ids): Collection
    {
        $ids = array_slice(array_values(array_filter(array_map('intval', $receipt_ids))), 0, 500);
        if ($ids === []) {
            return collect();
        }

        return $this->ForList($actor)
            ->whereIn('receipt_id', $ids)
            ->get()
            ->groupBy('receipt_id')
            ->map(function (Collection $notes): array {
                $original = (int) $notes->max('original_grand_total_minor');
                $net_credited = (int) $notes->sum(function (CreditNote $note): int {
                    if ($note->status?->value === 'issued') {
                        return (int) $note->difference_grand_total_minor;
                    }
                    if ($note->status?->value === 'cancelled' && $note->number !== null) {
                        return -(int) $note->difference_grand_total_minor;
                    }
                    return 0;
                });
                return [
                    'original_minor' => $original,
                    'net_credited_minor' => $net_credited,
                    'status' => $net_credited <= 0 ? 'not_credited' : ($net_credited >= $original ? 'fully_credited' : 'partially_credited'),
                ];
            });
    }

    private function ApplyCentreScope(Builder $query, User $actor): void
    {
        if ($actor->can('all-centre')) {
            return;
        }
        if ($actor->department_id === null) {
            $query->whereRaw('1 = 0');
            return;
        }
        $query->where('centre_id', (int) $actor->department_id);
    }

    private function CentreIsVisible(User $actor, int $centre_id): bool
    {
        return $actor->can('all-centre') || ($actor->department_id !== null && (int) $actor->department_id === $centre_id);
    }
}
