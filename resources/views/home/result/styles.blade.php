<style>
    body.result-page { background: #f2f4f7 !important; color: #1f2937; font-size: 16px; line-height: 1.6; }
    .result-page .result-main { box-sizing: border-box; width: 100%; max-width: 1100px; margin: 0 auto; padding: 40px 24px 64px; flex: 1; }
    .result-page :is(a, button, summary):focus-visible { outline: 2px solid #c2410c !important; outline-offset: 4px; }
    .result-page .modern-nav { background: #fff; border-bottom: 1px solid #e5e7eb; box-shadow: none; backdrop-filter: none; }
    .result-page .modern-nav * { box-shadow: none !important; filter: none !important; backdrop-filter: none !important; transform: none !important; transition: none !important; }
    .result-page .result-heading { display: grid; grid-template-columns: minmax(0, 1fr) 240px; grid-template-areas: "identity score" "metrics metrics"; border: 1px solid #dce2e8; border-top: 4px solid #c2410c; border-radius: 12px; overflow: hidden; background: #14283f; color: #fff; }
    .result-page .result-score-panel { grid-area: score; padding: 32px; display: flex; align-items: center; justify-content: center; min-width: 0; border-left: 1px solid #334155; }
    .result-page .result-score-panel dl { margin: 0; text-align: center; max-width: 100%; }
    .result-page .result-score-panel dt { color: #cbd5e1; font-size: 14px; font-weight: 600; }
    .result-page .result-score { color: #fb923c; font-size: 64px; font-weight: 700; line-height: 1.15; font-variant-numeric: tabular-nums; margin: 4px 0 0; overflow-wrap: anywhere; }
    .result-page .result-identity { grid-area: identity; padding: 32px; min-width: 0; }
    .result-page .result-label { color: #fb923c; margin: 0 0 8px; font-size: 14px; }
    .result-page .result-heading h1 { font-size: 32px; line-height: 1.4; font-weight: 700; margin: 0 0 16px; overflow-wrap: anywhere; }
    .result-page .result-student { margin: 0; overflow-wrap: anywhere; }
    .result-page .result-student span { color: #cbd5e1; display: block; font-size: 14px; }
    .result-page .result-summary { grid-area: metrics; display: flex; flex-wrap: wrap; align-items: baseline; gap: 16px 40px; margin: 0; padding: 20px 32px; font-size: 14px; background: #fff; color: #1f2937; }
    .result-page .result-summary > div { min-width: 0; overflow-wrap: anywhere; }
    .result-page .result-summary dt, .result-page .result-answer-summary dt { color: #6b7280; font-weight: 400; }
    .result-page .result-summary dd { margin: 4px 0 0; font-weight: 600; }
    .result-page .result-review > h2 { font-size: 24px; font-weight: 700; margin: 32px 0 20px; color: #14283f; }
    .result-page .result-question { padding: 24px; margin-bottom: 20px; border: 1px solid #dce2e8; border-radius: 12px; background: #fff; min-width: 0; }
    .result-page .result-question-heading { display: flex; flex-wrap: wrap; align-items: baseline; gap: 12px 24px; margin-bottom: 16px; }
    .result-page .result-question-heading h3 { display: flex; align-items: center; gap: 8px; font-size: 20px; font-weight: 600; margin: 0; }
    .result-page .result-question-number { display: inline-flex; align-items: center; justify-content: center; min-width: 36px; min-height: 36px; padding: 0 4px; box-sizing: border-box; border-radius: 8px; background: #14283f; color: #fff; }
    .result-page .result-status { font-size: 14px; padding: 4px 12px; border-radius: 6px; font-weight: 600; }
    .result-page .result-status-correct, .result-page .result-correct-label { color: #166534; }
    .result-page .result-status-incorrect { color: #991b1b; background: #fef2f2; }
    .result-page .result-status-unanswered { color: #475569; background: #f1f5f9; }
    .result-page .result-question-text { max-width: 75ch; font-size: 18px; line-height: 1.65; margin-bottom: 16px; overflow-wrap: anywhere; overflow-x: auto; }
    .result-page .result-question img { max-width: 100%; height: auto; }
    .result-page .result-answer-summary { margin: 0; }
    .result-page .result-answer-pair { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; max-width: 100%; }
    .result-page .result-answer-summary > div { display: grid; grid-template-columns: 140px minmax(0, 1fr); gap: 16px; margin: 8px 0; }
    .result-page .result-answer-pair > div { grid-template-columns: minmax(0, 1fr); gap: 8px; border: 1px solid #dce2e8; border-radius: 8px; padding: 16px; min-width: 0; }
    .result-page .result-answer-summary dd { margin: 0; overflow-x: auto; overflow-wrap: anywhere; }
    .result-page .result-options { list-style: none; padding: 0; margin: 16px 0 0; max-width: 680px; }
    .result-page .result-option { display: flex; flex-wrap: wrap; align-items: flex-start; gap: 8px 12px; padding: 12px; border: 1px solid #dce2e8; border-radius: 8px; margin-bottom: 8px; }
    .result-page .result-option-letter { color: #6b7280; width: 24px; flex-shrink: 0; }
    .result-page .result-option-content { flex: 0 1 auto; min-width: 0; max-width: calc(100% - 36px); overflow-x: auto; overflow-wrap: anywhere; }
    .result-page .result-option-content img { display: block; max-height: 320px; object-fit: contain; object-position: left; }
    .result-page .result-option-labels { display: flex; flex-wrap: wrap; gap: 8px 16px; font-size: 14px; }
    .result-page .result-explanation { background: #fff7ed; border: 1px solid #fed7aa; border-radius: 8px; padding: 16px; margin-top: 20px; }
    .result-page .result-explanation h4 { font-size: 16px; font-weight: 600; margin: 0 0 8px; }
    .result-page .result-explanation p { margin: 0 0 16px; max-width: 75ch; overflow-wrap: anywhere; }
    .result-page .result-explanation-image { display: block; margin: 16px 0; }
    .result-page .result-video summary { cursor: pointer; color: #c2410c; min-height: 44px; box-sizing: border-box; padding: 10px 16px; width: fit-content; max-width: 100%; border: 1px solid #c2410c; border-radius: 6px; background: #fff; }
    .result-page .result-video iframe { width: 100%; aspect-ratio: 16 / 9; height: auto; border: 0; display: block; margin-top: 8px; }
    .result-page .result-status-correct { background: #f0fdf4; }
    .result-page .result-option-selected { border: 2px solid #c2410c; padding: 11px; }
    .result-page .result-option-correct { background: #f0fdf4; }
    @media (max-width: 767px) {
        .result-page .result-main { padding: 32px 16px 48px; }
        .result-page .result-heading { grid-template-columns: minmax(0, 1fr); grid-template-areas: "identity" "score" "metrics"; }
        .result-page .result-score-panel { padding: 24px; border-left: 0; border-top: 1px solid #334155; }
        .result-page .result-score { font-size: 48px; }
        .result-page .result-identity { padding: 24px; }
        .result-page .result-heading h1 { font-size: 26px; }
        .result-page .result-summary { padding: 20px 24px; }
        .result-page .result-answer-pair { grid-template-columns: minmax(0, 1fr); gap: 0; }
        .result-page .result-answer-summary > div { grid-template-columns: 1fr; gap: 4px; margin: 16px 0; }
        .result-page .result-option-labels { flex-basis: 100%; padding-left: 36px; }
    }
</style>
