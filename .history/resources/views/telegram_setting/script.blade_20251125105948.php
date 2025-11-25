<script>
    $(document).ready(function() {
        // Initialize DataTable
        var table = $('#Listview').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('telegram-setting.index') }}",
            columns: [
                { data: 'no', name: 'no', orderable: false, searchable: false },
                { data: 'description', name: 'description' },
                { data: 'created_at', name: 'created_at' },
                { data: 'updated_at', name: 'updated_at' },
                { data: 'status', name: 'status', orderable: false, searchable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false },
            ]
        });

        // Open Create Modal
        $('#CreateButton').click(function() {
            $('#CreateForm')[0].reset();
            $('#CreateModal').modal('show');
        });

        // Create Form Submit
        $('#CreateForm').submit(function(e) {
            e.preventDefault();
            var formData = $(this).serialize();

            $.ajax({
                url: "{{ route('telegram-setting.store') }}",
                type: "POST",
                data: formData,
                success: function(response) {
                    $('#CreateModal').modal('hide');
                    table.ajax.reload();
                    toastr.success(response.success || 'Telegram setting created successfully');
                },
                error: function(xhr) {
                    var errors = xhr.responseJSON.errors;
                    if (errors) {
                        $.each(errors, function(key, value) {
                            toastr.error(value[0]);
                        });
                    } else {
                        toastr.error('An error occurred');
                    }
                }
            });
        });

        // Edit Button Click
        $(document).on('click', '.btn-edit', function() {
            var id = $(this).data('id');

            $.ajax({
                url: "{{ url('telegram-setting') }}/" + id + "/edit",
                type: "GET",
                success: function(response) {
                    $('#edit_id').val(response.data.id);
                    $('#edit_bot_token').val(response.bot_token);
                    $('#edit_chat_id').val(response.chat_id);
                    $('#edit_description').val(response.data.description);
                    $('#EditModal').modal('show');
                },
                error: function(xhr) {
                    toastr.error('Failed to load data');
                }
            });
        });

        // Edit Form Submit
        $('#EditForm').submit(function(e) {
            e.preventDefault();
            var id = $('#edit_id').val();
            var formData = $(this).serialize();

            $.ajax({
                url: "{{ url('telegram-setting') }}/" + id,
                type: "PUT",
                data: formData,
                success: function(response) {
                    $('#EditModal').modal('hide');
                    table.ajax.reload();
                    toastr.success(response.message || 'Telegram setting updated successfully');
                },
                error: function(xhr) {
                    var errors = xhr.responseJSON.errors;
                    if (errors) {
                        $.each(errors, function(key, value) {
                            toastr.error(value[0]);
                        });
                    } else {
                        toastr.error('An error occurred');
                    }
                }
            });
        });

        // Delete Button Click
        $(document).on('click', '.btn-delete', function() {
            var id = $(this).data('rowid');

            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('telegram-setting') }}/" + id,
                        type: "DELETE",
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            table.ajax.reload();
                            toastr.success(response.message || 'Telegram setting deleted successfully');
                        },
                        error: function(xhr) {
                            toastr.error('Failed to delete');
                        }
                    });
                }
            });
        });

        // Status Toggle
        $(document).on('change', '.toggle-status-update', function() {
            var id = $(this).data('id');
            var status = $(this).is(':checked') ? 1 : 0;

            $.ajax({
                url: "{{ url('telegram-setting') }}/" + id + "/status",
                type: "PUT",
                data: {
                    _token: "{{ csrf_token() }}",
                    status: status
                },
                success: function(response) {
                    table.ajax.reload();
                    toastr.success(response.message || 'Status updated successfully');
                },
                error: function(xhr) {
                    toastr.error('Failed to update status');
                }
            });
        });

        // Test Notification Button Click
        $(document).on('click', '.btn-test', function() {
            var id = $(this).data('id');
            var btn = $(this);

            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Testing...');

            $.ajax({
                url: "{{ url('telegram-setting') }}/" + id + "/test",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Test');
                    if (response.success) {
                        toastr.success(response.message);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Test');
                    toastr.error('Failed to send test notification');
                }
            });
        });
    });
</script>
