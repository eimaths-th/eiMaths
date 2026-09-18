<?php

namespace App\Http\Middleware;

use App\Domain\CreditNote\Exceptions\CreditNoteDomainException;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class HandleCreditNoteResponses
{
    public function handle(Request $request, Closure $next): Response
    {
        $correlation_id = (string) $request->attributes->get('credit_note_correlation_id', '');
        try {
            return $next($request);
        } catch (CreditNoteDomainException $exception) {
            return $this->Error($request, $exception->ErrorCode(), __($exception->SafeTranslationKey()), $exception->StatusCode(), $exception);
        } catch (ValidationException $exception) {
            return $this->Error($request, 'CREDIT_NOTE_VALIDATION_FAILED', __('credit-note.validation.invalid_request'), 422, $exception, $exception->errors());
        } catch (AuthorizationException $exception) {
            return $this->Error($request, 'CREDIT_NOTE_FORBIDDEN', __('credit-note.errors.forbidden'), 403, $exception);
        } catch (ModelNotFoundException $exception) {
            return $this->Error($request, 'CREDIT_NOTE_NOT_FOUND', __('credit-note.errors.not_found'), 404, $exception);
        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            Log::error('credit_note.request_failed', [
                'correlation_id' => $correlation_id,
                'actor_id' => $request->user()?->getAuthIdentifier(),
                'resource_id' => $request->route('credit_note')?->id ?? $request->route('credit_note'),
                'exception_class_code' => get_class($exception),
            ]);
            return $this->Error($request, 'CREDIT_NOTE_INTERNAL_ERROR', __('credit-note.errors.internal'), 500, $exception);
        }
    }

    private function Error(Request $request, string $error_code, string $message, int $status, Throwable $exception, array $details = []): Response
    {
        if ($request->expectsJson() || $request->isJson() || $request->wantsJson()) {
            return response()->json([
                'error_code' => $error_code,
                'message' => $message,
                'details' => $details,
                'correlation_id' => $request->attributes->get('credit_note_correlation_id'),
            ], $status);
        }

        return redirect()->back()
            ->withErrors([$message])
            ->with('credit_note_error_code', $error_code);
    }
}
