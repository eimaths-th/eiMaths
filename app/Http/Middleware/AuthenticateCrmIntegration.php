<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthenticateCrmIntegration
{
    public function handle(Request $request, Closure $next)
    {
        $expected = (string) config('services.crm_integrations.token');
        $provided = (string) ($request->bearerToken() ?: $request->header('X-EIMATHS-TOKEN'));
        abort_unless($expected !== '' && $provided !== '' && hash_equals($expected, $provided), 401, 'Invalid integration token.');
        return $next($request);
    }
}
