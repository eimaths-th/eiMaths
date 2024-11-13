<!DOCTYPE html>
<html dir="" class="" lang="en" itemscope itemtype="{{ url('/') }}">

<head>
    <!-- Web Application Manifest -->
    <link rel="manifest" href="https://ecommerce.eimaths-th.com/manifest.json">
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
    <meta name="csrf-token" content="{{ csrf_token() }}" />

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

    {{-- <link rel="stylesheet" href="https://ecommerce.eimaths-th.com/public/backend/css/themify-icons.css?v=7024" /> --}}

    {{-- <link rel="stylesheet"
        href="https://ecommerce.eimaths-th.com/Modules/AoraPageBuilder/Resources/assets/css/fontawesome.css"
        data-type="aoraeditor-style" />

    <link rel="stylesheet"
        href="https://ecommerce.eimaths-th.com/Modules/AoraPageBuilder/Resources/assets/css/aoraeditor.css"
        data-type="aoraeditor-style" />
    <link rel="stylesheet"
        href="https://ecommerce.eimaths-th.com/Modules/AoraPageBuilder/Resources/assets/css/aoraeditor-components.css"
        data-type="aoraeditor-style" />


    <link rel="stylesheet" type="text/css" data-type="aoraeditor-style"
        href="https://ecommerce.eimaths-th.com/Modules/AoraPageBuilder/Resources/assets/css/style.css"> --}}

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
</head>

