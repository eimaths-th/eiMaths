<script>
    $(document).ready(function() {

        $("#timepicker").timepicker();
        $(".timepicker").timepicker({
            timeFormat: 'hh:mm tt', // Display format
            // dateFormat: 'yy-mm-dd', // Date format
            showButtonPanel: true, // Show the "Now" and "Done" buttons
            controlType: 'select'
        });

        $('#datetimepicker').timepicker({
            timeFormat: 'hh:mm tt', // Display format
            // dateFormat: 'yy-mm-dd', // Date format
            showButtonPanel: true, // Show the "Now" and "Done" buttons
            controlType: 'select' // Use select menus for hours and minutes
        });

        function showOverlay() {
            $('#overlay').css({
                'display': 'flex',
                'justify-content': 'center', // Center horizontally
                'align-items': 'center' // Center vertically
            });
        }

        function hideOverlay() {
            $('#overlay').css('display', 'none');
        }

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
        //

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
        $(".select3_single").select2({
            maximumSelectionLength: 1,
            allowClear: false,
            //theme: 'bootstrap4'
            placeholder: 'select student'
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
            // maximumSelectionLength: 2,
            allowClear: false,
            //theme: 'bootstrap4'
            placeholder: 'Please select'
        });

        var startDate;
        var endDate;

        function datesearch() {
            var currentDate = moment();
            // Set the start date to 7 days before today
            startDate = moment(currentDate).subtract(7, 'days').format('YYYY-MM-DD');
            // Set the end date to the end of the current month
            endDate = moment(currentDate).endOf('month').format('YYYY-MM-DD');
        }

        function retrieveFieldValues() {
            var saveddateStart = localStorage.getItem('dateStart');
            var savedSearchType = localStorage.getItem('searchType');
            var savedKeyword = localStorage.getItem('keyword');

            // Set field values from local storage
            if (saveddateStart) {
                var dateParts = saveddateStart.split(' - ');
                startDate = dateParts[0];
                endDate = dateParts[1];
            } else {
                datesearch();
            }
            if (savedSearchType) {
                $('#search_type').val(savedSearchType);
            }
            if (savedKeyword) {
                $('#keyword').val(savedKeyword);
            }
        }
        // Call the function to set initial field values on page load
        retrieveFieldValues();
        let daterange = () => {
            $('#reservation').daterangepicker({
                startDate: startDate,
                endDate: endDate,
                locale: {
                    format: 'YYYY-MM-DD'
                }
            });
            // Apply the custom date range filter on input change
            $('#reservation').on('apply.daterangepicker', function() {
                table.draw();
                storeFieldValues();
            });
        }

        daterange();

        $.datepicker.setDefaults($.datepicker.regional['en']);
        $(".AddDate").datepicker({
            /*  onSelect: function() {
                 table.draw();
             }, */
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true,
            yearRange: '1980:2050'
        });

        //$.noConflict();
        var token = ''
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(".s_student").change(function() {
            let std_id = $('#s_student').val();
            //console.log(product);
            //alert(product);
            $('#AddLevel').html('');
            $('#AddTerm').html('');
            // $('#AddBook').html('');
            if (std_id.length !== 0) {
                $.ajax({
                    method: "GET",
                    url: "student/find/add/" + std_id,
                    success: function(res) {
                        $('.level_id').html(res.html);
                        //$('.terms').removeAttr('readonly');
                        $('.level_id').prop('disabled', false);
                    }
                });
            }
        })
        $(".level_id").change(function() {
            let level = $('#AddLevel').val();
            //console.log(product);
            //alert(product);
            $('#AddTerm').html('');
            // $('#AddBook').html('');
            // console.log(level);
            if (level.length !== 0) {
                $.ajax({
                    method: "GET",
                    url: "level/find/add/" + level,
                    success: function(res) {
                        $('.terms').html(res.html);
                        //$('.terms').removeAttr('readonly');
                        $('.terms').prop('disabled', false);
                    }
                });
            }
        })
        $(".terms").change(function() {
            let level = $('#AddLevel').val();
            let term = $('#AddTerm').val();
            // $('#AddBook').html('');
            if (level.length !== 0 && term.length !== 0) {
                $.ajax({
                    method: "GET",
                    url: "term/find/add/" + level + "/" + term,
                    async: false,
                    success: function(res) {
                        //console.log(res)
                        $('.books').html(res.html);
                        $('.books').prop('disabled', false);
                    }
                });
            }
        })

        $(".elevels").change(function() {
            let level = $('#EditLevel').val();
            //console.log(product);
            //alert(product);
            $('#EditTerm').html('');
            $('#EditBook').html('');
            if (level.length !== 0) {
                $.ajax({
                    method: "GET",
                    url: "level/find/add/" + level,
                    success: function(res) {
                        $('.eterms').html(res.html);
                        //$('.terms').removeAttr('readonly');
                        $('.eterms').prop('disabled', false);
                    }
                });
            }
        })

        $(".eterms").change(function() {
            let level = $('#EditLevel').val();
            let term = $('#EditTerm').val();
            $('#EditBook').html('');
            if (level.length !== 0 && term.length !== 0) {
                $.ajax({
                    method: "GET",
                    url: "term/find/add/" + level + "/" + term,
                    async: false,
                    success: function(res) {
                        //console.log(res)
                        $('.ebooks').html(res.html);
                        $('.ebooks').prop('disabled', false);
                    }
                });
            }
        })

        let reservationChanged = false;

        $("#reservation").change(function() {
            reservationChanged = true;
            table.draw();
        });

        const table_option = {
            ajax: {
                data: function(d) {

                    if (reservationChanged && $('#reservation').val()) {
                        d.sdate = $('#reservation').val();
                    }

                }
            },

            serverSide: true,
            processing: true,
            language: {
                loadingRecords: '&nbsp;',
                processing: `<div class="spinner-border text-primary"></div>`,
                "sProcessing": "Processing...",
                "sLengthMenu": "Display _MENU_ Row",
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
            //searching: false,
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
                    data: 'centre',
                    name: 'centre'
                },
                {
                    data: 'parent_name',
                    name: 'parent_name'
                },
                {
                    data: 'parent_telp',
                    name: 'parent_telp'
                },
                {
                    data: 'relationship',
                    name: 'relationship'
                },
                {
                    data: 'student_name',
                    name: 'student_name'
                },
                {
                    data: 'emergency_contact',
                    name: 'emergency_contact'
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
        };
        var table = $('#Listview').DataTable(table_option);

        $('#SearchButtons').on('click', function() {
            var searchType = $('#search_type').val();
            var keyword = $('#keyword').val();

            if (searchType !== '' && keyword !== '') {
                // Clear the previous search and any custom filters
                table.search('').draw();
                $.fn.dataTable.ext.search.pop(); // Remove the custom date range filter

                // Apply the new search based on searchType and keyword
                if (searchType === '1') {
                    table.column(2).search(keyword).draw();
                } else if (searchType === '2') {
                    table.column(3).search(keyword).draw();
                } else if (searchType === '3') {
                    table.column(14).search(keyword).draw();
                } else if (searchType === '4') {
                    table.column(15).search(keyword).draw();
                } else if (searchType === '5') {
                    table.column(10).search(keyword).draw();
                } else if (searchType === '6') {
                    table.column(11).search(keyword).draw();
                }
            } else {
                // Handle the case where searchType or keyword is empty
                toastr.error('Please input Search Type and Keyword', {
                    timeOut: 5000
                });
            }
        });


        // Attach event handler to a button or element to trigger the reset
        $('#resetSearchButton').on('click', async function() {
            localStorage.removeItem('dateStart');
            localStorage.removeItem('searchType');
            localStorage.removeItem('keyword');

            // Set field values to empty
            $('#search_type').val('');
            $('#keyword').val('');

            $('#Listview').html('');

            // Clear DataTable state
            if (table) {
                table.state.clear();
                await table.destroy();
            }
            // Set the date range back to its default
            var currentDate = moment();
            var startDate = moment(currentDate).subtract(7, 'days').format('YYYY-MM-DD');
            var endDate = moment(currentDate).endOf('month').format('YYYY-MM-DD');
            daterange();
            table = $('#Listview').DataTable(table_option);
            table.draw();
        });



        function storeFieldValues() {
            var dateStart = $('#reservation').val();
            var searchType = $('#search_type').val();
            var keyword = $('#keyword').val();

            // Store values in local storage
            localStorage.setItem('dateStart', dateStart);
            localStorage.setItem('searchType', searchType);
            localStorage.setItem('keyword', keyword);
        }

        // Attach event handlers to the fields to store values when they change
        $('#search_type').on('change', storeFieldValues);
        $('#keyword').on('input', storeFieldValues);


        $("#AddCentre").change(function() {
            let centre = $('#AddCentre').val();
            // console.log(centre);
            if (centre.length !== 0) {
                $.ajax({
                    method: "GET",
                    url: "histories/running/" + centre,
                    async: false,
                    success: function(res) {
                        console.log(res)
                        //$('#AddCode').prop('readonly', false);
                        $('#s_student').html(res.html_std);
                    }
                });
            }
        })

        if ($("#AddCentre").val() !== null) {
            let centre = $('#AddCentre').val();
            console.log(centre);
            if (centre.length !== 0) {
                $.ajax({
                    method: "GET",
                    url: "histories/running/" + centre,
                    async: false,
                    success: function(res) {
                        console.log(res)
                        //$('#AddCode').prop('readonly', false);
                        $('#s_student').html(res.html_std);
                    }
                });
            }
        }


        $(document).on('click', '#CreateButton', async function(e) {
            e.preventDefault();
            $('.alert-danger').html('');
            $('.alert-danger').hide();
            $('.alert-success').html('');
            $('.alert-success').hide();

            $('#custom-tabs-one-home-tab').tab('show');
            $('#AddSTerm').val(null).trigger("change");
            // $('#AddBook').val(null).trigger("change");
            $('#AddLevel').val(null).trigger("change")
            $('#AddTerm').val(null).trigger("change")
            var centre = 0;
            var createUrl = "{{ route('create', ':centre') }}".replace(':centre', centre);
            $.ajax({
                url: createUrl,
                method: "GET",
                success: function(response) {
                    $('#AddCode').val(response.running);
                    $('#AddTerm').prop('disabled', true);
                    // $('#AddBook').prop('disabled', true);
                    $('#s_student').html(response.html_std);
                    console.log(response);
                },
                error: function(error) {
                    console.error(error);
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
                url: "{{ route('parent.store') }}",
                method: 'post',
                data: {
                    centre_id: $('#AddCentre').val()[0],
                    student_id: $('#s_student').val(),
                    fname: $('#AddFname').val(),
                    lname: $('#AddLname').val(),
                    telp: $('#AddTelp').val(),
                    email: $('#AddEmail').val(),
                    relationship: $('#AddRelationship').val(),
                    gender: $('input[name="gender"]:checked').val(),
                    password: $('#AddPassword').val(),
                    password_confirmation: $('#password_confirmation').val(),
                    notes: $('#AddNotes').val(),
                    address: $('#AddAddress').val(),
                    emergency_contact: $('#AddEmergency').val(),
                    // signature: $('#AddSignature').val(),
                    _token: token,
                },

                error: function(result) {
                    var dangerValidate = $('#danger_validate');
                    dangerValidate.html(''); // Clear any previous messages

                    // console.log(result);

                    if (result.responseJSON.errors) {
                        dangerValidate.show();

                        $.each(result.responseJSON.errors, function(field, messages) {
                            messages.forEach(function(message) {
                                dangerValidate.append('<strong><li>' +
                                    message + '</li></strong>');
                            });
                        });
                    } else {
                        dangerValidate.append('<strong><li>' + result.responseJSON.message +
                            '</li></strong>');
                    }
                    dangerValidate.show();
                },
                success: function(result) {
                    // console.log(result);
                    if (result.success) {

                        $('.alert-danger').hide();
                        $('.alert-success').show();
                        $('.alert-success').append('<strong><li>' + result.message +
                            '</li></strong>');
                        toastr.success(result.message, {
                            timeOut: 3000
                        });

                        // $('.form').trigger('reset');
                        $('#CreateModal').modal('hide');
                        location.reload();

                    } else {

                        $('.alert-danger').html('');
                        $('.alert-danger').show();
                        $('.alert-danger').append('<strong><li>' + result.message +
                            '</li></strong>');


                    }
                    location.reload();
                },
                // error: function(xhr, textStatus, errorThrown) {
                //     console.error(xhr.statusText);
                // }

            });
            // location.reload();

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
                // url: "histories/edit/" + id,
                url: "{{ route('parent.edit', ':id') }}".replace(':id', id),
                method: 'GET',
                success: function(res) {
                    $('#Edits_student').html(res.html_std);

                    $('#EditCentre').html(res.centre);
                    $('#EditFname').val(res.data.fname);
                    $('#EditLname').val(res.data.lname);
                    $('#EditTelp').val(res.data.telp);
                    $('#EditEmail').val(res.data.email);
                    $('#EditEmergency').val(res.data.emergency_contact);
                    $('#EditRelationship').val(res.data.relationship);
                    $('#EditAddress').val(res.data.address);
                    $('#EditNotes').val(res.data.notes);
                    // $('#EditPassword').val(res.data.password);
                    if (res.data.gender) {
                        $("input[name='gender'][value='" + res.data.gender + "']").prop(
                            'checked', true);
                    }

                    // console.log($('#getSignature').val(res.data.signature));
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
                url: "parent/update/" + id,
                // url: "{{ route('parents.update', ['id' => '+id+']) }}",

                method: 'PUT',
                data: {
                    centre_id: $('#EditCentre').val()[0],
                    student_id: $('#Edits_student').val(),
                    fname: $('#EditFname').val(),
                    lname: $('#EditLname').val(),
                    telp: $('#EditTelp').val(),
                    email: $('#EditEmail').val(),
                    address: $('#EditAddress').val(),
                    relationship: $('#EditRelationship').val(),
                    // gender: $('#getComment').val(),
                    emergency_contact: $('#EditEmergency').val(),
                    notes: $('#EditNotes').val(),
                    gender: $('input[name="gender"]:checked').val(),
                    password: $('#EditPassword').val(),
                    password_confirmation: $('#password_confirmation1').val(),
                },

                error: function(result) {
                    var dangerValidate = $('#edit_validate');
                    dangerValidate.html(''); // Clear any previous messages
                    // console.log(result);
                    if (result.responseJSON.errors) {
                        dangerValidate.show();

                        $.each(result.responseJSON.errors, function(field, messages) {
                            messages.forEach(function(message) {
                                dangerValidate.append('<strong><li>' +
                                    message + '</li></strong>');
                            });
                        });
                    } else {
                        dangerValidate.append('<strong><li>' + result.responseJSON.message +
                            '</li></strong>');
                    }
                    dangerValidate.show();
                },
                success: function(result) {
                    // console.log(result);
                    if (result.success) {

                        $('.alert-danger').hide();
                        $('.alert-success').show();
                        $('.alert-success').append('<strong><li>' + result.message +
                            '</li></strong>');
                        $('#EditModal').modal('hide');

                        toastr.success(result.message, {
                            timeOut: 3000,

                        });

                        // $('.form').trigger('reset');


                        // location.reload();

                    } else {

                        $('.alert-danger').html('');
                        $('.alert-danger').show();
                        $('.alert-danger').append('<strong><li>' + result.message +
                            '</li></strong>');


                    }

                },
            });

        });

        $(document).on('click', '.btn-delete', function() {
            if (!confirm("Confirm the action?")) return;

            var rowid = $(this).data('rowid');
            var el = $(this);
            if (!rowid) return;


            $.ajax({
                //type: "POST",
                method: 'DELETE',
                dataType: 'JSON',
                url: "/parent/" + rowid,
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
