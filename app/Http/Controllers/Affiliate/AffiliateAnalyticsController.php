<?php

namespace App\Http\Controllers\Affiliate;

use App\Domain\Affiliate\Exceptions\AffiliateDomainException;
use App\Domain\Affiliate\Queries\AffiliateAnalyticsQuery;
use App\Domain\Affiliate\Services\AffiliateAuditService;
use App\Domain\Affiliate\ValueObjects\AffiliateAnalyticsRange;
use App\Http\Controllers\Controller;
use App\Http\Requests\Affiliate\AffiliateAnalyticsRangeRequest;
use App\Http\Resources\Affiliate\AffiliateAnalyticsResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class AffiliateAnalyticsController extends Controller
{
    public function __construct(
        private readonly AffiliateAnalyticsQuery $analytics_query,
        private readonly AffiliateAuditService $audit_service,
    ) {
    }

    public function Index(Request $request): View|JsonResponse
    {
        try {
            $result = $this->analytics_query->ForUser($request->user(), AffiliateAnalyticsRange::Default());
        } catch (AffiliateDomainException $exception) {
            return response()->json([
                'error_code' => $exception->ErrorCode(),
                'message' => $exception->Reason(),
            ], $exception->StatusCode());
        } catch (Throwable $exception) {
            return $this->RefreshFailure($request, $exception);
        }

        return view('affiliate.analytics.index', [
            'analytics' => (new AffiliateAnalyticsResource($result))->toArray($request),
        ]);
    }

    public function Data(AffiliateAnalyticsRangeRequest $request): JsonResponse
    {
        try {
            $result = $this->analytics_query->ForUser(
                $request->user(),
                $request->Range(),
                $request->SalesPage(),
            );

            return (new AffiliateAnalyticsResource($result))->response();
        } catch (AffiliateDomainException $exception) {
            return response()->json([
                'error_code' => $exception->ErrorCode(),
                'message' => $exception->Reason(),
            ], $exception->StatusCode());
        } catch (Throwable $exception) {
            return $this->RefreshFailure($request, $exception);
        }
    }

    private function RefreshFailure(Request $request, Throwable $exception): JsonResponse
    {
        $correlation_id = (string) ($request->attributes->get('affiliate_correlation_id') ?: 'unknown');
        $this->audit_service->EmitSignal('analytics.refresh_failed', $correlation_id, [
            'operation' => 'analytics.refresh',
            'outcome' => 'FAILED',
            'exception_class_code' => get_class($exception),
            'occurred_at' => now()->toIso8601String(),
        ]);

        return response()->json([
            'error_code' => 'AFFILIATE_ANALYTICS_UNAVAILABLE',
            'message' => 'Affiliate analytics are temporarily unavailable.',
        ], 500);
    }
}
