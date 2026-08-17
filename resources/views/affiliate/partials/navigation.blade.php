@php
    $affiliate_navigation_user = auth()->user();
    $affiliate_portal_access_service = app(\App\Domain\Affiliate\Services\AffiliatePortalAccess::class);
    $affiliate_navigation_visible = $affiliate_navigation_user !== null
        && $affiliate_portal_access_service->CanAccess($affiliate_navigation_user);
@endphp

@if ($affiliate_navigation_visible)
    <nav class="affiliate-navigation nav nav-pills flex-wrap mb-4" aria-label="{{ __('affiliate.navigation.label') }}">
        @if ((bool) config('affiliate.features.wallet_withdrawals', false))
            <a href="{{ route('affiliate.dashboard') }}" class="nav-link {{ request()->routeIs('affiliate.dashboard') ? 'active' : '' }}" @if (request()->routeIs('affiliate.dashboard')) aria-current="page" @endif>
                <i class="fas fa-chart-line mr-1" aria-hidden="true"></i>{{ __('affiliate.navigation.dashboard') }}
            </a>
            <a href="{{ route('affiliate.payout-accounts.index') }}" class="nav-link {{ request()->routeIs('affiliate.payout-accounts.*') ? 'active' : '' }}" @if (request()->routeIs('affiliate.payout-accounts.*')) aria-current="page" @endif>
                <i class="fas fa-credit-card mr-1" aria-hidden="true"></i>{{ __('affiliate.navigation.payout_accounts') }}
            </a>
            <a href="{{ route('affiliate.withdrawals.index') }}" class="nav-link {{ request()->routeIs('affiliate.withdrawals.*') ? 'active' : '' }}" @if (request()->routeIs('affiliate.withdrawals.*')) aria-current="page" @endif>
                <i class="fas fa-money-bill-wave mr-1" aria-hidden="true"></i>{{ __('affiliate.navigation.withdrawals') }}
            </a>
        @endif
        @if ((bool) config('affiliate.features.analytics', false))
            <a href="{{ route('affiliate.analytics.index') }}" class="nav-link {{ request()->routeIs('affiliate.analytics.*') ? 'active' : '' }}" @if (request()->routeIs('affiliate.analytics.*')) aria-current="page" @endif>
                <i class="fas fa-chart-pie mr-1" aria-hidden="true"></i>{{ __('affiliate.navigation.analytics') }}
            </a>
        @endif
        @if ((bool) config('affiliate.features.media_library', false))
            <a href="{{ route('affiliate.media.index') }}" class="nav-link {{ request()->routeIs('affiliate.media.*') ? 'active' : '' }}" @if (request()->routeIs('affiliate.media.*')) aria-current="page" @endif>
                <i class="fas fa-photo-video mr-1" aria-hidden="true"></i>{{ __('affiliate.navigation.media') }}
            </a>
        @endif
    </nav>
@endif
