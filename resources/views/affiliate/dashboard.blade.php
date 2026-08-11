@extends('layouts.app')

@section('content')
<main class="container py-4" aria-labelledby="affiliate-dashboard-title">
    <h1 id="affiliate-dashboard-title">Affiliate dashboard</h1>
    <p>Referral code: <code>{{ $profile->referral_code }}</code></p>
    @include('affiliate.partials.navigation')
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

    <section class="row" aria-label="Wallet summary">
        <div class="col-md-6">
            <h2>Available balance</h2>
            <p>{{ number_format($wallet->available_balance_minor / 100, 2) }}</p>
        </div>
        <div class="col-md-6">
            <h2>Reserved balance</h2>
            <p>{{ number_format($wallet->reserved_balance_minor / 100, 2) }}</p>
        </div>
    </section>

    <section class="mt-4" aria-labelledby="commission-history-title">
        <h2 id="commission-history-title">Commission history</h2>
        <table class="table" aria-describedby="commission-history-title">
            <thead><tr><th scope="col">ID</th><th scope="col">Status</th><th scope="col">Amount</th><th scope="col">Release at</th></tr></thead>
            <tbody>
            @forelse ($commissions as $commission)
                <tr><td>{{ $commission->id }}</td><td>{{ $commission->status->value }}</td><td>{{ number_format($commission->commission_minor / 100, 2) }}</td><td>{{ optional($commission->release_at)->toDateTimeString() }}</td></tr>
            @empty
                <tr><td colspan="4">No commissions yet.</td></tr>
            @endforelse
            </tbody>
        </table>
        {{ $commissions->links() }}
    </section>

    <section class="mt-4" aria-labelledby="withdrawal-history-title">
        <h2 id="withdrawal-history-title">Withdrawal history</h2>
        <p><a href="{{ route('affiliate.withdrawals.index') }}">Open withdrawals</a></p>
        <table class="table" aria-describedby="withdrawal-history-title">
            <thead><tr><th scope="col">ID</th><th scope="col">Status</th><th scope="col">Amount</th><th scope="col">Requested at</th><th scope="col">Details</th></tr></thead>
            <tbody>
            @forelse ($withdrawals as $withdrawal)
                <tr>
                    <td>{{ $withdrawal->id }}</td>
                    <td>{{ $withdrawal->status->value }}</td>
                    <td>{{ number_format($withdrawal->amount_minor / 100, 2) }}</td>
                    <td>{{ optional($withdrawal->requested_at)->toDateTimeString() }}</td>
                    <td><a href="{{ route('affiliate.withdrawals.index') }}">View withdrawals</a></td>
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
