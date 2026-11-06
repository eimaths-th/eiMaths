# Test Preview Cards Implementation Plan

> **REQUIRED EXECUTION SKILL:** After plan approval, use `executing-plans` with native execution, followed by final review. Preserve the user's instruction not to create a worktree.

- **Goal:** Give the staff Test Preview page a stronger visual identity while keeping results readable and all existing actions intact.
- **Architecture:** Replace the table presentation in `home.tests` with a semantic list of attempt cards. Continue using the existing `test_preview_page` layout opt-in and scoped stylesheet. No controller, route, authentication, or data changes.
- **Tech Stack:** Laravel Blade, existing CSS and localization, PHPUnit, native Chrome with the application's Vite bundle.
- **Source:** The user selected B and C, then approved the proposed combination: navy page header with white heading and orange accents; separate filter bar; pale-grey background; white bordered cards with moderate rounded corners; horizontal desktop cards with participant and quiz on the left and prominent score, time, date, and orange answer action on the right; stacked mobile cards; no emoji or invented statistics. The reason is that plan 0010 made the page too minimal. This approval supersedes that plan's table and all-white visual treatment, not its preserved behavior.

## Global Constraints

- Work on the existing `demo` checkout. Preserve unrelated login, Result/workbook, translations, and documentation changes. No commit, deployment, database migration, or worktree creation.
- Keep all attempt fields, fallback text, score calculations, duration/date formatting, escaping, result URLs, filter POST/CSRF/hidden route, page sizes, and paginator query behavior.
- Use the existing Thai/English labels and existing font stack. No new dependencies, illustrations, summary metrics, or decorative emoji.
- Scope changes to `resources/views/home/tests.blade.php`, `resources/views/home/test-preview/styles.blade.php`, and `tests/Feature/Elearning/TestPreviewPageTest.php`.
- Implementation choices proposed by the assistant: navy `#14283f`, orange action `#c2410c`, pale-grey canvas `#f2f4f7`, white cards, text `#1f2937`, secondary text `#64748b`; 12px card radius, 16px gaps, 24px card padding. Existing layout maximum width remains 1200px. No gradients or animation. Keep navigation/footer content and existing scoped treatment.
- Use one full-width card per row, never a multi-column card gallery. Header padding 32px on desktop, 24px on mobile; filter bar white with border and 20px padding. Heading 32px desktop/26px mobile. Score 36px bold with tabular numerals. Detail links become filled orange buttons with white text, minimum height 44px, and visible keyboard focus.

## Review Focus

- Long Thai/English names and quiz titles must wrap without pushing scores or actions outside cards.
- Zero score, zero questions, absent user/quiz/date, and duplicate participant detail must preserve existing treatment.
- Each card must expose its quiz heading, participant, labeled metadata, and uniquely named result link to assistive technology.
- Filters and pagination must retain current behavior, including page-size reset when changing quiz.
- Visual review must load real Vite assets: `public/build/manifest.json` now exists. The previous no-assets fixture is insufficient evidence of final appearance.

## Task 1: Render accessible attempt cards

**Files:** Modify `resources/views/home/tests.blade.php`; test `tests/Feature/Elearning/TestPreviewPageTest.php`.

- **Consumes:** Existing `$tests`, `$quizzes`, `$quiz_slug`, `$perPage`, `$perPageOptions`, translation keys, and `results.show` route.
- **Produces:** One h1 inside a page header, the unchanged filter form, and a `ul.test-preview-list` with one `li > article` per attempt. Each article has an h2 containing the quiz title, participant details, grade/term/section, labeled score/time/date, and the existing uniquely labeled answer link. Retain empty state and pagination.
- **Checkpoint:** Start only after this completed plan is explicitly approved.

**Presentation behavior change — use `test-driven-development`:**

- Keep the existing value/link and filter/pagination characterization tests. Replace `RendersAccessibleResultsTableInBothLocales` with `RendersAccessibleResultCardsInBothLocales`, using its current en/th data provider. Assert one expected h1, one list article, one quiz h2, all six localized field/action labels, associated select labels, a result link, and no unresolved translation keys. Change missing/long-value checks from table XPath to article XPath while retaining zero, fallback, and escaped-script assertions. Add a two-attempt case with IDs 42 and 43, asserting separate articles and matching uniquely named result links.
- Run the focused command below before implementation; expect failures because the current view has no list articles.
- Replace table markup with the stated list/article structure. Keep the existing PHP value calculations and form handlers unchanged. Use visible metadata labels and native semantics rather than table roles. Introduce only kebab-case page classes, preserving existing controller-facing IDs and names.
- Run the same command; expect all Preview cases to pass.

```powershell
$env:PHP_INI_SCAN_DIR = Join-Path $env:TEMP 'eimaths-php-no-extra-ini'
php -c "$env:TEMP/eimaths-elearning-php.ini" vendor/bin/phpunit --do-not-cache-result tests/Feature/Elearning/TestPreviewPageTest.php
```

## Task 2: Apply the approved visual treatment

**Files:** Modify `resources/views/home/test-preview/styles.blade.php`.

- **Consumes:** Task 1 card markup and the existing `.test-preview-page` layout opt-in.
- **Produces:** Navy header, separate white filter bar, grey canvas and white cards with orange score emphasis and filled answer buttons.
- **Checkpoint:** None after plan approval.

**Nonbehavioral presentation work:**

- Apply the pinned palette, spacing, type sizes, and radii above. Replace obsolete table/mobile-cell selectors rather than layering conflicting overrides.
- At widths 768px and above, use a two-region card grid: `minmax(0, 1fr) minmax(240px, 320px)`. Keep participant and quiz details left, score/time/date/action right, with a subtle dividing rule. At widths below 768px, stack the regions and replace the vertical rule with a horizontal rule. Use `min-width: 0` and wrapping for long content. Preserve 44px controls, focus visibility, and status text so meaning never relies only on color.
- Keep mobile filters stacked and desktop filters aligned. Give empty state the same white bordered surface as cards.
- Inspect with real assets at desktop width, 390px, and 200% zoom in Thai and English. Expect no horizontal overflow, readable long content, visible action/focus, and no duplicated navigation caused by missing styles. Do not substitute unrelated CSS if assets fail; report and resolve the loading issue within the authorized local preview scope.

## Task 3: Verify and hand off the visual result

**Files:** Create `docs/plans/0011-test-preview-cards-execution-record.md`.

- **Consumes:** Completed view, styles, tests, disposable local preview, and existing Result page.
- **Produces:** English execution record with checks, review findings, fixes, and explicit limitations.
- **Checkpoint:** No deployment or commit.

**Nonbehavioral verification record:**

- Run `php -c "$env:TEMP/eimaths-elearning-php.ini" vendor/bin/phpunit --do-not-cache-result tests/Feature/Elearning` with the same scan-directory setting; expect Preview and Result tests to pass. Run `git diff --check`; expect exit 0. Broaden testing only if affected behavior or failures warrant it.
- In the local fixture, verify quiz selection, page size, next page, and opening a matching Result in a new tab. Check empty state, long title, zero score, keyboard focus, desktop/mobile, and zoom. Confirm Vite CSS/JS requests succeed before judging screenshots. Do not test with live data or discard unsaved answers.
- Perform final review under the existing review workflow; fix in-scope findings and repeat affected checks. Record any remaining asset, browser, or accessibility limitations accurately.
- Leave the fully styled Preview open for the user and report the local URL and focused verification results. Do not describe the fixture's injected staff identity as validation of normal login.
