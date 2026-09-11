<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class LogProductStockFailures
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->routeIs('products.add_stock', 'products.rm_stock')) {
            return $next($request);
        }

        try {
            $response = $next($request);
        } catch (Throwable $exception) {
            $this->logFailure($request, $exception);
            throw $exception;
        }

        if (($response->exception ?? null) instanceof Throwable) {
            $this->logFailure($request, $response->exception, $response->getStatusCode());
        }

        return $response;
    }

    private function logFailure(Request $request, Throwable $exception, ?int $responseStatus = null): void
    {
        $status = $responseStatus ?? $this->statusCode($exception);
        $type = $this->scalarInput($request, 'type');
        $context = [
            'operation' => $request->routeIs('products.rm_stock') ? 'remove_stock' : 'add_stock',
            'actor_id' => $request->user()?->getAuthIdentifier(),
            'product_id' => $request->route('id'),
            'source_centre_id' => $this->scalarInput($request, 'centre'),
            'destination_type' => match ((int) $type) {
                1 => 'student',
                2 => 'centre',
                default => null,
            },
            'destination_id' => $this->scalarInput($request, 'student'),
            'amount' => $this->scalarInput($request, 'amount'),
            'http_status' => $status,
            'exception_class' => $exception::class,
            'error' => $exception->getMessage(),
        ];

        if ($exception instanceof ValidationException) {
            $context['validation_errors'] = $exception->errors();
        }

        if ($status >= 500) {
            $context['exception'] = $exception;
            Log::error('product.stock_adjustment_failed', $context);
        } else {
            Log::warning('product.stock_adjustment_failed', $context);
        }
    }

    private function statusCode(Throwable $exception): int
    {
        if ($exception instanceof ValidationException) {
            return 422;
        }

        if ($exception instanceof ModelNotFoundException) {
            return 404;
        }

        if ($exception instanceof HttpExceptionInterface) {
            return $exception->getStatusCode();
        }

        return 500;
    }

    private function scalarInput(Request $request, string $key): bool|float|int|string|null
    {
        $value = $request->input($key);

        return is_scalar($value) ? $value : null;
    }
}
