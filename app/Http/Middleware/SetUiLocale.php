<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetUiLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $previous_locale = app()->getLocale();

        try {
            app()->setLocale($this->ResolveLocale($request));

            return $next($request);
        } finally {
            app()->setLocale($previous_locale);
        }
    }

    private function ResolveLocale(Request $request): string
    {
        $supported_locales = (array) config('ui-localization.supported_locales', ['en', 'th']);
        $default_locale = (string) config('ui-localization.default_locale', 'en');
        if (! in_array($default_locale, $supported_locales, true)) {
            $default_locale = 'en';
        }

        $user_locale = $request->user('web')?->language_code;

        return is_string($user_locale) && in_array($user_locale, $supported_locales, true)
            ? $user_locale
            : $default_locale;
    }
}
