# Result Review Cards Implementation Plan

> **REQUIRED EXECUTION SKILL:** After plan approval, use `executing-plans` with native execution and final independent review. Do not create a worktree.

- **Goal:** Give the single-attempt Result page a navy score header and distinct white question cards matching the approved Preview design, while keeping answers and explanations easy to compare.
- **Architecture:** Change presentation in the existing Result view, answer partial, and scoped stylesheet only. Retain the shared layout, footer, controller data, calculations, question ordering, and video authorization.
- **Tech Stack:** Laravel Blade, existing CSS and MathJax, existing PHPUnit view tests, native Chrome with real Vite assets.
- **Source:** The user confirmed `/results/{test}`, selected A, and approved the short design: navy header with quiz/participant left and large orange score right; percentage/time/date below; pale-grey canvas and separate white question cards; numbered questions and text status badges; selected/correct answers differentiated with borders and labels; side-by-side written answers; a lightly colored explanation area; existing expandable video; mobile stacking. Prior workbook styling is superseded by this visual choice.

## Global Constraints

- Work in the existing `demo` checkout, HEAD `4fb6262306765c24e7e079ed45573f9e06d657ac`. Preserve unrelated pending work, especially Preview and the newly shared footer. No commit, deployment, migration, dependencies, or worktree creation.
- Keep all score/percentage/duration/date expressions, participant fallback, answer classification, zero-versus-unanswered behavior, correct-option collection, formatting helpers, image paths, escaping, and video URL gating unchanged.
- Preserve Thai Result labels as currently implemented. Full Result localization is outside this design task; the shared footer continues using its existing locale handling.
- Preserve the existing MathJax loading, history/beforeunload behavior, and closed-by-default video details. Do not accept leave-page prompts that could discard user state during verification.
- No emoji, decorative metrics, animations, gradients, or new features. Use existing fonts and the shared footer without overriding it.
- Assistant-specified visual values: canvas `#f2f4f7`, header `#14283f`, white cards, header score `#fb923c`, body text `#1f2937`, secondary text `#64748b`, borders `#dce2e8`. Maximum content width 1100px; desktop outer padding 40px 24px 64px; cards 24px padding, 12px radius, 20px gaps.

## Review Focus

- Zero score and zero questions must not become missing values or cause division errors; unknown duration remains a dash.
- A selected correct choice must show both labels. Every correct option remains visible, even when multiple options are correct. Unanswered choices must not look selected.
- Long titles, image choices, fractions, and equations must fit; wide equations may scroll inside their own content region without widening the page.
- Explanations remain escaped and unauthorized video URLs must not appear in HTML.
- Result changes must not override the shared footer or alter Preview styles and routes.

## Task 1: Reshape the score summary

**Files:** Modify `resources/views/home/result.blade.php` and `resources/views/home/result/styles.blade.php`.

- **Consumes:** Existing `$test`, `$questions_count`, `$results`, and `$canViewSolutionVideos` contract.
- **Produces:** An h1-led navy identity/score area and a full-width light summary strip below it, retaining all current displayed values.
- **Checkpoint:** Begin only after explicit approval of this completed plan.

**Nonbehavioral presentation work:**

- Run the affected suite below before changes; expect the existing 23 tests to pass.
- Put identity before score in DOM reading order. Remove the decorative duplicate logo inside the score panel; navigation and footer retain the brand. Preserve title, participant name/detail, and the score definition list.
- Make `.result-heading` a two-column grid `minmax(0, 1fr) 240px` with identity left and score right on navy. Use 32px inner padding, 32px white title, pale secondary text, and a 64px orange score. Use a subtle vertical divider before the score.
- Consolidate percentage, duration, and optional date into the existing summary definition list, spanning the full header width on white with dark text. Keep date formatting and its machine-readable `datetime` unchanged. Remove the obsolete separate date paragraph styles.
- Below 768px, stack identity, score, and summary in that DOM order. Use 24px header padding, 26px title, and 48px score; replace the vertical score divider with a horizontal rule. Let summary fields wrap naturally.

