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

        function syncPermissionGroup(group) {
            const $group = $(group);
            const $items = $group.find('.permission-item-checkbox');
            const selected = $items.filter(':checked').length;
            const total = $items.length;
            $group.find('.permission-group-toggle')
                .prop('checked', total > 0 && selected === total)
                .prop('indeterminate', selected > 0 && selected < total);
            $group.find('[data-group-selected]').text(selected);
            $items.each(function() {
                $(this).closest('.permission-option').toggleClass('is-selected', this.checked);
            });
        }

        function syncPermissionPanel(panel) {
            const $panel = $(panel);
            $panel.find('[data-permission-group]').each(function() {
                syncPermissionGroup(this);
            });
            $panel.find('[data-total-selected]').text(
                $panel.find('.permission-item-checkbox:checked').length
            );
        }

        $(document).on('change', '.permission-group-toggle', function() {
            const $group = $(this).closest('[data-permission-group]');
            $group.find('.permission-item-checkbox').prop('checked', this.checked);
            syncPermissionPanel($(this).closest('[data-permission-panel]'));
        });

        $(document).on('change', '.permission-item-checkbox', function() {
            syncPermissionPanel($(this).closest('[data-permission-panel]'));
        });

        $(document).on('click', '.permission-select-all', function() {
            const $panel = $(this).closest('[data-permission-panel]');
            $panel.find('.permission-item-checkbox').prop('checked', true);
            syncPermissionPanel($panel);
        });

        $(document).on('click', '.permission-clear-all', function() {
            const $panel = $(this).closest('[data-permission-panel]');
            $panel.find('.permission-item-checkbox').prop('checked', false);
            syncPermissionPanel($panel);
        });

        $(document).on('input', '.permission-search', function() {
            const query = this.value.trim().toLowerCase();
            const $panel = $(this).closest('[data-permission-panel]');
            let visibleOptions = 0;
            $panel.find('[data-permission-group]').each(function() {
                let visibleInGroup = 0;
                $(this).find('[data-permission-option]').each(function() {
                    const match = !query || String($(this).data('permission-search')).toLowerCase().includes(query);
                    $(this).toggle(match);
                    if (match) visibleInGroup += 1;
                });
                visibleOptions += visibleInGroup;
                $(this).toggle(visibleInGroup > 0);
            });
            $panel.find('.permission-no-results').prop('hidden', visibleOptions > 0);
        });

        $('.permission-panel').each(function() {
            syncPermissionPanel(this);
        });



        var table = $('#Listview').DataTable({
            "language": {

            },
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
                /*     {
                        data: 'id',
                        name: 'id'
                    }, */
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'role',
                    name: 'role'
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
            $('#CreateModal .form').trigger('reset');
            $('#CreatePermissionPanel .permission-search').val('').trigger('input');
            syncPermissionPanel('#CreatePermissionPanel');
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
                url: "{{ route('roles.store') }}",
                method: 'post',
                data: {
                    name: $('#AddName').val(),
                    branch_id: $('#AddRoleBranch').val(),
                    template_id: $('#AddRoleTemplate').val(),
                    permission: list,
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

        $('#AddRoleTemplate').on('change', function() {
            if (!this.value) return;
            $.get("{{ url('/roles/template') }}/" + this.value, function(res) {
                $('input[name="permission[]"]').each(function() {
                    this.checked = res.permission.map(String).indexOf(this.value) !== -1;
                });
                syncPermissionPanel('#CreatePermissionPanel');
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
                url: "roles/edit/" + id,
                method: 'GET',
                success: function(res) {
                    //console.log(res);
                    strs = Array.from(res.permission, x => `${x}`);
                    $(':checkbox[id^=ecustomCheckbox]').filter(function(index, val) {
                        if (strs.indexOf(this.value) >= 0) {
                            return this.checked = true;
                        } else {
                            return this.checked = false;
                        }

                    })
                    $('#EditPermissionPanel .permission-search').val('').trigger('input');
                    syncPermissionPanel('#EditPermissionPanel');
                    $('#editName').val(res.name);
                    //$('#EditModalBody').html(res.html);
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


            var elist = $("input[name='epermission[]']:checked").map(function() {
                return this.value;
            }).get();

            $.ajax({
                url: "roles/save/" + id,
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
