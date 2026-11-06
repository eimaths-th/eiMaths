# Student Login Bot Protection and Privacy Consent Implementation Plan

> **REQUIRED EXECUTION SKILL:** After plan approval and execution-method selection, use `executing-plans` with the selected method for task-by-task execution.

- **Goal:** All four public e-learning forms are protected from bots, and new Free Trial registrations store privacy acknowledgement and optional marketing consent.
- **Architecture:** A focused `RecaptchaVerifier` returns a result, controllers apply each form's failure policy, shared Blade partials keep forms identical, and named rate limiters run on the routes.
- **Tech Stack:** Laravel, Blade, PHPUnit, in-memory SQLite in tests, Google reCAPTCHA v3.
- **Source:** `docs/specs/0004-student-login-bot-protection-and-privacy-consent-design.md`

## Global Constraints

- Work only in `eimaths_backend`. Do not change affiliate forms or `NoCaptcha` markup.
- `RECAPTCHA_ENABLED` defaults to `false`. Secret keys are never rendered or logged.
- Logs never contain tokens, passwords, codes, email addresses, phone numbers, or IP addresses.
- Only `marketing_consent = true` means consent.
- Existing backend test failures are outside this work. Success is no new failures against the failure list recorded in Task 0.
- Every production step needs the user's confirmation.

## Review Focus

- A student on a low score must see retry and LINE guidance, and the correct login panel must reopen.
- A wrong password during a Google outage must still fail.
- A registration rejected by reCAPTCHA must create no account and keep the applicant's input and checkbox choices.
- Two students at one school must not share the per-account login limit.
- A guest with `marketing_consent = null` must not show a withdraw button and must never count as consented.

## Task 0: Record the Test Baseline

**Files:** none.

- **Consumes:** current `demo` revision
- **Produces:** a failure list at a path outside the repository
- **Checkpoint:** none

**Actions:**

**For nonbehavioral artifacts:**

- [ ] Run `php artisan test --log-junit <scratchpad>/backend-junit-0016-baseline.xml` in `eimaths_backend`.
- [ ] Parse the failing `classname::name` values; expect the same 65 failures reported on 2026-11-06.
- [ ] Inspect `git status --short`; expect only the untracked `storage/fonts/Helvetica.afm.json` and this work's documents.

## Task 1: Verifier Returns a Reliable Result

**Files:**

- Create: `app/Support/Recaptcha/RecaptchaResult.php`
- Create: `app/Support/Recaptcha/RecaptchaVerifier.php`
- Modify: `config/services.php` under a new `recaptcha` key
- Modify: `.env.example`
- Test: `tests/Feature/Guest/RecaptchaVerifierTest.php`

- **Consumes:** `Illuminate\Support\Facades\Http`, `Illuminate\Support\Facades\Log`
- **Produces:** `RecaptchaVerifier::IsEnabled(): bool`, `SiteKey(): string`, `Verify(?string $token, string $action, ?string $ip = null): RecaptchaResult`
- **Checkpoint:** none

**Actions:**

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Write the test with `config(['services.recaptcha' => [...]])` for `enabled` true, `site_key` `site`, `secret_key` `secret`, `min_score` `0.5`, and `allowed_hostnames` `app.eimaths-th.com`. Use `Http::fake` on `https://www.google.com/recaptcha/api/siteverify`.
- [ ] Add these test cases:
  - `null` and `''` tokens return `REJECTED`, with `Http::assertNothingSent`.
  - `['success' => true, 'score' => 0.9, 'action' => 'student_login', 'hostname' => 'app.eimaths-th.com']` returns `PASSED`.
  - Score `0.49`, action `guest_login`, and hostname `evil.test` each return `REJECTED`.
  - Score exactly `0.5` returns `PASSED`.
  - `['success' => false, 'error-codes' => ['timeout-or-duplicate']]` returns `REJECTED`.
  - `['success' => false, 'error-codes' => ['invalid-input-secret']]` and `missing-input-secret` return `UNAVAILABLE`.
  - HTTP 500 and `Http::failedConnection()` return `UNAVAILABLE`.
  - `IsEnabled` is false when `enabled` is false, when `site_key` is empty, and when `secret_key` is empty. A missing key while enabled logs at error level.
  - `Log::spy` shows `RECAPTCHA_CHECK` with `recaptcha_action`, `recaptcha_result`, and `recaptcha_score`, and no `token`, `ip`, or secret in the context. `UNAVAILABLE` logs at warning, other results at info.
