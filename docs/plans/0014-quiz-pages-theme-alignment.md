# Quiz Pages Theme Alignment Implementation Plan

> **REQUIRED EXECUTION SKILL:** After plan approval and execution-method selection, use `git-worktrees`, then `executing-plans` with the selected method. Implementation is not yet authorized by approval of the design alone.

- **Goal:** Give Home, Leaderboard, and My Results the same visual language as Test Preview for learners and signed-in quiz users, including desktop and mobile layouts, without changing existing navigation or result behavior.
- **Architecture:** Add one opt-in presentation mode to the existing quiz layout and one shared, scoped stylesheet for these three views. Retain each page's existing data contract and page-specific content. This is one coupled presentation change, not independent subsystems.
- **Tech Stack:** Laravel 10, Blade, CSS, existing Vite assets, PHPUnit 10, DOM assertions and browser verification.
- **Source:** The user reported that Home, Leaderboard, and My Results had not adopted the tests theme, then approved the approach and short design in conversation. The approved design uses a white navigation bar, navy headings, orange accents/actions, pale-grey background, white bordered cards, matching typography and spacing, mobile layouts, and preserved data, filters, rankings, links and pagination. Home includes its level/term/section selection steps. Leaderboard retains its featured ranks and regular entries. My Results retains its summary and result cards. Empty states, zero scores, long text and keyboard usability are acceptance conditions.

## Design Decisions

- **Approaches:** A shared scoped stylesheet and layout opt-in were proposed and accepted. Separate per-page themes would repeat the visual rules; unscoped layout replacement would affect other quiz screens. These are agent-assessed trade-offs, not separate user selections.
- **Chosen:** Reuse the existing layout's opt-in pattern, with shared presentation rules for only the three target views. Preserve Test Preview and the answer-review page as regression references.
- **User's choices:** Align the three menu destinations with tests; approved the proposed page composition and compatibility requirements.
- **Agent's choices:** Use `quiz_portal_page` as the layout flag, `quiz-portal-page` as the body class, `quiz-portal-main` as the main class, and `home.portal.styles` as the shared Blade stylesheet. Use the exact reference colors and dimensions listed below. Replace obsolete page decorations rather than layering more competing overrides.
- **Left to the implementer:** None at the design level. Follow existing Blade, translation and test conventions for local implementation details.

## Global Constraints

- Preserve controller queries, route signatures, authentication, authorization, payloads, CSRF fields, sorting, score calculations, time/date formats and pagination. No database changes, dependency additions, commit or deployment.
- Preserve unrelated changes; inspect current status before execution. Repository root is `eimaths_backend`, not its parent directory.
- Preserve the existing Test Preview and answer-review presentation, shared footer content, navigation destinations and account actions. Scope new rules under `.quiz-portal-page`. (agent choice)
- Reference palette: navy `#14283f`, orange `#c2410c`, orange hover `#9a3412`, canvas `#f2f4f7`, white cards, border `#dce2e8`, primary text `#1f2937`, secondary text `#64748b`. Retain the layout's font stack, base size 16px and line-height 1.6. (agent choice)
- Main width 1200px maximum, centered; padding 40px 24px 64px. Cards use 12px corners, 24px padding and 16px gaps. Page header uses 32px padding, 32px heading and a 4px orange top border. At widths below 768px, main padding becomes 24px 16px 40px, header padding 24px, heading 26px, and content stacks as needed. (agent choice)
- Remove decorative gradients, drop shadows, text shadows, hover movement and decorative emoji from the target page content. Keep textual rank/performance cues. Do not remove information conveyed only by an icon without a text replacement. (agent choice)
- Interactive controls have visible keyboard focus (2px orange outline with 3px offset) and at least 44px height. Text and labels must wrap; avoid fixed card heights that clip long titles. (agent choice)
- Keep existing visible content and localization behavior. Do not broaden this work into translating the English-only pages. (agent choice)
- All new documentation is English; user-facing progress and completion reports are Thai.

## Verified Integration and Baseline

- `routes/web2.php` maps `home3`, `home.level`, `home.term`, `home.section`, `leaderboard`, `myresults`, and `submit.leaderboard` to `app/Http/Controllers/eLeanController.php`. The earlier conversational HomeController link was not the active route mapping.
- `eLeanController::home`, `homeLevel`, `homeTerm`, and `homeSection` render `home.home` with `levelCheck` values 0, 1, 2, and 3 respectively. The first three levels use grouped collections; the last uses individual quizzes.
- `home.leaderboard` uses a paginator, shows the top three on page one only, and derives later ranks from `firstItem()`. Its POST filter carries `_token`, `quiz_slug`, and hidden `route`.
- `home.result-list` computes summary statistics from the current paginator collection. Preserve that scope; do not claim these are all-history aggregates or silently change queries.
- `layouts.quiz_layout` already supports `result_page` and `test_preview_page`. Its fallback includes decorative containers that the new flag must bypass.
- `layouts.quiz_css` loads Vite and `@stack('css')`; the new stylesheet can be included directly by the layout after the existing CSS include. `layouts.nav-bar` also includes styles, so new navigation rules need the scoped body selector to win specificity.
- `tests/Feature/Elearning/TestPreviewPageTest.php` demonstrates in-memory Eloquent fixtures, `LengthAwarePaginator`, route resolution, `withoutVite()`, and DOMXPath checks without database migrations.
- On 2026-11-05, `php vendor/bin/phpunit --do-not-cache-result tests/Feature/Elearning/TestPreviewPageTest.php` exited 0: 7 tests, 59 assertions. PHP emitted an existing duplicate `exif` module warning. This validates the test command, not the proposed presentation.
- `vendor/bin/phpunit` and `public/build/manifest.json` exist. The older temporary PHP configuration named in previous plans does not exist; use the verified command above.

