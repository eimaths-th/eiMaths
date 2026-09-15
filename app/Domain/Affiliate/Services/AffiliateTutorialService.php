<?php

namespace App\Domain\Affiliate\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AffiliateTutorialService
{
    public const VERSION = 2;

    public function ForUser(User $user): array
    {
        $profile = $user->AffiliateProfile()->firstOrFail();
        $progress = DB::table('affiliate_tutorial_progress')->where('affiliate_profile_id', $profile->id)->first();
        $steps = $this->Steps();
        // Preserve old accounts' completion and resume at the equivalent lesson.
        $key = match ($progress?->step_key) {
            'code' => 'link',
            'password' => 'bank',
            default => $progress?->step_key,
        };
        if (!in_array($key, array_column($steps, 'key'), true)) {
            $key = 'welcome';
        }

        return [
            'version' => self::VERSION,
            'progressUrl' => route('affiliate.tutorial.progress'),
            'storageKey' => 'affiliate-tutorial:' . $profile->id,
            'dashboardUrl' => route('affiliate.dashboard'),
            'completionStep' => 'done',
            'autoStart' => $progress === null && request()->routeIs('affiliate.dashboard'),
            'progress' => $progress === null ? null : ['status' => $progress->status, 'step' => $key],
            'labels' => __('affiliate_tutorial.labels'),
            'chapters' => __('affiliate_tutorial.chapters'),
            'starterSteps' => $this->StarterSteps($steps),
            'steps' => $steps,
        ];
    }

    public function Terms(): array
    {
        $settings = app(AffiliateConfigurationService::class);

        return [
            'rate' => rtrim(rtrim(number_format($settings->CommissionRateBasisPoints() / 100, 2, '.', ''), '0'), '.'),
            'holding_days' => $settings->CommissionHoldingDays(),
            'attribution_days' => $settings->AttributionDurationDays(),
            'minimum' => number_format($settings->MinimumWithdrawalMinor() / 100, 2),
            'support_phone' => (string) config('affiliate.legal.support_phone'),
        ];
    }

    public function Steps(): array
    {
        $steps = [];
        $terms = $this->Terms();
        $add = function (string $key, string $chapter, string $route, ?string $target, ?string $fallback = null, ?string $example = null) use (&$steps, $terms): void {
            $steps[] = [
                'key' => $key,
                'chapter' => $chapter,
                'url' => route($route),
                'target' => $target,
                'mobileTarget' => match ($key) {
                    'guide' => '[data-tutorial-process] .affiliate-guide-process',
                    'analytics' => '[data-tutorial-kpis] .affiliate-kpi:has([data-affiliate-kpi="net_commission_income"])',
                    'promptpay' => '[data-tutorial-promptpay] .card-body',
                    'bank' => '[data-tutorial-bank] .card-body',
                    default => null,
                },
                'fallback' => $fallback,
                'title' => __('affiliate_tutorial.steps.' . $key . '.title'),
                'body' => __('affiliate_tutorial.steps.' . $key . '.body', $terms),
                'emptyBody' => trans()->has('affiliate_tutorial.steps.' . $key . '.empty')
                    ? __('affiliate_tutorial.steps.' . $key . '.empty', $terms) : null,
                'example' => $example,
                'image' => in_array($key, ['welcome', 'done'], true) ? asset('images/affiliate/tutorial/journey-v2.png') . '?v=' . filemtime(public_path('images/affiliate/tutorial/journey-v2.png')) : null,
            ];
        };

        $add('welcome', 'share', 'affiliate.dashboard', null);
        $add('link', 'share', 'affiliate.dashboard', '[data-tutorial-referral]');
        $add('copy', 'share', 'affiliate.dashboard', '.affiliate-page-intro [data-affiliate-copy-referral-link]', example: 'link');
        $add('guide', 'share', 'affiliate.guide.index', '[data-tutorial-process]');
        $add('curriculum', 'share', 'affiliate.guide.index', '.affiliate-curriculum-card');

        if ((bool) config('affiliate.features.media_library', false)) {
            $add('media', 'media', 'affiliate.media.index', '[data-tutorial-folders]');
            $add('search', 'media', 'affiliate.media.index', '[data-tutorial-search]');
            $add('download', 'media', 'affiliate.media.index', '[data-media-card] .affiliate-media-card__footer a', '.affiliate-media-empty');
            $add('share', 'media', 'affiliate.media.index', '[data-media-card] .affiliate-media-card__footer [data-media-share-panel]', '.affiliate-media-empty');
            $add('share_options', 'media', 'affiliate.media.index', '.affiliate-media-share-destinations', '.affiliate-media-empty', 'share');
        }

        if ((bool) config('affiliate.features.analytics', false)) {
            $add('dates', 'income', 'affiliate.analytics.index', '[data-affiliate-analytics-range]');
            $add('analytics', 'income', 'affiliate.analytics.index', '[data-tutorial-kpis]');
        }
        $add('wallet', 'income', 'affiliate.dashboard', '[data-tutorial-wallet]', example: 'wallet');
        $add('commission', 'income', 'affiliate.dashboard', '[data-tutorial-commissions]', example: 'commission');
        $add('promptpay', 'payout', 'affiliate.payout-accounts.index', '[data-tutorial-promptpay]', example: 'promptpay');
        $add('bank', 'payout', 'affiliate.payout-accounts.index', '[data-tutorial-bank]', example: 'bank');
        $add('accounts', 'payout', 'affiliate.payout-accounts.index', '[data-tutorial-accounts]', example: 'accounts');
        $add('withdraw', 'withdraw', 'affiliate.withdrawals.index', '[data-tutorial-withdraw-form]', '#withdrawal-request-title', 'withdraw');
        $add('history', 'withdraw', 'affiliate.withdrawals.index', '[data-tutorial-withdraw-history]', example: 'history');
        $add('help', 'withdraw', 'affiliate.guide.index', '[data-tutorial-support]');
        $add('done', 'withdraw', 'affiliate.dashboard', null);

        return $steps;
    }