- [ ] Run `php artisan test --filter=RecaptchaVerifierTest`; expect failure because the classes do not exist.
- [ ] Create the backed enum with cases `PASSED`, `REJECTED`, `UNAVAILABLE`, and create the verifier using the spec's Verifier table with `Http::asForm()->timeout(3)`. Catch `Illuminate\Http\Client\ConnectionException` for `UNAVAILABLE`.
- [ ] Add the `recaptcha` config using the spec's Configuration table, with `allowed_hostnames` compared after trimming each comma-separated value. Add `RECAPTCHA_*` entries with empty keys and `PRIVACY_*` entries to `.env.example`.
- [ ] Run `php artisan test --filter=RecaptchaVerifierTest`; expect exit code 0.

## Task 2: Student Login Applies the Login Policy

**Files:**

- Create: `resources/views/partials/recaptcha.blade.php`
- Create: `resources/views/partials/privacy-notice-link.blade.php`
- Create: `config/privacy.php`
- Modify: `app/Http/Controllers/ContactController.php` at `studentsCheckLogin`
- Modify: `resources/views/contacts/login.blade.php` at the `student.check` form
- Modify: `lang/en/students.php` and `lang/th/students.php` under `login`
- Test: `tests/Feature/Contact/StudentLoginRecaptchaTest.php`

- **Consumes:** `RecaptchaVerifier`, `RecaptchaResult`
- **Produces:** `partials.recaptcha` with `$recaptcha_action`, and `partials.privacy-notice-link`
- **Checkpoint:** none

**Actions:**

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Write the test using the `setUp` and `CreateStudent` setup of `tests/Feature/Contact/StudentElearningSessionTest.php`, with these cases against `POST /students-login-check` and `from('/students-login')`:
  - Rejected score: redirects back, has the `error` session error, `assertGuest('student')`.
  - Missing token: same result, and Google is not called.
  - Unavailable with correct password: redirects to `route('home3')` and is authenticated.
  - Unavailable with wrong password: has `error`, and `assertGuest('student')`.
  - Passed with correct password: redirects to `route('home3')`.
  - Disabled reCAPTCHA: login works with no token and no HTTP call.
  - `GET /students-login` while enabled renders `data-recaptcha-action="student_login"`, the configured `notice_url`, and does not render the secret.
  - `GET /students-login` while disabled renders no `recaptcha/api.js`.
- [ ] Run `php artisan test --filter=StudentLoginRecaptchaTest`; expect the rejected, missing-token, and rendering cases to fail.
- [ ] Create `config/privacy.php` using the spec's table.
- [ ] Add `recaptcha_token` as `nullable|string|max:4000` to the validation in `studentsCheckLogin`, and inject `RecaptchaVerifier $recaptcha`. After validation and before the student lookup, return `back()->withErrors(['error' => __('students.login.recaptcha_rejected')])` when `IsEnabled()` is true and `Verify($request->input('recaptcha_token'), 'student_login', $request->ip())` is `REJECTED`.
- [ ] Add `recaptcha_notice`, `recaptcha_rejected`, and `privacy_notice_link` keys to both language files. `recaptcha_rejected` tells the user to try again or contact eiMaths on LINE.
- [ ] Create `partials/privacy-notice-link` as a link to `config('privacy.notice_url')` opening in a new tab with `rel="noopener noreferrer"`.
- [ ] Create `partials/recaptcha`. When enabled, it renders a hidden `recaptcha_token` input, the notice text, and, once per page using `@once`, the script `https://www.google.com/recaptcha/api.js?render=` with the URL-encoded site key, plus this behavior: on submit of `form[data-recaptcha-action]`, prevent default, ignore repeat submits while pending, call `grecaptcha.execute` inside `grecaptcha.ready`, set the token, and call `form.submit()`. If `grecaptcha` is undefined or execution fails, submit without a token.
- [ ] Add `data-recaptcha-action="student_login"` to the student form, and include both partials after its submit button.
- [ ] Run `php artisan test --filter=StudentLoginRecaptchaTest`; expect exit code 0.

