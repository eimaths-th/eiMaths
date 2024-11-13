@extends(theme('layouts.dashboard_master'))
@section('title')
    {{Settings('site_title')  ? Settings('site_title')  : 'Infix LMS'}} | {{__('communication.Your referral link')}}
@endsection
@section('css')
    <link rel="stylesheet" href="{{asset('Modules/Affiliate/Resources/assets/css')}}/daterangepicker.css"></link>
    <style>
        .modal-header .close {
            min-width: var(--btn);
        }

        .cs_modal .modal-header {
            gap: 20px;
        }

        .cursor-not-allowed {
            cursor: not-allowed;
        }

        /* dashboard design update */

        .affiliate_filter_section {
            background: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: -12px 16px 40px 0px #0000000D;
        }

        .affiliate_balence_grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 35px;
        }

        @media (max-width: 1400px) {
            .affiliate_balence_grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 1200px) {
            .affiliate_balence_grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 576px) {
            .affiliate_balence_grid {
                grid-template-columns: repeat(1, 1fr);
            }
        }

        .affiliate_balence_grid .card {
            padding: 20px 35px;
            box-shadow: -12px 16px 40px 0px #0000000D;
            border-radius: 10px;
            border: 0;
        }

        .affiliate_balence_grid .card h4 {
            color: #313336;
            font-size: 32px;
            font-weight: 700;
        }

        .affiliate_balence_grid .card:hover h4 {
            color: #FB1159;
        }

        .affiliate_balence_grid .card p {
            color: #3F4654;
            font-size: 20px;
            font-weight: 500;
        }

        .primary_datepicker_input input {
            width: 100%;
            border-radius: 4px;
            border: 1px solid #e9e7f7;
            padding: 6px 10px;
        }

        .affiliate_tabs_container .affiliate_content {
            padding-bottom: 12px;
            margin-bottom: 30px;
            border-bottom: 1px solid #e9e7f7;
        }

        .affiliate_tabs_container table tr td,
        .affiliate_tabs_container table tr th {
            padding: 13px 10px !important;
        }

        .nav-tabs.affiliate_tabs {
            flex-wrap: nowrap;
            overflow: auto;
            white-space: nowrap;
            max-width: 100%;
        }

        .nav-tabs.affiliate_tabs .nav-item {
            margin-bottom: 0;
        }

        .nav-tabs.affiliate_tabs .nav-item .nav-link {
            border: 0.5px solid #D0D0D0;
            background: #E8EAEB;
            margin: 0;
            font-size: 16px;
            font-weight: 600;
            text-transform: capitalize;
        }

        @media (max-width: 420px) {
            .nav-tabs.affiliate_tabs .nav-item .nav-link {
                font-size: 13px;
            }
        }

        .nav-tabs.affiliate_tabs .nav-item .nav-link.active {
            background: #3A393A;
            color: #ffffff;
        }


        .affiliate_modals {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;

        }

        @media (max-width: 1400px) {
            .affiliate_modals {
                margin-top: 15px;
            }
        }

        .affiliate_modals button {
            font-size: 16px;
            font-weight: 600;
            border: 0;
            padding: 8.5px 20px;
            border-radius: 4px;
            color: white;
        }

        @media (max-width: 420px) {
            .affiliate_modals button {
                font-size: 13px;
            }
        }

        .affiliate_modals button.type1 {
            background: #FB1159;
            border: 1px solid #FB1159;

        }

        .affiliate_modals button.type1:hover {
            border: 1px solid #FB1159;
            background: transparent;
            color: #FB1159;
        }

        .affiliate_modals button.type2 {
            background: #333333;
            border: 1px solid #333333;

        }

        .affiliate_modals button.type2:hover {
            border: 1px solid #333333;
            background: transparent;
            color: #333333;
        }

        .tab-pane table thead tr th:not(:first-child), .tab-pane table tbody tr td:not(:first-child) {
            text-align: center;
            font-weight: 600;
        }

        .tab-pane table.affiliate_link_tab thead tr th:first-child,
        .tab-pane table.affiliate_link_tab tbody tr td:first-child {
            width: 400px;
        }

        .tab-pane table tbody tr td .link {
            max-width: 360px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .tab-pane table {
            border: 0.5px solid #e9e7f7 !important;
        }

        .modal .theme_btn {
            border: 1px solid var(--system_primery_color);
        }

        .modal .theme_btn:hover {
            background: transparent;
            border: 1px solid var(--system_primery_color);
        }

        .tab-pane table tr td {
            border-top: 0.5px solid #e9e7f7 !important;
            border-bottom: 0.5px solid #e9e7f7 !important;

            font-size: 13px;
        }

        .tab-pane table tr th {
            border-top: 1px solid #e9e7f7 !important;
            border-bottom: 1px solid #e9e7f7 !important;

            font-size: 13px;
        }

        .white_box {
            background: white;
            box-shadow: -12px 16px 40px 0px #0000000D;
            border-radius: 10px;
            padding: 18px;
            padding-left: 27px;
            padding-bottom: 48px;
        }

        .affiliate_buttons button {
            border: 0;
            border-radius: 4px;
            height: 26px;
            width: 26px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.3s all ease-in-out;
        }

        .affiliate_buttons button:hover {
            opacity: 0.7;
        }

        .affiliate_buttons .view_link {
            background: #D9D9D9 !important;
        }

        .affiliate_buttons .copy_link {
            background: #FB1159 !important;
        }

        .text-wrap {
            white-space: break-spaces;
            word-break: break-all;
        }

        .cs_modal .modal-body input {
            border: 1px solid #e9e7f7;
        }

        .copy_link_on_modal {
            width: fit-content !important;
            margin-top: 20px;
            height: fit-content !important;
        }

    </style>
@endsection

@section('mainContent')
    <div class="main_content_iner main_content_padding">
        <div class="">
            <div class="container-fluid g-0">
                <div class="row">
                    <div class="col-12">
                        <div class="purchase_history_wrapper">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="section__title3 mb_20">
                                        <h3 class="mb-0">{{__('affiliate.My Affiliate')}} @if($start_date && $end_date)
                                                [ {{showDate($start_date)}}
                                                - {{showDate($end_date)}} {{__('affiliate.Filter Record')}} ]
                                            @endif</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="row mx-0 affiliate_filter_section">
                                <div class="col-lg-12">
                                    @include('affiliate::student.components._filter')
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    @include('affiliate::student.components._balance_info')
                                </div>
                            </div>
                            <div class="row">
                                {{-- <div class="col-lg-3">
                                     @include('affiliate::student.components._create_link')
                                     @include('affiliate::student.components._paypal_account')
                                 </div>--}}
                                <div class="col-lg-12">
                                    @include('affiliate::student.components._table_data')
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="append_html"></div>
    @include('affiliate::student.components._withdraw_request_modal')
    @include('affiliate::student.components._balance_transfer_modal')
    @include('affiliate::_deleteModalForAjax',['item_name' => __("affiliate.Withdraw")])
    <input type="hidden" value="{{affiliateConfig('min_withdraw')}}" id="minimum_withdraw_amount">
    <input type="hidden" value="{{$user->affiliateWallet ? $user->affiliateWallet->amount : 0}}" id="user_balance">
    <input type="hidden" value="{{route('affiliate.withdraw_request.store')}}" id="withdraw_request_store_url">
    <input type="hidden" value="{{route('affiliate.withdraw_request.destroy')}}" id="withdraw_request_delete_url">
    <input type="hidden" value="{{route('affiliate.withdraw_request.edit',':id')}}" id="withdraw_request_edit_url">
    <input type="hidden" value="{{route('affiliate.withdraw_request.update',':id')}}" id="withdraw_request_update_url">
    <input type="hidden" value="{{route('affiliate.balance_transfer_to_wallet')}}" id="balance_transfer_url">
    <input name="csrf-token" value="{{ csrf_token() }}" id="csrf_token" type="hidden">
@endsection

@section('js')
    <script src="{{ asset('public/frontend/infixlmstheme/js/copy_currency.js') }}"></script>
    <script src="{{asset('public/js/moment.min.js')}}"></script>
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
@endsection
