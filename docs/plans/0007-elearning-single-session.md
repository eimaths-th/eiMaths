# E-learning Single Active Session Implementation Plan

> **REQUIRED EXECUTION SKILL:** After plan approval and execution-method selection, use `executing-plans` for task-by-task execution. Use `test-driven-development` for all production behavior changes.

- **Goal:** Each student account has one active e-learning browser session. A successful newer login replaces the previous session.
- **Architecture:** Store a nullable SHA-256 session-token hash on `contacts`, with its random token in the student browser session. Validate it in middleware attached only to the existing e-learning route group. Keep lifecycle logic in one focused domain service.
- **Tech Stack:** Laravel 10, PHP 8.1+, existing session guard, relational database, PHPUnit 10, Blade and browser JavaScript.
- **Source:** Conversation-approved design: option A selected, detailed design approved with `ok`. Requirements are reproduced below.
- **Execution method:** Pending selection. Recommend native execution because the database, login, middleware and quiz response handling form one tightly coupled change. Alternative: per-task independent implementer and reviewer.

## Global Constraints

- Restrict the rule to student e-learning authentication. Preserve staff, e-book and free-quiz behavior.
- Latest successful login wins. Invalid credentials must not revoke an existing session.
- One session means one browser session, not verified physical hardware. Tabs sharing that session remain usable.
- Reject obsolete sessions on subsequent requests, including answer saving and quiz submission. Requests already admitted may finish.
- Logout from an obsolete session must not revoke its replacement. Preserve unrelated session contents and other guards.
- Existing student sessions without a token must log in again after deployment. Previously saved answers remain; unsaved answers may be lost.
- Preserve the existing `.env.example` modification. Do not deploy, migrate a live database, commit, or modify other authentication systems.
- Follow existing PSR-4 class filenames and PHPUnit discovery conventions; use requested naming conventions for new identifiers where framework contracts allow.
- Write documentation in English; maintain both supported UI translation locales.

## Verified Integration Points

- `app/Http/Controllers/ContactController.php`: `studentsCheckLogin` validates credentials, calls the `student` guard and redirects to `home3`; `studentsLogout` currently flushes the entire session.
- `app/Models/Contact.php`: authenticatable student model using `contacts`; `$hidden` currently includes password and remember token.
- `routes/web2.php`: the group using `auth.userOrStudent` and `affiliate.redirect` contains learning navigation, answer save/check, submission and results.
- `app/Http/Middleware/CheckUserOrStudent.php`: allows either student or staff authentication. New student-session enforcement must run before this authorization so a stale student cannot fall through to a coexisting staff identity.
- `resources/views/home/quiz-show.blade.php`: `saveAnswer` uses fetch, and answer checking uses jQuery AJAX. The template is also used by guest quiz and must preserve that flow.
- `resources/views/contacts/login.blade.php`: renders the `error` validation message and opens the student login form for that error.
- `lang/en/students.php` and `lang/th/students.php`: existing student translations.
- `tests/Feature/Contact/StudentCodeIntegrityTest.php`: provides student fixtures and existing login assertions; uses `RefreshDatabase`.
- `phpunit.xml`: forces SQLite `:memory:`, testing environment, array sessions and disabled affiliate features. `php vendor/bin/phpunit --version` succeeds with PHPUnit 10.5.66; local PHP emits duplicate-extension warnings. Runtime tests have not yet run.

## Review Focus

1. An obsolete browser attempts logout after replacement: the new session must remain valid.
2. Staff and student guards coexist: reject stale student requests without logging out staff or clearing unrelated state.
3. Two successful logins overlap: the last committed token replacement is authoritative; at most one token matches.
4. An old browser submits an answer: middleware must stop controller execution, and the browser must display the session-expiry message rather than a JSON parsing error.
5. Legacy sessions lack tokens: reject them rather than silently accepting or upgrading them.

## Task 1: Persist and Manage the Authoritative Student Session

**Files:**
- Create: `database/migrations/2026_11_02_000001_add_elearning_session_token_hash_to_contacts.php`
- Create: `app/Domain/Elearning/StudentSessionService.php`
- Modify: `app/Models/Contact.php` at `$hidden`
- Modify: `app/Http/Controllers/ContactController.php` at `studentsCheckLogin` and `studentsLogout`
- Create/Test: `tests/Feature/Contact/StudentElearningSessionTest.php`

- **Consumes:** Existing contact credentials, `Auth::guard('student')`, Laravel request session and database connection.
- **Produces:** Nullable `contacts.elearning_session_token_hash` (`char(64)`), session key `elearning_session_token`, and service methods `Start(Contact $student, Request $request): void`, `IsCurrent(Contact $student, Request $request): bool`, `End(Contact $student, Request $request): void`.
- **Checkpoint:** Approved plan and execution method required; stop if the test environment is not SQLite in-memory.

