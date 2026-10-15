@php
    $show_employee_affiliate_entry = false;
    $header_user = auth()->user();

    if ($header_user && (bool) config('affiliate.features.employee_enrollment', false)) {
        $employee_affiliate_eligibility = app(\App\Domain\Affiliate\Services\AffiliateEmployeeEligibility::class);
        $show_employee_affiliate_entry = $employee_affiliate_eligibility->IsEligible($header_user);
    }

    $centre_name = $header_user && $header_user->department && $header_user->department->name
        ? $header_user->department->name
        : '—';
    $department_name = $header_user && $header_user->position && $header_user->position->name
        ? $header_user->position->name
        : '—';
    $user_name = $header_user && $header_user->name ? $header_user->name : 'User';
    $user_initial = mb_strtoupper(mb_substr(trim($user_name), 0, 1, 'UTF-8'), 'UTF-8');
    $member_since = $header_user && $header_user->created_at
        ? $header_user->created_at->format('M. Y')
        : '—';
@endphp

<nav class="main-header navbar navbar-expand navbar-white navbar-light app-topbar"
    aria-label="{{ __('navigation.header.main_navigation') }}">
    <ul class="navbar-nav app-topbar__left">
        <li class="nav-item">
            <a class="nav-link sidebar-toggle-btn app-topbar__icon-button" data-widget="pushmenu" href="#" role="button"
                aria-label="{{ __('navigation.header.toggle_main_menu') }}"
                title="{{ __('navigation.header.toggle_main_menu') }}">
                <i class="fas fa-bars" aria-hidden="true"></i>
            </a>
        </li>
        <li class="nav-item app-context-strip d-none d-md-flex"
            aria-label="{{ __('navigation.header.current_work_context') }}">
            <span class="app-context-chip">
                <span class="app-context-chip__icon" aria-hidden="true"><i class="fas fa-building"></i></span>
                <span class="app-context-chip__copy">
                    <span class="app-context-chip__label">{{ __('common.fields.centre') }}</span>
                    <strong class="app-context-chip__value" title="{{ $centre_name }}">{{ $centre_name }}</strong>
                </span>
            </span>
            <span class="app-context-chip">
                <span class="app-context-chip__icon" aria-hidden="true"><i class="fas fa-users"></i></span>
                <span class="app-context-chip__copy">
                    <span class="app-context-chip__label">{{ __('common.fields.department') }}</span>
                    <strong class="app-context-chip__value" title="{{ $department_name }}">{{ $department_name }}</strong>
                </span>
            </span>
        </li>
    </ul>

    <ul class="navbar-nav ml-auto app-topbar__right">
        @if (isset($pending_work_alert_enabled) && $pending_work_alert_enabled)
            @include('partials.pending-work-alert', [
                'authenticated_user' => $authenticated_user,
            ])
        @endif
        @include('layouts.partials.language-switcher')
        <li class="nav-item d-none d-md-flex">
            <a class="nav-link app-topbar__action" data-widget="fullscreen" href="#" role="button"
                aria-label="{{ __('navigation.header.full_screen') }}"
                title="{{ __('navigation.header.full_screen') }}">
                <i class="fas fa-expand" aria-hidden="true"></i>
                <span class="d-none d-xl-inline">{{ __('navigation.header.full_screen') }}</span>
            </a>
        </li>
        <li class="nav-item dropdown user-menu app-user-menu">
            <a href="#" class="nav-link dropdown-toggle app-user-trigger" data-toggle="dropdown" aria-haspopup="true"
                aria-expanded="false"
                aria-label="{{ __('navigation.header.user_menu', ['name' => $user_name]) }}">
                <span class="app-user-avatar" aria-hidden="true">{{ $user_initial }}</span>
                <span class="app-user-trigger__copy d-none d-lg-flex">
                    <strong>{{ $user_name }}</strong>
                    <small>{{ $department_name }}</small>
                </span>
                <i class="fas fa-chevron-down app-user-trigger__chevron" aria-hidden="true"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-right app-user-dropdown">
                <li class="app-user-dropdown__summary">
                    <span class="app-user-avatar app-user-avatar--large" aria-hidden="true">{{ $user_initial }}</span>
                    <span class="app-user-dropdown__identity">
                        <strong>{{ $user_name }}</strong>
                        <small>{{ $department_name }}</small>
                        <span>{{ __('navigation.header.member_since', ['date' => $member_since]) }}</span>
                    </span>
                </li>
                @if ($show_employee_affiliate_entry)
                    <li>
                        <a class="dropdown-item app-user-dropdown__link" href="{{ route('affiliate.employee-access.show') }}">
                            <i class="fas fa-handshake" aria-hidden="true"></i>
                            {{ __('affiliate.employee_access.menu_label') }}
                        </a>
                    </li>
                @endif
                <li class="dropdown-divider"></li>
                <li class="app-user-dropdown__actions">
                    <a href="{{ route('profile.edit') }}" class="btn btn-light btn-sm">
                        <i class="far fa-user mr-1" aria-hidden="true"></i>{{ __('navigation.header.profile') }}
                    </a>
                    <a href="#" class="btn btn-outline-danger btn-sm" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="fas fa-sign-out-alt mr-1" aria-hidden="true"></i>{{ __('navigation.header.sign_out') }}
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </li>
            </ul>
        </li>
    </ul>
</nav>
