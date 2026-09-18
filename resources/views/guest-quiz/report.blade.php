@extends('layouts.app')

@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <!-- <h4>Guest Quizzers Management</h4> -->
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-users"></i> {{ __('students.titles.guest_reports') }}</h3>
                    </div>
                    <div class="card-body">
                        <table id="guestTable" class="table table-bordered table-striped table-hover w-100">
                            <thead>
                                <tr>
                                    <th>{{ __('common.fields.id') }}</th>
                                    <th>{{ __('common.fields.student_name') }}</th>
                                    <th>{{ __('common.fields.grade') }}</th>
                                    <th>{{ __('common.fields.school') }}</th>
                                    <th>{{ __('common.fields.email') }}</th>
                                    <th>{{ __('common.fields.phone') }}</th>
                                    <th>{{ __('common.fields.created_at') }}</th>
                                    <th>{{ __('common.fields.expires_at') }}</th>
                                    <th>{{ __('common.fields.status') }}</th>
                                    <th>{{ __('common.fields.action') }}</th>
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
</section>
@endsection

@section('script')
<script>
    const guestReportUi = {
        delete_confirm: @json(__('students.guest_reports.delete_confirm')),
        deleted: @json(__('students.guest_reports.deleted')),
        delete_error: @json(__('students.guest_reports.delete_error')),
        datatable: @json(__('common.datatable')),
    };

    $(document).ready(function() {
        var table = $('#guestTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('guest.report') }}",
            language: {
                processing: `<div class="spinner-border text-primary"></div>`,
                lengthMenu: guestReportUi.datatable.length_menu,
                zeroRecords: guestReportUi.datatable.zero_records,
                info: guestReportUi.datatable.info,
                infoEmpty: guestReportUi.datatable.info_empty,
                infoFiltered: guestReportUi.datatable.info_filtered,
                search: guestReportUi.datatable.search,
                paginate: {
                    first: guestReportUi.datatable.first,
                    previous: guestReportUi.datatable.previous,
                    next: guestReportUi.datatable.next,
                    last: guestReportUi.datatable.last
                }
            },
            columns: [
                { data: 'id', name: 'id' },
                { data: 'name', name: 'first_name' }, // Search against first_name instead of generic name
                { data: 'grade', name: 'grade' },
                { data: 'school', name: 'school' },
                { data: 'email', name: 'email' },
                { data: 'phone', name: 'phone' },
                { data: 'created_at', name: 'created_at' },
                { data: 'expires_at', name: 'expires_at' },
                { data: 'status', name: 'status', orderable: false, searchable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            order: [[0, 'desc']]
        });

        $('body').on('click', '.btn-delete', function () {
            var id = $(this).data("rowid");
            if(confirm(guestReportUi.delete_confirm)){
                $.ajax({
                    type: "POST",
                    url: "{{ route('guest.report.destroy') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: id
                    },
                    success: function (data) {
                        table.draw();
                        toastr.success(guestReportUi.deleted);
                    },
                    error: function (data) {
                        console.log('Error:', data);
                        toastr.error(guestReportUi.delete_error);
                    }
                });
            }
        });
    });
</script>
@endsection