    public function StarterSteps(?array $steps = null): array
    {
        $steps ??= $this->Steps();
        $stepsByKey = array_column($steps, null, 'key');
        $terms = [...$this->Terms(), 'total' => count($steps)];
        $starterSteps = [];

        foreach (['copy', 'commission', 'promptpay', 'withdraw'] as $key) {
            $step = $stepsByKey[$key];
            $translationKey = 'affiliate_tutorial.starter_steps.' . $key;
            $step['title'] = __($translationKey . '.title');
            $step['body'] = __($translationKey . '.body', $terms);
            $step['emptyBody'] = trans()->has($translationKey . '.empty')
                ? __($translationKey . '.empty', $terms) : null;

            if ($key === 'promptpay') {
                $step['target'] = '#payout-account-forms-title';
                $step['mobileTarget'] = null;
                $step['fallback'] = null;
            }

            $starterSteps[] = $step;
        }

        return $starterSteps;
    }

    public function Save(User $user, array $input): array
    {
        $steps = $this->Steps();
        $validated = Validator::make($input, [
            'version' => ['required', 'integer', Rule::in([self::VERSION])],
            'action' => ['required', Rule::in(['start', 'advance', 'pause', 'skip', 'finish'])],
            'step' => ['required', Rule::in(array_column($steps, 'key'))],
        ])->validate();

        return DB::transaction(function () use ($user, $validated): array {
            $profile = $user->AffiliateProfile()->lockForUpdate()->firstOrFail();
            $prior = DB::table('affiliate_tutorial_progress')->where('affiliate_profile_id', $profile->id)->first();
            $action = $validated['action'];
            $step = $validated['step'];
            abort_if($action === 'finish' && $step !== 'done', 422);
            abort_if($action === 'advance' && ($prior === null || $prior->status !== 'in_progress'), 409);
            $status = match ($action) {
                'pause' => 'paused',
                'skip' => 'skipped',
                'finish' => 'completed',
                default => 'in_progress',
            };
            $now = now();
            DB::table('affiliate_tutorial_progress')->updateOrInsert(
                ['affiliate_profile_id' => $profile->id],
                [
                    'version' => self::VERSION,
                    'status' => $status,
                    'step_key' => $step,
                    'started_at' => $action === 'start' ? $now : ($prior?->started_at ?? $now),
                    'finished_at' => in_array($status, ['in_progress', 'paused'], true) ? null : $now,
                    'created_at' => $prior?->created_at ?? $now,
                    'updated_at' => $now,
                ],
            );

            return ['status' => $status, 'step' => $step];
        });
    }
}
