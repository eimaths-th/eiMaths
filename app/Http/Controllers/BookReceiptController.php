<?php

namespace App\Http\Controllers;

use App\Exceptions\BookReceiptException;
use App\Models\Contact;
use App\Models\OrderDetailList;
use App\Services\BookReceiptService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookReceiptController extends Controller
{
    public function __construct(private readonly BookReceiptService $book_receipt_service)
    {
        $this->middleware('permission:student-list')->only(['Index', 'Logs']);
        $this->middleware('permission:student-edit')->only(['Update', 'ReceiveAll', 'Destroy']);
    }

    public function Index(Contact $contact, Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->book_receipt_service->GetStudentBookReceipts(
                $contact,
                auth()->user(),
                $this->ShipmentId($request)
            ),
        ]);
    }

    public function Update(
        Contact $contact,
        OrderDetailList $order_detail_list,
        Request $request
    ): JsonResponse
    {
        try {
            $payload = $this->book_receipt_service->MarkReceived(
                $contact,
                $order_detail_list,
                auth()->user(),
                $this->ShipmentId($request)
            );

            return $this->MutationResponse($payload, $order_detail_list->id, 'Book marked as received.');
        } catch (BookReceiptException $exception) {
            return $this->BookReceiptErrorResponse($exception);
        }
    }

    public function ReceiveAll(Contact $contact, Request $request): JsonResponse
    {
        try {
            $payload = $this->book_receipt_service->MarkAllReceived(
                $contact,
                auth()->user(),
                $this->ShipmentId($request)
            );

            return response()->json([
                'data' => [
                    'summary' => $payload['summary'],
                ],
                'message' => 'All books marked as received.',
            ]);
        } catch (BookReceiptException $exception) {
            return $this->BookReceiptErrorResponse($exception);
        }
    }

    public function Destroy(
        Contact $contact,
        OrderDetailList $order_detail_list,
        Request $request
    ): JsonResponse
    {
        try {
            $payload = $this->book_receipt_service->CancelReceipt(
                $contact,
                $order_detail_list,
                auth()->user(),
                $this->ShipmentId($request)
            );

            return $this->MutationResponse($payload, $order_detail_list->id, 'Book receipt cancelled.');
        } catch (BookReceiptException $exception) {
            return $this->BookReceiptErrorResponse($exception);
        }
    }

    public function Logs(
        Contact $contact,
        OrderDetailList $order_detail_list,
        Request $request
    ): JsonResponse
    {
        $logs = $this->book_receipt_service->GetBookReceiptLogs(
            $contact,
            $order_detail_list,
            auth()->user(),
            $this->ShipmentId($request)
        );

        return response()->json([
            'data' => $logs->map(static function ($log): array {
                return [
                    'action' => $log->action,
                    'actor_name' => $log->actor_name,
                    'created_at' => $log->created_at?->toIso8601String(),
                ];
            })->values(),
        ]);
    }

    private function MutationResponse(array $payload, int $book_id, string $message): JsonResponse
    {
        return response()->json([
            'data' => [
                'book' => $this->FindBook($payload, $book_id),
                'summary' => $payload['summary'],
            ],
            'message' => $message,
        ]);
    }

    private function FindBook(array $payload, int $book_id): ?array
    {
        foreach ($payload['orders'] as $order) {
            foreach ($order['books'] as $book) {
                if ((int) $book['id'] === $book_id) {
                    return $book;
                }
            }
        }

        return null;
    }

    private function BookReceiptErrorResponse(BookReceiptException $exception): JsonResponse
    {
        $message = match ($exception->Reason()) {
            BookReceiptException::ALREADY_RECEIVED => 'This book is already marked as received.',
            BookReceiptException::NOT_RECEIVED => 'This book is not currently marked as received.',
            BookReceiptException::INELIGIBLE => 'This row is not an eligible book.',
            BookReceiptException::SHIPMENT_BLOCKED => 'This book is not yet available for student receipt.',
            default => 'The book receipt state could not be changed.',
        };

        return response()->json([
            'error' => [
                'code' => $exception->ErrorCode(),
                'message' => $message,
            ],
        ], $exception->StatusCode());
    }

    private function ShipmentId(Request $request): ?int
    {
        $shipment_id = $request->query('shipment_id');
        if ($shipment_id === null || $shipment_id === '') {
            return null;
        }

        if (filter_var($shipment_id, FILTER_VALIDATE_INT) === false || (int) $shipment_id < 1) {
            abort(422, 'The shipment_id must be a positive integer.');
        }

        return (int) $shipment_id;
    }
}
