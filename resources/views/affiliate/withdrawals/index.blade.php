@extends('layouts.app')

@section('content')
<main class="affiliate-portal" aria-labelledby="affiliate-withdrawals-title">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-8">
                    <h1 id="affiliate-withdrawals-title" class="mb-0">{{ __('affiliate.withdrawals.title') }}</h1>
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

            <section class="mb-4" aria-label="{{ __('affiliate.withdrawals.wallet_summary') }}">
                <div class="row">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <div class="card card-primary affiliate-card h-100">
                            <div class="card-header"><h2 class="card-title">{{ __('affiliate.withdrawals.available_balance') }}</h2></div>
                            <div class="card-body"><p class="h3 mb-0">{{ number_format($wallet->available_balance_minor / 100, 2) }}</p></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card card-primary affiliate-card h-100">
                            <div class="card-header"><h2 class="card-title">{{ __('affiliate.withdrawals.reserved_balance') }}</h2></div>
                            <div class="card-body"><p class="h3 mb-0">{{ number_format($wallet->reserved_balance_minor / 100, 2) }}</p></div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="card card-primary affiliate-card mb-4" aria-labelledby="withdrawal-request-title">
                <div class="card-header"><h2 id="withdrawal-request-title" class="card-title">{{ __('affiliate.withdrawals.request_title') }}</h2></div>
                <div class="card-body">
                    <p>{{ __('affiliate.withdrawals.minimum', ['amount' => number_format($minimum_withdrawal_minor / 100, 2)]) }}</p>
                    @if ($payout_accounts->isEmpty())
                        <p class="mb-0">{{ __('affiliate.withdrawals.save_account_first') }}</p>
                    @else
                        <form method="POST" action="{{ route('affiliate.withdrawals.store') }}">
                            @csrf
                            <input type="hidden" name="request_key" value="{{ (string) str()->uuid() }}">
                            <div class="form-row align-items-end">
                                <div class="form-group col-md-4">
                                    <label for="withdrawal_amount">{{ __('affiliate.withdrawals.amount') }}</label>
                                    <input id="withdrawal_amount" name="amount" type="number" class="form-control" min="{{ number_format($minimum_withdrawal_minor / 100, 2, '.', '') }}" step="0.01" value="{{ old('amount') }}" required>
                                </div>
                                <div class="form-group col-md-5">
                                    <label for="payout_account_id">{{ __('affiliate.withdrawals.payout_account') }}</label>
                                    <select id="payout_account_id" name="payout_account_id" class="custom-select" required>
                                        @foreach ($payout_accounts as $payout_account)
                                            <option value="{{ $payout_account->id }}" @selected((string) old('payout_account_id') === (string) $payout_account->id)>{{ $payout_account->payout_type->value }} — {{ $payout_account->masked_label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-3 mb-0">
                                    <button type="submit" class="btn btn-success btn-block">{{ __('affiliate.withdrawals.request_action') }}</button>
                                </div>
                            </div>
                        </form>
                    @endif
                </div>
            </section>

            <section class="card card-primary affiliate-card" aria-labelledby="withdrawal-history-title">
                <div class="card-header"><h2 id="withdrawal-history-title" class="card-title">{{ __('affiliate.withdrawals.history') }}</h2></div>
                <div class="card-body p-0">
                    <div class="affiliate-table-scroll">
                        <table class="table table-bordered table-striped table-hover mb-0" aria-describedby="withdrawal-history-title">
                            <thead><tr><th scope="col">{{ __('affiliate.withdrawals.id') }}</th><th scope="col">{{ __('affiliate.withdrawals.status') }}</th><th scope="col">{{ __('affiliate.withdrawals.amount') }}</th><th scope="col">{{ __('affiliate.withdrawals.requested_at') }}</th><th scope="col">{{ __('affiliate.withdrawals.action') }}</th></tr></thead>
                            <tbody>
                            @forelse ($withdrawals as $withdrawal)
                                <tr>
                                    <td>{{ $withdrawal->id }}</td>
                                    <td>{{ $withdrawal->status->value }}</td>
                                    <td>{{ number_format($withdrawal->amount_minor / 100, 2) }}</td>
                                    <td>{{ optional($withdrawal->requested_at)->toDateTimeString() }}</td>
                                    <td>
                                        @if (in_array($withdrawal->status->value, ['REQUESTED', 'APPROVED'], true))
                                            <form method="POST" action="{{ route('affiliate.withdrawals.cancel', $withdrawal) }}">
                                                @csrf
                                                <input type="hidden" name="transition_key" value="{{ (string) str()->uuid() }}">
                                                <input type="hidden" name="reason" value="Cancelled by affiliate">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('affiliate.withdrawals.cancel') }}</button>
                                            </form>
                                        @else
                                            <span aria-hidden="true">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5">{{ __('affiliate.withdrawals.no_history') }}</td></tr>
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
<link rel="stylesheet" href="{{ asset('css/affiliate-portal.css') }}">
@endsection
