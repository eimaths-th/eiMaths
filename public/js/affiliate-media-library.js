import { CopyText } from './affiliate-clipboard.js';

export { CopyText };

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
    const translations = options.Translations ?? options.translations ?? ReadEmbeddedTranslations(root);
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
                    SetMediaFeedback(feedback, translations.copied ?? 'Copied.', 'success');
                } else {
                    const result = await ShareMedia(resolved, { Navigator: navigator_object, Copy: (value) => CopyText(value, { Navigator: navigator_object, Fallback: fallback }) });
                    if (result.status === 'cancelled') {
                        SetMediaFeedback(feedback, '', '');
                    } else {
                        SetMediaFeedback(
                            feedback,
                            result.status === 'shared'
                                ? translations.shared ?? 'Shared.'
                                : translations.tracking_link_copied ?? 'Tracking link copied.',
                            'success',
                        );
                    }
                }
            } catch {
                SetMediaFeedback(feedback, translations.action_unavailable ?? 'Action unavailable.', 'error');
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

function SetMediaFeedback(feedback, message, state) {
    if (!feedback) return;
    feedback.textContent = message;
    if (state) {
        feedback.setAttribute('data-media-feedback-state', state);
    } else {
        feedback.removeAttribute('data-media-feedback-state');
    }
}

function ReadEmbeddedTranslations(root) {
    const node = root.querySelector?.('[data-media-translations]');
    if (!node) return {};
    try {
        const translations = JSON.parse(node.textContent ?? '{}');
        return translations && typeof translations === 'object' ? translations : {};
    } catch {
        return {};
    }
}
