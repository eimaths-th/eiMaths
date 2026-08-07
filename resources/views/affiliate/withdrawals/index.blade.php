@extends('layouts.app')

@section('content')
<main class="container py-4" aria-labelledby="affiliate-withdrawals-title">
    <h1 id="affiliate-withdrawals-title">Withdrawals</h1>
    <p><a href="{{ route('affiliate.dashboard') }}">Affiliate dashboard</a> · <a href="{{ route('affiliate.payout-accounts.index') }}">Payout accounts</a></p>

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

    <section class="row mb-4" aria-label="Wallet summary">
        <div class="col-md-6 mb-3 mb-md-0">
            <div class="card card-body h-100">
                <h2 class="h5 mb-1">Available balance</h2>
                <p class="h3 mb-0">{{ number_format($wallet->available_balance_minor / 100, 2) }}</p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-body h-100">
                <h2 class="h5 mb-1">Reserved balance</h2>
                <p class="h3 mb-0">{{ number_format($wallet->reserved_balance_minor / 100, 2) }}</p>
            </div>
        </div>
    </section>

    <section aria-labelledby="withdrawal-request-title">
        <h2 id="withdrawal-request-title">Request withdrawal</h2>
        <p>Minimum withdrawal: {{ number_format($minimum_withdrawal_minor / 100, 2) }}</p>
        @if ($payout_accounts->isEmpty())
            <p>Save a payout account before requesting a withdrawal.</p>
        @else
            <form method="POST" action="{{ route('affiliate.withdrawals.store') }}" class="card card-body">
                @csrf
                <input type="hidden" name="request_key" value="{{ (string) \Illuminate\Support\Str::uuid() }}">
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="withdrawal_amount">Amount</label>
                        <input id="withdrawal_amount" name="amount" type="number" class="form-control" min="{{ number_format($minimum_withdrawal_minor / 100, 2, '.', '') }}" step="0.01" value="{{ old('amount') }}" required>
                    </div>
                    <div class="form-group col-md-5">
                        <label for="payout_account_id">Payout account</label>
                        <select id="payout_account_id" name="payout_account_id" class="form-control" required>
                            @foreach ($payout_accounts as $payout_account)
                                <option value="{{ $payout_account->id }}" @selected((string) old('payout_account_id') === (string) $payout_account->id)>{{ $payout_account->payout_type->value }} — {{ $payout_account->masked_label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-success w-100">Request withdrawal</button>
                    </div>
                </div>
            </form>
        @endif
    </section>

    <section class="mt-4" aria-labelledby="withdrawal-history-title">
        <h2 id="withdrawal-history-title">Withdrawal history</h2>
        <table class="table" aria-describedby="withdrawal-history-title">
            <thead><tr><th scope="col">ID</th><th scope="col">Status</th><th scope="col">Amount</th><th scope="col">Requested at</th><th scope="col">Action</th></tr></thead>
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
                                <input type="hidden" name="transition_key" value="{{ (string) \Illuminate\Support\Str::uuid() }}">
                                <input type="hidden" name="reason" value="Cancelled by affiliate">
                                <button type="submit" class="btn btn-sm btn-outline-danger">Cancel</button>
                            </form>
                        @else
                            <span aria-hidden="true">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5">No withdrawals yet.</td></tr>
            @endforelse
            </tbody>
        </table>
        {{ $withdrawals->links() }}
    </section>
</main>
@endsection