<body>
    {{-- preloader --}}
    {{-- <div class="preloader" dir="ltr">
        <div class="dot dot2 d21"></div>
        <div class="dot dot2 d22"></div>
        <div class="dot dot2 d23"></div>
    </div> --}}



    <script>
        window.Laravel = {
            "baseUrl": '{{ url('/') }}' + '/',
            "current_path_without_domain": '{{ request()->path() }}',
            "csrfToken": '{{ csrf_token() }}',
        }
    </script>


    {{-- <script>
        window._locale = 'th';
        window._translations = {
            "data_success": "\u0e2a\u0e33\u0e40\u0e23\u0e47\u0e08",
            "data_update": "\u0e2d\u0e31\u0e1b\u0e40\u0e14\u0e15",
            "data_showing": "\u0e41\u0e2a\u0e14\u0e07",
            "data_entries": "\u0e23\u0e32\u0e22\u0e01\u0e32\u0e23",
            "data_to": "\u0e16\u0e36\u0e07",
            "data_of": "\u0e02\u0e2d\u0e07",
            "data_no_data_available_in_table": "\u0e44\u0e21\u0e48\u0e21\u0e35\u0e02\u0e49\u0e2d\u0e21\u0e39\u0e25\u0e43\u0e19\u0e15\u0e32\u0e23\u0e32\u0e07\u0e19\u0e35\u0e49",
            "data_search": "\u0e04\u0e49\u0e19\u0e2b\u0e32",
            "data_drag_drop": "\u0e25\u0e32\u0e01 & \u0e27\u0e32\u0e07\u0e44\u0e1f\u0e25\u0e4c\u0e02\u0e2d\u0e07\u0e04\u0e38\u0e13\u0e2b\u0e23\u0e37\u0e2d",
            "data_browse": "\u0e40\u0e23\u0e35\u0e22\u0e01\u0e14\u0e39",
            "data_invalid_file": "\u0e1f\u0e34\u0e25\u0e14\u0e4c\u0e21\u0e35\u0e44\u0e1f\u0e25\u0e4c\u0e17\u0e35\u0e48\u0e44\u0e21\u0e48\u0e16\u0e39\u0e01\u0e15\u0e49\u0e2d\u0e07",
            "data_wait_for_size": "\u0e23\u0e2d\u0e02\u0e19\u0e32\u0e14",
            "data_upload": "\u0e2d\u0e31\u0e1b\u0e42\u0e2b\u0e25\u0e14",
            "data_retry": "\u0e25\u0e2d\u0e07\u0e2d\u0e35\u0e01\u0e04\u0e23\u0e31\u0e49\u0e07",
            "data_undo": "\u0e22\u0e01\u0e40\u0e25\u0e34\u0e01",
            "data_cancel": "\u0e22\u0e01\u0e40\u0e25\u0e34\u0e01",
            "data_abort": "\u0e22\u0e01\u0e40\u0e25\u0e34\u0e01",
            "data_remove": "\u0e25\u0e1a",
            "data_tap_to_undo": "\u0e41\u0e15\u0e30\u0e40\u0e1e\u0e37\u0e48\u0e2d\u0e22\u0e01\u0e40\u0e25\u0e34\u0e01",
            "data_tap_to_retry": "\u0e41\u0e15\u0e30\u0e40\u0e1e\u0e37\u0e48\u0e2d\u0e25\u0e2d\u0e07\u0e2d\u0e35\u0e01\u0e04\u0e23\u0e31\u0e49\u0e07",
            "data_tab_or_wait": "\u0e41\u0e15\u0e30\u0e40\u0e1e\u0e37\u0e48\u0e2d\u0e22\u0e01\u0e40\u0e25\u0e34\u0e01 | \u0e23\u0e2d\u0e08\u0e19\u0e01\u0e27\u0e48\u0e32\u0e08\u0e30\u0e40\u0e2b\u0e47\u0e19\u0e27\u0e48\u0e32\u0e2d\u0e31\u0e1b\u0e42\u0e2b\u0e25\u0e14\u0e40\u0e2a\u0e23\u0e47\u0e08\u0e2a\u0e21\u0e1a\u0e39\u0e23\u0e13\u0e4c",
            "data_error_during_revert": "\u0e40\u0e01\u0e34\u0e14\u0e02\u0e49\u0e2d\u0e1c\u0e34\u0e14\u0e1e\u0e25\u0e32\u0e14\u0e23\u0e30\u0e2b\u0e27\u0e48\u0e32\u0e07\u0e01\u0e32\u0e23\u0e04\u0e37\u0e19\u0e04\u0e48\u0e32",
            "data_error_during_upload": "\u0e40\u0e01\u0e34\u0e14\u0e02\u0e49\u0e2d\u0e1c\u0e34\u0e14\u0e1e\u0e25\u0e32\u0e14\u0e23\u0e30\u0e2b\u0e27\u0e48\u0e32\u0e07\u0e01\u0e32\u0e23\u0e2d\u0e31\u0e1b\u0e42\u0e2b\u0e25\u0e14",
            "data_upload_cancel": "\u0e01\u0e32\u0e23\u0e2d\u0e31\u0e1b\u0e42\u0e2b\u0e25\u0e14\u0e16\u0e39\u0e01\u0e22\u0e01\u0e40\u0e25\u0e34\u0e01",
            "data_upload_complete": "\u0e01\u0e32\u0e23\u0e2d\u0e31\u0e1b\u0e42\u0e2b\u0e25\u0e14\u0e40\u0e2a\u0e23\u0e47\u0e08\u0e2a\u0e21\u0e1a\u0e39\u0e23\u0e13\u0e4c",
            "data_uploading": "\u0e01\u0e33\u0e25\u0e31\u0e07\u0e2d\u0e31\u0e1b\u0e42\u0e2b\u0e25\u0e14",
            "data_error_during_remove": "\u0e40\u0e01\u0e34\u0e14\u0e02\u0e49\u0e2d\u0e1c\u0e34\u0e14\u0e1e\u0e25\u0e32\u0e14\u0e23\u0e30\u0e2b\u0e27\u0e48\u0e32\u0e07\u0e01\u0e32\u0e23\u0e25\u0e1a",
            "data_removed": "\u0e16\u0e39\u0e01\u0e25\u0e1a",
            "data_error_during_load": "\u0e40\u0e01\u0e34\u0e14\u0e02\u0e49\u0e2d\u0e1c\u0e34\u0e14\u0e1e\u0e25\u0e32\u0e14\u0e23\u0e30\u0e2b\u0e27\u0e48\u0e32\u0e07\u0e01\u0e32\u0e23\u0e42\u0e2b\u0e25\u0e14",
            "data_added": "\u0e40\u0e1e\u0e34\u0e48\u0e21\u0e41\u0e25\u0e49\u0e27",
            "data_loading": "\u0e01\u0e33\u0e25\u0e31\u0e07\u0e42\u0e2b\u0e25\u0e14...",
            "data_files_in_list": "\u0e44\u0e1f\u0e25\u0e4c\u0e43\u0e19\u0e23\u0e32\u0e22\u0e01\u0e32\u0e23",
            "data_file_in_list": "\u0e44\u0e1f\u0e25\u0e4c\u0e43\u0e19\u0e23\u0e32\u0e22\u0e01\u0e32\u0e23",
            "data_size_not_available": "\u0e02\u0e19\u0e32\u0e14\u0e44\u0e21\u0e48\u0e1e\u0e23\u0e49\u0e2d\u0e21\u0e43\u0e0a\u0e49\u0e07\u0e32\u0e19",
            "data_operation_success": "\u0e14\u0e33\u0e40\u0e19\u0e34\u0e19\u0e01\u0e32\u0e23\u0e2a\u0e33\u0e40\u0e23\u0e47\u0e08",
            "data_something_went_wrong": "\u0e40\u0e01\u0e34\u0e14\u0e02\u0e49\u0e2d\u0e1c\u0e34\u0e14\u0e1e\u0e25\u0e32\u0e14\u0e1a\u0e32\u0e07\u0e2d\u0e22\u0e48\u0e32\u0e07",
            "data_error": "\u0e02\u0e49\u0e2d\u0e1c\u0e34\u0e14\u0e1e\u0e25\u0e32\u0e14",
            "data_select_option": "\u0e42\u0e1b\u0e23\u0e14\u0e40\u0e25\u0e37\u0e2d\u0e01\u0e15\u0e31\u0e27\u0e40\u0e25\u0e37\u0e2d\u0e01",
            "data_select_match": "\u0e42\u0e1b\u0e23\u0e14\u0e40\u0e25\u0e37\u0e2d\u0e01\u0e15\u0e31\u0e27\u0e40\u0e25\u0e37\u0e2d\u0e01\u0e17\u0e35\u0e48\u0e15\u0e23\u0e07\u0e01\u0e31\u0e19",
            "data_write_ans": "\u0e42\u0e1b\u0e23\u0e14\u0e40\u0e02\u0e35\u0e22\u0e19\u0e04\u0e33\u0e15\u0e2d\u0e1a",
            "data_quick_search": "\u0e04\u0e49\u0e19\u0e2b\u0e32\u0e2d\u0e22\u0e48\u0e32\u0e07\u0e23\u0e27\u0e14\u0e40\u0e23\u0e47\u0e27",
            "data_characters_count": "\u0e08\u0e33\u0e19\u0e27\u0e19\u0e15\u0e31\u0e27\u0e2d\u0e31\u0e01\u0e29\u0e23",
            "no_data_available": "\u0e44\u0e21\u0e48\u0e21\u0e35\u0e02\u0e49\u0e2d\u0e21\u0e39\u0e25",
            "data_all": "\u0e17\u0e31\u0e49\u0e07\u0e2b\u0e21\u0e14",
            "data_info_filtered": "\u0e01\u0e23\u0e2d\u0e07\u0e08\u0e32\u0e01",
            "data_show": "\u0e41\u0e2a\u0e14\u0e07",
            "data_processing": "\u0e01\u0e33\u0e25\u0e31\u0e07\u0e14\u0e33\u0e40\u0e19\u0e34\u0e19\u0e01\u0e32\u0e23...",
            "data_no_match": "\u0e44\u0e21\u0e48\u0e1e\u0e1a\u0e23\u0e30\u0e40\u0e1a\u0e35\u0e22\u0e19\u0e17\u0e35\u0e48\u0e15\u0e23\u0e07\u0e01\u0e31\u0e19",
            "data_first": "\u0e41\u0e23\u0e01",
            "data_last": "\u0e2a\u0e38\u0e14\u0e17\u0e49\u0e32\u0e22",
            "data_col_visibility": "\u0e04\u0e27\u0e32\u0e21\u0e2a\u0e32\u0e21\u0e32\u0e23\u0e16\u0e43\u0e19\u0e01\u0e32\u0e23\u0e21\u0e2d\u0e07\u0e40\u0e2b\u0e47\u0e19\u0e04\u0e2d\u0e25\u0e31\u0e21\u0e19\u0e4c",
            "data_col_restore": "\u0e01\u0e39\u0e49\u0e04\u0e37\u0e19\u0e04\u0e27\u0e32\u0e21\u0e2a\u0e32\u0e21\u0e32\u0e23\u0e16\u0e43\u0e19\u0e01\u0e32\u0e23\u0e21\u0e2d\u0e07\u0e40\u0e2b\u0e47\u0e19",
            "data_collection": "\u0e01\u0e32\u0e23\u0e23\u0e27\u0e1a\u0e23\u0e27\u0e21",
            "data_copy": "\u0e04\u0e31\u0e14\u0e25\u0e2d\u0e01",
            "data_copy_keys": "\u0e01\u0e14 ctrl \u0e2b\u0e23\u0e37\u0e2d u2318 + C \u0e40\u0e1e\u0e37\u0e48\u0e2d\u0e04\u0e31\u0e14\u0e25\u0e2d\u0e01\u0e02\u0e49\u0e2d\u0e21\u0e39\u0e25\u0e15\u0e32\u0e23\u0e32\u0e07\u0e44\u0e1b\u0e22\u0e31\u0e07\u0e04\u0e25\u0e34\u0e1b\u0e1a\u0e2d\u0e23\u0e4c\u0e14\u0e02\u0e2d\u0e07\u0e23\u0e30\u0e1a\u0e1a\u0e04\u0e38\u0e13",
            "data_copy_cancel": "\u0e2b\u0e32\u0e01\u0e15\u0e49\u0e2d\u0e07\u0e01\u0e32\u0e23\u0e22\u0e01\u0e40\u0e25\u0e34\u0e01 \u0e04\u0e25\u0e34\u0e01\u0e17\u0e35\u0e48\u0e02\u0e49\u0e2d\u0e04\u0e27\u0e32\u0e21\u0e19\u0e35\u0e49\u0e2b\u0e23\u0e37\u0e2d\u0e01\u0e14 escape",
            "data_copy_success": "\u0e04\u0e31\u0e14\u0e25\u0e2d\u0e01 1 \u0e41\u0e16\u0e27\u0e44\u0e1b\u0e22\u0e31\u0e07\u0e04\u0e25\u0e34\u0e1b\u0e1a\u0e2d\u0e23\u0e4c\u0e14",
            "data_copied": "\u0e04\u0e31\u0e14\u0e25\u0e2d\u0e01\u0e41\u0e25\u0e49\u0e27",
            "data_rows_to_clipboard": "\u0e41\u0e16\u0e27\u0e44\u0e1b\u0e22\u0e31\u0e07\u0e04\u0e25\u0e34\u0e1b\u0e1a\u0e2d\u0e23\u0e4c\u0e14",
            "data_copy_to_clipboard": "\u0e04\u0e31\u0e14\u0e25\u0e2d\u0e01\u0e44\u0e1b\u0e22\u0e31\u0e07\u0e04\u0e25\u0e34\u0e1b\u0e1a\u0e2d\u0e23\u0e4c\u0e14",
            "data_csv": "CSV",
            "data_excel": "Excel",
            "data_show_all_rows": "\u0e41\u0e2a\u0e14\u0e07\u0e17\u0e38\u0e01\u0e41\u0e16\u0e27",
            "data_rows": "\u0e41\u0e16\u0e27",
            "data_pdf": "PDF",
            "data_print": "\u0e1e\u0e34\u0e21\u0e1e\u0e4c",
            "data_restore": "\u0e01\u0e39\u0e49\u0e04\u0e37\u0e19",
            "data_states": "\u0e2a\u0e16\u0e32\u0e19\u0e30",
            "data_rename": "\u0e40\u0e1b\u0e25\u0e35\u0e48\u0e22\u0e19\u0e0a\u0e37\u0e48\u0e2d",
            "data_remove_all": "\u0e25\u0e1a\u0e17\u0e31\u0e49\u0e07\u0e2b\u0e21\u0e14",
            "data_create": "\u0e2a\u0e23\u0e49\u0e32\u0e07",
            "data_previous": "\u0e01\u0e48\u0e2d\u0e19\u0e2b\u0e19\u0e49\u0e32",
            "data_next": "\u0e16\u0e31\u0e14\u0e44\u0e1b",
            "data_hours": "\u0e0a\u0e31\u0e48\u0e27\u0e42\u0e21\u0e07",
            "data_minutes": "\u0e19\u0e32\u0e17\u0e35",
            "data_seconds": "\u0e27\u0e34\u0e19\u0e32\u0e17\u0e35",
            "data_am": "AM",
            "data_pm": "PM",
            "data_Sun": "\u0e2d\u0e32.",
            "data_Mon": "\u0e08.",
            "data_Tue": "\u0e2d.",
            "data_Wed": "\u0e1e.",
            "data_Thu": "\u0e1e\u0e24.",
            "data_Fri": "\u0e28.",
            "data_Sat": "\u0e2a.",
            "data_January": "\u0e21\u0e01\u0e23\u0e32\u0e04\u0e21",
            "data_February": "\u0e01\u0e38\u0e21\u0e20\u0e32\u0e1e\u0e31\u0e19\u0e18\u0e4c",
            "data_March": "\u0e21\u0e35\u0e19\u0e32\u0e04\u0e21",
            "data_April": "\u0e40\u0e21\u0e29\u0e32\u0e22\u0e19",
            "data_May": "\u0e1e\u0e24\u0e29\u0e20\u0e32\u0e04\u0e21",
            "data_June": "\u0e21\u0e34\u0e16\u0e38\u0e19\u0e32\u0e22\u0e19",
            "data_July": "\u0e01\u0e23\u0e01\u0e0e\u0e32\u0e04\u0e21",
            "data_August": "\u0e2a\u0e34\u0e07\u0e2b\u0e32\u0e04\u0e21",
            "data_September": "\u0e01\u0e31\u0e19\u0e22\u0e32\u0e22\u0e19",
            "data_October": "\u0e15\u0e38\u0e25\u0e32\u0e04\u0e21",
            "data_November": "\u0e1e\u0e24\u0e28\u0e08\u0e34\u0e01\u0e32\u0e22\u0e19",
            "data_December": "\u0e18\u0e31\u0e19\u0e27\u0e32\u0e04\u0e21",
            "data_info_empty": "\u0e41\u0e2a\u0e14\u0e07 0 \u0e16\u0e36\u0e07 0 \u0e02\u0e2d\u0e07 0 \u0e23\u0e32\u0e22\u0e01\u0e32\u0e23",
            "data_aora_editor": "\u0e01\u0e33\u0e25\u0e31\u0e07\u0e41\u0e01\u0e49\u0e44\u0e02\u0e14\u0e49\u0e27\u0e22 Aora Editor",
            "data_view_mobile": "\u0e14\u0e39\u0e1a\u0e19\u0e21\u0e37\u0e2d\u0e16\u0e37\u0e2d",
            "data_view_tablet": "\u0e14\u0e39\u0e1a\u0e19\u0e41\u0e17\u0e47\u0e1a\u0e40\u0e25\u0e47\u0e15",
            "data_view_laptop": "\u0e14\u0e39\u0e1a\u0e19\u0e41\u0e25\u0e47\u0e1b\u0e17\u0e47\u0e2d\u0e1b",
            "data_view_desktop": "\u0e14\u0e39\u0e1a\u0e19\u0e40\u0e14\u0e2a\u0e01\u0e4c\u0e17\u0e47\u0e2d\u0e1b",
            "data_preview_on": "\u0e14\u0e39\u0e15\u0e31\u0e27\u0e2d\u0e22\u0e48\u0e32\u0e07\u0e40\u0e1b\u0e34\u0e14",
            "data_preview_off": "\u0e14\u0e39\u0e15\u0e31\u0e27\u0e2d\u0e22\u0e48\u0e32\u0e07\u0e1b\u0e34\u0e14",
            "data_fullscreen_on": "\u0e40\u0e15\u0e47\u0e21\u0e2b\u0e19\u0e49\u0e32\u0e08\u0e2d\u0e40\u0e1b\u0e34\u0e14",
            "data_fullscreen_off": "\u0e40\u0e15\u0e47\u0e21\u0e2b\u0e19\u0e49\u0e32\u0e08\u0e2d\u0e1b\u0e34\u0e14",
            "data_save": "\u0e1a\u0e31\u0e19\u0e17\u0e36\u0e01",
            "data_add_content": "\u0e40\u0e1e\u0e34\u0e48\u0e21\u0e40\u0e19\u0e37\u0e49\u0e2d\u0e2b\u0e32",
            "data_add_content_below": "\u0e40\u0e1e\u0e34\u0e48\u0e21\u0e40\u0e19\u0e37\u0e49\u0e2d\u0e2b\u0e32\u0e14\u0e49\u0e32\u0e19\u0e25\u0e48\u0e32\u0e07",
            "data_paste_content": "\u0e27\u0e32\u0e07\u0e40\u0e19\u0e37\u0e49\u0e2d\u0e2b\u0e32",
            "data_paste_content_below": "\u0e27\u0e32\u0e07\u0e40\u0e19\u0e37\u0e49\u0e2d\u0e2b\u0e32\u0e14\u0e49\u0e32\u0e19\u0e25\u0e48\u0e32\u0e07",
            "data_drag": ["\u0e25\u0e32\u0e01", "\u0e25\u0e32\u0e01\u0e44\u0e14\u0e49"],
            "data_move_up": "\u0e40\u0e25\u0e37\u0e48\u0e2d\u0e19\u0e02\u0e36\u0e49\u0e19",
            "data_move_down": "\u0e40\u0e25\u0e37\u0e48\u0e2d\u0e19\u0e25\u0e07",
            "data_setting": "\u0e01\u0e32\u0e23\u0e15\u0e31\u0e49\u0e07\u0e04\u0e48\u0e32",
            "data_cut": "\u0e15\u0e31\u0e14",
            "data_delete": "\u0e25\u0e1a",
            "data_category": "\u0e2b\u0e21\u0e27\u0e14\u0e2b\u0e21\u0e39\u0e48",
            "data_type_to_search": "\u0e1e\u0e34\u0e21\u0e1e\u0e4c\u0e40\u0e1e\u0e37\u0e48\u0e2d\u0e04\u0e49\u0e19\u0e2b\u0e32...",
            "data_drag_to_resize": "\u0e25\u0e32\u0e01\u0e40\u0e1e\u0e37\u0e48\u0e2d\u0e1b\u0e23\u0e31\u0e1a\u0e02\u0e19\u0e32\u0e14",
            "data_container_settings": "\u0e01\u0e32\u0e23\u0e15\u0e31\u0e49\u0e07\u0e04\u0e48\u0e32\u0e20\u0e32\u0e0a\u0e19\u0e30",
            "data_confirm_delete_container": "\u0e04\u0e38\u0e13\u0e41\u0e19\u0e48\u0e43\u0e08\u0e2b\u0e23\u0e37\u0e2d\u0e44\u0e21\u0e48\u0e27\u0e48\u0e32\u0e15\u0e49\u0e2d\u0e07\u0e01\u0e32\u0e23\u0e25\u0e1a\u0e20\u0e32\u0e0a\u0e19\u0e30\u0e19\u0e35\u0e49? \u0e01\u0e32\u0e23\u0e01\u0e23\u0e30\u0e17\u0e33\u0e19\u0e35\u0e49\u0e44\u0e21\u0e48\u0e2a\u0e32\u0e21\u0e32\u0e23\u0e16\u0e22\u0e49\u0e2d\u0e19\u0e01\u0e25\u0e31\u0e1a\u0e44\u0e14\u0e49!",
            "data_confirm_delete_component": "\u0e04\u0e38\u0e13\u0e41\u0e19\u0e48\u0e43\u0e08\u0e2b\u0e23\u0e37\u0e2d\u0e44\u0e21\u0e48\u0e27\u0e48\u0e32\u0e15\u0e49\u0e2d\u0e07\u0e01\u0e32\u0e23\u0e25\u0e1a\u0e2a\u0e48\u0e27\u0e19\u0e1b\u0e23\u0e30\u0e01\u0e2d\u0e1a\u0e19\u0e35\u0e49? \u0e01\u0e32\u0e23\u0e01\u0e23\u0e30\u0e17\u0e33\u0e19\u0e35\u0e49\u0e44\u0e21\u0e48\u0e2a\u0e32\u0e21\u0e32\u0e23\u0e16\u0e22\u0e49\u0e2d\u0e19\u0e01\u0e25\u0e31\u0e1a\u0e44\u0e14\u0e49!",
            "data_select_options": "\u0e40\u0e25\u0e37\u0e2d\u0e01\u0e15\u0e31\u0e27\u0e40\u0e25\u0e37\u0e2d\u0e01",
            "data_selected": "\u0e40\u0e25\u0e37\u0e2d\u0e01\u0e41\u0e25\u0e49\u0e27",
            "data_select_all": "\u0e40\u0e25\u0e37\u0e2d\u0e01\u0e17\u0e31\u0e49\u0e07\u0e2b\u0e21\u0e14",
            "data_unselect_all": "\u0e44\u0e21\u0e48\u0e40\u0e25\u0e37\u0e2d\u0e01\u0e17\u0e31\u0e49\u0e07\u0e2b\u0e21\u0e14",
            "data_none_selected": "\u0e44\u0e21\u0e48\u0e21\u0e35\u0e17\u0e35\u0e48\u0e40\u0e25\u0e37\u0e2d\u0e01",
            "data_all_selected": "\u0e40\u0e25\u0e37\u0e2d\u0e01\u0e17\u0e31\u0e49\u0e07\u0e2b\u0e21\u0e14",
            "data_select": "\u0e40\u0e25\u0e37\u0e2d\u0e01",
            "data_apply": "\u0e19\u0e33\u0e44\u0e1b\u0e43\u0e0a\u0e49",
            "data_from": "\u0e08\u0e32\u0e01",
            "data_custom": "\u0e01\u0e33\u0e2b\u0e19\u0e14\u0e40\u0e2d\u0e07",
            "data_today": "\u0e27\u0e31\u0e19\u0e19\u0e35\u0e49",
            "data_yesterday": "\u0e40\u0e21\u0e37\u0e48\u0e2d\u0e27\u0e32\u0e19",
            "data_last_7_days": "7 \u0e27\u0e31\u0e19\u0e17\u0e35\u0e48\u0e1c\u0e48\u0e32\u0e19\u0e21\u0e32",
            "data_last_30_days": "30 \u0e27\u0e31\u0e19\u0e17\u0e35\u0e48\u0e1c\u0e48\u0e32\u0e19\u0e21\u0e32",
            "data_this_month": "\u0e40\u0e14\u0e37\u0e2d\u0e19\u0e19\u0e35\u0e49",
            "data_last_month": "\u0e40\u0e14\u0e37\u0e2d\u0e19\u0e17\u0e35\u0e48\u0e41\u0e25\u0e49\u0e27",
            "data_this_year": "\u0e1b\u0e35\u0e19\u0e35\u0e49",
            "data_last_year": "\u0e1b\u0e35\u0e17\u0e35\u0e48\u0e41\u0e25\u0e49\u0e27",
            "data_browse_files": "\u0e40\u0e23\u0e35\u0e22\u0e01\u0e14\u0e39\u0e44\u0e1f\u0e25\u0e4c",
            "data_10": "10",
            "data_25": "25",
            "data_50": "50",
            "data_100": "100",
            "data_minimum_payout": "\u0e08\u0e33\u0e19\u0e27\u0e19\u0e40\u0e07\u0e34\u0e19\u0e02\u0e31\u0e49\u0e19\u0e15\u0e48\u0e33\u0e17\u0e35\u0e48\u0e16\u0e2d\u0e19\u0e04\u0e37\u0e2d",
            "data_your_balance": "\u0e22\u0e2d\u0e14\u0e40\u0e07\u0e34\u0e19\u0e02\u0e2d\u0e07\u0e04\u0e38\u0e13\u0e04\u0e37\u0e2d",
            "data_withdraw_request_submitted": "\u0e04\u0e33\u0e02\u0e2d\u0e16\u0e2d\u0e19\u0e40\u0e07\u0e34\u0e19\u0e16\u0e39\u0e01\u0e2a\u0e48\u0e07\u0e40\u0e23\u0e35\u0e22\u0e1a\u0e23\u0e49\u0e2d\u0e22\u0e41\u0e25\u0e49\u0e27",
            "data_link_copied": "\u0e25\u0e34\u0e07\u0e01\u0e4c\u0e16\u0e39\u0e01\u0e04\u0e31\u0e14\u0e25\u0e2d\u0e01\u0e40\u0e23\u0e35\u0e22\u0e1a\u0e23\u0e49\u0e2d\u0e22\u0e41\u0e25\u0e49\u0e27"
        }
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
    </script> --}}

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

            <header>
                <div id="sticky-header" class="header_area ">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-12">
                                <div class="header__wrapper">
                                    <!-- header__left__start  -->
                                    <div class="header__left d-flex align-items-center gap-20 ">
                                        <div class="">
                                            <a class="logo_img" href="{{ route('affiliate.index') }}">
                                                <img class="p-2" src="{{ asset('images/logo.png') }}"
                                                    width="150" alt="eiMaths ecommerce">
                                            </a>
                                        </div>
                                        <div class="me-3 translator-switch">


                                        </div>

                                        <div class="category_search d-flex category_box_iner">
                                            {{-- <form action="https://ecommerce.eimaths-th.com/search"> --}}
                                            <form action="#">
                                                <div class="input-group theme_search_field">
                                                    <div class="input-group-prepend">
                                                        <button class="btn" type="button" id="button-addon1"><i
                                                                class="ti-search"></i>
                                                        </button>
                                                    </div>

                                                    <input type="text" class="form-control" name="query"
                                                        placeholder="ค้นหาหลักสูตร ทักษะ และวิดีโอ"
                                                        onfocus="this.placeholder = ''"
                                                        onblur="this.placeholder = 'ค้นหาหลักสูตร ทักษะ และวิดีโอ'">

                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    <!-- header__left__start  -->

                                    <!-- main_menu_start  -->
                                    <div class="main_menu text-end d-none d-lg-block category_box_iner">
                                        <nav>
                                            <div class="menu_dropdown">
                                                <ul>
                                                    <li class="mega_menu_dropdown active_menu_item">
                                                        {{-- <a href="https://ecommerce.eimaths-th.com/courses?category=2"> --}}
                                                        <a href="#">
                                                            Upper Primary
                                                        </a>
                                                    </li>
                                                    <li class="mega_menu_dropdown active_menu_item">
                                                        {{-- <a href="https://ecommerce.eimaths-th.com/courses?category=1"> --}}
                                                        <a href="#">
                                                            Lower Primary
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>


                                            <ul id="mobile-menu">


                                                <li class="  ">
                                                    {{-- <a href="https://ecommerce.eimaths-th.com/"> --}}
                                                    <a href="#"> Home </a>
                                                </li>

                                                <li class="  ">
                                                    {{-- <a href="https://ecommerce.eimaths-th.com/courses"> --}}
                                                    <a href="#"> Courses </a>
                                                </li>

                                                <li class="  ">
                                                    {{-- <a href="https://ecommerce.eimaths-th.com/classes"> --}}
                                                    <a href="#">

                                                        Classes</a>
                                                </li>

                                                <li class="  ">
                                                    {{-- <a href="https://ecommerce.eimaths-th.com/affiliate"> --}}
                                                    <a href="#">

                                                        Affiliate</a>
                                                </li>

                                                <li class="  ">
                                                    {{-- <a href="https://ecommerce.eimaths-th.com/contact-us"> --}}
                                                    <a href="#">
                                                        Contact Us</a>
                                                </li>

                                                <li><a href="#"></a></li>


                                            </ul>


                                        </nav>
                                    </div>
                                    <!-- main_menu_start  -->


                                    <div class="me-3 translator-switch">


                                    </div>
                                    <!-- header__right_start  -->
                                    <div class="header__right">
                                        <div class="contact_wrap d-flex align-items-center">
                                            <div class="contact_btn">
                                                <a href="https://ecommerce.eimaths-th.com/login"
                                                    class="theme_btn small_btn2">เข้าสู่ระบบ </a>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- header__right_end  -->
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mobile_menu d-block d-lg-none"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <a href="#" class="float notification_wrapper cart_icon">
                <div class="notify_icon cart_store" style="padding-top: 7px">
                    <img style="max-width: 30px;" src="{{ asset('frontend/infixlmstheme/img/svg/cart_white.svg') }}"
                        {{-- src="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/svg/cart_white.svg" --}} alt="">
                </div>
                <span class="notify_count">0</span>
            </a>
        </div>
        <div id="content-area">
            <div class="full-page" data-type="component-text" data-preview="" data-aoraeditor-title="All Section"
                data-aoraeditor-categories="Affiliate Page">
                <div class="row">
                    <div class="col-sm-12 ui-resizable" data-type="container-content">
                        <div data-aoraeditor-categories="Affiliate Page" data-aoraeditor-title="Affiliate Title"
                            data-preview="{{ asset('frontend/infixlmstheme/img/snippets/preview/affiliate/title.png') }}"
                            data-type="component-text">
                            <div class="affiliate_bradcam_area">
                                <div class="container">
                                    <div class="row">
                                        <div class="col-lg-10 offset-lg-1">
                                            <div class="breadcam_text text-center"><br><span><span
                                                        style="font-size:28px;">เข้าร่วมโปรแกรม Affiliate
                                                        ของเรา</span></span>
                                                <h4><strong>เป็นส่วนหนึ่งของความสำเร็จของเรา รับค่าคอมมิชชั่นสูงสุด 20%
                                                        จากการแนะนำ</strong></h4>
                                                <p>เรามีค่าคอมมิชชั่นที่น่าสนใจสำหรับแต่ละการแนะนำที่ประสบความสำเร็จ</p>
                                                <a class="theme_btn" href="#">
                                                    {{-- <a class="theme_btn" href="https://ecommerce.eimaths-th.com/affiliate/registration"> --}}
                                                    เข้าร่วมเป็นครอบครัว Affiliate</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12 ui-resizable" data-type="container-content">
                        <div data-aoraeditor-categories="Affiliate Page" data-aoraeditor-title="Affiliate Commission"
                            data-preview="{{ asset('frontend/infixlmstheme/img/snippets/preview/affiliate/commission_section.png') }}"
                            {{-- data-preview="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/snippets/preview/affiliate/commission_section.png" --}} data-type="component-text">
                            <div class="lms_section">
                                <div class="container">
                                    <div class="row justify-content-center">
                                        <div class="col-xl-10">
                                            <div class="commision_box">
                                                <div alt=""
                                                    class="{{ asset('frontend/infixlmstheme/img/snippets/preview/affiliate/round.svg') }}">
                                                    {{-- class="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/snippets/preview/affiliate/round.svg"> --}}
                                                    &nbsp;</div>
                                                <div class="commision_left">
                                                    <h4>อัตราค่าคอมมิชชั่น</h4>
                                                    <p class="rate_text">ช่วยกำหนดอนาคตของการค้า</p>
                                                    <h3>20%</h3>
                                                    <p class="Sale_text">For the First Sale</p><a
                                                        class="theme_btn small_btn2" {{-- href="https://ecommerce.eimaths-th.com/my-affiliate"> --}}
                                                        href="#">
                                                        &nbsp;สร้าง
                                                        Affiliate ลิงค์</a>
                                                    <p class="discription_text">เสนอค่าคอมมิชชั่น 20%
                                                        สำหรับการขายครั้งแรกที่แนะนำ
                                                        หากผู้เยี่ยมชมของคุณซื้อสินค้าของเรามูลค่า 10,000 บาท
                                                        คุณจะได้รับค่าคอมมิชชั่น 2,000</p>
                                                </div>
                                                <div class="commision_right flex-fill">
                                                    <div class="commision_payment_lists">
                                                        <div class="commision_payment_list">
                                                            <div class="thumb"><img alt=""
                                                                    src="{{ asset('frontend/infixlmstheme/img/snippets/preview/affiliate/wallet_1.svg') }}">
                                                                {{-- src="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/snippets/preview/affiliate/wallet_1.svg"> --}}
                                                            </div>
                                                            <div class="comission_payment_text">
                                                                <h4>วิธีและกำหนดการจ่ายเงิน</h4>
                                                                <p>การจ่ายค่าคอมมิชชั่นจะดำเนินการทุกเดือนผ่านบัญชีธนาคาร
                                                                    ตามที่คุณเลือก</p>
                                                            </div>
                                                        </div>
                                                        <div class="commision_payment_list">
                                                            <div class="thumb"><img alt=""
                                                                    src="{{ asset('frontend/infixlmstheme/img/snippets/preview/affiliate/wallet_2.svg') }}">
                                                                {{-- src="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/snippets/preview/affiliate/wallet_2.svg"> --}}
                                                            </div>
                                                            <div class="comission_payment_text">
                                                                <h4>อัตราค่าคอมมิชชั่น</h4>
                                                                <p>คุณจะได้รับค่าคอมมิชชั่นตามอัตราที่กำหนด
                                                                    โดยขึ้นอยู่กับประเภทของผลิตภัณฑ์หรือบริการที่แนะนำ
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <div class="commision_payment_list">
                                                            <div class="thumb"><img alt=""
                                                                    src="{{ asset('frontend/infixlmstheme/img/snippets/preview/affiliate/wallet_3.svg') }}">
                                                                {{-- src="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/snippets/preview/affiliate/wallet_3.svg"> --}}
                                                            </div>
                                                            <div class="comission_payment_text">
                                                                <h4>กำหนดเวลาการจ่ายเงิน</h4>
                                                                <p>ค่าคอมมิชชั่นจะถูกจ่ายภายใน 30
                                                                    วันหลังจากสิ้นสุดรอบบัญชีหรือยอดเงินถึงต่ำที่กำหนด
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <style type="text/css" data-type="component-text">
                            .about .mt-40 {
                                margin-top: 35px;
                            }

                            @media only screen and (max-width: 767px) {
                                .about .mt-40 {
                                    margin-top: 0
                                }
                            }

                            .counter {
                                padding: 0 32px
                            }

                            @media only screen and (min-width: 992px) and (max-width: 1279px) {
                                .counter {
                                    padding: 0
                                }
                            }

                            @media only screen and (max-width: 991px) {
                                .counter {
                                    padding: 0
                                }
                            }

                            .counter #counters {
                                position: relative;
                                z-index: 1
                            }

                            .counter #counters .row {
                                margin-top: -36px
                            }

                            @media (min-width: 480px) {
                                .counter #counters .col-sm-6 {
                                    width: 50%
                                }
                            }

                            .counter-shape {
                                position: absolute;
                                top: 50%;
                                left: 50%;
                                transform: translate(-50%, -50%);
                                --width: 255px;
                                width: var(--width);
                                height: var(--width);
                                border-radius: 100%;
                                background-color: var(--system_primery_color)
                            }

                            @media only screen and (max-width: 767px) {
                                .counter-shape {
                                    --width: 175px
                                }
                            }

                            .counter-shape::before,
                            .counter-shape::after {
                                content: "";
                                width: 100%;
                                height: 100%;
                                border-radius: 100%;
                                border: 1px dashed var(--system_primery_color);
                                position: absolute;
                                top: 0;
                                left: 0;
                                opacity: .3;
                            }

                            html[dir=rtl] .counter-shape::before,
                            html[dir=rtl] .counter-shape::after {
                                left: auto;
                                right: 0;
                            }

                            .counter-shape::before {
                                transform: scale(1.3)
                            }

                            .counter-shape::after {
                                transform: scale(1.75)
                            }

                            .counter-item {
                                padding: 40px;
                                border-radius: 24px;
                                position: relative;
                                z-index: 1;
                                background-color: #fff;
                                margin-top: 36px;
                                max-width: 270px;
                            }

                            @media only screen and (min-width: 1280px) and (max-width: 1439px) {
                                .counter-item {
                                    padding: 30px
                                }
                            }

                            @media only screen and (min-width: 992px) and (max-width: 1279px) {
                                .counter-item {
                                    padding: 30px
                                }
                            }

                            @media only screen and (max-width: 767px) {
                                .counter-item {
                                    margin-top: 24px !important;
                                    padding: 24px;
                                }
                            }

                            @media only screen and (max-width: 479px) {
                                .counter-item {
                                    max-width: 100%;
                                }
                            }

                            .counter-item-icon {
                                --icon: 80px;
                                width: var(--icon);
                                height: var(--icon);
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                border-radius: 100%;
                                margin-bottom: 14px
                            }

                            @media only screen and (min-width: 1280px) and (max-width: 1439px) {
                                .counter-item-icon {
                                    margin-bottom: 10px
                                }
                            }

                            @media only screen and (min-width: 992px) and (max-width: 1279px) {
                                .counter-item-icon {
                                    --icon: 70px
                                }
                            }

                            @media only screen and (max-width: 767px) {
                                .counter-item-icon {
                                    --icon: 60px
                                }
                            }

                            .counter-item-icon>* {
                                width: 36px
                            }

                            @media only screen and (max-width: 767px) {
                                .counter-item-icon>* {
                                    width: 24px
                                }
                            }

                            .counter-item label {
                                display: inline-flex
                            }

                            .counter-item h4 {
                                font-size: 32px;
                                line-height: 1.25;
                                line-height: 1;
                                margin-bottom: 5px
                            }

                            @media only screen and (min-width: 992px) and (max-width: 1279px) {
                                .counter-item h4 {
                                    font-size: 28px
                                }
                            }

                            @media only screen and (min-width: 768px) and (max-width: 991px) {
                                .counter-item h4 {
                                    font-size: 26px
                                }
                            }

                            @media only screen and (max-width: 767px) {
                                .counter-item h4 {
                                    font-size: 24px
                                }
                            }

                            @media only screen and (min-width: 1280px) and (max-width: 1439px) {
                                .counter-item h4 {
                                    font-size: 21px
                                }
                            }

                            .counter-item p {
                                font-size: 20px;
                                line-height: 1.5;
                                color: currentColor;
                            }

                            @media only screen and (min-width: 992px) and (max-width: 1279px) {
                                .counter-item p {
                                    font-size: 18px
                                }
                            }

                            @media only screen and (max-width: 991px) {
                                .counter-item p {
                                    font-size: 18px
                                }
                            }

                            .about-content {
                                position: relative;
                                padding-left: 50px
                            }

                            html[dir=rtl] .about-content {
                                padding-left: 0;
                                padding-right: 50px;
                            }

                            @media only screen and (min-width: 992px) and (max-width: 1279px) {
                                .about-content {
                                    padding-left: 20px
                                }

                                html[dir=rtl] .about-content {
                                    padding-left: 0;
                                    padding-right: 20px;
                                }
                            }

                            @media only screen and (max-width: 991px) {
                                .about-content {
                                    margin-top: 40px;
                                    padding-bottom: 20px;
                                    padding-left: 0
                                }

                                html[dir=rtl] .about-content {
                                    padding-right: 0;
                                }
                            }

                            @media only screen and (max-width: 767px) {
                                .about-content {
                                    margin-top: 26px
                                }
                            }

                            .about-content::after {
                                content: "";
                                width: 100%;
                                height: 100%;
                                border-radius: 100%;
                                background-color: rgba(255, 255, 255, 0.2);
                                position: absolute;
                                top: 0;
                                left: 0;
                                z-index: -1;
                                filter: blur(150px)
                            }

                            html[dir=rtl] .about-content::after {
                                left: auto;
                                right: 0;
                            }

                            .about-content h3 {
                                font-size: 48px;
                                line-height: 1.25;
                                font-weight: 600;
                                margin-bottom: 24px
                            }

                            @media only screen and (min-width: 1280px) and (max-width: 1439px) {
                                .about-content h3 {
                                    font-size: 42px
                                }
                            }

                            @media only screen and (min-width: 992px) and (max-width: 1279px) {
                                .about-content h3 {
                                    font-size: 36px
                                }
                            }

                            @media only screen and (min-width: 768px) and (max-width: 991px) {
                                .about-content h3 {
                                    font-size: 32px
                                }
                            }

                            @media only screen and (max-width: 767px) {
                                .about-content h3 {
                                    font-size: 28px
                                }
                            }

                            @media only screen and (min-width: 992px) and (max-width: 1279px) {
                                .about-content h3 {
                                    margin-bottom: 18px
                                }
                            }

                            @media only screen and (min-width: 768px) and (max-width: 991px) {
                                .about-content h3 {
                                    font-size: 28px
                                }
                            }

                            @media only screen and (max-width: 991px) {
                                .about-content h3 {
                                    margin-bottom: 16px
                                }
                            }

                            .about-content p {
                                color: var(--system_paragraph_color);
                                line-height: 1.5;
                                margin-bottom: 26px
                            }

                            @media only screen and (min-width: 992px) and (max-width: 1279px) {
                                .about-content p {
                                    margin-bottom: 16px
                                }
                            }

                            @media only screen and (max-width: 991px) {
                                .about-content p {
                                    margin-bottom: 14px
                                }
                            }

                            @media only screen and (max-width: 479px) {
                                .about-content p {
                                    font-size: 14px;
                                    line-height: 1.683
                                }
                            }

                            .about-content ul {
                                width: 56%
                            }

                            @media only screen and (min-width: 1280px) and (max-width: 1439px) {
                                .about-content ul {
                                    width: 66%
                                }
                            }

                            @media only screen and (min-width: 992px) and (max-width: 1279px) {
                                .about-content ul {
                                    width: 66%
                                }
                            }

                            @media only screen and (max-width: 991px) {
                                .about-content ul {
                                    width: 100%;
                                    margin-top: 20px
                                }
                            }

                            .about-content ul li {
                                color: var(--system_paragraph_color);
                                --width: 24px
                            }

                            .about-content ul li:not(:last-child) {
                                margin-bottom: 20px
                            }

                            .about-content ul li .icon {
                                color: #38485C;
                                width: var(--width);
                                height: var(--width);
                                flex: 0 0 auto;
                                position: relative;
                                top: 3px
                            }

                            .about-content ul li .icon img,
                            .about-content ul li .icon>* {
                                width: 100%;
                                height: 100%;
                                object-fit: contain;
                                position: absolute;
                                top: 0;
                                left: 0;
                                z-index: -1
                            }

                            html[dir=rtl] .about-content ul li .icon img,
                            html[dir=rtl] .about-content ul li .icon>* {
                                left: auto;
                                right: 0;
                            }

                            .about-content ul li .icon svg path {
                                fill: #CEE8FF;
                                stroke: var(--system_primery_color)
                            }

                            .about-content ul li .content {
                                width: calc(100% - var(--width));
                                flex: 0 0 auto;
                                padding-left: 18px
                            }

                            html[dir=rtl] .about-content ul li .content {
                                padding-left: 0;
                                padding-right: 18px;
                            }

                            .about-content a {
                                --btn-padding-y: 9px;
                                min-width: 120px
                            }

                            @media only screen and (min-width: 992px) and (max-width: 1279px) {
                                .about-content a {
                                    margin-top: 10px
                                }
                            }

                            @media only screen and (max-width: 991px) {
                                .about-content a {
                                    margin-top: 10px
                                }
                            }
                        </style>
                        <div class="row">
                            <div class="col-sm-12 ui-resizable" data-type="container-content">
                                <div class="text-center" data-type="component-text">
                                    <div class="photo-panel"><img class="w-60 h-60 img-cover"
                                            src="{{ asset('uploads/main/images/15-09-2024/66e7009b50afa.png') }}"
                                            {{-- src="https://ecommerce.eimaths-th.com/public/uploads/main/images/15-09-2024/66e7009b50afa.png" --}} style="height: 80%; width: 80%;"></div>
                                </div>
                            </div>
                        </div>
                        <div class="about section-margin-lg" data-type="component-text">
                            <div class="container">
                                <div class="row align-items-center">
                                    <div class="col-lg-6">
                                        <div>
                                            <div class="row" id="counters">
                                                <div class="counter-shape">&nbsp;</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="about-content">
                                            <h4>&nbsp;</h4>
                                            <h4>&nbsp;</h4>
                                            <h4>&nbsp;</h4>
                                            <h4><strong>What is<br>eiMaths-TH&nbsp;Affiliate</strong></h4>
                                            <p>Affiliate เป็นระบบที่ให้คุณร่วมเป็นPartnerกับเรา
                                                โดยการโปรโมตผลิตภัณฑ์หรือบริการผ่านลิงก์เฉพาะของคุณ
                                                ทุกครั้งที่มีการซื้อหรือทำรายการผ่านลิงก์ของคุณ
                                                คุณจะได้รับค่าคอมมิชชั่นจากยอดขาย</p>
                                            <ul class="mb-4 mb-lg-5">
                                                <li class="d-flex flex-wrap"><span class="icon text-primary"><svg
                                                            fill="none" height="26" viewBox="0 0 26 26"
                                                            width="26" xmlns="http://www.w3.org/2000/svg">
                                                            <path
                                                                d="M13 25C19.6274 25 25 19.6274 25 13C25 6.37258 19.6274 1 13 1C6.37258 1 1 6.37258 1 13C1 19.6274 6.37258 25 13 25Z"
                                                                fill="#CEE8FF" stroke="currentColor"
                                                                stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"></path>
                                                            <path
                                                                d="M8.20312 14.2002L11.694 17.8002C13.3367 13.0814 14.7048 11.0108 17.8031 8.2002"
                                                                stroke="currentColor" stroke-linecap="round"
                                                                stroke-linejoin="round" stroke-width="2"></path>
                                                        </svg> </span> <span class="content"><span
                                                            style="color: var(--system_paragraph_color); background-color: var(--bs-body-bg); font-family: var(--font_family2); font-size: var(--bs-body-font-size); text-align: var(--bs-body-text-align);">คุณสามารถสร้างรายได้จากการแนะนำลูกค้าใหม่ผ่านลิงก์ที่เราให้
                                                            ระบบของเราจะติดตามผลและรายงานยอดคลิก ยอดขาย
                                                            และค่าคอมมิชชั่นแบบเรียลไทม์
                                                            เพื่อให้คุณทราบถึงผลการทำงานของคุณอย่างต่อเนื่อง</span></span>
                                                </li>
                                                <li class="d-flex flex-wrap"><span class="icon text-primary"><svg
                                                            fill="none" height="26" viewBox="0 0 26 26"
                                                            width="26" xmlns="http://www.w3.org/2000/svg">
                                                            <path
                                                                d="M13 25C19.6274 25 25 19.6274 25 13C25 6.37258 19.6274 1 13 1C6.37258 1 1 6.37258 1 13C1 19.6274 6.37258 25 13 25Z"
                                                                fill="#CEE8FF" stroke="currentColor"
                                                                stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"></path>
                                                            <path
                                                                d="M8.20312 14.2002L11.694 17.8002C13.3367 13.0814 14.7048 11.0108 17.8031 8.2002"
                                                                stroke="currentColor" stroke-linecap="round"
                                                                stroke-linejoin="round" stroke-width="2"></path>
                                                        </svg> </span> <span class="content"><span
                                                            style="color: var(--system_paragraph_color); background-color: var(--bs-body-bg); font-family: var(--font_family2); font-size: var(--bs-body-font-size); text-align: var(--bs-body-text-align);">เพื่อเข้าร่วมโปรแกรม
                                                            คุณต้องยอมรับและปฏิบัติตามข้อตกลงที่ระบุไว้
                                                            ซึ่งรวมถึงการไม่ใช้วิธีการโปรโมตที่ผิดกฎหมายหรือสแปม
                                                            และการปฏิบัติตามแนวทางที่ระบุในนโยบายของเรา</span></span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12 ui-resizable" data-type="container-content">
                        <div data-aoraeditor-categories="Affiliate Page"
                            data-aoraeditor-title="Affiliate How Does Work Section"
                            data-preview="{{ asset('frontend/infixlmstheme/img/snippets/preview/affiliate/how_work_section.png') }}"
                            {{-- data-preview="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/snippets/preview/affiliate/how_work_section.png" --}} data-type="component-text">
                            <div class="lms_section section_padding2 pb-0">
                                <div class="container">
                                    <div class="row justify-content-center">
                                        <div class="col-xl-10">
                                            <div class="work_title_area">
                                                <div class="section__title4 "><span class="subheading_text">Affiliate
                                                        ของเราทำงานอย่างไร&nbsp;<span
                                                            style="color: var(--system_primery_color); background-color: var(--bs-body-bg); font-family: var(--font_family2); text-align: var(--bs-body-text-align);">?</span></span>
                                                    <h4><strong>เราได้ปรับการรายงานและการติดตามผลสำหรับ
                                                            Partner<br>ให้ราบรื่นยิ่งขึ้น</strong></h4>
                                                </div>
                                                <div class="work_title_desc">
                                                    <p>ระบบของเราช่วยให้ Affiliate
                                                        สามารถติดตามผลการทำงานได้อย่างละเอียด<br>ไม่ว่าจะเป็นจำนวนคลิก
                                                        ยอดขาย
                                                        และรายได้ที่เกิดจากการแนะนำของคุณ<br>ข้อมูลเหล่านี้สามารถดูได้ผ่านแดชบอร์ดที่ใช้งานง่าย
                                                    </p>
                                                    <p>ระบบรายงานจะแสดงข้อมูลแบบเรียลไทม์<br>เพื่อให้คุณทราบถึงผลลัพธ์และสถานะการทำงานได้อย่างแม่นยำและทันเหตุการณ์<br>ช่วยให้คุณปรับกลยุทธ์ได้อย่างมีประสิทธิภาพ<br><br>เรามีแบนเนอร์,
                                                        ลิงก์ติดตาม, และเนื้อหาสำหรับการโฆษณาที่
                                                        Affiliate<br>สามารถนำไปใช้โปรโมท
                                                        คู่มือหรือบทความที่ให้คำแนะนำเกี่ยวกับวิธีการทำการตลาด<br>เพื่อเพิ่มโอกาสในการขาย
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="row justify-content-center">
                                                <div class="col-xl-4 col-lg-4 col-md-6">
                                                    <div class="process_widget_box text-center mb_30">
                                                        <div class="icon"><img alt=""
                                                                src="{{ asset('frontend/infixlmstheme/img/snippets/preview/affiliate/process_icon_1.svg') }}">
                                                            {{-- src="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/snippets/preview/affiliate/process_icon_1.svg"> --}}
                                                        </div>
                                                        <h4>ลงทะเบียนกับเรา</h4>
                                                        <p>สมัครเป็น Partner
                                                            กับเราและเริ่มสร้างรายได้จากการโปรโมตผลิตภัณฑ์
                                                            ง่ายและรวดเร็ว!</p>
                                                        <p>&nbsp;</p>
                                                    </div>
                                                </div>
                                                <div class="col-xl-4 col-lg-4 col-md-6">
                                                    <div class="process_widget_box text-center mb_30">
                                                        <div class="icon"><img alt=""
                                                                src="{{ asset('frontend/infixlmstheme/img/snippets/preview/affiliate/process_icon_2.svg') }}">
                                                            {{-- src="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/snippets/preview/affiliate/process_icon_2.svg"> --}}
                                                        </div>
                                                        <h4>ยืนยัน Email</h4>
                                                        <p>ย่าลืมยืนยันอีเมลของคุณผ่านลิงก์ที่เราส่งไปยังกล่องจดหมายของคุณ
                                                            เพื่อเปิดใช้งานบัญชีและเริ่มต้นการใช้งานระบบของเราได้ทันที
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="col-xl-4 col-lg-4 col-md-6">
                                                    <div class="process_widget_box text-center mb_30">
                                                        <div class="icon"><img alt=""
                                                                src="{{ asset('frontend/infixlmstheme/img/snippets/preview/affiliate/process_icon_3.svg') }}">
                                                            {{-- src="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/snippets/preview/affiliate/process_icon_3.svg"> --}}
                                                        </div>
                                                        <h4>สร้างAffiliateของคุณ</h4>
                                                        <p>สร้างบัญชี Affiliate
                                                            และเริ่มโปรโมตเพื่อรับค่าคอมมิชชั่นทันที<span
                                                                style="font-family: var(--font_family2); background-color: var(--bs-body-bg);">และรับค่าคอมมิชชั่นจากยอดขายที่มาจากลิงก์ของคุณ</span>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12 ui-resizable" data-type="container-content">
                        <div data-aoraeditor-categories="Affiliate Page"
                            data-aoraeditor-title="Affiliate Rules & Requirement Section"
                            data-preview="{{ asset('frontend/infixlmstheme/img/snippets/preview/affiliate/rules_section.png') }}"
                            {{-- data-preview="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/snippets/preview/affiliate/rules_section.png" --}} data-type="component-text">
                            <div class="lms_section section_padding2 affiliate_bg">
                                <div class="container">
                                    <div class="row justify-content-center">
                                        <div class="col-xl-6 col-lg-8 col-md-8">
                                            <div class="section__title4 text-center margin_52"><span
                                                    class="subheading_text">กฎและข้อกำหนด</span>
                                                <h3>สิ่งที่ควรทำและไม่ควรทำสำหรับ<br>พาร์ทเนอร์ Affiliate ของ eiMath-TH
                                                </h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row justify-content-center">
                                        <div class="col-xl-10">
                                            <div class="Requirements_boxs mb_80">
                                                <div class="single_Requirements_box">
                                                    <div class="Requirements_box_head">
                                                        <h3>สิ่งที่ควรทำ</h3>
                                                    </div>
                                                    <div class="Requirements_box_body">
                                                        <ul>
                                                            <li>
                                                                <div class="icon"><img alt=""
                                                                        src="{{ asset('frontend/infixlmstheme/img/snippets/preview/affiliate/green_check.svg') }}">
                                                                    {{-- src="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/snippets/preview/affiliate/green_check.svg"> --}}
                                                                </div>
                                                                <p>มีส่วนร่วมกับชุมชน Facebook Tiktok IG&nbsp;
                                                                    และแนะนำโซลูชันของเราในกระทู้ที่เกี่ยวข้อง</p>
                                                            </li>
                                                            <li>
                                                                <div class="icon"><img alt=""
                                                                        src="{{ asset('frontend/infixlmstheme/img/snippets/preview/affiliate/green_check.svg') }}">
                                                                    {{-- src="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/snippets/preview/affiliate/green_check.svg"> --}}
                                                                </div>
                                                                <p>เขียนรีวิวและบทความในบล็อก
                                                                    เกี่ยวกับผลิตภัณฑ์และโซลูชันของเรา</p>
                                                            </li>
                                                            <li>
                                                                <div class="icon"><img alt=""
                                                                        src="{{ asset('frontend/infixlmstheme/img/snippets/preview/affiliate/green_check.svg') }}">
                                                                    {{-- src="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/snippets/preview/affiliate/green_check.svg"> --}}
                                                                </div>
                                                                <p>เผยแพร่ข้อมูลเชิงบวกเกี่ยวกับผลิตภัณฑ์และคุณสมบัติต่าง
                                                                    ๆ ผ่านช่องทางโซเชียล</p>
                                                            </li>
                                                            <li>
                                                                <div class="icon"><img alt=""
                                                                        src="{{ asset('frontend/infixlmstheme/img/snippets/preview/affiliate/green_check.svg') }}">
                                                                    {{-- src="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/snippets/preview/affiliate/green_check.svg"> --}}
                                                                </div>
                                                                <p>เผยแพร่การเปรียบเทียบผลิตภัณฑ์, white papers,
                                                                    อินโฟกราฟิก, รูปภาพ และ กรณีศึกษา</p>
                                                            </li>
                                                            <li>
                                                                <div class="icon"><img alt=""
                                                                        src="{{ asset('frontend/infixlmstheme/img/snippets/preview/affiliate/green_check.svg') }}">
                                                                    {{-- src="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/snippets/preview/affiliate/green_check.svg"> --}}
                                                                </div>
                                                                <p>สร้างวิดีโอสาธิตและบล็อกวิดีโอเกี่ยวกับผลิตภัณฑ์ของเรา
                                                                    บนช่องทางของคุณเอง</p>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                                <div class="single_Requirements_box">
                                                    <div class="Requirements_box_head style2">
                                                        <h3>สิ่งที่ไม่ควรทำ</h3>
                                                    </div>
                                                    <div class="Requirements_box_body">
                                                        <ul>
                                                            <li>
                                                                <div class="icon"><img alt=""
                                                                        src="{{ asset('frontend/infixlmstheme/img/snippets/preview/affiliate/red_cross.svg') }}">
                                                                    {{-- src="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/snippets/preview/affiliate/red_cross.svg"> --}}
                                                                </div>
                                                                <p>ไม่ควรสแปมลิงก์
                                                                    โดยไม่มีการสนทนาที่มีความหมายและในกระทู้ที่ไม่เกี่ยวข้อง
                                                                </p>
                                                            </li>
                                                            <li>
                                                                <div class="icon"><img alt=""
                                                                        src="{{ asset('frontend/infixlmstheme/img/snippets/preview/affiliate/red_cross.svg') }}">
                                                                    {{-- src="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/snippets/preview/affiliate/red_cross.svg"> --}}
                                                                </div>
                                                                <p>ห้ามใช้วิธีการ Blackhat
                                                                    และวิธีการที่ไม่ถูกต้องตามจริยธรรมในการจัดการกับเครื่องมือค้นหา
                                                                </p>
                                                            </li>
                                                            <li>
                                                                <div class="icon">&nbsp;</div>
                                                            </li>
                                                            <li>
                                                                <div class="icon">&nbsp;</div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="apply_btn text-center mb_30"><a
                                                    class="theme_btn min_windth_200 text-center" {{-- href="https://ecommerce.eimaths-th.com/affiliate/registration"> --}}
                                                    href="#">
                                                    สมัครเลย!!</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12 ui-resizable" data-type="container-content">
                        <div data-type="component-text">
                            <div class="breadcrumb_area bradcam_bg_1 position-relative">
                                <div class="breadcrumb_img w-100 h-100 position-absolute bottom-0 left-0 "><img
                                        alt="" class="w-100 h-100 img-cover"
                                        src="{{ asset('frontend/infixlmstheme/img/banner/bradcam_bg_1.jpg') }}">
                                    {{-- src="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/banner/bradcam_bg_1.jpg"> --}}
                                </div>
                                <div class="container">
                                    <div class="row">
                                        <div class="col-lg-9 offset-lg-1">
                                            <div class="breadcam_wrap"><span>About Company</span>
                                                <h3>The leading global marketplace.</h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="contact_section " data-type="component-text">
                            <div class="container">
                                <div class="row justify-content-center">
                                    <div class="col-xl-10">
                                        <div class="contact_address">
                                            <div class="row justify-content-center">
                                                <div class="col-xl-12">
                                                    <div class="row justify-content-between">
                                                        <div class="col-lg-5">
                                                            <div class="contact_info mb_30">
                                                                <h4 class="mb-0">ข้อมูลการติดต่อ</h4>
                                                                <div class="address_lines">
                                                                    <div class="single_address_line d-flex">
                                                                        <div class="address_info">
                                                                            <p>The Crystal SB ราชพฤกษ์ ชั้น 3, 555/9
                                                                                อำเภอบางกรวย จังหวัดนนทบุรี 11130</p>
                                                                        </div>
                                                                    </div>
                                                                    <div class="single_address_line d-flex">
                                                                        <div class="address_info">
                                                                            <p>0 616-208-666</p>
                                                                        </div>
                                                                    </div>
                                                                    <div class="single_address_line d-flex">
                                                                        <div class="address_info">
                                                                            <p>info@eimaths-th.com</p>
                                                                            <p>ส่งคำถามของคุณมาหาเราได้ตลอดเวลา!</p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <style type="text/css" data-type="component-text">
                            .bg-deep-green {
                                background-color: #6CB552 !important
                            }

                            @media only screen and (min-width: 992px) and (max-width: 1279px) {
                                .counter .row {
                                    margin-top: -70px
                                }
                            }

                            @media only screen and (max-width: 991px) {
                                .counter .row {
                                    margin-top: -70px
                                }
                            }

                            @media only screen and (max-width: 767px) {
                                .counter>.container {
                                    max-width: 92%
                                }
                            }

                            .counter-item {
                                padding: 40px;
                                border-radius: 40px;
                                position: relative;
                                z-index: 1;
                                max-width: 300px;
                            }

                            html[dir=rtl] .counter-item::before,
                            html[dir=rtl] .counter-item::after {
                                left: auto;
                                right: 0;
                            }

                            @media only screen and (min-width: 1280px) and (max-width: 1439px) {
                                .counter-item {
                                    padding: 30px
                                }
                            }

                            @media only screen and (min-width: 992px) and (max-width: 1279px) {
                                .counter-item {
                                    margin-top: 70px
                                }
                            }

                            @media only screen and (max-width: 991px) {
                                .counter-item {
                                    margin-top: 70px
                                }
                            }

                            .counter-item::before,
                            .counter-item::after {
                                content: "";
                                width: 100%;
                                height: 100%;
                                border-radius: 40px;
                                position: absolute;
                                top: 0;
                                left: 0;
                                transform: rotate(5deg);
                                z-index: -1;
                                background-color: #EEE6F5;
                                opacity: .4
                            }

                            html[dir=rtl] .counter-item::before,
                            html[dir=rtl] .counter-item::after {
                                left: auto;
                                right: 0;
                            }

                            .counter-item::after {
                                z-index: -2;
                                opacity: .3;
                                transform: rotate(10deg)
                            }

                            .counter-item.bg-primary::before,
                            .counter-item.bg-primary::after {
                                background-color: var(--system_primery_color)
                            }

                            .counter-item.bg-deep-green::before,
                            .counter-item.bg-deep-green::after {
                                background-color: #6CB552
                            }

                            .counter-item.bg-orange::before,
                            .counter-item.bg-orange::after {
                                background-color: #FEB74C
                            }

                            .counter-item.bg-green::before,
                            .counter-item.bg-green::after {
                                background-color: #16CE8C
                            }

                            .counter-item-icon {
                                margin-bottom: 1rem
                            }

                            @media only screen and (min-width: 1280px) and (max-width: 1439px) {
                                .counter-item-icon {
                                    margin-bottom: 10px
                                }
                            }

                            .counter-item label {
                                display: inline-flex
                            }

                            .counter-item h4 {
                                margin-bottom: 16px;
                                font-size: 24px;
                                line-height: 1
                            }

                            @media only screen and (min-width: 992px) and (max-width: 1279px) {
                                .counter-item h4 {
                                    font-size: 22px
                                }
                            }

                            @media only screen and (max-width: 991px) {
                                .counter-item h4 {
                                    font-size: 20px
                                }
                            }

                            @media only screen and (min-width: 1280px) and (max-width: 1439px) {
                                .counter-item h4 {
                                    margin-bottom: 10px;
                                    font-size: 21px
                                }
                            }

                            .counter-item h4 span {
                                display: block;
                                font-size: 36px;
                                line-height: 1.52778;
                                opacity: .8
                            }

                            @media only screen and (min-width: 1280px) and (max-width: 1439px) {
                                .counter-item h4 span {
                                    font-size: 32px
                                }
                            }

                            @media only screen and (min-width: 992px) and (max-width: 1279px) {
                                .counter-item h4 span {
                                    font-size: 30px
                                }
                            }

                            @media only screen and (min-width: 768px) and (max-width: 991px) {
                                .counter-item h4 span {
                                    font-size: 28px
                                }
                            }

                            @media only screen and (max-width: 767px) {
                                .counter-item h4 span {
                                    font-size: 24px
                                }
                            }

                            .counter-item p {
                                color: #fff;
                            }
                        </style>
                        <div class="about section-margin" data-type="component-text">
                            <div class="container">
                                <div class="row" id="counters">
                                    <div class="col-xl-3 col-md-6">
                                        <div class="counter-item bg-primary text-white">
                                            <div class="counter-item-icon"><svg fill="none" height="37"
                                                    viewBox="0 0 37 37" width="37"
                                                    xmlns="http://www.w3.org/2000/svg">
                                                    <path
                                                        d="M18.5726 34.4966C16.7247 34.4966 16.7091 33.1522 15.1171 32.0052C12.3478 30.0101 8.09093 27.6623 4.70159 27.2026C4.09404 27.1354 3.53292 26.8455 3.12654 26.3889C2.72016 25.9323 2.49731 25.3413 2.50102 24.73V4.82601C2.501 4.46888 2.57834 4.11597 2.72773 3.79158C2.87712 3.46719 3.09501 3.17901 3.36641 2.94687C3.63303 2.71896 3.9451 2.55037 4.28187 2.45233C4.61865 2.35428 4.97244 2.329 5.31973 2.37819C10.4238 3.2253 15.093 5.76898 18.5726 9.59803V34.4966Z"
                                                        stroke="white" stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="3"></path>
                                                    <path
                                                        d="M18.5724 34.4966C20.4203 34.4966 20.4359 33.1522 22.0279 32.0052C24.7973 30.0101 29.0541 27.6623 32.4434 27.2026C33.051 27.1354 33.6121 26.8455 34.0185 26.3889C34.4249 25.9323 34.6477 25.3413 34.644 24.73V4.82601C34.644 4.46888 34.5667 4.11597 34.4173 3.79158C34.2679 3.46719 34.05 3.17901 33.7786 2.94687C33.512 2.71896 33.1999 2.55037 32.8631 2.45233C32.5264 2.35428 32.1726 2.329 31.8253 2.37819C26.7212 3.2253 22.052 5.76898 18.5724 9.59803V34.4966Z"
                                                        stroke="white" stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="3"></path>
                                                </svg></div>
                                            <h4 class="currentColor"><span class="d-flex align-items-center">1</span>
                                                ข้อตกลงทั่วไป</h4>
                                            <p>ข้อตกลงนี้กำหนดเงื่อนไขและข้อกำหนดในการเข้าร่วมโปรแกรม Affiliate
                                                รวมถึงสิทธิและความรับผิดชอบของทั้งสองฝ่าย</p>
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-md-6">
                                        <div class="counter-item bg-deep-green text-white">
                                            <div class="counter-item-icon"><svg fill="none" height="37"
                                                    viewBox="0 0 40 37" width="40"
                                                    xmlns="http://www.w3.org/2000/svg">
                                                    <path
                                                        d="M27.1709 18.7017C30.9915 18.7017 34.0887 15.6045 34.0887 11.7839C34.0887 8.82843 32.2354 6.30583 29.6275 5.31494"
                                                        stroke="white" stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="3"></path>
                                                    <path
                                                        d="M15.5303 17.3302C19.6718 17.3302 23.0292 13.9729 23.0292 9.83137C23.0292 5.68987 19.6718 2.33252 15.5303 2.33252C11.3888 2.33252 8.03149 5.68987 8.03149 9.83137C8.03149 13.9729 11.3888 17.3302 15.5303 17.3302Z"
                                                        stroke="white" stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="3"></path>
                                                    <path
                                                        d="M27.0165 27.8659C30.6846 30.3089 28.2077 35.0208 23.8006 35.0208H7.25036C2.8432 35.0208 0.36634 30.3089 4.0344 27.8659C7.32457 25.6746 11.2759 24.3975 15.5255 24.3975C19.7751 24.3975 23.7264 25.6746 27.0165 27.8659Z"
                                                        stroke="white" stroke-width="3"></path>
                                                    <path
                                                        d="M36.6195 28.4199L35.788 29.6684L36.6195 28.4199ZM23.1628 33.5205C22.3344 33.5205 21.6628 34.192 21.6628 35.0205C21.6628 35.8489 22.3344 36.5205 23.1628 36.5205V33.5205ZM34.2513 25.4271C33.4917 25.0965 32.6079 25.4443 32.2774 26.2039C31.9468 26.9635 32.2946 27.8473 33.0542 28.1779L34.2513 25.4271ZM23.1628 36.5205H33.6527V33.5205H23.1628V36.5205ZM33.6527 36.5205C36.2184 36.5205 38.3575 35.1415 39.2822 33.2141C39.7481 32.2428 39.9061 31.1179 39.6022 30.0064C39.2959 28.8864 38.5558 27.9073 37.451 27.1715L35.788 29.6684C36.3752 30.0594 36.6185 30.4688 36.7084 30.7977C36.8007 31.1351 36.7678 31.5195 36.5773 31.9165C36.189 32.726 35.1528 33.5205 33.6527 33.5205V36.5205ZM33.0542 28.1779C34.0124 28.5949 34.9267 29.0947 35.788 29.6684L37.451 27.1715C36.4436 26.5005 35.3735 25.9155 34.2513 25.4271L33.0542 28.1779Z"
                                                        fill="white"></path>
                                                </svg></div>
                                            <h4 class="currentColor"><span class="d-flex align-items-center"><span
                                                        class="counter p-0 m-0 ">2</span></span>ความเป็นส่วนตัว</h4>
                                            <p>อธิบายวิธีการรวบรวม ใช้ และปกป้องข้อมูลของพาทเนอร์
                                                รวมถึงสิทธิ์ในการจัดการข้อมูล</p>
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-md-6">
                                        <div class="counter-item bg-orange text-white">
                                            <div class="counter-item-icon"><svg fill="none" height="40"
                                                    viewBox="0 0 40 40" width="40"
                                                    xmlns="http://www.w3.org/2000/svg">
                                                    <path
                                                        d="M20 38C29.9411 38 38 29.9411 38 20C38 10.0589 29.9411 2 20 2C10.0589 2 2 10.0589 2 20C2 29.9411 10.0589 38 20 38Z"
                                                        stroke="white" stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="3"></path>
                                                    <path
                                                        d="M8.23064 26.9231C9.51592 26.9231 10.7486 26.4125 11.6574 25.5037C12.5662 24.5948 13.0768 23.3622 13.0768 22.0769V17.9231C13.0768 16.6378 13.5874 15.4051 14.4962 14.4963C15.405 13.5875 16.6377 13.0769 17.9229 13.0769C19.2082 13.0769 20.4409 12.5663 21.3497 11.6575C22.2585 10.7487 22.7691 9.51604 22.7691 8.23076V2.21168C21.8665 2.07231 20.9417 2 20 2C10.0589 2 2 10.0589 2 20C2 22.4533 2.49078 24.7919 3.37953 26.9231H8.23064Z"
                                                        stroke="white" stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="3"></path>
                                                    <path
                                                        d="M37.9978 19.722C36.6117 19.0032 35.0748 18.6238 33.5134 18.6152H27.615C26.3297 18.6152 25.097 19.1258 24.1882 20.0346C23.2794 20.9435 22.7688 22.1761 22.7688 23.4614C22.7688 24.7467 23.2794 25.9793 24.1882 26.8881C25.097 27.797 26.3297 28.3075 27.615 28.3075C28.533 28.3075 29.4135 28.6722 30.0626 29.3214C30.7118 29.9706 31.0765 30.851 31.0765 31.7691V34.1783H31.0904C35.2707 30.9039 37.9663 25.8208 37.9996 20.1069V19.8928C37.9992 19.8358 37.9986 19.7789 37.9978 19.722Z"
                                                        stroke="white" stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="3"></path>
                                                </svg></div>
                                            <h4 class="currentColor"><span class="d-flex align-items-center">3</span>
                                                นโยบายการใช้งาน</h4>
                                            <p>กำหนดวิธีการใช้ระบบและเครื่องมือในโปรแกรม Affiliate
                                                รวมถึงข้อห้ามที่ต้องปฏิบัติตาม</p>
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-md-6">
                                        <div class="counter-item bg-green text-white">
                                            <div class="counter-item-icon"><svg fill="none" height="36"
                                                    viewBox="0 0 40 36" width="40"
                                                    xmlns="http://www.w3.org/2000/svg">
                                                    <path
                                                        d="M20.25 6.86275L19.0872 7.8104C19.3721 8.15992 19.7991 8.36275 20.25 8.36275C20.7009 8.36275 21.1279 8.15992 21.4127 7.8104L20.25 6.86275ZM2.25 11.9473L0.750164 11.9251C0.750055 11.9325 0.75 11.9399 0.75 11.9473H2.25ZM38.25 11.9473H39.75C39.75 11.9399 39.7499 11.9325 39.7498 11.9251L38.25 11.9473ZM3.74984 11.9695C3.80301 8.37207 5.86454 5.0043 8.66757 3.69812C10.0351 3.06085 11.6143 2.89066 13.3317 3.4358C15.0693 3.98734 17.0448 5.30437 19.0872 7.8104L21.4127 5.91511C19.1068 3.08573 16.6732 1.34896 14.2393 0.576395C11.7853 -0.202583 9.43065 0.032806 7.40043 0.978862C3.40788 2.83934 0.817805 7.34926 0.750164 11.9251L3.74984 11.9695ZM20.25 32.6024C20.1152 32.6024 19.7289 32.5356 19.0552 32.2606C18.4195 32.0012 17.6408 31.6037 16.7661 31.0708C15.018 30.0057 12.967 28.449 11.0133 26.5203C7.06408 22.6216 3.75 17.4461 3.75 11.9473H0.75C0.75 18.6163 4.71522 24.5183 8.90565 28.6552C11.0218 30.7443 13.2557 32.4449 15.2052 33.6327C16.1793 34.2262 17.1026 34.704 17.9217 35.0383C18.7029 35.3571 19.5244 35.6024 20.25 35.6024V32.6024ZM39.7498 11.9251C39.6822 7.34925 37.0921 2.83934 33.0995 0.978861C31.0693 0.0328063 28.7147 -0.202582 26.2606 0.576394C23.8267 1.34896 21.3932 3.08573 19.0872 5.91511L21.4127 7.8104C23.4551 5.30437 25.4307 3.98734 27.1683 3.4358C28.8856 2.89066 30.4648 3.06085 31.8324 3.69812C34.6354 5.0043 36.697 8.37207 36.7502 11.9695L39.7498 11.9251ZM20.25 35.6024C20.9755 35.6024 21.797 35.3571 22.5782 35.0383C23.3973 34.704 24.3206 34.2262 25.2948 33.6327C27.2443 32.4449 29.4782 30.7443 31.5943 28.6552C35.7848 24.5184 39.75 18.6163 39.75 11.9473H36.75C36.75 17.4461 33.4359 22.6216 29.4867 26.5203C27.533 28.449 25.482 30.0057 23.7338 31.0708C22.8592 31.6037 22.0805 32.0012 21.4447 32.2606C20.771 32.5356 20.3848 32.6024 20.25 32.6024V35.6024Z"
                                                        fill="white"></path>
                                                </svg></div>
                                            <h4 class="currentColor"><span class="d-flex align-items-center"><span
                                                        class="counter p-0 m-0 ">4</span></span>การเปลี่ยนแปลง<br>และการยกเลิก
                                            </h4>
                                            <p>ระบุขั้นตอนเมื่อมีการเปลี่ยนแปลงนโยบายหรือยกเลิกโปรแกรม
                                                รวมถึงวิธีการจัดการของ Partner</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modules_area section__padding_custom" data-type="component-text">
                            <div class="container">
                                <div class="row justify-content-center">
                                    <div class="col-lg-6 col-md-8">
                                        <h3>&nbsp;</h3>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <div class="modile_main_wrap">
                                            <div class="single_module text-center">
                                                <p style="text-align: left;"><a
                                                        href="javascript:void(0)">&nbsp;</a>1.1 การสมัครเข้าร่วมโปรแกรม
                                                    Affiliate<br>ผู้ใช้ที่ต้องการเข้าร่วมโปรแกรม Affiliate
                                                    จะต้องสมัครสมาชิกและกรอกข้อมูลที่จำเป็นอย่างครบถ้วนและถูกต้อง
                                                    การสมัครเข้าร่วมโปรแกรมหมายความว่าผู้ใช้ยอมรับข้อตกลงและเงื่อนไขทั้งหมดที่ระบุไว้ในเอกสารนี้<br><br>1.2
                                                    การใช้ลิงก์ Affiliate<br>ผู้ใช้ที่เข้าร่วมโปรแกรม Affiliate
                                                    จะได้รับลิงก์พิเศษเพื่อใช้ในการแนะนำลูกค้าใหม่
                                                    หากมีการสั่งซื้อคอร์สเรียนผ่านลิงก์นั้น
                                                    ผู้ใช้จะได้รับค่าคอมมิชชั่นตามอัตราที่กำหนดไว้<br><br>1.3
                                                    การชำระเงินค่าคอมมิชชั่น<br>ค่าคอมมิชชั่นจะถูกจ่ายให้กับผู้ใช้ทุก ๆ
                                                    สิ้นเดือน หลังจากผ่านกระบวนการตรวจสอบความถูกต้องของยอดขาย
                                                    ผู้ใช้ต้องมีบัญชีธนาคารที่ถูกต้องเพื่อรับเงินค่าคอมมิชชั่น</p>
                                            </div>
                                            <div class="single_module text-center">
                                                <div style="text-align: left;">2.1
                                                    การเก็บข้อมูลส่วนบุคคล<br>ข้อมูลส่วนบุคคลของผู้ใช้ที่สมัครเข้าร่วมโปรแกรม
                                                    Affiliate
                                                    จะถูกเก็บรักษาอย่างปลอดภัยและจะไม่ถูกนำไปใช้ในทางที่ผิดหรือเผยแพร่ให้บุคคลภายนอกโดยไม่ได้รับความยินยอม
                                                    ยกเว้นในกรณีที่จำเป็นต้องเปิดเผยตามกฎหมาย<br><br>2.2
                                                    การใช้ข้อมูล<br>ข้อมูลส่วนบุคคลที่เก็บรวบรวมจะถูกใช้เพื่อวัตถุประสงค์ในการบริหารจัดการโปรแกรม
                                                    Affiliate และเพื่อปรับปรุงการบริการเท่านั้น<br><br>2.3
                                                    การคุ้มครองข้อมูล<br>เรามีมาตรการรักษาความปลอดภัยที่เข้มงวดเพื่อปกป้องข้อมูลของผู้ใช้จากการเข้าถึงโดยไม่ได้รับอนุญาตหรือการเปิดเผยข้อมูลโดยไม่ได้รับอนุญาต
                                                </div>
                                            </div>
                                            <div class="single_module text-center" style="text-align: left;">
                                                <div style="text-align: left;">3.1
                                                    การส่งเสริมการขาย<br>ผู้ใช้ต้องส่งเสริมการขายคอร์สเรียนโดยไม่ละเมิดกฎหมายหรือข้อบังคับใด
                                                    ๆ และไม่ใช้วิธีการที่เป็นการหลอกลวงหรือเป็นการรบกวน<br><br>3.2
                                                    ความรับผิดชอบของ
                                                    Affiliate<br>ผู้ใช้ต้องรับผิดชอบต่อการกระทำของตนเองในการโปรโมตคอร์สเรียนและการใช้ลิงก์
                                                    Affiliate หากมีการกระทำใดที่ฝ่าฝืนกฎระเบียบและเงื่อนไข
                                                    ผู้ใช้จะต้องรับผิดชอบต่อความเสียหายที่เกิดขึ้นทั้งหมด<br><br>3.3
                                                    การยุติการเป็น Affiliate<br>เราขอสงวนสิทธิ์ในการยุติบัญชี Affiliate
                                                    ของผู้ใช้ได้ทุกเมื่อ
                                                    หากพบว่ามีการละเมิดข้อตกลงหรือนโยบายการใช้งานที่กำหนดไว้</div>
                                            </div>
                                            <div class="single_module text-center">
                                                <p style="text-align: left;">4.1
                                                    เราขอสงวนสิทธิ์ในการแก้ไขหรือปรับเปลี่ยนข้อตกลง,
                                                    นโยบายความเป็นส่วนตัว, และนโยบายการใช้งาน Affiliate
                                                    โดยไม่ต้องแจ้งให้ทราบล่วงหน้า การเปลี่ยนแปลงใด ๆ
                                                    จะมีผลบังคับใช้ทันทีเมื่อมีการประกาศบนเว็บไซต์<br><br>4.2&nbsp;ในกรณีที่บริษัทตัดสินใจยกเลิกโปรแกรม
                                                    Affiliate
                                                    เราจะแจ้งให้พาทเนอร์ทราบล่วงหน้าผ่านทางอีเมลหรือช่องทางสื่อสารที่เหมาะสม
                                                    การยกเลิกจะไม่มีผลกระทบต่อค่าคอมมิชชั่นที่ได้สะสมไว้ก่อนการยกเลิก<br><br>4.3
                                                    บริษัทขอสงวนสิทธิ์ในการระงับบัญชี Affiliate หรือยุติความร่วมมือ
                                                    หากพบว่ามีการกระทำที่ละเมิดข้อกำหนดหรือมีผลกระทบต่อผลประโยชน์ของบริษัท
                                                    การกระทำดังกล่าวอาจส่งผลให้สูญเสียสิทธิ์ในค่าคอมมิชชั่นที่ยังไม่ได้รับ
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12 ui-resizable" data-type="container-content">
                        <div data-aoraeditor-categories="Affiliate Page"
                            data-aoraeditor-title="Affiliate Frequently Asked Questions Section"
                            data-preview="{{ asset('frontend/infixlmstheme/img/snippets/preview/affiliate/frequntlya_ask_section.png') }}"
                            {{-- data-preview="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/snippets/preview/affiliate/frequntlya_ask_section.png" --}} data-type="component-text">
                            <div class="affliate_faq-section">
                                <div class="container">
                                    <div class="row justify-content-center">
                                        <div class="col-xl-6 col-lg-8 col-md-8">
                                            <div class="section__title4 text-center margin_52"><span
                                                    class="subheading_text">รีวิวจากการใช้&nbsp;Affiliate</span>
                                                <h4><strong>ใช้&nbsp;Affiliate แล้วเป็นไรอย่างไร</strong></h4>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-xl-4 col-lg-4 col-md-6">
                                            <div class="affiliate_faq_box mb_25">
                                                <h4>เริ่มต้น</h4>
                                                <p>คุณเมย์เริ่มต้นเข้าร่วมโปรแกรม Affiliate
                                                    โดยมีเป้าหมายเพียงเพื่อสร้างรายได้เสริมจากการโปรโมตคอร์สเรียนที่เธอชื่นชอบในช่วงเวลาว่าง
                                                    ๆ เธอเริ่มต้นด้วยการโปรโมตลิงก์ Affiliate
                                                    ผ่านทางโซเชียลมีเดียของเธอเอง เช่น Facebook และ Instagram
                                                    และใช้วิธีการสร้างเนื้อหาที่เกี่ยวข้องกับคอร์สเรียนที่เธอแนะนำ</p>
                                            </div>
                                        </div>
                                        <div class="col-xl-4 col-lg-4 col-md-6">
                                            <div class="affiliate_faq_box mb_25">
                                                <h4>กลยุทธ์</h4>
                                                <p>คุณเมย์เชื่อว่าการทำคอนเทนต์ที่มีคุณภาพเป็นกุญแจสำคัญในการดึงดูดความสนใจของกลุ่มเป้าหมาย
                                                    เธอจึงใช้เวลาในการเขียนบทความรีวิวคอร์สเรียน,
                                                    การทำวิดีโอที่แนะนำเนื้อหาคอร์ส
                                                    และการแชร์ประสบการณ์จริงจากการเรียนรู้<br><br>เธอใช้โซเชียลมีเดียในการโปรโมตลิงก์
                                                    Affiliate โดยเน้นการสร้างโพสต์ที่ให้คุณค่ากับผู้ติดตาม เช่น
                                                    การให้ความรู้, เคล็ดลับ, และแนวทางการพัฒนาตัวเอง
                                                    ซึ่งช่วยให้ผู้ติดตามเชื่อมั่นในคำแนะนำของเธอ</p>
                                            </div>
                                        </div>
                                        <div class="col-xl-4 col-lg-4 col-md-6">
                                            <div class="affiliate_faq_box mb_25">
                                                <h4>การเข้าถึงกลุ่มเป้าหมายที่ตรงจุด</h4>
                                                <p>คุณเมย์เน้นการเข้าถึงกลุ่มเป้าหมายที่มีความสนใจในคอร์สเรียนที่เธอโปรโมต
                                                    โดยใช้การโฆษณาที่กำหนดกลุ่มเป้าหมายเฉพาะเจาะจง เช่น
                                                    ผู้ที่สนใจการพัฒนาทักษะใหม่ ๆ
                                                    หรือผู้ที่กำลังมองหาการเรียนรู้เพิ่มเติมในสายงานเฉพาะ</p>
                                            </div>
                                        </div>
                                        <div class="col-xl-4 col-lg-4 col-md-6">
                                            <div class="affiliate_faq_box mb_25">
                                                <h4>การตอบสนองและให้ความช่วยเหลือที่รวดเร็ว</h4>
                                                <p>คุณเมย์มักจะตอบคำถามและข้อสงสัยของผู้ติดตามในทันทีเมื่อมีการสอบถามเกี่ยวกับคอร์สเรียนที่เธอแนะนำ
                                                    การให้ข้อมูลที่เป็นประโยชน์และตรงไปตรงมาช่วยให้ผู้ติดตามตัดสินใจซื้อได้ง่ายขึ้น
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-xl-4 col-lg-4 col-md-6">
                                            <div class="affiliate_faq_box mb_25">
                                                <h4>ผลลัพธ์ที่ได้รับ</h4>
                                                <p>จากการใช้กลยุทธ์เหล่านี้ คุณเมย์สามารถสร้างรายได้จากโปรแกรม Affiliate
                                                    ได้มากกว่า 50,000 บาทต่อเดือนอย่างต่อเนื่อง นอกจากนี้
                                                    เธอยังได้รับการยอมรับจากกลุ่มเป้าหมายและผู้ติดตามว่าเป็นผู้เชี่ยวชาญในด้านการเรียนรู้และพัฒนาตัวเอง
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-xl-4 col-lg-4 col-md-6">
                                            <div class="affiliate_faq_box mb_25">
                                                <h4>คำแนะนำ</h4>
                                                <p>รู้จักผู้ติดตามของคุณ:
                                                    เข้าใจกลุ่มเป้าหมายและสร้างเนื้อหาที่ตอบโจทย์ความต้องการ</p>
                                                <p>ให้คุณค่ามากกว่าการขาย:
                                                    สร้างความน่าเชื่อถือโดยการให้ข้อมูลที่เป็นประโยชน์ ไม่ใช่แค่ขายของ
                                                </p>
                                                <p>ทดลองและปรับปรุง: ทดลองวิธีใหม่ ๆ
                                                    เพื่อพัฒนากลยุทธ์การตลาดให้ดียิ่งขึ้น</p>
                                                <p>กรณีศึกษานี้แสดงให้เห็นว่าความสำเร็จในโปรแกรม Affiliate
                                                    มาจากการวางแผนและใช้กลยุทธ์ที่เหมาะสมในการดึงดูดและสร้างความเชื่อมั่นในกลุ่มเป้าหมาย
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="aoraeditor-skip aoraeditor-footer">
            <div>
            </div>

            <footer class="">
                <div>
                    <div class="footer_top_area">
                        <div class="container">
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="footer__cta">
                                        <div class="thumb">
                                            <img src="{{ asset('frontend/infixlmstheme/img/book.png') }}"
                                                {{-- <img src="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/book.png" --}} alt="" class="w-100">
                                        </div>
                                        <div class="cta_content">
                                            <h3>ติดตามข่าวสาร — รับการอัปเดตทางอีเมล</h3>
                                            <p>ติดตามข่าวสารล่าสุดจาก eiMaths</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">

                                    <div class="subcribe-form theme_mailChimp">
                                        <form action="#" method="POST" {{-- <form action="https://ecommerce.eimaths-th.com/subscribe" method="POST" --}}
                                            class="subscription relative"><input type="hidden" name="_token"
                                                value="UXqxMUzO3NAybCH6WduKA4cNN4PbjD0GdofYpyEl"> <input
                                                name="email" class="form-control" placeholder="กรอกที่อยู่อีเมล"
                                                onfocus="this.placeholder = ''" onblur="this.placeholder = 'อีเมล'"
                                                required="" type="email" value="">

                                            <button type="submit">สมัครรับข้อมูลตอนนี้</button>
                                            <div class="info">
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="copyright_area">
                    <div class="container">
                        <div class="row">


                            <div class="col-xl-4 col-lg-4 col-md-12">
                                <div class="footer_widget">
                                    <div class="footer_logo">
                                        <a href="#">
                                            <img src="{{ asset('uploads/main/files/08-08-2024/logo-eimath.png') }}"
                                                {{-- <img src="https://ecommerce.eimaths-th.com/public/uploads/main/files/08-08-2024/logo-eimath.png" --}} alt="" style="width: 108px">
                                        </a>
                                    </div>
                                    <p></p>
                                    <div class="copyright_text">
                                        <p>Copyright © 2024 eiMaths. All rights reserved | Made By <a
                                                href="https://eimaths-th.com" target="_blank"><span
                                                    style="color:#D12053">eiMaths</span></a></p>
                                    </div>

                                    <style>


                                    </style>
                                    <div class="">
                                        <ul class="pt-3 ">
                                            <ul class="social-network">
                                                <div>
                                                </div>
                                            </ul>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-8 col-lg-8 col-md-6">

                                <div class="row">
                                    <div class="col-lg-4 col-md-4">
                                        <div class="footer_widget">
                                            <div class="footer_title">
                                                <h3>Support Zone</h3>
                                            </div>
                                            <ul class="footer_links">
                                                <li>
                                                    <a href="#">
                                                        {{-- <a href="https://ecommerce.eimaths-th.com/unlock-your-potential"> --}}
                                                        Unlock Your Potential </a>
                                                </li>
                                                <li>
                                                    {{-- <a href="https://ecommerce.eimaths-th.com/privacy-policy-and-cookie-policy"> --}}
                                                    <a href="#">

                                                        Privacy
                                                        policy and cookie policy </a>
                                                </li>
                                                <li><a href="#">Sitemap </a>
                                                    {{-- <li><a href="https://ecommerce.eimaths-th.com/sitemap">Sitemap </a> --}}
                                                </li>
                                                <li>
                                                    {{-- <a href="https://ecommerce.eimaths-th.com/featured-courses"> --}}
                                                    <a href="#">

                                                        Featured courses
                                                    </a>
                                                </li>
                                                <li>
                                                    {{-- <a href="https://ecommerce.eimaths-th.com/join-us"> --}}
                                                    <a href="#">

                                                        Join Us </a>
                                                </li>
                                            </ul>

                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-md-4">
                                        <div class="footer_widget">
                                            <div class="footer_title">
                                                <h3>Company Info</h3>
                                            </div>
                                            <ul class="footer_links">

                                                <li>
                                                    <a href="#">

                                                    {{-- <a href="https://ecommerce.eimaths-th.com/infixedu-for-business"> --}}
                                                        eiMathsfor Business 
                                                    </a>
                                                    </li>
                                                <li>
                                                    <a href="#">

                                                    {{-- <a href="https://ecommerce.eimaths-th.com/teach-on-infixedu"> --}}
                                                        Teach on eiMaths </a></li>
                                                <li>
                                                    {{-- <a href="https://ecommerce.eimaths-th.com/get-the-app"> --}}
                                                    <a href="#">

                                                        Get the app
                                                    </a></li>
                                                <li>
                                                    {{-- <a href="https://ecommerce.eimaths-th.com/about-us"> --}}
                                                        <a href="#">
                                                            About us </a>
                                                </li>
                                                <li>
                                                    {{-- <a href="https://ecommerce.eimaths-th.com/contact-us"> --}}
                                                        <a href="#">
                                                    
                                                        Contact us
                                                    </a></li>

                                            </ul>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-md-4">
                                        <div class="footer_widget">
                                            <div class="footer_title">
                                                <h3>Explore Services</h3>
                                            </div>
                                            <ul class="footer_links">
                                                <li>
                                                    {{-- <a href="https://ecommerce.eimaths-th.com/careers"> --}}
                                                    <a href="#">
                                                        
                                                        Careers </a>
                                                </li>
                                                <li>
                                                    <a href="#">

                                                    {{-- <a href="https://ecommerce.eimaths-th.com/blog"> --}}
                                                        Blog </a></li>
                                                <li>
                                                    {{-- <a href="https://ecommerce.eimaths-th.com/help-and-support"> --}}
                                                    <a href="#">
                                                        
                                                        Help and Support 
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="#">

                                                    {{-- <a href="https://ecommerce.eimaths-th.com/terms"> --}}
                                                        Terms </a></li>
                                                <li>
                                                    {{-- <a href="https://ecommerce.eimaths-th.com/certificate-verification"> --}}
                                                    <a href="#">

                                                        Certificate Verification </a>
                                                    </li>
                                                <li>
                                                    <a href="#">

                                                    {{-- <a href="https://ecommerce.eimaths-th.com/free-course"> --}}
                                                        Free Course
                                                    </a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>
                </div>
            </footer>

            {{-- ต่อพรุ่งนี้ --}}

            <div class="shoping_wrapper">
                <div class="dark_overlay"></div>
                <div class="shoping_cart">
                    <div class="shoping_cart_inner">
                        <div class="cart_header d-flex justify-content-between">
                            <h4>ตะกร้าสินค้า</h4>
                            <div class="chart_close">
                                <i class="ti-close"></i>
                            </div>
                        </div>
                        <div id="cartView">
                            {{-- <div class="single_cart">
                                Loading...
                            </div> --}}
                        </div>


                    </div>

                </div>
            </div>
            <!-- shoping_cart::end  -->

            <!-- UP_ICON  -->
            <div id="back-top" style="display: none;">
                <a title="Go to Top" href="#">
                    <i class="fa fa-angle-up"></i>
                </a>
            </div>

            <input type="hidden" name="item_list" class="item_list"
                value="https://ecommerce.eimaths-th.com/getItemList">
            <input type="hidden" name="enroll_cart" class="enroll_cart"
                value="https://ecommerce.eimaths-th.com/enrollOrCart">
            <input type="hidden" name="csrf_token" class="csrf_token"
                value="UXqxMUzO3NAybCH6WduKA4cNN4PbjD0GdofYpyEl">
            <!--/ UP_ICON -->

            <div>
                <style>
                    .remove_cart {
                        margin-left: -22px;
                        margin-right: 8px;
                        cursor: pointer;
                    }

                    .theme_cookies {
                        background: #FFEDE2;
                    }

                    .theme_cookies .cookie_btn {
                        background: #F8D4BE;
                    }

                    .cookie-setting-details {
                        transition: all 0.4s ease-in-out;
                        max-width: 100% !important;
                    }

                    .height-none {
                        height: 0;
                        overflow: hidden;
                    }

                    .theme_btn_outline {
                        background: transparent;
                        border-radius: 5px;
                        font-family: Source Sans Pro, sans-serif;
                        font-size: 16px;
                        color: var(--system_primery_color);
                        font-weight: 600;
                        padding: 21px 28px;
                        text-transform: capitalize;
                        display: inline-block;
                        line-height: 1;
                        border: 1px solid var(--system_primery_color);
                    }

                    .theme_btn_outline:hover {
                        background: var(--system_primery_color);
                        color: #ffffff
                    }

                    @media (min-width: 576px) {
                        .modal-dialog {
                            max-width: 500px;
                        }
                    }

                    @media (min-width: 1080px) {
                        .modal-dialog {
                            max-width: 800px;
                        }
                    }
                </style>

                <div class="theme_cookies" style="display: none">
                    <div class="theme_cookies_info flex-fill">
                        <div class="icon">
                            <img src="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/icon/cookies_icon.svg"
                                alt="">
                        </div>
                        <p>We collect and use cookies to give you the best and most relevant website experience. Kindly
                            accept the cookies.
                            <a href=''>Privacy Policy</a>
                        </p>
                        <button type="button" class="cookie_btn" onclick="setCookies(1);">I Accept</button>
                        <button type="button" class="cookie_btn" data-bs-toggle="modal"
                            data-bs-target="#cookieSettingsModal">Customize Setting</button>

                        <!-- Modal -->
                        <div class="modal fade" id="cookieSettingsModal" tabindex="-1"
                            aria-labelledby="cookieModal" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalToggleLabel">GDPR</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        When you visit any of our websites, it may store or retrieve information on your
                                        browser, mostly in the form of cookies. This information might be about you,
                                        your preferences or your device and is mostly used to make the site work as you
                                        expect it to. The information does not usually directly identify you, but it can
                                        give you a more personalized web experience. Because we respect your right to
                                        privacy, you can choose not to allow some types of cookies. Click on the
                                        different category headings to find out more and manage your preferences. Please
                                        note, that blocking some types of cookies may impact your experience of the site
                                        and the services we are able to offer

                                        <ul class="mt-3 cookie-setting-list">
                                            <li>
                                                <div class="d-flex align-items-center mb-2">
                                                    <label class="primary_checkbox d-flex mb-0">
                                                        <input type="checkbox" class="cookie_type" value="strictly"
                                                            disabled checked />
                                                        <span class="checkmark mr_15"></span>
                                                        <span class="label_name">Strictly Necessary</span>
                                                    </label>
                                                    <i class="far fa-question-circle ms-2" style="cursor: pointer"
                                                        onclick="displayCookieSettingDetails(this)"></i>
                                                </div>
                                                <p class="height-none cookie-setting-details my-3">
                                                    These cookies are necessary for our website to function properly and
                                                    cannot be switched off in our systems. They are usually only set in
                                                    response to actions made by you that amount to a request for
                                                    services, such as setting your privacy preferences, logging in or
                                                    filling in forms, or where they’re essential to providing you with a
                                                    service you have requested. You cannot opt out of these cookies. You
                                                    can set your browser to block or alert you about these cookies, but
                                                    if you do, some parts of the site will not then work. These cookies
                                                    do not store any personally identifiable information
                                                </p>
                                            </li>

                                            <li>
                                                <div class="d-flex align-items-center mb-2">
                                                    <label class="primary_checkbox d-flex mb-0">
                                                        <input type="checkbox" class="cookie_type"
                                                            value="performance" />
                                                        <span class="checkmark mr_15"></span>
                                                        <span class="label_name">Performance Cookies</span>
                                                    </label>
                                                    <i class="far fa-question-circle ms-2" style="cursor: pointer"
                                                        onclick="displayCookieSettingDetails(this)"></i>
                                                </div>
                                                <p class="height-none cookie-setting-details my-3">
                                                    These cookies allow us to count visits and traffic sources so we can
                                                    measure and improve the performance of our site. They help us to
                                                    know which pages are the most and least popular and see how visitors
                                                    move around the site, which helps us optimize your experience. All
                                                    information these cookies collect is aggregated and therefore
                                                    anonymous. If you do not allow these cookies we will not be able to
                                                    use your data in this way.
                                                </p>
                                            </li>

                                            <li>
                                                <div class="d-flex align-items-center mb-2">
                                                    <label class="primary_checkbox d-flex mb-0">
                                                        <input type="checkbox" class="cookie_type"
                                                            value="functional" />
                                                        <span class="checkmark mr_15"></span>
                                                        <span class="label_name">Functional Cookies</span>
                                                    </label>
                                                    <i class="far fa-question-circle ms-2" style="cursor: pointer"
                                                        onclick="displayCookieSettingDetails(this)"></i>
                                                </div>
                                                <p class="height-none cookie-setting-details my-3">
                                                    These cookies enable the website to provide enhanced functionality
                                                    and personalization. They may be set by us or by third-party
                                                    providers whose services we have added to our pages. If you do not
                                                    allow these cookies then some or all of these services may not
                                                    function properly.
                                                </p>
                                            </li>

                                            <li>
                                                <div class="d-flex align-items-center mb-2">
                                                    <label class="primary_checkbox d-flex mb-0">
                                                        <input type="checkbox" class="cookie_type"
                                                            value="targeting" />
                                                        <span class="checkmark mr_15"></span>
                                                        <span class="label_name">Targeting Cookies</span>
                                                    </label>
                                                    <i class="far fa-question-circle ms-2" style="cursor: pointer"
                                                        onclick="displayCookieSettingDetails(this)"></i>
                                                </div>
                                                <p class="height-none cookie-setting-details my-3">
                                                    These cookies may be set through our site by our advertising
                                                    partners. They may be used by those companies to build a profile of
                                                    your interests and show you relevant adverts on other sites. They do
                                                    not store directly personal information but are based on uniquely
                                                    identifying your browser and internet device. If you do not allow
                                                    these cookies, you will experience less targeted advertising.
                                                </p>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" onclick="setCookies();" class="theme_btn">Confirm My
                                            Choices
                                        </button>
                                        <button type="button" class="theme_btn_outline"
                                            onclick="setCookies(1);">Accept All
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>


                    </div>
                </div>

                <script>
                    $(document).ready(function() {
                        checkCookie();
                    });


                    function setCookies(type = 0) {
                        $('.theme_cookies').hide(500);
                        let d = new Date();

                        let items;
                        if (type === 1) {
                            items = $('.cookie_type');
                        } else {
                            items = $(".cookie_type:checked");
                        }
                        // set type of cookie allow
                        items.each(function() {
                            document.cookie = $(this).val() + "_allow=1; expires=Thu, 21 Dec " + (d.getFullYear() + 1) +
                                " 12:00:00 UTC";

                        });
                        //set allow cookie
                        document.cookie = "allow=1; expires=Thu, 21 Dec " + (d.getFullYear() + 1) + " 12:00:00 UTC";

                        //hide modal if open modal
                        $('#cookieSettingsModal').modal('hide');
                    }

                    function getCookie(cname) {
                        var name = cname + "=";
                        var ca = document.cookie.split(';');
                        for (var i = 0; i < ca.length; i++) {
                            var c = ca[i];
                            while (c.charAt(0) == ' ') {
                                c = c.substring(1);
                            }
                            if (c.indexOf(name) == 0) {
                                return c.substring(name.length, c.length);
                            }
                        }
                        return "";
                    }

                    function checkCookie() {
                        var check = getCookie("allow");
                        if (check != "") {
                            $('.theme_cookies').hide();
                        } else {
                            $('.theme_cookies').show();
                        }
                    }

                    // function to show hide cookie setting details

                    function displayCookieSettingDetails(element) {
                        let cookieSettingElement = element.parentElement;
                        let detailsElement = cookieSettingElement.parentElement.querySelector(".cookie-setting-details");
                        detailsElement.classList.toggle("height-none")
                    }
                </script>
            </div>

            <div class="modal fade leaderboard-boarder" id="myLeaderBoard" tabindex="-1" role="dialog"
                aria-labelledby="myLeaderBoard" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3 class="modal-title fs-5" id="">กระดานผู้นำ</h3>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true"><i class="fa fa-times"></i></span></button>
                        </div>
                        <div class="modal-body">
                            <div class="reward-leader">
                                <ul class="nav nav-tabs border-bottom-0 m-0" id="myTab" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link  nav-point type-point" id="point-tab"
                                            data-bs-toggle="tab" data-bs-target="#point" data-type="point"
                                            type="button" role="tab" aria-controls="point"
                                            aria-selected="true">Points
                                        </button>
                                    </li>

                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link nav-point type-level" id="level-tab"
                                            data-bs-toggle="tab" data-bs-target="#level" data-type="level"
                                            type="button" role="tab" aria-controls="level"
                                            aria-selected="true">Levels
                                        </button>
                                    </li>

                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link nav-point type-badge" id="badge-tab"
                                            data-bs-toggle="tab" data-bs-target="#badge" data-type="badge"
                                            type="button" role="tab" aria-controls="badge"
                                            aria-selected="true">Badges
                                        </button>
                                    </li>
                                </ul>
                                <div id="leaderboardBody" class="leaderboardBody"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="modal fade noticeboard-modal" id="myNoticeboard" tabindex="-1" role="dialog"
                aria-labelledby="myNoticeboard" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-centered" id="myNoticeboardBody">

                </div>
            </div>

            <script src="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/js/app.js?v=5896"></script>

            <script src="https://ecommerce.eimaths-th.com/public/backend/js/summernote-bs4.min.js?v=6977"></script>
            <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBq4zPuqxFWOoPVLs1qpXV0ZkeOhGe0JIg"></script>
            <script src="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/js/map.js"></script>
            <script src="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/js/contact.js?v=7552"></script>

            <script type="text/javascript"></script>




            <script>
                // setTimeout(function() {
                //     $('.preloader').fadeOut('hide', function() {
                //         // $(this).remove();

                //     });
                // }, 0);


                $('#cartView').on('click', '.remove_cart', function() {
                    let id = $(this).data('id');
                    let total = $('.notify_count').html();

                    $(this).closest(".single_cart").hide();
                    let url = "https://ecommerce.eimaths-th.com/home/removeItemAjax" + '/' + id;

                    $.ajax({
                        type: 'GET',
                        url: url,
                        success: function(data) {
                            let finalTotal = total - 1;
                            if (finalTotal < 0) {
                                finalTotal = 0
                            }
                            $('.notify_count').html(finalTotal);
                        }
                    });
                });

                $(function() {
                    $('.lazy').Lazy();
                });
            </script>
            <script>
                $(document).on('click', '.show_notify', function(e) {
                    e.preventDefault();

                    console.log('notify');
                });
                if ($('#main-nav-for-chat').length) {} else {
                    $('#main-content').append('<div id="main-nav-for-chat" style="visibility: hidden;"></div>');
                }

                if ($('#admin-visitor-area').length) {} else {
                    $('#main-content').append('<div id="admin-visitor-area" style="visibility: hidden;"></div>');
                }
            </script>






            <script src="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/js/custom.js?v=1166"></script>
            <script>
                $(document).on("click", ".point_btn", function() {
                    let modal = $('#myLeaderBoard')
                    modal.modal('show');
                    let type = modal.find('.nav-link.active').data('type');
                    if (type == undefined) {
                        let link = modal.find('.nav-link:first');
                        link.addClass('active')
                        type = link.data('type');

                    }
                    loadData(type);
                });
                $(document).on("click", ".how_to_point", function() {
                    let modal = $('#myLeaderBoard')
                    modal.modal('show');
                    let link = modal.find('.nav-link:first');
                    link.addClass('active')
                    loadData('how_to_point')
                });

                $(document).on("click", ".nav-point", function() {
                    let type = $(this).data('type');
                    loadData(type);
                });

                function loadData(type, id = 0) {
                    let body = $('#leaderboardBody');
                    let url = 'https://ecommerce.eimaths-th.com';
                    let formData = {
                        type: type,
                        id: id,
                    };
                    body.html(
                        '<div class="d-flex align-items-center justify-content-center py-3"><i class="fas fa-spinner  fa-spin"></i></div>'
                    )


                    $.ajax({
                        type: "get",
                        data: formData,
                        dataType: "html",
                        url: url + '/my-leaderboard',
                        success: function(data) {
                            body.html(data);
                        },
                        error: function(data) {
                            body.html("");
                        }

                    });
                }
            </script>



            <script>
                function getList() {
                    $('.shoping_cart ,.dark_overlay').toggleClass('active');

                    let url = $('.item_list').val();
                    $.ajax({
                        type: 'GET',
                        dataType: 'html',
                        url: url,
                        data: {
                            "responseType": "view"
                        },

                        success: function(data) {
                            console.log(data);
                            $('#cartView').empty().html(data);
                            // $('.preloader').fadeOut('slow');
                        }
                    });
                }

                $(document).on('click', '.cart_store', function(e) {
                    e.preventDefault();
                    let btn = $(this);
                    let id = $(this).data('id');
                    let url = $('.enroll_cart').val();
                    let csrf_token = $('.csrf_token').val();
                    if ($.isNumeric(id)) {

                        $.ajax({
                            type: 'POST',
                            dataType: 'json',
                            url: url + '/' + id,
                            data: {
                                _token: csrf_token
                            },
                            success: function(data) {
                                // $('.preloader').fadeOut('slow');
                                if (data['result'] === "failed") {
                                    toastr.error(data['message']);
                                    btn.show();
                                } else {
                                    toastr.success(data['message']);
                                    btn.hide();
                                }
                                if (data.type === 'addToCart') {
                                    $('.notify_count').html(data.total);
                                    getList();
                                } else {

                                }

                            }
                        });

                    } else {
                        getList();
                    }


                });
                $(".stripe-button-el").remove();
                $(".razorpay-payment-button").hide();
            </script>
        </div>
    </div>
