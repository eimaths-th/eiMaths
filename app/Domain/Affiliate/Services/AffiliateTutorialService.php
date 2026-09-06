<?php

namespace App\Domain\Affiliate\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AffiliateTutorialService
{
    public const VERSION = 1;

    public function ForUser(User $user): array
    {
        $profile = $user->AffiliateProfile()->firstOrFail();
        $progress = DB::table('affiliate_tutorial_progress')->where('affiliate_profile_id', $profile->id)->first();

        return [
            'version' => self::VERSION,
            'progressUrl' => route('affiliate.tutorial.progress'),
            'dashboardUrl' => route('affiliate.dashboard'),
            'autoStart' => $progress === null && request()->routeIs('affiliate.dashboard'),
            'progress' => $progress === null ? null : [
                'status' => $progress->status,
                'step' => $progress->step_key,
            ],
            'labels' => __('affiliate_tutorial.labels'),
            'steps' => $this->Steps(),
        ];
    }

    public function Steps(): array
    {
        $steps = [];
        $add = function (string $key, string $route, ?string $target, ?string $fallback = null, ?string $image = null) use (&$steps): void {
            $steps[] = [
                'key' => $key,
                'url' => route($route),
                'target' => $target,
                'fallback' => $fallback,
                'title' => __('affiliate_tutorial.steps.' . $key . '.title'),
                'body' => __('affiliate_tutorial.steps.' . $key . '.body'),
                'emptyBody' => trans()->has('affiliate_tutorial.steps.' . $key . '.empty')
                    ? __('affiliate_tutorial.steps.' . $key . '.empty') : null,
                'image' => $image === null ? null : asset('images/affiliate/tutorial/' . $image),
            ];
        };

        $add('welcome', 'affiliate.dashboard', null, image: 'welcome.png');
        $add('code', 'affiliate.dashboard', '[data-affiliate-copy-referral-code]');
        $add('link', 'affiliate.dashboard', '[data-affiliate-referral-url]');
        $add('copy', 'affiliate.dashboard', '.affiliate-page-intro [data-affiliate-copy-referral-link]');
        $add('guide', 'affiliate.guide.index', '#affiliate-guide-process-title');

        if ((bool) config('affiliate.features.media_library', false)) {
            $add('media', 'affiliate.media.index', '.affiliate-media-folder', image: 'media-folders.png');
            $add('search', 'affiliate.media.index', '#affiliate-media-search');
            $add('download', 'affiliate.media.index', '[data-media-card] .affiliate-media-card__footer a', '.affiliate-media-empty');
            $add('share', 'affiliate.media.index', '[data-media-card] .affiliate-media-card__footer [data-media-share-panel]', '.affiliate-media-empty');
        }

        if ((bool) config('affiliate.features.analytics', false)) {
            $add('dates', 'affiliate.analytics.index', '[data-affiliate-analytics-range]');
            $add('analytics', 'affiliate.analytics.index', '#affiliate-analytics-kpis-title');
        }

        $add('wallet', 'affiliate.dashboard', '[data-tutorial-wallet]');
        $add('commission', 'affiliate.dashboard', '#commission-history-title');
        $add('promptpay', 'affiliate.payout-accounts.index', '#promptpay_identifier', image: 'payout-form.png');
        $add('bank', 'affiliate.payout-accounts.index', '#account_number');
        $add('password', 'affiliate.payout-accounts.index', '#bank_current_password');
        $add('withdraw', 'affiliate.withdrawals.index', '#withdrawal_amount', '#withdrawal-request-title');
        $add('history', 'affiliate.withdrawals.index', '#withdrawal-history-title');
        $add('done', 'affiliate.dashboard', null, image: 'welcome.png');

        return $steps;
    }

    public function Save(User $user, array $input): array
    {
        $steps = $this->Steps();
        $validated = Validator::make($input, [
            'version' => ['required', 'integer', Rule::in([self::VERSION])],
            'action' => ['required', Rule::in(['start', 'advance', 'skip', 'finish'])],
            'step' => ['required', Rule::in(array_column($steps, 'key'))],
        ])->validate();

        // The owner comes from the session, never from client-supplied identifiers.
        return DB::transaction(function () use ($user, $validated, $steps): array {
            $profile = $user->AffiliateProfile()->lockForUpdate()->firstOrFail();
            $prior = DB::table('affiliate_tutorial_progress')->where('affiliate_profile_id', $profile->id)->first();
            $action = $validated['action'];
            $step = $action === 'start' ? $steps[0]['key'] : $validated['step'];
            abort_if($action === 'finish' && $step !== 'done', 422);
            abort_if($action === 'advance' && ($prior === null || $prior->status !== 'in_progress'), 409);

            $status = match ($action) {
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
                    'finished_at' => $status === 'in_progress' ? null : $now,
                    'created_at' => $prior?->created_at ?? $now,
                    'updated_at' => $now,
                ],
            );

            return ['status' => $status, 'step' => $step];
        });
    }
}
