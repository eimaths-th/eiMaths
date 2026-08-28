<?php

use App\Http\Controllers\Affiliate\AffiliateAccountAdminController;
use App\Http\Controllers\Affiliate\AffiliatePayoutAccountController;
use App\Http\Controllers\Affiliate\AffiliateWithdrawalController;
use App\Http\Controllers\Affiliate\AffiliateWithdrawalAdminController;
use App\Http\Controllers\Affiliate\AffiliateDashboardController;
use App\Http\Controllers\Affiliate\AffiliateCommissionAdminController;
use App\Http\Controllers\Affiliate\AffiliateFinanceAdminController;
use App\Http\Controllers\Affiliate\AffiliateConfigurationPageController;
use App\Http\Controllers\Affiliate\AffiliateReconciliationController;
use App\Http\Controllers\Affiliate\AffiliatePayoutAccountPageController;
use App\Http\Controllers\Affiliate\AffiliateTrackingController;
use App\Http\Controllers\Affiliate\AffiliateAnalyticsController;
use App\Http\Controllers\Affiliate\AffiliateMediaController;
use App\Http\Controllers\Affiliate\AffiliateGuideController;
use App\Http\Controllers\Affiliate\AffiliateEmployeeAccessController;
use App\Http\Controllers\AffiliateContentManageController;
use App\Http\Controllers\Affiliate\AffiliateWithdrawalPageController;
use Illuminate\Support\Facades\Route;

Route::get('/r/{referral_code}/{campaign_code}', [AffiliateTrackingController::class, 'Media'])
    ->middleware(['affiliate.correlation', 'throttle:affiliate-click'])
    ->where('referral_code', '[A-Za-z0-9-]{3,64}')
    ->where('campaign_code', '[a-z0-9]+(?:-[a-z0-9]+)*')
    ->name('affiliate.tracking.media');

Route::get('/r/{referral_code}', [AffiliateTrackingController::class, 'Direct'])
    ->middleware(['affiliate.correlation', 'throttle:affiliate-click'])
    ->where('referral_code', '[A-Za-z0-9-]{3,64}')
    ->name('affiliate.tracking.direct');

Route::middleware([
    'auth',
    'affiliate.locale',
    'affiliate.correlation',
    'affiliate.employee-eligibility',
    'affiliate.feature:employee_enrollment',
])
    ->prefix('affiliate/employee-access')
    ->group(function (): void {
        Route::get('/', [AffiliateEmployeeAccessController::class, 'Show'])
            ->name('affiliate.employee-access.show');
        Route::post('/', [AffiliateEmployeeAccessController::class, 'Store'])
            ->middleware('throttle:affiliate-employee-enrollment')
            ->name('affiliate.employee-access.store');
    });

Route::middleware(['auth', 'affiliate.locale', 'affiliate.correlation', 'affiliate.portal-access', 'affiliate.feature:analytics'])
    ->prefix('affiliate/analytics')
    ->group(function (): void {
        Route::get('/', [AffiliateAnalyticsController::class, 'Index'])->name('affiliate.analytics.index');
        Route::get('/data', [AffiliateAnalyticsController::class, 'Data'])->name('affiliate.analytics.data');
    });

Route::get('/affiliate/media', [AffiliateMediaController::class, 'Index'])
    ->middleware(['auth', 'affiliate.locale', 'affiliate.correlation', 'affiliate.portal-access', 'affiliate.feature:media_library'])
    ->name('affiliate.media.index');

Route::middleware(['auth', 'affiliate.locale', 'affiliate.correlation', 'affiliate.portal-access'])
    ->prefix('affiliate/guide')
    ->group(function (): void {
        Route::get('/', [AffiliateGuideController::class, 'Index'])->name('affiliate.guide.index');
        Route::get('/document', [AffiliateGuideController::class, 'Document'])->name('affiliate.guide.document');
    });