</body>

</html>
</div>


<script type="text/javascript"
    src="https://ecommerce.eimaths-th.com/Modules/AoraPageBuilder/Resources/assets/js/bootstrap.min.js"></script>
<script type="text/javascript"
    src="https://ecommerce.eimaths-th.com/Modules/AoraPageBuilder/Resources/assets/js/jquery-ui.min.js"></script>
<script type="text/javascript"
    src="https://ecommerce.eimaths-th.com/Modules/AoraPageBuilder/Resources/assets/js/ckeditor.js"></script>
<script type="text/javascript"
    src="https://ecommerce.eimaths-th.com/Modules/AoraPageBuilder/Resources/assets/js/form-builder.min.js"></script>
<script type="text/javascript"
    src="https://ecommerce.eimaths-th.com/Modules/AoraPageBuilder/Resources/assets/js/form-render.min.js"></script>
<script type="text/javascript"
    src="https://ecommerce.eimaths-th.com/Modules/AoraPageBuilder/Resources/assets/js/aoraeditor.js"></script>

<script type="text/javascript"
    src="https://ecommerce.eimaths-th.com/Modules/AoraPageBuilder/Resources/assets/js/aoraeditor-components.js">
</script>


<script src="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/js/courses.js"></script>

<script>
    $('.ui-resizable-resizer').remove()
