<!-- Web Application Manifest -->
<link rel="manifest" href="{{ asset('vendor/livewire/manifest.json') }}">
<!-- Chrome for Android theme color -->
<meta name="theme-color" content="#000000">

<!-- Add to homescreen for Chrome on Android -->
<meta name="mobile-web-app-capable" content="yes">
<meta name="application-name" content="eiMaths ecommerce">
{{-- <link rel="icon" sizes="512x512" href="{{ asset('images/icons/icon-512x512.png') }}"> --}}

<link rel="icon" href="{{ asset('images/cropped-footer-logo-32x32.png') }}{{ assetVersion() }}"
    type="image/png" />


<!-- Add to homescreen for Safari on iOS -->
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta name="apple-mobile-web-app-title" content="eiMaths ecommerce">


<link href="public/images/icons/splash-640x1136.png"
    media="(device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2)"
    rel="apple-touch-startup-image" />
<link href="public/images/icons/splash-750x1334.png"
    media="(device-width: 375px) and (device-height: 667px) and (-webkit-device-pixel-ratio: 2)"
    rel="apple-touch-startup-image" />
<link href="public/images/icons/splash-1242x2208.png"
    media="(device-width: 621px) and (device-height: 1104px) and (-webkit-device-pixel-ratio: 3)"
    rel="apple-touch-startup-image" />
<link href="public/images/icons/splash-1125x2436.png"
    media="(device-width: 375px) and (device-height: 812px) and (-webkit-device-pixel-ratio: 3)"
    rel="apple-touch-startup-image" />
<link href="public/images/icons/splash-828x1792.png"
    media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 2)"
    rel="apple-touch-startup-image" />
<link href="public/images/icons/splash-1242x2688.png"
    media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 3)"
    rel="apple-touch-startup-image" />
<link href="public/images/icons/splash-1536x2048.png"
    media="(device-width: 768px) and (device-height: 1024px) and (-webkit-device-pixel-ratio: 2)"
    rel="apple-touch-startup-image" />
<link href="public/images/icons/splash-1668x2224.png"
    media="(device-width: 834px) and (device-height: 1112px) and (-webkit-device-pixel-ratio: 2)"
    rel="apple-touch-startup-image" />
<link href="public/images/icons/splash-1668x2388.png"
    media="(device-width: 834px) and (device-height: 1194px) and (-webkit-device-pixel-ratio: 2)"
    rel="apple-touch-startup-image" />
<link href="public/images/icons/splash-2048x2732.png"
    media="(device-width: 1024px) and (device-height: 1366px) and (-webkit-device-pixel-ratio: 2)"
    rel="apple-touch-startup-image" />

<!-- Tile for Win8 -->
<meta name="msapplication-TileColor" content="#ffffff">
<meta name="msapplication-TileImage" content="public/images/icons/icon-512x512.png">

<script type="text/javascript">
    // Initialize the service worker
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/serviceworker.js', {
            scope: '/'
        })
    }
</script>
<meta charset="utf-8">
<meta http-equiv="x-ua-compatible" content="ie=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">

<meta property="og:url" content="{{ url()->current() }}" />
<meta property="og:type" content="website" />
<meta property="og:title" content="eiMaths ecommerce" />
<meta property="og:description" content="" />
<meta property="og:image" content="{{ asset('images/cropped-footer-logo-32x32.png') }}" />

<meta name="title" content="eiMaths ecommerce | Affiliate">
<meta name="description" content="">
<meta name="keywords" content="">

<title>eiMaths ecommerce | Affiliate</title>
<link rel="icon" href="{{ asset('images/cropped-footer-logo-32x32.png') }}" type="image/png" />

<div>
    <style data-type="aoraeditor-style">
        :root {
            --system_primery_color: #fb8f13;
            --system_secendory_color: #202e3b;
            --footer_background_color: #e55a10;
            --footer_headline_color: #ffffff;
            --footer_text_color: #5b5c6e;
            --footer_text_hover_color: #000000;
            --bg_color: #ffffff;

            --menu_bg: #f8f9fa;
            --menu_text: #2b3d4e;
            --menu_hover_text: #FB1159;
            --menu_title_text: #202E3B;


            --system_primery_color_0: #fb8f1300;
            --system_primery_color_05: #fb8f130d;
            --system_primery_color_07: #fb8f1312;
            --system_primery_color_08: #fb8f1314;
            --system_primery_color_10: #fb8f131a;
            --system_primery_color_20: #fb8f1333;
            --system_primery_color_30: #fb8f134d;
            --system_primery_color_50: #fb8f1380;
            --system_primery_color_60: #fb8f1399;
            --system_primery_color_70: #fb8f13b3;
            --system_secendory_color_10: #202e3b1a;
            --sytem_gradient_2: #202e3b;


            --font_family1: "ABeeZee", sans-serif;
            --font_family2: "ABeeZee", sans-serif;


        }
    </style>
    <script src="https://ajax.googleapis.com/ajax/libs/webfont/1.6.26/webfont.js"></script>
    <script>
        WebFont.load({
            google: {
                families: [
                    "ABeeZee",
                    "ABeeZee"
                ]
            }
        });
    </script>
