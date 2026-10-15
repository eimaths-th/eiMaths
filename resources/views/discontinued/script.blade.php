<script>
    const discontinuedUi = {
        confirm_restart: @json(__('students.messages.confirm_restart')),
        select_items: @json(__('students.messages.select_items')),
        confirm_action: @json(__('students.messages.confirm_action')),
        search_required: @json(__('students.messages.search_required')),
        datatable: @json(__('common.datatable')),
    };

    $(document).ready(function() {
        $(".delete_all_button").click(function() {
            var len = $('input[name="table_records[]"]:checked').length;
            if (len > 0) {
                if (confirm(discontinuedUi.confirm_restart)) {
                    $('form#delete_all').submit();
                }
            } else {
                alert(discontinuedUi.select_items);
            }
        });

        $('#check-all').click(function() {
            $(':checkbox.flat').prop('checked', this.checked);
        });
        //

        var startDate;
        var endDate;


        function datesearch() {
            var currentDate = moment();
            // Set the start date to 7 days before today
            startDate = moment('2023-01-01').format('YYYY-MM-DD');
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
                "sProcessing": discontinuedUi.datatable.processing,
                "sLengthMenu": discontinuedUi.datatable.length_menu,
                "sZeroRecords": discontinuedUi.datatable.zero_records,
                "sInfo": discontinuedUi.datatable.info,
                "sInfoEmpty": discontinuedUi.datatable.info_empty,
                "sInfoFiltered": discontinuedUi.datatable.info_filtered,
                "sInfoPostFix": discontinuedUi.datatable.info_postfix,
                "sSearch": discontinuedUi.datatable.search,
                "sUrl": discontinuedUi.datatable.url,
                "oPaginate": {
                    "sFirst": discontinuedUi.datatable.first,
                    "sPrevious": discontinuedUi.datatable.previous,
                    "sNext": discontinuedUi.datatable.next,
                    "sLast": discontinuedUi.datatable.last
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
                    data: 'discontinued_date',
                    name: 'discontinued_date'
                },
                {
                    data: 'discontinued_reason',
                    name: 'discontinued_reason'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'more',
                    name: 'more',
                    orderable: false,
                    searchable: false
                }
            ]
        };

        var table = $('#Listview').DataTable(table_option);

        $('#SearchButtons').on('click', function() {
            var searchType = $('#search_type').val();
            var keyword = $('#keyword').val();

            if (searchType !== '' && keyword !== '') {
                // Clear the previous search and any custom filters
                table.search('').columns().search('');

                // Apply the new search based on searchType and keyword
                if (searchType === '1') {
                    table.column('code:name').search(keyword).draw();
                } else if (searchType === '2') {
                    table.column('name:name').search(keyword).draw();
                }
            } else {
                // Handle the case where searchType or keyword is empty
                toastr.error(discontinuedUi.search_required, {
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


            // Clear DataTable state
            if (table) {
                table.state.clear();
                await table.destroy();
            }

            datesearch();
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

        $(document).on('click', '#getEditData', function() {
            if (!confirm(discontinuedUi.confirm_action)) return;

            var rowid = $(this).data('rowid')
            var el = $(this)
            if (!rowid) return;


            $.ajax({
                //type: "POST",
                method: 'PUT',
                dataType: 'JSON',
                url: "discontinued/restart/",
                data: {
                    id: rowid,
                    //_method: 'delete',
                    _token: token
                },
                success: function(data) {
                    console.log(data);

                    /* if (data.success) {
                        toastr.success(data.message, {
                            timeOut: 5000
                        });
                        table.row(el.parents('tr'))
                            .remove()
                            .draw();
                    } */
                    var url = "{{ route('contacts') }}";
                    window.location.href = url;
                }
            }); //end ajax
        })

    });
</script>
