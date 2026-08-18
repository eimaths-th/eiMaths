<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class AffiliateApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $referral_code,
    ) {
        $this->afterCommit();
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $locale = $this->PreferredLocale($notifiable);

        return (new MailMessage)
            ->subject(trans('affiliate.notifications.approved_subject', [], $locale))
            ->greeting(trans('affiliate.notifications.approved_greeting', [
                'name' => (string) ($notifiable->name ?? ''),
            ], $locale))
            ->line(trans('affiliate.notifications.approved_body', [
                'referral_code' => $this->referral_code,
            ], $locale))
            ->action(
                trans('affiliate.notifications.approved_action', [], $locale),
                route('affiliate.login'),
            )
            ->line(trans('affiliate.notifications.approved_footer', [], $locale));
    }

    private function PreferredLocale(object $notifiable): string
    {
        $language_code = strtolower(trim((string) ($notifiable->language_code ?? '')));
        $supported_locales = config('affiliate.supported_locales', ['th', 'en']);

        if (is_array($supported_locales) && in_array($language_code, $supported_locales, true)) {
            return $language_code;
        }

        return (string) config('affiliate.default_locale', 'th');
    }
}
