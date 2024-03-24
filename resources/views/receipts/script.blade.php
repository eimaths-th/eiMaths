@php
    $detect = new Detection\MobileDetect();
@endphp
<script>
    $('#check-all').click(function() {
        $(':checkbox.flat').prop('checked', this.checked);
    });
    $(".export_button").click(function() {
        var len = $('input[name="table_records[]"]:checked').length;
        if (len > 0) {
            if (confirm("Click OK to Export?")) {
                $('form#delete_all').attr('action',
                    '{{ route('reciept.export') }}'); //this fails silently
                $('form#delete_all').submit();
            }
        } else {
            alert("Please Select Record For Export");
        }

    });

    function clearDropzonePreviews() {
        $('input[name="imgFiles[]"]').remove();
        $('#dropzone_preview').html('');
        $('#dropzone_preview').css("display", "none");
    }

    function clearDropzonePreviews2() {
        $('input[name="imgFiles2[]"]').remove();
        $('#dropzone_preview2').html('');
        $('#dropzone_preview2').css("display", "none");
    }


    $(document).ready(function() {
        let id;

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

        //$.noConflict();
        var token = ''
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var table_option = {
            ajax: {
                data: function(d) {
                    d.sdate = $('#reservation').val();
                }
            },
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
            @if ($detect->isMobile())
                responsive: true,
            @else
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
            @endif
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
                    data: 'contact_code',
                    name: 'contact_code'
                },
                {
                    data: 'contact_name',
                    name: 'contact_name'
                },
                {
                    data: 'receipt_number',
                    name: 'receipt_number'
                },
                {
                    data: 'payment_date',
                    name: 'payment_date'
                },
                {
                    data: 'product_level',
                    name: 'product_level'
                },
                {
                    data: 'book_use',
                    name: 'book_use'
                },
                {
                    data: 'total_fee',
                    name: 'total_fee'
                },
                /* {
                    data: 'start_term',
                    name: 'start_term'
                }, */
                {
                    data: 'action',
                    name: 'action'
                },
                {
                    data: 'more',
                    name: 'more'
                }
            ],
            "footerCallback": function(row, data, start, end, display) {
                var api = this.api();

                // Calculate the sum of values in column 8 for all pages
                var sum = api
                    .column(8)
                    .data()
                    .reduce(function(acc, value) {
                        return parseFloat(acc) + parseFloat(value);
                    }, 0);

                // Update the footer cell with the sum
                $(api.column(8).footer()).html(sum.toFixed(
                    2)); // Adjust the decimal places as needed
            }
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
                table.destroy();
            }

            // Set the date range back to its default
            var currentDate = moment();
            var startDate = moment(currentDate).subtract(365, 'days').format('YYYY-MM-DD');
            // Set the end date to the end of the current month
            var endDate = moment(currentDate).endOf('year').format('YYYY-MM-DD');
            //$('#reservation').val(startDate + ' - ' + endDate);
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

        let pdid;
        $(document).on('click', '#getShareData', function(e) {
            e.preventDefault();
            pdid = $(this).data('id');
            window.location.href = "/receipts/show/" + pdid + "/1";
        });


        let pid;
        $(document).on('click', '#getPrintData', function(e) {
            e.preventDefault();


            $('.alert-danger').html('');
            $('.alert-danger').hide();
            $('.alert-success').html('');
            $('.alert-success').hide();


            pid = $(this).data('id');
            $.ajax({
                url: "receipts/show/" + pid + '/0',
                method: 'GET',
                success: function(res) {
                    $('#print_data').html(res.html);
                    $('#PrintModal').modal('show');
                }
            });

        })

        $(document).on('click', '.btn-edit', function(e) {
            e.preventDefault();


            $('.alert-danger').html('');
            $('.alert-danger').hide();
            $('.alert-success').html('');
            $('.alert-success').hide();


            pid = $(this).data('id');
            $.ajax({
                url: "receipts/show/" + pid + '/2',
                method: 'GET',
                success: function(res) {
                    $('#edit_data').html(res.html);
                    $("#rdate").datepicker({
                        dateFormat: 'yy-mm-dd',
                        changeMonth: true,
                        changeYear: true,
                        yearRange: '1980:2050'
                    });
                    $('#EditRModal').modal('show');
                }
            });

        })


        let lid;
        $(document).on('click', '#getLogData', function(e) {
            e.preventDefault();
            lid = $(this).data('id');
            $.ajax({
                url: "receipts/log/" + lid,
                method: 'GET',
                success: function(res) {
                    $('#log_table').html(res.html);
                    $('#LogModal').modal('show');
                }
            });


        })

        let rid;
        let rid2;
        $(document).on('click', '#getDeleteData', function(e) {
            e.preventDefault();
            $('.alert-danger').html('');
            $('.alert-danger').hide();
            $('.alert-success').html('');
            $('.alert-success').hide();
            rid = $(this).data('id');
            rid2 = $(this).data('id2');
            if (confirm("Click OK to Delete?")) {
                $.ajax({
                    url: "receipts/delete/img/" + rid + "/" + rid2,
                    method: 'PUT',
                    // async : false,
                    success: function(res) {
                        toastr.success('ลบไฟล์เรียบร้อยแล้ว', {
                            timeOut: 5000
                        });
                        $('#imgs').html(res.imgs);

                    }
                })
            }
        })

        $(document).on('click', '#SaveReceipt', function(e) {
            e.preventDefault();
            $('.alert-danger').html('');
            $('.alert-danger').hide();
            $('.alert-success').html('');
            $('.alert-success').hide();

            var amountValues = [];
            var priceValues = [];
            var desValues = [];
            var unitValues = [];
            var disValues = [];
            var taxValues = [];
            var isValid = true;


            var rnmtInput = $('#rnumber'); // Get the input field element itself
            var rnmtValue = rnmtInput.val(); // Get the value of the input field
            if (rnmtValue.trim() === '') {
                // Invalid input
                rnmtInput.addClass("is-invalid");
                rnmtInput.removeClass("is-valid");
                rnmtInput.siblings(".error-message").text("กรุณาระบุหมายเลขใบเสร็จ");
                toastr.error('กรุณาระบุหมายเลขใบเสร็จ', {
                    timeOut: 5000
                });
                isValid = false;
            } else {
                // Valid input
                rnmtInput.removeClass("is-invalid");
                rnmtInput.addClass("is-valid");
            }

            var rdInput = $('#rdate'); // Get the input field element itself
            var rdValue = rdInput.val(); // Get the value of the input field
            if (rdValue.trim() === '') {
                // Invalid input
                rdInput.addClass("is-invalid");
                rdInput.removeClass("is-valid");
                rdInput.siblings(".error-message").text("กรุณาวันที่รับเงิน");
                toastr.error('กรุณาวันที่รับเงิน', {
                    timeOut: 5000
                });
                isValid = false;
            } else {
                // Valid input
                rdInput.removeClass("is-invalid");
                rdInput.addClass("is-valid");
            }

            $("input[name='description[]']").each(function() {
                var desInput = $(this);
                var inputValue = desInput.val(); // Get the value of the input field
                if (inputValue.trim() === '') {
                    // Invalid input
                    desInput.addClass("is-invalid");
                    desInput.removeClass("is-valid");
                    desInput.siblings(".error-message").text("กรุณาระบุรายละเอียด");
                    toastr.error('กรุณาระบุรายละเอียด', {
                        timeOut: 5000
                    });
                    isValid = false;
                } else {
                    // Valid input
                    desInput.removeClass("is-invalid");
                    desInput.addClass("is-valid");
                    desValues.push(inputValue); // Store the value if needed
                }
            });



            $("input[name='quantity[]']").each(function() {
                var amountInput = $(this);
                var value = parseFloat(amountInput.val());

                if (isNaN(value) || value % 0.5 !== 0) {
                    // Invalid amount input
                    amountInput.addClass("is-invalid");
                    amountInput.removeClass("is-valid");
                    amountInput.siblings(".error-message").text(
                        "กรุณาระบุจำนวน");
                    toastr.error('กรุณาระบุจำนวน', {
                        timeOut: 5000
                    });
                    isValid = false;
                } else {
                    // Valid amount input
                    amountInput.removeClass("is-invalid");
                    amountInput.addClass("is-valid");
                    amountValues.push(value);
                }
            });


            // Collect price values and validate
            $("input[name='price[]']").each(function() {
                var priceInput = $(this);
                var value = parseFloat(priceInput.val());

                if (isNaN(value) || value <= 0) {
                    // Invalid price input
                    priceInput.addClass("is-invalid");
                    priceInput.removeClass("is-valid");
                    priceInput.siblings(".error-message").text(
                        "กรุณาระบุราคาต่อหน่วย");
                    toastr.error('กรุณาระบุราคาต่อหน่วย', {
                        timeOut: 5000
                    });
                    isValid = false;
                } else {
                    // Valid price input
                    priceInput.removeClass("is-invalid");
                    priceInput.addClass("is-valid");
                    priceValues.push(value);
                }
            });

            /*  if (!document.getElementsByName('imgFiles[]').length) {
                 toastr.error('กรุณาแนบหลักฐานการชำระเงิน', {
                     timeOut: 5000
                 });
                 isValid = false;
             } */

            if (!isValid) {
                return false;
            }

            $("input[name='unit[]']").each(function() {
                var desInput = $(this);
                var inputValue = desInput.val();
                unitValues.push(inputValue);
            });

            $("input[name='discount[]']").each(function() {
                var desInput = $(this);
                var inputValue = desInput.val();
                disValues.push(inputValue);
            });

            $("input[name='tax[]']").each(function() {
                var desInput = $(this);
                var inputValue = desInput.prop('checked') ? desInput.val() : '0';
                taxValues.push(inputValue);
            });

            var values = $("input[name='imgFiles2[]']")
                .map(function() {
                    return $(this).val();
                }).get();


            var formData = {
                receipt_number: rnmtValue,
                receipt_date: rdValue,
                charge: $('#charge').val(),
                payment: $("input[name='payment']:checked").val(),
                des: desValues,
                amount: amountValues,
                price: priceValues,
                unit: unitValues,
                discount: disValues,
                tax: taxValues,
                total: parseFloat($('#netp').val()),
                img: values,
                _token: token
            };

console.log(formData);

            $.ajax({
                url: "receipts/save/" + pid,
                method: 'PUT',
                data: formData,

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
                        $('#EditRModal').modal('hide');
                        toastr.success(result.success, {
                            timeOut: 5000
                        });
                        clearDropzonePreviews2();
                        $('#Listview').DataTable().ajax.reload();


                    }
                }
            });
        });


    });

    var token = ''
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });




    //auto decimal
    $(document).on('change', '.auto_decimal', function() {
        const inputValue = parseFloat(this.value);
        const formattedValue = inputValue.toFixed(2);
        this.value = formattedValue;
    });

    $(".AddDate").datepicker({
        /*  onSelect: function() {
             table.draw();
         }, */
        dateFormat: 'yy-mm-dd',
        changeMonth: true,
        changeYear: true,
        yearRange: '1980:2050'
    });


    function calculateTotal(row) {
        var quantity = parseFloat(row.find("input[name='quantity[]']").val()) || 0;
        var price = parseFloat(row.find("input[name='price[]']").val()) || 0;
        var discount = parseFloat(row.find("input[name='discount[]']").val()) || 0;
        var total = (quantity * price) - discount;
        row.find("input[name='total[]']").val(total.toFixed(2));
    }

    let summary_total = () => {
        let total = 0;
        let net = 0;
        let netc = 0;
        let btotal = 0;
        let dtotal = 0;
        let vat = 0;
        let vatp = 0;


        $("table tbody tr").each(function() {
            calculateTotal($(this));

            let rowTotal = parseFloat($(this).find("input[name='total[]']").val()) || 0;
            net += rowTotal;
            if ($(this).find("input[name='tax[]']").is(":checked")) {
                total += rowTotal;
            } else {
                total += 0;
            }
            if (!$(this).find("input[name='tax[]']").is(":checked")) {
                btotal += rowTotal;
            } else {
                btotal += 0;
            }
            dtotal += parseFloat($(this).find("input[name='discount[]']").val()) || 0;
            vat = (total * 7) / 107;
            vatp = total - vat;

        });

        netc = net + parseInt($("#charge").val());
        $("#nonv").val(btotal.toFixed(2));
        $("#vatp").val(vatp.toFixed(2));
        $("#dis").val(dtotal.toFixed(2));
        $("#vat").val(vat.toFixed(2));
        $("#netp").val(netc.toFixed(2));

        $.ajax({
            url: "receipts/price_text/" + netc,
            method: 'GET',
            success: function(res) {
                $('#text_th').html(res.th);
                $('#text_en').html(res.en);
            }
        });
    }


    $(document).on('input', 'input[name="quantity[]"], input[name="price[]"], input[name="discount[]"],#charge',
        async function() {
            var value = $(this).val().trim();
            if (value === '') {
                $(this).val(0);
            }
            var row = $(this).closest('tr');
            await calculateTotal(row);
            summary_total();
        });


    $(document).on('click', "input[name='tax[]']", function() {
        summary_total();
    });

    $(function() {

        $(document).on('click', '#addRow', function() {
            var clonedRow = $(".firstTr:eq(0)").clone(true);
            var lastNo = parseInt($(".firstTr").last().find("input[name='no[]']").val()) || 0;
            clonedRow.find("input").val("");
            clonedRow.find("input[name='no[]']").val(lastNo + 1);
            clonedRow.find("select").prop('selectedIndex', 0);
            clonedRow.appendTo($("#myTbl"));
            summary_total();
        });
        $(document).on('click', '#removeRow', function() {
            if ($("#myTbl tr").length > 2) { // จะลบรายการได้ อย่างน้อย ต้องมี 1 รายการ
                $("#myTbl tr:last").remove(); // ลบรายการสุดท้าย
                summary_total();
            } else {
                // เหลือ 1 รายการลบไม่ได้
                if ($("#myTbl tr").length > 1) {
                    //alert("ต้องมีรายการข้อมูลอย่างน้อย 1 รายการ");
                    toastr.success('ต้องมีรายการข้อมูลอย่างน้อย 1 รายการ', {
                        timeOut: 5000
                    });
                }
            }
        });
        $(document).on('click', '.btnRemoveg', function() {
            if ($("#myTbl tr").length > 2) {
                $(this).closest("tr").remove();
                summary_total();
            } else {
                // เหลือ 1 รายการลบไม่ได้
                if ($("#myTbl tr").length > 1) {
                    //alert("ต้องมีรายการข้อมูลอย่างน้อย 1 รายการ");
                    toastr.success('ต้องมีรายการข้อมูลอย่างน้อย 1 รายการ', {
                        timeOut: 5000
                    });
                }
            }
        });

        $(this).closest(".abcd").remove();

    });

    function printReport() {
        var prtContent = document.getElementById("reportPrinting").innerHTML;

        // Combine styles and content to create the final HTML for printing
        var htmlToPrint = '<!DOCTYPE html><html><head>';

        htmlToPrint +=
            '<link rel="stylesheet" href="dist/css/print.css"><link rel="stylesheet" href="dist/css/adminlte.css?v=3.2.0">';
        htmlToPrint += '</head><body>' + prtContent + '</body></html>';
        var WinPrint = window.open();
        WinPrint.document.write(htmlToPrint);
        WinPrint.document.close();

        // Wait for a short moment to allow images and CSS to load
        setTimeout(function() {
            WinPrint.focus();
            WinPrint.print();
            WinPrint.close();
        }, 1000); // Adjust the delay as needed
    }
</script>
