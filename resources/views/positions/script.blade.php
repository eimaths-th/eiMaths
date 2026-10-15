<script>
    $(document).ready(function() {
        const centre_management_ui = @json(__('centre-management'));

        $(".delete_all_button").click(function() {
            var len = $('input[name="table_records[]"]:checked').length;
            if (len > 0) {
                if (confirm(centre_management_ui.messages.confirm_delete)) {
                    $('form#delete_all').submit();
                }
            } else {
                alert(centre_management_ui.messages.select_to_delete);
            }
        });

        $('#check-all').click(function() {
            $(':checkbox.flat').prop('checked', this.checked);
        });

        $(".select2_single").select2({
            maximumSelectionLength: 1,
            allowClear: false,
            //theme: 'bootstrap4'
            placeholder: centre_management_ui.messages.select_centre
        });

        $(".select2_single").on("select2:unselect", function(e) {
            //$('.products').html('');
        });

        $(".select2_singles").select2({
            maximumSelectionLength: 1,
            allowClear: false,
            //theme: 'bootstrap4'
            placeholder: centre_management_ui.messages.select_centre
        });

        $(".select2_multiple").select2({
            maximumSelectionLength: 2,
            allowClear: false,
            //theme: 'bootstrap4'
            placeholder: centre_management_ui.messages.select_centre
        });

        //$.noConflict();
        var token = ''
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });


        var table = $('#Listview').DataTable({
            /*"aoColumnDefs": [
            {
            'bSortable': true,
            'aTargets': [0]
            } //disables sorting for column one
            ],
            "searching": false,
            "lengthChange": false,
            "paging": false,
            'iDisplayLength': 10,
            "sPaginationType": "full_numbers",
            "dom": 'T<"clear">lfrtip',
                */
            ajax: '',
            serverSide: true,
            processing: true,
            language: {
                loadingRecords: '&nbsp;',
                processing: `<div class="spinner-border text-primary"></div>`,
                "sProcessing": centre_management_ui.data_table.processing,
                "sLengthMenu": centre_management_ui.data_table.length_menu,
                "sZeroRecords": centre_management_ui.data_table.zero_records,
                "sInfo": centre_management_ui.data_table.info,
                "sInfoEmpty": centre_management_ui.data_table.info_empty,
                "sInfoFiltered": centre_management_ui.data_table.info_filtered,
                "sInfoPostFix": "",
                "sSearch": centre_management_ui.data_table.search,
                "sUrl": "",
                "oPaginate": {
                    "sFirst": centre_management_ui.data_table.first,
                    "sPrevious": centre_management_ui.data_table.previous,
                    "sNext": centre_management_ui.data_table.next,
                    "sLast": centre_management_ui.data_table.last
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
                    data: 'dname',
                    name: 'dname'
                },
                {
                    data: 'status',
                    name: 'status'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
            ]
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

            if ($('#customCheckbox1').is(":checked")) {
                sstatus = 1;
            } else {
                sstatus = 0;
            }


            $.ajax({
                url: "{{ route('positions.store') }}",
                method: 'post',
                data: {
                    name: $('#AddName').val(),
                    department_id: $('#AddDepartment').val()[0],
                    status: sstatus,
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
                        $("#AddDepartment").val(null).trigger("change")
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

            id = $(this).data('id');
            $.ajax({
                url: "positions/edit/" + id,
                method: 'GET',
                success: function(res) {
                    $('#EditName').val(res.data.name);
                    $('#EditDepartment').val(res.data.department_id).change();
                    if (res.data.status == 1) {
                        $('#ecustomCheckbox1').prop('checked', true);
                    } else {
                        $('#ecustomCheckbox1').prop('checked', false);
                    }

                    $('#EditModal').modal('show');
                }
            });

        })

        $('#SubmitEditForm').click(function(e) {
            if (!confirm(centre_management_ui.messages.confirm_update)) return;
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
                url: "positions/save/" + id,
                method: 'PUT',
                data: {
                    name: $('#EditName').val(),
                    department: $('#EditDepartment').val()[0],
                    status: esstatus,
                },

                success: function(result) {
                    console.log(result);
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
            }).fail(function(xhr) {
                const payload = xhr.responseJSON || {};
                const errors = payload.errors;
                const message = Array.isArray(errors) ? errors.join('\n') :
                    (errors && typeof errors === 'object' ? Object.values(errors).flat().join('\n') :
                    (payload.message || 'บันทึกไม่สำเร็จ กรุณาลองอีกครั้ง'));
                toastr.error(message, { timeOut: 7000 });
            });
        });

        $(document).on('click', '.btn-delete', function() {
            if (!confirm(centre_management_ui.messages.confirm_delete)) return;

            var rowid = $(this).data('rowid')
            var el = $(this)
            if (!rowid) return;


            $.ajax({
                //type: "POST",
                method: 'DELETE',
                dataType: 'JSON',
                url: "positions/destroy/",
                data: {
                    id: rowid,
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
                    }
                }
            }); //end ajax
        })


    });
</script>