</div>

<style data-type="aoraeditor-style">
    :root {
        --white: #fff;
        --icon: #000;
        --backend-main-bg: #F7F7FA;
        --backend-primary-color: #556ee6;
        --header-bg: #fff;
        --sidebar-bg: #2A3042;
        --sidebar-link: #a6b0cf;
        --sidebar-link-active: #fff;
        --dashboard-summery-bg: #fff;
        --header-search-bg: #F3F3F9;
        --theme-default-color: #343a40;
        --notification-count: #fff;
        --icon-color: #555B6D;
        --icon-color-active: #212636;
        --sun-yellow: #c3530f;
        --white-box-bg: #fff;
        --sidebar-mini-link-hover: #2e3548;
        --card-header-bg: rgba(0, 0, 0, 0.03);
        --uppy-title-color: #000;
        --uppy-action-btn-color: #415094;
        --uppy-border: #dfdfdf;
        --selector-bg: #fff;
        --selector-shadow: 0px 10px 20px rgb(0 0 0 / 30%);
        --data-table-info: #415094;
        --data-table-row-hover: #fff;
        --pink-color: var(--backend-primary-color);
        --button-background: #556ee6;
        --button-hover: #485ec4;
        --pagination-bg: #fff;
        --pagination-color: ;
        --disabled-pagination-btn: #fff;
        --disabled-pagination-color: #000;
        --shadow: 0 0.75rem 1.5rem rgba(18, 38, 63, .03);
        --white_box_shadow: 0 0.75rem 1.5rem #12263f08;
        --popup_box_shadow: 0 1rem 3rem rgba(0, 0, 0, .175);
        --backend-border-color: #eff2f7;
        --border-hover-color: #d3dbe9;
        --nav-tab: #cad5f3;
        --nav-tab-active: var(--backend-main-bg);
        --dropdown-bg: var(--white);
        --notification-palse: rgba(85, 110, 230, 0.75);
        --dynamic-text-color: #495057;
        --required-red: #FF0000;
        --table-head: #eff2f7;
        --scroll-thumb: #a6b0cf;
        --scroll-thumb-hover: var(--backend-primary-color);
        --topnav-link: #fff9;
        --topnav-submenu-link: #545a6d;
        --footer-bg: #f2f2f5;
        --muted-text: rgba(33, 37, 41, 0.75);
        --bs-modal-bg: var(--backend-main-bg);
        --payment-logo-border-color: #E7EAEE;

    }


    .dark {
        --white: #fff;
        --icon: #fff;
        --backend-main-bg: #222736;
        --backend-primary-color: #556ee6;
        --header-bg: #262b3c;
        --sidebar-bg: #2A3042;
        --sidebar-link: #a6b0cf;
        --sidebar-link-active: #fff;
        --dashboard-summery-bg: #2A3042;
        --header-search-bg: #2A3042;
        --theme-default-color: #f6f6f6;
        --notification-count: #ffffff;
        --icon-color: #f6f6f6;
        --icon-color-active: #fff;
        --uppy-border-color: #32394e;
        --white-box-bg: #2A3042;
        --sidebar-mini-link-hover: #2e3548;
        --card-header-bg: #2e3548;
        --uppy-title-color: #fff;
        --uppy-action-btn-color: #6275cb;
        --uppy-border: #353d55;
        --selector-bg: #2A3042;
        --selector-shadow: 0px 10px 20px rgb(0 0 0 / 30%);
        --data-table-info: #f6f6f6;
        --data-table-row-hover: #2A3042;
        --pink-color: var(--backend-primary-color);
        --button-background: #556ee6;
        --button-hover: #485ec4;
        --pagination-bg: #2A3042;
        --pagination-color: #fff;
        --disabled-pagination-btn: #2A3042;
        --disabled-pagination-color: #fff;
        --shadow: 0 0.75rem 1.5rem rgba(18, 38, 63, .03);
        --white_box_shadow: 0 0.75rem 1.5rem #12263f08;
        --popup_box_shadow: 0 1rem 3rem rgba(0, 0, 0, .175);
        --backend-border-color: #32394e;
        --border-hover-color: #6a7492;
        --nav-tab: var(--header-bg);
        --nav-tab-active: var(--backend-main-bg);
        --dropdown-bg: var(--backend-main-bg);
        --notification-palse: rgba(255, 255, 255, 0.75);
        --dynamic-text-color: #a6b0cf;
        --required-red: #FF3535;
        --table-head: #32394e;
        --scroll-thumb: #a6b0cf;
        --scroll-thumb-hover: var(--backend-primary-color);
        --topnav-link: #fff9;
        --topnav-submenu-link: var(--topnav-link);
        --footer-bg: #262b3c;
        --muted-text: rgba(255, 255, 255, 0.5);
        --bs-secondary-color: rgba(255, 255, 255, 0.5);
        --bs-modal-bg: var(--backend-main-bg);
        --payment-logo-border-color: #E7EAEE26;
    }
