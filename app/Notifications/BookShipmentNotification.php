<?php

namespace App\Notifications;

use App\Models\BookShipment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BookShipmentNotification extends Notification
{
    use Queueable;

    public const EVENT_DISPATCHED = 'DISPATCHED';
    public const EVENT_CENTRE_RECEIVED = 'CENTRE_RECEIVED';
    public const EVENT_STUDENT_COMPLETED = 'STUDENT_COMPLETED';

    public function __construct(
        private readonly BookShipment $shipment,
        private readonly string $event_type,
        private readonly ?string $completion_status = null
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $event_content = match ($this->event_type) {
            self::EVENT_DISPATCHED => [
                'title' => trans('book-shipments.notifications.dispatched_title', [], 'th'),
                'body' => trans('book-shipments.notifications.dispatched_body', [
                    'shipment_number' => $this->shipment->shipment_number,
                ], 'th'),
            ],
            self::EVENT_CENTRE_RECEIVED => [
                'title' => trans('book-shipments.notifications.centre_received_title', [], 'th'),
                'body' => trans('book-shipments.notifications.centre_received_body', [
                    'shipment_number' => $this->shipment->shipment_number,
                ], 'th'),
            ],
            self::EVENT_STUDENT_COMPLETED => [
                'title' => $this->completion_status === BookShipment::DERIVED_COMPLETED_WITH_ISSUES
                    ? trans('book-shipments.notifications.completed_with_issues_title', [], 'th')
                    : trans('book-shipments.notifications.completed_title', [], 'th'),
                'body' => $this->completion_status === BookShipment::DERIVED_COMPLETED_WITH_ISSUES
                    ? trans('book-shipments.notifications.completed_with_issues_body', [
                        'shipment_number' => $this->shipment->shipment_number,
                    ], 'th')
                    : trans('book-shipments.notifications.completed_body', [
                        'shipment_number' => $this->shipment->shipment_number,
                    ], 'th'),
            ],
        };

        return [
            'title' => $event_content['title'],
            'body' => $event_content['body'],
            'actionText' => trans('book-shipments.notifications.open', [], 'th'),
            'actionURL' => $this->ActionURL(),
            'shipment_id' => $this->shipment->id,
            'shipment_number' => $this->shipment->shipment_number,
            'event_type' => $this->event_type,
        ];
    }

    private function ActionURL(): string
    {
        if ($this->event_type === self::EVENT_DISPATCHED) {
            return url('/incoming-book-shipments/' . $this->shipment->id);
        }

        return url('/book-shipments/' . $this->shipment->id);
    }
}
