<meta charset="utf-8" class="test" />
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="icon" href="{{ asset(Settings('favicon')) }}{{ assetVersion() }}" type="image/png" />
<title>
    {{ Settings('site_title') ? Settings('site_title') : 'Infix LMS' }}
</title>
<meta name="_token" content="{!! csrf_token() !!}" />
@include('backend.partials.style')
<script src="{{ asset('js/common.js')}}{{assetVersion()}}" type="application/javascript"></script>


<script>
    window.Laravel = {
        "baseUrl": '{{ url('/') }}' + '/',
        "current_path_without_domain": '{{ request()->path() }}',
        "csrfToken": '{{ csrf_token() }}',
    }
</script>

<script>
    window._locale = '{{ app()->getLocale() }}';
    window._translations = '';
    window.jsLang = function(key, replace) {
        let output = '';

        if (key.includes('.')) {
            const parts = key.split('.');
            key = parts[1];
        }

        if (window._translations.hasOwnProperty(key)) {
            output = window._translations[key];
        } else {
            output = key;
        }
        return output;

    }

    function localizeNumbers(text) {
        let numberMap = {
            '0': '{{ translatedNumber(0) }}',
            '1': '{{ translatedNumber(1) }}',
            '2': '{{ translatedNumber(2) }}',
            '3': '{{ translatedNumber(3) }}',
            '4': '{{ translatedNumber(4) }}',
            '5': '{{ translatedNumber(5) }}',
            '6': '{{ translatedNumber(6) }}',
            '7': '{{ translatedNumber(7) }}',
            '8': '{{ translatedNumber(8) }}',
            '9': '{{ translatedNumber(9) }}',
        };
        text = text.toString();
        return text.replace(/[0-9]/g, function(match) {
            return numberMap[match];
        });
    }

    window.translatedNumber = function(data) {

        var parsedValue = parseFloat(data);

        if (!isNaN(parsedValue) && isFinite(parsedValue)) {
            return localizeNumbers(data);
        } else {
            return data;
        }

    }
</script>


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

<script>
    const RTL = "{{ isRtl() }}";
    const LANG = "{{ app()->getLocale() }}";
</script>

@livewireStyles
