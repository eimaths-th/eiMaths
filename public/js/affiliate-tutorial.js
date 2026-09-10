export function TutorialIndex(steps, key) {
    return Math.max(0, steps.findIndex(step => step.key === key));
}

export function TutorialUrl(url, origin, continueTour = true) {
    const target = new URL(url, origin);
    if (target.origin !== new URL(origin).origin) throw new Error('Invalid tutorial destination');
    if (continueTour) target.searchParams.set('affiliate_tutorial', '1');
    return target.href;
}

export function PlaceTutorial(rect, panel, viewport) {
    const pad = 12, gap = 16;
    const clamp = (value, low, high) => Math.max(low, Math.min(value, Math.max(low, high)));
    let left = (viewport.width - panel.width) / 2, top = (viewport.height - panel.height) / 2;
    if (rect) {
        if (rect.right + panel.width + gap + pad <= viewport.width) {
            left = rect.right + gap; top = rect.top;
        } else if (rect.left - panel.width - gap >= pad) {
            left = rect.left - panel.width - gap; top = rect.top;
        } else if (rect.bottom + gap + panel.height <= viewport.height - pad) {
            left = rect.left; top = rect.bottom + gap;
        } else if (rect.top - gap - panel.height >= pad) {
            left = rect.left; top = rect.top - gap - panel.height;
        } else {
            top = viewport.height - panel.height - pad;
        }
    }
    return { left: clamp(left, pad, viewport.width - panel.width - pad), top: clamp(top, pad, viewport.height - panel.height - pad) };
}

// Dismissal invalidates an in-flight save even if a transport ignores cancellation.
export function TutorialOperations() {
    let generation = 0, active = null;
    return {
        begin() { active?.abort(); active = new AbortController(); return { generation: ++generation, signal: active.signal }; },
        current(operation) { return operation.generation === generation; },
        cancel() { ++generation; active?.abort(); active = null; },
    };
}

export async function SaveTutorialProgress(config, action, step, csrf, signal, transport = fetch) {
    const response = await transport(config.progressUrl, {
        method: 'POST', credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
        body: JSON.stringify({ version: config.version, action, step }), signal,
    });
    if (!response.ok) throw new Error('Unable to save tutorial progress');
    const result = await response.json();
    if (!['in_progress', 'paused', 'skipped', 'completed'].includes(result.status) || typeof result.step !== 'string') {
        throw new Error('Invalid tutorial progress response');
    }
    return result;
}

