@php
    $detect = new Detection\MobileDetect();
@endphp
<script>
    var $eventLog = $(".js-event-log");

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


    $(document).ready(function() {
        let id;

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
                    data: 'invoice_number',
                    name: 'invoice_number'
                },
                {
                    data: 'invoice_date',
                    name: 'invoice_date'
                },
                {
                    data: 'level',
                    name: 'level'
                },
                {
                    data: 'total_fee',
                    name: 'total_fee'
                },
                {
                    data: 'order_term',
                    name: 'order_term'
                },
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
            window.location.href = "/invoices/show/" + pdid + "/1";
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
                url: "invoices/show/" + pid + '/0',
                method: 'GET',
                success: function(res) {
                    $('#print_data').html(res.html);
                    $('#PrintModal').modal('show');
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
