<?php

namespace App\Http\Middleware;

use App\Services\Faq\UnavailableFaqUrls;
use Closure;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

final class PreventFaqPageCaching
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->is('api/faq-pages/*')) return $next($request);
        try {
            $response = $next($request);
        } catch (HttpExceptionInterface $exception) {
            $response = app(ExceptionHandler::class)->render($request, $exception);
        } catch (\Throwable $exception) {
            $safe_exception = $exception instanceof UnavailableFaqUrls
                ? $exception : new UnavailableFaqUrls('faq.urls.http');
            $safe_exception->report();
            $response = $safe_exception->render($request);
        }
        if ($response->getStatusCode() === 404) {
            $response = response()->json(['error_code' => 'FAQ_NOT_FOUND', 'message' => 'FAQ not found.'], 404);
        } elseif ($response->getStatusCode() >= 500) {
            $response = response()->json(['error_code' => 'FAQ_CONTENT_UNAVAILABLE',
                'message' => 'FAQ information is temporarily unavailable.'], 503);
        }
        $response->headers->set('Cache-Control', 'no-store');
        return $response;
    }
}
