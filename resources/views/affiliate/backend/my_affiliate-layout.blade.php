<!DOCTYPE html>
<html dir="{{ isRtl() ? 'rtl' : 'ltr' }}" class="{{ isRtl() ? 'rtl' : 'ltr' }} dark"
    lang="{{ app()->getLocale() }}">
<head>
    @include('affiliate.backend.my_affiliate-head')
    @yield('style')
</head>
<body class="admin">
    @php
        $user = \Illuminate\Support\Facades\Auth::user();
    @endphp
    
    <input type="hidden" name="url" id="url" value="{{ url('/') }}">
    <input type="hidden" name="csrf_token" class="csrf_token" value="{{ csrf_token() }}">
    
    <div class="main-wrapper" style="min-height: 600px">
        <!-- Sidebar -->
        @include('affiliate.backend.my_affiliate-sidebar')

        <!-- Page Content -->
        <div id="main-content" class="">
            <!-- Header -->
            @include('affiliate.backend.my_affiliate-header')
            
            @yield('content')
        </div>
    </div>

    {{-- โหลด Script หลักของระบบ (jQuery, DataTable, FilePond ฯลฯ) --}}
    @include('affiliate.backend.my_affiliate-script')

    {{-- Script สลับ Dark/Light Mode --}}
    <script>
        function ApplyTheme(theme) {
            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
                document.documentElement.classList.remove('light');
            } else {
                document.documentElement.classList.add('light');
                document.documentElement.classList.remove('dark');
            }
            localStorage.setItem('affiliate_theme', theme);
        }

        function ToggleTheme() {
            var is_dark = document.documentElement.classList.contains('dark');
            ApplyTheme(is_dark ? 'light' : 'dark');
        }

        // โหลด Theme จาก LocalStorage (Default = dark)
        (function() {
            var saved = localStorage.getItem('affiliate_theme') || 'dark';
            ApplyTheme(saved);
        })();
    </script>

    @yield('script')
</body>
</html>
