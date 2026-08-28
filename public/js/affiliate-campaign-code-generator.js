const CAMPAIGN_CODE_PATTERN = /^[a-z0-9]+(?:-[a-z0-9]+)*$/;

export function ValidateCampaignCodeResponse(payload) {
    const campaign_code = payload?.campaign_code;
    if (typeof campaign_code !== 'string' || !CAMPAIGN_CODE_PATTERN.test(campaign_code)) {
        throw new Error('Invalid campaign code response.');
    }

    return campaign_code;
}

export async function RequestUniqueCampaignCode(endpoint, options = {}) {
    const fetch_implementation = options.Fetch ?? options.fetch ?? globalThis.fetch;
    if (typeof fetch_implementation !== 'function') {
        throw new Error('Campaign code generation is unavailable.');
    }

    let response;
    try {
        response = await fetch_implementation(endpoint, {
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
    } catch {
        throw new Error('Campaign code generation request failed.');
    }

    if (!response?.ok) {
        throw new Error(`Campaign code generation failed with status ${response?.status ?? 'unknown'}.`);
    }

    let payload;
    try {
        payload = await response.json();
    } catch {
        throw new Error('Invalid campaign code response.');
    }

    return ValidateCampaignCodeResponse(payload);
}

export function InitializeAffiliateCampaignCodeGenerator(options = {}) {
    const root = options.Root ?? options.root ?? globalThis.document;
    if (!root?.querySelector) return null;

    const button = options.Button ?? root.querySelector('[data-affiliate-campaign-code-generate]');
    const input = options.Input ?? root.querySelector('[data-affiliate-campaign-code-input]');
    const status = options.Status ?? root.querySelector('[data-affiliate-campaign-code-status]');
    const endpoint = options.Endpoint ?? button?.dataset?.affiliateCampaignCodeEndpoint;
    if (!button || !input || !endpoint) return null;

    const event_constructor = options.Event ?? globalThis.Event;
    const generating_message = button.dataset?.affiliateCampaignCodeGeneratingMessage ?? 'Generating campaign code.';
    const generated_message = button.dataset?.affiliateCampaignCodeGeneratedMessage ?? 'New campaign code generated.';
    const failed_message = button.dataset?.affiliateCampaignCodeGenerationFailedMessage ?? 'Unable to generate a campaign code. Please try again.';

    const set_status = (message, state) => {
        if (!status) return;
        status.textContent = message;
        status.setAttribute('data-affiliate-campaign-code-state', state);
    };

    const notify_input_changed = () => {
        if (typeof input.dispatchEvent !== 'function' || typeof event_constructor !== 'function') return;
        input.dispatchEvent(new event_constructor('input', { bubbles: true }));
    };

    const generate = async () => {
        if (button.disabled) return;
        const previously_disabled = button.disabled;
        button.disabled = true;
        button.setAttribute('aria-busy', 'true');
        set_status(generating_message, 'loading');

        try {
            const campaign_code = await RequestUniqueCampaignCode(endpoint, options);
            input.value = campaign_code;
            notify_input_changed();
            set_status(generated_message, 'success');
            input.focus?.();
        } catch {
            set_status(failed_message, 'error');
        } finally {
            button.disabled = previously_disabled;
            button.removeAttribute('aria-busy');
        }
    };

    button.addEventListener('click', generate);
    return { Generate: generate };
}

if (typeof globalThis.document !== 'undefined') {
    InitializeAffiliateCampaignCodeGenerator();
}
