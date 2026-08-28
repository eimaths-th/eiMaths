function FindPublicationEndField(root, field_id) {
    if (!field_id) return null;
    if (typeof root?.getElementById === 'function') return root.getElementById(field_id);
    if (typeof root?.querySelector === 'function') return root.querySelector(`#${field_id}`);
    return null;
}

function ApplyPublicationWindowState(toggle, end_field) {
    const is_unlimited = toggle.checked === true;
    end_field.disabled = is_unlimited;

    if (is_unlimited) {
        end_field.value = '';
        end_field.setAttribute('aria-disabled', 'true');
        return;
    }

    end_field.removeAttribute('aria-disabled');
}

export function InitializeAffiliatePublicationWindow({ root = globalThis.document } = {}) {
    if (!root?.querySelectorAll) return [];

    const publication_windows = [];
    for (const toggle of Array.from(root.querySelectorAll('[data-affiliate-publication-unlimited-toggle]'))) {
        const field_id = toggle.dataset?.affiliatePublicationEndField;
        const end_field = FindPublicationEndField(root, field_id);
        if (!end_field) continue;

        toggle.setAttribute('aria-controls', end_field.id);
        const UpdateState = () => ApplyPublicationWindowState(toggle, end_field);
        toggle.addEventListener('change', UpdateState);
        UpdateState();
        publication_windows.push({ Toggle: toggle, EndField: end_field, ApplyState: UpdateState });
    }

    return publication_windows;
}

if (typeof globalThis.document !== 'undefined') {
    InitializeAffiliatePublicationWindow();
}
