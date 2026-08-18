<?php

namespace App\Http\Controllers\Affiliate;

use App\Domain\Affiliate\Services\AffiliateConfigurationService;
use App\Domain\Affiliate\ValueObjects\Money;
use App\Http\Controllers\Controller;
use App\Http\Requests\Affiliate\UpdateAffiliateConfigurationPageRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AffiliateConfigurationPageController extends Controller
{
    public function __construct(
        private readonly AffiliateConfigurationService $configuration_service,
    ) {
    }

    public function Edit(): View
    {
        $settings = $this->configuration_service->CurrentSettings();

        return view('affiliate.finance.configuration', [
            'settings' => [
                'commission_rate_percent' => $this->FormatBasisPointsAsPercent($settings['commission_rate_basis_points']),
                'commission_holding_days' => $settings['commission_holding_days'],
                'attribution_duration_days' => $settings['attribution_duration_days'],
                'minimum_withdrawal_baht' => Money::FromMinor($settings['minimum_withdrawal_minor'])->Decimal(),
                'withdrawal_approval_required' => $settings['withdrawal_approval_required'],
            ],
        ]);
    }

    public function Update(UpdateAffiliateConfigurationPageRequest $request): RedirectResponse
    {
        $this->configuration_service->UpdateApprovedSettings(
            $request->ApprovedSettings(),
            $request->user(),
            $this->CorrelationId($request),
        );

        return redirect()
            ->route('affiliate.finance.configuration.edit')
            ->with('status', __('affiliate.configuration.updated'));
    }

    private function FormatBasisPointsAsPercent(int $basis_points): string
    {
        $whole = intdiv($basis_points, 100);
        $fraction = $basis_points % 100;
        if ($fraction === 0) {
            return (string) $whole;
        }
        if ($fraction % 10 === 0) {
            return $whole . '.' . intdiv($fraction, 10);
        }

        return $whole . '.' . str_pad((string) $fraction, 2, '0', STR_PAD_LEFT);
    }

    private function CorrelationId(Request $request): string
    {
        return (string) ($request->attributes->get('affiliate_correlation_id') ?: Str::uuid());
    }
}
