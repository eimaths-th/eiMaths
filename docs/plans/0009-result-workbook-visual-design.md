# Friendly eiMaths Result Workbook Implementation Plan

> **REQUIRED EXECUTION SKILL:** Use `executing-plans` after approval of this completed plan. Preserve the previously selected native execution method.

- **Goal:** Give the existing Result page a recognizable, child-friendly eiMaths workbook identity while keeping scores and answer review easy to read.
- **Architecture:** Refine only the existing Result Blade markup and scoped stylesheet. Reuse the current Result-only layout variant and all existing answer, score, permission, and disclosure logic.
- **Tech Stack:** Laravel Blade, CSS, existing image assets, MathJax, existing PHPUnit view tests, native Chrome verification.
- **Source:** The user rejected the first redesign as too plain, selected approach A (a friendly checked workbook), and approved the short design with `ok`. Approved requirements and implementation choices are reproduced below.
- **Execution:** Native execution, carried forward from the same Result task. Work in place on `demo`; no worktree.

## Global Constraints and Design Record

- Intended users are children reviewing their quiz results. The page should feel like eiMaths while making the result and mistakes easy to understand.
- Desktop: lesson title and learner identity on the left; prominent score on the right. Orange-tinted space and one small brand/mascot image establish identity; percentage and duration are secondary.
- Mobile: score appears first, followed by learner/lesson information and one-column answer review. Keep a logical reading order and heading structure.
- Questions: prominent numbers, clear question text, compact choices, learner/correct answer labels close to their content, and less empty horizontal space.
- Explanations: a light background separates the worked solution from the question, inline readable images, and an obvious video disclosure control.
- Anti-goals: nested cards, shadows, gradients, floating effects, medals/stars, invented achievements, and praise unrelated to performance.
- Preserve all five answer variants, stored scores/correctness, zero and unanswered semantics, video access rules, existing routes, navigation behavior, and other learning/Free Trial pages. No controller, schema, authentication, or dependency change.
- Preserve prior uncommitted login and Result work and the existing stash. No commit, deploy, or live migration.
- User chose A over the alternative modern school report, whose restrained typography could remain too plain. The implementer proposes the exact tokens and layout below; they are not user-specified pixel values.
- Asset choice: reuse the complete existing `public/images/Eimaths-Logo-Without-Halo.png`, visually inspected and containing the lion mascot. Display it at 148px wide in the summary only, with empty alt text as redundant decoration. Do not crop, redraw, or generate a new character.
- Palette: white `#ffffff`, orange tint `#fff3e6`, orange `#c2410c`, dark text `#1f2937`, secondary text `#6b7280`, divider `#e5e7eb`. Retain the current correct/incorrect colors. Keep the current font stack and 16px/1.6 body text.

## Review Focus

1. Zero score and missing duration remain visible and distinct; visual prominence must not imply an achievement or change a value.
2. Long lesson/learner names wrap at 390px and 200% zoom without colliding with the score or image.
3. Text, image, and fraction choices retain correct/selected labels next to the intended option, including multiple correct choices and unanswered records.
4. Larger inline images and MathJax remain contained; status meaning stays available in text rather than color alone.
5. Video disclosure stays keyboard-operable with visible focus; unauthorized users still receive no video URL.

## Task 1: Give the summary a clear visual identity

**Files:** Modify `resources/views/home/result.blade.php` at `.result-heading` / `.result-summary`; modify `resources/views/home/result/styles.blade.php` at the corresponding selectors.

- **Consumes:** Current controller-provided values and the existing logo asset.
- **Produces:** A branded, responsive summary with unchanged displayed values and readable order.
- **Checkpoint:** Approval of this plan before implementation.

**Nonbehavioral presentation actions:**
- [ ] Run the existing Result suite as a baseline using the command below; expect 16 passing cases. Record any discrepancy before editing.
- [ ] Group score, lesson/learner information, and secondary metrics into explicit layout regions without changing their expressions. Put the score region first in DOM order; use named CSS grid areas to position identity left and score right on desktop. Keep one `h1` for the quiz title.
- [ ] Use a white identity region and an orange-tinted score region inside one summary, with a 4px orange top rule and 12px outer radius, no shadow. At 640px and above use `minmax(0, 1fr) 220px`; below 640px stack the score above identity. Keep the container width at 960px.
- [ ] Set score text to 64px desktop / 48px mobile, title to 30px / 26px, secondary information to 14px. Use 24px summary padding desktop / 20px mobile. Place the 148px brand image in the score region without overlapping text. Preserve the existing numeric score string and percentage formatting.
- [ ] Run `git diff --check`; inspect the summary diff for unchanged value/permission logic. Re-run the Result suite; expect exit 0.

