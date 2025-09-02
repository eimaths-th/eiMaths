<!DOCTYPE html>
<html dir="" class="" lang="en" itemscope itemtype="{{ url('/') }}">

<head>
    @include('affiliate.frontend.head')
</head>

<body>

    <script>
        window.Laravel = {
            "baseUrl": '{{ url('/') }}' + '/',
            "current_path_without_domain": '{{ request()->path() }}',
            "csrfToken": '{{ csrf_token() }}',
        }
    </script>
    <script>
        window._locale = '{{ app()->getLocale() }}';
        window._translations = {!! $jsonLang ?? '' !!}
        window.jsLang = function(key, replace) {
            let translation = true

            let json_file = window._translations;
            translation = json_file[key] ?
                json_file[key] :
                key
            $.each(replace, (value, key) => {
                translation = translation.replace(':' + key, value)
            })
            return translation
        }
    </script>



    <input type="hidden" id="url" value="{{ url('/') }}">
    <input type="hidden" name="lat" class="lat" value="13.809561">
    <input type="hidden" name="lng" class="lng" value="-619.551604">
    <input type="hidden" name="zoom" class="zoom" value="18">
    <input type="hidden" name="slider_transition_time" id="slider_transition_time" value="5">
    <input type="hidden" name="base_url" class="base_url" value="{{ url('/') }}">
    <input type="hidden" name="csrf_token" class="csrf_token" value="{{ csrf_token() }}">
    <input type="hidden" name="currency_symbol" class="currency_symbol" value="฿">
    <input type="hidden" name="app_debug" class="app_debug" value="1">
    @if (auth()->check())
        <input type="hidden" name="balance" class="user_balance" value="{{ auth()->user()->balance }}">
    @endif
    <div data-aoraeditor="html">
        <div class="aoraeditor-skip aoraeditor-header">
            <style>
                .header_area .main_menu ul li ul.leftcontrol_submenu {
                    left: auto !important;
                    right: 100% !important;
                }

                /* drop down menu index issue */


                @media (max-width: 768px) {
                    .header__right.login_user .profile_info_iner {
                        top: 40px;
                    }
                }

                @media (max-width: 576px) {
                    .header__right.login_user .profile_info_iner {
                        top: 70px;
                    }
                }
            </style>

            <!-- HEADER::START -->
            @include('affiliate.frontend.header')

            {{-- <a href="#" class="float notification_wrapper cart_icon">
                <div class="notify_icon cart_store" style="padding-top: 7px">
                    <img style="max-width: 30px;" src="{{ asset('frontend/infixlmstheme/img/svg/cart_white.svg') }}"
                        src="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/svg/cart_white.svg" alt="">
                </div>
                <span class="notify_count">0</span>
            </a> --}}
        </div>
        @yield('content')

        {{-- @include('affiliate.frontend.footer') --}}
    </div>
    @include('affiliate.form_modal')
    @include('affiliate.modal_line_contact')

</body>
@include('affiliate.frontend.script')
@yield('script')
<input type="hidden" name="lat" class="lat" value="13.809561">
<input type="hidden" name="lng" class="lng" value="-619.551604">
<input type="hidden" name="zoom" class="zoom" value="18">


</html>
