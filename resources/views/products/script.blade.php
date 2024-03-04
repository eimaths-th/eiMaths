<script>
    const detectKeyboardLanguage = (inputText) => {
        // Define patterns for different languages
        const thaiPattern = /[ก-๙]/;
        const englishPattern = /[a-zA-Z]/;
        const defaultLanguage = 'Unknown';

        // Check if input text contains Thai characters
        if (thaiPattern.test(inputText)) {
            return 'Thai';
        }

        // Check if input text contains English characters
        if (englishPattern.test(inputText)) {
            return 'English';
        }

        // If no language pattern is matched, return default language
        return defaultLanguage;
    };

    function showAlert(message) {
        ezBSAlert({
            headerText: "Alert",
            messageText: message,
            alertType: "danger",
        });
    }

    function clearFields() {
        $('#PlusCode').val('');
        $('#PlusCodeh').val('');
        $('#PlusName').val('');
        $('#PlusUnit').val('');
        $('#PlusUnit').val('');
        $('#istock').html('');
    }

    $(window).ready(function() {

        /* $("#scannerInput").scannerDetection({
            timeBeforeScanTest: 200,
            avgTimeByChar: 40,
            preventDefault: true,

            endChar: [13],
            onError: function(string){alert('Error ' + string);}
        }); */

        console.log('all is well');

        $(document).scannerDetection({
            timeBeforeScanTest: 200, // wait for the next character for upto 200ms
            avgTimeByChar: 40, // it's not a barcode if a character takes longer than 100ms
            preventDefault: true,
            endChar: [13],
        });

        $(document).bind('scannerDetectionComplete', async function(e, data) {
                console.log('complete ' + data.string);
                const userInput = data.string; // Sample input text
                const keyboardLanguage = detectKeyboardLanguage(userInput);

                // Display alert error if the detected language is not English
                if (keyboardLanguage !== 'English') {
                    //alert("Error: กรุณาเปลี่ยน Keyboard เป็นภาษาไทย");
                    const prom = ezBSAlert({
                        headerText: "Alert",
                        messageText: "กรุณาเปลี่ยน Keyboard เป็นภาษาอังกฤษ",
                        alertType: "danger",
                    });
                } else {
                    $("#PlusCode").val(data.string);
                    var pcode = $('#PlusCode').val();
                    await $.ajax({
                        url: "products/find/" + pcode,
                        method: 'GET',
                        success: function(res) {
                            if (res.product) {
                                $('#PlusCodeh').val(res.product.id);
                                $('#PlusName').val(res.product.name);
                                $('#PlusUnit').val(res.product.unit);
                                $('#PlusDetail').val(res.product.detail);
                                $('#istock').html('คงเหลือ ' + res.product.amount + ' ' +
                                    res.product.unit);
                            } else {
                                showAlert(res.message);
                                clearFields();
                            }
                        },
                        error: function(xhr, status, error) {
                            showAlert(error);
                            clearFields();
                        }
                    });
                }

            })
            .bind('scannerDetectionError', function(e, data) {
                console.log('detection error ' + data.string);
            })
            .bind('scannerDetectionReceive', function(e, data) {
                console.log('Recieve');
                console.log(data.evt.which);
            });
    });
    //$(window).scannerDetection('success');

    $(document).ready(function() {
        $(document).on('change', '.auto_decimal', function() {
            // Parse the input value to a number
            const inputValue = parseFloat(this.value);

            // Format the number with 2 decimal places
            const formattedValue = inputValue.toFixed(2);

            // Update the input value with the formatted number
            this.value = formattedValue;
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

        $(".delete_all_button").click(function() {
            var len = $('input[name="table_records[]"]:checked').length;
            if (len > 0) {

                if (confirm("Click OK to Delete?")) {
                    $('form#delete_all').submit();
                }
            } else {
                alert("Please Select Record For Delete");
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
                    data: 'code',
                    name: 'code'
                },
                {
                    data: 'centre_name',
                    name: 'centre_name'
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'amount',
                    name: 'amount'
                },
                {
                    data: 'unit',
                    name: 'unit'
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


        $("#AddCentre").change(function() {
            let centre = $('#AddCentre').val();
            if (centre.length !== 0) {
                $.ajax({
                    method: "GET",
                    url: "products/running/" + centre,
                    async: false,
                    success: function(res) {
                        //console.log(res)
                        //$('#AddCode').prop('readonly', false);
                        $('#AddCode').val(res.running);
                    }
                });
            }
        })

        $(document).on('click', '#AddStock', async function(e) {
            e.preventDefault();
            $('.alert-danger').html('');
            $('.alert-danger').hide();
            $('.alert-success').html('');
            $('.alert-success').hide();

            $('#PlusModal').modal('show');
        });

        $(document).on('click', '#CreateButton', async function(e) {
            e.preventDefault();
            $('.alert-danger').html('');
            $('.alert-danger').hide();
            $('.alert-success').html('');
            $('.alert-success').hide();

            var addCentre = $('#AddCentre').val();
            await $.ajax({
                method: "GET",
                url: "products/running/" + addCentre,
                success: function(res) {
                    //console.log(res)
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
                url: "{{ route('products.store') }}",
                method: 'post',
                data: {
                    centre: $('#AddCentre').val()[0],
                    code: $('#AddCode').val(),
                    name: $('#AddName').val(),
                    unit: $('#AddUnit').val(),
                    amount: $('#AddAmount').val(),
                    detail: $('#AddDetail').val(),
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
                        //$('#AddCentre').val(null).trigger("change");
                        $('.form').trigger('reset');
                        $('#CreateModal').modal('hide');
                    }
                }
            });
        });

        let id;
        $(document).on('click', '.btn-edit', function(e) {
            e.preventDefault();


            $('.alert-danger').html('');
            $('.alert-danger').hide();
            $('.alert-success').html('');
            $('.alert-success').hide();

            id = $(this).data('id');
            $.ajax({
                url: "products/edit/" + id,
                method: 'GET',
                success: function(res) {
                    $('#EditCentre').val(res.data.centre).change();
                    $('#EditName').val(res.data.name);
                    $('#EditCode').val(res.data.code);
                    $('#EditUnit').val(res.data.unit);
                    $('#EditAmount').val(res.data.amount);
                    $('#EditDetail').val(res.data.detail);
                    $('#EditModalBody').html(res.html);
                    $('#EditModal').modal('show');
                }
            });

        })

        $(document).on('click', '.btn-barcode', function(e) {
            e.preventDefault();

            $('.alert-danger').html('');
            $('.alert-danger').hide();
            $('.alert-success').html('');
            $('.alert-success').hide();

            id = $(this).data('id');
            $.ajax({
                url: "products/barcode/" + id,
                method: 'GET',
                success: function(res) {
                    $('#barcodeContainer').html(res.barcode);
                    $('#BarcodeModal').modal('show');
                }
            });

        })

        $(document).on('click', '.btn-qbarcode', function(e) {
            e.preventDefault();

            $('.alert-danger').html('');
            $('.alert-danger').hide();
            $('.alert-success').html('');
            $('.alert-success').hide();

            id = $(this).data('id');
            $.ajax({
                url: "products/qbarcode/" + id,
                method: 'GET',
                success: function(res) {
                    $('#qbarcodeContainer').html(res.qbarcode);
                    $('#qBarcodeModal').modal('show');
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
                url: "products/save/" + id,
                method: 'PUT',
                data: {

                    name: $('#EditName').val(),
                    unit: $('#EditUnit').val(),
                    amount: $('#EditAmount').val(),
                    detail: $('#EditDetail').val(),
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


        $('#PlusButton').click(function(e) {
            if (!confirm("Confirm the action?")) return;
            e.preventDefault();

            $('.alert-danger').html('');
            $('.alert-danger').hide();
            $('.alert-success').html('');
            $('.alert-success').hide();

            id = $('#PlusCodeh').val();

            $.ajax({
                url: "products/add_stock/" + id,
                method: 'PUT',
                data: {
                    amount: $('#PlusAmount').val(),
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
                        $('#PlusModal').modal('hide');
                        toastr.success(result.success, {
                            timeOut: 5000
                        });
                        clearFields();
                        $('#Listview').DataTable().ajax.reload();
                    }
                }
            });
        });

        $('#printBarcodeButton').click(function(event) {
            event.preventDefault(); // Prevent default button behavior

            // Create a new window with the barcode content
            var printWindow = window.open('', '_blank');
            printWindow.document.open();
            printWindow.document.write('<html><head><title>Print Barcode</title></head><body>');
            printWindow.document.write('<div id="barcodeContainer">' + $('#barcodeContainer').html() +
                '</div>');
            printWindow.document.write('</body></html>');
            printWindow.document.close();

            setTimeout(function() {
                printWindow.focus();
                printWindow.print();
                printWindow.close();
            }, 1000);

        });

        $('#printqBarcodeButton').click(function(event) {
            event.preventDefault(); // Prevent default button behavior

            // Create a new window with the barcode content
            var printWindow = window.open('', '_blank');
            printWindow.document.open();
            printWindow.document.write('<html><head><title>Print Qrcode</title></head><body>');
            printWindow.document.write('<div id="qbarcodeContainer">' + $('#qbarcodeContainer').html() +
                '</div>');
            printWindow.document.write('</body></html>');
            printWindow.document.close();

            setTimeout(function() {
                printWindow.focus();
                printWindow.print();
                printWindow.close();
            }, 1000);

        });




        /*  $(document).on('keyup', function(e) {
             // Check if the pressed key is a numeric key (key codes 48 to 57) or Enter key (key code 13)
             if ((e.keyCode >= 48 && e.keyCode <= 57) || e.keyCode === 13) {
                 // If so, focus on the barcodeInput field
                 $('#barcodeInput').focus();

                 // Get the pressed key character
                 var char = String.fromCharCode(e.keyCode);

                 console.log(char);
                 // Append the character to the barcodeInput value
                 $('#barcodeInput').val(char);
             }
         }); */


        $(document).on('click', '.btn-delete', function() {
            if (!confirm("Confirm the action?")) return;

            var rowid = $(this).data('rowid')
            var el = $(this)
            if (!rowid) return;


            $.ajax({
                //type: "POST",
                method: 'DELETE',
                dataType: 'JSON',
                url: "products/destroy/",
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
