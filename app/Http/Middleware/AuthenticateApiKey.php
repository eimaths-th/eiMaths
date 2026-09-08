<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthenticateApiKey
{
    public function handle(Request $request, Closure $next)
    {
        $configured = (string) config('services.staff_api.key');
        $provided = (string) $request->header('X-API-KEY');
        abort_unless($configured !== '' && $provided !== '' && hash_equals($configured, $provided), 401);
        return $next($request);
    }
}
