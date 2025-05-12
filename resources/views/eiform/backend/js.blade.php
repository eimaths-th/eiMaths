<script>
    $(document).ready(function() {
        // Initialize the form
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
            columns: [
                /*     {
                        data: 'id',
                        name: 'id'
                    }, */


                {
                    data: 'first_name_parent',
                    name: 'first_name_parent'
                },
                {
                    data: 'telp_parent',
                    name: 'telp_parent'
                }, 
                {
                    data: 'first_name_student',
                    name: 'first_name_student'
                }, 
                {
                    data: 'category',
                    name: 'category'
                },
                {
                    data: 'address',
                    name: 'address'
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
    });
</script>
