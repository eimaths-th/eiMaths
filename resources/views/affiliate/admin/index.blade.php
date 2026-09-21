@extends('layouts.app')

@section('content')
<main class="container py-4" aria-labelledby="affiliate-admin-title">
    <h1 id="affiliate-admin-title">{{ __('affiliate-management.accounts.title') }}</h1>
    <table class="table" aria-describedby="affiliate-admin-title">
        <thead>
            <tr>
                <th scope="col">{{ __('affiliate-management.accounts.columns.user') }}</th>
                <th scope="col">{{ __('affiliate-management.accounts.columns.email') }}</th>
                <th scope="col">{{ __('affiliate-management.accounts.columns.referral_code') }}</th>
                <th scope="col">{{ __('affiliate-management.accounts.columns.identity') }}</th>
                <th scope="col">{{ __('affiliate-management.accounts.columns.status') }}</th>
                <th scope="col">{{ __('affiliate-management.accounts.columns.actions') }}</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($affiliate_profiles as $profile)
            <tr>
                <td>{{ $profile->User?->name }}</td>
                <td>{{ $profile->User?->email }}</td>
                <td><code>{{ $profile->referral_code }}</code></td>
                <td>{{ $profile->IdentityProfile?->status?->value ? __('affiliate-management.accounts.statuses.identity.' . $profile->IdentityProfile->status->value) : __('affiliate-management.accounts.identity_fallback') }} @if ($profile->IdentityProfile?->national_id)<br><small>{{ $profile->IdentityProfile->MaskedNationalId() }}</small>@endif</td>
                <td>{{ __('affiliate-management.accounts.statuses.' . $profile->status->value) }}</td>
                <td>
                    @if ($profile->status->value === 'PENDING_APPROVAL' && $profile->IdentityProfile)
                        @can('affiliate-identity-review')
                            <a class="btn btn-sm btn-primary" href="{{ route('affiliate.admin.identity.show', $profile) }}">{{ __('affiliate-management.accounts.actions.review_identity') }}</a>
                        @endcan
                    @elseif ($profile->status->value === 'PENDING_APPROVAL')
                        <form method="post" action="{{ route('affiliate.admin.approve', $profile) }}" class="d-inline">@csrf<button class="btn btn-sm btn-success" type="submit">{{ __('affiliate-management.accounts.actions.approve') }}</button></form>
                    @endif
                    @if ($profile->status->value === 'ACTIVE')
                        <form method="post" action="{{ route('affiliate.admin.suspend', $profile) }}" class="d-inline">@csrf<input type="hidden" name="reason" value="Admin status change"><button class="btn btn-sm btn-warning" type="submit">{{ __('affiliate-management.accounts.actions.suspend') }}</button></form>
                    @endif
                    @if ($profile->status->value === 'SUSPENDED')
                        <form method="post" action="{{ route('affiliate.admin.reactivate', $profile) }}" class="d-inline">@csrf<button class="btn btn-sm btn-primary" type="submit">{{ __('affiliate-management.accounts.actions.reactivate') }}</button></form>
                    @endif
                    @if (in_array($profile->status->value, ['ACTIVE', 'SUSPENDED'], true))
                        <form method="post" action="{{ route('affiliate.admin.deactivate', $profile) }}" class="d-inline">@csrf<input type="hidden" name="reason" value="Admin status change"><button class="btn btn-sm btn-danger" type="submit">{{ __('affiliate-management.accounts.actions.deactivate') }}</button></form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="6">{{ __('affiliate-management.accounts.no_profiles') }}</td></tr>
        @endforelse
        </tbody>
    </table>
    {{ $affiliate_profiles->links() }}
</main>
@endsection
