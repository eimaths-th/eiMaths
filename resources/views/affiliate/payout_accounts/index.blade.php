@extends('layouts.app')

@section('content')
<main class="container py-4" aria-labelledby="affiliate-payout-accounts-title">
    <h1 id="affiliate-payout-accounts-title">Payout accounts</h1>
    <p><a href="{{ route('affiliate.dashboard') }}">Affiliate dashboard</a> · <a href="{{ route('affiliate.withdrawals.index') }}">Withdrawals</a></p>

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
        <h2 id="payout-account-forms-title">Add or update a payout account</h2>
        <div class="row">
            <div class="col-lg-6 mb-3">
                <form method="POST" action="{{ route('affiliate.payout-accounts.promptpay') }}" class="card card-body h-100">
                    @csrf
                    @method('PUT')
                    <h3 class="h5">PromptPay</h3>
                    <label for="promptpay_identifier">PromptPay number</label>
                    <input id="promptpay_identifier" name="promptpay_identifier" class="form-control" inputmode="numeric" maxlength="16" placeholder="0812345678" value="{{ old('promptpay_identifier') }}" required>
                    <label for="promptpay_current_password" class="mt-2">Current password</label>
                    <input id="promptpay_current_password" name="current_password" type="password" class="form-control" autocomplete="current-password" required>
                    <button type="submit" class="btn btn-primary mt-3">Save PromptPay</button>
                </form>
            </div>
            <div class="col-lg-6 mb-3">
                <form method="POST" action="{{ route('affiliate.payout-accounts.bank') }}" class="card card-body h-100">
                    @csrf
                    @method('PUT')
                    <h3 class="h5">Bank account</h3>
                    <label for="bank_name">Bank name</label>
                    <input id="bank_name" name="bank_name" class="form-control" maxlength="255" value="{{ old('bank_name') }}" required>
                    <label for="account_name" class="mt-2">Account name</label>
                    <input id="account_name" name="account_name" class="form-control" maxlength="255" value="{{ old('account_name') }}" required>
                    <label for="account_number" class="mt-2">Account number</label>
                    <input id="account_number" name="account_number" class="form-control" inputmode="numeric" maxlength="32" value="{{ old('account_number') }}" required>
                    <label for="bank_current_password" class="mt-2">Current password</label>
                    <input id="bank_current_password" name="current_password" type="password" class="form-control" autocomplete="current-password" required>
                    <button type="submit" class="btn btn-primary mt-3">Save bank account</button>
                </form>
            </div>
        </div>
    </section>

    <section class="mt-4" aria-labelledby="saved-payout-accounts-title">
        <h2 id="saved-payout-accounts-title">Saved payout accounts</h2>
        @if ($payout_accounts->isEmpty())
            <p>No payout accounts saved yet.</p>
        @else
            <ul>
                @foreach ($payout_accounts as $payout_account)
                    <li>
                        {{ $payout_account->payout_type->value }} — {{ $payout_account->masked_label }}
                        @if ($payout_account->payout_cooldown_until?->isFuture())
                            <span class="text-warning">(Withdrawal cooling period until {{ $payout_account->payout_cooldown_until->toDateTimeString() }})</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</main>
@endsection
