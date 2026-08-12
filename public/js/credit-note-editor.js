export function DecimalToMinor(value) {
    const normalized = String(value ?? '').trim();
    if (!/^\d+(?:\.\d{1,2})?$/.test(normalized)) throw new Error('Invalid decimal amount');
    const [whole, fraction = ''] = normalized.split('.');
    return (BigInt(whole) * 100n) + BigInt((fraction + '00').slice(0, 2));
}

export function PreviewTotals(values) {
    return values.reduce((total, value) => total + DecimalToMinor(value), 0n);
}

export function SetFeedback(message) {
    const target = document.getElementById('credit-note-editor-feedback');
    if (target) target.textContent = String(message);
}

if (typeof document !== 'undefined') {
    const form = document.getElementById('credit-note-editor');
    if (form) form.addEventListener('submit', () => SetFeedback('Saving Credit Note Draft…'));
}
