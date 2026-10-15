<style>
    /* Hide the large logo & text perfectly synced with AdminLTE slide transition */
    body.sidebar-collapse:not(.sidebar-open) .main-sidebar:not(:hover):not(.sidebar-focused) #logo,
    body.sidebar-collapse:not(.sidebar-open) .main-sidebar:not(:hover):not(.sidebar-focused) .brand-text {
        display: none !important;
    }

    .main-sidebar .nav-sidebar .nav-link {
        display: flex;
        align-items: center;
        max-width: 100%;
    }

    .main-sidebar .nav-sidebar .nav-link > .nav-icon {
        flex: 0 0 1.6rem;
    }

    .main-sidebar .nav-sidebar .nav-link > p,
    .sidebar-mini.sidebar-collapse .main-sidebar:hover .nav-sidebar .nav-link > p,
    .sidebar-mini.sidebar-collapse .main-sidebar.sidebar-focused .nav-sidebar .nav-link > p {
        display: flex;
        align-items: center;
        flex: 1 1 0%;
        min-width: 0;
    }

    .main-sidebar .nav-sidebar .nav-menu-label {
        display: block;
        flex: 1 1 0%;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* Keep arrows and live counts outside the clipped menu label. */
    .main-sidebar .nav-sidebar .nav-link > .right,
    .main-sidebar .nav-sidebar .nav-link > p > .sidebar-menu-badge {
        position: static;
        flex: 0 0 auto;
        margin-left: .45rem;
    }

    .main-sidebar .nav-sidebar .nav-treeview > .nav-item > .nav-link {
        padding-left: 2.25rem;
    }

    /* Restore centred icons while the desktop sidebar is collapsed. */
    @media (min-width: 992px) {
        .sidebar-mini.sidebar-collapse:not(.sidebar-open) .main-sidebar:not(:hover):not(.sidebar-focused) .nav-treeview > .nav-item > .nav-link {
            padding-left: .75rem;
        }

        .sidebar-mini.sidebar-collapse:not(.sidebar-open) .main-sidebar:not(:hover):not(.sidebar-focused) .nav-link > p,
        .sidebar-mini.sidebar-collapse:not(.sidebar-open) .main-sidebar:not(:hover):not(.sidebar-focused) .nav-link > .right {
            display: none;
        }
    }
</style>
<aside class="main-sidebar sidebar-light-primary elevation-4">
    <div class="center-content text-center py-2">
        <a href="{{ route('home') }}" class="brand-link" style="border-bottom: none;">
            <img id="logo" src="{{ asset('images/logo.png') }}" alt="eiMaths" height="100" style="max-width: 100%;"><br>
            <span class="brand-text font-weight-bold text-dark" style="font-size: 1.2rem;">{{ config('app.name') }}</span>
        </a>
    </div>

    <div class="sidebar" style="overflow-y: auto; height: calc(100vh - 150px);">
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                data-accordion="false">
                @include('layouts.menu')
            </ul>
        </nav>
    </div>

</aside>
