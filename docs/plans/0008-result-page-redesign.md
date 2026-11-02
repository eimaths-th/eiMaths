# Result Page Redesign Implementation Plan

> **REQUIRED EXECUTION SKILL:** After plan approval and execution-method selection, use `executing-plans` with the selected method for task-by-task execution.

- **Goal:** Turn the normal e-learning Result page into a restrained, readable exam review sheet.
- **Architecture:** Keep `ResultController::show`, ownership checks, stored scores, and video authorization unchanged. Opt this view into a plain variant of the existing quiz layout. Keep result styles and answer presentation together under `resources/views/home/result/`.
- **Tech Stack:** Laravel Blade, PHP, CSS, existing MathJax, PHPUnit, native browser verification when available.
- **Source:** Conversation-approved approach A and the subsequent approved design, reproduced below.
- **Execution method:** Recommend native execution by the current agent; awaiting selection with plan approval.

## Global Constraints

- Work on the existing `demo` branch. Do not create a worktree.
- Preserve the uncommitted e-learning session work and the stashed `.env.example` change. Do not commit, deploy, or migrate a live database.
- Scope is `home.result`, served by `results/{test}`. Preserve other learning pages and `guest-quiz.result`.
- Use one summary containing quiz title, student identity, score, percentage, and duration. Keep the existing attempt date as secondary metadata. Remove repeated Results headings.
- Use left-aligned content, white background, dark text, grey rules, and restrained eiMaths orange. Green and red identify answer outcomes only.
- Replace nested cards with question sections separated by space and thin rules. Remove gradients, shadows, decorative emojis, and floating hover effects on this page.
- Show the question, explicitly labelled learner and correct answers, and correct/incorrect/unanswered text with symbols. Preserve available option content and the existing fraction formatting helpers.
- Show explanation text and readable images inline; keep authorized solution videos collapsed in native `details` elements.
- Use a single column on mobile, approximately 16px body text, visible keyboard focus, and media that fits the content width. Retain existing fonts; introduce no dependency.
- Preserve scoring, answer correctness, video permission rules, routes, and existing navigation behavior. Do not turn this into an authentication, localization, or scoring refactor.
- Keep Thai presentation labels consistent with the current page. Documents are English. New identifiers follow the supplied naming rules, with framework conventions taking precedence.

## Verified Context and Limits

- `resources/views/layouts/quiz_layout.blade.php` supplies decorative backgrounds, a repeated heading, and nested content wrappers. It includes `layouts.nav-bar` and `home.footer`, which also contain decorative styles.
- `resources/views/home/result.blade.php` supports options, images, written answers, and fractions with option/written variants. It includes hover-only image previews and MathJax plus browser-history handlers.
- `ResultController::show` passes `test`, `questions_count`, `results`, `header`, and `canViewSolutionVideos`. Student ownership and assigned-quiz video checks remain in that controller.
- The current percentage expression divides by question count without a zero guard. The replacement will display an em dash for percentage when the count is zero; it will not invent a score. Missing duration displays an em dash; zero duration displays zero. Duration formatting uses hours, remaining minutes, and seconds without changing stored seconds.
- PHPUnit and the isolated temporary PHP configuration exist. `phpunit.xml` forces SQLite in memory. Existing browser-fixture setup and limitations are documented in `0007-elearning-execution-record.md`.
- The earlier frontend build was blocked by unavailable dependencies/network access. Do not claim a production build or complete browser verification from a fixture that suppresses Vite. Result CSS will be a Blade style partial, requiring no new build step.

## Review Focus

1. Zero score, zero questions, and zero or missing duration must remain distinct and render without division errors.
2. Unanswered values must not be confused with the valid written answer `0`; all five answer variants must retain selected and correct content.
3. A learner without video access must receive no video URL or iframe, including inside collapsed content.
4. Long text, fractions, and explanation images must remain readable at mobile width and 200% zoom, without hover dependence.
5. The plain layout must apply only to Result, preserving existing navigation links, footer content, and default quiz layout behavior.

## Task 1: Introduce the Result-only plain shell

**Files:**
- Modify: `resources/views/layouts/quiz_layout.blade.php` at its head, body, decorative wrappers, heading, and main content.
- Modify: `resources/views/home/result.blade.php` at layout declaration and title.
- Create: `resources/views/home/result/styles.blade.php`.

- **Consumes:** Existing quiz layout, navigation, footer, and content section.
- **Produces:** An opt-in `result_page` layout argument, defaulting to false, and styles scoped to `.result-page`.
- **Checkpoint:** Approved plan and execution method required before editing implementation.

**Nonbehavioral presentation actions:**
- [ ] Pass `result_page => true` only from Result. Set its document title to the quiz title. Include the result style partial only for this variant.
- [ ] Render a white body and a plain main container for the variant; omit decorative pattern, line, duplicate heading, nested card wrappers, and footer spacer only in this branch. Keep default markup and all shared includes available to other views.
- [ ] Use a 960px maximum content width, 24px horizontal padding, 16px at widths below 640px, and 16px/1.6 body text. Use `#1f2937` text, `#6b7280` secondary text, `#e5e7eb` rules, and `#c2410c` orange accents.
- [ ] Flatten navigation and footer decoration using styles scoped to `.result-page`; preserve their content and actions. Remove shadows, text shadows, blur, transforms, and decorative backgrounds in this variant, including inline hover decoration overrides. Do not change global styles.
- [ ] Inspect `git diff -- resources/views/layouts/quiz_layout.blade.php resources/views/home/result.blade.php resources/views/home/result/styles.blade.php`; verify Result-only branching and unchanged default layout output. Run `git diff --check`; expect exit 0.

