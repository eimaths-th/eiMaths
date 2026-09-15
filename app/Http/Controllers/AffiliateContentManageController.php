<?php

namespace App\Http\Controllers;

use App\Domain\Affiliate\Services\AffiliateContentService;
use App\Http\Requests\Affiliate\StoreAffiliateContentRequest;
use App\Http\Requests\Affiliate\ToggleAffiliateContentStatusRequest;
use App\Http\Requests\Affiliate\UpdateAffiliateContentRequest;
use App\Models\AffiliateContent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use RuntimeException;

/**
 * Controller สำหรับ Admin จัดการสื่อการตลาด Affiliate
 * CRUD: แสดงรายการ, อัปโหลด, ลบ, เปลี่ยนสถานะ
 */
class AffiliateContentManageController extends Controller
{
    public function __construct(private readonly AffiliateContentService $affiliate_content_service)
    {
    }

    public function Index(): View
    {
        return view('affiliate_content.index', [
            'contents' => AffiliateContent::withTrashed()
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->paginate(10, ['*'], 'content_page'),
        ]);
    }

    public function GenerateCampaignCode(): JsonResponse
    {
        try {
            $campaign_code = $this->affiliate_content_service->GenerateUniqueCampaignCode();
        } catch (RuntimeException $exception) {
            report($exception);

            return response()->json([
                'message' => __('affiliate.content_admin.campaign_code_generation_failed'),
            ], 503)->header('Cache-Control', 'no-store, private');
        }

        return response()->json(['campaign_code' => $campaign_code])
            ->header('Cache-Control', 'no-store, private');
    }

    public function StoreValidated(StoreAffiliateContentRequest $request): RedirectResponse
    {
        $actor = $request->user();
        abort_unless($actor !== null, 403);
        $this->affiliate_content_service->Create(
            $request->validated(),
            $request->file('file'),
            $actor,
            (string) $request->attributes->get('affiliate_correlation_id', request()->header('X-Correlation-ID', 'content')),
        );

        return back()->with('success', __('affiliate.content_admin.created'));
    }

    public function Destroy(AffiliateContent $affiliate_content): RedirectResponse
    {
        $actor = request()->user();
        abort_unless($actor !== null, 403);
        $this->affiliate_content_service->Archive($affiliate_content, $actor, $this->CorrelationId());
        return back()->with('success', __('affiliate.content_admin.archived'));
    }

    public function UpdateValidated(UpdateAffiliateContentRequest $request, AffiliateContent $affiliate_content): RedirectResponse
    {
        $actor = $request->user();
        abort_unless($actor !== null, 403);
        $validated = $request->validated();
        unset($validated['file']);
        $this->affiliate_content_service->Update(
            $affiliate_content,
            $validated,
            $actor,
            (string) $request->attributes->get('affiliate_correlation_id', request()->header('X-Correlation-ID', 'content')),
            $request->file('file'),
        );

        return back()->with('success', __('affiliate.content_admin.updated'));
    }

    public function ToggleStatusValidated(ToggleAffiliateContentStatusRequest $request): RedirectResponse
    {
        $actor = $request->user();
        abort_unless($actor !== null, 403);
        $content = AffiliateContent::findOrFail((int) $request->validated('id'));
        $this->affiliate_content_service->ToggleStatus($content, $actor, $this->CorrelationId());

        return back()->with('success', __('affiliate.content_admin.updated'));
    }

    private function CorrelationId(): string
    {
        return (string) request()->attributes->get('affiliate_correlation_id', request()->header('X-Correlation-ID', 'content'));
    }
}
