@extends('backend.master')
@push('styles')
    <link rel="stylesheet" href="{{asset('Modules/Affiliate/Resources/assets/css')}}/daterangepicker.css"></link>
@endpush
@section('mainContent')

    {!! generateBreadcrumb() !!}

    <section class="admin-visitor-area up_st_admin_visitor">
        <div class="container-fluid p-0">
            <div class="white-box">
                <div class="row">
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-md-6 date-range-block">
                                <div class="primary_input mb-15 date_range">
                                    <div class="primary_datepicker_input filter">
                                        <label class="primary_input_label"
                                               for="">{{__('affiliate.Select Date Range')}}</label>
                                        <div class="g-0 input-right-icon">
                                            <div class="col">
                                                <div class="">
                                                    <input readonly
                                                           class="primary_input_field filter_date_input_field date_range_input"
                                                           type="text"
                                                           name="date_range_filter" value="">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 mt-30">
                                <div class="d-flex">
                                    <button id="reset-date-filter" type="button"
                                            class="primary-btn mr-10 fix-gr-bg">{{__('affiliate.Filter')}}</button>
                                    <a type="button" href="{{route('affiliate.pending_withdraw')}}"
                                       class="primary-btn  fix-gr-bg">{{__('affiliate.Reset')}}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-end mt-4">
                            @if(permissionCheck('affiliate.my_affiliate.index') && affiliateConfig('communication_approval_system') != 'cron')
                                <a href="{{route('affiliate.pending_commission.approved')}}" type="button"
                                   class="primary-btn   fix-gr-bg">{{__('affiliate.Commission Approved')}}</a>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
            <div class="white-box mt-20">
                <div class="row justify-content-center">
                    <div class="col-12">
                        <div class="box_header common_table_header">
                            <div class="main-title d-md-flex">
                                <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{__('affiliate.Pending Withdrawn List')}}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12">

                        <div class="QA_section QA_section_heading_custom check_box_table">
                            <div class="QA_table ">
                                <div class="">
                                    <table id="lms_table" class="table classList">
                                        <thead>
                                        <tr>
                                            <th scope="col">{{__('affiliate.SL')}}</th>
                                            <th scope="col">{{__('affiliate.Date')}}</th>
                                            <th scope="col">{{__('affiliate.Amount')}}</th>
                                            <th scope="col">{{__('common.Title')}}</th>
                                            <th scope="col">{{__('affiliate.User')}}</th>
                                            <th scope="col">{{__('common.Status')}}</th>
                                        </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" value="{{route('affiliate.commissionData')}}" id="datatable_url">
    </section>
@endsection
@push('scripts')
    <script src="{{asset('Modules/Affiliate/Resources/assets/js')}}/daterangepicker.min.js"></script>
    <script src="{{asset('public/modules/common/date_range_init.js')}}"></script>

    <script>
        dataTableOptions.ajax = {
            url: $('#datatable_url').val(),
            data: function (d) {
                d.filter_date = $('input[name="date_range_filter"]').val();
            },
        }
        dataTableOptions.processing = true
        dataTableOptions.serverSide = true

        dataTableOptions.columns = [
            {data: 'DT_RowIndex', name: 'id'},
            {data: 'date', name: 'date'},
            {data: 'amount', name: 'amount'},
            {data: 'title', name: 'title'},
            {data: 'user_name', name: 'user_name'},
            {data: 'status', name: 'status'},
        ];
        dataTableOptions = updateColumnExportOption(dataTableOptions, [0, 1, 2, 3, 4]);

        $('#lms_table').DataTable(dataTableOptions);

    </script>
@endpush