export function InitializeAffiliateTutorial(root = document) {
    const configNode = root.getElementById('affiliate-tutorial-config');
    const dialog = root.getElementById('affiliate-tutorial');
    if (!configNode || !dialog || dialog.dataset.initialized) return null;
    dialog.dataset.initialized = 'true';
    const config = JSON.parse(configNode.textContent), steps = config.steps, labels = config.labels;
    if (!steps.length || typeof dialog.showModal !== 'function') return null;
    const get = selector => dialog.querySelector(selector);
    const panel = get('[data-tour-panel]'), title = get('#affiliate-tutorial-title'), body = get('#affiliate-tutorial-body');
    const error = get('[data-tour-error]'), next = get('[data-tour-next]'), back = get('[data-tour-back]');
    const skip = get('[data-tour-skip]'), close = get('[data-tour-close]'), chapters = get('[data-tour-chapter]');
    const hero = get('[data-tour-hero]'), example = get('[data-tour-example]'), exampleContent = get('[data-tour-example-content]');
    const ring = get('[data-tour-ring]'), cutout = get('[data-tour-cutout]'), hint = get('[data-tour-hint]');
    const saving = get('[data-tour-saving]'), zoom = root.getElementById('affiliate-tutorial-example');
    const operations = TutorialOperations();
    let progress = config.progress, index = TutorialIndex(steps, progress?.step);
    let busy = false, choosing = false, target = null, frame = null, launcher = null, ownedDrawer = null;
    let dismissal = null;
    try { dismissal = JSON.parse(sessionStorage.getItem(config.storageKey)); } catch { /* Storage is optional. */ }

    function remember(value) {
        dismissal = value;
        try {
            if (value) sessionStorage.setItem(config.storageKey, JSON.stringify(value));
            else sessionStorage.removeItem(config.storageKey);
        } catch { /* Closing a tour must also work when storage is unavailable. */ }
    }

    const visible = node => node && node.getClientRects().length > 0 && getComputedStyle(node).visibility !== 'hidden';
    const pageMatches = step => new URL(step.url, location.href).pathname === location.pathname;
    function setBusy(value) {
        busy = value;
        panel.setAttribute('aria-busy', String(value));
        [next, back, chapters].forEach(button => { button.disabled = value; });
        saving.hidden = !value;
        // Pause and Skip remain usable during slow or failed requests.
    }

    function feedback(message) {
        const node = root.querySelector('[data-tour-feedback]');
        node.textContent = message; node.hidden = false;
        window.setTimeout(() => { node.hidden = true; }, 8000);
    }

    function repaint() {
        if (!dialog.open) return;
        const viewport = { width: dialog.clientWidth, height: dialog.clientHeight };
        const bounds = visible(target) ? target.getBoundingClientRect() : null;
        const position = PlaceTutorial(bounds, { width: panel.offsetWidth, height: panel.offsetHeight }, viewport);
        panel.style.left = `${position.left}px`; panel.style.top = `${position.top}px`;
        ring.hidden = !bounds;
        if (bounds) {
            const x = Math.max(3, bounds.left - 7), y = Math.max(3, bounds.top - 7);
            const width = Math.max(0, Math.min(viewport.width - x - 3, bounds.right + 7 - x));
            const height = Math.max(0, Math.min(viewport.height - y - 3, bounds.bottom + 7 - y));
            for (const [name, value] of Object.entries({ x, y, width, height })) cutout.setAttribute(name, value);
            Object.assign(ring.style, { left: `${x}px`, top: `${y}px`, width: `${width}px`, height: `${height}px` });
        } else {
            cutout.setAttribute('width', '0'); cutout.setAttribute('height', '0');
        }
    }
    function schedulePaint() {
        if (frame) cancelAnimationFrame(frame);
        frame = requestAnimationFrame(() => { frame = null; repaint(); });
    }
    function fitTarget() {
        if (!dialog.open || !visible(target)) return;
        target.scrollIntoView({ block: 'center', inline: 'nearest', behavior: 'instant' });
        const rect = target.getBoundingClientRect();
        const side = rect.left >= panel.offsetWidth + 28 || dialog.clientWidth - rect.right >= panel.offsetWidth + 28;
        if (!side) {
            const desiredTop = Math.max(20, (dialog.clientHeight - panel.offsetHeight - Math.min(rect.height, Math.max(0, dialog.clientHeight - panel.offsetHeight - 40)) - 32) / 2);
            window.scrollBy({ top: rect.top - desiredTop, behavior: 'instant' });
        }
    }

    function cleanupPreview() {
        if (ownedDrawer?.open) ownedDrawer.close();
        if (ownedDrawer) delete ownedDrawer.dataset.tourPreview;
        ownedDrawer = null;
    }
    function preparePreview(step) {
        cleanupPreview();
        if (step.key !== 'share_options') return;
        const trigger = root.querySelector('[data-media-card] .affiliate-media-card__footer [data-media-share-panel]');
        const drawer = root.querySelector('[data-media-share-drawer]');
        if (trigger && drawer && !drawer.open) {
            drawer.dataset.tourPreview = "true";
            trigger.click(); // Opens only the local chooser; never triggers a social share.
            if (drawer.open) {
                ownedDrawer = drawer;
                if (dialog.open) dialog.close(); // Return the tutorial to the top modal layer.
            }
        }
    }

    function stepTarget(step) {
        const selector = window.innerWidth <= 575 && step.mobileTarget ? step.mobileTarget : step.target;
        return selector ? root.querySelector(selector) : null;
    }

    function show(resume = false) {
        choosing = resume;
        const step = steps[index];
        if (!resume) preparePreview(step); else cleanupPreview();
        error.hidden = true;
        title.textContent = resume ? labels.resumeTitle : step.title;
        target = !resume ? stepTarget(step) : null;
        const missing = !resume && !!step.target && !visible(target);
        if (missing) target = step.fallback ? root.querySelector(step.fallback) : null;
        body.textContent = resume ? labels.resumeBody : missing ? (step.emptyBody ?? `${step.body} ${labels.missing}`) : step.body;
        hint.hidden = resume || ['welcome', 'done'].includes(step.key);
        next.textContent = resume ? labels.resume : step.key === 'done' ? labels.finish : step.key === 'welcome' ? labels.start : labels.next;
        back.hidden = !resume && index === 0; back.textContent = resume ? labels.restart : labels.back;
        chapters.value = step.chapter;
        get('[data-tour-progress]').textContent = labels.progress.replace(':current', index + 1).replace(':total', steps.length);
        get('[data-tour-meter]').setAttribute('aria-valuemax', steps.length);
        get('[data-tour-meter]').setAttribute('aria-valuenow', index + 1);
        get('[data-tour-bar]').style.width = `${(index + 1) / steps.length * 100}%`;
        hero.hidden = resume || !step.image;
        if (!hero.hidden) hero.src = step.image;
        example.open = false;
        exampleContent.replaceChildren();
        const template = !resume && step.example ? root.getElementById(`affiliate-example-${step.example}`) : null;
        example.hidden = !template;
        if (template) exampleContent.append(template.content.cloneNode(true));
        if (!dialog.open) dialog.showModal();
        panel.scrollTop = 0;
        fitTarget(); repaint(); schedulePaint(); title.focus({ preventScroll: true });
    }

    async function request(action, step, signal) {
        return SaveTutorialProgress(config, action, step, root.querySelector('meta[name="csrf-token"]')?.content ?? '', signal);
    }

    function dismiss(action = 'pause') {
        operations.cancel();
        setBusy(false); cleanupPreview();
        if (zoom.open) zoom.close();
        const key = steps[index].key;
        progress = { status: action === 'skip' ? 'skipped' : 'paused', step: key };
        remember(progress);
        dialog.close();
        feedback(action === 'skip' ? labels.skipped : labels.paused);
        (launcher ?? root.querySelector('[data-affiliate-tutorial-start]'))?.focus({ preventScroll: true });
        const operation = operations.begin();
        const timer = window.setTimeout(() => operations.current(operation) && operations.cancel(), 12000);
        void request(action, key, operation.signal).catch(() => {
            if (!dialog.open) feedback(labels.unsaved);
        }).finally(() => window.clearTimeout(timer));
    }

    async function transition(action, to = index) {
        if (busy) return;
        const operation = operations.begin();
        setBusy(true); error.hidden = true;
        const timer = window.setTimeout(() => {
            if (operations.current(operation)) {
                operations.cancel(); setBusy(false); error.textContent = labels.error; error.hidden = false; schedulePaint();
            }
        }, 12000);
        try {
            const effectiveAction = action === 'advance' && progress?.status !== 'in_progress' ? 'start' : action;
            const result = await request(effectiveAction, steps[to].key, operation.signal);
            if (!operations.current(operation) || !dialog.open) return;
            progress = result; index = TutorialIndex(steps, result.step); choosing = false; remember(null);
            if (action === 'finish') {
                cleanupPreview(); dialog.close(); feedback(labels.completed);
                (launcher ?? root.querySelector('[data-affiliate-tutorial-start]'))?.focus({ preventScroll: true });
            } else if (pageMatches(steps[index])) show();
            else location.assign(TutorialUrl(steps[index].url, location.origin));
        } catch {
            if (operations.current(operation) && dialog.open) {
                error.textContent = labels.error; error.hidden = false; schedulePaint();
            }
        } finally {
            window.clearTimeout(timer);
            if (operations.current(operation)) setBusy(false);
        }
    }

    const chapterKeys = [...new Set(steps.map(step => step.chapter))];
    chapterKeys.forEach((key, position) => {
        const option = root.createElement('option'); option.value = key; option.textContent = `${position + 1}. ${config.chapters[key]}`; chapters.append(option);
    });
    chapters.addEventListener('change', () => void transition('start', steps.findIndex(step => step.chapter === chapters.value)));
    root.querySelectorAll('[data-affiliate-tutorial-start]').forEach(button => {
        button.disabled = false; button.removeAttribute('aria-busy');
        button.addEventListener('click', () => {
            operations.cancel(); setBusy(false); launcher = button;
            const remembered = dismissal ?? progress;
            if (remembered && ['in_progress', 'paused'].includes(remembered.status)) {
                index = TutorialIndex(steps, remembered.step); show(true);
            } else { index = 0; show(); }
        });
    });
    next.addEventListener('click', () => {
        if (choosing) void transition('start', index);
        else if (index === steps.length - 1) void transition('finish');
        else void transition('advance', index + 1);
    });
    back.addEventListener('click', () => {
        if (choosing) void transition('start', 0);
        else if (index > 0) void transition('advance', index - 1);
    });
    skip.addEventListener('click', () => dismiss('skip'));
    close.addEventListener('click', () => dismiss());
    dialog.addEventListener('cancel', event => { event.preventDefault(); dismiss(); });
    get('[data-tour-enlarge]').addEventListener('click', () => {
        zoom.querySelector('[data-tour-zoom-content]').replaceChildren(...Array.from(exampleContent.childNodes, node => node.cloneNode(true)));
        zoom.showModal();
    });
    zoom.querySelector('[data-tour-zoom-close]').addEventListener('click', () => zoom.close());
    zoom.addEventListener('close', () => get('[data-tour-enlarge]').focus());
    window.addEventListener('scroll', schedulePaint, { passive: true });
    const refit = () => {
        if (!choosing) {
            target = stepTarget(steps[index]);
            if (!visible(target) && steps[index].fallback) target = root.querySelector(steps[index].fallback);
        }
        fitTarget(); schedulePaint();
    };
    window.addEventListener('resize', refit, { passive: true });
    window.visualViewport?.addEventListener('resize', refit);
    new ResizeObserver(refit).observe(panel);
    hero.addEventListener('load', schedulePaint);
    hero.addEventListener('error', () => { hero.hidden = true; schedulePaint(); });
    example.addEventListener('toggle', schedulePaint);
    window.addEventListener('pageshow', event => { if (event.persisted) location.reload(); });
    const continuation = new URL(location.href).searchParams.get('affiliate_tutorial') === '1';
    if (continuation) {
        const url = new URL(location.href); url.searchParams.delete('affiliate_tutorial'); history.replaceState(history.state, '', url);
    }
    if (!dismissal && progress?.status === 'in_progress') show(!(continuation && pageMatches(steps[index])));
    else if (!dismissal && config.autoStart) { index = 0; show(); }
    return { dialog, dismiss };
}

if (typeof document !== 'undefined') {
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', () => InitializeAffiliateTutorial(), { once: true });
    else InitializeAffiliateTutorial();
}
