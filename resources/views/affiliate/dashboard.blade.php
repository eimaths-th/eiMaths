@extends('layouts.app')

@section('content')
<main class="affiliate-portal" aria-labelledby="affiliate-dashboard-title">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-12">
                    <h1 id="affiliate-dashboard-title" class="mb-0">{{ __('affiliate.dashboard.title') }}</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            @include('affiliate.partials.navigation')

            <section class="affiliate-page-intro p-3 mb-4" aria-label="{{ __('affiliate.dashboard.referral_code') }}">
                <div class="d-flex flex-wrap align-items-center mb-2">
                    <strong>{{ __('affiliate.dashboard.referral_code') }}:</strong>
                    <code class="ml-1" data-affiliate-referral-code>{{ $profile->referral_code }}</code>
                    <button class="btn btn-outline-primary btn-sm ml-3" type="button" data-affiliate-copy-referral-code data-affiliate-copy-value="{{ $profile->referral_code }}" data-affiliate-copy-success="{{ __('affiliate.dashboard.referral_code_copied') }}" data-affiliate-copy-unavailable="{{ __('affiliate.dashboard.copy_unavailable') }}">
                        {{ __('affiliate.dashboard.copy_referral_code') }}
                    </button>
                </div>
                <div class="d-flex flex-wrap align-items-center">
                    <strong class="mr-2">{{ __('affiliate.dashboard.referral_link') }}:</strong>
                    <a class="affiliate-referral-link text-break mr-3" href="{{ $referral_url }}" data-affiliate-referral-url>{{ $referral_url }}</a>
                    <button class="btn btn-primary btn-sm" type="button" data-affiliate-copy-referral-link data-affiliate-copy-value="{{ $referral_url }}" data-affiliate-copy-success="{{ __('affiliate.dashboard.referral_link_copied') }}" data-affiliate-copy-unavailable="{{ __('affiliate.dashboard.copy_unavailable') }}">
                        {{ __('affiliate.dashboard.copy_referral_link') }}
                    </button>
                </div>
                <span class="d-block text-muted mt-2" role="status" aria-live="polite" data-affiliate-referral-feedback></span>
            </section>

            <section class="affiliate-guide-callout mb-4" aria-labelledby="affiliate-guide-callout-title">
                <div class="affiliate-guide-callout-mark" aria-hidden="true"><i class="fas fa-book-open"></i></div>
                <div class="affiliate-guide-callout-content">
                    <p class="affiliate-guide-eyebrow mb-1">{{ __('affiliate.guide.eyebrow') }}</p>
                    <h2 id="affiliate-guide-callout-title">{{ __('affiliate.guide.intro_title') }}</h2>
                    <p class="mb-3">{{ __('affiliate.guide.intro') }}</p>
                    <button type="button" class="btn affiliate-tutorial-button" data-affiliate-tutorial-start aria-haspopup="dialog" aria-controls="affiliate-tutorial" aria-busy="true" disabled>
                        <i class="fas fa-play-circle mr-1" aria-hidden="true"></i>{{ __('affiliate_tutorial.button') }}
                    </button>
                </div>
            </section>

            @include('affiliate.partials.analytics-summary')

            @if (session('success'))
                <div class="alert alert-success" role="status">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <section class="mb-4" aria-label="{{ __('affiliate.dashboard.wallet_summary') }}" data-tutorial-wallet>
                <div class="row">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <div class="card card-primary affiliate-card h-100">
                            <div class="card-header"><h2 class="card-title">{{ __('affiliate.dashboard.available_balance') }}</h2></div>
                            <div class="card-body"><p class="h3 mb-0">{{ number_format($wallet->available_balance_minor / 100, 2) }}</p></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card card-primary affiliate-card h-100">
                            <div class="card-header"><h2 class="card-title">{{ __('affiliate.dashboard.reserved_balance') }}</h2></div>
                            <div class="card-body"><p class="h3 mb-0">{{ number_format($wallet->reserved_balance_minor / 100, 2) }}</p></div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="card card-primary affiliate-card mb-4" aria-labelledby="commission-history-title">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h2 id="commission-history-title" class="card-title">{{ __('affiliate.dashboard.commission_history') }}</h2>
                </div>
                <div class="card-body p-0">
                    <div class="affiliate-table-scroll">
                        <table class="table table-bordered table-striped table-hover mb-0" aria-describedby="commission-history-title">
                            <thead><tr><th scope="col">{{ __('affiliate.dashboard.id') }}</th><th scope="col">{{ __('affiliate.dashboard.status') }}</th><th scope="col">{{ __('affiliate.dashboard.amount') }}</th><th scope="col">{{ __('affiliate.dashboard.release_at') }}</th></tr></thead>
                            <tbody>
                            @forelse ($commissions as $commission)
                                <tr><td>{{ $commission->id }}</td><td>{{ $commission->status->value }}</td><td>{{ number_format($commission->commission_minor / 100, 2) }}</td><td>{{ optional($commission->release_at)->toDateTimeString() }}</td></tr>
                            @empty
                                <tr><td colspan="4">{{ __('affiliate.dashboard.no_commissions') }}</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if ($commissions->hasPages())
                    <div class="card-footer">{{ $commissions->links() }}</div>
                @endif
            </section>

            <section class="card card-primary affiliate-card" aria-labelledby="withdrawal-history-title">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h2 id="withdrawal-history-title" class="card-title">{{ __('affiliate.dashboard.withdrawal_history') }}</h2>
                    <a class="btn btn-light btn-sm" href="{{ route('affiliate.withdrawals.index') }}">{{ __('affiliate.dashboard.open_withdrawals') }}</a>
                </div>
                <div class="card-body p-0">
                    <div class="affiliate-table-scroll">
                        <table class="table table-bordered table-striped table-hover mb-0" aria-describedby="withdrawal-history-title">
                            <thead><tr><th scope="col">{{ __('affiliate.dashboard.id') }}</th><th scope="col">{{ __('affiliate.dashboard.status') }}</th><th scope="col">{{ __('affiliate.dashboard.amount') }}</th><th scope="col">{{ __('affiliate.dashboard.requested_at') }}</th><th scope="col">{{ __('affiliate.dashboard.details') }}</th></tr></thead>
                            <tbody>
                            @forelse ($withdrawals as $withdrawal)
                                <tr>
                                    <td>{{ $withdrawal->id }}</td>
                                    <td>{{ $withdrawal->status->value }}</td>
                                    <td>{{ number_format($withdrawal->amount_minor / 100, 2) }}</td>
                                    <td>{{ optional($withdrawal->requested_at)->toDateTimeString() }}</td>
                                    <td><a href="{{ route('affiliate.withdrawals.index') }}">{{ __('affiliate.dashboard.view_withdrawals') }}</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="5">{{ __('affiliate.dashboard.no_withdrawals') }}</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if ($withdrawals->hasPages())
                    <div class="card-footer">{{ $withdrawals->links() }}</div>
                @endif
            </section>
        </div>
    </section>
</main>
@endsection

@section('style')
<link rel="stylesheet" href="{{ asset('css/affiliate-portal.css') }}?v={{ filemtime(public_path('css/affiliate-portal.css')) }}">
@endsection

@section('script')
<script type="module">
    import { InitializeAffiliateAnalyticsSummary, InitializeAffiliateReferralCopyActions } from '{{ asset('js/affiliate-analytics-page.js') }}';
    const analytics_summary = InitializeAffiliateAnalyticsSummary();
    if (!analytics_summary) InitializeAffiliateReferralCopyActions();
</script>
@endsection