## Task 2: Make answer review compact and easy to scan

**Files:** Modify `resources/views/home/result/answer.blade.php` at question headings and presentation wrappers only; modify `resources/views/home/result/styles.blade.php` at question, option, explanation, and video selectors.

- **Consumes:** Existing answer markup and all existing formatting/permission branches.
- **Produces:** Workbook-style question numbering, nearby answer labels, and a clearly separated worked solution.
- **Checkpoint:** None after Task 1 checks pass.

**Nonbehavioral presentation actions:**
- [ ] Give each existing question number a 36px orange-tinted marker, retain the visible word/number and explicit status text, and set question text to 18px/1.65. Keep sections separated by thin rules rather than enclosing every section in a card.
- [ ] Reduce question section padding from 32px to 24px and question-to-answer spacing to 16px. Constrain choice content to 680px maximum. Remove the flex growth that pushes option labels to the far right; place labels after the answer with a 12px gap and allow wrapping beneath that same answer on narrow screens. Align image option letters with the top of their image rather than the image baseline.
- [ ] Present written/fraction-written learner and correct answers in two adjacent columns above 640px and stack them below that width. Use plain labels and values, not nested bordered cards. Preserve all option contents, labels, and expressions.
- [ ] Give the explanation area a `#fff3e6` background, 16px padding, 8px radius, and a 3px orange left rule. Keep images responsive and visible without hover. Style the existing video summary as a compact white control with an orange border, 44px minimum height, and the existing visible focus outline; preserve native `details` behavior.
- [ ] Run the Result suite and `git diff --check`; expect exit 0. Inspect all five variants for preserved semantics. Do not add tests that merely assert CSS implementation details.

## Task 3: Verify rendering and record evidence

**Files:** Create `docs/plans/0009-result-workbook-execution-record.md`.

- **Consumes:** Final scoped diff, existing automated tests, and disposable browser fixture.
- **Produces:** Evidence of the redesigned appearance, preserved semantics, and any remaining integration limitations.
- **Checkpoint:** Do not claim production visual acceptance when bundle or browser checks are incomplete.

**Nonbehavioral verification actions:**
- [ ] Run the focused Result and student session suites, then the required full PHP and JavaScript suites. Record exact results; do not repair unrelated failures. The prior full PHP run had 66 failures, one error, and three skips; compare failing identities if those recur rather than assuming every failure is pre-existing.
- [ ] Re-render the existing disposable preview with the latest views. Inspect score zero, unanswered cases, all five answer variants, long lesson/learner names, images, and fractions at 390px and desktop width, plus 200% zoom. Verify the image does not crowd the score, labels remain close to their option, and no observed content is clipped.
- [ ] Verify video disclosure using keyboard input and visible focus. Confirm forbidden video URLs remain absent through the automated suite. Review the diff to ensure other pages and shared layout files were not changed.
- [ ] Record native Chrome observations and screenshot limitations. Existing preview uses `withoutVite()`; shared navigation is visibly incomplete and Livewire reports outdated published assets. Production navigation/bundle integration must remain explicitly unverified until real assets are available. Do not silently repair the shared navigation or dependency setup as part of this visual revision.
- [ ] Obtain final implementation review under `reviewing-code`, record findings/dispositions, and leave the result uncommitted on `demo`.

## Verified Commands and Dependencies

PowerShell, working directory `C:/Users/user/github/eimaths/eimaths_backend`:

```powershell
$env:PHP_INI_SCAN_DIR = Join-Path $env:TEMP 'eimaths-php-no-extra-ini'
php -c "$env:TEMP/eimaths-elearning-php.ini" vendor/bin/phpunit --do-not-cache-result tests/Feature/Elearning/ResultPageTest.php
php -c "$env:TEMP/eimaths-elearning-php.ini" vendor/bin/phpunit --do-not-cache-result tests/Feature/Contact/StudentElearningSessionTest.php
php -c "$env:TEMP/eimaths-elearning-php.ini" vendor/bin/phpunit --do-not-cache-result
node --test tests/JavaScript/*.test.mjs
git diff --check
```

These commands and test paths were executed in the preceding Result revision. The tests force isolated SQLite configuration. The preview renderer is `%TEMP%/eimaths-result-preview/render.php`, and its local router serves port 18972; verify current availability before reuse. No new library, font, image-generation service, or build configuration is required.

## Self-Review

The approved direction maps to the summary and answer-review tasks; verification covers the named edge cases. All changes are presentation-only and preserve existing logic, so existing semantic tests and real rendering are the acceptance checks. Exact visual tokens and reuse of the complete brand image are implementer proposals made reviewable in this plan. Shared asset issues remain separate and disclosed. This plan is not approved merely because the earlier design was approved.
