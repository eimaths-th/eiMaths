export function DecimalToMinor(value) {
    const normalized = String(value ?? '').trim();
    if (!/^\d+(?:\.\d{1,2})?$/.test(normalized)) throw new Error('Invalid decimal amount');
    const [whole, fraction = ''] = normalized.split('.');
    return (BigInt(whole) * 100n) + BigInt((fraction + '00').slice(0, 2));
}

export function PreviewTotals(values) {
    return values.reduce((total, value) => total + DecimalToMinor(value), 0n);
}

export function SyncReasonDetailsField(reason_code_control, reason_details_control, reason_details_label, required_indicator, custom_label, details_label) {
    const custom_reason_is_selected = String(reason_code_control.value) === 'other';

    reason_details_control.required = custom_reason_is_selected;
    reason_details_label.textContent = custom_reason_is_selected ? custom_label : details_label;
    required_indicator.hidden = !custom_reason_is_selected;
}

export function SetFeedback(message) {
    const target = document.getElementById('credit-note-editor-feedback');
    if (target) target.textContent = String(message);
}

export function SyncBuyerTaxFields(vat_registered_control, field_groups) {
    const is_vat_registered = String(vat_registered_control.value) === '1';

    vat_registered_control.setAttribute('aria-expanded', String(is_vat_registered));
    for (const field_group of field_groups) {
        field_group.hidden = !is_vat_registered;
        field_group.setAttribute('aria-hidden', String(!is_vat_registered));

        for (const form_control of field_group.querySelectorAll('[data-required-when-vat-registered]')) {
            form_control.disabled = !is_vat_registered;
            form_control.required = is_vat_registered;
        }
    }
}

export function InitializeBuyerTaxFields(root_document) {
    const vat_registered_control = root_document.getElementById('buyer_is_vat_registered');
    if (!vat_registered_control) return;

    const field_groups = root_document.querySelectorAll('[data-buyer-tax-field]');
    const SyncFields = () => SyncBuyerTaxFields(vat_registered_control, field_groups);

    vat_registered_control.addEventListener('change', SyncFields);
    SyncFields();
}

export function InitializeReasonFields(root_document) {
    const reason_code_control = root_document.getElementById('reason_code');
    const reason_details_control = root_document.getElementById('reason_details');
    const reason_details_label = root_document.getElementById('reason-details-label-text');
    const required_indicator = root_document.querySelector('[data-reason-details-required]');

    if (!reason_code_control || !reason_details_control || !reason_details_label || !required_indicator) return;

    const SyncFields = () => SyncReasonDetailsField(
        reason_code_control,
        reason_details_control,
        reason_details_label,
        required_indicator,
        reason_details_label.dataset.customLabel,
        reason_details_label.dataset.detailsLabel,
    );

    reason_code_control.addEventListener('change', SyncFields);
    SyncFields();
}

if (typeof document !== 'undefined') {
    InitializeBuyerTaxFields(document);
    InitializeReasonFields(document);
    const form = document.getElementById('credit-note-editor');
    if (form) {
        const feedback = document.getElementById('credit-note-editor-feedback');
        form.addEventListener('submit', () => SetFeedback(feedback?.dataset.savingMessage || ''));
    }
}
