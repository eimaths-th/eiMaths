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

export function BuildShareDestinationUrl(destination, media) {
    const url = encodeURIComponent(String(media?.url ?? ''));
    const caption = encodeURIComponent(String(media?.caption ?? ''));
    if (destination === 'facebook') {
        return `https://www.facebook.com/sharer/sharer.php?u=${url}`;
    }
    if (destination === 'line') {
        return `https://social-plugins.line.me/lineit/share?url=${url}&text=${caption}`;
    }
    return '';
}

export function InitializeAffiliateMediaLibrary(options = {}) {
    const root = options.Root ?? options.root ?? globalThis.document;
    if (!root?.querySelectorAll) return null;
    const fallback = options.Fallback ?? options.fallback;
    const navigator_object = options.Navigator ?? options.navigator ?? globalThis.navigator;
    const translations = options.Translations ?? options.translations ?? ReadEmbeddedTranslations(root);
    const bind = (selector, action) => root.querySelectorAll(selector).forEach((button) => {
        button.addEventListener('click', async () => {
            const card = button.closest('[data-media-card]')
                ?? button.closest?.('[data-media-share-drawer]')
                ?? button;
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

export function InitializeAffiliateMediaMenus(options = {}) {
    const root = options.Root ?? options.root ?? globalThis.document;
    if (!root?.querySelectorAll) return null;

    const toggles = Array.from(root.querySelectorAll('[data-media-menu-toggle]'));
    const menus = Array.from(root.querySelectorAll('[data-media-menu]'));

    const SetOpen = (toggle, menu, is_open) => {
        menu.hidden = !is_open;
        toggle.setAttribute('aria-expanded', is_open ? 'true' : 'false');
    };

    const CloseAll = (except_menu = null) => {
        toggles.forEach((toggle) => {
            const card = toggle.closest?.('[data-media-card]');
            const menu = card?.querySelector?.('[data-media-menu]');
            if (!menu || menu === except_menu) return;
            SetOpen(toggle, menu, false);
        });
    };

    toggles.forEach((toggle) => {
        const card = toggle.closest?.('[data-media-card]');
        const menu = card?.querySelector?.('[data-media-menu]');
        if (!menu) return;

        toggle.addEventListener('click', (event) => {
            event?.preventDefault?.();
            event?.stopPropagation?.();
            const will_open = menu.hidden;
            CloseAll(menu);
            SetOpen(toggle, menu, will_open);
            if (will_open) menu.querySelector?.('a, button')?.focus?.();
        });

        menu.querySelectorAll?.('a, button').forEach((action) => {
            action.addEventListener('click', () => SetOpen(toggle, menu, false));
        });
    });

    root.addEventListener?.('click', (event) => {
        if (!event?.target?.closest?.('[data-media-menu-wrap]')) CloseAll();
    });
    root.addEventListener?.('keydown', (event) => {
        if (event?.key !== 'Escape') return;
        CloseAll();
    });

    return { Root: root, CloseAll };
}

export function InitializeAffiliateMediaShareDrawer(options = {}) {
    const root = options.Root ?? options.root ?? globalThis.document;
    if (!root?.querySelectorAll) return null;

    const drawer = root.querySelector?.('[data-media-share-drawer]');
    const close_button = drawer?.querySelector?.('[data-media-share-close]');
    const title = drawer?.querySelector?.('[data-media-share-title]');
    const caption = drawer?.querySelector?.('[data-media-share-caption]');
    const icon = drawer?.querySelector?.('[data-media-share-icon] i');
    const facebook_link = drawer?.querySelector?.('[data-media-share-facebook]');
    const line_link = drawer?.querySelector?.('[data-media-share-line]');
    const native_share = drawer?.querySelector?.('[data-media-share]');
    const download_link = drawer?.querySelector?.('[data-media-share-download]');
    if (!drawer || !close_button || !title || !caption || !facebook_link || !line_link || !native_share || !download_link) return null;

    let active_trigger = null;

    const RestoreFocus = () => {
        const trigger = active_trigger;
        active_trigger = null;
        trigger?.focus?.();
    };

    const Close = () => {
        if (typeof drawer.close === 'function' && drawer.open) {
            drawer.close();
            return;
        }
        drawer.removeAttribute?.('open');
        RestoreFocus();
    };

    const Open = (trigger) => {
        const card = trigger?.closest?.('[data-media-card]');
        if (!card) return;

        const media = {
            title: String(card.dataset?.mediaTitle ?? ''),
            caption: String(card.dataset?.mediaCaption ?? ''),
            url: String(card.dataset?.mediaUrl ?? ''),
            downloadUrl: String(card.dataset?.mediaDownloadUrl ?? ''),
            type: String(card.dataset?.mediaType ?? ''),
        };

        active_trigger = trigger;
        title.textContent = media.title;
        caption.textContent = media.caption;
        facebook_link.href = BuildShareDestinationUrl('facebook', media);
        line_link.href = BuildShareDestinationUrl('line', media);
        download_link.href = media.downloadUrl;
        native_share.dataset.mediaTitle = media.title;
        native_share.dataset.mediaCaption = media.caption;
        native_share.dataset.mediaUrl = media.url;
        if (icon) icon.className = media.type === 'video' ? 'fas fa-video' : 'fas fa-image';

        const menu = trigger.closest?.('[data-media-menu]');
        if (menu) {
            menu.hidden = true;
            const menu_toggle = card.querySelector?.('[data-media-menu-toggle]');
            menu_toggle?.setAttribute?.('aria-expanded', 'false');
        }

        if (typeof drawer.showModal === 'function') {
            drawer.showModal();
        } else {
            drawer.setAttribute?.('open', '');
        }
        close_button.focus?.();
    };

    root.querySelectorAll('[data-media-share-panel]').forEach((trigger) => {
        trigger.addEventListener('click', () => Open(trigger));
    });
    close_button.addEventListener('click', Close);
    drawer.addEventListener('close', RestoreFocus);
    drawer.addEventListener('cancel', (event) => {
        event.preventDefault?.();
        Close();
    });
    drawer.addEventListener('click', (event) => {
        if (event.target === drawer) Close();
    });

    return { Root: root, Drawer: drawer, Open, Close };
}

export function InitializeAffiliateMediaViewer(options = {}) {
    const root = options.Root ?? options.root ?? globalThis.document;
    if (!root?.querySelectorAll) return null;

    const dialog = root.querySelector?.('[data-media-dialog]');
    const dialog_title = dialog?.querySelector?.('[data-media-dialog-title]');
    const dialog_content = dialog?.querySelector?.('[data-media-dialog-content]');
    const close_button = dialog?.querySelector?.('[data-media-dialog-close]');
    if (!dialog || !dialog_title || !dialog_content || !close_button) return null;

    const document_object = options.Document ?? options.document ?? root.ownerDocument ?? globalThis.document;
    let active_trigger = null;

    const RestoreFocus = () => {
        const trigger = active_trigger;
        active_trigger = null;
        trigger?.focus?.();
    };

    const ClearDialogContent = () => {
        if (typeof dialog_content.replaceChildren === 'function') {
            dialog_content.replaceChildren();
            return;
        }
        while (dialog_content.firstChild) {
            dialog_content.removeChild?.(dialog_content.firstChild);
        }
    };

    const HandleDialogClose = () => {
        ClearDialogContent();
        RestoreFocus();
    };

    const CloseDialog = () => {
        if (typeof dialog.close === 'function' && dialog.open) {
            dialog.close();
            return;
        }
        dialog.removeAttribute?.('open');
        HandleDialogClose();
    };

    const OpenDialog = (trigger, event) => {
        event?.preventDefault?.();
        const data = trigger?.dataset ?? {};
        const media_url = String(data.mediaViewUrl ?? '').trim();
        if (!media_url || typeof document_object?.createElement !== 'function') return;

        const media_title = String(data.mediaViewTitle ?? '');
        const media_type = data.mediaViewType === 'video' ? 'video' : 'image';
        const media_element = CreateDialogMediaElement(document_object, media_type, media_url, media_title);
        if (!media_element) return;

        active_trigger = trigger;
        dialog_title.textContent = media_title;
        ClearDialogContent();
        dialog_content.append?.(media_element);

        if (typeof dialog.showModal === 'function') {
            dialog.showModal();
        } else {
            dialog.setAttribute?.('open', '');
        }
        close_button.focus?.();
    };

    root.querySelectorAll('[data-media-view]').forEach((trigger) => {
        trigger.addEventListener('click', (event) => OpenDialog(trigger, event));
    });
    close_button.addEventListener('click', CloseDialog);
    dialog.addEventListener('close', HandleDialogClose);
    dialog.addEventListener('cancel', (event) => {
        event.preventDefault?.();
        CloseDialog();
    });
    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) CloseDialog();
    });

    return { Root: root, Dialog: dialog };
}

function CreateDialogMediaElement(document_object, media_type, media_url, media_title) {
    const media_element = document_object.createElement(media_type === 'video' ? 'video' : 'img');
    media_element.className = 'affiliate-media-dialog__asset';
    media_element.src = media_url;
    media_element.setAttribute?.('aria-label', media_title);

    if (media_type === 'video') {
        media_element.controls = true;
        media_element.preload = 'metadata';
    } else {
        media_element.alt = media_title;
        media_element.decoding = 'async';
    }

    return media_element;
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
