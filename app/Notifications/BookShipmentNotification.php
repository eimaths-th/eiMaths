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
        $locale = $this->NotificationLocale($notifiable);
        $event_content = match ($this->event_type) {
            self::EVENT_DISPATCHED => [
                'title' => trans('book-shipments.notifications.dispatched_title', [], $locale),
                'body' => trans('book-shipments.notifications.dispatched_body', [
                    'shipment_number' => $this->shipment->shipment_number,
                ], $locale),
            ],
            self::EVENT_CENTRE_RECEIVED => [
                'title' => trans('book-shipments.notifications.centre_received_title', [], $locale),
                'body' => trans('book-shipments.notifications.centre_received_body', [
                    'shipment_number' => $this->shipment->shipment_number,
                ], $locale),
            ],
            self::EVENT_STUDENT_COMPLETED => [
                'title' => $this->completion_status === BookShipment::DERIVED_COMPLETED_WITH_ISSUES
                    ? trans('book-shipments.notifications.completed_with_issues_title', [], $locale)
                    : trans('book-shipments.notifications.completed_title', [], $locale),
                'body' => $this->completion_status === BookShipment::DERIVED_COMPLETED_WITH_ISSUES
                    ? trans('book-shipments.notifications.completed_with_issues_body', [
                        'shipment_number' => $this->shipment->shipment_number,
                    ], $locale)
                    : trans('book-shipments.notifications.completed_body', [
                        'shipment_number' => $this->shipment->shipment_number,
                    ], $locale),
            ],
        };

        return [
            'title' => $event_content['title'],
            'body' => $event_content['body'],
            'actionText' => trans('book-shipments.notifications.open', [], $locale),
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

    private function NotificationLocale(object $notifiable): string
    {
        $supported_locales = (array) config('ui-localization.supported_locales', ['en', 'th']);
        $recipient_locale = $notifiable->language_code ?? null;
        $application_locale = app()->getLocale();

        if (is_string($recipient_locale) && in_array($recipient_locale, $supported_locales, true)) {
            return $recipient_locale;
        }

        return in_array($application_locale, $supported_locales, true)
            ? $application_locale
            : 'en';
    }
}
