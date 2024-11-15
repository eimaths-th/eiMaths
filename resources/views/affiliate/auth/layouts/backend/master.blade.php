<!DOCTYPE html>
<html dir="{{ isRtl() ? 'rtl' : 'ltr' }}" class="{{ isRtl() ? 'rtl' : 'ltr' }} {{ auth()->check() && auth()->user()->dark_mode == 1 ? 'dark' : 'light' }}"
    lang="{{ app()->getLocale() }}">

<head>
    {{-- @include('backend.partials.header') --}}
    @include('affiliate.auth.layouts.backend.partials.header')

</head>

<body class="admin">
    {{-- @include('preloader') --}}
    <input type="hidden" name="demoMode" id="demoMode" value="{{ appMode() }}">
    <input type="hidden" name="url" id="url" value="{{ URL::to('/') }}">
    {{-- <input type="hidden" name="active_date_format" id="active_date_format" value="{{ Settings('active_date_format') }}"> --}}
    {{-- <input type="hidden" name="js_active_date_format" id="js_active_date_format" value="{{ getActiveJsDateFormat() }}"> --}}
    {{-- <input type="hidden" name="table_name" id="table_name" value="@yield('table')"> --}}
    <input type="hidden" name="csrf_token" class="csrf_token" value="{{ csrf_token() }}">
    {{-- <input type="hidden" name="currency_symbol" class="currency_symbol" value="{{ Settings('currency_symbol') }}">
    <input type="hidden" name="currency_show" class="currency_show" value="{{ Settings('currency_show') }}"> --}}
    {{-- <input type="hidden" name="chat_settings" id="chat_settings" value="{{ env('BROADCAST_DRIVER') }}"> --}}
    <div class="main-wrapper" style="min-height: 600px">
        <!-- Sidebar  -->
        @if (isModuleActive('LmsSaas') && Auth::user()->is_saas_admin == 1 && Auth::user()->active_panel == 'saas')
            @include('lmssaas::partials.sidebar')
        @elseif(isModuleActive('LmsSaasMD') && Auth::user()->is_saas_admin == 1 && Auth::user()->active_panel == 'saas')
            @include('lmssaasmd::partials.sidebar')
        @else
            {{-- @include('backend.partials.sidebar') --}}
            @include('affiliate.auth.layouts.backend.partials.sidebar')
            {{-- @include('lmssaasmd::partials.sidebar') --}}

        @endif


        <!-- Page Content  -->
        <div id="main-content"
            class="{{ auth()->check() && auth()->user()->sidebar == 1 ? '' : 'top-padding full-width' }}">
            {{-- @include('secret_login') --}}

            {{-- @include('backend.partials.menu') --}}
            {{-- @include('affiliate.auth.layouts.backend.partials.menu') --}}

            @yield('mainContent')

            <footer class="footer-area">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12 text-center mt-5 footer-copyright">
                            <p class="p-3 mb-0">
                                copyright_text
                                {{-- {!! Settings('copyright_text') !!} --}}
                            </p>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    {{-- @include('backend.partials.footer') --}}
    @include('affiliate.auth.layouts.backend.partials.footer')

</body>

</html>
