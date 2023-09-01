<script>
    $(document).ready(function() {
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

        const discontinuedCheckbox = $('#ecustomCheckbox1');
        const editDDateInput = $('#EditDDate');
        const editReasonInput = $('#EditReason');
        const sreasonSection = $('#sreason');

        // Add an event listener to the checkbox using jQuery
        discontinuedCheckbox.on('change', function() {
            // Enable/disable the inputs based on the checkbox state
            editDDateInput.prop('disabled', !this.checked);
            editReasonInput.prop('disabled', !this.checked);
            if (this.checked) {
                sreasonSection.removeClass('d-none');
                if (editDDateInput.val() === "" || editDDateInput.val() ===
                    null) {
                    const currentDate = new Date().toISOString().split('T')[0];
                    $('#EditDDate').val(currentDate);
                }
            } else {
                sreasonSection.addClass('d-none');
                $('#EditDDate').val('');
                $('#EditReason').val('');
            }
        });

        $('#ddl_discontinueReason').on('change', function() {
            const selectedReason = $(this).val();
            $('#EditReason').val(selectedReason);
        });

        $(".select2_single").select2({
            maximumSelectionLength: 1,
            allowClear: false,
            //theme: 'bootstrap4'
            placeholder: 'Please select'
        });

        $(".select2_single").on("select2:unselect", function(e) {
            //$('.products').html('');
        });

        $(".select2_singles").select2({
            maximumSelectionLength: 1,
            allowClear: false,
            //theme: 'bootstrap4'
            placeholder: 'Please select'
        });

        $(".select2_singlec").select2({
            maximumSelectionLength: 1,
            allowClear: false,
            //theme: 'bootstrap4'
            placeholder: 'Please select'
        });

        $(".select2_multiple").select2({
            maximumSelectionLength: 2,
            allowClear: false,
            //theme: 'bootstrap4'
            placeholder: 'Please select'
        });

        $(".AddDate").datepicker({
            dateFormat: "yy-mm-dd"
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
                "sProcessing": "Processcing...",
                "sLengthMenu": "Display_MENU_ Row",
                "sZeroRecords": "No Data Found",
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
            fixedHeader: true,
            responsive: {
                details: {
                    type: 'column',
                    target: 'tr'
                }
            },
            columnDefs: [{
                className: 'control',
                orderable: false,
                targets: -1
            }],
            sPaginationType: "full_numbers",
            dom: 'T<"clear">lfrtip',
            columns: [{
                    data: 'checkbox',
                    name: 'checkbox',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'centre_name',
                    name: 'centre_name'
                },
                {
                    data: 'code',
                    name: 'code'
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'start_date',
                    name: 'start_date'
                },
                {
                    data: 'level_name',
                    name: 'level_name'
                },
                {
                    data: 'book_name',
                    name: 'level_name'
                },
                {
                    data: 'start_term_name',
                    name: 'start_term_name'
                },
                {
                    data: 'term_name',
                    name: 'term_name'
                },
                {
                    data: 'telephone',
                    name: 'telephone'
                },
                {
                    data: 'action',
                    name: 'action'
                },
                {
                    data: 'more',
                    name: 'more'
                }
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

            $('#custom-tabs-one-home-tab').tab('show');
            $('#AddSTerm').val(null).trigger("change");
            $('#AddBook').val(null).trigger("change");
            $('#AddLevel').val(null).trigger("change")
            $('#AddTerm').val(null).trigger("change")

            $.ajax({
                method: "GET",
                url: "{{ route('contacts.running') }}",
                success: function(res) {
                    console.log(res)
                    $('#AddCode').val(res.running);
                }
            });
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
                url: "{{ route('contacts.store') }}",
                method: 'post',
                data: {
                    centre: $('#AddCentre').val()[0],
                    code: $('#AddCode').val(),
                    name: $('#AddName').val(),
                    start_date: $('#AddDate').val(),
                    start_term: $('#AddSTerm').val()[0],
                    school: $('#AddSchool').val(),
                    level: $('#AddLevel').val()[0],
                    term: $('#AddTerm').val()[0],
                    bookuse: $('#AddBook').val()[0],
                    //discontinued: $('#customCheckbox1').is(':checked') ? 1 : 0,
                    //ddate: $('#AddDDate').val(),
                    //reason: $('#AddReason').val(),
                    postcode: $('#AddPostcode').val(),
                    address: $('#AddAddress').val(),
                    telephone: $('#AddTelephone').val(),
                    father_name: $('#AddfName').val(),
                    father_email: $('#AddfEmail').val(),
                    father_mobile: $('#AddfTelephone').val(),
                    mother_name: $('#AddmName').val(),
                    mother_email: $('#AddmEmail').val(),
                    mother_mobile: $('#AddmTelephone').val(),
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
                        $('#AddSTerm').val(null).trigger("change");
                        $('#AddBook').val(null).trigger("change");
                        $('#AddLevel').val(null).trigger("change")
                        $('#AddTerm').val(null).trigger("change")
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

            $('#custom-tabs-one-homee-tab').tab('show');

            id = $(this).data('id');
            $.ajax({
                url: "contacts/edit/" + id,
                method: 'GET',
                success: function(res) {
                    $('#EditCentre').val(res.data.centre).change();
                    $('#EditName').val(res.data.name);
                    $('#EditCode').val(res.data.code);
                    $('#EditDate').val(res.data.start_date);
                    $('#EditSTerm').val(res.data.start_term).change();
                    $('#EditLevel').val(res.data.level).change();
                    $('#EditSchool').val(res.data.school);
                    $('#EditTerm').val(res.data.term).change();
                    $('#EditBook').val(res.data.bookuse).change();

                    if (res.data.discontinued == 1) {
                        $('#ecustomCheckbox1').prop('checked', true);
                        $('#EditDDate').prop('disabled', false);
                        $('#EditReason').prop('disabled', false);
                        $('#sreason').removeClass('d-none');
                        $('#EditDDate').val(res.data.discontinued_date);
                    } else {
                        $('#ecustomCheckbox1').prop('checked', false);
                        $('#EditDDate').prop('disabled', true);
                        $('#EditReason').prop('disabled', true);
                        $('#sreason').addClass('d-none');
                        $('#EditDDate').val('');
                    }



                    $('#EditReason').val(res.data.discontinued_reason);

                    $('#EditPostcode').val(res.data.postcode);
                    $('#EditAddress').val(res.data.address);
                    $('#EditTelephone').val(res.data.telephone);

                    $('#EditfName').val(res.data.father_name);
                    $('#EditfEmail').val(res.data.father_email);
                    $('#EditfTelephone').val(res.data.father_mobile);
                    $('#EditmName').val(res.data.mother_name);
                    $('#EditmEmail').val(res.data.mother_email);
                    $('#EditmTelephone').val(res.data.mother_mobile);

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


            $.ajax({
                url: "contacts/save/" + id,
                method: 'PUT',
                data: {
                    centre: $('#EditCentre').val()[0],
                    code: $('#EditCode').val(),
                    name: $('#EditName').val(),
                    start_date: $('#EditDate').val(),
                    start_term: $('#EditSTerm').val()[0],
                    school: $('#EditSchool').val(),
                    level: $('#EditLevel').val()[0],
                    term: $('#EditTerm').val()[0],
                    bookuse: $('#EditBook').val()[0],
                    discontinued: $('#ecustomCheckbox1').is(':checked') ? 1 : 0,
                    discontinued_date: $('#EditDDate').val(),
                    discontinued_reason: $('#EditReason').val(),
                    postcode: $('#EditPostcode').val(),
                    address: $('#EditAddress').val(),
                    telephone: $('#EditTelephone').val(),
                    father_name: $('#EditfName').val(),
                    father_email: $('#EditfEmail').val(),
                    father_mobile: $('#EditfTelephone').val(),
                    mother_name: $('#EditmName').val(),
                    mother_email: $('#EditmEmail').val(),
                    mother_mobile: $('#EditmTelephone').val(),
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
                //type: "POST",
                method: 'DELETE',
                dataType: 'JSON',
                url: "contacts/destroy/",
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
