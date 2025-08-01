<script>
    $(document).ready(function() {

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

        $(".export_button").click(function() {
            var len = $('input[name="table_records[]"]:checked').length;
            if (len > 0) {
                if (confirm("Click OK to Export?")) {
                    $('form#delete_all').attr('action',
                        '{{ route('contact.export') }}'); //this fails silently
                    $('form#delete_all').submit();
                }
            } else {
                alert("Please Select Record For Export");
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

        var startDate;
        var endDate;

        function datesearch() {
            var currentDate = moment();
            // Set the start date to 7 days before today
            startDate = moment(currentDate).subtract(365, 'days').format('YYYY-MM-DD');
            // Set the end date to the end of the current month
            endDate = moment(currentDate).endOf('year').format('YYYY-MM-DD');
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
                showDropdowns: true,
                linkedCalendars: false,
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
            yearRange: '1980:2050',
            autoclose: true
        });



        //$.noConflict();
        var token = ''
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

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
                    data: 'nickname',
                    name: 'nickname'
                },
                {
                    data: 'start_date',
                    name: 'start_date'
                },
                {
                    data: 'end_date',
                    name: 'end_date'
                },
                {
                    data: 'level_name',
                    name: 'level_name'
                },
                {
                    data: 'bookuse_name',
                    name: 'bookuse_name'
                },
                /*  {
                     data: 'start_term_name',
                     name: 'start_term_name'
                 }, */
                {
                    data: 'term_name',
                    name: 'term_name'
                },
                {
                    data: 'telephone',
                    name: 'telephone'
                },
                {
                    data: 'father_mobile',
                    name: 'father_mobile'
                },
                {
                    data: 'mother_mobile',
                    name: 'mother_mobile'
                },
                {
                    data: 'father_email',
                    name: 'father_email'
                },
                {
                    data: 'mother_email',
                    name: 'mother_email'
                },
                {
                    data: 'father_name',
                    name: 'father_name'
                },
                {
                    data: 'mother_name',
                    name: 'mother_name'
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
        var table = $('#Listview1').DataTable(table_option);

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
            var startDate = moment(currentDate).subtract(365, 'days').format('YYYY-MM-DD');
            var endDate = moment(currentDate).endOf('year').format('YYYY-MM-DD');

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
            if (centre.length !== 0) {
                $.ajax({
                    method: "GET",
                    url: "contacts/running/" + centre,
                    async: false,
                    success: function(res) {
                        //console.log(res)
                        //$('#AddCode').prop('readonly', false);
                        $('#AddCode').val(res.running);
                    }
                });
            }
        })


        $(document).on('click', '#CreateDemoButton', async function(e) {
            e.preventDefault();
            $('.alert-danger').html('');
            $('.alert-danger').hide();
            $('.alert-success').html('');
            $('.alert-success').hide();

            $('#custom-tabs-one-home-tab').tab('show');
            //$('#AddSTerm').val(null).trigger("change");
            var addCentre = $('#AddCentre').val();
            var url = '{{ route('demo.code') }}';

            await $.ajax({
                method: "GET",
                url: url,
                success: function(res) {
                    console.log(res);
                    $('#AddDemoCode').val(res.demoCode);
                    $('#AddDemoSchool').val('eiMaths');
                    $('#AddDemoName').val(res.demoCode);
                    $('#AddDemoNickname').val(res.demoCode);

                }
            });
            $('#CreateDemoModal').modal('show');
        });

        $('#SubmitDemoCreateForm').click(function(e) {
            e.preventDefault();
            $('.alert-danger').html('');
            $('.alert-danger').hide();
            $('.alert-success').html('');
            $('.alert-success').hide();

            var url = "{{ route('demo.store') }}";
            $.ajax({
                url: url,
                method: 'post',
                data: {
                    code: $('#AddDemoCode').val(),
                    school: $('#AddDemoSchool').val(),
                    name: $('#AddDemoName').val(),
                    nickname: $('#AddDemoNickname').val(),
                    password: $('#AddDemoPassword').val(),
                    password_confirmation: $('#AddDemoConfPassword').val(),
                    _token: token,
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
                        toastr.success(result.success, {
                            timeOut: 5000
                        });
                        $('#Listview1').DataTable().ajax.reload();

                        $('.form').trigger('reset');
                        $('#CreateDemoModal').modal('hide');

                        // showOverlay();

                    }
                }
            });
        });

        $(document).on('click', '#CreateButton', async function(e) {
            e.preventDefault();
            $('.alert-danger').html('');
            $('.alert-danger').hide();
            $('.alert-success').html('');
            $('.alert-success').hide();

            $('#custom-tabs-one-home-tab').tab('show');
            //$('#AddSTerm').val(null).trigger("change");
            $('#AddBook').val(null).trigger("change");
            $('#AddLevel').val(null).trigger("change");
            $('#AddTerm').val(null).trigger("change");
            $('#AddBook2').val(null).trigger("change");
            $('#AddLevel2').val(null).trigger("change")
            $('#AddTerm2').val(null).trigger("change")

            var addCentre = $('#AddCentre').val();

            await $.ajax({
                method: "GET",
                url: "contacts/running/" + addCentre,
                success: function(res) {
                    //console.log(res)
                    $('#AddCode').val(res.running);
                    $('#AddTerm').prop('disabled', true);
                    $('#AddBook').prop('disabled', true);
                    $('#AddTerm2').prop('disabled', true);
                    $('#AddBook2').prop('disabled', true);
                }
            });
            $('#CreateModal').modal('show');
        });


        $(document).on('click', '#ReceiptButton', async function(e) {
            e.preventDefault();
            $('.alert-danger').html('');
            $('.alert-danger').hide();
            $('.alert-success').html('');
            $('.alert-success').hide();

            $('#custom-tabs-one-home-tab-receipt').tab('show');
            //$('#AddSTerm').val(null).trigger("change");
            $('#AddBook').val(null).trigger("change");
            $('#AddLevel').val(null).trigger("change");
            $('#AddTerm').val(null).trigger("change");
            $('#AddBook2').val(null).trigger("change");
            $('#AddLevel2').val(null).trigger("change")
            $('#AddTerm2').val(null).trigger("change");

            $('#ReceiptModal').modal('show');
        });



        // Create product Ajax request.
        $('#SubmitCreateForm').click(function(e) {
            e.preventDefault();
            $('.alert-danger').html('');
            $('.alert-danger').hide();
            $('.alert-success').html('');
            $('.alert-success').hide();


            var selectedValue1 = $('#AddLevel').val()[0];
            var selectedValue2 = $('#AddLevel2').val()[0];

            if (selectedValue1 && selectedValue1.trim() !== '') {
                $("#AddLevel").removeClass("is-invalid");
                $("#AddLevel").addClass("is-valid");
            } else {
                $("#AddLevel").addClass("is-invalid");
                $("#AddLevel").removeClass("is-valid");
                toastr.error('Please Select Start Level', {
                    timeOut: 5000
                });
                return false;
            }

            if (selectedValue2 < selectedValue1) {
                toastr.error("Cannot select 'To level' less than 'Start level'", {
                    timeOut: 5000
                });
                return false;
            }

            $.ajax({
                url: "{{ route('contacts.store') }}",
                method: 'post',
                data: {
                    centre: $('#AddCentre').val()[0],
                    code: $('#AddCode').val(),
                    name: $('#AddName').val(),
                    nickname: $('#AddNickname').val(),
                    gender: $("input[name='gender']:checked").val(),
                    birth_date: $('#AddBirthDate').val(),
                    start_date: $('#AddDate').val(),
                    //start_term: $('#AddSTerm').val()[0],
                    school: $('#AddSchool').val(),
                    level: $('#AddLevel').val()[0],
                    term: $('#AddTerm').val()[0],
                    bookuse: $('#AddBook').val()[0],
                    level2: $('#AddLevel2').val()[0],
                    term2: $('#AddTerm2').val()[0],
                    bookuse2: $('#AddBook2').val()[0],
                    discontinued: 0,
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

                        showOverlay();

                        var oid = result.oid;
                        var url = "{{ route('receipts_pending') }}?new=" +
                            encodeURIComponent(oid);
                        window.location.href = url;
                    }
                }
            });
        });

        let id;
        $(document).on('click', '.btn-show', function(e) {
            e.preventDefault();


            $('.alert-danger').html('');
            $('.alert-danger').hide();
            $('.alert-success').html('');
            $('.alert-success').hide();

            $('#custom-tabs-one-homee-tab').tab('show');
            id = $(this).data('id');


            $.ajax({
                url: "contacts/course/" + id,
                method: 'GET',
                success: function(res) {

                    console.log(res);
                    $('#CourseTable').html(res);
                    $('#CourseModal').modal('show');
                }
            });

        })

        $(document).on('click', '#getEditData', function(e) {
            e.preventDefault();


            $('.alert-danger').html('');
            $('.alert-danger').hide();
            $('.alert-success').html('');
            $('.alert-success').hide();

            $('#custom-tabs-one-homee-tab').tab('show');
            id = $(this).data('id');
            console.log(id);

            $.ajax({
                url: "contacts/edit/" + id,
                method: 'GET',
                success: function(res) {
                    $('#EditCentre').val(res.data.centre).change();
                    $('#EditName').val(res.data.name);
                    $('#EditNickname').val(res.data.nickname);
                    $('#EditCode').val(res.data.code);
                    $('#EditDate').val(res.data.start_date);
                    $('#EditBirthDate').val(res.data.birth_date);
                    $('#EditSchool').val(res.data.school);
                    //$('#EditSTerm').val(res.data.start_term).trigger('change.select2');
                    $('#EditLevel').val(res.datas[0].level_id).trigger('change.select2');
                    $('#EditTerm').val(res.datas[0].term_id).trigger('change.select2');
                    $('#EditBook').val(res.datas[0].product_id).change();

                    if (res.data.order_status == 1) {
                        //$('#EditLevel').prop('disabled', true);
                        //$('#EditLevel2').prop('disabled', true);
                    }

                    if (res.datas[1] && res.datas[1].hasOwnProperty('level_id') && res
                        .datas[1].level_id !== '') {
                        $('#EditLevel2').val(res.datas[1].level_id).trigger(
                            'change.select2');
                        $('#EditTerm2').val(res.datas[1].term_id).trigger('change.select2');
                        $('#EditBook2').val(res.datas[1].product_id).change();
                    }

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

                    if (res.data.gender == 1) {
                        $("#eradioSuccess1").prop("checked", true);
                    } else if (res.data.gender == 2) {
                        $("#eradioSuccess2").prop("checked", true);
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

                    $('#EditTerm').prop('disabled', true);
                    $('#EditBook').prop('disabled', true);
                    $('#EditTerm2').prop('disabled', true);
                    $('#EditBook2').prop('disabled', true);

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

            var selectedValue1 = $('#EditLevel').val()[0];
            var selectedValue2 = $('#EditLevel2').val()[0];

            if (selectedValue1 && selectedValue1.trim() !== '') {
                $("#EditLevel").removeClass("is-invalid");
                $("#EditLevel").addClass("is-valid");
            } else {
                $("#EditLevel").addClass("is-invalid");
                $("#EditLevel").removeClass("is-valid");
                toastr.error('Please Select Start Level', {
                    timeOut: 5000
                });
                return false;
            }

            if (selectedValue2 < selectedValue1) {
                toastr.error("Cannot select 'To level' less than 'Start level'", {
                    timeOut: 5000
                });
                return false;
            }


            $.ajax({
                url: "contacts/save/" + id,
                method: 'PUT',
                data: {
                    centre: $('#EditCentre').val()[0],
                    code: $('#EditCode').val(),
                    name: $('#EditName').val(),
                    nickname: $('#EditNickname').val(),
                    gender: $("input[name='egender']:checked").val(),
                    birth_date: $('#EditBirthDate').val(),
                    start_date: $('#EditDate').val(),
                    //start_term: $('#EditSTerm').val()[0],
                    school: $('#EditSchool').val(),
                    level: $('#EditLevel').val()[0],
                    term: $('#EditTerm').val()[0],
                    bookuse: $('#EditBook').val()[0],
                    level2: $('#EditLevel2').val()[0],
                    term2: $('#EditTerm2').val()[0],
                    bookuse2: $('#EditBook2').val()[0],
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
                        var ostatus = result.discontinued;
                        if (ostatus == 1) {
                            var url = "{{ route('discontinued') }}";
                            window.location.href = url;
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
                }
            });
        });


        // Create product Ajax request.
        $('#ReceiptCreateForm').click(function(e) {
            e.preventDefault();
            $('.alert-danger').html('');
            $('.alert-danger').hide();
            $('.alert-success').html('');
            $('.alert-success').hide();


            var selectedValue1 = $('#ReceiptLevel').val()[0];
            var selectedValue2 = $('#ReceiptLevel2').val()[0];

            if (selectedValue1 && selectedValue1.trim() !== '') {
                $("#ReceiptLevel").removeClass("is-invalid");
                $("#ReceiptLevel").addClass("is-valid");
            } else {
                $("#ReceiptLevel").addClass("is-invalid");
                $("#ReceiptLevel").removeClass("is-valid");
                toastr.error('Please Select Start Level', {
                    timeOut: 5000
                });
                return false;
            }

            if (selectedValue2 < selectedValue1) {
                toastr.error("Cannot select 'To level' less than 'Start level'", {
                    timeOut: 5000
                });
                return false;
            }

            $.ajax({
                url: "{{ route('contacts.receipt') }}",
                method: 'post',
                data: {
                    centre: $('#ReceiptCentre').val()[0],
                    student: $('#s_student').val()[0],
                    level: $('#ReceiptLevel').val()[0],
                    term: $('#ReceiptTerm').val()[0],
                    bookuse: $('#ReceiptBook').val()[0],
                    level2: $('#ReceiptLevel2').val()[0],
                    term2: $('#ReceiptTerm2').val()[0],
                    bookuse2: $('#ReceiptBook2').val()[0],
                    _token: token,
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
                        toastr.success(result.success, {
                            timeOut: 5000
                        });
                        $('#Listview').DataTable().ajax.reload();
                        $('#ReceiptBook').val(null).trigger("change");
                        $('#ReceiptLevel').val(null).trigger("change")
                        $('#ReceiptTerm').val(null).trigger("change")
                        $('.form').trigger('reset');
                        $('#ReceiptModal').modal('hide');

                        showOverlay();

                        var oid = result.oid;
                        var url = "{{ route('receipts_pending') }}?new=" +
                            encodeURIComponent(oid);
                        window.location.href = url;
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
