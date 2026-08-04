<?php

namespace App\Services\BookShipments;

use App\Exceptions\BookShipmentException;
use App\Models\BookShipment;
use App\Models\BookShipmentItem;
use App\Models\Department;
use App\Models\OrderDetailList;
use App\Models\User;
use App\Notifications\BookShipmentNotification;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class BookShipmentQuery
{
    public function GetCandidates(
        User $actor,
        int $destination_centre_id,
        ?string $search,
        int $per_page,
        ?int $draft_shipment_id = null
    ): LengthAwarePaginator {
        $this->AssertHqPermission($actor, 'book-shipment-create');
        $this->AssertEligibleDestination($destination_centre_id);

        $allowed_shipment_id = $this->ResolveCandidateDraftId(
            $destination_centre_id,
            $draft_shipment_id
        );
        $per_page = $this->NormalizePerPage($per_page);
        $candidate_query = $this->BuildCandidateQuery(
            $destination_centre_id,
            $search,
            $allowed_shipment_id
        );
        $total_students = (clone $candidate_query)
            ->distinct()
            ->count('contacts.id');
        $current_page = LengthAwarePaginator::resolveCurrentPage();
        $student_rows = (clone $candidate_query)
            ->select([
                'contacts.id as student_id',
                'contacts.created_at as student_created_at',
            ])
            ->distinct()
            ->orderByDesc('contacts.created_at')
            ->orderByDesc('contacts.id')
            ->forPage($current_page, $per_page)
            ->get();

        $student_ids = $student_rows
            ->pluck('student_id')
            ->map(static fn ($student_id): int => (int) $student_id)
            ->all();

        if ($student_ids === []) {
            return new LengthAwarePaginator(
                collect(),
                $total_students,
                $per_page,
                $current_page,
                [
                    'path' => LengthAwarePaginator::resolveCurrentPath(),
                    'pageName' => 'page',
                ]
            );
        }

        $book_query = (clone $candidate_query)
            ->whereIn('contacts.id', $student_ids)
            ->select([
                'contacts.id as student_id',
                'contacts.code as student_code',
                'contacts.name as student_name',
                'contacts.centre as centre_id',
                'orders.id as order_id',
                'orders.order_number',
                'order_detail_lists.id as source_book_id',
                'order_detail_lists.book as book_name',
                'order_detail_lists.level',
                'order_detail_lists.term',
            ])
            ->selectSub($this->BuildLatestUnresolvedIssueQuery(), 'replacement_for_shipment_item_id')
            ->orderByDesc('contacts.created_at')
            ->orderByDesc('contacts.id')
            ->orderBy('order_detail_lists.id');

        $students = [];
        foreach ($book_query->get() as $book_row) {
            $student_id = (int) $book_row->student_id;
            $students[$student_id] ??= [
                'student_id' => $student_id,
                'student_code' => $book_row->student_code,
                'student_name' => $book_row->student_name,
                'centre_id' => (int) $book_row->centre_id,
                'books' => [],
            ];
            $students[$student_id]['books'][] = [
                'source_book_id' => (int) $book_row->source_book_id,
                'book_name' => $book_row->book_name,
                'level' => $book_row->level,
                'term' => $book_row->term,
                'order_id' => (int) $book_row->order_id,
                'order_number' => $book_row->order_number,
                'replacement_for_shipment_item_id' => $book_row->replacement_for_shipment_item_id === null
                    ? null
                    : (int) $book_row->replacement_for_shipment_item_id,
            ];
        }

        return new LengthAwarePaginator(
            collect(array_values($students)),
            $total_students,
            $per_page,
            $current_page,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'pageName' => 'page',
            ]
        );
    }

    private function ResolveCandidateDraftId(
        int $destination_centre_id,
        ?int $draft_shipment_id
    ): ?int {
        if ($draft_shipment_id === null) {
            return null;
        }

        $shipment = BookShipment::query()->whereKey($draft_shipment_id)->first();
        if (!$shipment) {
            throw (new ModelNotFoundException())->setModel(BookShipment::class, [$draft_shipment_id]);
        }

        if (
            $shipment->status !== BookShipment::STATUS_DRAFT
            || (int) $shipment->destination_centre_id !== $destination_centre_id
        ) {
            throw new BookShipmentException(BookShipmentException::STATE_CONFLICT);
        }

        return (int) $shipment->id;
    }

    public function GetEligibleSourceBooks(
        User $actor,
        int $destination_centre_id,
        array $source_book_ids,
        ?int $allowed_shipment_id = null
    ): EloquentCollection {
        $this->AssertHqPermission($actor, 'book-shipment-create');
        $this->AssertEligibleDestination($destination_centre_id);

        $candidate_query = $this->BuildCandidateQuery(
            $destination_centre_id,
            null,
            $allowed_shipment_id
        );
        $eligible_id_query = (clone $candidate_query)
            ->select('order_detail_lists.id');

        return OrderDetailList::query()
            ->select('order_detail_lists.*')
            ->with(['order.contact'])
            ->whereIn('order_detail_lists.id', $source_book_ids)
            ->whereIn('order_detail_lists.id', $eligible_id_query)
            ->selectSub($this->BuildLatestUnresolvedIssueQuery(), 'replacement_for_shipment_item_id')
            ->orderBy('order_detail_lists.id')
            ->lockForUpdate()
            ->get();
    }

    public function GetHqShipments(User $actor, array $filters, int $per_page): LengthAwarePaginator
    {
        $this->AssertHqPermission($actor, 'book-shipment-list');

        return $this->GetShipmentList($filters, $per_page);
    }

    public function GetEligibleDestinations(User $actor): EloquentCollection
    {
        if (!$actor->isHq() || (int) $actor->disable !== 0) {
            throw new AuthorizationException();
        }

        return Department::query()
            ->where('status', 1)
            ->whereKeyNot(Department::HQ_ID)
            ->orderBy('name')
            ->get();
    }

    public function GetCentreShipments(User $actor, array $filters, int $per_page): LengthAwarePaginator
    {
        $this->AssertCentrePermission($actor, 'book-shipment-receive');

        return $this->GetShipmentList($filters, $per_page, (int) $actor->department_id);
    }

    public function GetCentrePendingSummary(User $actor): array
    {
        $this->AssertCentrePermission($actor, 'book-shipment-receive');

        $pending_query = BookShipment::query()
            ->where('destination_centre_id', $actor->department_id)
            ->where('status', BookShipment::STATUS_DISPATCHED);
        $pending_count = (clone $pending_query)->count();
        $latest_shipment = (clone $pending_query)
            ->select(['id', 'shipment_number', 'dispatched_at'])
            ->withCount('Items')
            ->orderByDesc('dispatched_at')
            ->orderByDesc('id')
            ->first();

        return [
            'pending_count' => $pending_count,
            'latest_shipment' => $latest_shipment === null ? null : [
                'id' => (int) $latest_shipment->id,
                'shipment_number' => $latest_shipment->shipment_number,
                'item_count' => (int) $latest_shipment->items_count,
                'dispatched_at' => $latest_shipment->dispatched_at?->toIso8601String(),
            ],
        ];
    }

    public function GetHqReceiptAlertSummary(User $actor): array
    {
        $this->AssertHqPermission($actor, 'book-shipment-list');

        $hq_is_active = Department::query()
            ->whereKey(Department::HQ_ID)
            ->where('status', 1)
            ->exists();
        if (!$hq_is_active) {
            throw new AuthorizationException();
        }

        $receipt_notifications = $actor->notifications()
            ->where('type', BookShipmentNotification::class)
            ->where('data->event_type', BookShipmentNotification::EVENT_CENTRE_RECEIVED);
        $unread_count = (clone $receipt_notifications)
            ->whereNull('read_at')
            ->count();
        $latest_event = (clone $receipt_notifications)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->first();
        $latest_unread_notification = (clone $receipt_notifications)
            ->whereNull('read_at')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->first();

        return [
            'active_shipment_count' => $this->CountActiveHqShipments(),
            'unread_count' => $unread_count,
            'latest_event_cursor' => $latest_event === null
                ? null
                : $latest_event->created_at?->toIso8601String() . '|' . $latest_event->id,
            'latest_unread_notification' => $this->MapReceiptAlertNotification($latest_unread_notification),
        ];
    }

    private function CountActiveHqShipments(): int
    {
        return (int) BookShipment::query()
            ->where(function (EloquentBuilder $active_query): void {
                $active_query
                    ->whereIn('book_shipments.status', [
                        BookShipment::STATUS_DRAFT,
                        BookShipment::STATUS_DISPATCHED,
                    ])
                    ->orWhere(function (EloquentBuilder $centre_received_query): void {
                        $centre_received_query
                            ->where('book_shipments.status', BookShipment::STATUS_CENTRE_RECEIVED)
                            ->whereNot(function (EloquentBuilder $completed_query): void {
                                $this->ApplyDerivedStatusFilter(
                                    $completed_query,
                                    BookShipment::DERIVED_COMPLETED
                                );
                            })
                            ->whereNot(function (EloquentBuilder $issue_query): void {
                                $this->ApplyDerivedStatusFilter(
                                    $issue_query,
                                    BookShipment::DERIVED_COMPLETED_WITH_ISSUES
                                );
                            });
                    });
            })
            ->count();
    }

    private function MapReceiptAlertNotification($notification): ?array
    {
        if ($notification === null) {
            return null;
        }

        $notification_data = is_array($notification->data) ? $notification->data : [];
        $shipment_id = filter_var($notification_data['shipment_id'] ?? null, FILTER_VALIDATE_INT);
        $shipment_number = $notification_data['shipment_number'] ?? null;
        $received_at = $notification->created_at?->toIso8601String();
        if (
            $shipment_id === false
            || $shipment_id < 1
            || !is_string($shipment_number)
            || trim($shipment_number) === ''
            || $received_at === null
        ) {
            return null;
        }

        return [
            'id' => (string) $notification->id,
            'shipment_id' => (int) $shipment_id,
            'shipment_number' => $shipment_number,
            'received_at' => $received_at,
            'read_url' => route(
                'book-shipment-notifications.read',
                ['notification_id' => $notification->id],
                false
            ),
        ];
    }

    public function GetShipmentForActor(
        int $shipment_id,
        User $actor,
        string $required_permission
    ): BookShipment {
        if ($required_permission === 'book-shipment-list') {
            $this->AssertHqPermission($actor, $required_permission);
        } elseif ($required_permission === 'book-shipment-receive') {
            $this->AssertCentrePermission($actor, $required_permission);
        } else {
            throw new AuthorizationException();
        }

        $query = BookShipment::query()
            ->with([
                'DestinationCentre',
                'Items.SourceBook',
                'Items.SourceStudent',
                'Items.SourceOrder',
                'Items.ReplacesShipmentItem',
                'Logs',
            ])
            ->whereKey($shipment_id);

        if (!$actor->isHq()) {
            $query->where('destination_centre_id', $actor->department_id);
        }

        $shipment = $query->first();
        if (!$shipment) {
            throw (new ModelNotFoundException())->setModel(BookShipment::class, [$shipment_id]);
        }

        $progress = $this->GetProgress($shipment);
        $shipment->setAttribute('progress', $progress);
        $shipment->setAttribute('derived_status', $this->GetDerivedStatusFromProgress($shipment, $progress));

        return $shipment;
    }

    public function GetProgress(BookShipment $shipment): array
    {
        $row = DB::table('book_shipment_items as progress_items')
            ->leftJoin(
                'order_detail_lists as progress_books',
                'progress_books.id',
                '=',
                'progress_items.source_order_detail_list_id'
            )
            ->where('progress_items.book_shipment_id', $shipment->id)
            ->selectRaw('COUNT(*) as total_count')
            ->selectRaw(
                'SUM(CASE WHEN progress_items.centre_status = ? THEN 1 ELSE 0 END) as centre_received_count',
                [BookShipmentItem::CENTRE_STATUS_RECEIVED]
            )
            ->selectRaw(
                'SUM(CASE WHEN progress_items.centre_status = ? THEN 1 ELSE 0 END) as missing_count',
                [BookShipmentItem::CENTRE_STATUS_MISSING]
            )
            ->selectRaw(
                'SUM(CASE WHEN progress_items.centre_status = ? THEN 1 ELSE 0 END) as damaged_count',
                [BookShipmentItem::CENTRE_STATUS_DAMAGED]
            )
            ->selectRaw(
                'SUM(CASE WHEN progress_items.centre_status = ? THEN 1 ELSE 0 END) as pending_count',
                [BookShipmentItem::CENTRE_STATUS_PENDING]
            )
            ->selectRaw(
                'SUM(CASE WHEN progress_items.centre_status = ? AND progress_books.received_at IS NOT NULL THEN 1 ELSE 0 END) as student_received_count',
                [BookShipmentItem::CENTRE_STATUS_RECEIVED]
            )
            ->first();

        return [
            'total_count' => (int) ($row->total_count ?? 0),
            'centre_received_count' => (int) ($row->centre_received_count ?? 0),
            'missing_count' => (int) ($row->missing_count ?? 0),
            'damaged_count' => (int) ($row->damaged_count ?? 0),
            'pending_count' => (int) ($row->pending_count ?? 0),
            'student_received_count' => (int) ($row->student_received_count ?? 0),
        ];
    }

    public function GetDerivedStatus(BookShipment $shipment): string
    {
        $progress = $shipment->getAttribute('progress');
        if (!is_array($progress)) {
            $progress = $this->GetProgress($shipment);
        }

        return $this->GetDerivedStatusFromProgress($shipment, $progress);
    }

    private function GetShipmentList(
        array $filters,
        int $per_page,
        ?int $destination_centre_id = null
    ): LengthAwarePaginator {
        $query = BookShipment::query()
            ->select('book_shipments.*')
            ->with('DestinationCentre')
            ->orderByDesc('book_shipments.created_at')
            ->orderByDesc('book_shipments.id');

        $this->AddProgressSelections($query);

        if ($destination_centre_id !== null) {
            $query->where('book_shipments.destination_centre_id', $destination_centre_id);
        }

        $this->ApplyShipmentFilters($query, $filters);
        $paginator = $query->paginate($this->NormalizePerPage($per_page));

        foreach ($paginator->items() as $shipment) {
            $progress = $this->ProgressFromSelectedAttributes($shipment);
            $shipment->setAttribute('progress', $progress);
            $shipment->setAttribute('derived_status', $this->GetDerivedStatusFromProgress($shipment, $progress));
        }

        return $paginator;
    }

    private function BuildCandidateQuery(
        int $destination_centre_id,
        ?string $search,
        ?int $allowed_shipment_id = null
    ): QueryBuilder
    {
        $query = DB::table('order_detail_lists')
            ->join('orders', 'orders.id', '=', 'order_detail_lists.order_id')
            ->join('contacts', 'contacts.id', '=', 'orders.cid')
            ->where('contacts.type', 'student')
            ->whereColumn('contacts.new_student_order_id', 'orders.id')
            ->whereColumn('orders.cid', 'contacts.id')
            ->whereColumn('orders.centre', 'contacts.centre')
            ->where('orders.centre', $destination_centre_id)
            ->whereNotNull('order_detail_lists.book')
            ->whereRaw("TRIM(order_detail_lists.book) <> ''")
            ->whereNull('order_detail_lists.received_at')
            ->where(function (QueryBuilder $active_query) use ($allowed_shipment_id): void {
                $active_query->whereNull('order_detail_lists.active_book_shipment_item_id');

                if ($allowed_shipment_id !== null) {
                    $active_query->orWhereExists(function (QueryBuilder $current_shipment_query) use ($allowed_shipment_id): void {
                        $current_shipment_query
                            ->selectRaw('1')
                            ->from('book_shipment_items as current_shipment_items')
                            ->whereColumn(
                                'current_shipment_items.id',
                                'order_detail_lists.active_book_shipment_item_id'
                            )
                            ->where('current_shipment_items.book_shipment_id', $allowed_shipment_id);
                    });
                }
            });

        $search = trim((string) $search);
        if ($search !== '') {
            $query->where(function (QueryBuilder $search_query) use ($search): void {
                $search_like = '%' . $search . '%';
                $search_query
                    ->where('contacts.code', 'like', $search_like)
                    ->orWhere('contacts.name', 'like', $search_like)
                    ->orWhere('orders.order_number', 'like', $search_like)
                    ->orWhere('order_detail_lists.book', 'like', $search_like);
            });
        }

        return $query;
    }

    private function BuildLatestUnresolvedIssueQuery(): QueryBuilder
    {
        return DB::table('book_shipment_items as unresolved_items')
            ->select('unresolved_items.id')
            ->whereColumn(
                'unresolved_items.source_order_detail_list_id',
                'order_detail_lists.id'
            )
            ->whereIn('unresolved_items.centre_status', [
                BookShipmentItem::CENTRE_STATUS_MISSING,
                BookShipmentItem::CENTRE_STATUS_DAMAGED,
            ])
            ->whereNotExists(function (QueryBuilder $replacement_query): void {
                $replacement_query
                    ->selectRaw('1')
                    ->from('book_shipment_items as replacement_items')
                    ->whereColumn(
                        'replacement_items.replaces_shipment_item_id',
                        'unresolved_items.id'
                    )
                    ->whereColumn('replacement_items.id', '>', 'unresolved_items.id');
            })
            ->orderByDesc('unresolved_items.id')
            ->limit(1);
    }

    private function AddProgressSelections(EloquentBuilder $query): void
    {
        $query
            ->selectSub($this->BuildItemCountQuery(), 'progress_total_count')
            ->selectSub(
                $this->BuildItemCountQuery([BookShipmentItem::CENTRE_STATUS_RECEIVED]),
                'progress_centre_received_count'
            )
            ->selectSub(
                $this->BuildItemCountQuery([BookShipmentItem::CENTRE_STATUS_MISSING]),
                'progress_missing_count'
            )
            ->selectSub(
                $this->BuildItemCountQuery([BookShipmentItem::CENTRE_STATUS_DAMAGED]),
                'progress_damaged_count'
            )
            ->selectSub(
                $this->BuildItemCountQuery([BookShipmentItem::CENTRE_STATUS_PENDING]),
                'progress_pending_count'
            )
            ->selectSub(
                $this->BuildItemCountQuery([BookShipmentItem::CENTRE_STATUS_RECEIVED], true),
                'progress_student_received_count'
            );
    }

    private function BuildItemCountQuery(?array $statuses = null, bool $student_received = false): QueryBuilder
    {
        $query = DB::table('book_shipment_items as counted_items')
            ->selectRaw('COUNT(*)')
            ->whereColumn('counted_items.book_shipment_id', 'book_shipments.id');

        if ($statuses !== null) {
            $query->whereIn('counted_items.centre_status', $statuses);
        }

        if ($student_received) {
            $query
                ->join(
                    'order_detail_lists as counted_books',
                    'counted_books.id',
                    '=',
                    'counted_items.source_order_detail_list_id'
                )
                ->whereNotNull('counted_books.received_at');
        }

        return $query;
    }

    private function ApplyShipmentFilters(EloquentBuilder $query, array $filters): void
    {
        $shipment_number = trim((string) ($filters['shipment_number'] ?? $filters['search'] ?? ''));
        if ($shipment_number !== '') {
            $query->where('book_shipments.shipment_number', 'like', '%' . $shipment_number . '%');
        }

        if (isset($filters['destination_centre_id'])) {
            $query->where('book_shipments.destination_centre_id', (int) $filters['destination_centre_id']);
        }

        $stored_status = $filters['status'] ?? $filters['stored_status'] ?? null;
        if ($stored_status !== null && $stored_status !== '') {
            $query->where('book_shipments.status', $stored_status);
        }

        $created_from = $filters['created_from'] ?? $filters['created_date_from'] ?? null;
        if ($created_from !== null && $created_from !== '') {
            $query->whereDate('book_shipments.created_at', '>=', $created_from);
        }

        $created_to = $filters['created_to'] ?? $filters['created_date_to'] ?? null;
        if ($created_to !== null && $created_to !== '') {
            $query->whereDate('book_shipments.created_at', '<=', $created_to);
        }

        $derived_status = $filters['derived_status'] ?? null;
        if ($derived_status !== null && $derived_status !== '') {
            $this->ApplyDerivedStatusFilter($query, $derived_status);
        }
    }

    private function ApplyDerivedStatusFilter(EloquentBuilder $query, string $derived_status): void
    {
        switch ($derived_status) {
            case BookShipment::DERIVED_DRAFT:
                $query->where('book_shipments.status', BookShipment::STATUS_DRAFT);
                return;
            case BookShipment::DERIVED_CANCELLED:
                $query->where('book_shipments.status', BookShipment::STATUS_CANCELLED);
                return;
            case BookShipment::DERIVED_WAITING_FOR_CENTRE:
                $query->where(function (EloquentBuilder $status_query): void {
                    $status_query
                        ->where('book_shipments.status', BookShipment::STATUS_DISPATCHED)
                        ->orWhere(function (EloquentBuilder $received_query): void {
                            $received_query->where(
                                'book_shipments.status',
                                BookShipment::STATUS_CENTRE_RECEIVED
                            );
                            $this->AddCountCondition($received_query, [BookShipmentItem::CENTRE_STATUS_PENDING], '>', 0, false);
                        });
                });
                return;
            case BookShipment::DERIVED_PENDING_STUDENT_DELIVERY:
                $query->where('book_shipments.status', BookShipment::STATUS_CENTRE_RECEIVED);
                $this->AddCountCondition($query, [BookShipmentItem::CENTRE_STATUS_PENDING], '=', 0, false);
                $this->AddCountCondition($query, [BookShipmentItem::CENTRE_STATUS_RECEIVED], '>', 0, false);
                $this->AddCountComparison(
                    $query,
                    [BookShipmentItem::CENTRE_STATUS_RECEIVED],
                    '>',
                    [BookShipmentItem::CENTRE_STATUS_RECEIVED],
                    true,
                    false
                );
                return;
            case BookShipment::DERIVED_COMPLETED:
                $query->where('book_shipments.status', BookShipment::STATUS_CENTRE_RECEIVED);
                $this->AddCountCondition($query, [BookShipmentItem::CENTRE_STATUS_PENDING], '=', 0, false);
                $this->AddCountCondition($query, [BookShipmentItem::CENTRE_STATUS_MISSING], '=', 0, false);
                $this->AddCountCondition($query, [BookShipmentItem::CENTRE_STATUS_DAMAGED], '=', 0, false);
                $this->AddCountCondition($query, null, '>', 0, false);
                $this->AddCountComparison(
                    $query,
                    null,
                    '=',
                    [BookShipmentItem::CENTRE_STATUS_RECEIVED],
                    false,
                    false
                );
                $this->AddCountComparison(
                    $query,
                    [BookShipmentItem::CENTRE_STATUS_RECEIVED],
                    '=',
                    [BookShipmentItem::CENTRE_STATUS_RECEIVED],
                    true,
                    false
                );
                return;
            case BookShipment::DERIVED_COMPLETED_WITH_ISSUES:
                $query->where('book_shipments.status', BookShipment::STATUS_CENTRE_RECEIVED);
                $this->AddCountCondition($query, [BookShipmentItem::CENTRE_STATUS_PENDING], '=', 0, false);
                $this->AddCountCondition($query, [
                    BookShipmentItem::CENTRE_STATUS_MISSING,
                    BookShipmentItem::CENTRE_STATUS_DAMAGED,
                ], '>', 0, false);
                $this->AddCountComparison(
                    $query,
                    [BookShipmentItem::CENTRE_STATUS_RECEIVED],
                    '=',
                    [BookShipmentItem::CENTRE_STATUS_RECEIVED],
                    true,
                    false
                );
                return;
            default:
                throw new BookShipmentException(BookShipmentException::DESTINATION_INELIGIBLE);
        }
    }

    private function AddCountCondition(
        EloquentBuilder $query,
        ?array $statuses,
        string $operator,
        int $value,
        bool $student_received
    ): void {
        $count_query = $this->BuildItemCountQuery($statuses, $student_received);
        $query->whereRaw(
            '(' . $count_query->toSql() . ') ' . $operator . ' ?',
            array_merge($count_query->getBindings(), [$value])
        );
    }

    private function AddCountComparison(
        EloquentBuilder $query,
        ?array $left_statuses,
        string $operator,
        ?array $right_statuses,
        bool $left_student_received,
        bool $right_student_received
    ): void {
        $left_query = $this->BuildItemCountQuery($left_statuses, $left_student_received);
        $right_query = $this->BuildItemCountQuery($right_statuses, $right_student_received);
        $query->whereRaw(
            '(' . $left_query->toSql() . ') ' . $operator . ' (' . $right_query->toSql() . ')',
            array_merge($left_query->getBindings(), $right_query->getBindings())
        );
    }

    private function ProgressFromSelectedAttributes(BookShipment $shipment): array
    {
        return [
            'total_count' => (int) $shipment->getAttribute('progress_total_count'),
            'centre_received_count' => (int) $shipment->getAttribute('progress_centre_received_count'),
            'missing_count' => (int) $shipment->getAttribute('progress_missing_count'),
            'damaged_count' => (int) $shipment->getAttribute('progress_damaged_count'),
            'pending_count' => (int) $shipment->getAttribute('progress_pending_count'),
            'student_received_count' => (int) $shipment->getAttribute('progress_student_received_count'),
        ];
    }

    private function GetDerivedStatusFromProgress(BookShipment $shipment, array $progress): string
    {
        return match ($shipment->status) {
            BookShipment::STATUS_DRAFT => BookShipment::DERIVED_DRAFT,
            BookShipment::STATUS_DISPATCHED => BookShipment::DERIVED_WAITING_FOR_CENTRE,
            BookShipment::STATUS_CANCELLED => BookShipment::DERIVED_CANCELLED,
            BookShipment::STATUS_CENTRE_RECEIVED => $this->GetCentreReceivedDerivedStatus($progress),
            default => BookShipment::DERIVED_WAITING_FOR_CENTRE,
        };
    }

    private function GetCentreReceivedDerivedStatus(array $progress): string
    {
        if ($progress['pending_count'] > 0) {
            return BookShipment::DERIVED_WAITING_FOR_CENTRE;
        }

        $issue_count = $progress['missing_count'] + $progress['damaged_count'];
        if ($issue_count > 0 && $progress['student_received_count'] === $progress['centre_received_count']) {
            return BookShipment::DERIVED_COMPLETED_WITH_ISSUES;
        }

        if (
            $progress['total_count'] > 0
            && $progress['centre_received_count'] === $progress['total_count']
            && $progress['student_received_count'] === $progress['total_count']
        ) {
            return BookShipment::DERIVED_COMPLETED;
        }

        if ($progress['centre_received_count'] > 0) {
            return BookShipment::DERIVED_PENDING_STUDENT_DELIVERY;
        }

        return BookShipment::DERIVED_WAITING_FOR_CENTRE;
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

    private function AssertCentrePermission(User $actor, string $permission): void
    {
        if (
            $actor->isHq()
            || (int) $actor->disable !== 0
            || !$actor->hasPermissionTo($permission)
            || !$this->IsActiveCentre((int) $actor->department_id)
        ) {
            throw new AuthorizationException();
        }
    }

    private function AssertEligibleDestination(int $destination_centre_id): void
    {
        $is_eligible = Department::query()
            ->whereKey($destination_centre_id)
            ->where('status', 1)
            ->whereKeyNot(Department::HQ_ID)
            ->exists();

        if (!$is_eligible) {
            throw new BookShipmentException(BookShipmentException::DESTINATION_INELIGIBLE);
        }
    }

    private function IsActiveCentre(int $department_id): bool
    {
        return Department::query()
            ->whereKey($department_id)
            ->where('status', 1)
            ->whereKeyNot(Department::HQ_ID)
            ->exists();
    }

    private function NormalizePerPage(int $per_page): int
    {
        return max(1, min(100, $per_page));
    }
}
