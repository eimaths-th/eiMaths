<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

final class EnsureLocalEbookDemo
{
    public static function allowed(Request $request): bool
    {
        return config('ebook.demo') && app()->environment('local') && PHP_SAPI === 'cli-server'
            && in_array($request->server('REMOTE_ADDR'), ['127.0.0.1', '::1'], true);
    }

    public function handle(Request $request, Closure $next)
    {
        abort_unless(self::allowed($request), 404);
        return $next($request);
    }
}
