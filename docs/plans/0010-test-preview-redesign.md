# Test Preview Redesign Implementation Plan

> **REQUIRED EXECUTION SKILL:** After plan approval, use `executing-plans` for native execution by the current agent, followed by final review.

- **Goal:** Make Test Preview a readable results list for staff reviewing attempts and opening individual answers.
- **Architecture:** Replace the presentation in `home.tests`, with an opt-in plain shell in the existing quiz layout. Preserve the controller, routes, filters, paginator, permissions, and Result page. Keep page-specific CSS under `home/test-preview`.
- **Tech Stack:** Laravel Blade, existing PHP translations, CSS, PHPUnit, existing JavaScript, native Chrome verification where available.
- **Source:** The user chose approach A and approved the short design with `ok`: a single left-aligned heading, filters together, a results table, vertical mobile records, restrained styling, no decorative emoji or statistics cards, and preserved data and behavior.

## Global Constraints

- Work in the existing `demo` workspace; do not create a worktree. Preserve all pending login, Result, and workbook changes, including plans 0007–0009. No commit, deployment, dependency installation, or live database changes.
- The purpose is scanning attempts and opening answers. Avoid a dashboard treatment: no summary statistic cards, decorative emoji, gradients, blur, shadows, or animation on this page.
- Use a white background, dark text, thin grey rules, and restrained orange accents. Retain the existing fonts. Support Thai and English.
- Show one visible heading, `ผลการทำแบบทดสอบ` / `Test results`. Keep the existing document title and sidebar labels unchanged.
- Present participant, quiz, score, duration, date, and an answer-detail link. Preserve attempt ID, identity secondary text, grade, term, section, percentage, and performance description as compact secondary information within the relevant cells.
- Put quiz selection and page size in one desktop toolbar. On narrow screens, stack controls and display each table record vertically with visible field labels; score and detail action remain easy to locate.
- Preserve default ordering, displayed dataset, filtering, page-size options, pagination, access boundaries, and links to Result. Do not add search, sorting, export, bulk actions, or query/authorization refactors.

## Verified Context

- `routes/web2.php` registers `tests/{quiz_slug?}` to `eLeanController::tests` inside the `auth` and `affiliate.redirect` group.
- `eLeanController::tests` selects attempts with `whereHas('user')`, loads user/quiz data, counts questions, orders latest first, and paginates using 10/20/50/100 (default 20). This is not a complete learner-results dataset; preserve its current boundary.
- Quiz selection posts `quiz_slug` and `route` with CSRF to `submit.leaderboard`; `submitLeaderboard` redirects to the selected route/slug. Changing quiz currently resets page size to its default. Preserve this behavior rather than silently redesigning the filter contract.
- Page size modifies the current URL's `per_page`, removes `page`, and retains other query parameters. Paginator links use `withQueryString()`.
- The current view has approximately 960 lines of decorative CSS and repeated card sections. `layouts.quiz_layout` already contains an isolated `result_page` variant, with pending Result/workbook changes. Do not reuse the Result body class or change its styles.
- Existing locale coverage includes the `tests` route in `tests/Feature/Localization/UiLocalizedPagesTest.php`. `tests/Feature/Elearning/ResultPageTest.php` establishes the `withoutVite()` and in-memory view-test pattern.
- Native Chrome worked for the last Result preview, but Tab Browser was unavailable. Missing frontend dependencies prevent a complete production-bundle check; the prior fixture omitted Vite. Do not present that fixture as full production verification.

## Review Focus

1. Changing quiz or page size and moving to the next page must retain the established URL/filter behavior and offer the allowed page sizes.
2. Zero score, zero questions, missing identity/quiz/date, and zero duration must render without disappearing or dividing by zero.
3. Long names and quiz titles must wrap at 390px and 200% zoom without hiding score or the detail action.
4. Both locales must have complete labels, associated filter labels, meaningful link names, and semantic table headers; mobile styling must retain understandable reading order.
5. Plain-shell styling must remain exclusive to Test Preview; current Result/workbook appearance and other quiz pages must remain intact.

