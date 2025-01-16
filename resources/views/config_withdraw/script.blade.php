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
                    data: 'request_date',
                    name: 'request_date'
                },
                {
                    data: 'user_id',
                    name: 'user_id'
                },
                {
                    data: 'withdraw_amount',
                    name: 'withdraw_amount'
                },
                {
                    data: 'payment_type',
                    name: 'payment_type'
                },
                {
                    data: 'account',
                    name: 'account'
                },
                {
                    data: 'status',
                    name: 'status'
                },
                {
                    data: 'confirmed_by',
                    name: 'confirmed_by'
                },
                {
                    data: 'confirm_date',
                    name: 'confirm_date'
                },
                {
                    data: 'action',
                    name: 'action'
                },
            ]
        });

    });

    $(document).on('click', '.approve-withdraw-btn, .reject-withdraw-btn', function(e) {
        e.preventDefault();
        let id = $(this).data('id');
        let action = $(this).hasClass('approve-withdraw-btn') ? 'approve' : 'reject';

        $.ajax({
            url: '{{ route('config_withdraw.approve') }}', // Dynamic route with ID
            method: 'POST',

            data: {
                id: id,
                action: action,
                _token: $('meta[name="csrf-token"]').attr('content')

            },
            success: function(response) {
                toastr.success(response.message, {
                    timeOut: 5000
                });
                $('#Listview').DataTable().ajax.reload();

                // $('#Listview').DataTable().ajax.reload(); // Reload table data
            },
            error: function(xhr) {
                console.error(xhr.responseText);
                toastr.error(xhr.responseJSON.error, 'Error');
            }
        });
    });

    // $(document).on('click', '.approve-withdraw-btn', function() {
    //     let withdrawId = $(this).data('id'); // Retrieve the ID from the button
    //     // let url = '/config-withdraw/approve/' + withdrawId;
    //     // Replace placeholder
    //     const url = "{{ route('config_withdraw.approve', ['id' => ':id']) }}".replace(':id', withdrawId);

    //     $.ajax({
    //         url: url,
    //         type: 'POST',
    //         headers: {
    //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
    //                 'content') // Include CSRF token in headers
    //         },
    //         success: function(response) {
    //             toastr.success(response.success, 'Success');
    //             // Optionally reload or update the table row
    //         },
    //         error: function(response) {
    //             toastr.error(response.responseJSON.error || 'An error occurred', 'Error');
    //         }
    //     });
    // });
</script>