**Production behavior actions:**
- [ ] Use TDD. Create the test class using `RefreshDatabase`, assert SQLite in-memory before fixture creation, and fake outbound HTTP/mail/notifications following `StudentCodeIntegrityTest`. Create department ID 2 and a student with code `BR0001`, password `correct-password`, type `student` and centre 2.
- [ ] Add assertions for successful login producing a nonempty token whose SHA-256 equals the stored hash; a second login changing it; incorrect password leaving the hash unchanged; legacy/missing/incorrect tokens failing `IsCurrent`; current token succeeding; hash absent from model serialization.
- [ ] Add assertions that `End` with the obsolete token preserves the replacement hash, while `End` with the current token clears it. Assert student logout preserves an authenticated staff guard and an unrelated session sentinel.
- [ ] Run `php vendor/bin/phpunit tests/Feature/Contact/StudentElearningSessionTest.php`; require failures caused by the missing feature before implementing.
- [ ] Add the nullable column, with a `down` migration dropping only that column; hide it on the model and keep it outside mass assignment inputs.
- [ ] Implement `Start` with `bin2hex(random_bytes(32))`; persist only its SHA-256 hash via a single row update, then store the raw token in the request session. Missing/deleted contacts or database failures must not yield a successful login. Compare a fresh database hash using `hash_equals`, rejecting null/empty tokens and hashes.
- [ ] Invoke `Start` only after credentials pass and before authenticating the student. Retain guard login/session regeneration and existing redirect behavior. Define concurrent winner as the last committed database update.
- [ ] Implement `End` with a conditional update matching contact ID and the current token hash, then remove only `elearning_session_token`. Replace `Session::flush()` in `studentsLogout` with this cleanup and student-guard logout. Do not modify `quizUserLogout`.
- [ ] Rerun the focused test and `php vendor/bin/phpunit tests/Feature/Contact/StudentCodeIntegrityTest.php`; expect exit 0 and unchanged credential/duplicate-code behavior. Refactor only within this feature and rerun affected tests.

## Task 2: Enforce the Session on E-learning Requests

**Files:**
- Create: `app/Http/Middleware/EnsureCurrentStudentSession.php`
- Modify: `routes/web2.php` at the e-learning route group
- Modify: `lang/en/students.php` and `lang/th/students.php` at `login`
- Test: `tests/Feature/Contact/StudentElearningSessionTest.php`

- **Consumes:** Task 1 `IsCurrent` and student authentication.
- **Produces:** Middleware `handle(Request $request, Closure $next): Response`; JSON 401 payload keys `code`, `message`, `login_url`, with code `ELEARNING_SESSION_REPLACED`.
- **Checkpoint:** None after plan approval; stop for a scope change.

**Production behavior actions:**
- [ ] Write failing tests using two independent session stores/tokens for one student; rebind guards between requests to avoid cached-user false positives. Assert replacement denial, current-session acceptance, repeated acceptance for shared-tab requests, legacy rejection, and staff-only bypass. For isolated middleware checks use a downstream closure sentinel and assert it is never called on denial.
- [ ] Test route middleware coverage for every member of the existing e-learning group, including `quiz.saveAnswer`, `quiz.submit`, `quiz.next`, `check.answer` and `results.show`. Assert no addition to e-book, free-quiz, or staff-management routes.
- [ ] Test browser redirects to `student.login` with validation key `error`, and JSON 401 responses for obsolete-session answer requests. Test stale student plus authenticated staff is rejected while staff remains authenticated.
- [ ] Run the focused test command; confirm missing enforcement causes the expected failures.
- [ ] Attach the middleware by class reference before `auth.userOrStudent` only on this route group. Do not alter the shared authorization middleware. Bypass when the student guard is absent; otherwise require `IsCurrent`.
- [ ] On mismatch, log out only the student guard, forget only the e-learning token, and set the localized validation error. Return JSON for `expectsJson()` or `ajax()`; otherwise redirect. Never clear the authoritative database token on mismatch.
- [ ] Add translation key `students.login.session_replaced`: English `This account was signed in elsewhere. Please sign in again.`; Thai `บัญชีนี้เข้าสู่ระบบจากที่อื่น กรุณาเข้าสู่ระบบอีกครั้ง`. Use it for legacy sessions as well, without adding a separate recovery workflow.
- [ ] Rerun focused tests; expect exit 0, no controller side effects on denied requests, and retained staff access. Refactor and rerun affected checks.

## Task 3: Show Session Replacement During Quiz Actions

**Files:**
- Modify: `resources/views/home/quiz-show.blade.php` at `saveAnswer` and the answer-check AJAX request
- Test: `tests/Feature/Contact/StudentElearningSessionTest.php`

- **Consumes:** Task 2 JSON 401 contract and existing login-page error rendering.
- **Produces:** Quiz requests handle `ELEARNING_SESSION_REPLACED` by navigating to the returned same-origin login URL, where the flashed error is displayed.
- **Checkpoint:** None; disclose any unavailable browser verification rather than claiming it passed.

**Production behavior actions:**
- [ ] Extend the HTTP test to request JSON, receive the replacement response, follow its login URL and assert the localized error is rendered. Run it and record the result before UI changes; this checks the response/redirect contract, not JavaScript execution.
- [ ] In a local browser with disposable test fixtures, reproduce the current fetch/AJAX failure after another login. Do not use production data. Record the observed failure before editing; if local browser execution is unavailable, report this verification gap.
- [ ] Add `Accept: application/json` for answer-save fetch. Inspect status and the error code before calling `moveToNextQuestion`; on replacement navigate to `login_url` and stop the success chain. Add equivalent code-specific handling to the answer-check AJAX error callback. Preserve normal and guest success paths and unrelated errors.
- [ ] Keep the server's flashed validation error available for the redirected login page. Do not expose raw tokens in responses, logs or templates.
- [ ] Run `php vendor/bin/phpunit tests/Feature/Contact` and inspect failures, warnings and skips. Expect exit 0 and no regressions in the contact suite. Run `php -l` on each changed PHP file and `git diff --check`; expect exit 0.
- [ ] Verify in two disposable browser sessions: B login replaces A; A save/check shows login with the message; B continues; A logout does not revoke B; same-browser tabs continue; staff and guest quiz still work. No claim of immediate tab closure or cancellation of admitted requests.
- [ ] Inspect final diff and status. Preserve `.env.example`. Record actual verification results, the one-time login requirement and deployment order (migration before serving new application code). Do not run production migration or deployment.

## Handoff and Approval

This file is the completed implementation proposal, not authorization to execute. Obtain explicit plan approval and execution-method selection. Then read `git-worktrees` and `executing-plans` before preparing the execution workspace. No commit is required by this plan.
