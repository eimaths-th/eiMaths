// Server-owned progress keeps onboarding consistent across browsers and devices.
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
    const pad = 12;
    const gap = 16;
    const clamp = (value, low, high) => Math.max(low, Math.min(value, Math.max(low, high)));
    let left = (viewport.width - panel.width) / 2;
    let top = (viewport.height - panel.height) / 2;
    if (rect) {
        if (rect.right + panel.width + gap + pad <= viewport.width) {
            left = rect.right + gap;
            top = rect.top;
        } else if (rect.left - panel.width - gap >= pad) {
            left = rect.left - panel.width - gap;
            top = rect.top;
        } else if (rect.bottom + gap + panel.height <= viewport.height - pad) {
            left = rect.left;
            top = rect.bottom + gap;
        } else if (rect.top - gap - panel.height >= pad) {
            left = rect.left;
            top = rect.top - gap - panel.height;
        } else {
            top = viewport.height - panel.height - pad;
        }
    }
    return {
        left: clamp(left, pad, viewport.width - panel.width - pad),
        top: clamp(top, pad, viewport.height - panel.height - pad),
    };
}

export function InitializeAffiliateTutorial(root = document) {
    const configNode = root.getElementById('affiliate-tutorial-config');
    const dialog = root.getElementById('affiliate-tutorial');
    if (!configNode || !dialog || dialog.dataset.initialized) return null;
    dialog.dataset.initialized = 'true';
    const config = JSON.parse(configNode.textContent);
    const steps = config.steps;
    const labels = config.labels;
    if (!steps.length || typeof dialog.showModal !== 'function') return null;

    const get = selector => dialog.querySelector(selector);
    const panel = get('[data-tour-panel]');
    const title = get('#affiliate-tutorial-title');
    const body = get('#affiliate-tutorial-body');
    const error = get('[data-tour-error]');
    const next = get('[data-tour-next]');
    const back = get('[data-tour-back]');
    const skip = get('[data-tour-skip]');
    const hero = get('[data-tour-hero]');
    const example = get('[data-tour-example]');
    const exampleImage = get('[data-tour-example-image]');
    const ring = get('[data-tour-ring]');
    const cutout = get('[data-tour-cutout]');
    const hint = get('[data-tour-hint]');
    let progress = config.progress;
    let index = TutorialIndex(steps, progress?.step);
    let busy = false;
    let choosing = false;
    let target = null;
    let frame = null;
    let launcher = null;

    const visible = node => node && node.getClientRects().length > 0
        && getComputedStyle(node).visibility !== 'hidden';
    const pageMatches = step => new URL(step.url, location.href).pathname === location.pathname;
    const setBusy = value => {
        busy = value;
        panel.setAttribute('aria-busy', String(value));
        [next, back, skip].forEach(button => { button.disabled = value; });
    };

    function repaint() {
        if (!dialog.open) return;
        const viewport = { width: dialog.clientWidth, height: dialog.clientHeight };
        const bounds = visible(target) ? target.getBoundingClientRect() : null;
        const position = PlaceTutorial(bounds, { width: panel.offsetWidth, height: panel.offsetHeight }, viewport);
        panel.style.left = `${position.left}px`;
        panel.style.top = `${position.top}px`;
        ring.hidden = !bounds;
        if (bounds) {
            const x = Math.max(3, bounds.left - 7);
            const y = Math.max(3, bounds.top - 7);
            const width = Math.max(0, Math.min(viewport.width - x - 3, bounds.right + 7 - x));
            const height = Math.max(0, Math.min(viewport.height - y - 3, bounds.bottom + 7 - y));
            for (const [name, value] of Object.entries({ x, y, width, height })) cutout.setAttribute(name, value);
            Object.assign(ring.style, { left: `${x}px`, top: `${y}px`, width: `${width}px`, height: `${height}px` });
        } else {
            cutout.setAttribute('width', '0');
            cutout.setAttribute('height', '0');
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
        const roomOnSide = rect.left >= panel.offsetWidth + 28 || dialog.clientWidth - rect.right >= panel.offsetWidth + 28;
        if (!roomOnSide) {
            const desiredTop = Math.max(20, (dialog.clientHeight - panel.offsetHeight - Math.min(rect.height, 250) - 32) / 2);
            window.scrollBy({ top: rect.top - desiredTop, behavior: 'instant' });
        }
    }

    function show(resume = false) {
        choosing = resume;
        const step = steps[index];
        error.hidden = true;
        title.textContent = resume ? labels.resumeTitle : step.title;
        target = !resume && step.target ? root.querySelector(step.target) : null;
        const missing = !resume && !!step.target && !visible(target);
        if (missing) target = step.fallback ? root.querySelector(step.fallback) : null;
        body.textContent = resume ? labels.resumeBody : missing ? (step.emptyBody ?? `${step.body} ${labels.missing}`) : step.body;
        hint.hidden = resume || step.key === 'done';
        next.textContent = resume ? labels.resume : step.key === 'done' ? labels.finish : step.key === 'welcome' ? labels.start : labels.next;
        back.hidden = !resume && index === 0;
        back.textContent = resume ? labels.restart : labels.back;
        skip.hidden = false;
        get('[data-tour-progress]').textContent = labels.progress.replace(':current', index + 1).replace(':total', steps.length);
        get('[data-tour-meter]').setAttribute('aria-valuemax', steps.length);
        get('[data-tour-meter]').setAttribute('aria-valuenow', index + 1);
        get('[data-tour-bar]').style.width = `${(index + 1) / steps.length * 100}%`;
        const isHero = !resume && !!step.image && !step.target;
        hero.hidden = !isHero;
        if (isHero) hero.src = step.image;
        example.open = false;
        example.hidden = resume || !step.image || isHero;
        if (!example.hidden) exampleImage.src = step.image;
        if (!dialog.open) dialog.showModal();
        panel.scrollTop = 0;
        fitTarget();
        repaint();
        schedulePaint();
        title.focus({ preventScroll: true });
    }

    async function save(action, step) {
        const response = await fetch(config.progressUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': root.querySelector('meta[name="csrf-token"]')?.content ?? '' },
            body: JSON.stringify({ version: config.version, action, step }),
            signal: AbortSignal.timeout(12000),
        });
        if (!response.ok) throw new Error('Unable to save tutorial progress');
        progress = await response.json();
    }

    async function transition(action, to = index) {
        if (busy) return;
        setBusy(true);
        error.hidden = true;
        try {
            if (action === 'advance' && progress?.status !== 'in_progress') await save('start', steps[0].key);
            await save(action, steps[to].key);
            index = TutorialIndex(steps, progress.step);
            choosing = false;
            if (action === 'skip' || action === 'finish') {
                dialog.close();
                const feedback = root.querySelector('[data-tour-feedback]');
                feedback.textContent = action === 'finish' ? labels.completed : labels.skipped;
                feedback.hidden = false;
                window.setTimeout(() => { feedback.hidden = true; }, 6000);
                (launcher ?? root.querySelector('[data-affiliate-tutorial-start]'))?.focus({ preventScroll: true });
            } else if (pageMatches(steps[index])) {
                show();
            } else {
                location.assign(TutorialUrl(steps[index].url, location.origin));
            }
        } catch {
            error.textContent = labels.error;
            error.hidden = false;
            repaint();
        } finally {
            setBusy(false);
        }
    }

    root.querySelectorAll('[data-affiliate-tutorial-start]').forEach(button => {
        button.disabled = false;
        button.removeAttribute('aria-busy');
        button.addEventListener('click', () => {
            launcher = button;
            if (progress?.status === 'in_progress') {
                index = TutorialIndex(steps, progress.step);
                show(true);
            } else {
                index = 0;
                show();
                void transition('start', 0);
            }
        });
    });
    next.addEventListener('click', () => {
        if (choosing) void transition('advance', index);
        else if (index === steps.length - 1) void transition('finish');
        else void transition('advance', index + 1);
    });
    back.addEventListener('click', () => {
        if (choosing) void transition('start', 0);
        else if (index > 0) void transition('advance', index - 1);
    });
    skip.addEventListener('click', () => void transition('skip'));
    dialog.addEventListener('cancel', event => { event.preventDefault(); void transition('skip'); });
    window.addEventListener('scroll', schedulePaint, { passive: true });
    const refit = () => { fitTarget(); schedulePaint(); };
    window.addEventListener('resize', refit, { passive: true });
    window.visualViewport?.addEventListener('resize', refit);
    const observer = new ResizeObserver(refit);
    observer.observe(panel);
    hero.addEventListener('load', schedulePaint);
    hero.addEventListener('error', () => { hero.hidden = true; schedulePaint(); });
    exampleImage.addEventListener('error', () => { example.hidden = true; schedulePaint(); });
    example.addEventListener('toggle', schedulePaint);
    // A cached page must not replay a stale in-progress state after Finish/Skip.
    window.addEventListener('pageshow', event => { if (event.persisted) location.reload(); });

    const continuation = new URL(location.href).searchParams.get('affiliate_tutorial') === '1';
    if (continuation) {
        const cleanUrl = new URL(location.href);
        cleanUrl.searchParams.delete('affiliate_tutorial');
        history.replaceState(history.state, '', cleanUrl);
    }
    if (progress?.status === 'in_progress') {
        show(!(continuation && pageMatches(steps[index])));
    } else if (config.autoStart) {
        index = 0;
        show();
        void transition('start', 0);
    }
    return { dialog };
}

if (typeof document !== 'undefined') {
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', () => InitializeAffiliateTutorial(), { once: true });
    else InitializeAffiliateTutorial();
}
