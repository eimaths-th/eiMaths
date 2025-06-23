<script>
    $(document).ready(function() {
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
                "sProcessing": "Processcing...",
                "sLengthMenu": "Display_MENU_ Row",
                "sZeroRecords": "No Data Fount",
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
                /*     {
                        data: 'id',
                        name: 'id'
                    }, */
                {
                    data: 'ref',
                    name: 'ref'
                },
                {
                    data: 'order_id',
                    name: 'order_id'
                },
                {
                    data: 'reciept_id',
                    name: 'reciept_id'
                },
                {
                    data: 'status',
                    name: 'status'
                },
                {
                    data: 'commission_amount',
                    name: 'commission_amount'
                },
                {
                    data: 'payment_to',
                    name: 'payment_to'
                },
                {
                    data: 'approved_by',
                    name: 'approved_by'
                },
                {
                    data: 'created_at',
                    name: 'created_at'
                },
                {
                    data: 'action',
                    name: 'action'
                },
            ]
        });

    });

    $(document).on('click', '.approve-btn, .reject-btn', function(e) {
        e.preventDefault();
        let id = $(this).data('id');
        let action = $(this).hasClass('approve-btn') ? 'approve' : 'reject';

        $.ajax({
            url: '{{ route('commission-list.updateStatus') }}', // Update this to your actual endpoint
            method: 'POST',
            data: {
                id: id,
                action: action,
                _token: $('meta[name="csrf-token"]').attr('content'),
            },
            success: function(response) {
                toastr.success(response.message, {
                    timeOut: 5000
                });
                $('#Listview').DataTable().ajax.reload();

            },
            error: function(xhr) {
                console.error(xhr.responseText);

                let errorMessage = "An error occurred.";
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                }

                toastr.error(errorMessage, {
                    timeOut: 5000
                });
            }
        });
    });

    $(document).on('click', '.view-btn', function(e) {
        e.preventDefault();
        let id = $(this).data('id');
        let action = $(this).hasClass('view-btn') ? 'view' : 'print';

        $.ajax({
            url: '{{ route('commission-list.view') }}', // Update this to your actual endpoint
            method: 'POST',
            data: {
                id: id,
                action: action,
                _token: $('meta[name="csrf-token"]').attr('content'),
            },
            success: function(response) {
                $('#print_data').html(response.html);
                $('#PrintModal').modal('show');

            },
            error: function(xhr) {
                console.error(xhr.responseText);

                let errorMessage = "An error occurred.";
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                }

                toastr.error(errorMessage, {
                    timeOut: 5000
                });
            }
        });
    });
</script>
