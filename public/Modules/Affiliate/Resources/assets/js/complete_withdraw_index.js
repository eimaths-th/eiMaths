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
    {data: 'request_date', name: 'request_date'},
    {data: 'amount', name: 'amount'},
    {data: 'payment_type', name: 'payment_type'},
    {data: 'user.name', name: 'user.name'},
    {data: 'confirm_date', name: 'confirm_date'},
    {data: 'confirmedUser', name: 'confirmedUser.name'},
];
dataTableOptions = updateColumnExportOption(dataTableOptions, [0, 1, 2, 3, 4, 5, 6]);

$('#lms_table').DataTable(dataTableOptions);

console.log(dataTableOptions)
