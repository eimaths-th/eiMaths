{{-- <script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script> --}}
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

        const table_option = {
            ajax: {
                data: function(d) {
                    d.sdate = $('#reservation').val();
                    //d.search = $('input[type="search"]').val();
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
                    data: 'code',
                    name: 'code'
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'level_name',
                    name: 'level_name'
                },
                {
                    data: 'term',
                    name: 'term'
                }, {
                    data: 'bookuse',
                    name: 'bookuse'
                },
                {
                    data: 'date',
                    name: 'date'
                },
                {
                    data: 'stime',
                    name: 'stime'
                }, {
                    data: 'etime',
                    name: 'etime'
                }, {
                    data: 'course_remaining',
                    name: 'course_remaining'
                },

                {
                    data: 'start_date',
                    name: 'start_date'
                },
                {
                    data: 'end_date',
                    name: 'end_date'
                },
                // {
                //     data: 'start_term_name',
                //     name: 'start_term_name'
                // },


                {
                    data: 'comment',
                    name: 'comment'
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
        $(document).on('click', '#getViewData', async function(e) {
            e.preventDefault();
            $('.alert-danger').html('');
            $('.alert-danger').hide();
            $('.alert-success').html('');
            $('.alert-success').hide();

            $('#custom-tabs-one-home-tab').tab('show');

            var hid = $(this).data('id')
            //document.getElementById('getViewData').getAttribute('data-id');
            var route = "{{ route('history.show', ['id' => ':id']) }}".replace(':id', hid);

            //console.log(hid)

            $.ajax({
                url: route,
                method: "GET",
                //data: { id: hid },
                success: function(response) {
                    console.log(response.centre);
                    console.log(response.history);
                    console.log(response.student);
                    $('#viewCentre').val(response.centre);
                    $('#viewStudent').val(response.student);
                    $('#viewLV').val(response.history.level_name);
                    $('#viewTerm').val(response.history.term);
                    $('#viewBook').val(response.history.bookuse);
                    $('#viewCourseRemaining').val(response.history.course_remaining);
                    $('#viewDate').val(response.history.date);
                    $('#ViewStartTime').val(response.history.stime);
                    $('#ViewEndTime').val(response.history.etime);
                    $('#viewStartDate').val(response.history.start_date);
                    $('#viewEndDate').val(response.history.end_date);
                    $('#hsliden').html(response.slide_html);
                    $('#hslidei').html(response.img_html);
                    //console.log(response.slide_html);
                    //console.log(response.img_html);
                    $('#ViewComment').val(response.history.comment);
                    $('#ViewSignature').attr('src', 'https://app.eimaths-th.com/file_upload/' + response.history.signature);
                    // console.log(response.code);
                },
                error: function(error) {
                    console.error(error);
                }
            });

            $('#ViewModal').modal('show');

        });



        // Create product Ajax request.
        $('#SubmitCreateForm').click(function(e) {
            e.preventDefault();
            $('.alert-danger').html('');
            $('.alert-danger').hide();
            $('.alert-success').html('');
            $('.alert-success').hide();
            var values = $("input[name='imgFiles2[]']")
                .map(function() {
                    return $(this).val();
                }).get();
            $.ajax({
                url: "{{ route('histories.store') }}",
                method: 'post',
                data: {
                    img: values,
                    centre: $('#AddCentre').val()[0],
                    student_id: $('#s_student').val()[0],
                    level_id: $('#AddLevel').val()[0],
                    term: $('#AddTerm').val()[0],
                    bookuse: $('#AddBook').val(),
                    date: $('#AddDate').val(),
                    stime: $('#AddStartTime').val(),
                    etime: $('#AddEndTime').val(),
                    comment: $('#AddComment').val(),
                    signature: $('#AddSignature').val(),
                    _token: token,
                },
                // error: function(result) {
                //     if (result.errors) {
                //         $('.alert-danger').html('');
                //         $.each(result.errors, function(key, value) {
                //             $('.alert-danger').show();
                //             $('.alert-danger').append('<strong><li>' + value +
                //                 '</li></strong>');
                //         });
                //     }
                // },
                error: function(result) {
                    $('.alert-danger').html('');
                    if (result.responseJSON.errors) {
                        $.each(result.responseJSON.errors, function(field, messages) {
                            messages.forEach(function(message) {
                                $('.alert-danger').append('<strong><li>' +
                                    message + '</li></strong>');
                            });
                        });
                    } else {
                        $('.alert-danger').append('<strong><li>' + result.responseJSON
                            .message + '</li></strong>');
                    }
                },
                success: function(result) {
                    console.log(result);
                    if (result.success) {

                        $('.alert-danger').hide();
                        $('.alert-success').show();
                        $('.alert-success').append('<strong><li>' + result.message +
                            '</li></strong>');
                        toastr.success(result.message, {
                            timeOut: 5000
                        });
                        $('#Listview').DataTable().ajax.reload();
                        $('#AddSTerm').val(null).trigger("change");
                        // $('#AddBook').val(null).trigger("change");
                        $('#AddLevel').val(null).trigger("change");
                        $('#AddTerm').val(null).trigger("change");
                        // $('.form').trigger('reset');
                        $('#CreateModal').modal('hide');

                    } else {

                        $('.alert-danger').html('');
                        $('.alert-danger').show();
                        $('.alert-danger').append('<strong><li>' + result.message +
                            '</li></strong>');


                    }
                },
                // error: function(xhr, textStatus, errorThrown) {
                //     console.error(xhr.statusText);
                // }

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
                // url: "histories/edit/" + id,
                url: "{{ route('histories.edit', ':id') }}".replace(':id', id),
                method: 'GET',
                success: function(res) {
                    $('#getCentre').val(res.data.centre).change();
                    $('#getStudent').val(res.data.code + ' ' + res.data.student_id);
                    $('#getLevel').val(res.data.level_name);
                    $('#getTerm').val(res.data.term);
                    $('#getBookuse').val(res.data.bookuse);
                    $('#getCourseRemaining').val(res.data.course_remaining);
                    $('#getDate').val(res.data.date);
                    $('#getStartTime').val(res.data.stime);
                    $('#getEndTime').val(res.data.etime);
                    $('#getStartDate').val(res.data.start_date);
                    $('#getEndDate').val(res.data.end_date);
                    $('#getComment').val(res.data.comment);
                    // $('#getSignature').val(res.data.signature);
                    $('#getSignature').attr('src', '{{ asset('file_upload/') }}/' + res.data
                        .signature);
                    console.log($('#getSignature').val(res.data.signature));
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
                url: "history/update/" + id,
                method: 'PUT',
                data: {
                    comment: $('#getComment').val(),
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

        $(document).on('click', '.btn-delete', function() {
            if (!confirm("Confirm the action?")) return;

            var rowid = $(this).data('rowid');
            var el = $(this);
            if (!rowid) return;


            $.ajax({
                //type: "POST",
                method: 'DELETE',
                dataType: 'JSON',
                url: "/histories/" + rowid,
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

    var myDropzone = {};
    Dropzone.options.myAwesomeDropzone2 = {
        url: '{{ route('history.upload') }}',
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        },
        paramName: "file", // ชื่อไฟล์ปลายทางเมื่อ upload แบบ mutiple จะเป็น array
        autoProcessQueue: true, // ใส่เพื่อไม่ให้อัพโหลดทันที หลังจากเลือกไฟล์
        uploadMultiple: true, // อัพโหลดไฟล์หลายไฟล์
        parallelUploads: 2, // ให้ทำงานพร้อมกัน 10 ไฟล์
        maxFiles: 5, // ไฟล์สูงสุด 5 ไฟล์
        maxfilesexceeded: function(file) {
            this.removeAllFiles();
            this.addFile(file);
        },
        addRemoveLinks: true, // อนุญาตให้ลบไฟล์ก่อนการอัพโหลด
        maxFilesize: 10, // MB
        renameFile: function(file) {
            let newName = new Date().getTime() + '_' + file.name;
            file.newName = newName;
            return newName;
        },
        previewsContainer: "#dropzone_preview2", // ระบุ element เป้าหลาย
        //previewTemplate: $('#template-preview').html(),
        dictRemoveFile: "Remove", // ชื่อ ปุ่ม remove
        dictCancelUpload: "Cancel", // ชื่อ ปุ่ม ยกเลิก
        dictDefaultMessage: "<img height='60' src='https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSNsug8XTE5KVJEMECVvm8p43BZTdvZExoQ9Q&usqp=CAU'><br><font size='3'>Browse File</font>", // ข้อความบนพื้นที่แสดงรูปจะแสดงหลังจากโหลดเพจเสร็จ
        dictFileTooBig: "ไม่อนุญาตให้อัพโหลดไฟล์เกิน 2 MB", //ข้อความแสดงเมื่อเลือกไฟล์ขนาดเกินที่กำหนด
        acceptedFiles: "image/*", // อนุญาตให้เลือกไฟล์ประเภทรูปภาพได้
        // The setting up of the dropzone
        init: function() {
            var myDropzone = this;
            this.on("addedfile", function(file) {
                $('#myForm').append("<input type='text' id='" + file.newName +
                    "' class='form_none' name='imgFiles2[]' value='" + file.newName + "'/>");
                file.previewElement.id = file.newName;
            }).on("removedfile", function(file) {
                var name = file.previewElement.id;

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    },
                    type: 'POST',
                    url: '{{ route('history.delete') }}',
                    data: {
                        name: name,
                    },
                    success: function(data) {
                        console.log('success: ' + data);
                        var element = document.getElementById("infinite");
                        var child = document.getElementById(name);
                        element.removeChild(child);
                        if (!document.getElementsByName('imgFiles2[]').length) {
                            //alert();
                            $('#dropzone_preview2').css("display", "none");
                            $('#dropzone-att').addClass('form-focus-error');
                            $('#dropzone-att')
                                .find('.form-error-message').css("display", "block");
                            $('#dropzone-att').css('border', 'solid 1px red');
                        }
                    }
                });
            }).on("maxfilesexceeded", function(file) {
                alert('allow maximum 5 file');
                this.removeFile(file);
            }).on('complete', function(file) {
                // let val = file.accepted;
                // if (file.accepted == true) {
                //     obj = JSON.parse(file.xhr.response);
                // }
                // let val1 = file.name;

                let val = file.accepted;
                if (file.accepted == true && file.xhr.response) {
                    obj = JSON.parse(file.xhr.response);
                }
                let val1 = file.name;


                $('#dropzone_preview2').css("display", "block");
                $('#dropzone-att').removeClass('form-focus-error');
                $('#dropzone-att').find('.form-error-message').css("display", "none");
                $('#dropzone-att').css('border', 'solid 1px green');
                if (document.getElementsByName('imgFiles2[]').length) {}
            }).on("success", function(file) {

            });


        }
    }
</script>