Route::middleware(['auth', 'affiliate.locale', 'affiliate.correlation', 'affiliate.feature:administration', 'permission:affiliate-content-manage'])
    ->prefix('affiliate-content-manage')
    ->group(function (): void {
        Route::get('/', [AffiliateContentManageController::class, 'Index'])->name('affiliate-content-manage.index');
        Route::get('/generate-campaign-code', [AffiliateContentManageController::class, 'GenerateCampaignCode'])->name('affiliate-content-manage.generate-campaign-code');
        Route::post('/', [AffiliateContentManageController::class, 'StoreValidated'])->name('affiliate-content-manage.store');
        Route::put('/{affiliate_content}', [AffiliateContentManageController::class, 'UpdateValidated'])->name('affiliate-content-manage.update');
        Route::delete('/{affiliate_content}', [AffiliateContentManageController::class, 'Destroy'])->name('affiliate-content-manage.destroy');
        Route::post('/toggle-status', [AffiliateContentManageController::class, 'ToggleStatusValidated'])->name('affiliate-content-manage.toggle-status');
    });

Route::middleware(['auth', 'affiliate.correlation', 'affiliate.feature:administration'])->prefix('admin/affiliates')->group(function (): void {
    Route::get('/', [AffiliateAccountAdminController::class, 'index'])
        ->middleware('permission:affiliate-list')
        ->name('affiliate.admin.index');
    Route::get('/pending-approval-summary', [AffiliateAccountAdminController::class, 'PendingApprovalSummary'])
        ->middleware('permission:affiliate-list')
        ->name('affiliate.admin.pending-summary');
    Route::post('/{affiliate_profile}/approve', [AffiliateAccountAdminController::class, 'approve'])
        ->middleware('permission:affiliate-approve')
        ->name('affiliate.admin.approve');
    Route::post('/{affiliate_profile}/suspend', [AffiliateAccountAdminController::class, 'suspend'])
        ->middleware('permission:affiliate-suspend')
        ->name('affiliate.admin.suspend');
    Route::post('/{affiliate_profile}/reactivate', [AffiliateAccountAdminController::class, 'reactivate'])
        ->middleware('permission:affiliate-suspend')
        ->name('affiliate.admin.reactivate');
    Route::post('/{affiliate_profile}/deactivate', [AffiliateAccountAdminController::class, 'deactivate'])
        ->middleware('permission:affiliate-suspend')
        ->name('affiliate.admin.deactivate');
});

Route::middleware(['auth', 'affiliate.locale', 'affiliate.correlation', 'affiliate.portal-access', 'affiliate.feature:wallet_withdrawals'])->prefix('affiliate/payout-accounts')->group(function (): void {
    Route::put('/promptpay', [AffiliatePayoutAccountController::class, 'PromptPay'])->name('affiliate.payout-accounts.promptpay');
    Route::put('/bank', [AffiliatePayoutAccountController::class, 'Bank'])->name('affiliate.payout-accounts.bank');
});

Route::middleware(['auth', 'affiliate.locale', 'affiliate.correlation', 'affiliate.portal-access', 'affiliate.feature:wallet_withdrawals'])->prefix('affiliate/withdrawals')->group(function (): void {
    Route::post('/', [AffiliateWithdrawalController::class, 'Store'])->name('affiliate.withdrawals.store');
    Route::post('/{withdrawal}/cancel', [AffiliateWithdrawalController::class, 'Cancel'])->name('affiliate.withdrawals.cancel');
});

Route::get('/affiliate/dashboard', [AffiliateDashboardController::class, 'Index'])
    ->middleware(['auth', 'affiliate.locale', 'affiliate.correlation', 'affiliate.portal-access', 'affiliate.feature:wallet_withdrawals'])
    ->name('affiliate.dashboard');

Route::get('/affiliate/payout-accounts', [AffiliatePayoutAccountPageController::class, 'Index'])
    ->middleware(['auth', 'affiliate.locale', 'affiliate.correlation', 'affiliate.portal-access', 'affiliate.feature:wallet_withdrawals'])
    ->name('affiliate.payout-accounts.index');

Route::get('/affiliate/withdrawals', [AffiliateWithdrawalPageController::class, 'Index'])
    ->middleware(['auth', 'affiliate.locale', 'affiliate.correlation', 'affiliate.portal-access', 'affiliate.feature:wallet_withdrawals'])
    ->name('affiliate.withdrawals.index');

Route::get('/affiliate/my_affiliate', fn () => redirect()->route('affiliate.dashboard'))
    ->middleware(['auth', 'affiliate.locale', 'affiliate.correlation', 'affiliate.portal-access', 'affiliate.feature:wallet_withdrawals'])
    ->name('my_affiliate.index');

