<?php

namespace App\Services;

use App\Exceptions\BookReceiptException;
use App\Jobs\SendBookShipmentNotification;
use App\Models\BookReceiptLog;
use App\Models\BookShipment;
use App\Models\BookShipmentItem;
use App\Models\Contact;
use App\Models\Order;
use App\Models\OrderDetailList;
use App\Models\User;
use App\Notifications\BookShipmentNotification;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class BookReceiptService
{
    public function GetStudentBookReceipts(
        Contact $student,
        User $actor,
        ?int $shipment_id = null
    ): array
    {
        $this->AssertStudentAccess($student, $actor);
        if ($shipment_id !== null) {
            $this->ResolveShipmentForStudent($student, $actor, $shipment_id);
        }

        $orders = Order::query()
            ->where('cid', $student->id)
            ->with([
                'orderDetailLists' => function ($query) use ($shipment_id, $student): void {
                    $query
                        ->whereNotNull('book')
                        ->whereRaw("TRIM(book) <> ''")
                        ->with('receivedBy')
                        ->orderBy('id');

                    if ($shipment_id !== null) {
                        $this->AddShipmentBookFilter($query, $shipment_id, $student->id);
                    }
                },
            ])
            ->orderBy('id')
            ->get();

        $book_count = 0;
        $received_count = 0;
        $order_data = [];

        foreach ($orders as $order) {
            $book_data = [];

            foreach ($order->orderDetailLists as $book) {
                $is_received = $book->received_at !== null;
                $book_count++;
                $received_count += $is_received ? 1 : 0;

                $book_data[] = [
                    'id' => $book->id,
                    'book_name' => $book->book,
                    'level' => $book->level,
                    'term' => $book->term,
                    'is_received' => $is_received,
                    'received_at' => $book->received_at?->toIso8601String(),
                    'received_by_name' => $book->receivedBy?->name,
                ];
            }

            if ($book_data !== []) {
                $order_data[] = [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'books' => $book_data,
                ];
            }
        }

        return [
            'student' => [
                'id' => $student->id,
                'code' => $student->code,
                'name' => $student->name,
            ],
            'summary' => [
                'received_count' => $received_count,
                'total_count' => $book_count,
            ],
            'orders' => $order_data,
        ];
    }

    public function GetBookReceiptLogs(
        Contact $student,
        OrderDetailList $book,
        User $actor,
        ?int $shipment_id = null
    ): Collection
    {
        $this->AssertStudentAccess($student, $actor);
        if ($shipment_id !== null) {
            $this->ResolveShipmentForStudent($student, $actor, $shipment_id);
        }
        $resolved_book = $this->ResolveBook($student, $book);
        if ($shipment_id !== null) {
            $this->AssertBookInShipment($resolved_book, $student, $shipment_id);
        }

        return $resolved_book->receiptLogs()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();
    }

    public function MarkReceived(
        Contact $student,
        OrderDetailList $book,
        User $actor,
        ?int $shipment_id = null
    ): array
    {
        return DB::transaction(function () use ($student, $book, $actor, $shipment_id): array {
            $this->AssertStudentAccess($student, $actor);
            if ($shipment_id !== null) {
                $this->ResolveShipmentForStudent($student, $actor, $shipment_id);
            }
            $resolved_book = $this->ResolveBook($student, $book, true);
            if ($shipment_id !== null) {
                $this->AssertBookInShipment($resolved_book, $student, $shipment_id);
            }
            $this->AssertBookShipmentEligibility($resolved_book);
            $shipment_ids = $this->GetShipmentIdsForBooks([$resolved_book->id]);

            if ($resolved_book->received_at !== null) {
                throw new BookReceiptException(BookReceiptException::ALREADY_RECEIVED);
            }

            $received_at = now();
            $resolved_book->forceFill([
                'received_at' => $received_at,
                'received_by' => $actor->id,
            ])->save();

            $this->CreateLog($student, $resolved_book, $actor, BookReceiptLog::ACTION_RECEIVED, $received_at);
            $this->QueueCompletionChecks($shipment_ids);

            return $this->GetStudentBookReceipts($student, $actor, $shipment_id);
        });
    }

    public function MarkAllReceived(
        Contact $student,
        User $actor,
        ?int $shipment_id = null
    ): array
    {
        return DB::transaction(function () use ($student, $actor, $shipment_id): array {
            $this->AssertStudentAccess($student, $actor);
            if ($shipment_id !== null) {
                $this->ResolveShipmentForStudent($student, $actor, $shipment_id);
            }

            $books = OrderDetailList::query()
                ->with('order')
                ->whereHas('order', function ($order_query) use ($student): void {
                    $order_query->where('cid', $student->id);
                })
                ->whereNotNull('book')
                ->whereRaw("TRIM(book) <> ''")
                ->when($shipment_id !== null, function ($query) use ($shipment_id, $student): void {
                    $this->AddShipmentBookFilter($query, $shipment_id, $student->id);
                })
                ->lockForUpdate()
                ->get();

            foreach ($books as $book) {
                if ($book->received_at === null) {
                    $this->AssertBookShipmentEligibility($book);
                }
            }

            $received_at = now();
            $changed_book_ids = [];

            foreach ($books as $book) {
                if ($book->received_at !== null) {
                    continue;
                }

                $book->forceFill([
                    'received_at' => $received_at,
                    'received_by' => $actor->id,
                ])->save();

                $this->CreateLog($student, $book, $actor, BookReceiptLog::ACTION_RECEIVED, $received_at);
                $changed_book_ids[] = (int) $book->id;
            }
            $this->QueueCompletionChecks($this->GetShipmentIdsForBooks($changed_book_ids));

            return $this->GetStudentBookReceipts($student, $actor, $shipment_id);
        });
    }

    public function CancelReceipt(
        Contact $student,
        OrderDetailList $book,
        User $actor,
        ?int $shipment_id = null
    ): array
    {
        return DB::transaction(function () use ($student, $book, $actor, $shipment_id): array {
            $this->AssertStudentAccess($student, $actor);
            if ($shipment_id !== null) {
                $this->ResolveShipmentForStudent($student, $actor, $shipment_id);
            }
            $resolved_book = $this->ResolveBook($student, $book, true);
            if ($shipment_id !== null) {
                $this->AssertBookInShipment($resolved_book, $student, $shipment_id);
            }

            if ($resolved_book->received_at === null) {
                throw new BookReceiptException(BookReceiptException::NOT_RECEIVED);
            }

            $resolved_book->forceFill([
                'received_at' => null,
                'received_by' => null,
            ])->save();

            $this->CreateLog($student, $resolved_book, $actor, BookReceiptLog::ACTION_CANCELLED, now());

            return $this->GetStudentBookReceipts($student, $actor, $shipment_id);
        });
    }

    public function GetReceiptSummary(
        Contact $student,
        User $actor,
        ?int $shipment_id = null
    ): array
    {
        return $this->GetStudentBookReceipts($student, $actor, $shipment_id)['summary'];
    }

    public function ResolveShipmentForActor(int $shipment_id, User $actor): BookShipment
    {
        $shipment = BookShipment::query()
            ->with('DestinationCentre')
            ->whereKey($shipment_id)
            ->first();

        if (!$shipment) {
            throw (new ModelNotFoundException())->setModel(BookShipment::class, [$shipment_id]);
        }

        if (!$actor->isHq() && (int) $shipment->destination_centre_id !== (int) $actor->department_id) {
            throw new AuthorizationException('You are not allowed to access this shipment.');
        }

        return $shipment;
    }

    public function AssertStudentAccess(Contact $student, User $actor): void
    {
        if ($student->type !== 'student') {
            throw (new ModelNotFoundException())->setModel(Contact::class, [$student->id]);
        }

        if (!$actor->isHq() && (int) $actor->department_id !== (int) $student->centre) {
            throw new AuthorizationException('You are not allowed to access this student.');
        }
    }

    private function ResolveShipmentForStudent(
        Contact $student,
        User $actor,
        int $shipment_id
    ): BookShipment {
        $shipment = $this->ResolveShipmentForActor($shipment_id, $actor);
        $belongs_to_shipment = BookShipmentItem::query()
            ->where('book_shipment_id', $shipment->id)
            ->where('source_student_id', $student->id)
            ->exists();

        if (!$belongs_to_shipment) {
            throw (new ModelNotFoundException())->setModel(BookShipmentItem::class, [$shipment_id]);
        }

        return $shipment;
    }

    private function AddShipmentBookFilter($query, int $shipment_id, int $student_id): void
    {
        $query->whereExists(function ($shipment_query) use ($shipment_id, $student_id): void {
            $shipment_query
                ->select(DB::raw('1'))
                ->from('book_shipment_items as receipt_shipment_items')
                ->whereColumn(
                    'receipt_shipment_items.source_order_detail_list_id',
                    'order_detail_lists.id'
                )
                ->where('receipt_shipment_items.book_shipment_id', $shipment_id)
                ->where('receipt_shipment_items.source_student_id', $student_id);
        });
    }

    private function AssertBookInShipment(
        OrderDetailList $book,
        Contact $student,
        int $shipment_id
    ): void {
        $belongs_to_shipment = BookShipmentItem::query()
            ->where('book_shipment_id', $shipment_id)
            ->where('source_order_detail_list_id', $book->id)
            ->where('source_student_id', $student->id)
            ->exists();

        if (!$belongs_to_shipment) {
            throw (new ModelNotFoundException())->setModel(OrderDetailList::class, [$book->id]);
        }
    }

    private function AssertBookShipmentEligibility(OrderDetailList $book): void
    {
        $active_item = $book->ActiveBookShipmentItem()
            ->with('BookShipment')
            ->first();

        if ($active_item) {
            if ($active_item->BookShipment?->status === BookShipment::STATUS_CANCELLED) {
                return;
            }

            if (!$this->IsCentreReceivedShipmentItem($active_item)) {
                throw new BookReceiptException(BookReceiptException::SHIPMENT_BLOCKED);
            }

            return;
        }

        $latest_item = BookShipmentItem::query()
            ->with('BookShipment')
            ->where('source_order_detail_list_id', $book->id)
            ->whereHas('BookShipment', function ($shipment_query): void {
                $shipment_query->where('status', '<>', BookShipment::STATUS_CANCELLED);
            })
            ->orderByDesc('id')
            ->first();

        if (!$latest_item || !$latest_item->BookShipment) {
            return;
        }

        if (!$this->IsCentreReceivedShipmentItem($latest_item)) {
            throw new BookReceiptException(BookReceiptException::SHIPMENT_BLOCKED);
        }
    }

    private function IsCentreReceivedShipmentItem(BookShipmentItem $item): bool
    {
        return $item->centre_status === BookShipmentItem::CENTRE_STATUS_RECEIVED
            && $item->BookShipment?->status === BookShipment::STATUS_CENTRE_RECEIVED;
    }

    private function GetShipmentIdsForBooks(array $book_ids): array
    {
        if ($book_ids === []) {
            return [];
        }

        return BookShipmentItem::query()
            ->whereIn('source_order_detail_list_id', $book_ids)
            ->whereHas('BookShipment', function ($shipment_query): void {
                $shipment_query->where('status', '<>', BookShipment::STATUS_CANCELLED);
            })
            ->pluck('book_shipment_id')
            ->map(static fn ($shipment_id): int => (int) $shipment_id)
            ->unique()
            ->values()
            ->all();
    }

    private function QueueCompletionChecks(array $shipment_ids): void
    {
        foreach ($shipment_ids as $shipment_id) {
            SendBookShipmentNotification::dispatch(
                $shipment_id,
                BookShipmentNotification::EVENT_STUDENT_COMPLETED
            )->afterCommit();
        }
    }

    private function ResolveBook(Contact $student, OrderDetailList $book, bool $lock_for_update = false): OrderDetailList
    {
        $query = OrderDetailList::query()
            ->with(['order.contact'])
            ->whereKey($book->id)
            ->whereHas('order', function ($order_query) use ($student): void {
                $order_query->where('cid', $student->id);
            });

        if ($lock_for_update) {
            $query->lockForUpdate();
        }

        $resolved_book = $query->first();

        if (!$resolved_book) {
            throw (new ModelNotFoundException())->setModel(OrderDetailList::class, [$book->id]);
        }

        if (trim((string) $resolved_book->book) === '') {
            throw new BookReceiptException(BookReceiptException::INELIGIBLE);
        }

        return $resolved_book;
    }

    private function CreateLog(
        Contact $student,
        OrderDetailList $book,
        User $actor,
        string $action,
        \DateTimeInterface $created_at
    ): BookReceiptLog {
        $book->loadMissing('order');

        return BookReceiptLog::create([
            'order_detail_list_id' => $book->id,
            'student_id' => $student->id,
            'acted_by' => $actor->id,
            'action' => $action,
            'order_number' => $book->order?->order_number,
            'book_name' => $book->book,
            'student_code' => $student->code,
            'student_name' => $student->name,
            'actor_name' => $actor->name,
            'created_at' => $created_at,
        ]);
    }
}
