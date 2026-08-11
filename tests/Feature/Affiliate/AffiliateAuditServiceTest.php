<?php

namespace Tests\Feature\Affiliate;

use App\Domain\Affiliate\Enums\AffiliateReconciliationStatus;
use App\Domain\Affiliate\Models\AffiliateAuditLog;
use App\Domain\Affiliate\Services\AffiliateAuditService;
use App\Http\Middleware\AttachAffiliateCorrelationId;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class AffiliateAuditServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_record_sanitizes_sensitive_evidence_and_open_issue_is_idempotent(): void
    {
        $service = app(AffiliateAuditService::class);
        $audit = $service->Record(
            operation: 'affiliate.withdrawal.request',
            subject_type: 'affiliate_withdrawal_request',
            subject_id: 1,
            actor: null,
            prior_state: ['password' => 'secret', 'status' => 'REQUESTED'],
            resulting_state: ['bank_account_number' => '1234567890', 'status' => 'REQUESTED'],
            amount_minor: 1000,
            outcome: 'SUCCESS',
            error_code: null,
            correlation_id: 'corr-audit-001',
        );

        $this->assertArrayNotHasKey('password', $audit->prior_state);
        $this->assertArrayNotHasKey('bank_account_number', $audit->resulting_state);

        $first = $service->OpenIssue('affiliate_commissions', 1, 'MISSING_LEDGER', 'HIGH', ['token' => 'hidden']);
        $second = $service->OpenIssue('affiliate_commissions', 1, 'MISSING_LEDGER', 'HIGH', ['token' => 'hidden']);
        $this->assertSame($first->id, $second->id);
        $this->assertSame(AffiliateReconciliationStatus::OPEN, $first->status);
    }

    public function test_emit_signal_uses_structured_sanitized_context(): void
    {
        Log::spy();
        app(AffiliateAuditService::class)->EmitSignal('affiliate.security.denial', 'corr-audit-002', [
            'user_id' => 7,
            'token' => 'secret',
        ]);

        Log::shouldHaveReceived('warning')->once();
    }

    public function test_correlation_middleware_preserves_bounded_id_or_generates_one(): void
    {
        $middleware = app(AttachAffiliateCorrelationId::class);
        $request = Request::create('/affiliate', 'GET', [], [], [], ['HTTP_X_CORRELATION_ID' => 'corr-123']);
        $response = $middleware->handle($request, fn (Request $next_request) => response()->json([
            'correlation_id' => $next_request->attributes->get('affiliate_correlation_id'),
        ]));

        $this->assertSame('corr-123', $response->headers->get('X-Correlation-ID'));
        $this->assertSame('corr-123', $response->getData(true)['correlation_id']);
    }
}