Route::middleware(['auth', 'affiliate.correlation', 'affiliate.feature:wallet_withdrawals'])->prefix('admin/affiliate-withdrawals')->group(function (): void {
    Route::get('/', [AffiliateWithdrawalAdminController::class, 'Index'])
        ->middleware('permission:affiliate-withdrawal-list')
        ->name('affiliate.withdrawals.admin.index');
    Route::post('/bulk-approve', [AffiliateWithdrawalAdminController::class, 'BulkApprove'])
        ->middleware('permission:affiliate-withdrawal-approve')
        ->name('affiliate.withdrawals.admin.bulk-approve');
    Route::post('/{withdrawal}/payout-details/reveal', [AffiliateWithdrawalAdminController::class, 'RevealPayoutDetails'])
        ->middleware('permission:affiliate-payout-details-view')
        ->name('affiliate.withdrawals.admin.payout-details.reveal');
    Route::post('/{withdrawal}/approve', [AffiliateWithdrawalAdminController::class, 'Approve'])
        ->middleware('permission:affiliate-withdrawal-approve')
        ->name('affiliate.withdrawals.admin.approve');
    Route::post('/{withdrawal}/pay', [AffiliateWithdrawalAdminController::class, 'Pay'])
        ->middleware('permission:affiliate-withdrawal-pay')
        ->name('affiliate.withdrawals.admin.pay');
    Route::post('/{withdrawal}/reject', [AffiliateWithdrawalAdminController::class, 'Reject'])
        ->middleware('permission:affiliate-withdrawal-approve')
        ->name('affiliate.withdrawals.admin.reject');
});

Route::middleware(['auth', 'affiliate.correlation', 'affiliate.feature:administration'])->prefix('admin/affiliate-finance')->group(function (): void {
    Route::get('/commissions', [AffiliateCommissionAdminController::class, 'Index'])
        ->middleware('permission:affiliate-list')
        ->name('affiliate.finance.commissions.index');
    Route::post('/commissions/{commission}/hold', [AffiliateCommissionAdminController::class, 'Hold'])
        ->middleware('permission:affiliate-commission-hold')
        ->name('affiliate.finance.commissions.hold');
    Route::post('/commissions/{commission}/release-hold', [AffiliateCommissionAdminController::class, 'ReleaseHold'])
        ->middleware('permission:affiliate-commission-hold')
        ->name('affiliate.finance.commissions.release-hold');
    Route::get('/configuration', [AffiliateFinanceAdminController::class, 'Configuration'])
        ->middleware('permission:affiliate-config-edit')
        ->name('affiliate.finance.configuration');
    Route::put('/configuration', [AffiliateFinanceAdminController::class, 'UpdateConfiguration'])
        ->middleware('permission:affiliate-config-edit')
        ->name('affiliate.finance.configuration.update');
    Route::get('/configuration/edit', [AffiliateConfigurationPageController::class, 'Edit'])
        ->middleware('permission:affiliate-config-edit')
        ->name('affiliate.finance.configuration.edit');
    Route::post('/configuration/edit', [AffiliateConfigurationPageController::class, 'Update'])
        ->middleware('permission:affiliate-config-edit')
        ->name('affiliate.finance.configuration.page.update');
});

Route::post('/admin/affiliate-finance/receipts/{receipt}/refunds', [AffiliateFinanceAdminController::class, 'RecordRefund'])
    ->middleware(['auth', 'affiliate.correlation', 'affiliate.feature:administration', 'affiliate.feature:confirmed_refunds', 'permission:affiliate-refund-record'])
    ->name('affiliate.finance.refunds.store');

Route::middleware(['auth', 'affiliate.correlation', 'affiliate.feature:administration'])->prefix('admin/affiliate-reconciliation')->group(function (): void {
    Route::get('/', [AffiliateReconciliationController::class, 'Index'])
        ->middleware('permission:affiliate-reconciliation')
        ->name('affiliate.reconciliation.index');
    Route::post('/{issue}/resolve', [AffiliateReconciliationController::class, 'Resolve'])
        ->middleware('permission:affiliate-reconciliation')
        ->name('affiliate.reconciliation.resolve');
    Route::post('/{issue}/adjust', [AffiliateReconciliationController::class, 'Adjust'])
        ->middleware('permission:affiliate-reconciliation')
        ->name('affiliate.reconciliation.adjust');
});
