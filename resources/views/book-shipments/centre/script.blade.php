<script>
    (() => {
        const receipt_form = document.querySelector('[data-receive-form]');
        const status_message = document.getElementById('centre-receipt-status');

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
                throw new Error(payload.error?.message || 'The shipment could not be received.');
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
                    validation_message = 'Choose a result for every book.';
                }

                if (['MISSING', 'DAMAGED'].includes(centre_status) && !note && !validation_message) {
                    validation_message = 'Missing and damaged books require a note.';
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
            set_status('Saving receipt…');

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