## Review Focus

1. Home's four collection shapes: preserve every selection link and the existing count/text for its branch.
2. Leaderboard page two: do not repeat the podium or reset rank numbering; preserve the selected quiz and paginator URLs.
3. Zero questions and zero scores: preserve division guards and show zero as a valid result. Preserve existing missing-user/quiz fallbacks where present.
4. Long Thai/English text and narrow screens: wrap without hiding actions or creating page-wide horizontal scrolling.
5. Shared layout isolation: tests and answer-review must keep their own classes, headings and footer behavior.

## Task 1: Integrate the shared presentation and Home selection flow

**Files:**
- Create: `resources/views/home/portal/styles.blade.php`
- Modify: `resources/views/layouts/quiz_layout.blade.php` at stylesheet includes, body classes, decoration conditions and main-content branches
- Modify: `resources/views/home/home.blade.php` at layout extension and the rendered content section
- Create: `tests/Feature/Elearning/QuizPortalThemeTest.php`

- **Consumes:** Existing layout flags, Home view variables and named routes described above.
- **Produces:** Opt-in `quiz_portal_page` mode and matching Home presentation for all four selection steps.
- **Checkpoint:** Begin only after approval of this completed plan and selection of an execution method. Stop if preserving a contract requires controller, authorization or database changes.

**Production behavior change — REQUIRED IMPLEMENTATION SKILL: `test-driven-development`.**

1. Follow the existing TestPreviewPageTest fixture/rendering pattern. Add PascalCase test methods `PreservesHomeSelectionLinksAcrossAllSteps`, `RendersHomeEmptyStates`, and `RendersOnePortalHeading`. Use a Quiz fixture with id 5, slug `addition`, title `Addition practice`, level `P1`, term `1`, section `2`, and questions_count 2. Cover `levelCheck` 0 through 3 with grouped collections matching the controller; assert links to `home.level(P1)`, `home.term(P1,1)`, `home.section(P1,1,2)` and `quiz.show(addition)` respectively. Resolve the request route by name as in the existing test. Test both `en` and `th` without changing existing translation text.
2. Assert one main heading and a `main.quiz-portal-main`, visible existing selection content, and empty-state text for empty collections. Run `php vendor/bin/phpunit --do-not-cache-result tests/Feature/Elearning/QuizPortalThemeTest.php`; the new portal structure assertions must fail before implementation, while preservation assertions establish the existing behavior.
3. Add the shared stylesheet include when the new flag is true. Add the body class and a dedicated main branch after the existing result/preview branches. Extend existing decoration/header suppression to include the new flag. Leave existing result/preview branches unchanged.
4. Opt Home into the new flag. Replace rendered inline visual styles and hover event mutations with scoped classes. Retain the switch, collection loops, route arguments, translation keys and counts. Use one navy page header, the existing student-quizzes section heading, and white selection cards. Use an auto-fitting grid with minimum card width 240px capped at 100% of available width. Retain contextual heading information without duplicating the h1. Leave commented-out legacy markup unactivated.
5. Define common palette, main, header, card, button, focus and navigation styles using the pinned values above. Navigation visual rules mirror Test Preview's scoped treatment; footer content remains supplied by the existing include.
6. Rerun the focused test and TestPreviewPageTest. Expect exit 0 and all selection links and empty states preserved.

## Task 2: Align Leaderboard without changing ranking

**Files:**
- Modify: `resources/views/home/leaderboard.blade.php` at its layout extension, CSS push and content section
- Modify: `resources/views/home/portal/styles.blade.php`
- Modify: `tests/Feature/Elearning/QuizPortalThemeTest.php`

- **Consumes:** Task 1's opt-in theme, `$tests`, `$quizzes`, `$quiz_slug`, existing form and rank calculations.
- **Produces:** Navy heading, white filter area, three featured white rank cards on page one and white regular rank entries.
- **Checkpoint:** Task 1 checks pass; no ranking-query changes.

**Production behavior change — REQUIRED IMPLEMENTATION SKILL: `test-driven-development`.**

