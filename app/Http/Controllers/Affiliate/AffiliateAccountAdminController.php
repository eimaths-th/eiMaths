<?php

namespace App\Http\Controllers\Affiliate;

use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Domain\Affiliate\Queries\AffiliateAccountAdminQuery;
use App\Domain\Affiliate\Services\AffiliateAccountService;
use App\Domain\Affiliate\Services\AffiliateAuditService;
use App\Domain\Affiliate\Services\AffiliateIdentityService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Affiliate\ChangeAffiliateStatusRequest;
use App\Http\Requests\Affiliate\ReviewAffiliateIdentityRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AffiliateAccountAdminController extends Controller
{
    public function __construct(
        private readonly AffiliateAccountService $account_service,
        private readonly AffiliateAccountAdminQuery $account_query,
        private readonly AffiliateIdentityService $identity_service,
        private readonly AffiliateAuditService $audit_service,
    ) {}

    public function index()
    {
        return view('affiliate.admin.index', ['affiliate_profiles' => $this->account_query->Paginate()]);
    }

    public function PendingApprovalSummary(): JsonResponse
    {
        return response()->json([
            'data' => $this->account_query->PendingApprovalSummary(auth()->user()),
        ]);
    }

    public function approve(AffiliateProfile $affiliate_profile, ChangeAffiliateStatusRequest $request)
    {
        $this->account_service->Approve($affiliate_profile, $request->user(), $request->validated('reason'));

        return back()->with('success', 'Affiliate approved.');
    }

    public function ShowIdentity(AffiliateProfile $affiliate_profile, Request $request)
    {
        $affiliate_profile->load(['User', 'IdentityProfile.ReviewedBy']);
        if ($affiliate_profile->IdentityProfile !== null) {
            $this->audit_service->Record(
                'affiliate.identity-viewed',
                'affiliate_identity_profile',
                $affiliate_profile->IdentityProfile->id,
                $request->user(),
                [],
                ['status' => $affiliate_profile->IdentityProfile->status->value],
                null,
                'SUCCESS',
                null,
                (string) ($request->attributes->get('affiliate_correlation_id') ?: Str::uuid()),
            );
        }

        return view('affiliate.admin.identity', [
            'profile' => $affiliate_profile,
            'identity' => $affiliate_profile->IdentityProfile,
        ]);
    }

    public function IdentityDocument(AffiliateProfile $affiliate_profile, Request $request): BinaryFileResponse
    {
        $identity = $affiliate_profile->IdentityProfile;
        if ($identity === null || blank($identity->id_document_path) || !Storage::disk('local')->exists($identity->id_document_path)) {
            abort(404);
        }

        $this->audit_service->Record(
            'affiliate.identity-document-viewed',
            'affiliate_identity_profile',
            $identity->id,
            $request->user(),
            [],
            ['status' => $identity->status->value],
            null,
            'SUCCESS',
            null,
            (string) ($request->attributes->get('affiliate_correlation_id') ?: Str::uuid()),
        );

        $response = response()->file(Storage::disk('local')->path($identity->id_document_path), [
            'Content-Type' => $identity->id_document_mime ?: 'application/octet-stream',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ]);
        $response->setPrivate();
        $response->setMaxAge(0);
        $response->headers->addCacheControlDirective('no-store');

        return $response;
    }

    public function RejectIdentity(AffiliateProfile $affiliate_profile, ReviewAffiliateIdentityRequest $request)
    {
        $this->identity_service->Reject(
            $affiliate_profile,
            $request->user(),
            $request->validated('rejection_reason'),
            (string) ($request->attributes->get('affiliate_correlation_id') ?: Str::uuid()),
        );

        return redirect()->route('affiliate.admin.index')->with('success', 'Identity verification returned for correction.');
    }

    public function suspend(AffiliateProfile $affiliate_profile, ChangeAffiliateStatusRequest $request)
    {
        $this->account_service->Suspend($affiliate_profile, $request->user(), (string) $request->validated('reason'));

        return back()->with('success', 'Affiliate suspended.');
    }

    public function reactivate(AffiliateProfile $affiliate_profile, ChangeAffiliateStatusRequest $request)
    {
        $this->account_service->Reactivate($affiliate_profile, $request->user(), $request->validated('reason'));

        return back()->with('success', 'Affiliate reactivated.');
    }

    public function deactivate(AffiliateProfile $affiliate_profile, ChangeAffiliateStatusRequest $request)
    {
        $this->account_service->Deactivate($affiliate_profile, $request->user(), (string) $request->validated('reason'));

        return back()->with('success', 'Affiliate deactivated.');
    }
}
