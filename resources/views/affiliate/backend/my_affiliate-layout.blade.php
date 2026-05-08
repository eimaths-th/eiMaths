<!DOCTYPE html>
<html lang="en">
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
        <!-- Sidebar  -->
        @include('affiliate.backend.my_affiliate-sidebar')

        <!-- Page Content  -->
        <div id="main-content" class="">
            <!-- Header -->
            @include('affiliate.backend.my_affiliate-header')
            
            @yield('content')
        </div>
    </div>

    @include('affiliate.backend.my_affiliate-script')
    @yield('script')
</body>
</html>
