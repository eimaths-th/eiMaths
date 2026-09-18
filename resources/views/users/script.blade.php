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

        $(".select2_single").select2({
            maximumSelectionLength: 1,
            allowClear: false,
            //theme: 'bootstrap4'
            placeholder: user_access_ui.users.modal.select
        });

        $(".select2_single").on("select2:unselect", function(e) {
            //log("select2:unselect", e);
            //$('.positions').html('');
        });

        $(".select2_singles").select2({
            maximumSelectionLength: 1,
            allowClear: false,
            //theme: 'bootstrap4'
            placeholder: user_access_ui.users.modal.select
        });


        $(".select2_multiple").select2({
            maximumSelectionLength: 2,
            //placeholder: "With Max Selection limit 4",
            allowClear: false,
            //theme: 'bootstrap4'
            placeholder: user_access_ui.users.modal.select
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


        const dashboard_filter_params = new URLSearchParams(window.location.search);
        const dashboard_years = dashboard_filter_params.getAll('years[]');
        const dashboard_date_range = dashboard_filter_params.get('date_range');
        const dashboard_centre = dashboard_filter_params.get('centre');

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
            ajax: {
                data: function(d) {
                    d.centre = dashboard_centre;
                    d.date_range = dashboard_date_range;
                    d.years = dashboard_years;
                }
            },
            serverSide: true,
            processing: true,
            language: {
                loadingRecords: '&nbsp;',
                processing: `<div class="spinner-border text-primary"></div>`,
                "sProcessing": user_access_ui.data_table.processing,
                "sLengthMenu": user_access_ui.data_table.length_menu,
                "sZeroRecords": user_access_ui.data_table.zero_records,
                "sInfo": user_access_ui.data_table.info,
                "sInfoEmpty": user_access_ui.data_table.info_empty,
                "sInfoFiltered": user_access_ui.data_table.info_filtered,
                "sInfoPostFix": "",
                "sSearch": user_access_ui.data_table.search,
                "sUrl": "",
                "oPaginate": {
                    "sFirst": user_access_ui.data_table.first,
                    "sPrevious": user_access_ui.data_table.previous,
                    "sNext": user_access_ui.data_table.next,
                    "sLast": user_access_ui.data_table.last
                }
            },
            // Name is the first meaningful sortable field on both user tables.
            order: [[{{ !empty($showReferralColumn) ? 2 : 1 }}, "asc"]],
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
                @if (!empty($showReferralColumn))
                    {
                        data: 'referral',
                        name: 'users.referral'
                    },
                @endif
                /*     {
                        data: 'id',
                        name: 'id'
                    }, */
                {
                    data: 'name',
                    name: 'users.name'
                },
                {
                    data: 'email',
                    name: 'users.email'
                },
                {
                    data: 'department',
                    name: 'department'
                },
                {
                    data: 'position',
                    name: 'position'
                },
                {
                    data: 'role',
                    name: 'role'
                },
                {
                    data: 'disable',
                    name: 'disable'
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
                url: "{{ route('users.store') }}",
                method: 'post',
                data: {
                    password: $('#AddPassword').val(),
                    password_confirmation: $('#AddPasswordc').val(),
                    name: $('#AddName').val(),
                    email: $('#AddEmail').val(),
                    department_id: $("#AddDepartment").val()[0],
                    position_id: $("#AddPosition").val()[0],
                    role: $('#AddRole').val(),
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
                        $("#AddPosition").val(null).trigger("change")
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

            $('#EditPosition').empty();

            id = $(this).data('id');
            url = "{{ route('users.edit', ':id') }}";
            url = url.replace(':id', id);
            console.log(url);
            $.ajax({
                url: url,
                method: 'GET',
                success: function(res) {
                    // console.log(res)
                    if (res.data.disable == 1) {
                        $('#ecustomCheckbox1').prop('checked', true);
                    } else {
                        $('#ecustomCheckbox1').prop('checked', false);
                    }
                    //$('#EditModalBody').html(res.html);
                    $('#editName').val(res.data.name);
                    $('#editEmail').val(res.data.email);
                    $('#EditDepartment').val(res.data.department_id).change();
                    $('#EditPosition').append(res.select_list_position);
                    $('#EditPosition').val(res.data.position_id).change();
                    $('#editRole').html(res.html);
                    $('#EditModal').modal('show');
                }
            });

        })

        $('#SubmitEditForm').click(function(e) {
            if (!confirm(user_access_ui.messages.confirm_save)) return;
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
            url = "{{ route('users.save', ':id') }}";
            url = url.replace(':id', id);

            $.ajax({
                url: url,
                method: 'PUT',
                data: {
                    name: $('#editName').val(),
                    password: $('#EditPassword').val(),
                    password_confirmation: $('#EditPasswordC').val(),
                    email: $('#editEmail').val(),
                    role: $('#editRole').val(),
                    department: $("#EditDepartment").val()[0],
                    position: $("#EditPosition").val()[0],
                    disable: esstatus

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
                url: "users/destroy/",
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
                    } else {
                        toastr.error(data.errors, {
                            timeOut: 5000
                        });
                    }
                }
            }); //end ajax
        });

        $(document).on('click', '.btn-delete-affiliate-user', function() {
            if (!confirm(user_access_ui.messages.confirm_delete)) return;

            var rowid = $(this).data('rowid')
            var el = $(this)
            if (!rowid) return;


            $.ajax({
                //type: "POST",
                method: 'DELETE',
                dataType: 'JSON',
                url: "/users/admin/affiliate/destroy",
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
                    } else {
                        toastr.error(data.errors, {
                            timeOut: 5000
                        });
                    }
                }
            }); //end ajax
        });

    });
</script>
