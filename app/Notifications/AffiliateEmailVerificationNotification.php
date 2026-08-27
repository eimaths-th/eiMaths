<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

final class AffiliateEmailVerificationNotification extends VerifyEmail
{
    public function toMail($notifiable): MailMessage
    {
        $locale = $this->PreferredLocale($notifiable);

        return parent::toMail($notifiable)
            ->line(trans('affiliate.notifications.verification_pending_approval', [], $locale));
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
