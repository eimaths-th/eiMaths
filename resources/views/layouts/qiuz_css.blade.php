<meta charset="utf-8">
<title>eiMaths Quiz | @yield('title')</title>

<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">


<!-- Fonts -->
<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

<link rel="icon" type="image/x-icon" href="{{ asset('images/cropped-footer-logo-32x32.png') }}">
<link rel="shortcut icon" href="{{ asset('images/cropped-footer-logo-32x32.png') }}" type="image/x-icon">
<!-- Styles and Scripts -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@vite(['resources/css/app.css', 'resources/js/app.js'])

{{-- @vite(['resources/css/app.css']) --}}
@livewireStyles
<style>
    .nav-link.active {
        font-weight: bold;
        color: #ff0000; /* Customize the active link color */
    }
</style>

@stack('css')
