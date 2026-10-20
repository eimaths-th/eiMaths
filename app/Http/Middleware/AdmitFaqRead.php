<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
final class AdmitFaqRead
{
    public function handle(Request $request, Closure $next)
    {
        $configured_token = (string) config('faq-analytics.integration_token');
        $provided_token = $request->bearerToken();
        $is_authenticated = $configured_token !== '' && is_string($provided_token)
            && hash_equals($configured_token, $provided_token);
        $limit_key = $is_authenticated ? 'faq-read:integration' : 'faq-read:invalid:'.hash('sha256', (string) $request->ip());
        $limit = max(1, (int) config($is_authenticated
            ? 'faq-analytics.integration_requests_per_minute' : 'faq-analytics.invalid_requests_per_minute'));
        try {
            if (RateLimiter::hit($limit_key, 60) > $limit) {
                return response()->json(['error_code' => 'FAQ_READ_RATE_LIMITED', 'message' => 'Please try again later.'],
                    429, ['Retry-After' => (string) max(1, RateLimiter::availableIn($limit_key))]);
            }
        } catch (\Throwable $exception) {
            $request_id = $request->header('X-Request-Id');
            $correlation_id = $is_authenticated && is_string($request_id)
                && preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/iD', $request_id)
                ? strtolower($request_id) : (string) \Illuminate\Support\Str::uuid();
            \Illuminate\Support\Facades\Log::warning('faq.read.admission_failed', [
                'operation' => 'faq.read.admission', 'outcome' => 'failed',
                'error_code' => 'FAQ_READ_UNAVAILABLE', 'correlation_id' => $correlation_id,
            ]);
            return $this->Unavailable();
        }
        if ($configured_token === '') {
            return $this->Unavailable();
        }
        if (!$is_authenticated) {
            return response()->json(['error_code' => 'FAQ_READ_UNAUTHORIZED', 'message' => 'Authentication is required.'], 401);
        }
        return $next($request);
    }
    private function Unavailable()
    {
        return response()->json(['error_code' => 'FAQ_READ_UNAVAILABLE', 'message' => 'Read recording is unavailable.'], 503);
    }
}