## Task 3: Free Trial Login Applies the Login Policy

**Files:**

- Modify: `app/Http/Controllers/GuestQuizController.php` at `login`
- Modify: `resources/views/contacts/login.blade.php` at the `guest.quiz.login` form
- Test: `tests/Feature/Guest/GuestLoginRecaptchaTest.php`

- **Consumes:** Task 1 and Task 2 interfaces
- **Produces:** guest login protection using action `guest_login`
- **Checkpoint:** none

**Actions:**

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Write the test with `RefreshDatabase` and a `GuestQuizzer::create` row with a hashed phone and a future `expires_at`, against `route('guest.quiz.login')`:
  - Rejected and missing token: has `guest_error`, `assertGuest('guest_quiz')`.
  - Unavailable with correct credentials: authenticated on `guest_quiz`.
  - Unavailable with wrong credentials: has `guest_error`, `assertGuest('guest_quiz')`.
  - Disabled reCAPTCHA: login works.
  - The login page renders `data-recaptcha-action="guest_login"`.
- [ ] Run `php artisan test --filter=GuestLoginRecaptchaTest`; expect the rejected, missing-token, and rendering cases to fail.
- [ ] Add `recaptcha_token` as `nullable|string|max:4000` to the `login` validator, and inject `RecaptchaVerifier $recaptcha`. After validation, return `back()->withErrors(['guest_error' => __('students.login.recaptcha_rejected')])->withInput($request->only('guest_email'))` on `REJECTED`.
- [ ] Add `data-recaptcha-action="guest_login"` to the form, and include both partials after its submit button.
- [ ] Run `php artisan test --filter=GuestLoginRecaptchaTest`; expect exit code 0.

## Task 4: Registration Stores Consent and Rejects Failed Checks

**Files:**

- Create: `database/migrations/2026_11_06_000002_add_privacy_consent_to_guest_quizzers_table.php`
- Create: `app/Support/Privacy/GuestPrivacyConsent.php`
- Create: `resources/views/partials/guest-privacy-consent.blade.php`
- Modify: `app/Models/GuestQuizzer.php` at `$fillable` and `$casts`
- Modify: `app/Http/Controllers/GuestQuizController.php` at `register`
- Modify: `resources/views/contacts/login.blade.php` at the `guest.quiz.register.store` form
- Modify: `resources/views/guest-quiz/register.blade.php` at the `guest.quiz.register.store` form
- Modify: `lang/en/students.php` and `lang/th/students.php` under `login`
- Test: `tests/Feature/Guest/GuestRegistrationConsentTest.php`

- **Consumes:** Task 1 and Task 2 interfaces
- **Produces:** the five spec columns, and `GuestPrivacyConsent::Rules(): array`, `Messages(): array`, `Attributes(Request $request): array`
- **Checkpoint:** none

**Actions:**

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Write the test with `RefreshDatabase`. Valid input has the existing required fields, `pdpa` `1`, and `privacy_notice_version` `p1-3-v1`, posted to `route('guest.quiz.register.store')`:
  - Passed with `marketing_consent` `1`: one row, `privacy_notice_version` `p1-3-v1`, `privacy_acknowledged_at` set, `marketing_consent` true, `marketing_consent_version` `marketing-v1`, `marketing_consent_recorded_at` set.
  - Passed without `marketing_consent`: `marketing_consent` false, and `marketing_consent_recorded_at` set.
  - Missing `pdpa`: no row, error on `pdpa`.
  - Stale `privacy_notice_version`: no row, error on `privacy_notice_version`.
  - Rejected, missing token, and unavailable: no row, `registration` error, old input keeps `first_name` and `marketing_consent`, and `assertGuest('guest_quiz')`.
  - Disabled reCAPTCHA: the row is created, and missing `pdpa` still fails.
  - `GET /students-login` and `GET /free-quiz/register` each render two unchecked checkboxes named `pdpa` and `marketing_consent`, and `data-recaptcha-action="guest_register"`.
  - After a failed submission with `marketing_consent`, the checkbox renders checked.
  - A row inserted without consent values reads `marketing_consent` as null.
  - The migration's `down()` throws `RuntimeException`, called through `require database_path(...)`.
