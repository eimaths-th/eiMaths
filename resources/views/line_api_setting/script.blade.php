<script>
    $(document).ready(function() {

        $(".delete_all_button").click(function() {
            var len = $('input[name="table_records[]"]:checked').length;
            if (len > 0) {

                if (confirm("Confirm Delete Data?")) {
                    $('form#delete_all').submit();
                }
            } else {
                alert("please select record");
            }

        });

        $('#check-all').click(function() {
            $(':checkbox.flat').prop('checked', this.checked);
        });

        $(".select2_single").select2({
            maximumSelectionLength: 1,
            allowClear: false,
            //theme: 'bootstrap4'
            placeholder: 'Please Select'
        });

        $(".select2_single").on("select2:unselect", function(e) {
            //log("select2:unselect", e);
            //$('.positions').html('');
        });

        $(".select2_singles").select2({
            maximumSelectionLength: 1,
            allowClear: false,
            //theme: 'bootstrap4'
            placeholder: 'Please Select'
        });


        $(".select2_multiple").select2({
            maximumSelectionLength: 2,
            //placeholder: "With Max Selection limit 4",
            allowClear: false,
            //theme: 'bootstrap4'
            placeholder: 'Please Select'
        });


        $(".departmentl").change(function() {
            let department = $('#AddDepartment').val();
            //console.log(product);
            //alert(product);
            $('#AddPosition').html('');
            if (department.length !== 0) {
                $.ajax({
                    method: "GET",
                    url: "departments/find/add/" + department,
                    success: function(res) {
                        $('.positions').html(res.html);
                    }
                });
            }
        })

        $(".departmente.select2").on('select2:select', function() {
            let department = $('#EditDepartment').val();
            //console.log(product);
            //alert(product);
            $('#EditPosition').html('');
            if (department.length !== 0) {
                $.ajax({
                    method: "GET",
                    url: "departments/find/add/" + department,
                    async: false,
                    success: function(res) {
                        $('#EditPosition').html(res.html);
                        //console.log(res);

                    }
                });
            }

        })

        //$.noConflict();
        var token = ''
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });


        var table = $('#Listview').DataTable({
            ajax: '',
            serverSide: true,
            processing: true,
            language: {
                loadingRecords: '&nbsp;',
                processing: `<div class="spinner-border text-primary"></div>`,
                "sProcessing": "Processing...",
                "sLengthMenu": "Display_MENU_ Row",
                "sZeroRecords": "No Data Found",
                "sInfo": "Display _START_ To _END_ From _TOTAL_ Records",
                "sInfoEmpty": "Display 0 To 0 From 0 Records",
                "sInfoFiltered": "(Filtered _MAX_ Row)",
                "sSearch": "Search:",
                "oPaginate": {
                    "sFirst": "First",
                    "sPrevious": "Previous",
                    "sNext": "Next",
                    "sLast": "Last"
                }
            },
            aaSorting: [
                [0, "desc"]
            ],
            iDisplayLength: 10,
            lengthMenu: [10, 25, 50, 75, 100],
            stateSave: true,
            autoWidth: false,
            responsive: true,
            sPaginationType: "full_numbers",
            dom: 'T<"clear">lfrtip',
            columns: [{
                    data: 'no',
                    name: 'no',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'name',
                    name: 'name',
                    width: '200px' // Adjust column width
                },
                {
                    data: 'created_at',
                    name: 'created_at',
                    width: '200px' // Adjust column width
                },
                {
                    data: 'updated_at',
                    name: 'updated_at',
                    width: '200px' // Adjust column width
                },
                {
                    data: 'line_user_id',
                    name: 'line_user_id',
                    width: '200px' // Adjust column width
                },
                // {
                //     data: 'channel_secret',
                //     name: 'channel_secret',
                //     width: '200px' // Adjust column width
                // },
                // {
                //     data: 'channel_access_token',
                //     name: 'channel_access_token',
                //     width: '200px' // Adjust column width
                // },
                {
                    data: 'create_by',
                    name: 'create_by'
                },
                {
                    data: 'update_by',
                    name: 'update_by'
                },
                {
                    data: 'status',
                    name: 'status'
                },
                {
                    data: 'action',
                    name: 'action'
                },
            ],
            columnDefs: [{
                targets: 0, // Index column, which is 'no'
                render: function(data, type, row, meta) {
                    return meta.row + 1; // Add row number, starting from 1
                }
            }]
        });

        $("#example1").DataTable({
            "responsive": true,
            "lengthChange": false,
            "autoWidth": false,
            "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
        }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
        $('#example2').DataTable({
            "paging": true,
            "lengthChange": false,
            "searching": false,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": true,
        });


        $(document).on('click', '.toggle-status-update', function() {
            const button = $(this);
            const id = button.data('id');
            const currentStatus = button.data('status');
            const newStatus = currentStatus == 1 ? 0 : 1; // Toggle the status

            $.ajax({
                url: "{{ route('line-api.updateStatus', ':id') }}".replace(':id', id),
                type: 'POST',
                data: {
                    status: newStatus,
                    _token: $('meta[name="csrf-token"]').attr('content') // Include CSRF token
                },
                success: function(response) {
                    if (response.success) {
                        button
                            .data('status', newStatus) // Update data-status attribute
                            .toggleClass('btn-success btn-danger') // Toggle button class
                            .text(newStatus == 1 ? 'On' : 'Off'); // Update button text
                        // alert(response.message);
                    }
                    toastr.success(response.message, {
                        timeOut: 5000
                    });
                    $('#Listview').DataTable().ajax.reload();

                },
                error: function() {
                    alert('Failed to update status.');
                }
            });
        });


        $(document).on('click', '#CreateButton', function(e) {
            e.preventDefault();
            $('.alert-danger').html('');
            $('.alert-danger').hide();
            $('.alert-success').html('');
            $('.alert-success').hide();
            $('.form').trigger('reset');
            $('#CreateModal').modal('show');
        });


        // Create product Ajax request.
        $('#SubmitCreateForm').click(function(e) {
            e.preventDefault();
            $('.alert-danger').html('');
            $('.alert-danger').hide();
            $('.alert-success').html('');
            $('.alert-success').hide();


            $.ajax({
                url: "{{ route('line-api.store') }}",
                method: 'post',
                data: {
                    name: $('#name').val(),
                    line_user_id: $('#Addline_user_id').val(),
                    channel_secret: $('#Addchannel_secret').val(),
                    channel_access_token: $('#Addchannel_access_token').val(),
                    _token: token,
                },
                success: function(result) {
                    console.log(result); // Debug the response
                    if (result.errors) {
                        $('.alert-danger').html('');
                        $.each(result.errors, function(key, value) {
                            $('.alert-danger').show();
                            $('.alert-danger').append('<strong><li>' + value +
                                '</li></strong>');
                        });
                    } else {
                        $('.alert-danger').hide();
                        $('.alert-success').show();
                        $('.alert-success').append('<strong><li>' + result.success +
                            '</li></strong>');
                        toastr.success(result.success, {
                            timeOut: 5000
                        });
                        $('#Listview').DataTable().ajax.reload();
                        $('.form').trigger('reset');
                        $('#CreateModal').modal('hide');
                    }
                }
            });
        });

        let id;
        $(document).on('click', '#getEditData', function(e) {
            e.preventDefault();


            $('.alert-danger').html('');
            $('.alert-danger').hide();
            $('.alert-success').html('');
            $('.alert-success').hide();

            $('#EditPosition').empty();

            id = $(this).data('id');
            $.ajax({
                url: "{{ route('line-api.edit', ':id') }}".replace(':id', id),
                method: 'GET',
                success: function(res) {
                    // console.log(res);

                    $('#editname').val(res.data.name);
                    $('#line_user_id').val(res.data.line_user_id);
                    $('#channel_secret').val(res.data.channel_secret);
                    $('#channel_access_token').val(res.data.channel_access_token);
                    // $('#line_user_id').val(res.line_user_id);
                    // $('#channel_secret').val(res.channel_secret);
                    // $('#channel_access_token').val(res.channel_access_token);

                    $('#EditModal').modal('show');
                }
            });

        })

        $('#SubmitEditForm').click(function(e) {
            if (!confirm("Confirm Save ?")) return;
            e.preventDefault();

            $('.alert-danger').html('');
            $('.alert-danger').hide();
            $('.alert-success').html('');
            $('.alert-success').hide();

            if ($('#ecustomCheckbox1').is(":checked")) {
                esstatus = 1;
            } else {
                esstatus = 0;
            }

            $.ajax({
                url: "{{ route('line-api.update', ':id') }}".replace(':id', id),
                method: 'PUT',
                data: {
                    name: $('#editname').val(),
                    channel_secret: $('#channel_secret').val(),
                    line_user_id: $('#line_user_id').val(),
                    channel_access_token: $('#channel_access_token').val(),
                },

                success: function(result) {
                    //console.log(result);
                    if (result.errors) {
                        $('.alert-danger').html('');
                        $.each(result.errors, function(key, value) {
                            $('.alert-danger').show();
                            $('.alert-danger').append('<strong><li>' + value +
                                '</li></strong>');
                        });
                    } else {
                        $('.alert-danger').hide();
                        $('.alert-success').show();
                        $('.alert-success').append('<strong><li>' + result.message +
                            '</li></strong>');
                        $('#EditModal').modal('hide');
                        toastr.success(result.message, {
                            timeOut: 5000
                        });
                        $('#Listview').DataTable().ajax.reload();
                        //setTimeout(function() {
                        //$('.alert-success').hide();

                        //}, 10000);

                    }
                }
            });
        });

        $(document).on('click', '.btn-delete', function() {
            if (!confirm("Confirm Delete Data ?")) return;

            var id = $(this).data('rowid')
            var el = $(this)
            if (!id) return;

            // console.log(id);

            $.ajax({
                //type: "POST",
                method: 'DELETE',
                dataType: 'JSON',
                // url: "users/destroy/",
                url: "{{ route('line-api.destroy', ':id') }}".replace(':id', id),
                data: {
                    id: id,
                    //_method: 'delete',
                    _token: token
                },
                success: function(data) {
                    console.log(data);
                    if (data.success) {
                        toastr.success(data.message, {
                            timeOut: 5000
                        });
                        table.row(el.parents('tr'))
                            .remove()
                            .draw();
                    } else {
                        toastr.error(data.errors, {
                            timeOut: 5000
                        });
                    }
                }
            }); //end ajax
        })


    });
</script>
