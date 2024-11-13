@extends('backend.master')
@push('styles')
    <style>
        .pos_tab_btn ul li a.active {
            background: #415094 !important;
        }
    </style>
@endpush
@section('mainContent')

    <section class="admin-visitor-area up_st_admin_visitor">

        <div class="container-fluid p-0">
            <div class="row">
                <div class="col-md-12 mb-20">
                    <div class="box_header_right">
                        <div class="float-lg-end float-none pos_tab_btn justify-content-end">
                            <ul class="nav" role="tablist">

                                <li class="nav-item">
                                    <a class="nav-link active show" href="#all_user" role="tab" data-bs-toggle="tab"
                                       id="1" aria-selected="true">{{__('affiliate.All User')}}</a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link" href="#active_user" role="tab" data-bs-toggle="tab"
                                       id="1" aria-selected="true">{{__('affiliate.Active User')}}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#inactive_user" role="tab" data-bs-toggle="tab"
                                       id="1" aria-selected="true">{{__('affiliate.Inactive User')}}</a>
                                </li>

                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-xl-12">
                    <div class=" mb_30">
                        <div class="tab-content white-box">

                            <div role="tabpanel" class="tab-pane fade active show" id="all_user">
                                <div class="box_header common_table_header ">
                                    <div class="main-title d-md-flex">
                                        <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{__('affiliate.All User')}}</h3>
                                    </div>
                                </div>
                                <div class="QA_section QA_section_heading_custom check_box_table">
                                    <div class="QA_table">
                                        <!-- table-responsive -->
                                        <div class="">
                                            @include('affiliate::user.components.all_list')
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div role="tabpanel" class="tab-pane fade" id="active_user">
                                <div class="box_header common_table_header ">
                                    <div class="main-title d-md-flex">
                                        <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{__('affiliate.Active User')}}</h3>
                                    </div>
                                </div>
                                <div class="QA_section QA_section_heading_custom check_box_table">
                                    <div class="QA_table">
                                        <!-- table-responsive -->
                                        <div class="">
                                            @include('affiliate::user.components.active_list')
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div role="tabpanel" class="tab-pane fade" id="inactive_user">
                                <div class="box_header common_table_header ">
                                    <div class="main-title d-md-flex">
                                        <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{__('affiliate.Inactive User')}}
                                        </h3>
                                    </div>
                                </div>
                                <div class="QA_section QA_section_heading_custom check_box_table">
                                    <div class="QA_table">
                                        <!-- table-responsive -->
                                        <div class="">
                                            @include('affiliate::user.components.inactive_list')
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" value="{{route('affiliate.users.approved',':id')}}" id="user_active_url">

    </section>
@endsection
@push('scripts')
    ')
    <script type="text/javascript">
        (function ($) {
            "use strict";

            function datatable(element, url) {

                dataTableOptions.ajax = {
                    url: url,
                }
                dataTableOptions.processing = true
                dataTableOptions.serverSide = true
                dataTableOptions.columns = [
                    {data: 'DT_RowIndex', name: 'id'},
                    {data: 'avatar', name: 'avatar'},
                    {data: 'name', name: 'name'},
                    {data: 'email', name: 'email'},
                    {data: 'phone', name: 'phone'},
                    {data: 'status', name: 'status'},
                    {data: 'action', name: 'action'}
                ];
                dataTableOptions = updateColumnExportOption(dataTableOptions, [0, 2, 3, 4]);

                element.DataTable(dataTableOptions);
                $('.dataTables_length label select').niceSelect();
                $('.dataTables_length label .nice-select').addClass('dataTable_select');
            }

            let allUserElement = $('#allUserTable');
            let activeUserElement = $('#activeUserTable');
            let inactiveUserElement = $('#inactiveUserTable');
            let allUserUrl = "{{ route('affiliate.users.datatable') }}" + '?table=all_users';
            let activeUserUrl = "{{ route('affiliate.users.datatable') }}" + '?table=active_users';
            let inactiveUserUrl = "{{ route('affiliate.users.datatable') }}" + '?table=inactive_users';


            datatable(allUserElement, allUserUrl)
            datatable(activeUserElement, activeUserUrl)
            datatable(inactiveUserElement, inactiveUserUrl)


            $(document).on('click', '.user_confirm', function (event) {
                event.preventDefault();
                let id = $(this).data('id');
                let url = $('#user_active_url').val();
                url = url.replace(':id', id);
                $.get(url, function (response) {
                    if (response) {
                        toastr.success("{{__('affiliate.Affiliate User Active Successfully')}}");
                        resetAfterChange();
                    }
                });
            });

            function resetAfterChange() {
                datatable(allUserElement, allUserUrl)
                datatable(activeUserElement, activeUserUrl)
                datatable(inactiveUserElement, inactiveUserUrl)
            }
        })(jQuery);

    </script>
@endpush
