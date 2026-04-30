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
                        <h3 class="card-title"><i class="fas fa-users"></i> Guest Quizzers List</h3>
                    </div>
                    <div class="card-body">
                        <table id="guestTable" class="table table-bordered table-striped table-hover w-100">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Grade</th>
                                    <th>School</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Created At</th>
                                    <th>Expires At</th>
                                    <th>Status</th>
                                    <th>Action</th>
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
    $(document).ready(function() {
        var table = $('#guestTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('guest.report') }}",
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
            if(confirm("Are you sure you want to delete this guest quizzer? All their test data will be deleted too.")){
                $.ajax({
                    type: "POST",
                    url: "{{ route('guest.report.destroy') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: id
                    },
                    success: function (data) {
                        table.draw();
                        toastr.success("Guest Quizzer deleted successfully");
                    },
                    error: function (data) {
                        console.log('Error:', data);
                        toastr.error("Error deleting guest quizzer");
                    }
                });
            }
        });
    });
</script>
@endsection
