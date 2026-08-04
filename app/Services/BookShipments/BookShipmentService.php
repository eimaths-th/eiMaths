<?php

namespace App\Services\BookShipments;

use App\Exceptions\BookShipmentException;
use App\Jobs\SendBookShipmentNotification;
use App\Models\BookShipment;
use App\Models\BookShipmentItem;
use App\Models\BookShipmentLog;
use App\Models\Department;
use App\Models\OrderDetailList;
use App\Models\User;
use App\Notifications\BookShipmentNotification;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookShipmentService
{
    public function __construct(private readonly BookShipmentQuery $book_shipment_query)
    {
    }

    public function CreateDraft(
        User $actor,
        int $destination_centre_id,
        array $source_book_ids
    ): BookShipment {
        $this->AssertHqPermission($actor, 'book-shipment-create');
        $source_book_ids = $this->NormalizeSourceBookIds($source_book_ids);

        $shipment = DB::transaction(function () use (
            $actor,
            $destination_centre_id,
            $source_book_ids
        ): BookShipment {
            $destination_centre = $this->GetEligibleDestination($destination_centre_id);
            $locked_source_books = $this->LockSourceBooks($source_book_ids);
            $this->AssertSourceRowsAreAvailable($source_book_ids, $locked_source_books);
            $source_books = $this->book_shipment_query->GetEligibleSourceBooks(
                $actor,
                $destination_centre_id,
                $source_book_ids
            );
            $this->AssertEligibleSourceSet($source_book_ids, $source_books);

            $shipment = BookShipment::create([
                'shipment_number' => 'PENDING-' . Str::uuid()->toString(),
                'destination_centre_id' => $destination_centre->id,
                'destination_centre_code' => $destination_centre->code,
                'destination_centre_name' => $destination_centre->name,
                'status' => BookShipment::STATUS_DRAFT,
                'created_by' => $actor->id,
                'created_by_name' => $actor->name,
            ]);

            foreach ($source_books as $source_book) {
                $shipment_item = $this->CreateDraftItem($shipment, $source_book);
                $this->ClaimSourceBook($source_book, $shipment_item);
            }

            $shipment->forceFill([
                'shipment_number' => $this->BuildShipmentNumber($shipment->id),
            ])->save();
            $this->CreateLog($shipment, $actor, BookShipmentLog::ACTION_CREATED);

            return $shipment;
        });

        return $this->RefreshShipment($shipment);
    }

    public function UpdateDraft(
        BookShipment $shipment,
        User $actor,
        array $source_book_ids
    ): BookShipment {
        $this->AssertHqPermission($actor, 'book-shipment-create');
        $source_book_ids = $this->NormalizeSourceBookIds($source_book_ids);

        $updated_shipment = DB::transaction(function () use (
            $shipment,
            $actor,
            $source_book_ids
        ): BookShipment {
            $locked_shipment = BookShipment::query()
                ->whereKey($shipment->id)
                ->lockForUpdate()
                ->first();

            if (!$locked_shipment) {
                throw (new ModelNotFoundException())->setModel(BookShipment::class, [$shipment->id]);
            }

            if ($locked_shipment->status !== BookShipment::STATUS_DRAFT) {
                throw new BookShipmentException(BookShipmentException::STATE_CONFLICT);
            }

            $existing_items = BookShipmentItem::query()
                ->where('book_shipment_id', $locked_shipment->id)
                ->orderBy('source_order_detail_list_id')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();
            $existing_by_source_book = $existing_items
                ->filter(fn (BookShipmentItem $item): bool => $item->source_order_detail_list_id !== null)
                ->keyBy('source_order_detail_list_id');
            $all_source_book_ids = collect($source_book_ids)
                ->merge($existing_by_source_book->keys())
                ->map(static fn ($source_book_id): int => (int) $source_book_id)
                ->unique()
                ->sort()
                ->values()
                ->all();
            $locked_source_books = $this->LockSourceBooks($all_source_book_ids);
            $locked_source_books_by_id = $locked_source_books->keyBy('id');

            foreach ($source_book_ids as $source_book_id) {
                $source_book = $locked_source_books_by_id->get($source_book_id);
                $existing_item = $existing_by_source_book->get($source_book_id);

                if ($source_book?->active_book_shipment_item_id !== null) {
                    if (!$existing_item || (int) $source_book->active_book_shipment_item_id !== (int) $existing_item->id) {
                        throw new BookShipmentException(BookShipmentException::SOURCE_CONFLICT);
                    }
                }
            }

            $source_books = $this->book_shipment_query->GetEligibleSourceBooks(
                $actor,
                (int) $locked_shipment->destination_centre_id,
                $source_book_ids,
                (int) $locked_shipment->id
            );
            $this->AssertEligibleSourceSet($source_book_ids, $source_books);
            $source_books_by_id = $source_books->keyBy('id');

            foreach ($existing_by_source_book as $source_book_id => $existing_item) {
                if (in_array((int) $source_book_id, $source_book_ids, true)) {
                    continue;
                }

                $source_book = $locked_source_books_by_id->get($source_book_id);
                if (
                    $source_book
                    && $source_book->active_book_shipment_item_id !== null
                    && (int) $source_book->active_book_shipment_item_id !== (int) $existing_item->id
                ) {
                    throw new BookShipmentException(BookShipmentException::SOURCE_CONFLICT);
                }

                if ($source_book && (int) $source_book->active_book_shipment_item_id === (int) $existing_item->id) {
                    $source_book->forceFill(['active_book_shipment_item_id' => null])->save();
                }
                $existing_item->delete();
            }

            foreach ($source_book_ids as $source_book_id) {
                $source_book = $source_books_by_id->get($source_book_id);
                $existing_item = $existing_by_source_book->get($source_book_id);

                if ($existing_item) {
                    if ((int) $source_book->active_book_shipment_item_id !== (int) $existing_item->id) {
                        $this->ClaimSourceBook($source_book, $existing_item);
                    }
                    continue;
                }

                $shipment_item = $this->CreateDraftItem($locked_shipment, $source_book);
                $this->ClaimSourceBook($source_book, $shipment_item);
            }

            $this->CreateLog($locked_shipment, $actor, BookShipmentLog::ACTION_ITEMS_UPDATED);

            return $locked_shipment;
        });

        return $this->RefreshShipment($updated_shipment);
    }

    public function Dispatch(BookShipment $shipment, User $actor): array
    {
        $this->AssertHqPermission($actor, 'book-shipment-dispatch');

        $dispatch_result = DB::transaction(function () use ($shipment, $actor): array {
            $locked_shipment = $this->LockShipment($shipment);
            if ($locked_shipment->status !== BookShipment::STATUS_DRAFT) {
                throw new BookShipmentException(BookShipmentException::STATE_CONFLICT);
            }

            $items = $this->LockShipmentItems($locked_shipment);
            if ($items->isEmpty()) {
                throw new BookShipmentException(BookShipmentException::SOURCE_INELIGIBLE);
            }

            $source_book_ids = $items
                ->pluck('source_order_detail_list_id')
                ->filter()
                ->map(static fn ($source_book_id): int => (int) $source_book_id)
                ->sort()
                ->values()
                ->all();
            if (count($source_book_ids) !== $items->count()) {
                throw new BookShipmentException(BookShipmentException::SOURCE_INELIGIBLE);
            }

            $source_books = $this->LockSourceBooks($source_book_ids)->keyBy('id');
            $destination_centre = $this->GetEligibleDestination((int) $locked_shipment->destination_centre_id);
            $dispatched_at = now();

            foreach ($items as $item) {
                $source_book = $source_books->get($item->source_order_detail_list_id);
                if (!$source_book || (int) $source_book->active_book_shipment_item_id !== (int) $item->id) {
                    throw new BookShipmentException(BookShipmentException::SOURCE_CONFLICT);
                }

                $source_book->loadMissing(['order.contact']);
                $source_order = $source_book->order;
                $source_student = $source_order?->contact;
                if (!$source_order || !$source_student) {
                    throw new BookShipmentException(BookShipmentException::SOURCE_INELIGIBLE);
                }

                $item->forceFill([
                    'source_student_id' => $source_student->id,
                    'source_order_id' => $source_order->id,
                    'source_student_code' => $source_student->code,
                    'source_student_name' => $source_student->name,
                    'source_order_number' => $source_order->order_number,
                    'book_name' => $source_book->book,
                    'level' => $source_book->level,
                    'term' => $source_book->term,
                    'centre_status' => BookShipmentItem::CENTRE_STATUS_PENDING,
                    'centre_note' => null,
                ])->save();
            }

            $locked_shipment->forceFill([
                'destination_centre_code' => $destination_centre->code,
                'destination_centre_name' => $destination_centre->name,
                'status' => BookShipment::STATUS_DISPATCHED,
                'dispatched_by' => $actor->id,
                'dispatched_by_name' => $actor->name,
                'dispatched_at' => $dispatched_at,
            ])->save();
            $this->CreateLog($locked_shipment, $actor, BookShipmentLog::ACTION_DISPATCHED, $dispatched_at);
            SendBookShipmentNotification::dispatch(
                (int) $locked_shipment->id,
                BookShipmentNotification::EVENT_DISPATCHED
            )->afterCommit();

            return [
                'shipment' => $locked_shipment,
                'warnings' => $this->GetDispatchWarnings($destination_centre->id),
            ];
        });

        return [
            'shipment' => $this->RefreshShipment($dispatch_result['shipment']),
            'warnings' => $dispatch_result['warnings'],
        ];
    }

    public function Cancel(BookShipment $shipment, User $actor, string $reason): BookShipment
    {
        $this->AssertHqPermission($actor, 'book-shipment-cancel');
        $reason = $this->NormalizeCancellationReason($reason);

        $cancelled_shipment = DB::transaction(function () use ($shipment, $actor, $reason): BookShipment {
            $locked_shipment = $this->LockShipment($shipment);
            if ($locked_shipment->status !== BookShipment::STATUS_DISPATCHED) {
                throw new BookShipmentException(BookShipmentException::STATE_CONFLICT);
            }

            $items = $this->LockShipmentItems($locked_shipment);
            $source_book_ids = $items
                ->pluck('source_order_detail_list_id')
                ->filter()
                ->map(static fn ($source_book_id): int => (int) $source_book_id)
                ->sort()
                ->values()
                ->all();
            $source_books = $this->LockSourceBooks($source_book_ids)->keyBy('id');

            foreach ($items as $item) {
                $source_book = $source_books->get($item->source_order_detail_list_id);
                if (
                    $source_book
                    && (int) $source_book->active_book_shipment_item_id === (int) $item->id
                ) {
                    $source_book->forceFill(['active_book_shipment_item_id' => null])->save();
                }
            }

            $cancelled_at = now();
            $locked_shipment->forceFill([
                'status' => BookShipment::STATUS_CANCELLED,
                'cancelled_by' => $actor->id,
                'cancelled_by_name' => $actor->name,
                'cancelled_at' => $cancelled_at,
                'cancellation_reason' => $reason,
            ])->save();
            $this->CreateLog($locked_shipment, $actor, BookShipmentLog::ACTION_CANCELLED, $cancelled_at, $reason);

            return $locked_shipment;
        });

        return $this->RefreshShipment($cancelled_shipment);
    }

    public function DiscardDraft(BookShipment $shipment, User $actor, string $reason): BookShipment
    {
        $this->AssertHqPermission($actor, 'book-shipment-cancel');
        $reason = $this->NormalizeCancellationReason($reason);

        $discarded_shipment = DB::transaction(function () use ($shipment, $actor, $reason): BookShipment {
            $locked_shipment = $this->LockShipment($shipment);
            if ($locked_shipment->status !== BookShipment::STATUS_DRAFT) {
                throw new BookShipmentException(BookShipmentException::STATE_CONFLICT);
            }

            $items = $this->LockShipmentItems($locked_shipment);
            $source_book_ids = $items
                ->pluck('source_order_detail_list_id')
                ->filter()
                ->map(static fn ($source_book_id): int => (int) $source_book_id)
                ->sort()
                ->values()
                ->all();
            $source_books = $this->LockSourceBooks($source_book_ids)->keyBy('id');

            foreach ($items as $item) {
                $source_book = $source_books->get($item->source_order_detail_list_id);
                if (
                    $source_book
                    && (int) $source_book->active_book_shipment_item_id === (int) $item->id
                ) {
                    $source_book->forceFill(['active_book_shipment_item_id' => null])->save();
                }
            }

            $cancelled_at = now();
            $locked_shipment->forceFill([
                'status' => BookShipment::STATUS_CANCELLED,
                'cancelled_by' => $actor->id,
                'cancelled_by_name' => $actor->name,
                'cancelled_at' => $cancelled_at,
                'cancellation_reason' => $reason,
            ])->save();
            $this->CreateLog(
                $locked_shipment,
                $actor,
                BookShipmentLog::ACTION_DRAFT_DISCARDED,
                $cancelled_at,
                $reason
            );

            return $locked_shipment;
        });

        return $this->RefreshShipment($discarded_shipment);
    }

    public function ReceiveAtCentre(
        BookShipment $shipment,
        User $actor,
        array $item_results
    ): BookShipment {
        $actor = $this->AssertCentreReceivePermission($actor);
        $normalized_results = $this->NormalizeCentreItemResults($item_results);

        $received_shipment = DB::transaction(function () use (
            $shipment,
            $actor,
            $normalized_results
        ): BookShipment {
            $locked_shipment = $this->LockShipment($shipment);
            if ($locked_shipment->status !== BookShipment::STATUS_DISPATCHED) {
                throw new BookShipmentException(BookShipmentException::STATE_CONFLICT);
            }
            if ((int) $locked_shipment->destination_centre_id !== (int) $actor->department_id) {
                throw new AuthorizationException();
            }

            $items = $this->LockShipmentItems($locked_shipment);
            $item_ids = $items->pluck('id')->map(static fn ($item_id): int => (int) $item_id)->sort()->values()->all();
            $result_ids = array_keys($normalized_results);
            sort($result_ids, SORT_NUMERIC);
            if ($item_ids !== $result_ids) {
                throw new BookShipmentException(BookShipmentException::ITEM_SET_INVALID);
            }

            $source_book_ids = $items
                ->pluck('source_order_detail_list_id')
                ->filter()
                ->map(static fn ($source_book_id): int => (int) $source_book_id)
                ->sort()
                ->values()
                ->all();
            $source_books = $this->LockSourceBooks($source_book_ids)->keyBy('id');

            foreach ($items as $item) {
                $result = $normalized_results[(int) $item->id];
                $source_book = $source_books->get($item->source_order_detail_list_id);
                if (
                    $result['centre_status'] === BookShipmentItem::CENTRE_STATUS_RECEIVED
                    && $source_book
                    && (int) $source_book->active_book_shipment_item_id !== (int) $item->id
                ) {
                    throw new BookShipmentException(BookShipmentException::SOURCE_CONFLICT);
                }

                if (
                    in_array($result['centre_status'], [
                        BookShipmentItem::CENTRE_STATUS_MISSING,
                        BookShipmentItem::CENTRE_STATUS_DAMAGED,
                    ], true)
                    && $source_book
                    && (int) $source_book->active_book_shipment_item_id === (int) $item->id
                ) {
                    $source_book->forceFill(['active_book_shipment_item_id' => null])->save();
                }

                $item->forceFill([
                    'centre_status' => $result['centre_status'],
                    'centre_note' => $result['note'],
                ])->save();
            }

            $centre_received_at = now();
            $locked_shipment->forceFill([
                'status' => BookShipment::STATUS_CENTRE_RECEIVED,
                'centre_received_by' => $actor->id,
                'centre_received_by_name' => $actor->name,
                'centre_received_at' => $centre_received_at,
            ])->save();
            $this->CreateLog(
                $locked_shipment,
                $actor,
                BookShipmentLog::ACTION_CENTRE_RECEIVED,
                $centre_received_at
            );
            SendBookShipmentNotification::dispatch(
                (int) $locked_shipment->id,
                BookShipmentNotification::EVENT_CENTRE_RECEIVED
            )->afterCommit();

            return $locked_shipment;
        });

        return $this->RefreshShipment($received_shipment);
    }

    private function CreateDraftItem(BookShipment $shipment, OrderDetailList $source_book): BookShipmentItem
    {
        $source_book->loadMissing(['order.contact']);
        $student = $source_book->order?->contact;
        $order = $source_book->order;

        return BookShipmentItem::create([
            'book_shipment_id' => $shipment->id,
            'source_order_detail_list_id' => $source_book->id,
            'source_student_id' => $student?->id,
            'source_order_id' => $order?->id,
            'replaces_shipment_item_id' => $source_book->getAttribute('replacement_for_shipment_item_id'),
            'source_student_code' => $student?->code,
            'source_student_name' => $student?->name,
            'source_order_number' => $order?->order_number,
            'book_name' => $source_book->book,
            'level' => $source_book->level,
            'term' => $source_book->term,
            'centre_status' => BookShipmentItem::CENTRE_STATUS_PENDING,
        ]);
    }

    private function ClaimSourceBook(OrderDetailList $source_book, BookShipmentItem $shipment_item): void
    {
        $source_book->forceFill([
            'active_book_shipment_item_id' => $shipment_item->id,
        ])->save();
    }

    private function CreateLog(
        BookShipment $shipment,
        User $actor,
        string $action,
        ?\DateTimeInterface $created_at = null,
        ?string $note = null
    ): BookShipmentLog
    {
        return BookShipmentLog::create([
            'book_shipment_id' => $shipment->id,
            'acted_by' => $actor->id,
            'action' => $action,
            'actor_name' => $actor->name,
            'note' => $note,
            'created_at' => $created_at ?? now(),
        ]);
    }

    private function LockShipment(BookShipment $shipment): BookShipment
    {
        $locked_shipment = BookShipment::query()
            ->whereKey($shipment->id)
            ->lockForUpdate()
            ->first();

        if (!$locked_shipment) {
            throw (new ModelNotFoundException())->setModel(BookShipment::class, [$shipment->id]);
        }

        return $locked_shipment;
    }

    private function LockShipmentItems(BookShipment $shipment): Collection
    {
        return BookShipmentItem::query()
            ->where('book_shipment_id', $shipment->id)
            ->orderBy('id')
            ->lockForUpdate()
            ->get();
    }

    private function NormalizeCentreItemResults(array $item_results): array
    {
        $normalized_results = [];
        foreach ($item_results as $item_result) {
            if (!is_array($item_result)) {
                throw new BookShipmentException(BookShipmentException::ITEM_SET_INVALID);
            }

            $item_id = $item_result['shipment_item_id'] ?? null;
            if (is_string($item_id) && preg_match('/^\d+$/', $item_id) === 1) {
                $item_id = (int) $item_id;
            }
            if (!is_int($item_id) || $item_id < 1 || isset($normalized_results[$item_id])) {
                throw new BookShipmentException(BookShipmentException::ITEM_SET_INVALID);
            }

            $centre_status = $item_result['centre_status'] ?? null;
            if (!in_array($centre_status, [
                BookShipmentItem::CENTRE_STATUS_RECEIVED,
                BookShipmentItem::CENTRE_STATUS_MISSING,
                BookShipmentItem::CENTRE_STATUS_DAMAGED,
            ], true)) {
                throw new BookShipmentException(BookShipmentException::ITEM_SET_INVALID);
            }

            $note = $item_result['note'] ?? null;
            if ($note !== null && !is_string($note)) {
                throw new BookShipmentException(BookShipmentException::ISSUE_NOTE_INVALID);
            }
            $note = $note === null ? null : trim($note);
            if (
                in_array($centre_status, [
                    BookShipmentItem::CENTRE_STATUS_MISSING,
                    BookShipmentItem::CENTRE_STATUS_DAMAGED,
                ], true)
                && ($note === null || $note === '' || mb_strlen($note) > 1000)
            ) {
                throw new BookShipmentException(BookShipmentException::ISSUE_NOTE_INVALID);
            }
            if ($centre_status === BookShipmentItem::CENTRE_STATUS_RECEIVED) {
                $note = null;
            }

            $normalized_results[$item_id] = [
                'centre_status' => $centre_status,
                'note' => $note,
            ];
        }

        return $normalized_results;
    }

    private function AssertCentreReceivePermission(User $actor): User
    {
        $current_actor = User::query()->whereKey($actor->id)->first();
        if (
            !$current_actor
            || $current_actor->isHq()
            || (int) $current_actor->disable !== 0
            || !$current_actor->hasPermissionTo('book-shipment-receive')
            || !$this->IsActiveCentre((int) $current_actor->department_id)
        ) {
            throw new AuthorizationException();
        }

        return $current_actor;
    }

    private function GetDispatchWarnings(int $destination_centre_id): array
    {
        $destination_is_active = Department::query()
            ->whereKey($destination_centre_id)
            ->where('status', 1)
            ->whereKeyNot(Department::HQ_ID)
            ->exists();

        if (!$destination_is_active) {
            return ['NO_ACTIVE_CENTRE_RECIPIENTS'];
        }

        $has_recipient = User::query()
            ->where('department_id', $destination_centre_id)
            ->where('disable', 0)
            ->get()
            ->contains(function (User $user): bool {
                return $user->hasPermissionTo('book-shipment-receive');
            });

        return $has_recipient ? [] : ['NO_ACTIVE_CENTRE_RECIPIENTS'];
    }

    private function IsActiveCentre(int $department_id): bool
    {
        return Department::query()
            ->whereKey($department_id)
            ->where('status', 1)
            ->whereKeyNot(Department::HQ_ID)
            ->exists();
    }

    private function LockSourceBooks(array $source_book_ids): Collection
    {
        if ($source_book_ids === []) {
            return new Collection();
        }

        return OrderDetailList::query()
            ->whereIn('id', $source_book_ids)
            ->orderBy('id')
            ->lockForUpdate()
            ->get();
    }

    private function AssertSourceRowsAreAvailable(array $source_book_ids, Collection $source_books): void
    {
        if ($source_books->count() !== count($source_book_ids)) {
            throw new BookShipmentException(BookShipmentException::SOURCE_INELIGIBLE);
        }

        foreach ($source_books as $source_book) {
            if ($source_book->active_book_shipment_item_id !== null) {
                throw new BookShipmentException(BookShipmentException::SOURCE_CONFLICT);
            }
        }
    }

    private function AssertEligibleSourceSet(array $source_book_ids, Collection $source_books): void
    {
        $actual_ids = $source_books
            ->pluck('id')
            ->map(static fn ($source_book_id): int => (int) $source_book_id)
            ->sort()
            ->values()
            ->all();
        $expected_ids = collect($source_book_ids)
            ->map(static fn ($source_book_id): int => (int) $source_book_id)
            ->sort()
            ->values()
            ->all();

        if ($actual_ids !== $expected_ids) {
            throw new BookShipmentException(BookShipmentException::SOURCE_INELIGIBLE);
        }
    }

    private function NormalizeSourceBookIds(array $source_book_ids): array
    {
        if ($source_book_ids === []) {
            throw new BookShipmentException(BookShipmentException::SELECTION_INVALID);
        }

        $normalized_ids = [];
        foreach ($source_book_ids as $source_book_id) {
            if (is_int($source_book_id)) {
                $normalized_id = $source_book_id;
            } elseif (is_string($source_book_id) && preg_match('/^\d+$/', $source_book_id) === 1) {
                $normalized_id = (int) $source_book_id;
            } else {
                throw new BookShipmentException(BookShipmentException::SELECTION_INVALID);
            }

            if ($normalized_id < 1) {
                throw new BookShipmentException(BookShipmentException::SELECTION_INVALID);
            }

            $normalized_ids[] = $normalized_id;
        }

        if (count(array_unique($normalized_ids)) !== count($normalized_ids)) {
            throw new BookShipmentException(BookShipmentException::SELECTION_INVALID);
        }

        sort($normalized_ids, SORT_NUMERIC);

        return $normalized_ids;
    }

    private function NormalizeCancellationReason(string $reason): string
    {
        $normalized_reason = trim($reason);
        if (mb_strlen($normalized_reason) < 3 || mb_strlen($normalized_reason) > 1000) {
            throw new BookShipmentException(BookShipmentException::CANCELLATION_REASON_INVALID);
        }

        return $normalized_reason;
    }

    private function AssertHqPermission(User $actor, string $permission): void
    {
        if (
            !$actor->isHq()
            || (int) $actor->disable !== 0
            || !$actor->hasPermissionTo($permission)
        ) {
            throw new AuthorizationException();
        }
    }

    private function GetEligibleDestination(int $destination_centre_id): Department
    {
        $destination_centre = Department::query()
            ->whereKey($destination_centre_id)
            ->where('status', 1)
            ->whereKeyNot(Department::HQ_ID)
            ->first();

        if (!$destination_centre) {
            throw new BookShipmentException(BookShipmentException::DESTINATION_INELIGIBLE);
        }

        return $destination_centre;
    }

    private function BuildShipmentNumber(int $shipment_id): string
    {
        return 'BS-' . now()->format('Ym') . '-' . str_pad((string) $shipment_id, 6, '0', STR_PAD_LEFT);
    }

    private function RefreshShipment(BookShipment $shipment): BookShipment
    {
        return $shipment->fresh([
            'DestinationCentre',
            'Items',
            'Logs',
        ]);
    }
}
