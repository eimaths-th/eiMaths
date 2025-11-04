@php
    $detect = new Detection\MobileDetect();
@endphp
<script>
    var $eventLog = $(".js-event-log");
    let fatotal = () => {
        let atotal = 0;
        let aamount = 0;
        $("input[name='total[]']").each(function() {
            let value = parseFloat(this.value);
            if (!isNaN(value)) {
                atotal += value;
            }
        });
        $("input[name='amount[]']").each(function() {
            let value = parseFloat(this.value);
            if (!isNaN(value)) {
                aamount += value;
            }
        });
        //alert(atotal);
        $('#AddTotalCost').val(atotal.toFixed(2));
        $('#AddAmount').val(aamount.toFixed(2));
    }

    let efatotal = () => {
        let atotal = 0;
        let aamount = 0;
        $("input[name='etotal[]']").each(function() {
            let value = parseFloat(this.value);
            if (!isNaN(value)) {
                atotal += value;
            }
        });
        $("input[name='eamount[]']").each(function() {
            let value = parseFloat(this.value);
            if (!isNaN(value)) {
                aamount += value;
            }
        });
        //alert(atotal);
        $('#EditTotalCost').val(atotal.toFixed(2));
        $('#EditAmount').val(aamount.toFixed(2));
    }

    let log = (name, evt) => {
        if (!evt) {
            var args = "{}";
        } else {
            var args = JSON.stringify(evt.params, function(key, value) {
                if (value && value.nodeName) return "[DOM node]";
                if (value instanceof $.Event) return "[$.Event]";
                return value;
            });
        }
        var $e = $("<li>" + name + " -> " + args + "</li>");
        $eventLog.append($e);
        $e.animate({
            opacity: 1
        }, 10000, 'linear', function() {
            $e.animate({
                opacity: 0
            }, 2000, 'linear', function() {
                $e.remove();
            });
        });
    }

    // Function to clear Dropzone previews
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
        var urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('new')) {
            // 'new' parameter exists, call your function here and pass the URL parameters
            showOverlay();
            getDataFunction(urlParams);
        }

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

        $(".select2_single").select2({
            maximumSelectionLength: 1,
            allowClear: false,
            //theme: 'bootstrap4'
            placeholder: 'กรุณาเลือกสินค้า'
        });

        $(".select2_single").on("select2:unselect", function(e) {
            //log("select2:unselect", e);
            $('.products').html('');
        });

        $(".select2_singles").select2({
            maximumSelectionLength: 1,
            allowClear: false,
            //theme: 'bootstrap4'
            placeholder: 'กรุณาเลือกสินค้า ก่อนเลือกสินค้าในคลังสินค้า'
        });

        $(".select2_singlec").select2({
            maximumSelectionLength: 1,
            allowClear: false,
            //theme: 'bootstrap4'
            placeholder: 'please select student'
        });


        $(".select2_multiple").select2({
            maximumSelectionLength: 2,
            //placeholder: "With Max Selection limit 4",
            allowClear: false,
            //theme: 'bootstrap4'
            placeholder: 'please select'
        });


        $("#price,#amount,#stock").on("change", function() {
            var parent = $(this).parent().parent().parent();

            var amount = parent.find('#amount').val();
            var price = parent.find('#price').val();
            var sid = parent.find('#stock').val();

            let total_cost = amount * price;
            parent.find('#total').val(total_cost.toFixed(2))

            fatotal();
            if (sid !== null) {
                $.ajax({
                    type: "GET",
                    dataType: 'JSON',
                    async: false,
                    url: "stocks/find/price/" + sid,
                    success: function(res) {
                        if (parseInt(res.cost) > parseInt(res.price)) {
                            pptext = `***ควรขายที่ราคา ${res.price} - ${res.cost} บาท `;
                        } else if (parseInt(res.cost) == parseInt(res.price)) {
                            pptext = `***ควรขายที่ราคา ${res.cost} บาท `;
                        } else {
                            pptext = `***ควรขายที่ราคา ${res.cost} - ${res.price} บาท `;
                        }
                        //console.log(res);
                        parent.find('#lot_price').html(pptext);
                        if (parseInt(amount) > parseInt(res.remaining)) {
                            toastr.error('จำนวนที่จะขายมากกว่าจำนวนที่เหลือในล๊อต', {
                                timeOut: 5000
                            });
                            parent.find('#lot_error').html(
                                '***จำนวนที่จะขายมากกว่าจำนวนที่เหลือในล๊อต');
                            parent.find('#amount').val('')
                        } else {
                            parent.find('#lot_error').html('');
                        }
                    }
                });
            }
        });

        $(document).on('change', '#eprice,#eamount,#estock', function(e) {
            var parent = $(this).parent().parent().parent();

            var amount = parent.find('#eamount').val();
            var amounth = parent.find('#eamounth').val();
            var price = parent.find('#eprice').val();
            var sid = parent.find('#estock').val();

            let total_cost = amount * price;
            parent.find('#etotal').val(total_cost.toFixed(2))

            //alert(amounth);
            if (isNaN(parseInt(amounth))) {
                amounth = 0;
            }

            efatotal();
            if (sid !== null) {
                $.ajax({
                    type: "GET",
                    dataType: 'JSON',
                    async: false,
                    url: "stocks/find/price/" + sid,
                    success: function(res) {
                        //console.log(res);
                        if (parseInt(res.cost) > parseInt(res.price)) {
                            pptext = `***ควรขายที่ราคา ${res.price} - ${res.cost} บาท `;
                        } else if (parseInt(res.cost) == parseInt(res.price)) {
                            pptext = `***ควรขายที่ราคา ${res.cost} บาท `;
                        } else {
                            pptext = `***ควรขายที่ราคา ${res.cost} - ${res.price} บาท `;
                        }

                        //alert(parseInt(amounth));
                        //alert(parseInt(res.remaining)+parseInt(amounth));
                        parent.find('#elot_price').html(pptext);
                        if (parseInt(amount) > (parseInt(res.remaining) + parseInt(
                                amounth))) {
                            toastr.error('จำนวนที่จะขายมากกว่าจำนวนที่เหลือในล๊อต', {
                                timeOut: 5000
                            });
                            parent.find('#elot_error').html(
                                '***จำนวนที่จะขายมากกว่าจำนวนที่เหลือในล๊อต');
                            parent.find('#eamount').val('')
                        } else {
                            parent.find('#elot_error').html('');
                        }
                    }
                });
            }
        });


        $("#EditOrtherFee,#EditRegisterFee,#EditAccessFee,#EditRefund,#EditDiscountTerm,#EditDiscountBook").on(
            "keyup input",
            function() {
                let fee = $("#EditRegisterFee").val() || 0;
                let access_fee = $("#EditAccessFee").val() || 0;
                let orther_fee = $("#EditOrtherFee").val() || 0;
                let refund = $("#EditRefund").val() || 0;
                let discount = $("#EditDiscountTerm").val() || 0;
                let discount_book = $("#EditDiscountBook").val() || 0;
                let price = $("#EditTotalCosth").val() || 0;
                let bookfee = $("#EditBookFee").val() || 0;
                let total_cost = (parseFloat(price) + parseFloat(bookfee)) + (parseFloat(fee) + parseFloat(
                    refund) + parseFloat(access_fee) + parseFloat(orther_fee)) - parseFloat(
                    discount) - parseFloat(
                    discount_book);
                let ftotal = parseFloat(total_cost);
                let formattedc = ftotal.toFixed(2);
                $("#EditTotalCost").val(formattedc);
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
                    data: 'created_at',
                    name: 'created_at'
                },
                {
                    data: 'ref_code',
                    name: 'ref_code'
                },
                /* {
                    data: 'order_number',
                    name: 'order_number'
                }, */
                {
                    data: 'contact_code',
                    name: 'contact_code'
                },
                {
                    data: 'contact_name',
                    name: 'contact_name'
                },
                {
                    data: 'product_level',
                    name: 'product_level'
                },
                {
                    data: 'product_name',
                    name: 'product_name'
                },
                /*{
                    data: 'amount',
                    name: 'amount'
                },
                  {
                     data: 'cost',
                     name: 'cost'
                 }, */
                {
                    data: 'total_price',
                    name: 'total_price'
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

                sum = 0;
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
        // console.log(table_option);

        $('#SearchButtons').on('click', function() {
            var searchType = $('#search_type').val();
            var keyword = $('#keyword').val();

            if (searchType !== '' && keyword !== '') {
                // Clear the previous search and any custom filters
                table.search('').draw();
                $.fn.dataTable.ext.search.pop(); // Remove the custom date range filter

                // Apply the new search based on searchType and keyword
                if (searchType === '1') {
                    table.column(3).search(keyword).draw();
                } else if (searchType === '2') {
                    table.column(4).search(keyword).draw();
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
            window.location.href = "/orders/show/" + pdid + "/1";
        });

        let lid;
        $(document).on('click', '#getLogData', function(e) {
            e.preventDefault();
            lid = $(this).data('id');
            $.ajax({
                url: "orders/log/" + lid,
                method: 'GET',
                success: function(res) {
                    $('#log_table').html(res.html);
                    $('#LogModal').modal('show');
                }
            });


        })






        $(document).on('click', '.btn-delete', function(e) {
            e.preventDefault();

            if (!confirm("ยืนยันการทำรายการ ?")) return;
            e.preventDefault();

            id = $(this).data('id');
            $.ajax({
                url: "orders/cancle/" + id,
                method: 'GET',
                success: function(res) {
                    toastr.success(res.success, {
                        timeOut: 5000
                    });
                    $('#Listview').DataTable().ajax.reload();
                }
            });

        })

        function getDataFunction(urlParams) {
            $('.alert-danger').html('');
            $('.alert-danger').hide();
            $('.alert-success').html('');
            $('.alert-success').hide();

            clearDropzonePreviews2();

            id = urlParams.get('new');
            $.ajax({
                url: "orders/edit/" + id,
                method: 'GET',
                success: function(res) {
                    console.log(res);
                    $('#EditCentre').val(res.data.centre).change();
                    $('#EditCentre').prop('disabled', true);
                    $('#EditLot').val(res.receipt_number);
                    $('#EditCompany').val(res.data.cid).change();
                    $('#EditTotalCost').val(res.total_price);
                    $('#EditTotalCosth').val(res.old_price);
                    $('#EditDetail').val(res.data.detail);


                    if (res.data.discount !== "" && res.data.discount !== null) {
                        $('#EditDiscountTerm').val(res.data.discount);
                    }

                    if (res.data.discount_book !== "" && res.data.discount_book !== null) {
                        $('#EditDiscountBook').val(res.data.discount_book);
                    }

                    if (res.data.termfee !== "" && res.data.termfee !== null) {
                        $('#EditTermFee').val(res.data.termfee);
                    }

                    if (res.data.bookfee !== "" && res.data.bookfee !== null) {
                        $('#EditBookFee').val(res.data.bookfee);
                    }

                    if (res.data.refund !== "" && res.data.refund !== null) {
                        $('#EditRefund').val(res.data.refund);
                    } else {
                        $('#EditRefund').val(res.para[0].price);
                    }
                    if (res.data.register_fee !== "" && res.data.register_fee !== null) {
                        $('#EditRegisterFee').val(res.data.register_fee);
                    } else {
                        $('#EditRegisterFee').val(res.para[1].price);
                    }
                    if (res.data.access_fee !== "" && res.data.access_fee !== null) {
                        $('#EditAccessFee').val(res.data.access_fee);
                    } else {
                        $('#EditAccessFee').val(res.para[2].price);
                    }

                    if (res.data.payment == 1) {
                        $("#radioSuccess1").prop("checked", true);
                    } else if (res.data.payment == 2) {
                        $("#radioSuccess2").prop("checked", true);
                    } else if (res.data.payment == 3) {
                        $("#radioSuccess3").prop("checked", true);
                    }

                    $('#EditModalBodyTable').html(res.table_html);
                    $('.imgs').html(res.imgs);
                    if (res.data.status == 1) {
                        $('#SubmitEditFormp').css('display', 'none');
                    }

                    if (res.data.ref) {
                        $('#getRef').val(res.data.ref);
                        $('#getRef').prop('readonly', true);
                    } else {
                        console.log('id Create Receipt');

                        $('#getRef').val(''); // Clear the value if ref is null or undefined
                        $('#getRef').prop('readonly',
                            false); // Make editable if ref is null or undefined
                    }
                    if (res.data.courses_pending_id) {
                        $('#courses_pending_id').val(res.data.courses_pending_id);

                    } else {
                        console.log('false courses_pending_id');
                        $('#courses_pending_id').val('');
                    }


                    $('#EditModal').modal('show');
                    hideOverlay();
                }
            });
        }

        $(document).on('click', '#getEditData', function(e) {
            e.preventDefault();


            $('.alert-danger').html('');
            $('.alert-danger').hide();
            $('.alert-success').html('');
            $('.alert-success').hide();
            clearDropzonePreviews2();
            id = $(this).data('id');
            $.ajax({
                url: "orders/edit/" + id,
                method: 'GET',
                success: function(res) {
                    // console.log(res);
                    $('#EditCentre').val(res.data.centre).change();
                    $('#EditCentre').prop('disabled', true);
                    $('#EditLot').val(res.receipt_number);
                    $('#EditCompany').val(res.data.cid).change();
                    $('#EditTotalCost').val(res.total_price);
                    $('#EditTotalCosth').val(res.old_price);
                    $('#EditDetail').val(res.data.detail);


                    if (res.data.discount !== "" && res.data.discount !== null) {
                        $('#EditDiscountTerm').val(res.data.discount);
                    }

                    if (res.data.discount_book !== "" && res.data.discount_book !== null) {
                        $('#EditDiscountBook').val(res.data.discount_book);
                    }

                    if (res.data.termfee !== "" && res.data.termfee !== null) {
                        $('#EditTermFee').val(res.data.termfee);
                    }

                    if (res.data.bookfee !== "" && res.data.bookfee !== null) {
                        $('#EditBookFee').val(res.data.bookfee);
                    }

                    if (res.data.refund !== "" && res.data.refund !== null) {
                        $('#EditRefund').val(res.data.refund);
                    } else {
                        $('#EditRefund').val(res.para[0].price);
                    }
                    if (res.data.register_fee !== "" && res.data.register_fee !== null) {
                        $('#EditRegisterFee').val(res.data.register_fee);
                    } else {
                        $('#EditRegisterFee').val(res.para[1].price);
                    }
                    if (res.data.access_fee !== "" && res.data.access_fee !== null) {
                        $('#EditAccessFee').val(res.data.access_fee);
                    } else {
                        $('#EditAccessFee').val(res.para[2].price);
                    }


                    if (res.data.payment == 1) {
                        $("#eradioSuccess1").prop("checked", true);
                    } else if (res.data.payment == 2) {
                        $("#eradioSuccess2").prop("checked", true);
                    } else if (res.data.payment == 3) {
                        $("#eradioSuccess3").prop("checked", true);
                    }

                    $('#EditModalBodyTable').html(res.table_html);
                    $('.imgs').html(res.imgs);

                    if (res.data.status == 1) {
                        $('#SubmitEditFormp').css('display', 'none');
                        $('#SubmitEditForm').html('<i class="fas fa-download"></i> Save');
                    }

                    if (res.data.ref) {

                        $('#getRef').val(res.data.ref);
                        $('#courses_pending_id').val(res.data.courses_pending_id);
                        $('#getRef').prop('readonly', true);
                    } else {

                        $('#getRef').val(''); // Clear the value if ref is null or undefined
                        $('#getRef').prop('readonly',
                            false); // Make editable if ref is null or undefined
                    }

                    console.log('test');


                    $('#EditModal').modal('show');

                }
            });

        })

        let status = 0;

        $('#SubmitEditForm, #SubmitEditFormp').click(function(e) {
            e.preventDefault();

            // Check which button was clicked and update the 'status' variable
            if (this.id === 'SubmitEditForm') {
                status = 1; // Set status to 1 if Confirm button is clicked
            } else if (this.id === 'SubmitEditFormp') {
                status = 0; // Set status to 0 if Pending button is clicked
            }

            // Show SweetAlert2 confirmation dialog
            Swal.fire({
                title: 'ยืนยันการทำรายการ?',
                text: status === 1 ? 'คุณต้องการยืนยันรายการนี้หรือไม่?' : 'คุณต้องการบันทึกเป็น Pending หรือไม่?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'ยืนยัน',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    // User confirmed, proceed with the submission
                    $('.alert-danger').html('');
                    $('.alert-danger').hide();
                    $('.alert-success').html('');
                    $('.alert-success').hide();

                    console.log('test1');

                    var values = $("input[name='imgFiles2[]']")
                        .map(function() {
                            return $(this).val();
                        }).get();

                    var formData = {
                        ref: $('#getRef').val(),
                        courses_pending_id: $('#courses_pending_id').val(),
                        order_number: $('#EditLot').val(),
                        cid: $('#EditCompany').val()[0],
                        total_cost: $('#EditTotalCost').val(),
                        detail: $('#EditDetail').val(),
                        refund: $('#EditRefund').val(),
                        register_fee: $('#EditRegisterFee').val(),
                        access_fee: $('#EditAccessFee').val(),
                        orther_fee: $('#EditOrtherFee').val(),
                        discount: $('#EditDiscountTerm').val(),
                        discount_book: $('#EditDiscountBook').val(),
                        status: status,
                        payment: $("input[name='payment']:checked").val(),
                        img: values,
                        _token: token
                    };

                    // Show loading and disable buttons
                    $('#SubmitEditForm, #SubmitEditFormp').prop('disabled', true);
                    Swal.fire({
                        title: 'กำลังบันทึกข้อมูล...',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: "orders/save/" + id,
                        method: 'PUT',
                        data: formData,

                        success: function(result) {
                            // Hide loading
                            Swal.close();
                            $('#SubmitEditForm, #SubmitEditFormp').prop('disabled', false);

                            //console.log(result);
                            if (result.errors) {
                                $('.alert-danger').html('');
                                $.each(result.errors, function(key, value) {
                                    $('.alert-danger').show();
                                    $('.alert-danger').append('<strong><li>' + value +
                                        '</li></strong>');
                                });
                            } else {
                                console.log(result);
                                // $('.alert-danger').hide();
                                // $('.alert-success').show();
                                // $('.alert-success').append('<strong><li>' + result.success +
                                //     '</li></strong>');
                                // $('#EditModal').modal('hide');
                                // toastr.success(result.success, {
                                //     timeOut: 5000
                                // });
                                // clearDropzonePreviews2();
                                // $('#Listview').DataTable().ajax.reload();

                                var ostatus = result.status;
                                if (ostatus == 1) {
                                    var url = "{{ route('receipts') }}";
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
                                    clearDropzonePreviews2();
                                    $('#Listview').DataTable().ajax.reload();
                                }


                            }
                        },
                        error: function(xhr, status, error) {
                            // Hide loading on error
                            Swal.close();
                            $('#SubmitEditForm, #SubmitEditFormp').prop('disabled', false);
                            
                            $('.alert-danger').html('');
                            $('.alert-danger').show();
                            $('.alert-danger').append('<strong><li>เกิดข้อผิดพลาด: ' + error + '</li></strong>');
                        }
                    });
                }
            });
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
                url: "orders/show/" + pid + '/0',
                method: 'GET',
                success: function(res) {
                    $('#print_data').html(res.html);
                    $('#PrintModal').modal('show');
                }
            });

        })



    });
</script>
<script type="text/javascript">
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
