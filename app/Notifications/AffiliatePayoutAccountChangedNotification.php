<?php

namespace App\Notifications;

use App\Domain\Affiliate\Enums\AffiliatePayoutType;
use DateTimeInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AffiliatePayoutAccountChangedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly AffiliatePayoutType $payout_type,
        private readonly ?DateTimeInterface $cooldown_until,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Affiliate payout account changed',
            'body' => 'Your ' . $this->payout_type->value . ' payout account was changed. Withdrawals are blocked during the cooling period.',
            'event_type' => 'PAYOUT_ACCOUNT_CHANGED',
            'payout_type' => $this->payout_type->value,
            'cooldown_until' => $this->cooldown_until?->format(DateTimeInterface::ATOM),
        ];
    }
}
