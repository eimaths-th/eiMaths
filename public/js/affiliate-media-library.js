export async function CopyText(text, options = {}) {
    const navigator_object = options.Navigator ?? options.navigator ?? globalThis.navigator;
    const fallback = options.Fallback ?? options.fallback;
    if (navigator_object?.clipboard?.writeText) {
        await navigator_object.clipboard.writeText(String(text));
        return { status: 'copied', value: String(text) };
    }
    if (typeof fallback !== 'function') throw new Error('Clipboard is unavailable.');
    await fallback(String(text));
    return { status: 'copied', value: String(text) };
}

export async function ShareMedia(media, options = {}) {
    const navigator_object = options.Navigator ?? options.navigator ?? globalThis.navigator;
    const copy = options.Copy ?? options.copy ?? ((value) => CopyText(value, options));
    if (typeof navigator_object?.share === 'function') {
        try {
            await navigator_object.share({ title: media.title, text: media.caption, url: media.url });
            return { status: 'shared' };
        } catch (error) {
            if (error?.name === 'AbortError') return { status: 'cancelled' };
        }
    }
    await copy(media.url);
    return { status: 'copied' };
}

export function ResolveMediaAction(card, action) {
    const data = card?.dataset ?? {};
    if (action === 'caption') return { type: 'copy', value: String(data.mediaCopyCaption ?? '') };
    if (action === 'link') return { type: 'copy', value: String(data.mediaCopyLink ?? '') };
    if (action === 'share') return {
        type: 'share',
        title: String(data.mediaTitle ?? ''),
        caption: String(data.mediaCaption ?? ''),
        url: String(data.mediaUrl ?? ''),
    };
    return null;
}

export function InitializeAffiliateMediaLibrary(options = {}) {
    const root = options.Root ?? options.root ?? globalThis.document;
    if (!root?.querySelectorAll) return null;
    const fallback = options.Fallback ?? options.fallback;
    const navigator_object = options.Navigator ?? options.navigator ?? globalThis.navigator;
    const bind = (selector, action) => root.querySelectorAll(selector).forEach((button) => {
        button.addEventListener('click', async () => {
            const card = button.closest('[data-media-card]') ?? button;
            const resolved = ResolveMediaAction(card, action);
            if (!resolved) return;
            const feedback = card.querySelector?.('[data-media-feedback]');
            const previously_disabled = button.disabled;
            button.disabled = true;
            button.setAttribute('aria-busy', 'true');
            try {
                if (resolved.type === 'copy') {
                    await CopyText(resolved.value, { Navigator: navigator_object, Fallback: fallback });
                    if (feedback) feedback.textContent = 'Copied.';
                } else {
                    const result = await ShareMedia(resolved, { Navigator: navigator_object, Copy: (value) => CopyText(value, { Navigator: navigator_object, Fallback: fallback }) });
                    if (feedback) feedback.textContent = result.status === 'cancelled' ? '' : result.status === 'shared' ? 'Shared.' : 'Tracking link copied.';
                }
            } catch {
                if (feedback) feedback.textContent = 'Action unavailable.';
            } finally {
                button.disabled = previously_disabled;
                button.removeAttribute('aria-busy');
                button.focus?.();
            }
        });
    });
    bind('[data-media-copy-caption]', 'caption');
    bind('[data-media-copy-link]', 'link');
    bind('[data-media-share]', 'share');
    return { Root: root };
}
