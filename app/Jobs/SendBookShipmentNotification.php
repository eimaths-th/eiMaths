<?php

namespace App\Jobs;

use App\Notifications\BookShipmentNotification;
use App\Services\BookShipments\BookShipmentNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendBookShipmentNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly int $shipment_id,
        public readonly string $event_type
    ) {
    }

    public function handle(BookShipmentNotificationService $notification_service): void
    {
        $notification_service->ProcessById($this->shipment_id, $this->event_type);
    }
}
