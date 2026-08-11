@php
    $affiliate_profile = auth()->user()?->AffiliateProfile;
    $affiliate_navigation_visible = auth()->check()
        && auth()->user()->hasVerifiedEmail()
        && $affiliate_profile?->status?->value === 'ACTIVE';
@endphp

@if ($affiliate_navigation_visible)
    <nav class="affiliate-navigation" aria-label="Affiliate navigation">
        @if ((bool) config('affiliate.features.wallet_withdrawals', false))
            <a href="{{ route('affiliate.dashboard') }}" class="{{ request()->routeIs('affiliate.dashboard') ? 'active' : '' }}">Dashboard</a>
            <a href="{{ route('affiliate.payout-accounts.index') }}" class="{{ request()->routeIs('affiliate.payout-accounts.*') ? 'active' : '' }}">Payout accounts</a>
            <a href="{{ route('affiliate.withdrawals.index') }}" class="{{ request()->routeIs('affiliate.withdrawals.*') ? 'active' : '' }}">Withdrawals</a>
        @endif
        @if ((bool) config('affiliate.features.analytics', false))
            <a href="{{ route('affiliate.analytics.index') }}" class="{{ request()->routeIs('affiliate.analytics.*') ? 'active' : '' }}">Analytics</a>
        @endif
        @if ((bool) config('affiliate.features.media_library', false))
            <a href="{{ route('affiliate.media.index') }}" class="{{ request()->routeIs('affiliate.media.*') ? 'active' : '' }}">Media</a>
        @endif
    </nav>
@endif
