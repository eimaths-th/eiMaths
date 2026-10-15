<script>
    $(document).ready(function() {
        const user_access_ui = @json(__('user-access'));
        $(".delete_all_button").click(function() {
            var len = $('input[name="table_records[]"]:checked').length;
            if (len > 0) {

                if (confirm(user_access_ui.messages.confirm_delete)) {
                    $('form#delete_all').submit();
                }
            } else {
                alert(user_access_ui.messages.select_to_delete);
            }

        });

        $('#check-all').click(function() {
            $(':checkbox.flat').prop('checked', this.checked);
        });

        //$.noConflict();
        var token = ''
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });



        var tableOptions = {
            language: {
                loadingRecords: '&nbsp;',
                processing: `<div class="spinner-border text-primary"></div>`,
                sProcessing: user_access_ui.data_table.processing,
                sLengthMenu: user_access_ui.data_table.length_menu,
                sZeroRecords: user_access_ui.data_table.zero_records,
                sInfo: user_access_ui.data_table.info,
                sInfoEmpty: user_access_ui.data_table.info_empty,
                sInfoFiltered: user_access_ui.data_table.info_filtered,
                sSearch: user_access_ui.data_table.search,
                oPaginate: {
                    sFirst: user_access_ui.data_table.first,
                    sPrevious: user_access_ui.data_table.previous,
                    sNext: user_access_ui.data_table.next,
                    sLast: user_access_ui.data_table.last
                }
            },
            ajax: '', // Your AJAX URL here
            serverSide: true,
            processing: true,
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
                    data: 'checkbox',
                    name: 'checkbox',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                }
            ]
        };

        // Initialize DataTable
        var table = $('#Listview').DataTable(tableOptions);

        // Reset button click event
        $('#resetSearchButton').on('click', async function() {
            if (table) {
                table.clear().draw(); // Clear all data from the table
                table.state.clear(); // Clear state
                await table.destroy(); // Destroy the instance
            }

            // Reinitialize the DataTable with options
            table = $('#Listview').DataTable(tableOptions);
            table.draw();
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
        $(document).on('click', '#CreateButton', function(e) {
            e.preventDefault();
            $('.alert-danger').html('');
            $('.alert-danger').hide();
            $('.alert-success').html('');
            $('.alert-success').hide();
            $('#CreateModal').modal('show');
        });


        // Create product Ajax request.
        $('#SubmitCreateForm').click(function(e) {
            e.preventDefault();
            $('.alert-danger').html('');
            $('.alert-danger').hide();
            $('.alert-success').html('');
            $('.alert-success').hide();

            var list = $("input[name='permission[]']:checked").map(function() {
                return this.value;
            }).get();



            $.ajax({
                url: "{{ route('permission.store') }}",
                method: 'post',
                data: {
                    name: $('#AddName').val(),
                    // permission: list,
                    _token: token,
                },
                success: function(result) {

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
                        //$('#SubmitCreateForm').hide();
                        //setTimeout(function() {
                        //$('.alert-success').hide();
                        $('#CreateModal').modal('hide');
                        //}, 10000);

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

            id = $(this).data('id');
            $.ajax({
                url: "{{ route('permission.edit', ':id') }}".replace(':id', id),
                method: 'GET',
                success: function(res) {
                    // console.log(res);
                    $('#editName').val(res.name);
                    $('#EditModal').modal('show');
                }
            });
            // $('#EditModal').modal('show');

        })

        $('#SubmitEditForm').click(function(e) {
            if (!confirm(user_access_ui.messages.confirm_save)) return;
            e.preventDefault();

            $('.alert-danger').html('');
            $('.alert-danger').hide();
            $('.alert-success').html('');
            $('.alert-success').hide();


            var elist = $("input[name='epermission[]']:checked").map(function() {
                return this.value;
            }).get();

            $.ajax({
                url: "{{ route('permission.update', ':id') }}".replace(':id', id),
                method: 'PUT',
                data: {
                    name: $('#editName').val(),
                    permission: elist,
                    _token: token
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
                        $('.alert-success').append('<strong><li>' + result.success +
                            '</li></strong>');
                        $('#EditModal').modal('hide');
                        toastr.success(result.success, {
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
            if (!confirm(user_access_ui.messages.confirm_delete)) return;

            var rowid = $(this).data('rowid')
            var el = $(this)
            if (!rowid) return;


            $.ajax({
                //type: "POST",
                method: 'DELETE',
                dataType: 'JSON',
                url: "roles/destroy/",
                data: {
                    id: rowid,
                    //_method: 'delete',
                    _token: token
                },
                success: function(data) {
                    if (data.success) {
                        toastr.success(data.message, {
                            timeOut: 5000
                        });
                        table.row(el.parents('tr'))
                            .remove()
                            .draw();
                    } else {
                        toastr.error(result.errors, {
                            timeOut: 5000
                        });
                    }
                }
            }); //end ajax
        })


    });
</script>
