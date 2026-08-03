<?php

namespace App\Services\BookShipments;

use App\Models\BookShipment;
use App\Models\Department;
use App\Models\User;
use App\Notifications\BookShipmentNotification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use InvalidArgumentException;

class BookShipmentNotificationService
{
    public function ProcessById(int $shipment_id, string $event_type): void
    {
        $shipment = BookShipment::query()->whereKey($shipment_id)->first();
        if (!$shipment) {
            Log::warning('Book shipment notification skipped because the shipment was not found.', [
                'shipment_id' => $shipment_id,
                'shipment_number' => null,
                'event_type' => $event_type,
                'recipient_count' => 0,
                'outcome' => 'shipment_not_found',
            ]);

            return;
        }

        $this->Process($shipment, $event_type);
    }

    public function Process(BookShipment $shipment, string $event_type): void
    {
        $this->AssertEventType($event_type);

        DB::transaction(function () use ($shipment, $event_type): void {
            $locked_shipment = BookShipment::query()
                ->whereKey($shipment->id)
                ->lockForUpdate()
                ->first();

            if (!$locked_shipment) {
                Log::warning('Book shipment notification skipped because the shipment was deleted.', [
                    'shipment_id' => $shipment->id,
                    'shipment_number' => $shipment->shipment_number,
                    'event_type' => $event_type,
                    'recipient_count' => 0,
                    'outcome' => 'shipment_not_found',
                ]);

                return;
            }

            $marker_field = $this->GetMarkerField($event_type);
            if ($locked_shipment->getAttribute($marker_field) !== null) {
                Log::info('Book shipment notification was already processed.', [
                    'shipment_id' => $locked_shipment->id,
                    'shipment_number' => $locked_shipment->shipment_number,
                    'event_type' => $event_type,
                    'recipient_count' => 0,
                    'outcome' => 'already_processed',
                ]);

                return;
            }

            $validation = $this->GetValidatedCompletionStatus($locked_shipment, $event_type);
            if (!$validation['ready']) {
                return;
            }

            $completion_status = $validation['completion_status'];
            $recipients = $this->GetRecipients($locked_shipment, $event_type);
            if ($recipients->isNotEmpty()) {
                Notification::sendNow(
                    $recipients,
                    new BookShipmentNotification($locked_shipment, $event_type, $completion_status)
                );
            }

            $locked_shipment->forceFill([$marker_field => now()])->save();

            $log_context = [
                'shipment_id' => $locked_shipment->id,
                'shipment_number' => $locked_shipment->shipment_number,
                'event_type' => $event_type,
                'recipient_count' => $recipients->count(),
                'outcome' => $recipients->isEmpty() ? 'zero_recipients' : 'sent',
            ];

            if ($recipients->isEmpty()) {
                Log::warning('Book shipment notification processed without recipients.', $log_context);
            } else {
                Log::info('Book shipment notification processed.', $log_context);
            }
        });
    }

    private function GetRecipients(BookShipment $shipment, string $event_type): Collection
    {
        if ($event_type === BookShipmentNotification::EVENT_DISPATCHED) {
            $destination_is_active = Department::query()
                ->whereKey($shipment->destination_centre_id)
                ->where('status', 1)
                ->whereKeyNot(Department::HQ_ID)
                ->exists();

            if (!$destination_is_active) {
                return new Collection();
            }

            return User::query()
                ->where('department_id', $shipment->destination_centre_id)
                ->where('disable', 0)
                ->get()
                ->filter(function (User $user): bool {
                    return $user->hasPermissionTo('book-shipment-receive');
                })
                ->values();
        }

        if ($event_type === BookShipmentNotification::EVENT_CENTRE_RECEIVED) {
            $hq_is_active = Department::query()
                ->whereKey(Department::HQ_ID)
                ->where('status', 1)
                ->exists();

            if (!$hq_is_active) {
                return new Collection();
            }

            return User::query()
                ->where('department_id', Department::HQ_ID)
                ->where('disable', 0)
                ->get()
                ->filter(function (User $user): bool {
                    return $user->hasPermissionTo('book-shipment-list');
                })
                ->values();
        }

        $actor_ids = collect([$shipment->created_by, $shipment->dispatched_by])
            ->filter()
            ->map(static fn ($actor_id): int => (int) $actor_id)
            ->unique()
            ->values();

        if ($actor_ids->isEmpty()) {
            return new Collection();
        }

        return User::query()
            ->whereIn('id', $actor_ids)
            ->where('disable', 0)
            ->get()
            ->unique('id')
            ->values();
    }

    private function GetValidatedCompletionStatus(
        BookShipment $shipment,
        string $event_type
    ): array {
        if ($event_type === BookShipmentNotification::EVENT_DISPATCHED) {
            if ($shipment->status !== BookShipment::STATUS_DISPATCHED) {
                return $this->NotReady($shipment, $event_type);
            }

            return ['ready' => true, 'completion_status' => null];
        }

        if ($event_type === BookShipmentNotification::EVENT_CENTRE_RECEIVED) {
            if ($shipment->status !== BookShipment::STATUS_CENTRE_RECEIVED) {
                return $this->NotReady($shipment, $event_type);
            }

            return ['ready' => true, 'completion_status' => null];
        }

        if ($shipment->status !== BookShipment::STATUS_CENTRE_RECEIVED) {
            return $this->NotReady($shipment, $event_type);
        }

        $completion_status = app(BookShipmentQuery::class)->GetDerivedStatus($shipment);
        if (!in_array($completion_status, [
            BookShipment::DERIVED_COMPLETED,
            BookShipment::DERIVED_COMPLETED_WITH_ISSUES,
        ], true)) {
            return $this->NotReady($shipment, $event_type);
        }

        return ['ready' => true, 'completion_status' => $completion_status];
    }

    private function NotReady(BookShipment $shipment, string $event_type): array
    {
        Log::warning('Book shipment notification state was not ready.', [
            'shipment_id' => $shipment->id,
            'shipment_number' => $shipment->shipment_number,
            'event_type' => $event_type,
            'recipient_count' => 0,
            'outcome' => 'state_not_ready',
        ]);

        return ['ready' => false, 'completion_status' => null];
    }

    private function GetMarkerField(string $event_type): string
    {
        return match ($event_type) {
            BookShipmentNotification::EVENT_DISPATCHED => 'dispatch_notified_at',
            BookShipmentNotification::EVENT_CENTRE_RECEIVED => 'centre_received_notified_at',
            BookShipmentNotification::EVENT_STUDENT_COMPLETED => 'student_completion_notified_at',
            default => throw new InvalidArgumentException('Unsupported shipment notification event.'),
        };
    }

    private function AssertEventType(string $event_type): void
    {
        $this->GetMarkerField($event_type);
    }
}
