<meta charset="utf-8" class="test" />
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="icon" href="{{ asset('uploads/main/files/09-08-2024/cropped-footer-logo-32x32.png') }}" type="image/png" />
<title>
    eiMaths ecommerce
</title>
<link rel="stylesheet" href="{{ asset('backend/css/jquery-ui.css') }}{{ assetVersion() }}" />

<link rel="stylesheet" href="{{ asset('backend/vendors/font_awesome/css/all.min.css') }}{{ assetVersion() }}" />
<link rel="stylesheet" href="{{ asset('backend/css/themify-icons.css') }}{{ assetVersion() }}" />


<link rel="stylesheet" href="{{ asset('chat/css/style.css') }}{{ assetVersion() }}">
{{-- <link rel="stylesheet" href="{{ asset('backend/css/preloader.css')}}{{assetVersion()}}"/> --}}
@if (isModuleActive('WhatsappSupport'))
    <link rel="stylesheet" href="{{ asset('whatsapp-support/style.css') }}{{ assetVersion() }}" />
@endif

<link rel="stylesheet" href="{{ asset('backend/css/fullcalendar.min.css') }}{{ assetVersion() }}">

<link rel="stylesheet" href="{{ asset('backend/css/app.css') }}{{ assetVersion() }}">


{{-- <link rel="stylesheet" href="{{ asset('backend/css/app1.css')}}{{assetVersion()}}"> --}}
{{-- @php
        dd(isRtl());
    @endphp --}}
@if (isRtl())
    <link rel="stylesheet" href="{{ asset('backend/css/backend_style_rtl.css') }}{{ assetVersion() }}" />
@else
    <link rel="stylesheet" href="{{ asset('backend/css/backend_style.css') }}{{ assetVersion() }}" />
    
@endif
{{-- <link rel="stylesheet" href="{{ asset('backend/css/backend_style.css') }}{{ assetVersion() }}" /> --}}

<!-- uppy css -->

<link rel="stylesheet" href="{{ asset('vendor/uppy/uppy.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/uppy/media.css') }}">

<link rel="stylesheet" href="{{ asset('Modules/Affiliate/Resources/assets/css/daterangepicker.css') }}">
</link>
<style>
    .mtr-10 {
        margin-top: -10px;
    }

    .cursor-not-allowed {
        cursor: not-allowed;
    }

    .badge_5 {
        background: rgba(140, 143, 141, 0.1);
        font-size: 13px !important;
        font-weight: 500 !important;
        color: var(--secondary) !important;
        border: 0;
        display: inline-block;
        border-radius: 10px;
        padding: 7px 21px;
        white-space: nowrap;
        line-height: 1.2;
        text-transform: capitalize;
    }

    .primary_datepicker_input button {
        position: absolute;
        color: #828BB2;
        font-size: 14px;
        font-weight: 400;
        right: 0;
        background: transparent;
        border: 0;
        cursor: pointer;
        z-index: 999;
        top: 70%;
        transform: translateY(-50%);
        right: 14px;
    }

    .primary_datepicker_input button i {
        top: 0;
        cursor: pointer;
        z-index: 9;
    }
</style>




<script src="{{asset('/js/common.js')}}{{ assetVersion() }}" type="application/javascript"></script>


<script>
    window.Laravel = {
        "baseUrl": "{{ url('/') }}/", // Generates the full base URL of your Laravel app
        "current_path_without_domain": "{{ request()->path() }}", // Gets the current path excluding the domain
        "csrfToken": "{{ csrf_token() }}" // Generates the CSRF token
    }
</script>


