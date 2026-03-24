<style>
    /* Hide the large logo & text perfectly synced with AdminLTE slide transition */
    body.sidebar-collapse:not(.sidebar-open) #logo,
    body.sidebar-collapse:not(.sidebar-open) .brand-text {
        display: none !important;
    }
</style>
<aside class="main-sidebar sidebar-light-primary elevation-4">
    <div class="center-content text-center py-2">
        <a href="{{ route('home') }}" class="brand-link" style="border-bottom: none;">
            <img id="logo" src="{{ asset('images/logo.png') }}" alt="..." height="100" style="max-width: 100%;"><br>
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
