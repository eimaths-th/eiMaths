@extends('layouts.app')

@section('content')
<main class="affiliate-portal" aria-labelledby="affiliate-payout-accounts-title">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-8">
                    <h1 id="affiliate-payout-accounts-title" class="mb-0">{{ __('affiliate.payout_accounts.title') }}</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            @include('affiliate.partials.navigation')

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

            <section aria-labelledby="payout-account-forms-title">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h2 id="payout-account-forms-title" class="h4 mb-0">{{ __('affiliate.payout_accounts.forms_title') }}</h2>
                </div>
                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <form method="POST" action="{{ route('affiliate.payout-accounts.promptpay') }}" class="card card-primary affiliate-card h-100">
                            @csrf
                            @method('PUT')
                            <div class="card-header"><h3 class="card-title">{{ __('affiliate.payout_accounts.promptpay') }}</h3></div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="promptpay_identifier">{{ __('affiliate.payout_accounts.promptpay_number') }}</label>
                                    <input id="promptpay_identifier" name="promptpay_identifier" class="form-control" inputmode="numeric" maxlength="16" placeholder="0812345678" value="{{ old('promptpay_identifier') }}" required>
                                </div>
                                <div class="form-group">
                                    <label for="promptpay_current_password">{{ __('affiliate.payout_accounts.current_password') }}</label>
                                    <input id="promptpay_current_password" name="current_password" type="password" class="form-control" autocomplete="current-password" required>
                                </div>
                            </div>
                            <div class="card-footer"><button type="submit" class="btn btn-primary">{{ __('affiliate.payout_accounts.save_promptpay') }}</button></div>
                        </form>
                    </div>
                    <div class="col-lg-6 mb-4">
                        <form method="POST" action="{{ route('affiliate.payout-accounts.bank') }}" class="card card-primary affiliate-card h-100">
                            @csrf
                            @method('PUT')
                            <div class="card-header"><h3 class="card-title">{{ __('affiliate.payout_accounts.bank_account') }}</h3></div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="bank_name">{{ __('affiliate.payout_accounts.bank_name') }}</label>
                                    <input id="bank_name" name="bank_name" class="form-control" maxlength="255" value="{{ old('bank_name') }}" required>
                                </div>
                                <div class="form-group">
                                    <label for="account_name">{{ __('affiliate.payout_accounts.account_name') }}</label>
                                    <input id="account_name" name="account_name" class="form-control" maxlength="255" value="{{ old('account_name') }}" required>
                                </div>
                                <div class="form-group">
                                    <label for="account_number">{{ __('affiliate.payout_accounts.account_number') }}</label>
                                    <input id="account_number" name="account_number" class="form-control" inputmode="numeric" maxlength="32" value="{{ old('account_number') }}" required>
                                </div>
                                <div class="form-group mb-0">
                                    <label for="bank_current_password">{{ __('affiliate.payout_accounts.current_password') }}</label>
                                    <input id="bank_current_password" name="current_password" type="password" class="form-control" autocomplete="current-password" required>
                                </div>
                            </div>
                            <div class="card-footer"><button type="submit" class="btn btn-primary">{{ __('affiliate.payout_accounts.save_bank') }}</button></div>
                        </form>
                    </div>
                </div>
            </section>

            <section class="card card-primary affiliate-card" aria-labelledby="saved-payout-accounts-title">
                <div class="card-header"><h2 id="saved-payout-accounts-title" class="card-title">{{ __('affiliate.payout_accounts.saved_accounts') }}</h2></div>
                <div class="card-body">
                    @if ($payout_accounts->isEmpty())
                        <p class="mb-0">{{ __('affiliate.payout_accounts.no_accounts') }}</p>
                    @else
                        <ul class="mb-0">
                            @foreach ($payout_accounts as $payout_account)
                                <li>
                                    {{ $payout_account->payout_type->value }} — {{ $payout_account->masked_label }}
                                    @if ($payout_account->payout_cooldown_until?->isFuture())
                                        <span class="text-warning">{{ __('affiliate.payout_accounts.cooling_period', ['date' => $payout_account->payout_cooldown_until->toDateTimeString()]) }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </section>
        </div>
    </section>
</main>
@endsection

@section('style')
<link rel="stylesheet" href="{{ asset('css/affiliate-portal.css') }}">
@endsection
