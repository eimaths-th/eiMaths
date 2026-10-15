<script>
    const eiFormUi = @json(['datatable' => __('common.datatable')]);

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
                "sProcessing": eiFormUi.datatable.processing,
                "sLengthMenu": eiFormUi.datatable.length_menu,
                "sZeroRecords": eiFormUi.datatable.zero_records,
                "sInfo": eiFormUi.datatable.info,
                "sInfoEmpty": eiFormUi.datatable.info_empty,
                "sInfoFiltered": eiFormUi.datatable.info_filtered,
                "sInfoPostFix": eiFormUi.datatable.info_postfix,
                "sSearch": eiFormUi.datatable.search,
                "sUrl": eiFormUi.datatable.url,
                "oPaginate": {
                    "sFirst": eiFormUi.datatable.first,
                    "sPrevious": eiFormUi.datatable.previous,
                    "sNext": eiFormUi.datatable.next,
                    "sLast": eiFormUi.datatable.last
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
                    name: 'action',
                    orderable: false,
                    searchable: false
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