- [ ] Run `php artisan test --filter=GuestRegistrationConsentTest`; expect failure because the columns, rules, and fields do not exist.
- [ ] Create the migration using the spec's Data table, placing columns after `expires_at`, with `down` throwing `RuntimeException`.
- [ ] Add the columns to `$fillable`. Cast `marketing_consent` to `boolean`, and `privacy_acknowledged_at` and `marketing_consent_recorded_at` to `datetime`.
- [ ] Implement `GuestPrivacyConsent` with these rules: `pdpa` `accepted`, `privacy_notice_version` `required|string|in:` plus the configured version, and `marketing_consent` `nullable|in:1`. `Attributes` returns the five columns, with `now()` for both timestamps.
- [ ] Merge the rules and messages into the `register` validator, add `recaptcha_token` as `nullable|string|max:4000`, and spread `GuestPrivacyConsent::Attributes($request)` into `GuestQuizzer::create`.
- [ ] Inject `RecaptchaVerifier $recaptcha` in `register`. After validation, when `IsEnabled()` is true and `Verify(...)` with `guest_register` is not `PASSED`, return `back()->withErrors(['registration' => __('students.login.recaptcha_rejected')])->withInput($request->except('recaptcha_token'))`.
- [ ] Create the consent partial with both labeled checkboxes, the notice link, and a hidden `privacy_notice_version`. `pdpa` has `required`. Use `@checked(old(...))`. Add `privacy_acknowledgement` and `marketing_choice` keys to both language files, using the approved wording for the applicant or parent.
- [ ] Add `data-recaptcha-action="guest_register"` to both forms, and include the consent partial and `partials.recaptcha` before each submit button.
- [ ] Run `php artisan test --filter=GuestRegistrationConsentTest`; expect exit code 0.

## Task 5: Rate Limits Stop Repeated Attempts

**Files:**

- Modify: `app/Providers/RouteServiceProvider.php` at `boot`
- Modify: `routes/web2.php` at `student.check`, `guest.quiz.register.store`, and `guest.quiz.login`
- Modify: `lang/en/students.php` and `lang/th/students.php` under `login`
- Test: `tests/Feature/Guest/LoginRateLimitTest.php`

- **Consumes:** the routes and error keys from Tasks 2 to 4
- **Produces:** limiters `student-login`, `guest-login`, `guest-register`
- **Checkpoint:** none

**Actions:**

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Write the test with `RefreshDatabase` and `RateLimiter::clear` in `setUp`, with reCAPTCHA disabled:
  - The 7th `student.check` post for one code redirects back with the `rate_limited` message on `error`, and a correct password no longer logs in.
  - A different code from the same IP is still processed after that.
  - The 7th `guest.quiz.login` post for one email has the message on `guest_error`.
  - The 6th registration post from one IP has the message on `registration` and creates no row.
  - The 31st student login from one IP across different codes is limited.
- [ ] Run `php artisan test --filter=LoginRateLimitTest`; expect failures because no limits exist.
- [ ] Add the limiters to `boot` using the spec's Rate Limits table. Account keys hash `strtolower` of the input with `sha256` and do not include the IP. Each limit uses `->response` to return `back()->withErrors([$error_key => __('students.login.rate_limited')])` with input except `password`, `guest_password`, and `recaptcha_token`.
- [ ] Add `rate_limited` to both language files, and attach `->middleware('throttle:<name>')` to the three routes.
- [ ] Run `php artisan test --filter=LoginRateLimitTest`; expect exit code 0.

## Task 6: Guest Report Shows and Withdraws Consent

**Files:**

