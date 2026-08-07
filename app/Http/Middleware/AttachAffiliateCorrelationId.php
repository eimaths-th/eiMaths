<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AttachAffiliateCorrelationId
{
    public function handle(Request $request, Closure $next): Response
    {
        $incoming = (string) $request->header('X-Correlation-ID', '');
        $correlation_id = preg_match('/\A[A-Za-z0-9._:-]{1,96}\z/', $incoming)
            ? $incoming
            : (string) Str::uuid();

        $request->attributes->set('affiliate_correlation_id', $correlation_id);
        $response = $next($request);
        $response->headers->set('X-Correlation-ID', $correlation_id);

        return $response;
    }
}
