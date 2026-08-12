<?php

namespace App\Http\Controllers\Affiliate;

use App\Domain\Affiliate\Enums\AffiliateProfileStatus;
use App\Domain\Affiliate\Services\AffiliateEmployeeEnrollmentService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class AffiliateEmployeeAccessController extends Controller
{
    public function __construct(
        private readonly AffiliateEmployeeEnrollmentService $enrollment_service,
    ) {
    }

    public function Show(Request $request): View
    {
        $user = $request->user();

        return view('affiliate.employee-access', [
            'user' => $user,
            'affiliate_profile' => $user?->AffiliateProfile,
        ]);
    }

    public function Store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $had_pending_email = $user?->AffiliateProfile?->status === AffiliateProfileStatus::PENDING_EMAIL;

        $this->enrollment_service->Enroll(
            $user,
            (string) $request->attributes->get('affiliate_correlation_id'),
        );

        return redirect()
            ->route('affiliate.employee-access.show')
            ->with(
                'status',
                __($had_pending_email
                    ? 'affiliate.employee_access.email_bypass_requested'
                    : 'affiliate.employee_access.enrollment_requested'),
            );
    }
}
