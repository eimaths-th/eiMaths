{{-- @extends('affiliate.auth.layouts.backend.master')
@section('title', 'My Affiliate')

@push('styles')
    <link rel="stylesheet" href="{{asset('Modules/Affiliate/Resources/assets/css')}}/daterangepicker.css"></link>
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
@endpush
@section('mainContent')

    {!! generateBreadcrumb() !!}
    @php
        // dd($data);
    @endphp

    <section class="admin-visitor-area up_st_admin_visitor">
        <div class="container-fluid p-0">
            @include('affiliate.auth.layouts.backend.components._filter')
            @include('affiliate.auth.layouts.backend.components._balance_info')
            <div class="row">
                <div class="col-lg-3">

                    @include('affiliate.auth.layouts.backend.components._create_link')
                    @include('affiliate.auth.layouts.backend.components._paypal_account')

                </div>
                <div class="col-lg-9">
                    @include('affiliate.auth.layouts.backend.components._table_data')
                </div>
            </div>
        </div>
        <div id="append_html"></div>
        @include('affiliate.auth.layouts.backend.components._withdraw_request_modal')
        @include('affiliate.auth.layouts.backend.components._balance_transfer_modal')
        @include('affiliate.backend._deleteModalForAjax',['item_name' => __("affiliate.Withdraw")])

        <input type="hidden" value="{{affiliateConfig('min_withdraw')}}" id="minimum_withdraw_amount">
        <input type="hidden" value="{{$user->affiliateWallet ? $user->affiliateWallet->amount : 0}}" id="user_balance">
        <input type="hidden" value="{{route('affiliate.withdraw_request.store')}}" id="withdraw_request_store_url">
        <input type="hidden" value="{{route('affiliate.withdraw_request.destroy')}}" id="withdraw_request_delete_url">
        <input type="hidden" value="{{route('affiliate.withdraw_request.edit',':id')}}" id="withdraw_request_edit_url">
        <input type="hidden" value="{{route('affiliate.withdraw_request.update',':id')}}" id="withdraw_request_update_url">
        <input type="hidden" value="{{route('affiliate.balance_transfer_to_wallet')}}" id="balance_transfer_url">
      
        <input type="hidden" value="{{affiliateConfig('min_withdraw')}}" id="minimum_withdraw_amount">
        <input type="hidden" value="{{$user->affiliateWallet ? $user->affiliateWallet->amount : 0}}" id="user_balance">
        <input type="hidden" value="#" id="withdraw_request_store_url">
        <input type="hidden" value="#" id="withdraw_request_delete_url">
        <input type="hidden" value="#" id="withdraw_request_edit_url">
        <input type="hidden" value="#" id="withdraw_request_update_url">
        <input type="hidden" value="#" id="balance_transfer_url">
    </section>
@endsection
@push('scripts')
    <script src="{{asset('Modules/Affiliate/Resources/assets/js')}}/infix_affiliate_link.js"></script>
    <script src="{{asset('Modules/Affiliate/Resources/assets/js')}}/balance_transfer.js"></script>
    <script src="{{asset('Modules/Affiliate/Resources/assets/js')}}/daterangepicker.min.js"></script>
    <script>
        (function ($) {
            "use strict";
            $(document).ready(function () {
                $('input[name="date_range_filter"]').daterangepicker({
                    ranges: {
                        'Today': [moment(), moment()],
                        'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                        'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                        'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                        'This Month': [moment().startOf('month'), moment().endOf('month')],
                        'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                    }

                }, function (start, end, label) {
                    $('#start').val(start.format('YYYY-MM-DD'))
                    $('#end').val(end.format('YYYY-MM-DD'))
                });
                $("#reset-date-filter").on('click', function () {
                    let filterRange = $('input[name="date_range_filter"]').val();
                    let formatDate = filterRange.split('-');
                    let startDate = dateFormat(formatDate[0]);
                    let endDate = dateFormat(formatDate[1]);
                    var params = [
                        "startDate=" + startDate,
                        "endDate=" + endDate
                    ];
                    window.location.href = window.location.protocol + "//" + window.location.host + window.location.pathname + '?' + params.join('&');
                });

                function dateFormat(date) {
                    var newdate = new Date(date);
                    var dd = ("0" + (newdate.getDate())).slice(-2);
                    var mm = ("0" + (newdate.getMonth() + 1)).slice(-2);
                    var y = newdate.getFullYear();
                    return y + '-' + mm + '-' + dd;
                }

            });
        })(jQuery);
    </script>
@endpush --}}


<!DOCTYPE html>
<html dir="ltr" class="ltr dark" lang="en">

<head>
    <meta charset="utf-8" class="test" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="csrf-token" content="S2b9x1qm9PItqnuUewh2B7IN3c7KcABR0nbRYaFy">
    <link rel="icon" href="{{ asset('uploads/main/files/09-08-2024/cropped-footer-logo-32x32.png') }}"
        type="image/png" />
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

    @if (isRtl())
        <link rel="stylesheet" href="{{ asset('backend/css/backend_style_rtl.css') }}{{ assetVersion() }}" />
    @else
        <link rel="stylesheet" href="{{ asset('backend/css/backend_style.css') }}{{ assetVersion() }}" />
    @endif

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

    <link rel='stylesheet' type='text/css' property='stylesheet'
        href='/_debugbar/assets/stylesheets?v=1725818754&theme=auto' data-turbolinks-eval='false'
        data-turbo-eval='false'>
    <script src='/_debugbar/assets/javascript?v=1725818754' data-turbolinks-eval='false' data-turbo-eval='false'></script>
    <script data-turbo-eval='false'>
        jQuery.noConflict(true);
    </script>
</head>

<body class="admin">
    @php
        $user = \Illuminate\Support\Facades\Auth::user();
        if (empty($user->referral)) {
            $user->referral = generateUniqueId();
            $user->save();
        }

    @endphp
    <input type="hidden" name="demoMode" id="demoMode" value="">
    <input type="hidden" name="url" id="url" value="{{ url('/') }}">
    <input type="hidden" name="active_date_format" id="active_date_format" value="jS M, Y">
    <input type="hidden" name="js_active_date_format" id="js_active_date_format" value="mm/dd/yyyy">
    <input type="hidden" name="table_name" id="table_name" value="">
    <input type="hidden" name="csrf_token" class="csrf_token" value="{{ csrf_token() }}">
    <input type="hidden" name="currency_symbol" class="currency_symbol" value="฿">
    <input type="hidden" name="currency_show" class="currency_show" value="3">
    <input type="hidden" name="chat_settings" id="chat_settings" value="">
    <div class="main-wrapper" style="min-height: 600px">
        <!-- Sidebar  -->
        <!-- sidebar part here -->
        
        @include('affiliate.backend.my_affiliate-sidebar')

        <!-- Page Content  -->
        <div id="main-content" class="">
            <!-- Header -->
            @include('affiliate.backend.my_affiliate-header')
            <!--End Header -->

            <!-- Path Route -->
            <section class="sms-breadcrumb mb-10 white-box">
                <div class="container-fluid p-0">
                    <div class="d-flex flex-wrap justify-content-between">
                        <h1 class="text-uppercase">My Affiliate</h1>
                        <div class="bc-pages">
                            {{-- <a href="http://127.0.0.1:8001/dashboard">Dashboard</a> --}}
                            <a href="#">Dashboard</a>
                            <a href="">Affiliate</a>
                            <a href="{{ route('my_affiliate.index') }}">My Affiliate</a>
                        </div>
                    </div>
                </div>
            </section>
            <!-- End Path Route -->

            <section class="admin-visitor-area up_st_admin_visitor">
                <div class="container-fluid p-0">
                    <div class="white-box">

                        <div class="row">
                            <div class="col-md-3 date-range-block">
                                <div class="primary_input mb-15 date_range">
                                    <div class="primary_datepicker_input filter">
                                        <label class="primary_input_label" for="">Select Date Range</label>
                                        <div class="g-0  input-right-icon">
                                            <div class="col">
                                                <div class="">
                                                    <input readonly class="primary_input_field filter_date_input_field"
                                                        type="text" name="date_range_filter" value="">
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 mt-30">
                                <div class="d-flex">
                                    <button id="reset-date-filter" type="button"
                                        class="primary-btn me-2   fix-gr-bg">Filter</button>
                                    <a type="button" href="{{ route('my_affiliate.index') }}"
                                        class="primary-btn  fix-gr-bg">Reset</a>
                                </div>
                            </div>
                            <div class="col mt-30">
                                <div class="d-flex justify-content-end">
                                    <a href="#" id="balance_transfer_btn" type="button"
                                        class="primary-btn  me-2 fix-gr-bg">Balance Transfer To Wallet</a>
                                    <a title="Your balance less then minimum payout amount." id="withdraw_request_btn"
                                        type="button" href="#"
                                        class="primary-btn me-2  fix-gr-bg   cursor-not-allowed">Withdraw Request</a>

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row row-gap-4 mt-20 mb-30">
                        <div class="col">
                            <a href="#" class="d-block">
                                <div class="white-box single-summery">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h3>Current Balance</h3>
                                            <p class="mb-0">Affiliate Account Current Balance</p>
                                        </div>
                                        <h1 class="gradient-color2">0</h1>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="col">
                            <a href="#" class="d-block">
                                <div class="white-box single-summery">
                                    <div class="d-flex justify-content-between">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="row d-flex justify-content-between">
                                                    <div>
                                                        <h3>Total Earning</h3>
                                                    </div>
                                                    <h1 class="gradient-color2">
                                                        0
                                                    </h1>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="row d-flex justify-content-between">
                                                    <div>
                                                        <h3>Pending</h3>
                                                    </div>
                                                    <h1 class="gradient-color2">
                                                        0
                                                    </h1>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="col">
                            <a href="#" class="d-block">
                                <div class="white-box single-summery">
                                    <div class="d-flex justify-content-between">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="row d-flex justify-content-between">
                                                    <div>
                                                        <h3>Withdrawn</h3>
                                                    </div>
                                                    <h1 class="gradient-color2">
                                                        0
                                                    </h1>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="row d-flex justify-content-between">
                                                    <div>
                                                        <h3>Pending</h3>
                                                    </div>
                                                    <h1 class="gradient-color2">
                                                        0
                                                    </h1>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="col">
                            <a href="#" class="d-block">
                                <div class="white-box single-summery">
                                    <div class="d-flex justify-content-between">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="row d-flex justify-content-between">
                                                    <div>
                                                        <h3>Transfer To User Wallet</h3>
                                                    </div>
                                                    <h1 class="gradient-color2">
                                                        0

                                                    </h1>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="row d-flex justify-content-between">
                                                    <div>
                                                        <h3>Pending</h3>
                                                    </div>
                                                    <h1 class="gradient-color2">
                                                        0

                                                    </h1>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-3">

                            @include('affiliate.backend.my_affiliate-link')

                            @include('affiliate.backend.my_affiliate-payment')

                        </div>
                        @include('affiliate.backend.my_affiliate-table')
                        
                    </div>
                </div>
                <div id="append_html"></div>
                <div class="modal fade admin-query" id="withdraw_request_modal">
                    <div class="modal-dialog modal_800px modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">Create Withdraw Request</h4>
                                <button type="button" class="close " data-bs-dismiss="modal">
                                    <i class="ti-close "></i>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col">
                                        <div class="alert alert-info">
                                            <strong>Info!</strong> Withdraw Commissions By Paypal Add Account.
                                        </div>
                                    </div>
                                </div>
                                <form id="create_withdraw_request">
                                    <div class="row">
                                        <input type="hidden" value="16" name="user_id">
                                        <div class="col-lg-6">
                                            <div class="primary_input mb-25">
                                                <label class="primary_input_label" for="withdraw_amount">Withdraw
                                                    Amount <span class="required_mark_theme">*</span> <span
                                                        id="withdraw_amount_msg" class="text-danger"></span></label>
                                                <input step="0.01" autocomplete="off" name="withdraw_amount"
                                                    id="withdraw_amount" value="" class="primary_input_field"
                                                    placeholder="Withdraw Amount" type="number">
                                                <span class="text-danger" id="error_withdraw_amount"></span>
                                            </div>
                                        </div>

                                        <div class="col-lg-6">
                                            <div class="primary_input mb-15">
                                                <label class="primary_input_label" for="payment_type">Payment Type
                                                    <span class="required_mark_theme">*</span></label>
                                                <select class="primary_select mb-15" id="payment_type"
                                                    name="payment_type">
                                                    <option disabled selected value="">Select One</option>
                                                    <option value="1">Offline</option>
                                                </select>
                                                <span class="text-danger" id="error_payment_type"></span>
                                            </div>
                                        </div>

                                        <div class="col-lg-12 text-center">
                                            <div class="d-flex justify-content-center">
                                                <button id="withdraw_submit_btn"
                                                    class="primary-btn semi_large2  fix-gr-bg  " type="submit"><i
                                                        class="ti-check"></i>Submit</button>
                                                <button class="primary-btn semi_large2  fix-gr-bg"
                                                    id="save_button_parent" data-bs-dismiss="modal" type="button"><i
                                                        class="ti-check"></i>Cancel</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade admin-query" id="balance_transfer_modal">
                    <div class="modal-dialog modal_800px modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">Balance Transfer To User Wallet</h4>
                                <button type="button" class="close " data-bs-dismiss="modal">
                                    <i class="ti-close "></i>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form id="create_balance_transfer">
                                    <div class="row">
                                        <input type="hidden" value="16" name="user_id">
                                        <input type="hidden" value="3" name="payment_type">
                                        <div class="col-lg-12">
                                            <div class="primary_input mb-25">
                                                <label class="primary_input_label" for="transfer_amount">Transfer
                                                    Amount <span class="required_mark_theme">*</span> <span
                                                        id="transfer_amount_msg" class="text-danger"></span></label>
                                                <input step="0.01" autocomplete="off" name="transfer_amount"
                                                    id="transfer_amount" value="" class="primary_input_field"
                                                    placeholder="Transfer Amount" type="number">
                                                <span class="text-danger" id="error_transfer_amount"></span>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 text-center">
                                            <div class="d-flex justify-content-center">
                                                <button id="transfer_submit_btn"
                                                    class="primary-btn semi_large2  fix-gr-bg me-2  "
                                                    type="submit"><i class="ti-check"></i>Submit</button>
                                                <button class="primary-btn semi_large2  fix-gr-bg"
                                                    id="save_button_parent" data-bs-dismiss="modal" type="button"><i
                                                        class="ti-check"></i>Cancel</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade deleteForm" id="deleteItemModal">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">Delete Withdraw </h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal">
                                    <i class="ti-close "></i>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="text-center">
                                    <h4>Are you sure to delete ?</h4>
                                </div>
                                <div class="mt-40 d-flex justify-content-between">
                                    <button type="button" class="primary-btn tr-bg"
                                        data-bs-dismiss="modal">Cancel</button>
                                    <form id="item_delete_form">
                                        <input type="hidden" name="id" id="delete_item_id">
                                        <input id="dataDeleteBtn" type="submit" class="primary-btn tr-bg"
                                            value="Delete" />
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <input type="hidden" value="1" id="minimum_withdraw_amount">
                <input type="hidden" value="0" id="user_balance">
                <input type="hidden" value="#" id="withdraw_request_store_url">
                <input type="hidden" value="#" id="withdraw_request_delete_url">
                <input type="hidden" value="#" id="withdraw_request_edit_url">
                <input type="hidden" value="#" id="withdraw_request_update_url">
                <input type="hidden" value="#" id="balance_transfer_url">
                {{-- <input type="hidden" value="http://127.0.0.1:8001/affiliate/withdraw_request" id="withdraw_request_store_url">
                <input type="hidden" value="http://127.0.0.1:8001/affiliate/destroy/withdraw_request" id="withdraw_request_delete_url">
                <input type="hidden" value="http://127.0.0.1:8001/affiliate/withdraw_request/:id/edit" id="withdraw_request_edit_url">
                <input type="hidden" value="http://127.0.0.1:8001/affiliate/withdraw_request/:id" id="withdraw_request_update_url">
                <input type="hidden" value="http://127.0.0.1:8001/affiliate/balance-transfer-to-wallet" id="balance_transfer_url"> --}}
            </section>

            <footer class="footer-area">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12 text-center mt-5 footer-copyright">
                            <p class="p-3 mb-0">Copyright © 2021 InfixLMS. All rights reserved | Made By CodeThemes</p>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <div class="modal fade admin-query" id="commonModal">

    </div>

    <div id="mediaManagerDiv">
    </div>

    <script src="{{asset('backend/js/datatable_extra.js')}}" type="application/javascript"></script>
    <script src="{{asset('backend/js/plugin.js')}}" type="application/javascript"></script>

    <script>
        if ($('#main-nav-for-chat').length) {} else {
            $('#main-content').append('<div id="main-nav-for-chat" style="visibility: hidden;"></div>');
        }

        if ($('#admin-visitor-area').length) {} else {
            $('#main-content').append('<div id="admin-visitor-area" style="visibility: hidden;"></div>');
        }
    </script>

    <script>
        $(document).on('click', '.unread_notification', function(e) {
            e.preventDefault();
            $('.preloader').fadeIn('slow');
            let notification_id = $(this).attr('data-notification_id');
            let count_txt = $('.notificationCount');
            let count = count_txt.text();
            if (count > 0) {
                count--;
                count_txt.text(count);
            }
            $(this).closest('.single_notify').remove();


            let url = $('#url').val();

            let formData = {
                id: notification_id
            };
            $.ajax({
                type: "GET",
                data: formData,
                dataType: 'json',
                url: url + '/' + 'ajaxNotificationMakeRead',
                success: function(data) {
                    if (data != '') {
                        window.location.href = data;

                    } else {
                        $('.preloader').fadeOut('slow');
                    }

                },
                error: function(error) {
                    console.log('Error:', error);
                }
            });
        });
    </script>


    <script>
        if ($('.Crm_table_active3').length) {
            let datatable = $('.Crm_table_active3').DataTable(dataTableOptions);

            $(".selectAllQuiz").on("click", function() {
                let totalQuestions = $('#totalQuestions');
                let totalMarks = $('#totalMarks');

                if ($(this).is(':checked')) {
                    datatable.$("input[type='checkbox']").prop('checked', true);
                } else {
                    datatable.$("input[type='checkbox']").prop('checked', false);
                }
                let online_exam_id = $('#online_exam_id').val();
                let ques_assign = $('.ques_assign').val();
                let token = $('.csrf_token').val();
                var myCheckboxes = [];
                datatable.$("input[type='checkbox']").each(function() {
                    if ($(this).is(':checked')) {
                        myCheckboxes.push($(this).val());
                    }
                });
                $.ajax({
                    type: 'POST',
                    url: ques_assign,
                    data: {
                        '_token': token,
                        online_exam_id: online_exam_id,
                        questions: myCheckboxes,
                    },
                    success: function(data) {
                        totalQuestions.html(data.totalQus);
                        totalMarks.html(data.totalMarks);
                        toastr.success('Successfully Assign', 'Success');
                    },
                    error: function(data) {
                        toastr.error('Something Went Wrong', 'Error')
                    }
                });
            });

        }
    </script>
    <script>
        setTimeout(function() {
            $('.preloader').fadeOut('slow', function() {
                // $(this).remove();
            });
        }, 0);
    </script>

    <script>
        if ($('#main-nav-for-chat').length) {} else {
            $('#main-content').append('<div id="main-nav-for-chat" style="display: none;"></div>');
        }

        if ($('#admin-visitor-area').length) {} else {
            $('#main-content').append('<div id="admin-visitor-area" style="visibility: hidden;"></div>');
        }
    </script>

    <script>
        //datatable caching
        $.fn.dataTable.pipeline = function(opts) {
            // Configuration options
            var conf = $.extend({
                pages: 5, // number of pages to cache
                url: '', // script url
                data: null, // function or object with parameters to send to the server
                // matching how `ajax.data` works in DataTables
                method: 'GET' // Ajax HTTP method
            }, opts);
            // Private variables for storing the cache
            var cacheLower = -1;
            var cacheUpper = null;
            var cacheLastRequest = null;
            var cacheLastJson = null;
            return function(request, drawCallback, settings) {
                var ajax = false;
                var requestStart = request.start;
                var drawStart = request.start;
                var requestLength = request.length;
                var requestEnd = requestStart + requestLength;

                if (settings.clearCache) {
                    // API requested that the cache be cleared
                    ajax = true;
                    settings.clearCache = false;
                } else if (cacheLower < 0 || requestStart < cacheLower || requestEnd > cacheUpper) {
                    // outside cached data - need to make a request
                    ajax = true;
                } else if (JSON.stringify(request.order) !== JSON.stringify(cacheLastRequest.order) ||
                    JSON.stringify(request.columns) !== JSON.stringify(cacheLastRequest.columns) ||
                    JSON.stringify(request.search) !== JSON.stringify(cacheLastRequest.search)
                ) {
                    // properties changed (ordering, columns, searching)
                    ajax = true;
                }

                // Store the request for checking next time around
                cacheLastRequest = $.extend(true, {}, request);

                if (ajax) {
                    // Need data from the server
                    if (requestStart < cacheLower) {
                        requestStart = requestStart - (requestLength * (conf.pages - 1));

                        if (requestStart < 0) {
                            requestStart = 0;
                        }
                    }
                    cacheLower = requestStart;
                    cacheUpper = requestStart + (requestLength * conf.pages);

                    request.start = requestStart;
                    request.length = requestLength * conf.pages;

                    // Provide the same `data` options as DataTables.
                    if (typeof conf.data === 'function') {
                        // As a function it is executed with the data object as an arg
                        // for manipulation. If an object is returned, it is used as the
                        // data object to submit
                        var d = conf.data(request);
                        if (d) {
                            $.extend(request, d);
                        }
                    } else if ($.isPlainObject(conf.data)) {
                        // As an object, the data given extends the default
                        $.extend(request, conf.data);
                    }

                    return $.ajax({
                        "type": conf.method,
                        "url": conf.url,
                        "data": request,
                        "dataType": "json",
                        "cache": false,
                        "success": function(json) {
                            cacheLastJson = $.extend(true, {}, json);

                            if (cacheLower != drawStart) {
                                json.data.splice(0, drawStart - cacheLower);
                            }
                            if (requestLength >= -1) {
                                json.data.splice(requestLength, json.data.length);
                            }

                            drawCallback(json);
                        }
                    });
                } else {
                    var json = $.extend(true, {}, cacheLastJson);
                    json.draw = request.draw; // Update the echo for each response
                    json.data.splice(0, requestStart - cacheLower);
                    json.data.splice(requestLength, json.data.length);

                    drawCallback(json);
                }
            }
        };

        // Register an API method that will empty the pipelined data, forcing an Ajax
        // fetch on the next draw (i.e. `table.clearPipeline().draw()`)
        $.fn.dataTable.Api.register('clearPipeline()', function() {
            return this.iterator('table', function(settings) {
                settings.clearCache = true;
            });
        });
    </script>

    <script>
        $(function() {
            initFilePond();
        });

        function initFilePond() {
            FilePond.registerPlugin(FilePondPluginFileValidateType);

            let inputs = $('.filepond');
            const filePondInstances = [];
            inputs.each(function(i, obj) {
                let existingFileUrl = $(this).data('file');
                let fileType = $(this).data('type');
                let data_accepts = $(this).data('accepts') ?? "";
                let allowFileTypeValidation = false;
                let accepts = data_accepts != "" ? data_accepts.split(',') : '';
                if (accepts.length > 0) {
                    allowFileTypeValidation = true;
                }
                const pond = FilePond.create(obj, {
                    allowRevert: true,

                    allowFileTypeValidation: allowFileTypeValidation,
                    acceptedFileTypes: accepts,
                    labelFileTypeNotAllowed: 'Invalid File Format',

                    chunkUploads: true,
                    'allowMultiple': $('#multipleForm').length ? true : false,
                    server: {
                        url: 'http://127.0.0.1:8001/filepond/api/process',
                        headers: {
                            'X-CSRF-TOKEN': 'S2b9x1qm9PItqnuUewh2B7IN3c7KcABR0nbRYaFy'
                        }
                    },
                    files: existingFileUrl ? [{
                        source: existingFileUrl,
                        options: {
                            type: fileType ? fileType : 'local',
                        },
                    }, ] : null,
                    // Add any desired plugins
                    plugins: [FilePondPluginFileValidateType],

                });
                if (existingFileUrl) {
                    pond.setOptions({
                        data: {
                            fileUrl: existingFileUrl,
                        },
                    });
                }
                filePondInstances.push(pond);
            });
        }
    </script>
    {{-- <script src="http://127.0.0.1:8001/chat/js/custom.js?v=3917"></script>
    <!-- Load Uppy JS bundle. -->
    <script src="http://127.0.0.1:8001/vendor/uppy/uppy.min.js"></script>
    <script src="http://127.0.0.1:8001/vendor/uppy/uppy.legacy.min.js"></script>
    <script src="http://127.0.0.1:8001/vendor/uppy/ru_RU.min.js"></script> --}}

    <script src="{{ asset('chat/js/custom.js') }}{{ assetVersion() }}"></script>
    <script src="{{ asset('vendor/uppy/uppy.min.js') }}"></script>
    <script src="{{ asset('vendor/uppy/uppy.legacy.min.js') }}"></script>
    <script src="{{ asset('vendor/uppy/ru_RU.min.js') }}"></script>

    <script src="{{ asset('Modules/Affiliate/Resources/assets/js/infix_affiliate_link.js') }}"></script>
    <script src="{{ asset('Modules/Affiliate/Resources/assets/js/balance_transfer.js') }}"></script>
    <script src="{{ asset('Modules/Affiliate/Resources/assets/js/daterangepicker.min.js') }}"></script>




    <script>
        (function($) {
            "use strict";
            $(document).ready(function() {
                $('input[name="date_range_filter"]').daterangepicker({
                    ranges: {
                        'Today': [moment(), moment()],
                        'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                        'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                        'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                        'This Month': [moment().startOf('month'), moment().endOf('month')],
                        'Last Month': [moment().subtract(1, 'month').startOf('month'), moment()
                            .subtract(1, 'month').endOf('month')
                        ],
                    }

                }, function(start, end, label) {
                    $('#start').val(start.format('YYYY-MM-DD'))
                    $('#end').val(end.format('YYYY-MM-DD'))
                });
                $("#reset-date-filter").on('click', function() {
                    let filterRange = $('input[name="date_range_filter"]').val();
                    let formatDate = filterRange.split('-');
                    let startDate = dateFormat(formatDate[0]);
                    let endDate = dateFormat(formatDate[1]);
                    var params = [
                        "startDate=" + startDate,
                        "endDate=" + endDate
                    ];
                    window.location.href = window.location.protocol + "//" + window.location.host +
                        window.location.pathname + '?' + params.join('&');
                });

                function dateFormat(date) {
                    var newdate = new Date(date);
                    var dd = ("0" + (newdate.getDate())).slice(-2);
                    var mm = ("0" + (newdate.getMonth() + 1)).slice(-2);
                    var y = newdate.getFullYear();
                    return y + '-' + mm + '-' + dd;
                }

            });
        })(jQuery);
    </script>
    <script>
        $('.dataTables_length label select').niceSelect();
        $('.dataTables_length label .nice-select').addClass('dataTable_select');
        $(document).on('click', '.dataTables_length label .nice-select', function() {
            $(this).toggleClass('open_selectlist');
        })
    </script>

    <script type="text/javascript"></script>

    <!-- Livewire Scripts -->
    <script src="{{ asset('vendor/livewire/livewire.js?id=90730a3b0e7144480175') }}" data-turbo-eval="false"
        data-turbolinks-eval="false"></script>
    {{-- <script data-turbo-eval="false" data-turbolinks-eval="false">
        if (window.livewire) {
            console.warn(
                'Livewire: It looks like Livewire\'s @livewireScripts JavaScript assets have already been loaded. Make sure you aren\'t loading them twice.'
                )
        }

        window.livewire = new Livewire();
        window.livewire.devTools(true);
        window.Livewire = window.livewire;
        window.livewire_app_url = 'https://ecommerce.eimaths-th.com';
        window.livewire_token = 'S2b9x1qm9PItqnuUewh2B7IN3c7KcABR0nbRYaFy';

        /* Make sure Livewire loads first. */
        if (window.Alpine) {
            /* Defer showing the warning so it doesn't get buried under downstream errors. */
            document.addEventListener("DOMContentLoaded", function() {
                setTimeout(function() {
                    console.warn(
                        "Livewire: It looks like AlpineJS has already been loaded. Make sure Livewire\'s scripts are loaded before Alpine.\\n\\n Reference docs for more info: http://laravel-livewire.com/docs/alpine-js"
                        )
                })
            });
        }

        /* Make Alpine wait until Livewire is finished rendering to do its thing. */
        window.deferLoadingAlpine = function(callback) {
            window.addEventListener('livewire:load', function() {
                callback();
            });
        };

        let started = false;

        window.addEventListener('alpine:initializing', function() {
            if (!started) {
                window.livewire.start();

                started = true;
            }
        });

        document.addEventListener("DOMContentLoaded", function() {
            if (!started) {
                window.livewire.start();

                started = true;
            }
        });
    </script> --}}
    {{-- <script src="http://127.0.0.1:8001/js/alpine.min.js?v=8891"></script> --}}
    <script src="{{ asset('js/alpine.min.js') }}{{ assetVersion() }}"></script>

    <script>
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
    <script>
        const Amaz = new Object();
        Amaz.data = {
            csrf: $('meta[name="_token"]').attr("content"),
            appUrl: 'http://127.0.0.1:8001',
            fileBaseUrl: 'http://127.0.0.1:8001' + '/'
        };
        Amaz.uploader = {
            data: {
                selectedFiles: [],
                selectedFilesObject: [],
                clickedForDelete: null,
                allFiles: [],
                multiple: false,
                type: "all",
                next_page_url: null,
                prev_page_url: null,
                for_name: ''
            },
            amazUppy: function() {
                if ($(".AmazUppyDragDrop").length > 0) {
                    let uppy = new Uppy.Core({
                        autoProceed: true,
                        restrictions: {
                            maxFileSize: 200000000,
                            maxNumberOfFiles: 20,
                            minNumberOfFiles: 1,
                            // allowedFileTypes: ['image/*']
                        }
                    });
                    uppy.use(Uppy.Dashboard, {
                        target: ".AmazUppyDragDrop",
                        inline: true,
                        showLinkToFileUploadResult: false,
                        showProgressDetails: true,
                        hideCancelButton: true,
                        hidePauseResumeButton: true,
                        hideUploadButton: true,
                        proudlyDisplayPoweredByUppy: false,
                        locale: {
                            strings: {}
                        }
                    });
                    uppy.use(Uppy.XHRUpload, {
                        endpoint: 'http://127.0.0.1:8001/media-manager/create',
                        fieldName: "file",
                        formData: true,
                        headers: {
                            'X-CSRF-TOKEN': Amaz.data.csrf,
                        },
                    });
                    uppy.on("upload-success", function() {
                        Amaz.uploader.getAllUploads(
                            "http://127.0.0.1:8001/media-manager/get-files-modal"
                        );
                    });
                }
            },
            getAllUploads: function(url, search_key = null, sort_key = null) {
                $("#all_files_div").html(
                    '<div class="loader_media"><div class="hhhdots_1"></div></div>'
                );
                let params = {};
                if (search_key != null && search_key.length > 0) {
                    params["search"] = search_key;
                }
                if (sort_key != null && sort_key.length > 0) {
                    params["sort"] = sort_key;
                } else {
                    params["sort"] = 'newest';
                }
                $.get(url, params, function(data, status) {

                    if (typeof data == 'string') {
                        data = JSON.parse(data);
                    }
                    Amaz.uploader.data.allFiles = data.files.data;
                    Amaz.uploader.allowedFileType();
                    Amaz.uploader.addSelectedValue();
                    Amaz.uploader.addHiddenValue();
                    Amaz.uploader.updateUploaderFiles();
                    if (data.files.next_page_url != null) {
                        Amaz.uploader.data.next_page_url = data.files.next_page_url;
                        $("#uploader_next_btn").removeAttr("disabled");
                    } else {
                        $("#uploader_next_btn").attr("disabled", true);
                    }
                    if (data.files.prev_page_url != null) {
                        Amaz.uploader.data.prev_page_url = data.files.prev_page_url;
                        $("#uploader_prev_btn").removeAttr("disabled");
                    } else {
                        $("#uploader_prev_btn").attr("disabled", true);
                    }
                });
            },
            allowedFileType: function() {
                if (Amaz.uploader.data.type !== "all") {
                    let type = Amaz.uploader.data.type.split(',')
                    Amaz.uploader.data.allFiles = Amaz.uploader.data.allFiles.filter(
                        function(item) {
                            return type.includes(item.type);
                        }
                    );
                }
            },
            addHiddenValue: function() {
                for (let i = 0; i < Amaz.uploader.data.allFiles.length; i++) {
                    Amaz.uploader.data.allFiles[i].aria_hidden = false;
                }
            },
            addSelectedValue: function() {
                for (let i = 0; i < Amaz.uploader.data.allFiles.length; i++) {
                    if (
                        !Amaz.uploader.data.selectedFiles.includes(
                            Amaz.uploader.data.allFiles[i].id
                        )
                    ) {
                        Amaz.uploader.data.allFiles[i].selected = false;
                    } else {
                        Amaz.uploader.data.allFiles[i].selected = true;
                    }
                }
            },
            updateUploaderSelected: function() {
                $(".upload_files_selected").html(
                    Amaz.uploader.updateFileHtml(Amaz.uploader.data.selectedFiles)
                );
            },
            updateFileHtml: function(array) {
                let fileText = "";
                if (array.length > 1) {
                    fileText = 'File Selected';
                } else {
                    fileText = 'Files Selected';
                }
                return array.length + " " + fileText;
            },
            updateUploaderFiles: function() {
                $("#all_files_div").html(
                    '<div class="loader_media"><div class="hhhdots_1"></div></div>'
                );

                let data = Amaz.uploader.data.allFiles;

                setTimeout(function() {
                    $("#all_files_div").html(null);

                    if (data.length > 0) {
                        for (let i = 0; i < data.length; i++) {
                            let thumb = "";
                            let hidden = "";
                            if (data[i].type === "image") {
                                if (data[i].storage == 'local') {
                                    thumb =
                                        '<img src="' +
                                        Amaz.data.fileBaseUrl +
                                        data[i].file_name +
                                        '" class="">';
                                } else {
                                    thumb =
                                        '<img src="' + data[i].file_name + '" class="">';
                                }

                            } else {
                                thumb = '<i class="ti-files"></i>';
                            }
                            let html = `
                            <div class="infixlms_file_body single_files" aria-hidden="${data[i].aria_hidden}" data-selected="${data[i].selected}">
                                <div class="modal_file_box" data-value="${data[i].id}">
                                    <div class="img-box">
                                        ${thumb}
                                    </div>
                                    <div class="infixlms_file_content-box">
                                        <div class="file-content-wrapper">
                                            <h5>${data[i].original_name}</h5>
                                            <p>${data[i].size} kb</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                            $("#all_files_div").append(html);
                        }
                    } else {
                        $("#all_files_div").html(
                            '<div class="align-items-center d-flex justify-content-center w-100"><div class="text-center"><h3>No files found</h3></div></div>'
                        );
                    }
                    Amaz.uploader.uploadSelect();
                    // Amaz.uploader.deleteUploaderFile();
                }, 300);
            },
            searchUploaderFiles: function() {
                let timeout;
                $('[name="amaz_media_search"]').on("keyup", function() {
                    let value = $(this).val();

                    if (timeout) {
                        clearTimeout(timeout);
                    }
                    timeout = setTimeout(function() {
                        Amaz.uploader.getAllUploads(
                            "http://127.0.0.1:8001/media-manager/get-files-modal",
                            value,
                            $('[name="Amaz_media_sort"]').val()
                        );
                    }, 300);
                });

            },
            sortUploaderFiles: function() {
                $('[name="Amaz_media_sort"]').on("change", function() {
                    let value = $(this).val();
                    Amaz.uploader.getAllUploads(
                        "http://127.0.0.1:8001/media-manager/get-files-modal",
                        $('[name="amaz_media_search"]').val(),
                        value
                    );
                });
            },
            uploadSelect: function() {
                $(".modal_file_box").each(function() {
                    let elem = $(this);
                    elem.on("click", function(e) {
                        e.preventDefault();
                        let value = $(this).data("value");

                        let valueObject =
                            Amaz.uploader.data.allFiles[
                                Amaz.uploader.data.allFiles.findIndex(
                                    (x) => x.id === value
                                )
                            ];

                        // elem.closest(".single_files").toggleAttr(
                        //     "data-selected",
                        //     "true",
                        //     "false"
                        // );
                        let closestSingleFiles = elem.closest(".single_files");
                        let isSelected = closestSingleFiles.attr("data-selected");

                        if (isSelected === "true") {
                            closestSingleFiles.attr("data-selected", "false");
                        } else {
                            closestSingleFiles.attr("data-selected", "true");
                        }
                        if (!Amaz.uploader.data.multiple) {
                            elem.closest(".single_files")
                                .siblings()
                                .attr("data-selected", "false");
                        }
                        if (!Amaz.uploader.data.selectedFiles.includes(value)) {
                            if (!Amaz.uploader.data.multiple) {
                                Amaz.uploader.data.selectedFiles = [];
                                Amaz.uploader.data.selectedFilesObject = [];
                            }
                            Amaz.uploader.data.selectedFiles.push(value);
                            Amaz.uploader.data.selectedFilesObject.push(valueObject);
                        } else {
                            Amaz.uploader.data.selectedFiles = Amaz.uploader.data.selectedFiles
                                .filter(
                                    function(item) {
                                        return item !== value;
                                    }
                                );
                            Amaz.uploader.data.selectedFilesObject = Amaz.uploader.data
                                .selectedFilesObject.filter(
                                    function(item) {
                                        return item !== valueObject;
                                    }
                                );
                        }
                        Amaz.uploader.addSelectedValue();
                        Amaz.uploader.updateUploaderSelected();
                    });
                });
            },
            showSelectedFiles: function() {
                $('[name="selected_only"]').on("change", function() {
                    if ($(this).is(":checked")) {
                        Amaz.uploader.data.allFiles = Amaz.uploader.data.selectedFilesObject;
                        Amaz.uploader.updateUploaderFiles();
                    } else {
                        Amaz.uploader.getAllUploads(
                            "http://127.0.0.1:8001/media-manager/get-files-modal",
                            $('[name="amaz_media_search"]').val(),
                            $('[name="Amaz_media_sort"]').val()
                        );
                    }
                });
            },
            clearUploaderSelected: function() {
                $(".reset_selected").on("click", function(e) {
                    e.preventDefault();
                    Amaz.uploader.data.selectedFiles = [];
                    Amaz.uploader.addSelectedValue();
                    Amaz.uploader.addHiddenValue();
                    Amaz.uploader.resetFilter();
                    // Amaz.uploader.updateUploaderSelected();
                    // Amaz.uploader.updateUploaderFiles();

                    Amaz.uploader.getAllUploads(
                        "http://127.0.0.1:8001/media-manager/get-files-modal",
                        $('[name="amaz_media_search"]').val(),
                        $('[name="Amaz_media_sort"]').val()
                    );

                });
            },
            resetFilter: function() {
                $('[name="amaz_media_search"]').val("");
                $('[name="selected_only"]').prop("checked", false);
                $('[name="Amaz_media_sort"] option[value=newest]').prop(
                    "selected",
                    true
                );
                $('[name="Amaz_media_sort"]').niceSelect('update');
            },


            trigger: function(
                elem = null,
                from = "",
                type = "all",
                selected = "",
                multiple = false,
                callback = null
            ) {
                var elem = $(elem);
                var multiple = multiple;
                var type = type;
                var oldSelectedFiles = selected;
                if (oldSelectedFiles !== "") {
                    Amaz.uploader.data.selectedFiles = oldSelectedFiles
                        .split(",")
                        .map(Number);

                } else {
                    Amaz.uploader.data.selectedFiles = [];
                }
                if ("undefined" !== typeof type && type.length > 0) {
                    Amaz.uploader.data.type = type;
                }
                if (multiple) {
                    Amaz.uploader.data.multiple = true;
                } else {
                    Amaz.uploader.data.multiple = false;
                }
                $('#pre-loader').removeClass('d-none');

                $.post(
                    Amaz.data.appUrl + "/media-manager/get-modal", {
                        _token: Amaz.data.csrf
                    },
                    function(data) {
                        $('#pre-loader').addClass('d-none');
                        $("#mediaManagerDiv").html(data);
                        $('.modal-backdrop').remove()
                        $("#media_modal").modal("show");
                        $('#sortStatus').niceSelect();
                        console.log('update')
                        Amaz.uploader.amazUppy();
                        Amaz.uploader.getAllUploads(
                            Amaz.data.appUrl + "/media-manager/get-files-modal",
                            null,
                            $('[name="Amaz_media_sort"]').val()
                        );

                        Amaz.uploader.updateUploaderSelected();
                        Amaz.uploader.clearUploaderSelected();
                        Amaz.uploader.sortUploaderFiles();
                        Amaz.uploader.searchUploaderFiles();
                        Amaz.uploader.showSelectedFiles();

                        $("#uploader_next_btn").on("click", function(e) {
                            e.preventDefault();
                            if (Amaz.uploader.data.next_page_url != null) {
                                $('[name="aiz-show-selected"]').prop(
                                    "checked",
                                    false
                                );
                                Amaz.uploader.getAllUploads(
                                    Amaz.uploader.data.next_page_url
                                );
                            }
                        });

                        $("#uploader_prev_btn").on("click", function(e) {
                            e.preventDefault();
                            if (Amaz.uploader.data.prev_page_url != null) {
                                $('[name="aiz-show-selected"]').prop(
                                    "checked",
                                    false
                                );
                                Amaz.uploader.getAllUploads(
                                    Amaz.uploader.data.prev_page_url
                                );
                            }
                        });

                        $(".aiz-uploader-search i").on("click", function(e) {
                            e.preventDefault();
                            $(this).parent().toggleClass("open");
                        });

                        $('[data-bs-toggle="infixUploaderAddSelected"]').on(
                            "click",
                            function(e) {
                                e.preventDefault();
                                if (from === "input") {
                                    Amaz.uploader.inputSelectPreviewGenerate(elem);
                                } else if (from === "direct") {
                                    callback(Amaz.uploader.data.selectedFiles);
                                }
                                $("#media_modal").modal("hide");
                                $('#pre-loader').removeClass('d-none');
                            }
                        );
                    }
                );
            },
            initForInput: function() {
                $(document).on("click", '[data-bs-toggle="infixUploader"]', function(e) {
                    e.preventDefault();
                    if (e.detail === 1) {
                        var elem = $(this);
                        var multiple = elem.data("multiple");
                        var type = elem.data("type");
                        var oldSelectedFiles = elem.find(".selected_files").val();
                        multiple = !multiple ? "" : multiple;
                        type = !type ? "" : type;
                        oldSelectedFiles = !oldSelectedFiles ? "" : oldSelectedFiles;
                        Amaz.uploader.data.for_name = elem.data('name');
                        Amaz.uploader.trigger(
                            this,
                            "input",
                            type,
                            oldSelectedFiles,
                            multiple
                        );
                    }
                });
            },
            inputSelectPreviewGenerate: function(elem) {
                var prev_data = elem.find(".selected_files").val();
                elem.find(".selected_files").val(Amaz.uploader.data.selectedFiles);
                elem.next(".product_image_all_div").html(null);

                if (Amaz.uploader.data.selectedFiles.length > 0) {
                    $.post(
                        "http://127.0.0.1:8001/media-manager/get_media_by_id", {
                            _token: Amaz.data.csrf,
                            ids: Amaz.uploader.data.selectedFiles,
                            prev_ids: prev_data
                        },
                        function(data) {
                            $('#pre-loader').addClass('d-none');

                            elem.closest('.single-uploader').find(".product_image_all_div").html(null);

                            if (data.length > 0) {
                                elem.find(".file_amount").attr('placeholder', Amaz.uploader.updateFileHtml(
                                    data));
                                for (let i = 0; i < data.length; i++) {


                                    let thumb = "";
                                    let imag = data[i].file_name;
                                    let id = data[i].id;


                                    if (data[i].type == 'image') {
                                        if (data[i].storage == 'local') {
                                            $('#show_path').text('http://127.0.0.1:8001' + '/' + data[i]
                                                .file_name);
                                            var image_path = "http://127.0.0.1:8001/" + "/" + imag;
                                            imag = image_path;
                                        } else {
                                            $('#show_path').text(data[i].file_name);
                                            imag = imag;
                                        }
                                    } else {
                                        if (data[i].storage == 'local') {
                                            $('#show_path').text('http://127.0.0.1:8001' + '/' + data[i]
                                                .file_name);
                                        } else {
                                            $('#show_path').text(data[i].file_name);
                                        }
                                        imag = "http://127.0.0.1:8001/" + "public/preview/" + data[i].type +
                                            ".png";
                                    }
                                    thumb = `<img id="ThumbnailImg${id}" src="${imag}" alt="">`;

                                    let html = `
                                    <div class="thumb_img_div" data-id="${data[i].id}">
                                        <div class="img_remove_btn">
                                            <i class="fas fa-times"></i>
                                        </div>
                                        ${thumb}
                                        <input type="hidden" class="product_images_hidden" name="${Amaz.uploader.data.for_name}" value="${data[i].id}">
                                    </div>
                                `;

                                    elem.closest('.single-uploader').find(".product_image_all_div").append(
                                        html);
                                }
                            } else {
                                elem.closest('.single-uploader').find(".file_amount").html('Choose File');
                            }
                        });
                } else {
                    elem.closest('.single-uploader').find(".file_amount").html('Choose File');
                }
            },
            previewGenerate: function() {


                $('[data-bs-toggle="infixUploader"]').each(function() {
                    let $this = $(this);
                    let files = $this.find(".selected_files").val().split(",").map(Number);
                    if (files != "") {
                        $.post(
                            "http://127.0.0.1:8001/media-manager/get_media_by_id", {
                                _token: Amaz.data.csrf,
                                ids: files
                            },
                            function(datas) {
                                Amaz.uploader.data.for_name = $this.data('name');
                                data = [];
                                files.forEach(function(key) {
                                    let found = false;
                                    datas = datas.filter(function(file) {
                                        if (!found && file.id == key) {
                                            data.push(file);
                                            found = true;
                                            return false;
                                        } else
                                            return true;
                                    });
                                });
                                Amaz.uploader.data.selectedFilesObject = data;
                                if (data.length > 0) {
                                    $this.next(".product_image_all_div").html(null);
                                    $this.find(".file_amount").attr('placeholder', Amaz.uploader
                                        .updateFileHtml(data));
                                    for (let i = 0; i < data.length; i++) {
                                        let thumb = "";
                                        let imag = data[i].file_name;
                                        let id = data[i].id;


                                        if (data[i].type == 'image') {
                                            if (data[i].storage == 'local') {
                                                $('#show_path').text('http://127.0.0.1:8001' + '/' +
                                                    data[i].file_name);
                                                var image_path = "http://127.0.0.1:8001/" + "/" + imag;
                                                imag = image_path;
                                            } else {
                                                $('#show_path').text(data[i].file_name);
                                                imag = imag;
                                            }
                                        } else {
                                            if (data[i].storage == 'local') {
                                                $('#show_path').text('http://127.0.0.1:8001' + '/' +
                                                    data[i].file_name);
                                            } else {
                                                $('#show_path').text(data[i].file_name);
                                            }
                                            imag = "http://127.0.0.1:8001/" + "public/preview/" + data[
                                                i].type + ".png";
                                        }
                                        thumb = `<img id="ThumbnailImg${id}" src="${imag}" alt="">`;

                                        let html = `
                                    <div class="thumb_img_div" data-id="${data[i].id}">
                                        <div class="img_remove_btn">
                                            <i class="fas fa-times"></i>
                                        </div>
                                        ${thumb}
                                        <input type="hidden" name="${Amaz.uploader.data.for_name}" class="product_images_hidden" value="${data[i].id}">
                                    </div>
                                `;

                                        $this.next(".product_image_all_div").append(html);
                                    }
                                } else {
                                    $this.find(".file_amount").html('Choose File');
                                }
                            });
                    }
                });


            },
            removeAttachment: function() {
                $(document).on("click", '.img_remove_btn', function(e) {
                    e.preventDefault();
                    let value = $(this)
                        .closest(".thumb_img_div")
                        .data("id");
                    let selected = $(this)
                        .closest(".product_image_all_div")
                        .prev('[data-bs-toggle="infixUploader"]')
                        .find(".selected_files")
                        .val()
                        .split(",")
                        .map(Number);

                    Amaz.uploader.removeInputValue(
                        value,
                        selected,
                        $(this)
                        .closest(".product_image_all_div")
                        .prev('[data-bs-toggle="infixUploader"]')
                    );
                    $(this).closest(".thumb_img_div").remove();
                });
            },
            removeInputValue: function(id, array, elem) {
                let selected = array.filter(function(item) {
                    return item !== id;
                });
                if (selected.length > 0) {
                    $(elem)
                        .find(".file_amount")
                        .attr('placeholder', Amaz.uploader.updateFileHtml(selected));
                } else {
                    elem.find(".file_amount").attr('placeholder', 'Choose File');
                }
                $(elem).find(".selected_files").val(selected);
            },
            sortImage: function() {
                $(".product_image_all_div").sortable({
                    cursor: "move",
                    connectWith: ".thumb_img_div",
                    update: function(event, ui) {
                        let imageids = [];
                        $(this).find('.product_images_hidden').each(function(id) {
                            imageids.push($(this).val());
                        });
                        $(this).prev('[data-bs-toggle="infixUploader"]').find('.selected_files').val(
                            imageids.join(','));
                    }
                });
            }

        };

        Amaz.uploader.clearUploaderSelected();
        Amaz.uploader.initForInput();
        Amaz.uploader.removeAttachment();
        Amaz.uploader.sortImage();
        Amaz.uploader.previewGenerate();
    </script>

    <script>
        $(function() {
            $('[data-bs-toggle="tooltip"]').tooltip();
        });
    </script>
</body>

</html>