## Task 2: Render the review sheet and protect answer semantics

**Files:**
- Modify: `resources/views/home/result.blade.php` at summary, answer loop, explanations, and hover preview.
- Modify: `resources/views/home/result/styles.blade.php`.
- Create: `resources/views/home/result/answer.blade.php`.
- Create/Test: `tests/Feature/Elearning/ResultPageTest.php`.

- **Consumes:** Existing controller variables and Eloquent answer relations; `replaceMixedFractions`, `toFraction`, and `toMixedFraction`.
- **Produces:** One summary, semantic question sections, labelled answer content, explicit unanswered states, and inline explanations.
- **Checkpoint:** None after Task 1.

**Production presentation behavior changes — REQUIRED IMPLEMENTATION SKILL: `test-driven-development`:**
- [ ] Create focused view-rendering tests using `Tests\TestCase`, `withoutVite()`, and in-memory model instances with `setRelation` to avoid live database writes. Use `App\Models\Test`, `Quiz`, `Answer`, `Question`, `Contact`, `Option`, `ImageOption`, and `FactionAnswer`; populate the relations the view consumes. Use the existing framework view assertion APIs. Test the content partial separately where shared navigation would require unrelated fixtures.
- [ ] Add `RendersZeroScoreAndDuration`, `RendersAnEmptyQuizWithoutDivision`, and `FormatsDurationWithoutChangingStoredSeconds`: assert 0/2 and 0.00%, zero duration visible, zero-question percentage em dash, null duration em dash, and 3661 seconds displayed as 1 hour 1 minute 1 second.
- [ ] Add parameterized `RendersSelectedAndCorrectAnswers` and `DistinguishesUnansweredFromZero`: cover options, image, written, fraction options, and fraction written. Assert selected and correct content, explicit outcome text, null/empty unanswered state, and written `0` preserved. Include multiple correct options so presentation does not silently discard them.
- [ ] Add `KeepsExplanationTextEscapedAndImagesInline` and `OmitsUnauthorizedVideoUrls`: use explanation text containing `<script>`, an image filename, and `https://drive.google.com/file/d/result-video/preview` as expected embed URL. Assert escaped text, image alternative text, closed `details` for authorized videos, and no iframe or video URL when permission is false.
- [ ] Run `$env:PHP_INI_SCAN_DIR = Join-Path $env:TEMP 'eimaths-php-no-extra-ini'`, then `php -c "$env:TEMP/eimaths-elearning-php.ini" vendor/bin/phpunit --do-not-cache-result tests/Feature/Elearning/ResultPageTest.php`; confirm failures arise from the specified missing presentation behavior before implementation.
- [ ] Replace the card markup with one header/summary and numbered question sections. Keep existing identity and date values. Use stored `correct` for answered outcomes; use answer-field absence for unanswered status. Treat only null or empty text as absent, never `0`.
- [ ] Move type-specific answer markup into `home.result.answer`, receiving the answer record. Use labelled learner/correct rows and a compact option list where applicable. Keep all answer variants and existing formatting helpers; avoid recomputing correctness.
- [ ] Keep escaped explanation text, readable responsive images, and permission-gated native video disclosure. Remove the hover preview script and hover-only image sizing. Preserve MathJax and existing history handlers.
- [ ] Style headings at 28px desktop/24px mobile, question headings at 20px, and body at 16px. Use rules and spacing instead of cards; use text and symbols with outcome colors. Make disclosure targets at least 44px high and focus outlines visible. Let images scale within their natural width and container. Allow local scrolling for exceptionally wide math without page overflow.
- [ ] Rerun the focused command; expect exit 0. Refactor only within these files and rerun affected tests.

## Task 3: Verify the complete page and document limits

**Files:**
- Create: `docs/plans/0008-result-page-execution-record.md`.

- **Consumes:** Finished Result view, prior disposable browser fixture, focused test results.
- **Produces:** Recorded validation evidence and any unresolved visual/build limits.
- **Checkpoint:** Do not claim visual verification without observed rendered output.

**Nonbehavioral verification artifact actions:**
- [ ] Run the focused Result suite and `php -c "$env:TEMP/eimaths-elearning-php.ini" vendor/bin/phpunit --do-not-cache-result tests/Feature/Contact/StudentElearningSessionTest.php` with the temporary scan directory set; expect exit 0. Run `git diff --check`.
- [ ] Use disposable data only for browser rendering. Inspect desktop at 1280px and mobile at 390px, plus 200% zoom, long questions, each answer variant, and explanation media. Tab through navigation and video disclosure; verify visible focus and keyboard expansion.
- [ ] Check one non-Result quiz page and the separate guest Result view for unintended layout changes. Verify authorized video presence and unauthorized absence in rendered HTML without needing to play external content.
- [ ] Record screenshots or concrete observations when computer access is available. If browser access or asset loading remains blocked, report the exact limit; do not equate passing PHP tests with visual acceptance.
- [ ] Review the final diff for preserved session changes, unchanged authorization/scoring logic, Result-only styling, and no extra dependencies. Record commands, results, and limitations in English. Do not commit or deploy.

## Self-Review

The approved design maps to the shell, summary/answers, and visual verification tasks. The zero-question guard and duration normalization are display decisions, not changes to stored results. Build limitations are explicit. No separate subsystem or optional product improvement is included. Plan approval and execution-method selection remain required before source edits.
