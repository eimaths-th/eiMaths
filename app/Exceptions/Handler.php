<?php

namespace App\Exceptions;

use App\Domain\Affiliate\Exceptions\AffiliateDomainException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->renderable(function (AffiliateDomainException $exception, Request $request) {
            $payload = [
                'error_code' => $exception->ErrorCode(),
                'message' => __($exception->SafeTranslationKey()),
            ];

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json($payload, $exception->StatusCode());
            }

            return back()->withErrors(['affiliate' => $payload['message']]);
        });

        $this->reportable(function (Throwable $e) {
            //
        });
    }
}