1. Add `PreservesLeaderboardFilterAndRanks` and `RendersLeaderboardEmptyAndZeroScores`. Use in-memory Test/User/Quiz fixtures and a LengthAwarePaginator with per-page 10. Cover four entries on page one and an entry on page two with total 11. Assert featured ranks 1–3 only on page one, regular ranks 4 and 11 respectively, preserved participant/quiz/time/score text, POST action, CSRF input, hidden route `leaderboard`, selected slug `addition` and page links. Use zero questions and zero score to exercise the existing guard. Assert an associated label for the quiz select and the common main/header structure.
2. Run the focused portal test and observe failure for the missing new presentation/label before editing.
3. Opt the view into the new flag. Replace its old gradient/animation stylesheet with page-specific rules in the shared scoped file. Retain grouping, score/time computations, selected options, form submission and paginator. Replace decorative rank medals with visible numeric ranks. Associate the existing filter heading text with the select using a label and unique id.
4. Use three equal featured columns on desktop and one on mobile. Use a single regular entry per row, with rank, participant, quiz and score/time groups; stack these groups on mobile. Render score fraction and percentage as readable text instead of decorative circular gradients, retaining both values.
5. Run the focused portal test; expect exit 0, correct later-page ranks and unchanged form/link contracts.

## Task 3: Align My Results and preserve summary semantics

**Files:**
- Modify: `resources/views/home/result-list.blade.php` at its layout extension, CSS push and content section
- Modify: `resources/views/home/portal/styles.blade.php`
- Modify: `tests/Feature/Elearning/QuizPortalThemeTest.php`

- **Consumes:** Task 1's theme, current `$tests` paginator, existing summary calculations and `results.show` links.
- **Produces:** Navy heading, white summary section and one result card per row with orange detail actions.
- **Checkpoint:** Preserve current-page summary calculations and result URLs; no aggregate-query changes.

**Production behavior change — REQUIRED IMPLEMENTATION SKILL: `test-driven-development`.**

1. Add `PreservesMyResultsSummaryAndAnswerLinks`, `RendersMyResultsEmptyAndZeroScores`, and `EscapesLongPortalText`. Use two attempts with ids 42/43 and scores 0/2 and 2/2, duration 0/62 seconds and fixed dates. Assert summary count 2, total score 2/4, average 50%, excellent count 1; assert distinct result links, grade/term/section/date/time and 0/2 remain visible. Test zero questions separately, empty results, page links and long titles containing literal `<script>` text that must remain escaped. Assert one main heading and clearly labeled result cards/actions.
2. Run the portal tests and observe the new structural assertions fail before implementation.
3. Opt the view into the new flag. Remove the old page-level gradient/shadow overrides and obsolete visual mutations. Keep summary calculation expressions, paginator links, visible labels and result destinations.
4. Use a four-column summary row on desktop, two columns at intermediate widths, and one column on narrow mobile screens. Result cards use identity/quiz metadata on the left and score, percentage and action on the right; stack on mobile. Display score at 36px in orange, retaining performance text/cues and all existing values. Replace circular visual meters with text without changing percentages. Give every result link an accessible name including its attempt id.
5. Rerun the portal tests; expect preserved values, links and summary scope with the new structure.

## Task 4: Verify the integrated theme and record evidence

**Files:**
- Create: `docs/plans/0014-quiz-pages-theme-alignment-execution-record.md`

- **Consumes:** Completed tasks 1–3, real Vite assets and available browser session.
- **Produces:** English execution record with command results, visual checks, limitations and review outcomes.
- **Checkpoint:** Do not report visual completion based solely on DOM tests or fixtures without actual assets.

**Nonbehavioral verification artifact:**

1. Run `php vendor/bin/phpunit --do-not-cache-result tests/Feature/Elearning` from the backend root. Record exact pass/fail/skip counts and warnings. Run `git diff --check` and inspect scope with `git diff --stat` and `git status --short`.
2. Use the available browser tool after reading its applicable skill. Inspect the running application at the user's supplied localhost origin. Use an existing authorized session; do not invent credentials or alter authentication. If access is unavailable, report the blocked runtime check and use isolated rendered fixtures only with their limits explicitly recorded.
3. Compare Home's four stages, Leaderboard pages one/two and selected filter, and My Results against `/tests`. Check desktop 1440px and mobile 390px/320px, plus zoom at 200%. Verify white navigation, navy headers, orange actions, grey canvas, card borders and spacing. Verify the shared footer and account/menu interactions remain usable.
4. Inspect long titles/names, empty states, zero-score cards, keyboard focus and navigation, select labels, and absence of unintended horizontal overflow. Test actual filter and pagination interactions through the existing authorized UI. Do not submit a quiz or change result data.
5. Capture the checks performed and distinguish DOM evidence, visual evidence and unverified runtime behavior. If a regression appears, fix only the affected approved presentation and rerun its checks. Any unrelated existing defect is a separately reported finding, not permission for scope expansion.
6. Conduct the independent review required by the selected execution method, resolve in-scope findings, and verify the final revision before reporting completion. No commit or push.

## Execution Handoff

- Plan status: ready for user review; implementation has not started.
- Recommended method: **Native execution** by the current agent, followed by one fresh independent reviewer. The views share layout and CSS, so this minimizes coordination while retaining a final independent check.
- Alternative: **Per-task independent execution**, with a fresh implementer/reviewer per task; higher coordination and context cost for this coupled change.
- Both plan approval and method selection are required. A reply approving this plan with native execution also authorizes the final independent reviewer under `writing-plans`.
