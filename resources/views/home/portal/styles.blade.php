<style>
    body.quiz-portal-page { background: #f2f4f7 !important; color: #1f2937; font-size: 16px; line-height: 1.6; }
    .quiz-portal-page .quiz-portal-main { box-sizing: border-box; width: 100%; max-width: 1200px; margin: 0 auto; padding: 40px 24px 64px; flex: 1; }
    .quiz-portal-page .quiz-portal-main * { box-sizing: border-box; }
    .quiz-portal-page :is(a, button, select):focus-visible { outline: 2px solid #c2410c !important; outline-offset: 3px; }
    .quiz-portal-page .modern-nav { background: #fff; border-bottom: 1px solid #e5e7eb; box-shadow: none; backdrop-filter: none; }
    .quiz-portal-page .modern-nav * { box-shadow: none !important; filter: none !important; backdrop-filter: none !important; transform: none !important; transition: none !important; animation: none !important; }
    .quiz-portal-page .modern-nav :is(a, button) { min-height: 44px; }
    .quiz-portal-page .portal-header { background: #14283f; color: #fff; padding: 32px; border-radius: 12px; border-top: 4px solid #c2410c; margin-bottom: 24px; }
    .quiz-portal-page .portal-header h1 { margin: 0; font-size: 32px; line-height: 1.4; font-weight: 700; color: #fff; }
    .quiz-portal-page .portal-header p { margin: 8px 0 0; color: #e2e8f0; }
    .quiz-portal-page .quiz-portal-main :is(h2, h3) { color: #14283f; font-weight: 700; }
    .quiz-portal-page .quiz-portal-main h2 { font-size: 22px; margin: 0 0 16px; }
    .quiz-portal-page .quiz-portal-main h3 { font-size: 20px; margin: 8px 0; }
    .quiz-portal-page .quiz-portal-main :is(p, h1, h2, h3, strong, a, div) { overflow-wrap: anywhere; min-width: 0; }
    .quiz-portal-page .portal-selection-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(240px, 100%), 1fr)); gap: 16px; }
    .quiz-portal-page :is(.portal-selection-card, .portal-empty, .filter-section, .podium-card, .leaderboard-card, .summary-card, .result-card, .empty-state) { background: #fff; border: 1px solid #dce2e8; border-radius: 12px; padding: 24px; }
    .quiz-portal-page .portal-selection-card { display: flex; flex-direction: column; align-items: flex-start; gap: 8px; color: #1f2937; text-decoration: none; }
    .quiz-portal-page .portal-selection-card:hover { border-color: #c2410c; }
    .quiz-portal-page .portal-selection-card p { margin: 0; color: #64748b; }
    .quiz-portal-page .portal-selection-card strong { margin-top: auto; padding-top: 16px; font-size: 14px; }
    .quiz-portal-page .portal-tag { display: inline-block; background: #f2f4f7; color: #14283f; border-radius: 4px; padding: 4px 10px; font-size: 14px; }
    .quiz-portal-page .portal-empty { grid-column: 1 / -1; }
    .quiz-portal-page .filter-section { margin-bottom: 24px; }
    .quiz-portal-page .filter-section label { display: block; font-size: 14px; font-weight: 600; margin-bottom: 8px; }
    .quiz-portal-page .filter-select { display: block; width: 100%; max-width: 680px; min-height: 44px; padding: 8px 36px 8px 12px; border: 1px solid #94a3b8; border-radius: 6px; background-color: #fff; color: #1f2937; font: inherit; }
    .quiz-portal-page .podium-section { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; margin-bottom: 24px; }
    .quiz-portal-page .podium-rank { color: #c2410c; font-size: 14px; font-weight: 700; margin-bottom: 16px; }
    .quiz-portal-page .podium-user { color: #14283f; font-size: 22px; font-weight: 700; }
    .quiz-portal-page :is(.podium-quiz, .podium-date, .user-email, .quiz-date, .score-time) { color: #64748b; font-size: 14px; }
    .quiz-portal-page .podium-score { margin: 16px 0 0; }
    .quiz-portal-page .score-fraction { color: #14283f; font-weight: 600; }
    .quiz-portal-page :is(.score-value, .score-value-small) { font-size: 30px; font-weight: 700; color: #c2410c; font-variant-numeric: tabular-nums; }
    .quiz-portal-page .leaderboard-grid { display: grid; gap: 16px; }
    .quiz-portal-page .leaderboard-card { display: grid; grid-template-columns: 44px minmax(0, 1fr) minmax(0, 1fr) minmax(120px, .6fr); align-items: center; gap: 24px; }
    .quiz-portal-page .rank-badge { display: flex; align-items: center; justify-content: center; min-height: 44px; background: #f2f4f7; border-radius: 6px; font-weight: 700; color: #14283f; }
    .quiz-portal-page :is(.user-name, .quiz-title) { font-weight: 600; color: #14283f; }
    .quiz-portal-page .leaderboard-pagination { margin-top: 24px; }
    .quiz-portal-page .leaderboard-pagination :is(a, span) { box-shadow: none !important; }
    .quiz-portal-page .leaderboard-pagination .pagination { display: flex; flex-wrap: wrap; gap: 8px; padding: 0; margin: 0; list-style: none; }
    .quiz-portal-page .leaderboard-pagination .page-link { display: inline-flex; align-items: center; justify-content: center; min-height: 44px; min-width: 44px; padding: 8px 12px; margin: 0; border: 1px solid #dce2e8; border-radius: 6px; background: #fff; color: #475569; text-decoration: none; }
    .quiz-portal-page .leaderboard-pagination .active .page-link { background: #c2410c; border-color: #c2410c; color: #fff; }
    .quiz-portal-page .leaderboard-pagination a.page-link:hover { border-color: #c2410c; color: #9a3412; }
    .quiz-portal-page .empty-title { font-size: 20px; font-weight: 700; color: #14283f; }
    .quiz-portal-page .empty-message { color: #64748b; }
    .quiz-portal-page .summary-card { margin-bottom: 24px; }
    .quiz-portal-page .summary-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 24px; }
    .quiz-portal-page .summary-stat-number { color: #c2410c; font-size: 28px; font-weight: 700; }
    .quiz-portal-page :is(.summary-stat-label, .card-info-label, .card-title p, .card-score-percentage) { color: #64748b; font-size: 14px; }
    .quiz-portal-page .results-grid { display: grid; gap: 16px; }
    .quiz-portal-page .result-card { display: grid; grid-template-columns: minmax(0, 1fr) minmax(240px, 320px); column-gap: 28px; }
    .quiz-portal-page .card-header { display: flex; align-items: baseline; gap: 12px; grid-column: 1; }
    .quiz-portal-page .card-number { color: #64748b; font-size: 14px; }
    .quiz-portal-page .card-title h2 { margin: 0 0 8px; }
    .quiz-portal-page .card-content { display: flex; flex-wrap: wrap; gap: 16px; grid-column: 1; margin-top: 24px; }
    .quiz-portal-page .card-info-value { color: #14283f; font-size: 14px; }
    .quiz-portal-page .card-footer { grid-column: 2; grid-row: 1 / 3; border-left: 1px solid #e2e8f0; padding-left: 28px; }
    .quiz-portal-page .card-score { display: flex; flex-direction: column-reverse; margin-bottom: 18px; }
    .quiz-portal-page .card-score-fraction { color: #c2410c; font-size: 36px; font-weight: 700; line-height: 1.25; }
    .quiz-portal-page .score-display { color: #475569; font-size: 13px; margin: 8px 0; }
    .quiz-portal-page :is(.card-score-fraction, .summary-stat-number, .time-display) { font-variant-numeric: tabular-nums; }
    .quiz-portal-page .action-btn { display: inline-flex; align-items: center; justify-content: center; min-height: 44px; padding: 8px 20px; border-radius: 6px; background: #c2410c; color: #fff; text-decoration: none; font-weight: 600; font-size: 14px; }
    .quiz-portal-page .action-btn:hover { background: #9a3412; }
    .quiz-portal-page .portal-pagination { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; margin: 24px 0 16px; }
    .quiz-portal-page .portal-pagination :is(a, span) { display: inline-flex; gap: 4px; align-items: center; justify-content: center; min-height: 44px; min-width: 44px; padding: 8px 12px; border: 1px solid #dce2e8; border-radius: 6px; background: #fff; color: #475569; text-decoration: none; }
    .quiz-portal-page .portal-pagination svg { width: 16px; height: 16px; }
    .quiz-portal-page .portal-pagination .portal-current-page { background: #c2410c; border-color: #c2410c; color: #fff; }
    .quiz-portal-page .portal-pagination a:hover { border-color: #c2410c; color: #9a3412; }
    @media (max-width: 900px) {
        .quiz-portal-page .summary-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 767px) {
        .quiz-portal-page .quiz-portal-main { padding: 24px 16px 40px; }
        .quiz-portal-page .portal-header { padding: 24px; }
        .quiz-portal-page .portal-header h1 { font-size: 26px; }
        .quiz-portal-page .podium-section { grid-template-columns: minmax(0, 1fr); }
        .quiz-portal-page .leaderboard-card { grid-template-columns: 44px minmax(0, 1fr); gap: 16px; }
        .quiz-portal-page .leaderboard-card :is(.quiz-info, .score-info) { grid-column: 2; }
        .quiz-portal-page .result-card { grid-template-columns: minmax(0, 1fr); }
        .quiz-portal-page .card-footer { grid-column: 1; grid-row: auto; border-left: 0; border-top: 1px solid #e2e8f0; padding: 20px 0 0; margin-top: 20px; }
    }
    @media (max-width: 479px) {
        .quiz-portal-page .summary-stats { grid-template-columns: minmax(0, 1fr); }
    }
</style>
