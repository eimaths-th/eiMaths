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

                if (confirm("ยืนยันการลบข้อมูล?")) {
                    $('form#delete_all').submit();
                }
            } else {
                alert("กรุณาเลือกรายการที่จะลบ");
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
            placeholder: 'กรุณาเลือกบริษัท'
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


        $("#EditCost,#EditAmount").on("keyup", function() {
            let cost = $("#EditCost").val();
            let amount = $("#EditAmount").val();

            let total_cost = amount * cost;
            $("#EditTotalCost").val(total_cost);

        });

        $("#EditRegisterFee,#EditRefund,#EditPromotion").on("keyup", function() {
            let fee = $("#EditRegisterFee").val() || 0;
            let refund = $("#EditRefund").val() || 0;
            let promotion = $("#EditPromotion").val() || 0;
            let price = $("#EditTotalCosth").val() || 0;
            alert();
            let total_cost = parseFloat(price) + (parseFloat(fee) + parseFloat(refund)) - parseFloat(
                promotion);
                console.log(total_cost)
            $("#EditTotalCost").val(parseFloat(total_cost));
        });



        /* $(".productl").change(function() {
            let product = $('#AddProduct').val();
            $('#AddStock').html('');
            if (product.length !== 0) {
                $.ajax({
                    method: "GET",
                    url: "stocks/find/add/" + product,
                    success: function(res) {
                        $('.products').html(res.html);
                    }
                });
            }

        })

        $(".producte").change(function() {
            let product = $('#EditProduct').val();
            $('#EditStock').html('');
            if (product.length !== 0) {
                $.ajax({
                    method: "GET",
                    url: "stocks/find/edit/" + product,
                    async: false,
                    success: function(res) {
                        $('#EditStock').html(res.html);
                    }
                });
            }

        }) */

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
                    data: 'order_number',
                    name: 'order_number'
                },
                {
                    data: 'product_name',
                    name: 'product_name'
                },
                {
                    data: 'contact_name',
                    name: 'contact_name'
                },
                {
                    data: 'amount',
                    name: 'amount'
                },
                /*  {
                     data: 'cost',
                     name: 'cost'
                 }, */
                {
                    data: 'total_price',
                    name: 'total_price'
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


        $(document).on('click', '#CreateButton', function(e) {
            e.preventDefault();
            $('.alert-danger').html('');
            $('.alert-danger').hide();
            $('.alert-success').html('');
            $('.alert-success').hide();
            clearDropzonePreviews();
            $.ajax({
                method: "GET",
                url: "{{ route('orders.running') }}",
                success: function(res) {
                    console.log(res)
                    $('#AddLot').val(res.running);
                }
            });
            $('#CreateModal').modal('show');
        });


        $('#SubmitCreateForm').click(function(e) {
            e.preventDefault();
            $('.alert-danger').html('');
            $('.alert-danger').hide();
            $('.alert-success').html('');
            $('.alert-success').hide();

            var stockValues = [];
            var amountValues = [];
            var priceValues = [];
            var isValid = true;

            // Initializing array values
            $("select[name='stock[]']").each(function() {
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
            var stockSelects = $("select[name='stock[]']");
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
            $("input[name='amount[]']").each(function() {
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
            $("input[name='price[]']").each(function() {
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


            var values = $("input[name='imgFiles[]']")
                .map(function() {
                    return $(this).val();
                }).get();

            if ($('#AddCompany').val()[0] !== undefined) {
                var formData = {
                    img: values,
                    order_number: $('#AddLot').val(),
                    sid: stockValues, // Only consider the first selected stock value
                    cid: $('#AddCompany').val()[0],
                    pid: $('#AddProduct').val()[0],
                    amount: amountValues,
                    price: priceValues,
                    tamount: $('#AddAmount').val(),
                    //cost: $('#AddCost').val(),
                    total_cost: $('#AddTotalCost').val(),
                    detail: $('#AddDetail').val(),
                    _token: token
                };

                $.ajax({
                    url: "{{ route('orders.store') }}",
                    method: 'post',
                    data: formData,
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
                            $('.alert-success').text(result.success);
                            toastr.success(result.success, {
                                timeOut: 5000
                            });
                            $('#Listview').DataTable().ajax.reload();
                            $("#AddProduct").val(null).trigger("change")
                            $("#AddStock").val(null).trigger("change")
                            $("#AddCompany").val(null).trigger("change")
                            $('.form').trigger('reset');
                            clearDropzonePreviews();
                            $('#CreateModal').modal('hide');
                        }
                    }
                });

            } else {
                toastr.error('กรุณากรอกข้อมูลให้ครบ', {
                    timeOut: 5000
                });
            }
        });


        let id;
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
                    console.log(res);
                    $('#EditLot').val(res.data.order_number);
                    $('#EditProduct').val(1).change();
                    $("select[name=eproduct]").attr("readonly", "readonly");
                    $('#EditStock').val(res.data.sid).change();
                    $("select[name=estock]").attr("readonly", "readonly");
                    $('#EditCompany').val(res.data.cid).change();
                    //$('#EditAmount').val(res.data.amount);
                    //$('#EditCost').val(res.data.cost);
                    $('#EditTotalCost').val(res.data.total_price);
                    $('#EditTotalCosth').val(res.data.total_price);
                    $('#EditDetail').val(res.data.detail);
                    $('#EditModalBodyTable').html(res.table_html);
                    $('.imgs').html(res.imgs);
                    $('#EditModal').modal('show');
                }
            });

        })

        $('#SubmitEditForm').click(function(e) {
            if (!confirm("ยืนยันการทำรายการ ?")) return;
            e.preventDefault();

            $('.alert-danger').html('');
            $('.alert-danger').hide();
            $('.alert-success').html('');
            $('.alert-success').hide();

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
                    sid: stockValues, // Only consider the first selected stock value
                    cid: $('#EditCompany').val()[0],
                    pid: $('#EditProduct').val()[0],
                    amount: amountValues,
                    price: priceValues,
                    tamount: $('#EditAmount').val(),
                    //cost: $('#AddCost').val(),
                    total_cost: $('#EditTotalCost').val(),
                    detail: $('#EditDetail').val(),
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
                            //setTimeout(function() {
                            //$('.alert-success').hide();

                            //}, 10000);

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
                url: "orders/show/" + pid,
                method: 'GET',
                success: function(res) {
                    $('#print_data').html(res.html);
                    $('#PrintModal').modal('show');
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
                    url: "orders/delete/img/" + rid + "/" + rid2,
                    method: 'PUT',
                    // async : false,
                    success: function(res) {
                        toastr.success('ลบไฟล์เรียบร้อยแล้ว', {
                            timeOut: 5000
                        });
                        $('.imgs').html(res.imgs);

                    }
                })
            }
        })

        $(document).on('click', '.btn-delete', function() {
            if (!confirm("ยืนยันการทำรายการ ?")) return;

            var rowid = $(this).data('rowid')
            var el = $(this)
            if (!rowid) return;


            $.ajax({
                //type: "POST",
                method: 'DELETE',
                dataType: 'JSON',
                url: "orders/destroy/",
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

    $(function() {

        $("#addRow").click(function() {
            // ส่วนของการ clone ข้อมูลด้วย jquery clone() ค่า true คือ
            // การกำหนดให้ ไม่ต้องมีการ ดึงข้อมูลจากค่าเดิมมาใช้งาน
            // รีเซ้ตเป็นค่าว่าง ถ้ามีข้อมูลอยู่แล้ว ทั้ง select หรือ input
            // $(".firstTr1:eq(0)").clone(true)
            //     .find("input").attr("value", "").end()
            //     .find("select").attr("value", "").end()
            //     .appendTo($("#myTbl"));
            $(".firstTr:eq(0)").clone(true)

                .find("input").attr("value", "").end()
                .find("select").attr("value", "").end()
                .find("#elot_error").html('').end()
                .find("#elot_price").html('').end()
                .appendTo($("#myTbl"));


            efatotal();

        });
        $("#removeRow").click(function() {
            // // ส่วนสำหรับการลบ
            if ($("#myTbl tr").length > 2) { // จะลบรายการได้ อย่างน้อย ต้องมี 1 รายการ
                $("#myTbl tr:last").remove(); // ลบรายการสุดท้าย
                efatotal();
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
            // // ส่วนสำหรับการลบ
            if ($("#myTbl tr").length > 2) { // จะลบรายการได้ อย่างน้อย ต้องมี 1 รายการ
                //$("#myTbl tr:last").remove(); // ลบรายการสุดท้าย
                $(this).closest("tr").remove();
                efatotal();
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

        $("#addRow2").click(function() {
            // ส่วนของการ clone ข้อมูลด้วย jquery clone() ค่า true คือ
            // การกำหนดให้ ไม่ต้องมีการ ดึงข้อมูลจากค่าเดิมมาใช้งาน
            // รีเซ้ตเป็นค่าว่าง ถ้ามีข้อมูลอยู่แล้ว ทั้ง select หรือ input
            $(".firstTr3:eq(0)").clone(true)

                .find("input").attr("value", "").end()
                .find("select").attr("value", "").end()
                .find("#lot_error").html('').end()
                .find("#lot_price").html('').end()
                .appendTo($("#myTbl3"));

            fatotal();
        });
        $("#removeRow2").click(function() {
            // // ส่วนสำหรับการลบ
            if ($("#myTbl3 tr").length > 2) { // จะลบรายการได้ อย่างน้อย ต้องมี 1 รายการ
                $("#myTbl3 tr:last").remove(); // ลบรายการสุดท้าย
                fatotal();
            } else {
                // เหลือ 1 รายการลบไม่ได้
                if ($("#myTbl3 tr").length > 1) {
                    //alert("ต้องมีรายการข้อมูลอย่างน้อย 1 รายการ");
                    toastr.success('ต้องมีรายการข้อมูลอย่างน้อย 1 รายการ', {
                        timeOut: 5000
                    });
                }
            }
        });
        $(".btnRemoveg2").click(function() {
            // // ส่วนสำหรับการลบ
            if ($("#myTbl3 tr").length > 2) { // จะลบรายการได้ อย่างน้อย ต้องมี 1 รายการ
                //$("#myTbl tr:last").remove(); // ลบรายการสุดท้าย
                $(this).closest("tr").remove();
                fatotal();
            } else {
                // เหลือ 1 รายการลบไม่ได้
                if ($("#myTbl3 tr").length > 1) {
                    //alert("ต้องมีรายการข้อมูลอย่างน้อย 1 รายการ");
                    toastr.success('ต้องมีรายการข้อมูลอย่างน้อย 1 รายการ', {
                        timeOut: 5000
                    });
                }
            }
        });

        $(this).closest(".abcd").remove();

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
