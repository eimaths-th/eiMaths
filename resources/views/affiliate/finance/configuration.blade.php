@extends('layouts.app')

@section('content')
<main class="container-fluid py-4" aria-labelledby="affiliate-configuration-title">
    <section class="content-header px-0">
        <h1 id="affiliate-configuration-title">{{ __('affiliate.configuration.title') }}</h1>
        <p class="text-muted">{{ __('affiliate.configuration.description') }}</p>
    </section>

    @if (session('status'))
        <div class="alert alert-success" role="status">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <p class="mb-1">{{ __('affiliate.configuration.validation.summary') }}</p>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('affiliate.finance.configuration.page.update') }}" novalidate>
        @csrf

        <section class="card card-primary mb-4" aria-labelledby="affiliate-configuration-fields-title">
            <div class="card-header">
                <h2 id="affiliate-configuration-fields-title" class="card-title">{{ __('affiliate.configuration.fields_title') }}</h2>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="commission_rate_percent">{{ __('affiliate.configuration.commission_rate') }}</label>
                    <div class="input-group">
                        <input
                            id="commission_rate_percent"
                            name="commission_rate_percent"
                            type="text"
                            inputmode="decimal"
                            value="{{ old('commission_rate_percent', $settings['commission_rate_percent']) }}"
                            class="form-control @error('commission_rate_percent') is-invalid @enderror"
                            aria-describedby="commission-rate-help commission-rate-error"
                        >
                        <div class="input-group-append"><span class="input-group-text">{{ __('affiliate.configuration.unit_percent') }}</span></div>
                    </div>
                    <small id="commission-rate-help" class="form-text text-muted">{{ __('affiliate.configuration.commission_rate_help') }}</small>
                    @error('commission_rate_percent')
                        <div id="commission-rate-error" class="invalid-feedback d-block" role="alert">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="commission_holding_days">{{ __('affiliate.configuration.commission_holding_days') }}</label>
                    <div class="input-group">
                        <input
                            id="commission_holding_days"
                            name="commission_holding_days"
                            type="number"
                            min="0"
                            max="3650"
                            step="1"
                            value="{{ old('commission_holding_days', $settings['commission_holding_days']) }}"
                            class="form-control @error('commission_holding_days') is-invalid @enderror"
                            aria-describedby="commission-holding-help commission-holding-error"
                        >
                        <div class="input-group-append"><span class="input-group-text">{{ __('affiliate.configuration.unit_days') }}</span></div>
                    </div>
                    <small id="commission-holding-help" class="form-text text-muted">{{ __('affiliate.configuration.commission_holding_days_help') }}</small>
                    @error('commission_holding_days')
                        <div id="commission-holding-error" class="invalid-feedback d-block" role="alert">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="attribution_duration_days">{{ __('affiliate.configuration.attribution_duration') }}</label>
                    <div class="input-group">
                        <input
                            id="attribution_duration_days"
                            name="attribution_duration_days"
                            type="number"
                            min="1"
                            max="3650"
                            step="1"
                            value="{{ old('attribution_duration_days', $settings['attribution_duration_days']) }}"
                            class="form-control @error('attribution_duration_days') is-invalid @enderror"
                            aria-describedby="attribution-duration-help attribution-duration-error"
                        >
                        <div class="input-group-append"><span class="input-group-text">{{ __('affiliate.configuration.unit_days') }}</span></div>
                    </div>
                    <small id="attribution-duration-help" class="form-text text-muted">{{ __('affiliate.configuration.attribution_duration_help') }}</small>
                    @error('attribution_duration_days')
                        <div id="attribution-duration-error" class="invalid-feedback d-block" role="alert">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group mb-0">
                    <label for="minimum_withdrawal_baht">{{ __('affiliate.configuration.minimum_withdrawal') }}</label>
                    <div class="input-group">
                        <input
                            id="minimum_withdrawal_baht"
                            name="minimum_withdrawal_baht"
                            type="text"
                            inputmode="decimal"
                            value="{{ old('minimum_withdrawal_baht', $settings['minimum_withdrawal_baht']) }}"
                            class="form-control @error('minimum_withdrawal_baht') is-invalid @enderror"
                            aria-describedby="minimum-withdrawal-help minimum-withdrawal-error"
                        >
                        <div class="input-group-append"><span class="input-group-text">{{ __('affiliate.configuration.unit_baht') }}</span></div>
                    </div>
                    <small id="minimum-withdrawal-help" class="form-text text-muted">{{ __('affiliate.configuration.minimum_withdrawal_help') }}</small>
                    @error('minimum_withdrawal_baht')
                        <div id="minimum-withdrawal-error" class="invalid-feedback d-block" role="alert">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </section>

        <section class="card card-secondary mb-4" aria-labelledby="affiliate-approval-policy-title">
            <div class="card-header">
                <h2 id="affiliate-approval-policy-title" class="card-title">{{ __('affiliate.configuration.withdrawal_approval_title') }}</h2>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ __('affiliate.configuration.withdrawal_approval_mandatory') }}</p>
            </div>
        </section>

        <button type="submit" class="btn btn-primary">{{ __('affiliate.configuration.save') }}</button>
    </form>
</main>
@endsection
