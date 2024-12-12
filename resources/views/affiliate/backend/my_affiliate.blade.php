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
    @include('affiliate.backend.my_affiliate-head')

    @yield('style')

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
                                        <h1 class="gradient-color2"> {{ number_format($amount, 2) ?? '0' }} ฿</h1>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="col">
                            <a href="#" class="d-block">
                                <div class="white-box single-summery">
                                    <div class="d-flex justify-content-between">
                                        <div class="row">
                                            {{-- <div class="col-12">
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
                                            </div> --}}
                                            <div class="col-12">
                                                <div class="row d-flex justify-content-between">
                                                    <div>
                                                        <h3>Total Earning</h3>
                                                    </div>
                                                    <h1 class="gradient-color2">
                                                        @if ($start_date && $end_date)
                                                            {{ number_format($user->affiliateCommissions->where('status', 'approved')->whereBetween('created_at', [$start_date, $end_date])->sum('commission_amount'),2) }}
                                                        @else
                                                            {{ number_format($user->affiliateCommissions->where('status', 'approved')->sum('commission_amount'), 2) }}
                                                        @endif
                                                        ฿
                                                    </h1>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="row d-flex justify-content-between">
                                                    <div>
                                                        <h3>Pending</h3>
                                                    </div>
                                                    <h1 class="gradient-color2">
                                                        @if ($start_date && $end_date)
                                                            {{ number_format($user->affiliateCommissions->where('status', 'pending')->whereBetween('date', [$start_date, $end_date])->sum('commission_amount'),2) }}
                                                        @else
                                                            {{-- {{ number_format($user->affiliateCommissions->where('status', 'pending' or 'paid')->sum('commission_amount'), 2) }} --}}
                                                            {{ number_format($user->affiliateCommissions->whereIn('status', ['pending', 'paid'])->sum('commission_amount'), 2) }}
                                                        @endif
                                                        ฿
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

    @include('affiliate.backend.my_affiliate-script')
    @yield('script')

</body>

</html>
