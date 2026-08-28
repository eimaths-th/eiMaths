const MEDIA_TYPE_DETAILS = Object.freeze({
    image: Object.freeze({
        accept: 'image/jpeg,image/png,image/gif,image/webp',
        extensions: Object.freeze(['jpg', 'jpeg', 'png', 'gif', 'webp']),
        mime_types: Object.freeze(['image/jpeg', 'image/png', 'image/gif', 'image/webp']),
        help_data_key: 'affiliateMediaImageHelpMessage',
        default_help_message: 'Image files: JPG, PNG, GIF, WebP. Maximum 50 MB.',
    }),
    video: Object.freeze({
        accept: 'video/mp4,video/quicktime,video/x-msvideo,video/x-ms-wmv',
        extensions: Object.freeze(['mp4', 'mov', 'avi', 'wmv']),
        mime_types: Object.freeze(['video/mp4', 'video/quicktime', 'video/x-msvideo', 'video/x-ms-wmv']),
        help_data_key: 'affiliateMediaVideoHelpMessage',
        default_help_message: 'Video files: MP4, MOV, AVI, WMV. Maximum 50 MB.',
    }),
});

const DEFAULT_MEDIA_TYPE = 'image';

function GetMediaTypeDetails(media_type) {
    return MEDIA_TYPE_DETAILS[media_type] ?? MEDIA_TYPE_DETAILS[DEFAULT_MEDIA_TYPE];
}

function IsCompatibleMediaFile(file, media_type) {
    if (!file) return true;

    const media_type_details = GetMediaTypeDetails(media_type);
    const file_name = String(file.name ?? '').toLowerCase();
    const file_extension = file_name.includes('.') ? file_name.split('.').pop() : '';
    const mime_type = String(file.type ?? '').toLowerCase();

    return media_type_details.extensions.includes(file_extension)
        && (mime_type === '' || media_type_details.mime_types.includes(mime_type));
}

export function InitializeAffiliateContentMediaTypeSelector({ root = globalThis.document } = {}) {
    if (!root?.querySelector) return null;

    const type_select = root.querySelector('[data-affiliate-content-media-type]');
    const file_input = root.querySelector('[data-affiliate-content-media-file]');
    const help_text = root.querySelector('[data-affiliate-content-media-help]');
    const status = root.querySelector('[data-affiliate-content-media-status]');
    if (!type_select || !file_input) return null;

    const file_cleared_message = status?.dataset?.affiliateMediaFileClearedMessage
        ?? 'The selected file was cleared because it does not match the selected media type.';

    const set_status = (message, state) => {
        if (!status) return;
        status.textContent = message;
        status.setAttribute('data-affiliate-content-media-state', state);
    };

    const apply_media_type = (media_type, clear_incompatible_file = true) => {
        const media_type_details = GetMediaTypeDetails(media_type);
        const selected_media_type = MEDIA_TYPE_DETAILS[media_type] ? media_type : DEFAULT_MEDIA_TYPE;
        const help_message = help_text?.dataset?.[media_type_details.help_data_key]
            ?? media_type_details.default_help_message;

        file_input.setAttribute('accept', media_type_details.accept);
        if (help_text) help_text.textContent = help_message;

        const selected_file = file_input.files?.[0] ?? null;
        if (clear_incompatible_file && selected_file && !IsCompatibleMediaFile(selected_file, selected_media_type)) {
            file_input.value = '';
            set_status(file_cleared_message, 'cleared');
            return;
        }

        set_status('', 'idle');
    };

    type_select.addEventListener('change', () => apply_media_type(type_select.value));
    apply_media_type(type_select.value, true);

    return { ApplyMediaType: apply_media_type };
}

if (typeof globalThis.document !== 'undefined') {
    InitializeAffiliateContentMediaTypeSelector();
}
