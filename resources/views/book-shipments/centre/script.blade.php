@php
    $user_messages = [
        'receive_error' => trans('book-shipments.messages.could_not_receive', [], 'th'),
        'result_required' => 'กรุณาเลือกผลการตรวจรับให้ครบทุกรายการหนังสือ',
        'issue_note_required' => trans('book-shipments.centre.missing_or_damaged_note', [], 'th'),
        'saving' => 'กำลังบันทึกผลการรับหนังสือ…',
    ];
@endphp

<script>
    (() => {
        const receipt_form = document.querySelector('[data-receive-form]');
        const status_message = document.getElementById('centre-receipt-status');
        const user_messages = @json($user_messages);

        if (!receipt_form) {
            return;
        }

        const set_status = (message, is_error = false) => {
            if (!status_message) {
                return;
            }

            status_message.textContent = message;
            status_message.className = `centre-receipt-live mb-3 ${is_error ? 'text-danger' : 'text-muted'}`;
        };

        const request_json = async (url, options = {}) => {
            const response = await fetch(url, {
                ...options,
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'X-Requested-With': 'XMLHttpRequest',
                    ...(options.headers || {}),
                },
            });
            const payload = await response.json().catch(() => ({}));
            if (!response.ok) {
                throw new Error(payload.error?.message || user_messages.receive_error);
            }

            return payload;
        };

        receipt_form.addEventListener('submit', async (event) => {
            event.preventDefault();
            const submit_button = receipt_form.querySelector('button[type="submit"]');
            const item_results = [];
            let validation_message = '';

            receipt_form.querySelectorAll('[data-receipt-status]').forEach((status_select) => {
                const shipment_item_id = Number(status_select.dataset.receiptStatus);
                const centre_status = status_select.value;
                const note_input = receipt_form.querySelector(`[data-receipt-note="${shipment_item_id}"]`);
                const note = note_input?.value.trim() || '';

                if (!centre_status && !validation_message) {
                    validation_message = user_messages.result_required;
                }

                if (['MISSING', 'DAMAGED'].includes(centre_status) && !note && !validation_message) {
                    validation_message = user_messages.issue_note_required;
                }

                item_results.push({
                    shipment_item_id,
                    centre_status,
                    note: note || null,
                });
            });

            if (validation_message) {
                set_status(validation_message, true);
                return;
            }

            submit_button.disabled = true;
            set_status(user_messages.saving);

            try {
                await request_json(receipt_form.dataset.actionUrl, {
                    method: 'POST',
                    body: JSON.stringify({ items: item_results }),
                });
                window.location.reload();
            } catch (error) {
                set_status(error.message, true);
                submit_button.disabled = false;
            }
        });
    })();
</script>