- Modify: `app/Http/Controllers/GuestReportController.php` at `index`, plus a new `WithdrawMarketingConsent(Request $request)`
- Modify: `routes/web.php` after `guest.report.destroy`
- Modify: `resources/views/guest-quiz/report.blade.php`
- Modify: `lang/en/students.php` and `lang/th/students.php` under `guest_reports`
- Test: `tests/Feature/Guest/GuestMarketingConsentReportTest.php`

- **Consumes:** the `marketing_consent` column
- **Produces:** route `guest.report.marketing.withdraw`
- **Checkpoint:** none

**Actions:**

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Write the test with a central actor (an HQ user with the web `Admin` role, per `BranchAccess::isCentral`) holding `student-list` and `student-edit`:
  - The AJAX report returns the consented, not consented, and not recorded labels for `true`, `false`, and `null`.
  - The withdraw button appears only for `true`, and only with `student-edit`.
  - Withdrawal sets `marketing_consent` false, updates `marketing_consent_recorded_at`, and logs `MARKETING_CONSENT_WITHDRAWN` with `actor_id` and `guest_id` only.
  - A repeated withdrawal leaves `marketing_consent_recorded_at` unchanged and succeeds.
  - An actor without `student-edit` and a non-central actor each get 403, and the row is unchanged.
  - An unknown `id` fails validation.
- [ ] Run `php artisan test --filter=GuestMarketingConsentReportTest`; expect failures because the column and route do not exist.
- [ ] Add a `marketing_status` column using `students.guest_reports` keys `marketing_consented`, `marketing_not_consented`, and `marketing_not_recorded`. Extend the `action` column with a `btn-withdraw-marketing` button, and add `marketing_status` as non-orderable and non-searchable.
- [ ] Add `WithdrawMarketingConsent`: authorize with `student-edit`, validate `id` as `required|integer|exists:guest_quizzers,id`, update only when consent is true, log, and return `response()->json(['success' => true])`.
- [ ] Add the route, table header, DataTables column, confirm-then-post handler modeled on `.btn-delete`, and `guestReportUi` strings.
- [ ] Run `php artisan test --filter=GuestMarketingConsentReportTest`; expect exit code 0.

## Task 7: Whole-Change Verification

**Files:** none.

- **Consumes:** Tasks 1 to 6
- **Produces:** verification evidence
- **Checkpoint:** none

**Actions:**

**For nonbehavioral artifacts:**

- [ ] Run `php artisan test --log-junit <scratchpad>/backend-junit-0016-final.xml`, and compare with Task 0; expect no new failing `classname::name`.
- [ ] Run `php artisan route:list --name=guest.report.marketing.withdraw`; expect one route.
- [ ] Inspect the diff for `recaptcha_token`, `secret_key`, and logging; expect no secret, token, or personal data in logs or markup.

## Task 8: Production Rollout

**Files:** none.

- **Consumes:** the committed change and the user's reCAPTCHA key
- **Produces:** verified production forms
- **Checkpoint:** Stop before each step until the user confirms. Each step below is a separate approval.

**Actions:**

**For nonbehavioral artifacts:**

- [ ] Confirm that the user has created or updated a reCAPTCHA v3 key for `app.eimaths-th.com`.
- [ ] Confirm legal approval of the notice and wording. Update `PRIVACY_NOTICE_VERSION` and the public website together if the version changes.
- [ ] Confirm which branch reaches production, and confirm `$request->ip()` on production returns the visitor's address. Stop and report if it returns a proxy address.
- [ ] Check the production response headers for a content security policy that blocks `www.google.com` or `www.gstatic.com`.
- [ ] With `RECAPTCHA_ENABLED=false`, run `php artisan down`, update the code, run `php artisan migrate`, then run `php artisan up`.
- [ ] Set `RECAPTCHA_ENABLED=true` with the keys, clear the configuration cache, and test all four forms in a browser. The browser test covers the script behavior that automated tests do not exercise.
- [ ] Review `RECAPTCHA_CHECK` results with the user. Set `RECAPTCHA_ENABLED=false` to roll back.