<script>
    window._locale = 'en';
    window._translations = {
        "data_success": "Success ",
        "data_update": "Update ",
        "data_showing": "showing ",
        "data_entries": " entries",
        "data_to": "to",
        "data_of": "of",
        "data_no_data_available_in_table": "No Data Available In This Table",
        "data_search": "Search",
        "data_drag_drop": "Drag & Drop your files or",
        "data_browse": "Browse",
        "data_invalid_file": "Field contains invalid files",
        "data_wait_for_size": "Waiting for size",
        "data_upload": "Upload",
        "data_retry": "Retry",
        "data_undo": "Undo",
        "data_cancel": "Cancel",
        "data_abort": "Abort",
        "data_remove": "Remove",
        "data_tap_to_undo": "tap to undo",
        "data_tap_to_retry": "tap to retry",
        "data_tab_or_wait": "tap to cancel | Wait until see Uploaded completed",
        "data_error_during_revert": "Error during revert",
        "data_error_during_upload": "Error during upload",
        "data_upload_cancel": "Upload cancelled",
        "data_upload_complete": "Upload Completed",
        "data_uploading": "Uploading",
        "data_error_during_remove": "Error during remove",
        "data_removed": "Removed",
        "data_error_during_load": "Error during load",
        "data_added": "Added",
        "data_loading": "Loading...",
        "data_files_in_list": "files in list",
        "data_file_in_list": "file in list",
        "data_size_not_available": "Size not available",
        "data_operation_success": "Operation successful",
        "data_something_went_wrong": "Something went wrong",
        "data_error": "Error",
        "data_select_option": "Please select a option",
        "data_select_match": "Please match a option",
        "data_write_ans": "Please Write Answer",
        "data_quick_search": "Quick Search",
        "data_characters_count": "characters count",
        "no_data_available": "No data available",
        "data_all": "All",
        "data_info_filtered": "filtered from",
        "data_show": "Show",
        "data_processing": "Processing...",
        "data_no_match": "No matching records found",
        "data_first": "First",
        "data_last": "Last",
        "data_col_visibility": "Column Visibility",
        "data_col_restore": "Restore visibility",
        "data_collection": "Collection",
        "data_copy": "Copy",
        "data_copy_keys": "Press ctrl or u2318 + C to copy the table data to your system clipboard.",
        "data_copy_cancel": "To cancel, click this message or press escape.",
        "data_copy_success": "Copied 1 row to clipboard",
        "data_copied": "Copied",
        "data_rows_to_clipboard": "rows to clipboard",
        "data_copy_to_clipboard": "Copy to Clipboard",
        "data_csv": "CSV",
        "data_excel": "Excel",
        "data_show_all_rows": "Show all rows",
        "data_rows": "rows",
        "data_pdf": "PDF",
        "data_print": "Print",
        "data_restore": "Restore",
        "data_states": "States",
        "data_rename": "Rename",
        "data_remove_all": "Remove All",
        "data_create": "Create",
        "data_previous": "Previous",
        "data_next": "Next",
        "data_hours": "hours",
        "data_minutes": "minutes",
        "data_seconds": "seconds",
        "data_am": "AM",
        "data_pm": "PM",
        "data_Sun": "Sun",
        "data_Mon": "Mon",
        "data_Tue": "Tue",
        "data_Wed": "Wed",
        "data_Thu": "Thu",
        "data_Fri": "Fri",
        "data_Sat": "Sat",
        "data_January": "January",
        "data_February": "February",
        "data_March": "March",
        "data_April": "April",
        "data_May": "May",
        "data_June": "June",
        "data_July": "July",
        "data_August": "August",
        "data_September": "September",
        "data_October": "October",
        "data_November": "November",
        "data_December": "December",
        "data_info_empty": "Showing 0 to 0 of 0 entries",
        "data_aora_editor": "Editing with Aora Editor",
        "data_view_mobile": "View on mobile",
        "data_view_tablet": "View on tablet",
        "data_view_laptop": "View on laptop",
        "data_view_desktop": "View on desktop",
        "data_preview_on": "Preview ON",
        "data_preview_off": "Preview OFF",
        "data_fullscreen_on": "Fullscreen ON",
        "data_fullscreen_off": "Fullscreen Off",
        "data_save": "Save",
        "data_add_content": "Add content",
        "data_add_content_below": "Add content below",
        "data_paste_content": "Paste content",
        "data_paste_content_below": "Paste content below",
        "data_drag": ["Drag", "Draggable"],
        "data_move_up": "Move up",
        "data_move_down": "Move down",
        "data_setting": "Setting",
        "data_cut": "Cut",
        "data_delete": "Delete",
        "data_category": "Category",
        "data_type_to_search": "Type to search...",
        "data_drag_to_resize": "Drag to resize",
        "data_container_settings": "Container Settings",
        "data_confirm_delete_container": "Are you sure that you want to delete this container? This action can not be undone!",
        "data_confirm_delete_component": "Are you sure that you want to delete this component? This action can not be undone!",
        "data_select_options": "Select options",
        "data_selected": "selected",
        "data_select_all": "Select all",
        "data_unselect_all": "Unselect all",
        "data_none_selected": "None selected",
        "data_all_selected": "All selected",
        "data_select": "Select",
        "data_apply": "Apply",
        "data_from": "From",
        "data_custom": "Custom",
        "data_today": "Today",
        "data_yesterday": "Yesterday",
        "data_last_7_days": "Last 7 Days",
        "data_last_30_days": "Last 30 Days",
        "data_this_month": "This Month",
        "data_last_month": "Last Month",
        "data_this_year": "This Year",
        "data_last_year": "Last Year",
        "data_browse_files": "Browse Files",
        "data_10": "10",
        "data_25": "25",
        "data_50": "50",
        "data_100": "100",
        "data_minimum_payout": "Minimum Payout is",
        "data_your_balance": "Your Balance is",
        "data_withdraw_request_submitted": "Withdraw Request Submitted Successfully",
        "data_link_copied": "Link Copied Successfully"
    };
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
            '0': '0',
            '1': '1',
            '2': '2',
            '3': '3',
            '4': '4',
            '5': '5',
            '6': '6',
            '7': '7',
            '8': '8',
            '9': '9',
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
    const RTL = "";
    const LANG = "en";
</script>

<!-- Livewire Styles -->
<style>
    [wire\:loading],
    [wire\:loading\.delay],
    [wire\:loading\.inline-block],
    [wire\:loading\.inline],
    [wire\:loading\.block],
    [wire\:loading\.flex],
    [wire\:loading\.table],
    [wire\:loading\.grid],
    [wire\:loading\.inline-flex] {
        display: none;
    }

    [wire\:loading\.delay\.shortest],
    [wire\:loading\.delay\.shorter],
    [wire\:loading\.delay\.short],
    [wire\:loading\.delay\.long],
    [wire\:loading\.delay\.longer],
    [wire\:loading\.delay\.longest] {
        display: none;
    }

    [wire\:offline] {
        display: none;
    }

    [wire\:dirty]:not(textarea):not(input):not(select) {
        display: none;
    }

    input:-webkit-autofill,
    select:-webkit-autofill,
    textarea:-webkit-autofill {
        animation-duration: 50000s;
        animation-name: livewireautofill;
    }

    @keyframes livewireautofill {
        from {}
    }
</style>

{{-- <link rel='stylesheet' type='text/css' property='stylesheet' href='/_debugbar/assets/stylesheets' data-turbolinks-eval='false' data-turbo-eval='false'>
<script src='/_debugbar/assets/javascript' data-turbolinks-eval='false' data-turbo-eval='false'></script> --}}
{{-- <script data-turbo-eval='false'>
    jQuery.noConflict(true);
</script> --}}
