dataTableOptions.ajax = {
    url: $('#datatable_url').val(),
    data: function (d) {
        d.filter_date = $('input[name="date_range_filter"]').val();
    },
}
dataTableOptions.processing = true
dataTableOptions.serverSide = true

dataTableOptions.columns = [
    {data: 'DT_RowIndex', name: 'id'},
    {data: 'date', name: 'date'},
    {data: 'amount', name: 'amount'},
    {data: 'payment_type', name: 'payment_type'},
    {data: 'user.name', name: 'user.name'},
    {data: 'action', name: 'action'},
];
dataTableOptions = updateColumnExportOption(dataTableOptions, [0, 1, 2, 3, 4,]);

$('#lms_table').DataTable(dataTableOptions);