</style>


<link rel="stylesheet" href="{{ asset('backend/css/themify-icons.css') }}{{ assetVersion() }}" />
<link rel="stylesheet" href="{{ asset('Modules/AoraPageBuilder/Resources/assets/css/fontawesome.css') }}"
    data-type="aoraeditor-style" />

<link rel="stylesheet" href="{{ asset('Modules/AoraPageBuilder/Resources/assets/css/aoraeditor.css') }}"
    data-type="aoraeditor-style" />
<link rel="stylesheet" href="{{ asset('Modules/AoraPageBuilder/Resources/assets/css/aoraeditor-components.css') }}"
    data-type="aoraeditor-style" />
<link rel="stylesheet" type="text/css" data-type="aoraeditor-style"
    href="{{ asset('Modules/AoraPageBuilder/Resources/assets/css/style.css') }}">

{{-- <link rel="stylesheet"
    href="https://ecommerce.eimaths-th.com/Modules/AoraPageBuilder/Resources/assets/css/bootstrap.min.css?v=7259"
    data-type="aoraeditor-style" />
<link rel="stylesheet"
    href="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/css/fontawesome.css?v=9684 "
    data-type="aoraeditor-style">

<link rel="stylesheet" href="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/css/app.css?v=179"
    data-type="aoraeditor-style">

<link rel="stylesheet" type="text/css" data-type="aoraeditor-style"
    href="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/css/frontend_style.css?v=4092">

<link rel="stylesheet" href="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/css/mega_menu.css"> --}}
<link rel="stylesheet"
    href="{{ asset('Modules/AoraPageBuilder/Resources/assets/css/bootstrap.min.css') }}{{ assetVersion() }}"
    data-type="aoraeditor-style" />
<link rel="stylesheet" href="{{ asset('frontend/infixlmstheme') }}/css/fontawesome.css{{ assetVersion() }} "
    data-type="aoraeditor-style">
<link rel="stylesheet" type="text/css" data-type="aoraeditor-style"
    href="{{ asset('frontend/infixlmstheme/css/frontend_style.css') . assetVersion() }}">
<link rel="stylesheet" href="{{ asset('frontend/infixlmstheme/css/app.css') . assetVersion() }}"
    data-type="aoraeditor-style">
<link rel="stylesheet" href="{{ asset('frontend/infixlmstheme/css/mega_menu.css') }}">



{{-- <link rel="stylesheet" href="https://ecommerce.eimaths-th.com/public/css/preloader.css" /> --}}
{{-- <link rel="stylesheet" href="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/css/custom.css"> --}}
<link rel="stylesheet" href="{{ asset('css/preloader.css') }}" />
<link rel="stylesheet" href="{{ asset('frontend/infixlmstheme/css/custom.css') }}">


<style>

</style>
{{-- <script src="https://ecommerce.eimaths-th.com/public/js/common.js"></script>

<script type="text/javascript"
    src="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/js/jquery.lazy.min.js"></script> --}}

{{-- <link rel="stylesheet" href="https://ecommerce.eimaths-th.com/public/css/preloader.css" /> --}}

<script src="{{ asset('js/common.js') }}"></script>

<script type="text/javascript" src="{{ asset('frontend/infixlmstheme/js/jquery.lazy.min.js') }}"></script>

{{-- <link rel="stylesheet" href="{{ asset('css/preloader.css') }}" /> --}}


{{-- <link rel='stylesheet' type='text/css' property='stylesheet'
    href='//ecommerce.eimaths-th.com/_debugbar/assets/stylesheets?v=1676989262&theme=auto'
    data-turbolinks-eval='false' data-turbo-eval='false'>
<script src='//ecommerce.eimaths-th.com/_debugbar/assets/javascript?v=1676989262' data-turbolinks-eval='false'
    data-turbo-eval='false'></script>
<script data-turbo-eval="false">
    jQuery.noConflict(true);
</script> --}}
@yield('style')
