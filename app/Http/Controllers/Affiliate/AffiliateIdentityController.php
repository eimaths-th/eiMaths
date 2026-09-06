<?php

namespace App\Http\Controllers\Affiliate;

use App\Domain\Affiliate\Enums\AffiliateProfileStatus;
use App\Domain\Affiliate\Exceptions\AffiliateDomainException;
use App\Domain\Affiliate\Services\AffiliateEmployeeEligibility;
use App\Domain\Affiliate\Services\AffiliateIdentityService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Affiliate\SubmitAffiliateIdentityRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AffiliateIdentityController extends Controller
{
    public function __construct(
        private readonly AffiliateIdentityService $identity_service,
        private readonly AffiliateEmployeeEligibility $eligibility,
    ) {}

    public function Show(Request $request)
    {
        $user = $request->user();
        if (!$this->eligibility->IsAffiliateOnly($user)) {
            throw AffiliateDomainException::OwnershipDenied();
        }

        $profile = $user->AffiliateProfile()->with('IdentityProfile')->firstOrFail();
        if ($profile->status === AffiliateProfileStatus::ACTIVE) {
            return redirect()->route('affiliate.dashboard');
        }
        if ($profile->status === AffiliateProfileStatus::PENDING_APPROVAL) {
            return redirect()->route('affiliate.waiting');
        }
        if ($profile->status !== AffiliateProfileStatus::PENDING_IDENTITY) {
            return redirect()->route('affiliate.waiting');
        }

        return view('affiliate.identity.show', [
            'identity' => $profile->IdentityProfile,
        ]);
    }

    public function Store(SubmitAffiliateIdentityRequest $request): RedirectResponse
    {
        if (!$this->eligibility->IsAffiliateOnly($request->user())) {
            throw AffiliateDomainException::OwnershipDenied();
        }

        $this->identity_service->Submit(
            $request->user(),
            $request->validated(),
            $request->file('id_document'),
            (string) ($request->attributes->get('affiliate_correlation_id') ?: Str::uuid()),
        );

        return redirect()->route('affiliate.waiting')->with('identity_submitted', true);
    }

    public function Document(Request $request): BinaryFileResponse
    {
        if (!$this->eligibility->IsAffiliateOnly($request->user())) {
            throw AffiliateDomainException::OwnershipDenied();
        }

        $identity = $request->user()?->AffiliateProfile?->IdentityProfile;
        if ($identity === null || blank($identity->id_document_path) || !Storage::disk('local')->exists($identity->id_document_path)) {
            abort(404);
        }

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
}
