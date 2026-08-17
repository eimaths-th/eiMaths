export async function CopyText(text, options = {}) {
    const copy_value = String(text);
    const navigator_object = options.Navigator ?? options.navigator ?? globalThis.navigator;
    const document_object = options.Document ?? options.document ?? globalThis.document;
    const fallback = options.Fallback ?? options.fallback;
    const document_copy_succeeded = TryDocumentCopy(copy_value, document_object);
    if (navigator_object?.clipboard?.writeText) {
        try {
            await navigator_object.clipboard.writeText(copy_value);
            return { status: 'copied', value: copy_value };
        } catch {
            // Continue with the document or injected fallback below.
        }
    }
    if (document_copy_succeeded) {
        return { status: 'copied', value: copy_value };
    }
    if (typeof fallback === 'function') {
        await fallback(copy_value);
        return { status: 'copied', value: copy_value };
    }
    throw new Error('Clipboard is unavailable.');
}

function TryDocumentCopy(copy_value, document_object) {
    if (
        !document_object?.body
        || typeof document_object.createElement !== 'function'
        || typeof document_object.execCommand !== 'function'
    ) {
        return false;
    }

    const text_area = document_object.createElement('textarea');
    text_area.value = copy_value;
    text_area.setAttribute?.('readonly', '');
    if (text_area.style) {
        text_area.style.position = 'fixed';
        text_area.style.opacity = '0';
        text_area.style.pointerEvents = 'none';
    }
    document_object.body.appendChild(text_area);
    text_area.select?.();
    text_area.setSelectionRange?.(0, copy_value.length);

    try {
        return document_object.execCommand('copy') === true;
    } catch {
        return false;
    } finally {
        if (typeof text_area.remove === 'function') {
            text_area.remove();
        } else {
            document_object.body.removeChild?.(text_area);
        }
    }
}
