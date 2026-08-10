<?php

namespace App\Http\Controllers;

use App\Exceptions\BookShipmentException;
use App\Http\Requests\BookShipments\CandidateBookShipmentRequest;
use App\Http\Requests\BookShipments\CancelBookShipmentRequest;
use App\Http\Requests\BookShipments\StoreBookShipmentRequest;
use App\Http\Requests\BookShipments\UpdateBookShipmentRequest;
use App\Models\BookShipment;
use App\Services\BookShipments\BookShipmentQuery;
use App\Services\BookShipments\BookShipmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class BookShipmentController extends Controller
{
    public function __construct(
        private readonly BookShipmentQuery $book_shipment_query,
        private readonly BookShipmentService $book_shipment_service
    ) {
        $this->middleware('permission:book-shipment-list')->only(['Index', 'Show', 'ReceiptAlertSummary']);
        $this->middleware('permission:book-shipment-create')->only(['Candidates', 'Store', 'Update']);
        $this->middleware('permission:book-shipment-dispatch')->only(['Dispatch']);
        $this->middleware('permission:book-shipment-cancel')->only(['Cancel', 'Discard']);
    }

    public function Index(Request $request)
    {
        $shipments = $this->book_shipment_query->GetHqShipments(
            auth()->user(),
            $this->ShipmentFilters($request),
            (int) $request->input('per_page', 20)
        );
        $destinations = $this->book_shipment_query->GetEligibleDestinations(auth()->user());

        if ($request->expectsJson()) {
            return $this->PaginatorResponse($shipments);
        }

        return view('book-shipments.hq.index', [
            'shipments' => $shipments,
            'destinations' => $destinations,
        ]);
    }

    public function ReceiptAlertSummary(): JsonResponse
    {
        return response()->json([
            'data' => $this->book_shipment_query->GetHqReceiptAlertSummary(auth()->user()),
        ]);
    }

    public function Candidates(CandidateBookShipmentRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $candidates = $this->book_shipment_query->GetCandidates(
                auth()->user(),
                (int) $validated['destination_centre_id'],
                $validated['search'] ?? null,
                (int) ($validated['per_page'] ?? 20),
                isset($validated['draft_shipment_id'])
                    ? (int) $validated['draft_shipment_id']
                    : null
            );
        } catch (BookShipmentException $exception) {
            return $this->ShipmentErrorResponse($exception);
        }

        return $this->PaginatorResponse($candidates);
    }

    public function Store(StoreBookShipmentRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $shipment = $this->book_shipment_service->CreateDraft(
                auth()->user(),
                (int) $validated['destination_centre_id'],
                $validated['source_book_ids']
            );

            return $this->MutationResponse(
                $shipment,
                trans('book-shipments.messages.draft_created', [], 'th'),
                201
            );
        } catch (BookShipmentException $exception) {
            return $this->ShipmentErrorResponse($exception);
        }
    }

    public function Show(BookShipment $book_shipment)
    {
        $shipment = $this->book_shipment_query->GetShipmentForActor(
            (int) $book_shipment->id,
            auth()->user(),
            'book-shipment-list'
        );

        if (request()->expectsJson()) {
            return response()->json([
                'data' => [
                    'shipment' => $this->ShipmentData($shipment),
                    'progress' => $shipment->getAttribute('progress'),
                ],
            ]);
        }

        return view('book-shipments.hq.show', ['shipment' => $shipment]);
    }

    public function Update(
        UpdateBookShipmentRequest $request,
        BookShipment $book_shipment
    ): JsonResponse {
        try {
            $shipment = $this->book_shipment_service->UpdateDraft(
                $book_shipment,
                auth()->user(),
                $request->validated()['source_book_ids']
            );

            return $this->MutationResponse($shipment, trans('book-shipments.messages.draft_updated', [], 'th'));
        } catch (BookShipmentException $exception) {
            return $this->ShipmentErrorResponse($exception);
        }
    }

    public function Dispatch(BookShipment $book_shipment): JsonResponse
    {
        try {
            $result = $this->book_shipment_service->Dispatch($book_shipment, auth()->user());

            return $this->MutationResponse(
                $result['shipment'],
                trans('book-shipments.messages.dispatched', [], 'th'),
                200,
                $result['warnings'] ?? []
            );
        } catch (BookShipmentException $exception) {
            return $this->ShipmentErrorResponse($exception);
        }
    }

    public function Cancel(
        CancelBookShipmentRequest $request,
        BookShipment $book_shipment
    ): JsonResponse {
        try {
            $shipment = $this->book_shipment_service->Cancel(
                $book_shipment,
                auth()->user(),
                $request->validated()['reason']
            );

            return $this->MutationResponse($shipment, trans('book-shipments.messages.cancelled', [], 'th'));
        } catch (BookShipmentException $exception) {
            return $this->ShipmentErrorResponse($exception);
        }
    }

    public function Discard(
        CancelBookShipmentRequest $request,
        BookShipment $book_shipment
    ): JsonResponse {
        try {
            $shipment = $this->book_shipment_service->DiscardDraft(
                $book_shipment,
                auth()->user(),
                $request->validated()['reason']
            );

            return $this->MutationResponse($shipment, trans('book-shipments.messages.draft_discarded', [], 'th'));
        } catch (BookShipmentException $exception) {
            return $this->ShipmentErrorResponse($exception);
        }
    }

    private function ShipmentFilters(Request $request): array
    {
        return $request->only([
            'shipment_number',
            'search',
            'destination_centre_id',
            'status',
            'stored_status',
            'derived_status',
            'created_from',
            'created_to',
            'created_date_from',
            'created_date_to',
        ]);
    }

    private function PaginatorResponse(LengthAwarePaginator $paginator): JsonResponse
    {
        return response()->json([
            'data' => collect($paginator->items())
                ->map(fn (BookShipment|array $shipment): array => is_array($shipment)
                    ? $shipment
                    : $this->ShipmentSummaryData($shipment))
                ->values(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    private function MutationResponse(
        BookShipment $shipment,
        string $message,
        int $status = 200,
        array $warnings = []
    ): JsonResponse {
        $progress = $this->book_shipment_query->GetProgress($shipment);
        $shipment->setAttribute('progress', $progress);
        $shipment->setAttribute('derived_status', $this->book_shipment_query->GetDerivedStatus($shipment));

        $payload = [
            'data' => [
                'shipment' => $this->ShipmentData($shipment),
                'progress' => $progress,
            ],
            'message' => $message,
        ];

        if ($warnings !== []) {
            $payload['warnings'] = array_values($warnings);
        }

        return response()->json($payload, $status);
    }

    private function ShipmentSummaryData(BookShipment $shipment): array
    {
        return [
            'id' => (int) $shipment->id,
            'shipment_number' => $shipment->shipment_number,
            'destination_centre_id' => $shipment->destination_centre_id === null
                ? null
                : (int) $shipment->destination_centre_id,
            'destination_centre_code' => $shipment->destination_centre_code,
            'destination_centre_name' => $shipment->destination_centre_name,
            'status' => $shipment->status,
            'derived_status' => $shipment->getAttribute('derived_status'),
            'created_at' => $shipment->created_at?->toIso8601String(),
            'dispatched_at' => $shipment->dispatched_at?->toIso8601String(),
            'centre_received_at' => $shipment->centre_received_at?->toIso8601String(),
            'progress' => $shipment->getAttribute('progress'),
        ];
    }

    private function ShipmentData(BookShipment $shipment): array
    {
        return array_merge($this->ShipmentSummaryData($shipment), [
            'created_by_name' => $shipment->created_by_name,
            'dispatched_by_name' => $shipment->dispatched_by_name,
            'centre_received_by_name' => $shipment->centre_received_by_name,
            'cancelled_by_name' => $shipment->cancelled_by_name,
            'cancelled_at' => $shipment->cancelled_at?->toIso8601String(),
            'cancellation_reason' => $shipment->cancellation_reason,
            'items' => $shipment->Items->map(fn ($item): array => [
                'id' => (int) $item->id,
                'source_book_id' => $item->source_order_detail_list_id === null
                    ? null
                    : (int) $item->source_order_detail_list_id,
                'student_id' => $item->source_student_id === null ? null : (int) $item->source_student_id,
                'student_code' => $item->source_student_code,
                'student_name' => $item->source_student_name,
                'order_id' => $item->source_order_id === null ? null : (int) $item->source_order_id,
                'order_number' => $item->source_order_number,
                'book_name' => $item->book_name,
                'level' => $item->level,
                'term' => $item->term,
                'replacement_for_shipment_item_id' => $item->replaces_shipment_item_id === null
                    ? null
                    : (int) $item->replaces_shipment_item_id,
                'centre_status' => $item->centre_status,
                'centre_note' => $item->centre_note,
            ])->values()->all(),
            'logs' => $shipment->Logs->map(fn ($log): array => [
                'id' => (int) $log->id,
                'action' => $log->action,
                'actor_name' => $log->actor_name,
                'note' => $log->note,
                'created_at' => $log->created_at?->toIso8601String(),
            ])->values()->all(),
        ]);
    }

    private function ShipmentErrorResponse(BookShipmentException $exception): JsonResponse
    {
        $message = match ($exception->Reason()) {
            BookShipmentException::DESTINATION_INELIGIBLE => trans('book-shipments.messages.destination_ineligible', [], 'th'),
            BookShipmentException::SELECTION_INVALID => trans('book-shipments.messages.selection_invalid', [], 'th'),
            BookShipmentException::SOURCE_INELIGIBLE => trans('book-shipments.messages.source_ineligible', [], 'th'),
            BookShipmentException::SOURCE_CONFLICT => trans('book-shipments.messages.source_conflict', [], 'th'),
            BookShipmentException::STATE_CONFLICT => trans('book-shipments.messages.state_conflict', [], 'th'),
            BookShipmentException::CANCELLATION_REASON_INVALID => trans('book-shipments.messages.cancellation_reason_invalid', [], 'th'),
            BookShipmentException::ITEM_SET_INVALID => trans('book-shipments.messages.item_set_invalid', [], 'th'),
            BookShipmentException::ISSUE_NOTE_INVALID => trans('book-shipments.messages.issue_note_invalid', [], 'th'),
            default => trans('book-shipments.messages.could_not_change', [], 'th'),
        };

        return response()->json([
            'error' => [
                'code' => $exception->ErrorCode(),
                'message' => $message,
            ],
        ], $exception->StatusCode());
    }
}
