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
                "sProcessing": "Processcing...",
                "sLengthMenu": "Display_MENU_ Row",
                "sZeroRecords": "No Data Fount",
                "sInfo": "Display _START_ To _END_ From _TOTAL_ Records",
                "sInfoEmpty": "Display 0 To 0 From 0 Records",
                "sInfoFiltered": "(Filters _MAX_ Row)",
                "sInfoPostFix": "",
                "sSearch": "Search:",
                "sUrl": "",
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
                    name: 'action'
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
            $.ajax({
                url: "users/edit/" + id,
                method: 'GET',
                success: function(res) {
                    console.log(res)
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

        $('#update_qr_payment').click(function(e) {
            e.preventDefault();

            $('.alert-danger').html('');
            $('.alert-danger').hide();
            $('.alert-success').html('');
            $('.alert-success').hide();

            // if ($('#ecustomCheckbox1').is(":checked")) {
            //     esstatus = 1;
            // } else {
            //     esstatus = 0;
            // }
            let id = $('#qr_id').val(); // Laravel requires this for PUT requests
            $.ajax({
                url: "qrcode_payment/" + id,
                method: 'PUT',
                data: {
                    account_name: $('#account_name').val(),
                    bank_name: $('#bank_name').val(),
                    account_numbers: $('#account_numbers').val(),
                    promptpay_numbers: $('#promptpay_numbers').val(),
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
            if (!confirm("Confirm Delete Data ?")) return;

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
        })
        $(document).on('click', '.btn-delete-affiliate-user', function() {
            if (!confirm("Confirm Delete Data ?")) return;

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
        })


    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const findPeriodButton = document.getElementById('btn-find-preiod');
        const departmentSelect = document.getElementById('department');
        const daySelect = document.getElementById('day');
        const teachingPeriodTableBody = document.querySelector('#teaching_period_table');

        // Add event listener to the "Find" button
        findPeriodButton.addEventListener('click', function(event) {
            event.preventDefault(); // Prevent the form from submitting normally

            const departmentId = departmentSelect.value;
            const day = daySelect.value;

            // Check if both department and day are selected
            if (!departmentId || !day) {
                alert('Please select both department and day.');
                return;
            }

            // Send an AJAX request to the backend to find the teaching periods
            fetch(`/TeachingPeriod/find/${departmentId}/${day}`)
                .then(response => response.json()) // Parse the response as JSON
                .then(data => {
                    // Clear the current table rows
                    teachingPeriodTableBody.innerHTML = '';

                    // If no data is returned, display a message
                    if (!data.html) {
                        const noDataRow = document.createElement('tr');
                        const noDataCell = document.createElement('td');
                        noDataCell.colSpan = 4; // Span across all columns
                        noDataCell.textContent = 'No teaching periods found for this selection.';
                        noDataRow.appendChild(noDataCell);
                        teachingPeriodTableBody.appendChild(noDataRow);
                    } else {
                        // Insert the new HTML into the table body
                        teachingPeriodTableBody.innerHTML = data.html;
                    }
                })
                .catch(error => {
                    console.error('Error fetching data:', error);
                    alert('An error occurred while fetching the data.');
                });
        });
    });
</script>
