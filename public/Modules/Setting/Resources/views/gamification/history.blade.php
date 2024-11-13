@extends('backend.master')
@push('styles')
    <link rel="stylesheet" href="{{ asset('backend/css/student_list.css')}}{{assetVersion()}}"/>
@endpush
@php
    $table_name='users';
@endphp
@section('table')
    {{$table_name}}
@endsection

@section('mainContent')

    {!! generateBreadcrumb() !!}

    <section class="admin-visitor-area up_st_admin_visitor">
        <div class="container-fluid p-0">
            <div class="white-box">
                <div class="row justify-content-center">
                    <div class="col-lg-12">
                        <div class="QA_section QA_section_heading_custom check_box_table">
                            <div class="QA_table ">
                                <!-- table-responsive -->
                                <div class="">
                                    <table id="lms_table" class="table Crm_table_active3">
                                        <thead>
                                        <tr>
                                            <th scope="col">{{__('common.SL')}}</th>
                                            <th scope="col">{{__('common.Image')}}</th>
                                            <th scope="col">{{__('common.Name')}}</th>
                                            <th scope="col">{{__('common.Email')}}</th>
                                            <th scope="col">{{__('common.Total')}} {{__('common.Point')}}</th>
                                            <th scope="col">{{__('common.Spent')}} {{__('common.Point')}}</th>
                                            <th scope="col">{{__('common.Remained')}} {{__('common.Point')}}</th>
                                            <th scope="col">{{__('common.Action')}}</th>
                                        </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>


                </div>
            </div>

        </div>
    </section>


    <div class="modal fade admin-query" id="view_details">
        <div class="modal-dialog modal_1000px modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="ViewTitle"></h4>
                    <button type="button" class="close " data-bs-dismiss="modal">
                        <i class="ti-close "></i>
                    </button>
                </div>

                <div class="modal-body" id="viewBody" style="max-height: 500px;overflow-y: auto">

                </div>
            </div>
        </div>
    </div>

@endsection
@push('scripts')

    @php
        $url = route('gamification.historyData');
    @endphp

    <script>

        dataTableOptions.serverSide = true
        dataTableOptions.processing = true
        dataTableOptions.ajax = '{!! $url !!}';
        dataTableOptions.columns = [
            {data: 'DT_RowIndex', name: 'id', orderable: true},
            {data: 'image', name: 'image', orderable: false},
            {data: 'name', name: 'name'},
            {data: 'email', name: 'email'},
            {data: 'gamification_total_points', name: 'gamification_total_points'},
            {data: 'gamification_total_spent_points', name: 'gamification_total_spent_points'},
            {data: 'gamification_total_remain_points', name: 'gamification_total_remain_points', orderable: false},
            {data: 'action', name: 'action', orderable: false},
        ];
        dataTableOptions = updateColumnExportOption(dataTableOptions, [0, 2, 3, 4, 5, 6]);

        let table = $('#lms_table').DataTable(dataTableOptions);


        $(document).on('click', '.detailsHistory', function () {
            let id = $(this).data('id');
            let type = $(this).data('type');
            let title = $(this).data('title');
            let url = '{{url('/')}}/gamification/history-details/' + type + '/' + id;


            $.ajax({
                type: 'GET',
                url: url,
                dataType: "html",
                success: function (data) {
                    $("#ViewTitle").text(title);
                    $("#viewBody").html(data);
                    $("#view_details").modal('show');
                },
                error: function (data) {
                    toastr.error('Something Went Wrong', 'Error');
                }
            });


        });
    </script>

@endpush
