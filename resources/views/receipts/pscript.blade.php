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


        $("#EditRegisterFee,#EditRefund,#EditPromotion").on("keyup input", function() {
            let fee = $("#EditRegisterFee").val() || 0;
            let refund = $("#EditRefund").val() || 0;
            let promotion = $("#EditPromotion").val() || 0;
            let price = $("#EditTotalCosth").val() || 0;
            let total_cost = parseFloat(price) + (parseFloat(fee) + parseFloat(refund)) - parseFloat(
                promotion);
            let ftotal = parseFloat(total_cost);
            let formattedc = ftotal.toFixed(2);
            $("#EditTotalCost").val(formattedc);
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
                    //d.search = $('input[type="search"]').val();
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

                // Calculate the sum of values in column 8 for all pages
                var sum = api
                    .column(7)
                    .data()
                    .reduce(function(acc, value) {
                        return parseFloat(acc) + parseFloat(value);
                    }, 0);

                // Update the footer cell with the sum
                $(api.column(7).footer()).html(sum.toFixed(
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
            var startDate = moment(currentDate).subtract(7, 'days').format('YYYY-MM-DD');
            var endDate = moment(currentDate).endOf('month').format('YYYY-MM-DD');
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
                    $('#EditCentre').val(res.data.centre).change();
                    $('#EditCentre').prop('disabled', true);
                    $('#EditLevel').val(res.datas[0].level_id).change();
                    $('#EditLevel').prop('disabled', true);
                    $('#EditTerm').val(res.datas[0].term_id).change();
                    $('#EditTerm').prop('disabled', true);
                    $('#EditLot').val(res.receipt_number);
                    $('#EditProduct').val(1).change();
                    $("select[name=eproduct]").attr("readonly", "readonly");
                    $('#EditStock').val(res.data.sid).change();
                    $("select[name=estock]").attr("readonly", "readonly");
                    $('#EditCompany').val(res.data.cid).change();
                    //$('#EditAmount').val(res.data.amount);
                    //$('#EditCost').val(res.data.cost);
                    $('#EditTotalCost').val(res.data.total_price);
                    $('#EditTotalCosth').val(res.datas[0].price);
                    $('#EditDetail').val(res.data.detail);
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
                    $('#EditPromotion').val(res.data.promotion);
                    $('#EditModalBodyTable').html(res.table_html);
                    $('.imgs').html(res.imgs);
                    console.log(res);
                    if (res.data.status == 1) {
                        $('#SubmitEditFormp').css('display', 'none');
                        $('#SubmitEditForm').html('<i class="fas fa-download"></i> Save');
                    }
                    $('#EditModal').modal('show');

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
                    $('#EditCentre').val(res.data.centre).change();
                    $('#EditCentre').prop('disabled', true);
                    $('#EditLevel').val(res.datas[0].level_id).change();
                    $('#EditLevel').prop('disabled', true);
                    $('#EditTerm').val(res.datas[0].term_id).change();
                    $('#EditTerm').prop('disabled', true);
                    $('#EditLot').val(res.receipt_number);
                    $('#EditProduct').val(1).change();
                    $("select[name=eproduct]").attr("readonly", "readonly");
                    $('#EditStock').val(res.data.sid).change();
                    $("select[name=estock]").attr("readonly", "readonly");
                    $('#EditCompany').val(res.data.cid).change();
                    //$('#EditAmount').val(res.data.amount);
                    //$('#EditCost').val(res.data.cost);
                    $('#EditTotalCost').val(res.total_price);
                    $('#EditTotalCosth').val(res.datas[0].price);
                    $('#EditDetail').val(res.data.detail);
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

                    $('#EditPromotion').val(res.data.promotion);
                    $('#EditModalBodyTable').html(res.table_html);
                    $('.imgs').html(res.imgs);
                    if (res.data.status == 1) {
                        $('#SubmitEditFormp').css('display', 'none');
                    }
                    $('#EditModal').modal('show');
                    hideOverlay();
                }
            });
        }

        let status = 0;

        $('#SubmitEditForm, #SubmitEditFormp').click(function(e) {

            if (!confirm("ยืนยันการทำรายการ ?")) return;
            e.preventDefault();

            $('.alert-danger').html('');
            $('.alert-danger').hide();
            $('.alert-success').html('');
            $('.alert-success').hide();

            // Check which button was clicked and update the 'status' variable
            if (this.id === 'SubmitEditForm') {
                status = 1; // Set status to 1 if Confirm button is clicked
            } else if (this.id === 'SubmitEditFormp') {
                status = 0; // Set status to 0 if Pending button is clicked
            }

            var stockValues = [];
            var amountValues = [];
            var priceValues = [];
            var isValid = true;

            // Initializing array values
            $("select[name='estock[]']").each(function() {
                stockValues.push(this.value);
            });

            // Check for duplicates in stock values
            var duplicateValues = stockValues.filter(function(value, index, self) {
                return self.indexOf(value) !== index;
            });

            if (duplicateValues.length > 0) {
                toastr.error('ไม่สามารถเลือกสินค้าล็อตเดียวกันในรายการขาย', {
                    timeOut: 5000
                });
                return false;
            }

            // Validate stock select
            var stockSelects = $("select[name='estock[]']");
            stockSelects.each(function() {
                var stockSelect = $(this);
                var selectedValue = stockSelect.val();

                if (selectedValue === null || selectedValue.trim() === '') {
                    stockSelect.addClass("is-invalid");
                    stockSelect.removeClass("is-valid");
                    toastr.error('กรุณาเลือกสินค้าในคลังสินค้า', {
                        timeOut: 5000
                    });
                    isValid = false;
                } else {
                    stockSelect.removeClass("is-invalid");
                    stockSelect.addClass("is-valid");
                }
            });

            if (!isValid) {
                return false;
            }

            // Collect amount values and validate
            $("input[name='eamount[]']").each(function() {
                var amountInput = $(this);
                var value = parseFloat(amountInput.val());

                if (isNaN(value) || value % 0.5 !== 0) {
                    // Invalid amount input
                    amountInput.addClass("is-invalid");
                    amountInput.removeClass("is-valid");
                    amountInput.siblings(".error-message").text(
                        "กรุณาระบุจำนวนที่จะขาย");
                    toastr.error('กรุณาระบุจำนวนที่จะขาย', {
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

            if (!isValid) {
                return false;
            }

            // Collect price values and validate
            $("input[name='eprice[]']").each(function() {
                var priceInput = $(this);
                var value = parseFloat(priceInput.val());

                if (isNaN(value) || value <= 0) {
                    // Invalid price input
                    priceInput.addClass("is-invalid");
                    priceInput.removeClass("is-valid");
                    priceInput.siblings(".error-message").text(
                        "กรุณาระบุราคาที่จะขาย");
                    toastr.error('กรุณาระบุราคาที่จะขาย', {
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

            if (!isValid) {
                return false;
            }

            var values = $("input[name='imgFiles2[]']")
                .map(function() {
                    return $(this).val();
                }).get();


            if ($('#EditCompany').val()[0] !== undefined) {
                var formData = {
                    order_number: $('#EditLot').val(),
                    sid: stockValues,
                    amount: amountValues,
                    price: priceValues,
                    //tamount: $('#EditAmount').val(),
                    //cost: $('#AddCost').val(),
                    cid: $('#EditCompany').val()[0],
                    pid: $('#EditProduct').val()[0],
                    total_cost: $('#EditTotalCost').val(),
                    detail: $('#EditDetail').val(),
                    refund: $('#EditRefund').val(),
                    register_fee: $('#EditRegisterFee').val(),
                    promotion: $('#EditPromotion').val(),
                    status: status,
                    img: values,
                    _token: token
                };

                $.ajax({
                    url: "orders/save/" + id,
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
                    }
                });
            } else {
                toastr.error('กรุณากรอกข้อมูลให้ครบ', {
                    timeOut: 5000
                });
            }
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
