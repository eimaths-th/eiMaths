<?php

namespace App\Http\Controllers;

use App\Exceptions\BookShipmentException;
use App\Http\Requests\BookShipments\ReceiveBookShipmentRequest;
use App\Models\BookShipment;
use App\Services\BookShipments\BookShipmentQuery;
use App\Services\BookShipments\BookShipmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class IncomingBookShipmentController extends Controller
{
    public function __construct(
        private readonly BookShipmentQuery $incoming_book_shipment_query,
        private readonly BookShipmentService $incoming_book_shipment_service
    ) {
        $this->middleware('permission:book-shipment-receive');
    }

    public function Index(Request $request)
    {
        $shipments = $this->incoming_book_shipment_query->GetCentreShipments(
            auth()->user(),
            $request->only([
                'shipment_number',
                'search',
                'status',
                'derived_status',
                'created_from',
                'created_to',
                'created_date_from',
                'created_date_to',
            ]),
            (int) $request->input('per_page', 20)
        );

        if ($request->expectsJson()) {
            return $this->PaginatorResponse($shipments);
        }

        return view('book-shipments.centre.index', ['shipments' => $shipments]);
    }

    public function PendingSummary(): JsonResponse
    {
        return response()->json([
            'data' => $this->incoming_book_shipment_query->GetCentrePendingSummary(auth()->user()),
        ]);
    }

    public function Show(BookShipment $book_shipment)
    {
        $shipment = $this->incoming_book_shipment_query->GetShipmentForActor(
            (int) $book_shipment->id,
            auth()->user(),
            'book-shipment-receive'
        );

        if (request()->expectsJson()) {
            return response()->json([
                'data' => [
                    'shipment' => $this->ShipmentData($shipment),
                    'progress' => $shipment->getAttribute('progress'),
                ],
            ]);
        }

        return view('book-shipments.centre.show', ['shipment' => $shipment]);
    }

    public function Receive(
        ReceiveBookShipmentRequest $request,
        BookShipment $book_shipment
    ): JsonResponse {
        try {
            $shipment = $this->incoming_book_shipment_service->ReceiveAtCentre(
                $book_shipment,
                auth()->user(),
                $request->validated()['items']
            );

            return $this->MutationResponse($shipment, trans('book-shipments.messages.received', []));
        } catch (BookShipmentException $exception) {
            return $this->ShipmentErrorResponse($exception);
        }
    }

    private function PaginatorResponse(LengthAwarePaginator $paginator): JsonResponse
    {
        return response()->json([
            'data' => collect($paginator->items())->map(fn (BookShipment $shipment): array => [
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
            ])->values(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    private function MutationResponse(BookShipment $shipment, string $message): JsonResponse
    {
        $progress = $this->incoming_book_shipment_query->GetProgress($shipment);
        $shipment->setAttribute('progress', $progress);
        $shipment->setAttribute(
            'derived_status',
            $this->incoming_book_shipment_query->GetDerivedStatus($shipment)
        );

        return response()->json([
            'data' => [
                'shipment' => $this->ShipmentData($shipment),
                'progress' => $progress,
            ],
            'message' => $message,
        ]);
    }

    private function ShipmentData(BookShipment $shipment): array
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
            'created_by_name' => $shipment->created_by_name,
            'dispatched_by_name' => $shipment->dispatched_by_name,
            'centre_received_by_name' => $shipment->centre_received_by_name,
            'created_at' => $shipment->created_at?->toIso8601String(),
            'dispatched_at' => $shipment->dispatched_at?->toIso8601String(),
            'centre_received_at' => $shipment->centre_received_at?->toIso8601String(),
            'items' => $shipment->Items->map(fn ($item): array => [
                'id' => (int) $item->id,
                'source_book_id' => $item->source_order_detail_list_id === null
                    ? null
                    : (int) $item->source_order_detail_list_id,
                'student_code' => $item->source_student_code,
                'student_name' => $item->source_student_name,
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
            'progress' => $shipment->getAttribute('progress'),
        ];
    }

    private function ShipmentErrorResponse(BookShipmentException $exception): JsonResponse
    {
        $message = match ($exception->Reason()) {
            BookShipmentException::STATE_CONFLICT => trans('book-shipments.messages.state_conflict', []),
            BookShipmentException::ITEM_SET_INVALID => trans('book-shipments.messages.item_set_invalid', []),
            BookShipmentException::ISSUE_NOTE_INVALID => trans('book-shipments.messages.issue_note_invalid', []),
            BookShipmentException::SOURCE_CONFLICT => trans('book-shipments.messages.source_conflict', []),
            default => trans('book-shipments.messages.could_not_receive', []),
        };

        return response()->json([
            'error' => [
                'code' => $exception->ErrorCode(),
                'message' => $message,
            ],
        ], $exception->StatusCode());
    }
}
