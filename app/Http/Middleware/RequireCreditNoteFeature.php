<?php

namespace App\Http\Middleware;

use App\Domain\CreditNote\Exceptions\CreditNoteDomainException;
use App\Domain\CreditNote\Services\CreditNoteFeatureGate;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class RequireCreditNoteFeature
{
    public function __construct(private readonly CreditNoteFeatureGate $feature_gate)
    {
    }

    public function handle(Request $request, Closure $next, string $mode = 'read'): Response
    {
        try {
            if ($mode === 'financial') {
                $this->feature_gate->AssertFinancialActionsEnabled();
            } else {
                $this->feature_gate->AssertEnabled();
            }
            return $next($request);
        } catch (CreditNoteDomainException $exception) {
            return response()->json([
                'error_code' => $exception->ErrorCode(),
                'message' => __($exception->SafeTranslationKey()),
                'correlation_id' => $request->attributes->get('credit_note_correlation_id'),
            ], $exception->StatusCode());
        }
    }
}
