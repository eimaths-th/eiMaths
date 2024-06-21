<script>
    $(document).ready(function() {
        $(".delete_all_button").click(function() {
            var len = $('input[name="table_records[]"]:checked').length;
            if (len > 0) {

                if (confirm("Click OK to Delete?")) {
                    $('form#delete_all').submit();
                }
            } else {
                alert("please select record");
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
                sProcessing: "Processing...",
                sLengthMenu: "Display _MENU_ Row",
                sZeroRecords: "No Data Found",
                sInfo: "Display _START_ To _END_ From _TOTAL_ Records",
                sInfoEmpty: "Display 0 To 0 From 0 Records",
                sInfoFiltered: "(Filtered From _MAX_ Total Records)",
                sSearch: "Search:",
                oPaginate: {
                    sFirst: "First",
                    sPrevious: "Previous",
                    sNext: "Next",
                    sLast: "Last"
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
                    name: 'action'
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
            if (!confirm("Confirm Save ?")) return;
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
            if (!confirm("Confirm Delete ?")) return;

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