## Task 1: Characterize Existing List Contracts

**Files:** Create/test `tests/Feature/Elearning/TestPreviewPageTest.php`.

- **Consumes:** `home.tests`, `Test`, `Quiz`, `User`, and `LengthAwarePaginator`; existing named routes.
- **Produces:** View-level regression evidence for data, filter controls, paginator destinations, and answer-detail links.
- **Checkpoint:** This plan must be explicitly approved before execution.

**Behavior-preserving contract — use the Refactor Contract in `test-driven-development`:**
- [ ] Use `Tests\TestCase`, `withoutVite()`, in-memory model relations, and a `LengthAwarePaginator`; bind the test request to the real `tests` route for the existing view's route-name lookup. Do not write to real data.
- [ ] Add `PreservesAttemptDataAndResultLinks`: attempt ID 42, score 0/2, duration 0, explicit timestamp, identity, and quiz metadata; assert rendered content and `route('results.show', 42)`.
- [ ] Add `PreservesQuizSelectionAndPagination`: selected slug `addition`, page size 10, page 2, total 25; assert selected controls, CSRF form action and hidden route `tests`, all four sizes, and paginator URLs carrying `per_page=10`. Assert an empty paginator renders the existing empty-state translation.
- [ ] Run the focused command below before editing production. Expect passing characterization tests; correct fixture errors rather than changing production behavior to fit tests.

## Task 2: Replace Decorative Cards with the Responsive Results List

**Files:**
- Modify: `resources/views/home/tests.blade.php` at layout declaration, CSS, heading, filter controls, records, and pagination.
- Modify: `resources/views/layouts/quiz_layout.blade.php` at stylesheet inclusion, body classes, decorative wrappers, heading, and main branch.
- Create: `resources/views/home/test-preview/styles.blade.php`.
- Modify: `lang/en/quiz-management.php` and `lang/th/quiz-management.php` in `preview`.
- Test: `tests/Feature/Elearning/TestPreviewPageTest.php`.

- **Consumes:** Existing `tests`, `quizzes`, `quiz_slug`, `perPage`, `perPageOptions`; Task 1 characterization tests.
- **Produces:** A `test_preview_page` opt-in layout flag, scoped `.test-preview-page` styles, and one responsive semantic table.
- **Checkpoint:** None after Task 1 passes.

**Production presentation behavior — use `test-driven-development`:**
- [ ] Add failing `RendersAccessibleResultsTableInBothLocales`: verify one visible page heading, six associated column headers, explicit select labels, localized detail-link text, and every record's details URL. Add `RendersMissingAndLongValues`: zero-question score, absent relations/date, and long escaped names/titles must render without exceptions or raw markup. Test both locales and the empty state. Run the focused command and confirm failures come from missing presentation requirements.
- [ ] Add `test_preview_page => true` only to this view. Load its stylesheet only for that flag. Skip decorative outer wrappers and duplicate header for this flag; add a plain `test-preview-main` branch while leaving the existing Result and default branches intact.
- [ ] Replace all page CSS with the dedicated scoped style partial. Use maximum width 1200px, 24px desktop/16px mobile padding, white background, `#1f2937` text, `#6b7280` secondary text, `#e5e7eb` rules, and `#c2410c` accents. Scope navigation/footer decoration removal to `.test-preview-page`; preserve their contents and actions.
- [ ] Add translation keys `results_heading`, `participant`, `duration`, `attempted_at`, and `view_answers`, with Thai values `ผลการทำแบบทดสอบ`, `ผู้ทำ`, `เวลา`, `วันที่`, `ดูคำตอบ` and English values `Test results`, `Participant`, `Time`, `Date`, `View answers`. Keep existing keys for quiz, score, grade, term, level, page size, and empty state; do not remove old keys used elsewhere.
- [ ] Render one h1 and the two existing controls in a toolbar. Keep the quiz POST form, CSRF, hidden route, auto-submit, and page-size URL logic unchanged; give both selects explicit labels. Do not introduce a new JavaScript module or dependency.
- [ ] Use one table with column headers and six cells per attempt. Put identity/attempt ID in the participant cell, title/grade/term/section in quiz, score/rounded percentage/existing performance description in score, current HH:MM:SS duration, current timestamp format, and localized `view_answers` linked to `results.show`. Retain existing fallback calculations, including null duration as zero and zero-question percentage as zero; use the existing localized unavailable label for missing descriptive fields.
- [ ] Remove all decorative emoji, meter graphics, statistic cards, and empty-state promotional cards. Keep a simple translated empty-state message and the existing paginator.
- [ ] Below 768px, stack records and show localized field labels, retaining table semantics and reading order. Wrap long text, keep action targets at least 44px high, and provide visible orange focus outlines. Use thin separators and spacing rather than nested cards.
- [ ] Rerun the focused tests; expect exit 0. Run the existing Result suite and localization file using the commands below; report unrelated localization failures separately. `QuizManagementPagesRenderInBothLocales` covers the tests page in datasets 8 and 9 (English and Thai). Refactor only within the listed scope and rerun affected checks.

