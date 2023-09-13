<script>
    $(document).ready(function() {

        $(document).on('change', '.auto_decimal', function() {
            // Parse the input value to a number
            const inputValue = parseFloat(this.value);

            // Format the number with 2 decimal places
            const formattedValue = inputValue.toFixed(2);

            // Update the input value with the formatted number
            this.value = formattedValue;
        });

        $(".delete_all_button").click(function() {
            var len = $('input[name="table_records[]"]:checked').length;
            if (len > 0) {
                if (confirm("Confirm data deletion?")) {
                    $('form#delete_all').submit();
                }
            } else {
                alert("Please select items to delete.");
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


        var table = $('#Listview').DataTable({
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
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'price',
                    name: 'price'
                },

                {
                    data: 'action',
                    name: 'action'
                },
            ]
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
                url: "{{ route('departments.store') }}",
                method: 'post',
                data: {
                    name: $('#AddName').val(),
                    code: $('#AddCode').val(),
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
                url: "parameter/edit/" + id,
                method: 'GET',
                success: function(res) {
                    $('#EditName').val(res.data.name);
                    $('#EditFee').val(res.data.price);
                    $('#EditModalBody').html(res.html);
                    $('#EditModal').modal('show');
                }
            });

        })

        $('#SubmitEditForm').click(function(e) {
            if (!confirm("Confirm the action?")) return;
            e.preventDefault();

            $('.alert-danger').html('');
            $('.alert-danger').hide();
            $('.alert-success').html('');
            $('.alert-success').hide();


           /*  if ($('#ecustomCheckbox1').is(":checked")) {
                esstatus = 1;
            } else {
                esstatus = 0;
            } */

            $.ajax({
                url: "parameter/save/" + id,
                method: 'PUT',
                data: {
                    name: $('#EditName').val(),
                    price: $('#EditFee').val(),
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
            if (!confirm("Confirm the action?")) return;

            var rowid = $(this).data('rowid')
            var el = $(this)
            if (!rowid) return;


            $.ajax({
                type: "POST",
                dataType: 'JSON',
                url: "departments/destroy/",
                data: {
                    id: rowid,
                    _method: 'delete',
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
