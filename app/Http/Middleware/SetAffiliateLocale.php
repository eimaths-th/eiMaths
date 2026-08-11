<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetAffiliateLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $previous_locale = app()->getLocale();
        app()->setLocale($this->ResolveLocale());

        try {
            return $next($request);
        } finally {
            app()->setLocale($previous_locale);
        }
    }

    private function ResolveLocale(): string
    {
        $supported_locales = array_values(array_filter(array_map(
            static fn (mixed $locale): string => strtolower(trim((string) $locale)),
            (array) config('affiliate.supported_locales', ['th', 'en'])
        )));
        $default_locale = strtolower(trim((string) config('affiliate.default_locale', 'th')));
        if (! in_array($default_locale, $supported_locales, true)) {
            $default_locale = 'th';
        }

        $user_locale = auth()->user()?->language_code;
        $user_locale = is_string($user_locale) ? strtolower(trim($user_locale)) : null;

        return $user_locale !== null && in_array($user_locale, $supported_locales, true)
            ? $user_locale
            : $default_locale;
    }
}
