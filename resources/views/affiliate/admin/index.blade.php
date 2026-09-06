@extends('layouts.app')

@section('content')
<main class="container py-4" aria-labelledby="affiliate-admin-title">
    <h1 id="affiliate-admin-title">Affiliate account administration</h1>
    <table class="table" aria-describedby="affiliate-admin-title">
        <thead><tr><th scope="col">User</th><th scope="col">Email</th><th scope="col">Referral code</th><th scope="col">Identity</th><th scope="col">Status</th><th scope="col">Actions</th></tr></thead>
        <tbody>
        @forelse ($affiliate_profiles as $profile)
            <tr>
                <td>{{ $profile->User?->name }}</td>
                <td>{{ $profile->User?->email }}</td>
                <td><code>{{ $profile->referral_code }}</code></td>
                <td>{{ $profile->IdentityProfile?->status?->value ?? 'EMPLOYEE' }} @if ($profile->IdentityProfile?->national_id)<br><small>{{ $profile->IdentityProfile->MaskedNationalId() }}</small>@endif</td>
                <td>{{ $profile->status->value }}</td>
                <td>
                    @if ($profile->status->value === 'PENDING_APPROVAL' && $profile->IdentityProfile)
                        @can('affiliate-identity-review')
                            <a class="btn btn-sm btn-primary" href="{{ route('affiliate.admin.identity.show', $profile) }}">Review identity</a>
                        @endcan
                    @elseif ($profile->status->value === 'PENDING_APPROVAL')
                        <form method="post" action="{{ route('affiliate.admin.approve', $profile) }}" class="d-inline">@csrf<button class="btn btn-sm btn-success" type="submit">Approve</button></form>
                    @endif
                    @if ($profile->status->value === 'ACTIVE')
                        <form method="post" action="{{ route('affiliate.admin.suspend', $profile) }}" class="d-inline">@csrf<input type="hidden" name="reason" value="Admin status change"><button class="btn btn-sm btn-warning" type="submit">Suspend</button></form>
                    @endif
                    @if ($profile->status->value === 'SUSPENDED')
                        <form method="post" action="{{ route('affiliate.admin.reactivate', $profile) }}" class="d-inline">@csrf<button class="btn btn-sm btn-primary" type="submit">Reactivate</button></form>
                    @endif
                    @if (in_array($profile->status->value, ['ACTIVE', 'SUSPENDED'], true))
                        <form method="post" action="{{ route('affiliate.admin.deactivate', $profile) }}" class="d-inline">@csrf<input type="hidden" name="reason" value="Admin status change"><button class="btn btn-sm btn-danger" type="submit">Deactivate</button></form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="6">No Affiliate profiles found.</td></tr>
        @endforelse
        </tbody>
    </table>
    {{ $affiliate_profiles->links() }}
</main>
@endsection