## Task 2: Give each question a clear review card

**Files:** Modify `resources/views/home/result/answer.blade.php` and `resources/views/home/result/styles.blade.php`.

- **Consumes:** Existing `$answer`, `$question_number`, `$can_view_videos`, `$status`, `$is_selected`, and `$choice->correct` values.
- **Produces:** White question cards, text status badges, outlined answer options, and a distinct explanation area with unchanged content and actions.
- **Checkpoint:** None after plan approval.

**Nonbehavioral presentation work:**

- Keep existing article/heading IDs, labels, and article ordering. Give every question a white background, border, padding, and radius; replace the continuous divider layout. Keep a 24px review section title above the list.
- Style the question number as a compact navy tile with white text. Turn existing status spans into small badges: correct `#166534` on `#f0fdf4`, incorrect `#991b1b` on `#fef2f2`, unanswered `#475569` on `#f1f5f9`. Preserve their existing text and symbols, so status is not color-only.
- Add conditional kebab-case option classes derived only from the already computed `$is_selected` and existing `$choice->correct`. Do not change how either value is determined. Each option gets 12px padding, 8px radius, a thin neutral border, and 8px spacing. Selected options have an orange 2px border; correct options have a pale-green background. A selected correct option retains both treatments and both existing labels.
- Keep written/fraction-written answer pairs in two columns with separate lightly bordered surfaces; stack below 768px. Keep choice images proportional with the existing size limits and alt text, and preserve local overflow handling for formulas.
- Use a light warm explanation surface `#fff7ed`, a thin `#fed7aa` border, 16px padding, and a clear method heading. Retain images inline and existing video details/iframe behavior. Use orange outlined video controls with visible focus and a 44px minimum target.
- Remove replaced/obsolete rules rather than appending conflicting overrides. Do not touch footer or global navigation rules.

## Task 3: Verify and present the result

**Files:** Create `docs/plans/0013-result-review-cards-execution-record.md`.

- **Consumes:** Completed Result presentation and existing regression fixtures.
- **Produces:** English execution record covering approval, checks, review dispositions, screenshots inspected, and explicit limitations.
- **Checkpoint:** No commit or deployment.

**Nonbehavioral verification work:**

```powershell
$env:PHP_INI_SCAN_DIR = Join-Path $env:TEMP 'eimaths-php-no-extra-ini'
php -c "$env:TEMP/eimaths-elearning-php.ini" vendor/bin/phpunit --do-not-cache-result tests/Feature/Elearning
git diff --check
```

- Expect all existing 23 tests to pass, covering Result's five answer types, zero/unanswered cases, multiple correct choices, escaped explanations, and video gating, plus Preview regression checks. Do not add tests that merely mirror CSS declarations. Add behavioral tests only if an actual behavior change becomes necessary, using TDD and obtaining approval for scope changes first.
- Inspect rendered selected/correct choice classes against the visible labels for wrong, correct, and unanswered fixtures. Verify exact displayed score, percentage, duration, and date remain unchanged from the baseline.
- Use disposable local data and actual Vite assets for visual checks. Inspect desktop, 390px, and 200% zoom; long quiz/participant text; status variants; written, image, and fraction choices; explanation text/images; collapsed video control; and shared footer. Use an isolated view fixture if the existing browser dataset lacks an answer type, clearly labeling it as fixture evidence.
- Inspect formula rendering after MathJax settles; report external-resource failures separately from template checks. Navigate controls using the keyboard without opening unrelated external contacts. Preserve existing Result tabs if beforeunload appears.
- Obtain a fresh independent review under `reviewing-code` of the scoped final files against this plan. Resolve in-scope findings and rerun affected checks after changes. Broaden tests only when a new concern warrants it; do not claim full-suite readiness from focused checks.
- Leave the new Result design visible for the user and report the local URL, checks, and any remaining limitations. No shared footer, Preview, controller, route, authorization, or stored data changes are authorized by this plan.
