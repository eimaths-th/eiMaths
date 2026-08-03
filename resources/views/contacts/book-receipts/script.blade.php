<script>
    $(document).ready(function() {
        const book_receipt_base_url = @json(rtrim(request()->getBaseUrl(), '/') . '/contacts');
        const book_receipt_shipment_id = new URLSearchParams(window.location.search).get('shipment_id');
        const csrf_token = $('meta[name="csrf-token"]').attr('content');
        const BOOK_RECEIPT_TIMESTAMP_PATTERN = /^(\d{4})-(\d{2})-(\d{2})[T ](\d{2}):(\d{2})(?::(\d{2}))?/;
        let current_student_id = null;
        let current_student_name = '';
        let last_focus_element = null;

        function FormatBookReceiptDate(created_at) {
            if (!created_at) {
                return '';
            }

            const timestamp_match = String(created_at).match(BOOK_RECEIPT_TIMESTAMP_PATTERN);
            if (!timestamp_match) {
                return String(created_at);
            }

            return timestamp_match[3] + '-' + timestamp_match[2] + '-' + timestamp_match[1] + ' ' +
                timestamp_match[4] + ':' + timestamp_match[5] + ':' + (timestamp_match[6] || '00');
        }

        function ShowBookReceiptError(message) {
            $('#BookReceiptError').text(message).removeClass('d-none');
        }

        function ClearBookReceiptError() {
            $('#BookReceiptError').text('').addClass('d-none');
        }

        function SetBookReceiptLoading(is_loading) {
            $('#BookReceiptLoading').toggleClass('d-none', !is_loading);
        }

        function BookReceiptUrl(student_id) {
            return book_receipt_base_url + '/' + encodeURIComponent(student_id) + '/book-receipts';
        }

        function BookReceiptRequestData() {
            return book_receipt_shipment_id ? {
                shipment_id: book_receipt_shipment_id
            } : {};
        }

        function RenderBookReceiptSummary(payload) {
            const summary = payload.summary || {};
            const received_count = Number(summary.received_count || 0);
            const total_count = Number(summary.total_count || 0);
            const summary_text = 'Received ' + received_count + '/' + total_count + ' books';
            const can_edit = $('#BookReceiptModal').data('can-edit') === 1 ||
                $('#BookReceiptModal').data('can-edit') === '1';
            const summary_container = $('#BookReceiptSummary').empty();

            summary_container
                .append($('<strong>').text(current_student_name || 'Student'))
                .append($('<span>').addClass('ml-2').text(summary_text));

            if (can_edit && received_count < total_count) {
                $('<button>', {
                    type: 'button',
                    class: 'btn btn-sm btn-primary ml-2 btn-mark-all-book-received',
                    'aria-label': 'Mark all books received for ' + (current_student_name || 'this student')
                }).text('Mark all received').appendTo(summary_container);
            }
        }

        function CreateBookActionButton(label, class_name, book_id) {
            return $('<button>', {
                type: 'button',
                class: 'btn btn-sm ' + class_name,
                'data-book-id': book_id
            }).text(label);
        }

        function RenderBookReceiptOrders(payload) {
            const orders_container = $('#BookReceiptOrders').empty();
            const can_edit = $('#BookReceiptModal').data('can-edit') === 1 ||
                $('#BookReceiptModal').data('can-edit') === '1';
            const orders = payload.orders || [];

            if (orders.length === 0) {
                orders_container.append($('<p>').addClass('text-muted').text('No books found.'));
                return;
            }

            orders.forEach(function(order) {
                const order_container = $('<div>').addClass('border rounded p-3 mb-3');
                $('<h6>').text(order.order_number || 'Order').appendTo(order_container);

                (order.books || []).forEach(function(book) {
                    const book_row = $('<div>').addClass(
                        'd-flex flex-wrap justify-content-between align-items-center border-top pt-2 mt-2'
                    );
                    const book_label = $('<div>').addClass('mr-2');
                    const status_label = $('<span>').addClass('badge ml-2').text(
                        book.is_received ? 'Received' : 'Not received'
                    );
                    status_label.addClass(book.is_received ? 'badge-success' : 'badge-secondary');
                    $('<span>').text(book.book_name || 'Book').appendTo(book_label);
                    status_label.appendTo(book_label);

                    const actions = $('<div>').addClass('mt-2 mt-md-0');
                    if (can_edit && !book.is_received) {
                        CreateBookActionButton('Mark received', 'btn-primary btn-mark-book-received', book.id)
                            .appendTo(actions);
                    }
                    if (can_edit && book.is_received) {
                        CreateBookActionButton('Cancel receipt', 'btn-outline-danger btn-cancel-book-receipt', book.id)
                            .appendTo(actions);
                    }
                    CreateBookActionButton('History', 'btn-outline-secondary btn-book-receipt-history', book.id)
                        .attr('data-book-name', book.book_name || 'Book')
                        .appendTo(actions);

                    book_row.append(book_label).append(actions);
                    order_container.append(book_row);
                });

                orders_container.append(order_container);
            });
        }

        function RenderBookReceiptHistory(logs, book_name) {
            const history_container = $('#BookReceiptHistoryList').empty();
            $('#BookReceiptHistoryLabel').text((book_name || 'Book') + ' - Receipt History');

            if (!logs || logs.length === 0) {
                history_container.append($('<p>').addClass('text-muted').text('No receipt history.'));
                return;
            }

            logs.forEach(function(log) {
                const action_text = log.action === 'RECEIVED' ? 'Marked as received' : 'Receipt cancelled';
                const history_row = $('<div>').addClass('border rounded p-2 mb-2');
                $('<div>').text(action_text).appendTo(history_row);
                $('<small>').addClass('text-muted').text(
                    (log.actor_name || 'Unknown user') + ' - ' + FormatBookReceiptDate(log.created_at)
                ).appendTo(history_row);
                history_container.append(history_row);
            });
        }

        function MarkAllBookReceiptsReceived(trigger_button) {
            if (!current_student_id || !window.confirm(
                'Mark all books for ' + (current_student_name || 'this student') + ' as received?'
            )) {
                return;
            }

            const action_button = $(trigger_button);
            ClearBookReceiptError();
            SetBookReceiptLoading(true);
            action_button.prop('disabled', true);
            $.ajax({
                url: BookReceiptUrl(current_student_id) + '/receive-all',
                method: 'POST',
                dataType: 'json',
                data: BookReceiptRequestData(),
                headers: {
                    'X-CSRF-TOKEN': csrf_token
                }
            }).done(function() {
                RefreshBookReceiptState();
                if ($.fn.DataTable.isDataTable('#Listview')) {
                    $('#Listview').DataTable().ajax.reload(null, false);
                }
            }).fail(function(xhr) {
                const message = xhr.responseJSON && xhr.responseJSON.error && xhr.responseJSON.error.message ?
                    xhr.responseJSON.error.message : 'Unable to mark all books as received.';
                ShowBookReceiptError(message);
            }).always(function() {
                SetBookReceiptLoading(false);
                action_button.prop('disabled', false);
            });
        }

        function RefreshBookReceiptState() {
            if (!current_student_id) {
                return;
            }

            ClearBookReceiptError();
            SetBookReceiptLoading(true);
            $.ajax({
                url: BookReceiptUrl(current_student_id),
                method: 'GET',
                dataType: 'json',
                data: BookReceiptRequestData()
            }).done(function(response) {
                const payload = response.data || {};
                RenderBookReceiptSummary(payload);
                RenderBookReceiptOrders(payload);
            }).fail(function(xhr) {
                ShowBookReceiptError('Unable to load book receipt data.');
            }).always(function() {
                SetBookReceiptLoading(false);
            });
        }

        function ChangeBookReceiptState(book_id, method, confirmation_message, trigger_button) {
            if (!current_student_id || !window.confirm(confirmation_message)) {
                return;
            }

            const action_button = $(trigger_button);
            ClearBookReceiptError();
            SetBookReceiptLoading(true);
            action_button.prop('disabled', true);
            $.ajax({
                url: BookReceiptUrl(current_student_id) + '/' + encodeURIComponent(book_id),
                method: method,
                dataType: 'json',
                data: BookReceiptRequestData(),
                headers: {
                    'X-CSRF-TOKEN': csrf_token
                }
            }).done(function() {
                RefreshBookReceiptState();
                if ($.fn.DataTable.isDataTable('#Listview')) {
                    $('#Listview').DataTable().ajax.reload(null, false);
                }
            }).fail(function(xhr) {
                const message = xhr.responseJSON && xhr.responseJSON.error && xhr.responseJSON.error.message ?
                    xhr.responseJSON.error.message : 'Unable to update book receipt status.';
                if (xhr.status === 409) {
                    RefreshBookReceiptState();
                }
                ShowBookReceiptError(message);
            }).always(function() {
                SetBookReceiptLoading(false);
                action_button.prop('disabled', false);
            });
        }

        function LoadBookReceiptHistory(book_id, book_name) {
            ClearBookReceiptError();
            SetBookReceiptLoading(true);
            $('#BookReceiptOrders, #BookReceiptSummary').addClass('d-none');
            $('#BookReceiptHistory').removeClass('d-none');
            $.ajax({
                url: BookReceiptUrl(current_student_id) + '/' + encodeURIComponent(book_id) + '/logs',
                method: 'GET',
                dataType: 'json',
                data: BookReceiptRequestData()
            }).done(function(response) {
                RenderBookReceiptHistory(response.data || [], book_name);
            }).fail(function() {
                ShowBookReceiptError('Unable to load receipt history.');
            }).always(function() {
                SetBookReceiptLoading(false);
            });
        }

        $(document).on('click', '.btn-book-receipts', function() {
            last_focus_element = this;
            current_student_id = $(this).data('student-id');
            current_student_name = $(this).data('student-name') || '';
            $('#BookReceiptModal').modal('show');
            $('#BookReceiptHistory').addClass('d-none');
            $('#BookReceiptOrders, #BookReceiptSummary').removeClass('d-none');
            RefreshBookReceiptState();
        });

        $(document).on('click', '.btn-mark-book-received', function() {
            ChangeBookReceiptState($(this).data('book-id'), 'PUT', 'Mark this book as received?', this);
        });

        $(document).on('click', '.btn-mark-all-book-received', function() {
            MarkAllBookReceiptsReceived(this);
        });

        $(document).on('click', '.btn-cancel-book-receipt', function() {
            ChangeBookReceiptState($(this).data('book-id'), 'DELETE', 'Cancel this book receipt?', this);
        });

        $(document).on('click', '.btn-book-receipt-history', function() {
            LoadBookReceiptHistory($(this).data('book-id'), $(this).data('book-name'));
        });

        $('#BookReceiptHistoryBack').on('click', function() {
            $('#BookReceiptHistory').addClass('d-none');
            $('#BookReceiptOrders, #BookReceiptSummary').removeClass('d-none');
            RefreshBookReceiptState();
        });

        $('#BookReceiptModal').on('hidden.bs.modal', function() {
            if (last_focus_element) {
                $(last_focus_element).trigger('focus');
            }
        });
    });
</script>
