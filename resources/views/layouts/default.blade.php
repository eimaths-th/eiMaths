<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8" />
    <title>eiMaths | @yield('title')</title>
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport" />
    <meta content="" name="description" />
    <meta content="" name="author" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- ================== BEGIN BASE CSS STYLE ================== -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <link href="/assets/css/vendor.min.css" rel="stylesheet" />
    <link href="/assets/css/app.min.css" rel="stylesheet" />
    {{-- <link href="/assets/plugins/nvd3/build/nv.d3.css" rel="stylesheet" />
    <link href="../assets/plugins/nvd3/build/nv.d3.css" rel="stylesheet" /> --}}
    <link rel="icon" href="{{ asset('images/logo-eimaths.png') }}" type="image/x-icon">
    <link href="../assets/css/blog/vendor.min.css" rel="stylesheet" />
    <link href="../assets/css/blog/app.min.css" rel="stylesheet" />
    <!-- ================== END BASE CSS STYLE ================== -->
    @stack('css')

</head>
@php
    $bodyClass = !empty($appBoxedLayout) ? 'boxed-layout ' : '';
    $bodyClass .= !empty($paceTop) ? 'pace-top ' : $bodyClass;
    $bodyClass .= !empty($bodyClass) ? $bodyClass . ' ' : $bodyClass;
    $appSidebarHide = !empty($appSidebarHide) ? $appSidebarHide : '';
    $appHeaderHide = !empty($appHeaderHide) ? $appHeaderHide : '';
    $appSidebarTwo = !empty($appSidebarTwo) ? $appSidebarTwo : '';
    $appSidebarSearch = !empty($appSidebarSearch) ? $appSidebarSearch : '';
    $appTopMenu = !empty($appTopMenu) ? $appTopMenu : '';

    $appClass = !empty($appTopMenu) ? 'app-with-top-menu ' : '';
    $appClass .= !empty($appHeaderHide) ? 'app-without-header ' : ' app-header-fixed ';
    $appClass .= !empty($appSidebarEnd) ? 'app-with-end-sidebar ' : '';
    $appClass .= !empty($appSidebarLight) ? 'app-with-light-sidebar ' : '';
    $appClass .= !empty($appSidebarWide) ? 'app-with-wide-sidebar ' : '';
    $appClass .= !empty($appSidebarHide) ? 'app-without-sidebar ' : '';
    $appClass .= !empty($appSidebarMinified) ? 'app-sidebar-minified ' : '';
    $appClass .= !empty($appSidebarTwo) ? 'app-with-two-sidebar app-sidebar-end-toggled ' : '';
    $appClass .= !empty($appContentFullHeight) ? 'app-content-full-height ' : '';

    $appContentClass = !empty($appContentClass) ? $appContentClass : '';
@endphp

{{-- <body> --}}

<body class="{{ $bodyClass }}">
    <div id="app" class="app app-sidebar-fixed {{ $appClass }}">
        <div id="content" class="app-content {{ $appContentClass }}">
            @yield('content')
        </div>
    </div>


    @yield('outside-content')

    <!-- ================== BEGIN core-js ================== -->
    {{-- <script src="/assets/js/vendor.min.js"></script> --}}
    {{-- <script src="/assets/js/app.min.js"></script> --}}
    <!-- ================== END core-js ================== -->


    @stack('scripts')


</body>

</html>