## Task 3: Verify Browser Flows and Record Delivery Limits

**Files:** Create `docs/plans/0010-test-preview-execution-record.md`.

- **Consumes:** Final code, automated results, and disposable test data.
- **Produces:** English execution record with approval, revision, task progress, review findings/dispositions, and verification limits.
- **Checkpoint:** Do not claim complete browser verification from a Vite-suppressed preview.

**Nonbehavioral verification artifact:**
- [ ] Run fresh focused and affected checks, the full PHP suite, `node --test tests/JavaScript/*.test.mjs`, and `git diff --check`. Record exit codes and all failures/skips; compare known prior failures without assuming matching causes.
- [ ] Open a disposable preview in native Chrome if available; inspect 1280px and 390px, 200% zoom, both locales, empty results, long text, and zero scores. Test filter selection, page-size reset, pagination, and the Result link using disposable authenticated fixtures. A static rendered preview verifies only appearance, not these route flows.
- [ ] Inspect a current Result page and a default quiz-layout page for styling regressions. Check keyboard access, focus, and control labels. Record any missing bundle or browser limitation instead of claiming a pass.
- [ ] Perform final independent review via `reviewing-code` when available, including Review Focus above. Record every finding and declined judgment; preserve unrelated changes. No commit or deployment.

## Verified Commands and Self-Review

Run from `C:/Users/user/github/eimaths/eimaths_backend` in PowerShell. Both PHPUnit and the isolated PHP configuration exist:

```powershell
$env:PHP_INI_SCAN_DIR = Join-Path $env:TEMP 'eimaths-php-no-extra-ini'
php -c "$env:TEMP/eimaths-elearning-php.ini" vendor/bin/phpunit --do-not-cache-result tests/Feature/Elearning/TestPreviewPageTest.php
php -c "$env:TEMP/eimaths-elearning-php.ini" vendor/bin/phpunit --do-not-cache-result tests/Feature/Elearning/ResultPageTest.php
php -c "$env:TEMP/eimaths-elearning-php.ini" vendor/bin/phpunit --do-not-cache-result tests/Feature/Localization/UiLocalizedPagesTest.php
php -c "$env:TEMP/eimaths-elearning-php.ini" vendor/bin/phpunit --do-not-cache-result
node --test tests/JavaScript/*.test.mjs
git diff --check
```

The approved design maps to the single list redesign; no independent subsystem is added. Retaining controller/filter behavior avoids silently expanding the data boundary. Existing Result/workbook work remains separately owned. Missing bundle integration remains an explicit delivery limit. Approval of this completed plan is still required; the established native execution method is retained.
