@extends('backend.master')
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

    <section class="admin-visitor-area up_st_admin_visitor">
        <div class="container-fluid p-0">
            @include('affiliate::affiliate.components._filter')
            @include('affiliate::affiliate.components._balance_info')
            <div class="row">
                <div class="col-lg-3">

                    @include('affiliate::affiliate.components._create_link')
                    @include('affiliate::affiliate.components._paypal_account')

                </div>
                <div class="col-lg-9">
                    @include('affiliate::affiliate.components._table_data')
                </div>
            </div>
        </div>
        <div id="append_html"></div>
        @include('affiliate::affiliate.components._withdraw_request_modal')
        @include('affiliate::affiliate.components._balance_transfer_modal')
        @include('affiliate::_deleteModalForAjax',['item_name' => __("affiliate.Withdraw")])
        <input type="hidden" value="{{affiliateConfig('min_withdraw')}}" id="minimum_withdraw_amount">
        <input type="hidden" value="{{$user->affiliateWallet ? $user->affiliateWallet->amount : 0}}" id="user_balance">
        <input type="hidden" value="{{route('affiliate.withdraw_request.store')}}" id="withdraw_request_store_url">
        <input type="hidden" value="{{route('affiliate.withdraw_request.destroy')}}" id="withdraw_request_delete_url">
        <input type="hidden" value="{{route('affiliate.withdraw_request.edit',':id')}}" id="withdraw_request_edit_url">
        <input type="hidden" value="{{route('affiliate.withdraw_request.update',':id')}}"
               id="withdraw_request_update_url">
        <input type="hidden" value="{{route('affiliate.balance_transfer_to_wallet')}}" id="balance_transfer_url">
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
@endpush
