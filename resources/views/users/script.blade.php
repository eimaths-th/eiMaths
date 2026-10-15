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


        const department_options_url = "{{ route('departments.find', ['type' => 'add', 'department' => '__centre__']) }}";
        const role_options_url = "{{ route('users.role-options') }}";
        const department_options = {
            create: { centre: '#AddDepartment', position: '#AddPosition', role: '#AddRole', roleHelp: '#AddRoleHelp', save: '#SubmitCreateForm', version: 0, roleVersion: 0, departmentBlocked: false, roleBlocked: true, roles: [], blocked: true },
            edit: { centre: '#EditDepartment', position: '#EditPosition', role: '#editRole', roleHelp: '#EditRoleHelp', save: '#SubmitEditForm', version: 0, roleVersion: 0, departmentBlocked: false, roleBlocked: true, roles: [], blocked: true }
        };

        $('#editRole').attr('aria-describedby', 'EditRoleHelp');

        function updateUserFormSaveState(form) {
            const state = department_options[form];
            const centre = $(state.centre).val()?.[0] ?? null;
            state.blocked = state.departmentBlocked || state.roleBlocked || !centre || !state.roles.includes($(state.role).val());
            $(state.save).prop('disabled', state.blocked);
        }

        function setDepartmentBlocked(form, blocked) {
            const state = department_options[form];
            state.departmentBlocked = blocked;
            $(state.position).prop('disabled', blocked);
            updateUserFormSaveState(form);
        }

        function resetRoleOptions(form, loading) {
            const state = department_options[form];
            state.roles = [];
            state.roleBlocked = loading;
            $(state.role).empty().append(new Option(user_access_ui.users.modal.select, '')).val('').prop('disabled', true);
            $(state.roleHelp).text(form === 'edit' ? user_access_ui.messages.edit_roles_hint : user_access_ui.messages.assignable_roles_hint);
            updateUserFormSaveState(form);
        }

        function finishRoleOptions(form, selected = '') {
            const state = department_options[form];
            state.roles = Array.from($(state.role).prop('options')).map(option => option.value).filter(Boolean);
            state.roleBlocked = false;
            $(state.role).val(state.roles.includes(selected) ? selected : '').prop('disabled', state.roles.length === 0);
            $(state.roleHelp).text(state.roles.length ? (form === 'edit' ? user_access_ui.messages.edit_roles_hint : user_access_ui.messages.assignable_roles_hint) : user_access_ui.messages.no_assignable_roles);
            updateUserFormSaveState(form);
        }

        function refreshRoleOptions(form) {
            const state = department_options[form];
            const version = ++state.roleVersion;
            const centre = $(state.centre).val()?.[0] ?? null;
            resetRoleOptions(form, Boolean(centre));
            if (!centre) return;
            $.ajax({
                method: 'GET', url: role_options_url, data: { department_id: centre }, cache: false,
                success: function(res) {
                    if (state.roleVersion !== version) return;
                    for (const role of Array.isArray(res.roles) ? res.roles : []) {
                        if (typeof role.value === 'string' && role.value) {
                            $(state.role).append(new Option(role.label || role.value, role.value));
                        }
                    }
                    // A fresh list must never silently choose the first available role.
                    finishRoleOptions(form);
                },
                error: function(xhr) {
                    if (state.roleVersion !== version) return;
                    $(state.roleHelp).text(user_access_ui.messages.role_load_failed);
                    showUserFormLoadError(xhr, user_access_ui.messages.role_load_failed);
                }
            });
        }

        function showUserFormLoadError(xhr, fallback) {
            // Escape server messages before passing them to the HTML-based toast.
            toastr.error($('<div>').text(xhr.responseJSON?.message || fallback).html());
        }

        function refreshDepartmentOptions(form) {
            const state = department_options[form];
            const version = ++state.version;
            const centre = $(state.centre).val()?.[0] ?? null;
            $(state.position).empty().val([]).trigger('change.select2');
            setDepartmentBlocked(form, Boolean(centre));
            if (!centre) return;

            $.ajax({
                method: 'GET',
                url: department_options_url.replace('__centre__', encodeURIComponent(centre)),
                cache: false,
                success: function(res) {
                    if (state.version !== version) return;
                    $(state.position).html(res.html).val([]).trigger('change.select2');
                    setDepartmentBlocked(form, false);
                },
                error: function(xhr) {
                    if (state.version !== version) return;
                    // Keep Save blocked until a fresh lookup or an explicit centre clear.
                    showUserFormLoadError(xhr, user_access_ui.messages.department_load_failed || 'Unable to load departments. Reopen this form or select the centre again to retry.');
                }
            });
        }

        $('#AddDepartment').on('change', function() {
            refreshDepartmentOptions('create');
            refreshRoleOptions('create');
        });
        $('#EditDepartment').on('change', function() {
            refreshDepartmentOptions('edit');
            refreshRoleOptions('edit');
        });
        $('#AddRole').on('change', function() { updateUserFormSaveState('create'); });
        $('#editRole').on('change', function() { updateUserFormSaveState('edit'); });
        resetRoleOptions('create', false);
        resetRoleOptions('edit', false);

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
            const centre = $('#AddDepartment').val() || [];
            $('#CreateModal form').trigger('reset');
            $('#AddDepartment').val(centre).trigger('change.select2');
            refreshDepartmentOptions('create');
            refreshRoleOptions('create');
            $('#CreateModal').modal('show');
        });
        // Create product Ajax request.
        $('#SubmitCreateForm').click(function(e) {
            e.preventDefault();
            updateUserFormSaveState('create');
            if (department_options.create.blocked) return;
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
                    department_id: $("#AddDepartment").val()?.[0] ?? null,
                    position_id: $("#AddPosition").val()?.[0] ?? null,
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
                        $("#AddDepartment").val([]).trigger("change")
                        $("#AddPosition").val([]).trigger("change")
                        $('#CreateModal form').trigger('reset');
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
        let edit_request_version = 0;
        $(document).on('click', '#getEditData', function(e) {
            e.preventDefault();


            $('.alert-danger').html('');
            $('.alert-danger').hide();
            $('.alert-success').html('');
            $('.alert-success').hide();

            const version = ++edit_request_version;
            ++department_options.edit.version;
            ++department_options.edit.roleVersion;
            resetRoleOptions('edit', true);
            $('#EditPosition').empty().val([]).trigger('change.select2');
            $('#EditPassword').val('');
            $('#EditPasswordC').val('');
            setDepartmentBlocked('edit', true);

            id = $(this).data('id');
            let url = "{{ route('users.edit', ':id') }}";
            url = url.replace(':id', id);
            $.ajax({
                url: url,
                method: 'GET',
                cache: false,
                success: function(res) {
                    if (version !== edit_request_version) return;
                    // console.log(res)
                    if (res.data.disable == 1) {
                        $('#ecustomCheckbox1').prop('checked', true);
                    } else {
                        $('#ecustomCheckbox1').prop('checked', false);
                    }
                    //$('#EditModalBody').html(res.html);
                    $('#editName').val(res.data.name);
                    $('#editEmail').val(res.data.email);
                    $('#EditDepartment').val(res.data.department_id).trigger('change.select2');
                    // Multiple Select2 counts a selected blank option against its one-item limit.
                    // Use an empty array for no department, never null (jQuery selects value="").
                    const position_id = res.data.position_id;
                    const selected_positions = position_id == null || position_id === '' ? [] : [String(position_id)];
                    $('#EditPosition').html(res.select_list_position).val(selected_positions).trigger('change.select2');
                    $('#editRole').html(res.html);
                    const selected_roles = Array.from($('#editRole').prop('options')).filter(option => option.value && option.hasAttribute('selected'));
                    if (!Array.from($('#editRole').prop('options')).some(option => option.value === '')) {
                        $('#editRole').prepend(new Option(user_access_ui.users.modal.select, ''));
                    }
                    finishRoleOptions('edit', selected_roles.length === 1 ? selected_roles[0].value : '');
                    setDepartmentBlocked('edit', false);
                    $('#EditModal').modal('show');
                },
                error: function(xhr) {
                    if (version !== edit_request_version) return;
                    showUserFormLoadError(xhr, user_access_ui.messages.user_load_failed || 'Unable to load this user. Please reopen the edit form.');
                }
            });

        })

        $('#SubmitEditForm').click(function(e) {
            e.preventDefault();
            updateUserFormSaveState('edit');
            if (department_options.edit.blocked) return;
            if (!confirm(user_access_ui.messages.confirm_save)) return;

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
                    department: $("#EditDepartment").val()?.[0] ?? null,
                    position: $("#EditPosition").val()?.[0] ?? null,
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
