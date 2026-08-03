<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\BookShipmentNotification;
use App\Services\BookReceiptService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;

class BookShipmentNotificationController extends Controller
{
    public function __construct(private readonly BookReceiptService $book_receipt_service)
    {
    }

    public function Read(string $notification_id): RedirectResponse
    {
        $notification = auth()->user()
            ->notifications()
            ->whereKey($notification_id)
            ->first();

        if (!$notification || $notification->type !== BookShipmentNotification::class) {
            abort(404);
        }

        $notification_data = is_array($notification->data) ? $notification->data : [];
        $shipment_id = filter_var($notification_data['shipment_id'] ?? null, FILTER_VALIDATE_INT);
        if ($shipment_id === false || $shipment_id < 1) {
            abort(404);
        }

        try {
            $shipment = $this->book_receipt_service->ResolveShipmentForActor(
                (int) $shipment_id,
                auth()->user()
            );
        } catch (AuthorizationException|ModelNotFoundException) {
            abort(404);
        }

        $event_type = $notification_data['event_type'] ?? null;
        $expected_action_url = $event_type === BookShipmentNotification::EVENT_DISPATCHED
            ? url('/incoming-book-shipments/' . $shipment->id)
            : url('/book-shipments/' . $shipment->id);
        if (
            !in_array($event_type, [
                BookShipmentNotification::EVENT_DISPATCHED,
                BookShipmentNotification::EVENT_CENTRE_RECEIVED,
                BookShipmentNotification::EVENT_STUDENT_COMPLETED,
            ], true)
            ||
            !is_string($notification_data['actionURL'] ?? null)
            || !hash_equals($expected_action_url, $notification_data['actionURL'])
            || (int) ($notification_data['shipment_id'] ?? 0) !== (int) $shipment->id
            || ($notification_data['shipment_number'] ?? null) !== $shipment->shipment_number
        ) {
            abort(404);
        }

        $notification->markAsRead();

        return redirect()->to($expected_action_url);
    }
}
