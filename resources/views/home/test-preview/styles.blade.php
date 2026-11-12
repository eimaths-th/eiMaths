<style>
    body.test-preview-page { background: #f2f4f7 !important; color: #1f2937; font-size: 16px; line-height: 1.6; }
    .test-preview-page .test-preview-main { box-sizing: border-box; width: 100%; max-width: 1200px; margin: 0 auto; padding: 40px 24px 64px; flex: 1; }
    .test-preview-page :is(a, button, select):focus-visible { outline: 2px solid #c2410c !important; outline-offset: 3px; }
    .test-preview-page .modern-nav { background: #fff; border-bottom: 1px solid #e5e7eb; box-shadow: none; backdrop-filter: none; }
    .test-preview-page .modern-nav * { box-shadow: none !important; filter: none !important; backdrop-filter: none !important; transform: none !important; transition: none !important; animation: none !important; }
    .test-preview-page .test-preview-header { background: #14283f; color: #fff; padding: 32px; border-radius: 12px 12px 0 0; border-top: 4px solid #c2410c; }
    .test-preview-page .test-preview-heading { margin: 0; font-size: 32px; line-height: 1.4; font-weight: 700; }
    .test-preview-page .test-preview-toolbar { display: flex; align-items: flex-end; gap: 24px; padding: 20px; margin-bottom: 24px; background: #fff; border: 1px solid #dce2e8; border-top: 0; border-radius: 0 0 12px 12px; }
    .test-preview-page #quiz-form { flex: 1; max-width: 680px; min-width: 0; }
    .test-preview-page .test-preview-toolbar label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; }
    .test-preview-page .test-preview-toolbar select { box-sizing: border-box; min-height: 44px; width: 100%; padding: 8px 36px 8px 12px; background-color: #fff; color: #1f2937; border: 1px solid #94a3b8; border-radius: 6px; font: inherit; }
    .test-preview-page .test-preview-page-size { width: 100px; flex-shrink: 0; }
    .test-preview-page .test-preview-list { list-style: none; padding: 0; margin: 0; display: grid; gap: 16px; }
    .test-preview-page .test-preview-card { display: grid; grid-template-columns: minmax(0, 1fr) minmax(240px, 320px); gap: 28px; padding: 24px; background: #fff; border: 1px solid #dce2e8; border-radius: 12px; overflow-wrap: anywhere; }
    .test-preview-page .test-preview-identity, .test-preview-page .test-preview-outcome { min-width: 0; }
    .test-preview-page .test-preview-participant { display: flex; flex-wrap: wrap; align-items: baseline; gap: 8px 12px; }
    .test-preview-page .test-preview-participant strong { font-weight: 600; }
    .test-preview-page .test-preview-attempt { color: #64748b; font-size: 13px; margin-left: auto; }
    .test-preview-page .test-preview-label { display: block; color: #64748b; font-size: 13px; font-weight: 500; }
    .test-preview-page .test-preview-secondary { display: block; color: #64748b; font-size: 14px; margin-top: 4px; }
    .test-preview-page .test-preview-quiz-label { margin-top: 24px; }
    .test-preview-page .test-preview-card h2 { margin: 6px 0 16px; font-size: 22px; line-height: 1.5; font-weight: 700; color: #14283f; }
    .test-preview-page .test-preview-quiz-meta { display: flex; flex-wrap: wrap; gap: 8px; margin: 0; font-size: 13px; }
    .test-preview-page .test-preview-quiz-meta > div { display: flex; gap: 6px; padding: 4px 10px; background: #f2f4f7; border-radius: 4px; }
    .test-preview-page .test-preview-quiz-meta dt { color: #64748b; }
    .test-preview-page dd { margin: 0; }
    .test-preview-page .test-preview-outcome { border-left: 1px solid #e2e8f0; padding-left: 28px; }
    .test-preview-page .test-preview-score { display: block; color: #c2410c; font-size: 36px; line-height: 1.25; font-weight: 700; margin: 4px 0; }
    .test-preview-page .test-preview-performance { display: block; color: #475569; font-size: 13px; }
    .test-preview-page :is(.test-preview-score, .test-preview-numeric) { font-variant-numeric: tabular-nums; }
    .test-preview-page .test-preview-attempt-meta { display: grid; gap: 8px; margin: 18px 0; font-size: 13px; }
    .test-preview-page .test-preview-attempt-meta > div { display: grid; grid-template-columns: 48px minmax(0, 1fr); gap: 12px; }
    .test-preview-page .test-preview-attempt-meta dt { color: #64748b; }
    .test-preview-page .test-preview-detail-link { display: inline-flex; align-items: center; justify-content: center; min-height: 44px; padding: 8px 20px; border-radius: 6px; background: #c2410c; color: #fff; text-decoration: none; font-weight: 600; font-size: 14px; }
    .test-preview-page .test-preview-detail-link:hover { background: #9a3412; }
    .test-preview-page .test-preview-empty { padding: 32px; border: 1px solid #dce2e8; border-radius: 12px; background: #fff; }
    .test-preview-page .test-preview-empty h2 { font-size: 20px; font-weight: 600; margin: 0 0 8px; }
    .test-preview-page .test-preview-empty p { margin: 0; color: #64748b; }
    .test-preview-page .test-preview-pagination { margin-top: 24px; }
    .test-preview-page .test-preview-pagination :is(a, span) { box-shadow: none !important; }
    @media (max-width: 767px) {
        .test-preview-page .test-preview-main { padding: 24px 16px 40px; }
        .test-preview-page .test-preview-header { padding: 24px; }
        .test-preview-page .test-preview-heading { font-size: 26px; }
        .test-preview-page .test-preview-toolbar { display: grid; gap: 16px; }
        .test-preview-page #quiz-form { max-width: none; }
        .test-preview-page .test-preview-card { grid-template-columns: minmax(0, 1fr); gap: 24px; }
        .test-preview-page .test-preview-outcome { border-left: 0; border-top: 1px solid #e2e8f0; padding-left: 0; padding-top: 20px; }
        .test-preview-page .test-preview-card h2 { font-size: 20px; }
    }
</style>