</script>



<script type="text/javascript" data-aoraeditor="script">
    $(function() {
        // $('.dynamicData').each(function (i, obj) {
        //     aoraEditor.loadDynamicContent($(this));
        // });


    });
    // $(function () {
    //     if ($.isFunction($.fn.lazy)) {
    //         $('.lazy').lazy();
    //     }
    // });

    // setTimeout(function() {
    //     $('.preloader').fadeOut('hide', function() {
    //         // $(this).remove();

    //     });
    // }, 0);
</script>
<script type="text/javascript">
    var phpdebugbar = new PhpDebugBar.DebugBar();
    phpdebugbar.addIndicator("php_version", new PhpDebugBar.DebugBar.Indicator({
        "icon": "code",
        "tooltip": "PHP Version"
    }), "right");
    phpdebugbar.addIndicator("time", new PhpDebugBar.DebugBar.Indicator({
        "icon": "clock-o",
        "tooltip": "Request Duration"
    }), "right");
    phpdebugbar.addTab("timeline", new PhpDebugBar.DebugBar.Tab({
        "icon": "tasks",
        "title": "Timeline",
        "widget": new PhpDebugBar.Widgets.TimelineWidget()
    }));
    phpdebugbar.addIndicator("memory", new PhpDebugBar.DebugBar.Indicator({
        "icon": "cogs",
        "tooltip": "Memory Usage"
    }), "right");
    phpdebugbar.addTab("exceptions", new PhpDebugBar.DebugBar.Tab({
        "icon": "bug",
        "title": "Exceptions",
        "widget": new PhpDebugBar.Widgets.ExceptionsWidget()
    }));
    phpdebugbar.addTab("views", new PhpDebugBar.DebugBar.Tab({
        "icon": "leaf",
        "title": "Views",
        "widget": new PhpDebugBar.Widgets.LaravelViewTemplatesWidget()
    }));
    phpdebugbar.addTab("route", new PhpDebugBar.DebugBar.Tab({
        "icon": "share",
        "title": "Route",
        "widget": new PhpDebugBar.Widgets.HtmlVariableListWidget()
    }));
    phpdebugbar.addIndicator("currentroute", new PhpDebugBar.DebugBar.Indicator({
        "icon": "share",
        "tooltip": "Route"
    }), "right");
    phpdebugbar.addTab("queries", new PhpDebugBar.DebugBar.Tab({
        "icon": "database",
        "title": "Queries",
        "widget": new PhpDebugBar.Widgets.LaravelSQLQueriesWidget()
    }));
    phpdebugbar.addTab("models", new PhpDebugBar.DebugBar.Tab({
        "icon": "cubes",
        "title": "Models",
        "widget": new PhpDebugBar.Widgets.HtmlVariableListWidget()
    }));
    phpdebugbar.addTab("session", new PhpDebugBar.DebugBar.Tab({
        "icon": "archive",
        "title": "Session",
        "widget": new PhpDebugBar.Widgets.VariableListWidget()
    }));
    phpdebugbar.addTab("request", new PhpDebugBar.DebugBar.Tab({
        "icon": "tags",
        "title": "Request",
        "widget": new PhpDebugBar.Widgets.HtmlVariableListWidget()
    }));
    phpdebugbar.setDataMap({
        "php_version": ["php.version", ],
        "time": ["time.duration_str", '0ms'],
        "timeline": ["time", {}],
        "memory": ["memory.peak_usage_str", '0B'],
        "exceptions": ["exceptions.exceptions", []],
        "exceptions:badge": ["exceptions.count", null],
        "views": ["views", []],
        "views:badge": ["views.nb_templates", 0],
        "route": ["route", {}],
        "currentroute": ["route.uri", ],
        "queries": ["queries", []],
        "queries:badge": ["queries.nb_statements", 0],
        "models": ["models.data", {}],
        "models:badge": ["models.count", 0],
        "session": ["session", {}],
        "request": ["request", {}]
    });
    phpdebugbar.restoreState();
    phpdebugbar.ajaxHandler = new PhpDebugBar.AjaxHandler(phpdebugbar, undefined, true);
    phpdebugbar.ajaxHandler.bindToFetch();
    phpdebugbar.ajaxHandler.bindToXHR();
    phpdebugbar.setOpenHandler(new PhpDebugBar.OpenHandler({
        "url": "https:\/\/ecommerce.eimaths-th.com\/_debugbar\/open"
    }));
    phpdebugbar.addDataSet({
        "__meta": {
            "id": "Xe03d2b2dd0d22ab32b2cee001983f83f",
            "datetime": "2024-11-13 09:36:47",
            "utime": 1731465407.947257,
            "method": "GET",
            "uri": "\/affiliate",
            "ip": "49.49.224.104"
        },
        "php": {
            "version": "8.2.9",
            "interface": "apache2handler"
        },
        "time": {
            "start": 1731465407.399853,
            "end": 1731465407.94727,
            "duration": 0.5474169254302979,
            "duration_str": "547ms",
            "measures": [{
                "label": "Booting",
                "start": 1731465407.399853,
                "relative_start": 0,
                "end": 1731465407.870043,
                "relative_end": 1731465407.870043,
                "duration": 0.47019004821777344,
                "duration_str": "470ms",
                "params": [],
                "collector": null
            }, {
                "label": "Application",
                "start": 1731465407.870312,
                "relative_start": 0.470458984375,
                "end": 1731465407.947273,
                "relative_end": 3.0994415283203125e-6,
                "duration": 0.07696104049682617,
                "duration_str": "76.96ms",
                "params": [],
                "collector": null
            }, {
                "label": "select * from `front_pages` where `slug` = '\/affiliate' limit 1",
                "start": 1731465407.897166,
                "relative_start": 0.4973130226135254,
                "end": 1731465407.898856,
                "relative_end": 1731465407.898856,
                "duration": 0.001689910888671875,
                "duration_str": "1.69ms",
                "params": [],
                "collector": null
            }, {
                "label": "select * from `front_pages` where `slug` = '\/affiliate' limit 1",
                "start": 1731465407.899767,
                "relative_start": 0.49991393089294434,
                "end": 1731465407.900457,
                "relative_end": 1731465407.900457,
                "duration": 0.0006899833679199219,
                "duration_str": "690\u03bcs",
                "params": [],
                "collector": null
            }]
        },
        "memory": {
            "peak_usage": 37091328,
            "peak_usage_str": "35MB"
        },
        "exceptions": {
            "count": 0,
            "exceptions": []
        },
        "views": {
            "nb_templates": 13,
            "templates": [{
                    "name": "aorapagebuilder::pages.show (Modules\/AoraPageBuilder\/Resources\/views\/pages\/show.blade.php)",
                    "param_count": 3,
                    "params": ["row", "details", "jsonLang"],
                    "type": "blade",
                    "editorLink": "phpstorm:\/\/open?file=\/var\/www\/html\/ecommerce\/Modules\/AoraPageBuilder\/Resources\/views\/pages\/show.blade.php&line=0"
                }, {
                    "name": "aorapagebuilder::layouts.master (Modules\/AoraPageBuilder\/Resources\/views\/layouts\/master.blade.php)",
                    "param_count": 7,
                    "params": ["__env", "app", "errors", "row", "details", "jsonLang", "route"],
                    "type": "blade",
                    "editorLink": "phpstorm:\/\/open?file=\/var\/www\/html\/ecommerce\/Modules\/AoraPageBuilder\/Resources\/views\/layouts\/master.blade.php&line=0"
                }, {
                    "name": "laravelpwa::meta (resources\/views\/vendor\/laravelpwa\/meta.blade.php)",
                    "param_count": 2,
                    "params": ["config", "jsonLang"],
                    "type": "blade",
                    "editorLink": "phpstorm:\/\/open?file=\/var\/www\/html\/ecommerce\/resources\/views\/vendor\/laravelpwa\/meta.blade.php&line=0"
                }, {
                    "name": "frontend.infixlmstheme.components.frontend-dynamic-style-color (resources\/views\/frontend\/infixlmstheme\/components\/frontend-dynamic-style-color.blade.php)",
                    "param_count": 12,
                    "params": ["color", "componentName", "attributes", "resolve", "slot", "__laravel_slots",
                        "homeContent", "frontendContent", "about_page", "become_instructor",
                        "work_progress", "jsonLang"
                    ],
                    "type": "blade",
                    "editorLink": "phpstorm:\/\/open?file=\/var\/www\/html\/ecommerce\/resources\/views\/frontend\/infixlmstheme\/components\/frontend-dynamic-style-color.blade.php&line=0"
                }, {
                    "name": "backend.components.dynamic-color (resources\/views\/backend\/components\/dynamic-color.blade.php)",
                    "param_count": 6,
                    "params": ["componentName", "attributes", "resolve", "slot", "__laravel_slots",
                        "jsonLang"
                    ],
                    "type": "blade",
                    "editorLink": "phpstorm:\/\/open?file=\/var\/www\/html\/ecommerce\/resources\/views\/backend\/components\/dynamic-color.blade.php&line=0"
                },
                // {
                //     "name": "preloader (resources\/views\/preloader.blade.php)",
                //     "param_count": 10,
                //     "params": ["__env", "app", "errors", "row", "details", "jsonLang", "route", "config",
                //         "component", "currentTheme"
                //     ],
                //     "type": "blade",
                //     "editorLink": "phpstorm:\/\/open?file=\/var\/www\/html\/ecommerce\/resources\/views\/preloader.blade.php&line=0"
                // }, 
                {
                    "name": "frontend.infixlmstheme.partials._menu (resources\/views\/frontend\/infixlmstheme\/partials\/_menu.blade.php)",
                    "param_count": 18,
                    "params": ["__env", "app", "errors", "row", "details", "jsonLang", "route", "config",
                        "component", "currentTheme", "categories", "languages", "menus", "homeContent",
                        "frontendContent", "about_page", "become_instructor", "work_progress"
                    ],
                    "type": "blade",
                    "editorLink": "phpstorm:\/\/open?file=\/var\/www\/html\/ecommerce\/resources\/views\/frontend\/infixlmstheme\/partials\/_menu.blade.php&line=0"
                }, {
                    "name": "frontend.infixlmstheme.partials._footer (resources\/views\/frontend\/infixlmstheme\/partials\/_footer.blade.php)",
                    "param_count": 15,
                    "params": ["__env", "app", "errors", "row", "details", "jsonLang", "route", "config",
                        "component", "currentTheme", "homeContent", "frontendContent", "about_page",
                        "become_instructor", "work_progress"
                    ],
                    "type": "blade",
                    "editorLink": "phpstorm:\/\/open?file=\/var\/www\/html\/ecommerce\/resources\/views\/frontend\/infixlmstheme\/partials\/_footer.blade.php&line=0"
                }, {
                    "name": "frontend.infixlmstheme.components.popup-content (resources\/views\/frontend\/infixlmstheme\/components\/popup-content.blade.php)",
                    "param_count": 13,
                    "params": ["popup", "modal", "componentName", "attributes", "resolve", "slot",
                        "__laravel_slots", "homeContent", "frontendContent", "about_page",
                        "become_instructor", "work_progress", "jsonLang"
                    ],
                    "type": "blade",
                    "editorLink": "phpstorm:\/\/open?file=\/var\/www\/html\/ecommerce\/resources\/views\/frontend\/infixlmstheme\/components\/popup-content.blade.php&line=0"
                }, {
                    "name": "frontend.infixlmstheme.components.footer-news-letter (resources\/views\/frontend\/infixlmstheme\/components\/footer-news-letter.blade.php)",
                    "param_count": 12,
                    "params": ["newsletterSetting", "componentName", "attributes", "resolve", "slot",
                        "__laravel_slots", "homeContent", "frontendContent", "about_page",
                        "become_instructor", "work_progress", "jsonLang"
                    ],
                    "type": "blade",
                    "editorLink": "phpstorm:\/\/open?file=\/var\/www\/html\/ecommerce\/resources\/views\/frontend\/infixlmstheme\/components\/footer-news-letter.blade.php&line=0"
                }, {
                    "name": "frontend.infixlmstheme.components.footer-social-links (resources\/views\/frontend\/infixlmstheme\/components\/footer-social-links.blade.php)",
                    "param_count": 13,
                    "params": ["social_links", "componentName", "attributes", "resolve", "institute",
                        "slot", "__laravel_slots", "homeContent", "frontendContent", "about_page",
                        "become_instructor", "work_progress", "jsonLang"
                    ],
                    "type": "blade",
                    "editorLink": "phpstorm:\/\/open?file=\/var\/www\/html\/ecommerce\/resources\/views\/frontend\/infixlmstheme\/components\/footer-social-links.blade.php&line=0"
                }, {
                    "name": "frontend.infixlmstheme.components.footer-section-widgets (resources\/views\/frontend\/infixlmstheme\/components\/footer-section-widgets.blade.php)",
                    "param_count": 12,
                    "params": ["sectionWidgets", "componentName", "attributes", "resolve", "slot",
                        "__laravel_slots", "homeContent", "frontendContent", "about_page",
                        "become_instructor", "work_progress", "jsonLang"
                    ],
                    "type": "blade",
                    "editorLink": "phpstorm:\/\/open?file=\/var\/www\/html\/ecommerce\/resources\/views\/frontend\/infixlmstheme\/components\/footer-section-widgets.blade.php&line=0"
                }, {
                    "name": "frontend.infixlmstheme.components.footer-cookie (resources\/views\/frontend\/infixlmstheme\/components\/footer-cookie.blade.php)",
                    "param_count": 12,
                    "params": ["cookie", "componentName", "attributes", "resolve", "slot",
                        "__laravel_slots", "homeContent", "frontendContent", "about_page",
                        "become_instructor", "work_progress", "jsonLang"
                    ],
                    "type": "blade",
                    "editorLink": "phpstorm:\/\/open?file=\/var\/www\/html\/ecommerce\/resources\/views\/frontend\/infixlmstheme\/components\/footer-cookie.blade.php&line=0"
                }
            ]
        },
        "route": {
            "uri": "GET affiliate",
            "middleware": "web",
            "controller": "Modules\\Affiliate\\Http\\Controllers\\AffiliateController@frontend",
            "namespace": "Modules\\Affiliate\\Http\\Controllers",
            "prefix": "",
            "where": [],
            "as": "affiliate.frontend",
            "file": "<a href=\"phpstorm:\/\/open?file=\/var\/www\/html\/ecommerce\/Modules\/Affiliate\/Http\/Controllers\/AffiliateController.php&line=124\">Modules\/Affiliate\/Http\/Controllers\/AffiliateController.php:124-139<\/a>"
        },
        "queries": {
            "nb_statements": 2,
            "nb_failed_statements": 0,
            "accumulated_duration": 0.0023799999999999997,
            "accumulated_duration_str": "2.38ms",
            "statements": [{
                "sql": "select * from `front_pages` where `slug` = '\/affiliate' limit 1",
                "type": "query",
                "params": [],
                "bindings": ["\/affiliate"],
                "hints": ["Use <code>SELECT *<\/code> only if you need all columns from table",
                    "<code>LIMIT<\/code> without <code>ORDER BY<\/code> causes non-deterministic results, depending on the query execution plan"
                ],
                "show_copy": false,
                "backtrace": [{
                    "index": 15,
                    "namespace": null,
                    "name": "\/Modules\/Affiliate\/Http\/Controllers\/AffiliateController.php",
                    "line": 126
                }, {
                    "index": 16,
                    "namespace": null,
                    "name": "\/vendor\/laravel\/framework\/src\/Illuminate\/Routing\/Controller.php",
                    "line": 54
                }, {
                    "index": 17,
                    "namespace": null,
                    "name": "\/vendor\/laravel\/framework\/src\/Illuminate\/Routing\/ControllerDispatcher.php",
                    "line": 43
                }, {
                    "index": 18,
                    "namespace": null,
                    "name": "\/vendor\/laravel\/framework\/src\/Illuminate\/Routing\/Route.php",
                    "line": 259
                }, {
                    "index": 19,
                    "namespace": null,
                    "name": "\/vendor\/laravel\/framework\/src\/Illuminate\/Routing\/Route.php",
                    "line": 205
                }],
                "duration": 0.0016899999999999999,
                "duration_str": "1.69ms",
                "stmt_id": "\/Modules\/Affiliate\/Http\/Controllers\/AffiliateController.php:126",
                "connection": "ecommerce"
            }, {
                "sql": "select * from `front_pages` where `slug` = '\/affiliate' limit 1",
                "type": "query",
                "params": [],
                "bindings": ["\/affiliate"],
                "hints": ["Use <code>SELECT *<\/code> only if you need all columns from table",
                    "<code>LIMIT<\/code> without <code>ORDER BY<\/code> causes non-deterministic results, depending on the query execution plan"
                ],
                "show_copy": false,
                "backtrace": [{
                    "index": 13,
                    "namespace": null,
                    "name": "\/Modules\/Affiliate\/Http\/Controllers\/AffiliateController.php",
                    "line": 128
                }, {
                    "index": 14,
                    "namespace": null,
                    "name": "\/vendor\/laravel\/framework\/src\/Illuminate\/Routing\/Controller.php",
                    "line": 54
                }, {
                    "index": 15,
                    "namespace": null,
                    "name": "\/vendor\/laravel\/framework\/src\/Illuminate\/Routing\/ControllerDispatcher.php",
                    "line": 43
                }, {
                    "index": 16,
                    "namespace": null,
                    "name": "\/vendor\/laravel\/framework\/src\/Illuminate\/Routing\/Route.php",
                    "line": 259
                }, {
                    "index": 17,
                    "namespace": null,
                    "name": "\/vendor\/laravel\/framework\/src\/Illuminate\/Routing\/Route.php",
                    "line": 205
                }],
                "duration": 0.00069,
                "duration_str": "690\u03bcs",
                "stmt_id": "\/Modules\/Affiliate\/Http\/Controllers\/AffiliateController.php:128",
                "connection": "ecommerce"
            }]
        },
        "models": {
            "data": {
                "Modules\\FrontendManage\\Entities\\FrontPage": 1
            },
            "count": 1
        },
        "session": {
            "domain": "main",
            "_token": "UXqxMUzO3NAybCH6WduKA4cNN4PbjD0GdofYpyEl",
            "_previous": "array:1 [\n  \"url\" => \"https:\/\/ecommerce.eimaths-th.com\/affiliate\"\n]",
            "_flash": "array:2 [\n  \"old\" => []\n  \"new\" => []\n]",
            "PHPDEBUGBAR_STACK_DATA": "[]"
        },
        "request": {
            "path_info": "\/affiliate",
            "status_code": "<pre class=sf-dump id=sf-dump-1385656747 data-indent-pad=\"  \"><span class=sf-dump-num>200<\/span>\n<\/pre><script>Sfdump(\"sf-dump-1385656747\", {\"maxDepth\":0})<\/script>\n",
            "status_text": "OK",
            "format": "html",
            "content_type": "text\/html; charset=UTF-8",
            "request_query": "<pre class=sf-dump id=sf-dump-1062989474 data-indent-pad=\"  \">[]\n<\/pre><script>Sfdump(\"sf-dump-1062989474\", {\"maxDepth\":0})<\/script>\n",
            "request_request": "<pre class=sf-dump id=sf-dump-966953105 data-indent-pad=\"  \">[]\n<\/pre><script>Sfdump(\"sf-dump-966953105\", {\"maxDepth\":0})<\/script>\n",
            "request_headers": "<pre class=sf-dump id=sf-dump-1434198212 data-indent-pad=\"  \"><span class=sf-dump-note>array:15<\/span> [<samp data-depth=1 class=sf-dump-expanded>\n  \"<span class=sf-dump-key>host<\/span>\" => <span class=sf-dump-note>array:1<\/span> [<samp data-depth=2 class=sf-dump-compact>\n    <span class=sf-dump-index>0<\/span> => \"<span class=sf-dump-str title=\"24 characters\">ecommerce.eimaths-th.com<\/span>\"\n  <\/samp>]\n  \"<span class=sf-dump-key>connection<\/span>\" => <span class=sf-dump-note>array:1<\/span> [<samp data-depth=2 class=sf-dump-compact>\n    <span class=sf-dump-index>0<\/span> => \"<span class=sf-dump-str title=\"10 characters\">keep-alive<\/span>\"\n  <\/samp>]\n  \"<span class=sf-dump-key>sec-ch-ua<\/span>\" => <span class=sf-dump-note>array:1<\/span> [<samp data-depth=2 class=sf-dump-compact>\n    <span class=sf-dump-index>0<\/span> => \"<span class=sf-dump-str title=\"65 characters\">&quot;Chromium&quot;;v=&quot;130&quot;, &quot;Google Chrome&quot;;v=&quot;130&quot;, &quot;Not?A_Brand&quot;;v=&quot;99&quot;<\/span>\"\n  <\/samp>]\n  \"<span class=sf-dump-key>sec-ch-ua-mobile<\/span>\" => <span class=sf-dump-note>array:1<\/span> [<samp data-depth=2 class=sf-dump-compact>\n    <span class=sf-dump-index>0<\/span> => \"<span class=sf-dump-str title=\"2 characters\">?0<\/span>\"\n  <\/samp>]\n  \"<span class=sf-dump-key>sec-ch-ua-platform<\/span>\" => <span class=sf-dump-note>array:1<\/span> [<samp data-depth=2 class=sf-dump-compact>\n    <span class=sf-dump-index>0<\/span> => \"<span class=sf-dump-str title=\"9 characters\">&quot;Windows&quot;<\/span>\"\n  <\/samp>]\n  \"<span class=sf-dump-key>upgrade-insecure-requests<\/span>\" => <span class=sf-dump-note>array:1<\/span> [<samp data-depth=2 class=sf-dump-compact>\n    <span class=sf-dump-index>0<\/span> => \"<span class=sf-dump-str>1<\/span>\"\n  <\/samp>]\n  \"<span class=sf-dump-key>user-agent<\/span>\" => <span class=sf-dump-note>array:1<\/span> [<samp data-depth=2 class=sf-dump-compact>\n    <span class=sf-dump-index>0<\/span> => \"<span class=sf-dump-str title=\"111 characters\">Mozilla\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/130.0.0.0 Safari\/537.36<\/span>\"\n  <\/samp>]\n  \"<span class=sf-dump-key>accept<\/span>\" => <span class=sf-dump-note>array:1<\/span> [<samp data-depth=2 class=sf-dump-compact>\n    <span class=sf-dump-index>0<\/span> => \"<span class=sf-dump-str title=\"135 characters\">text\/html,application\/xhtml+xml,application\/xml;q=0.9,image\/avif,image\/webp,image\/apng,*\/*;q=0.8,application\/signed-exchange;v=b3;q=0.7<\/span>\"\n  <\/samp>]\n  \"<span class=sf-dump-key>sec-fetch-site<\/span>\" => <span class=sf-dump-note>array:1<\/span> [<samp data-depth=2 class=sf-dump-compact>\n    <span class=sf-dump-index>0<\/span> => \"<span class=sf-dump-str title=\"4 characters\">none<\/span>\"\n  <\/samp>]\n  \"<span class=sf-dump-key>sec-fetch-mode<\/span>\" => <span class=sf-dump-note>array:1<\/span> [<samp data-depth=2 class=sf-dump-compact>\n    <span class=sf-dump-index>0<\/span> => \"<span class=sf-dump-str title=\"8 characters\">navigate<\/span>\"\n  <\/samp>]\n  \"<span class=sf-dump-key>sec-fetch-user<\/span>\" => <span class=sf-dump-note>array:1<\/span> [<samp data-depth=2 class=sf-dump-compact>\n    <span class=sf-dump-index>0<\/span> => \"<span class=sf-dump-str title=\"2 characters\">?1<\/span>\"\n  <\/samp>]\n  \"<span class=sf-dump-key>sec-fetch-dest<\/span>\" => <span class=sf-dump-note>array:1<\/span> [<samp data-depth=2 class=sf-dump-compact>\n    <span class=sf-dump-index>0<\/span> => \"<span class=sf-dump-str title=\"8 characters\">document<\/span>\"\n  <\/samp>]\n  \"<span class=sf-dump-key>accept-encoding<\/span>\" => <span class=sf-dump-note>array:1<\/span> [<samp data-depth=2 class=sf-dump-compact>\n    <span class=sf-dump-index>0<\/span> => \"<span class=sf-dump-str title=\"23 characters\">gzip, deflate, br, zstd<\/span>\"\n  <\/samp>]\n  \"<span class=sf-dump-key>accept-language<\/span>\" => <span class=sf-dump-note>array:1<\/span> [<samp data-depth=2 class=sf-dump-compact>\n    <span class=sf-dump-index>0<\/span> => \"<span class=sf-dump-str title=\"35 characters\">th-TH,th;q=0.9,en-US;q=0.8,en;q=0.7<\/span>\"\n  <\/samp>]\n  \"<span class=sf-dump-key>cookie<\/span>\" => <span class=sf-dump-note>array:1<\/span> [<samp data-depth=2 class=sf-dump-compact>\n    <span class=sf-dump-index>0<\/span> => \"<span class=sf-dump-str title=\"985 characters\">_gcl_au=1.1.1340509618.1731293720; _ga=GA1.1.436658098.1731293720; _ga_0CDRZY1XT1=GS1.1.1731465288.3.0.1731465296.0.0.0; _ga_ZV46434GKP=GS1.1.1731465288.3.0.1731465296.0.0.0; strictly_allow=1; performance_allow=1; functional_allow=1; targeting_allow=1; allow=1; XSRF-TOKEN=eyJpdiI6Im5hTWVyL0JKNjhqZ2Z1QjBXMGF3V0E9PSIsInZhbHVlIjoiU1NLU1VXY1k2Rmh0UUUydVNlYzRMNlluN01ucFh0MkVPeEFFbHFQVy9vMHpSNGgyanVQbjljdnVFdjVMSE9QUkxVMnlNQWJocml5a0Z2RE1PN0xaQ3U4clJ2MEtCZVVuQUhuZ2ZYYkFwOVpYcFlCVUR0dkhqY1BiUXRQTUJ3WjUiLCJtYWMiOiIxYTVjOTM3YTU2ZTFjNDNmYjAzMDNhYmExODBmNTdiMzBmMGE3YTYyNzUxOWU5YTI4MGYyNGMwYmE2MTJkMjkxIiwidGFnIjoiIn0%3D; eimaths_ecommerce_session=eyJpdiI6Im1Mem14K0c5NVhCaDVSemo3UnFRNkE9PSIsInZhbHVlIjoiaUZKYlRqOXBhNktIc1RHVTloMDl4U2VzNHFESEltSFJwZ3NFelpnNW1MRnpjV3lJNDFQZU9MMmxMd3pLcVdFM2xmd2RheXQxSDg4YjRWTlZ0bXRTT2Q5QTBLcVBqdFAyVHlhTy9ZTGJlK3F6eUFhTW1IaUdYNUNBWmFlZU5CU1giLCJtYWMiOiI5OGNiYjc2NjQ5YzZmMmQ0ZDQ1MDk3M2Y5YTNjYjdmMGRmY2JjNDI3NGQ5ZTQwYmVkYjZiYmVlMzYzMDRlYWM0IiwidGFnIjoiIn0%3D<\/span>\"\n  <\/samp>]\n<\/samp>]\n<\/pre><script>Sfdump(\"sf-dump-1434198212\", {\"maxDepth\":0})<\/script>\n",
            "request_server": "<pre class=sf-dump id=sf-dump-1735167352 data-indent-pad=\"  \"><span class=sf-dump-note>array:46<\/span> [<samp data-depth=1 class=sf-dump-expanded>\n  \"<span class=sf-dump-key>REDIRECT_UNIQUE_ID<\/span>\" => \"<span class=sf-dump-str title=\"27 characters\">ZzQQvy0gghpXBD9rKtrsGwAAAAQ<\/span>\"\n  \"<span class=sf-dump-key>REDIRECT_HTTPS<\/span>\" => \"<span class=sf-dump-str title=\"2 characters\">on<\/span>\"\n  \"<span class=sf-dump-key>REDIRECT_SSL_TLS_SNI<\/span>\" => \"<span class=sf-dump-str title=\"24 characters\">ecommerce.eimaths-th.com<\/span>\"\n  \"<span class=sf-dump-key>REDIRECT_STATUS<\/span>\" => \"<span class=sf-dump-str title=\"3 characters\">200<\/span>\"\n  \"<span class=sf-dump-key>UNIQUE_ID<\/span>\" => \"<span class=sf-dump-str title=\"27 characters\">ZzQQvy0gghpXBD9rKtrsGwAAAAQ<\/span>\"\n  \"<span class=sf-dump-key>HTTPS<\/span>\" => \"<span class=sf-dump-str title=\"2 characters\">on<\/span>\"\n  \"<span class=sf-dump-key>SSL_TLS_SNI<\/span>\" => \"<span class=sf-dump-str title=\"24 characters\">ecommerce.eimaths-th.com<\/span>\"\n  \"<span class=sf-dump-key>HTTP_HOST<\/span>\" => \"<span class=sf-dump-str title=\"24 characters\">ecommerce.eimaths-th.com<\/span>\"\n  \"<span class=sf-dump-key>HTTP_CONNECTION<\/span>\" => \"<span class=sf-dump-str title=\"10 characters\">keep-alive<\/span>\"\n  \"<span class=sf-dump-key>HTTP_SEC_CH_UA<\/span>\" => \"<span class=sf-dump-str title=\"65 characters\">&quot;Chromium&quot;;v=&quot;130&quot;, &quot;Google Chrome&quot;;v=&quot;130&quot;, &quot;Not?A_Brand&quot;;v=&quot;99&quot;<\/span>\"\n  \"<span class=sf-dump-key>HTTP_SEC_CH_UA_MOBILE<\/span>\" => \"<span class=sf-dump-str title=\"2 characters\">?0<\/span>\"\n  \"<span class=sf-dump-key>HTTP_SEC_CH_UA_PLATFORM<\/span>\" => \"<span class=sf-dump-str title=\"9 characters\">&quot;Windows&quot;<\/span>\"\n  \"<span class=sf-dump-key>HTTP_UPGRADE_INSECURE_REQUESTS<\/span>\" => \"<span class=sf-dump-str>1<\/span>\"\n  \"<span class=sf-dump-key>HTTP_USER_AGENT<\/span>\" => \"<span class=sf-dump-str title=\"111 characters\">Mozilla\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/130.0.0.0 Safari\/537.36<\/span>\"\n  \"<span class=sf-dump-key>HTTP_ACCEPT<\/span>\" => \"<span class=sf-dump-str title=\"135 characters\">text\/html,application\/xhtml+xml,application\/xml;q=0.9,image\/avif,image\/webp,image\/apng,*\/*;q=0.8,application\/signed-exchange;v=b3;q=0.7<\/span>\"\n  \"<span class=sf-dump-key>HTTP_SEC_FETCH_SITE<\/span>\" => \"<span class=sf-dump-str title=\"4 characters\">none<\/span>\"\n  \"<span class=sf-dump-key>HTTP_SEC_FETCH_MODE<\/span>\" => \"<span class=sf-dump-str title=\"8 characters\">navigate<\/span>\"\n  \"<span class=sf-dump-key>HTTP_SEC_FETCH_USER<\/span>\" => \"<span class=sf-dump-str title=\"2 characters\">?1<\/span>\"\n  \"<span class=sf-dump-key>HTTP_SEC_FETCH_DEST<\/span>\" => \"<span class=sf-dump-str title=\"8 characters\">document<\/span>\"\n  \"<span class=sf-dump-key>HTTP_ACCEPT_ENCODING<\/span>\" => \"<span class=sf-dump-str title=\"23 characters\">gzip, deflate, br, zstd<\/span>\"\n  \"<span class=sf-dump-key>HTTP_ACCEPT_LANGUAGE<\/span>\" => \"<span class=sf-dump-str title=\"35 characters\">th-TH,th;q=0.9,en-US;q=0.8,en;q=0.7<\/span>\"\n  \"<span class=sf-dump-key>HTTP_COOKIE<\/span>\" => \"<span class=sf-dump-str title=\"985 characters\">_gcl_au=1.1.1340509618.1731293720; _ga=GA1.1.436658098.1731293720; _ga_0CDRZY1XT1=GS1.1.1731465288.3.0.1731465296.0.0.0; _ga_ZV46434GKP=GS1.1.1731465288.3.0.1731465296.0.0.0; strictly_allow=1; performance_allow=1; functional_allow=1; targeting_allow=1; allow=1; XSRF-TOKEN=eyJpdiI6Im5hTWVyL0JKNjhqZ2Z1QjBXMGF3V0E9PSIsInZhbHVlIjoiU1NLU1VXY1k2Rmh0UUUydVNlYzRMNlluN01ucFh0MkVPeEFFbHFQVy9vMHpSNGgyanVQbjljdnVFdjVMSE9QUkxVMnlNQWJocml5a0Z2RE1PN0xaQ3U4clJ2MEtCZVVuQUhuZ2ZYYkFwOVpYcFlCVUR0dkhqY1BiUXRQTUJ3WjUiLCJtYWMiOiIxYTVjOTM3YTU2ZTFjNDNmYjAzMDNhYmExODBmNTdiMzBmMGE3YTYyNzUxOWU5YTI4MGYyNGMwYmE2MTJkMjkxIiwidGFnIjoiIn0%3D; eimaths_ecommerce_session=eyJpdiI6Im1Mem14K0c5NVhCaDVSemo3UnFRNkE9PSIsInZhbHVlIjoiaUZKYlRqOXBhNktIc1RHVTloMDl4U2VzNHFESEltSFJwZ3NFelpnNW1MRnpjV3lJNDFQZU9MMmxMd3pLcVdFM2xmd2RheXQxSDg4YjRWTlZ0bXRTT2Q5QTBLcVBqdFAyVHlhTy9ZTGJlK3F6eUFhTW1IaUdYNUNBWmFlZU5CU1giLCJtYWMiOiI5OGNiYjc2NjQ5YzZmMmQ0ZDQ1MDk3M2Y5YTNjYjdmMGRmY2JjNDI3NGQ5ZTQwYmVkYjZiYmVlMzYzMDRlYWM0IiwidGFnIjoiIn0%3D<\/span>\"\n  \"<span class=sf-dump-key>PATH<\/span>\" => \"<span class=sf-dump-str title=\"49 characters\">\/usr\/local\/sbin:\/usr\/local\/bin:\/usr\/sbin:\/usr\/bin<\/span>\"\n  \"<span class=sf-dump-key>SERVER_SIGNATURE<\/span>\" => \"\"\n  \"<span class=sf-dump-key>SERVER_SOFTWARE<\/span>\" => \"<span class=sf-dump-str title=\"51 characters\">Apache\/2.4.6 (CentOS) OpenSSL\/1.0.2k-fips PHP\/8.2.9<\/span>\"\n  \"<span class=sf-dump-key>SERVER_NAME<\/span>\" => \"<span class=sf-dump-str title=\"24 characters\">ecommerce.eimaths-th.com<\/span>\"\n  \"<span class=sf-dump-key>SERVER_ADDR<\/span>\" => \"<span class=sf-dump-str title=\"10 characters\">10.148.0.3<\/span>\"\n  \"<span class=sf-dump-key>SERVER_PORT<\/span>\" => \"<span class=sf-dump-str title=\"3 characters\">443<\/span>\"\n  \"<span class=sf-dump-key>REMOTE_ADDR<\/span>\" => \"<span class=sf-dump-str title=\"13 characters\">49.49.224.104<\/span>\"\n  \"<span class=sf-dump-key>DOCUMENT_ROOT<\/span>\" => \"<span class=sf-dump-str title=\"23 characters\">\/var\/www\/html\/ecommerce<\/span>\"\n  \"<span class=sf-dump-key>REQUEST_SCHEME<\/span>\" => \"<span class=sf-dump-str title=\"5 characters\">https<\/span>\"\n  \"<span class=sf-dump-key>CONTEXT_PREFIX<\/span>\" => \"\"\n  \"<span class=sf-dump-key>CONTEXT_DOCUMENT_ROOT<\/span>\" => \"<span class=sf-dump-str title=\"23 characters\">\/var\/www\/html\/ecommerce<\/span>\"\n  \"<span class=sf-dump-key>SERVER_ADMIN<\/span>\" => \"<span class=sf-dump-str title=\"14 characters\">root@localhost<\/span>\"\n  \"<span class=sf-dump-key>SCRIPT_FILENAME<\/span>\" => \"<span class=sf-dump-str title=\"33 characters\">\/var\/www\/html\/ecommerce\/index.php<\/span>\"\n  \"<span class=sf-dump-key>REMOTE_PORT<\/span>\" => \"<span class=sf-dump-str title=\"5 characters\">56432<\/span>\"\n  \"<span class=sf-dump-key>REDIRECT_URL<\/span>\" => \"<span class=sf-dump-str title=\"10 characters\">\/affiliate<\/span>\"\n  \"<span class=sf-dump-key>GATEWAY_INTERFACE<\/span>\" => \"<span class=sf-dump-str title=\"7 characters\">CGI\/1.1<\/span>\"\n  \"<span class=sf-dump-key>SERVER_PROTOCOL<\/span>\" => \"<span class=sf-dump-str title=\"8 characters\">HTTP\/1.1<\/span>\"\n  \"<span class=sf-dump-key>REQUEST_METHOD<\/span>\" => \"<span class=sf-dump-str title=\"3 characters\">GET<\/span>\"\n  \"<span class=sf-dump-key>QUERY_STRING<\/span>\" => \"\"\n  \"<span class=sf-dump-key>REQUEST_URI<\/span>\" => \"<span class=sf-dump-str title=\"10 characters\">\/affiliate<\/span>\"\n  \"<span class=sf-dump-key>SCRIPT_NAME<\/span>\" => \"<span class=sf-dump-str title=\"10 characters\">\/index.php<\/span>\"\n  \"<span class=sf-dump-key>PHP_SELF<\/span>\" => \"<span class=sf-dump-str title=\"10 characters\">\/index.php<\/span>\"\n  \"<span class=sf-dump-key>REQUEST_TIME_FLOAT<\/span>\" => <span class=sf-dump-num>1731465407.3999<\/span>\n  \"<span class=sf-dump-key>REQUEST_TIME<\/span>\" => <span class=sf-dump-num>1731465407<\/span>\n<\/samp>]\n<\/pre><script>Sfdump(\"sf-dump-1735167352\", {\"maxDepth\":0})<\/script>\n",
            "request_cookies": "<pre class=sf-dump id=sf-dump-165731796 data-indent-pad=\"  \"><span class=sf-dump-note>array:11<\/span> [<samp data-depth=1 class=sf-dump-expanded>\n  \"<span class=sf-dump-key>_gcl_au<\/span>\" => <span class=sf-dump-const>null<\/span>\n  \"<span class=sf-dump-key>_ga<\/span>\" => <span class=sf-dump-const>null<\/span>\n  \"<span class=sf-dump-key>_ga_0CDRZY1XT1<\/span>\" => <span class=sf-dump-const>null<\/span>\n  \"<span class=sf-dump-key>_ga_ZV46434GKP<\/span>\" => <span class=sf-dump-const>null<\/span>\n  \"<span class=sf-dump-key>strictly_allow<\/span>\" => <span class=sf-dump-const>null<\/span>\n  \"<span class=sf-dump-key>performance_allow<\/span>\" => <span class=sf-dump-const>null<\/span>\n  \"<span class=sf-dump-key>functional_allow<\/span>\" => <span class=sf-dump-const>null<\/span>\n  \"<span class=sf-dump-key>targeting_allow<\/span>\" => <span class=sf-dump-const>null<\/span>\n  \"<span class=sf-dump-key>allow<\/span>\" => <span class=sf-dump-const>null<\/span>\n  \"<span class=sf-dump-key>XSRF-TOKEN<\/span>\" => \"<span class=sf-dump-str title=\"40 characters\">UXqxMUzO3NAybCH6WduKA4cNN4PbjD0GdofYpyEl<\/span>\"\n  \"<span class=sf-dump-key>eimaths_ecommerce_session<\/span>\" => \"<span class=sf-dump-str title=\"40 characters\">FPrOMKAZWterAqd4vP8gQSeeDSTQXMQ2THOWLnri<\/span>\"\n<\/samp>]\n<\/pre><script>Sfdump(\"sf-dump-165731796\", {\"maxDepth\":0})<\/script>\n",
            "response_headers": "<pre class=sf-dump id=sf-dump-100347884 data-indent-pad=\"  \"><span class=sf-dump-note>array:5<\/span> [<samp data-depth=1 class=sf-dump-expanded>\n  \"<span class=sf-dump-key>content-type<\/span>\" => <span class=sf-dump-note>array:1<\/span> [<samp data-depth=2 class=sf-dump-compact>\n    <span class=sf-dump-index>0<\/span> => \"<span class=sf-dump-str title=\"24 characters\">text\/html; charset=UTF-8<\/span>\"\n  <\/samp>]\n  \"<span class=sf-dump-key>cache-control<\/span>\" => <span class=sf-dump-note>array:1<\/span> [<samp data-depth=2 class=sf-dump-compact>\n    <span class=sf-dump-index>0<\/span> => \"<span class=sf-dump-str title=\"17 characters\">no-cache, private<\/span>\"\n  <\/samp>]\n  \"<span class=sf-dump-key>date<\/span>\" => <span class=sf-dump-note>array:1<\/span> [<samp data-depth=2 class=sf-dump-compact>\n    <span class=sf-dump-index>0<\/span> => \"<span class=sf-dump-str title=\"29 characters\">Wed, 13 Nov 2024 02:36:47 GMT<\/span>\"\n  <\/samp>]\n  \"<span class=sf-dump-key>set-cookie<\/span>\" => <span class=sf-dump-note>array:2<\/span> [<samp data-depth=2 class=sf-dump-compact>\n    <span class=sf-dump-index>0<\/span> => \"<span class=sf-dump-str title=\"414 characters\">XSRF-TOKEN=eyJpdiI6InVYdjdKMEU4eXNyZWJ3SHh2UnU1NVE9PSIsInZhbHVlIjoibkhvOHViZVB3TXpITGxlSEN6eEtCMjdKMFd0aUx3L1B0NVczQ2FhZ05jS2tjc3htUzE2ODZNTVUzaGFUd2NOTHYvV3dlTitiV2I5QWY5U0g2aW5BSXQ2UUs0WDZJekNDLzlLVGw2eDZqcytnUXZhbUpDOHY1MUlNcHI3ZVZnQ28iLCJtYWMiOiI4M2M5NDJmYmJlMjg1ODIxNjk3OTE0MjVjZGZiZDQ4ZTQwOTQ5MjcxMzI0YzFjZThjMDMzYThhNjhkYmY1ZDY2IiwidGFnIjoiIn0%3D; expires=Wed, 13 Nov 2024 04:36:47 GMT; Max-Age=7200; path=\/<\/span>\"\n    <span class=sf-dump-index>1<\/span> => \"<span class=sf-dump-str title=\"439 characters\">eimaths_ecommerce_session=eyJpdiI6ImF0SmFnNFcvaUFCMzltdlczZ3F6ZUE9PSIsInZhbHVlIjoiYnc5RmFOWE02cUFkTmpoSHNVTWhzRkd3Y3F0L0RueXNnWHpBVitRKzRFMkpYWk5FUUJCZlUreDUyZHZNdFFhcEc0QjZvVkR5RUFvNDVodWFkcXJiWlVwN2F5Mng4WndtUWhJRERKUGVQdXY5d0NncEp1TlFnV0k1M1ZSUzZhY2ciLCJtYWMiOiI2YTc1NDY1NWRiMmM0NWM4YjM0NDkwNmZkZTYzZDQ4Y2JkNjIyOGI5YmExMzNlNTQ5ZWQ3MjYzMWUxYzVlNWRkIiwidGFnIjoiIn0%3D; expires=Wed, 13 Nov 2024 04:36:47 GMT; Max-Age=7200; path=\/; httponly<\/span>\"\n  <\/samp>]\n  \"<span class=sf-dump-key>Set-Cookie<\/span>\" => <span class=sf-dump-note>array:2<\/span> [<samp data-depth=2 class=sf-dump-compact>\n    <span class=sf-dump-index>0<\/span> => \"<span class=sf-dump-str title=\"400 characters\">XSRF-TOKEN=eyJpdiI6InVYdjdKMEU4eXNyZWJ3SHh2UnU1NVE9PSIsInZhbHVlIjoibkhvOHViZVB3TXpITGxlSEN6eEtCMjdKMFd0aUx3L1B0NVczQ2FhZ05jS2tjc3htUzE2ODZNTVUzaGFUd2NOTHYvV3dlTitiV2I5QWY5U0g2aW5BSXQ2UUs0WDZJekNDLzlLVGw2eDZqcytnUXZhbUpDOHY1MUlNcHI3ZVZnQ28iLCJtYWMiOiI4M2M5NDJmYmJlMjg1ODIxNjk3OTE0MjVjZGZiZDQ4ZTQwOTQ5MjcxMzI0YzFjZThjMDMzYThhNjhkYmY1ZDY2IiwidGFnIjoiIn0%3D; expires=Wed, 13-Nov-2024 04:36:47 GMT; path=\/<\/span>\"\n    <span class=sf-dump-index>1<\/span> => \"<span class=sf-dump-str title=\"425 characters\">eimaths_ecommerce_session=eyJpdiI6ImF0SmFnNFcvaUFCMzltdlczZ3F6ZUE9PSIsInZhbHVlIjoiYnc5RmFOWE02cUFkTmpoSHNVTWhzRkd3Y3F0L0RueXNnWHpBVitRKzRFMkpYWk5FUUJCZlUreDUyZHZNdFFhcEc0QjZvVkR5RUFvNDVodWFkcXJiWlVwN2F5Mng4WndtUWhJRERKUGVQdXY5d0NncEp1TlFnV0k1M1ZSUzZhY2ciLCJtYWMiOiI2YTc1NDY1NWRiMmM0NWM4YjM0NDkwNmZkZTYzZDQ4Y2JkNjIyOGI5YmExMzNlNTQ5ZWQ3MjYzMWUxYzVlNWRkIiwidGFnIjoiIn0%3D; expires=Wed, 13-Nov-2024 04:36:47 GMT; path=\/; httponly<\/span>\"\n  <\/samp>]\n<\/samp>]\n<\/pre><script>Sfdump(\"sf-dump-100347884\", {\"maxDepth\":0})<\/script>\n",
            "session_attributes": "<pre class=sf-dump id=sf-dump-178392542 data-indent-pad=\"  \"><span class=sf-dump-note>array:5<\/span> [<samp data-depth=1 class=sf-dump-expanded>\n  \"<span class=sf-dump-key>domain<\/span>\" => \"<span class=sf-dump-str title=\"4 characters\">main<\/span>\"\n  \"<span class=sf-dump-key>_token<\/span>\" => \"<span class=sf-dump-str title=\"40 characters\">UXqxMUzO3NAybCH6WduKA4cNN4PbjD0GdofYpyEl<\/span>\"\n  \"<span class=sf-dump-key>_previous<\/span>\" => <span class=sf-dump-note>array:1<\/span> [<samp data-depth=2 class=sf-dump-compact>\n    \"<span class=sf-dump-key>url<\/span>\" => \"<span class=sf-dump-str title=\"42 characters\">https:\/\/ecommerce.eimaths-th.com\/affiliate<\/span>\"\n  <\/samp>]\n  \"<span class=sf-dump-key>_flash<\/span>\" => <span class=sf-dump-note>array:2<\/span> [<samp data-depth=2 class=sf-dump-compact>\n    \"<span class=sf-dump-key>old<\/span>\" => []\n    \"<span class=sf-dump-key>new<\/span>\" => []\n  <\/samp>]\n  \"<span class=sf-dump-key>PHPDEBUGBAR_STACK_DATA<\/span>\" => []\n<\/samp>]\n<\/pre><script>Sfdump(\"sf-dump-178392542\", {\"maxDepth\":0})<\/script>\n"
        }
    }, "Xe03d2b2dd0d22ab32b2cee001983f83f");
</script>

</body>

<input type="hidden" name="lat" class="lat" value="13.809561">
<input type="hidden" name="lng" class="lng" value="-619.551604">
<input type="hidden" name="zoom" class="zoom" value="18">


</html>
