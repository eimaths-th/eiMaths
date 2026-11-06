# Student Login Bot Protection and Privacy Consent Design

## Summary

Protect the public e-learning entry forms from bots, and record privacy acknowledgement and optional marketing consent for Free Trial registrations.

The work applies to `eimaths_backend`, served at `app.eimaths-th.com`. It follows the approach used by the public website's free-trial form in `eimaths_frontend`.

## Brief

- **Intended outcome:** Bots cannot freely register or guess passwords, and each new Free Trial registration has privacy evidence.
- **Why it matters:** The user wants these forms to match the public website's privacy and reCAPTCHA behavior, and wants to block bots.
- **Intended users:** Students, parents, and trial applicants who use the forms; central back-office staff who read the Guest Report.
- **Constraints:** Real students must keep access to learning. reCAPTCHA must be switchable from the environment without a code change. Keys are created by the user.
- **Success criteria:** Every listed form enforces the behavior below, and tests cover each outcome. After rollout, all four forms work on production with reCAPTCHA enabled.

## Goals

- Add reCAPTCHA v3 to the four forms below.
- Add rate limits to the three public entry points that accept credentials or registrations.
- Require privacy acknowledgement and offer optional marketing consent on both Free Trial registration forms.
- Show marketing consent in the Guest Report and let authorized staff record a withdrawal.

## Non-Goals

- Changing the phone-number-as-password behavior of Free Trial accounts.
- Requiring existing guests to acknowledge the notice retroactively.
- Building promotional sending or list export features.
- Adding a self-service consent withdrawal page.
- Changing the affiliate forms or their `NoCaptcha` markup. The `composer.json` file has no captcha package, so that code is not reused.

## Anti-Goals

- Real students being widely unable to log in.
- Storing personal data beyond the fields listed in this specification.
- Treating an existing or unknown record as having marketing consent.

## Decisions

### User Decisions

- **Scope:** Option A. Registration forms get checkboxes and reCAPTCHA; login forms get reCAPTCHA, rate limits, and a privacy link with no checkbox.
- **Login failure policy:** Option A. If the server cannot reach Google, login continues under the rate limit and is logged. A low score blocks login and shows retry and LINE contact guidance.
- **Registration failure policy:** Reject the submission on every reCAPTCHA failure, as the public website does.
- **Marketing:** Option B. Registration includes a separate optional marketing checkbox, because the team will contact applicants for sales.

### Agent Decisions

- A missing token counts as rejected, including on login. A client can omit a token at will, so only a server-side failure reaching Google may pass. Networks that block Google cannot log in while reCAPTCHA is enabled.
- reCAPTCHA is checked before the password.
- Rate-limit values follow the table below, and the per-account login limit is keyed by the hashed account identifier alone.
- Withdrawal is recorded by staff through the Guest Report, matching the published instruction to phone an administrator.
- Consent state lives on `guest_quizzers` with a structured log event, not a new event table.
- `config/privacy.php` in this repository holds the notice version, consent version, and notice URL.

### Approaches Considered

- **Scope A, chosen:** Covers every entry point without adding a checkbox to login. It costs more work than registration-only coverage.
- **Scope B, rejected:** Registration only is fastest but leaves password guessing open.
- **Scope C, rejected:** Checkboxes on every form burden each login without stopping bots.
- **Login policy A, chosen:** Blocks bots while a Google outage does not stop learning. During an outage only the rate limit protects login.
- **Login policy B, rejected:** Rejecting on outage would lock out every student when Google or a key fails.
- **Login policy C, rejected:** Observing scores only gives the weakest bot protection.

## Forms

| Form | Route | Privacy | reCAPTCHA action | Rejected | Google unavailable |
| --- | --- | --- | --- | --- | --- |
| Student login | `student.check` | Link only | `student_login` | Block | Continue and log |
| Free Trial login | `guest.quiz.login` | Link only | `guest_login` | Block | Continue and log |
| Free Trial registration on `students-login` | `guest.quiz.register.store` | Both checkboxes | `guest_register` | Reject | Reject |
| Free Trial registration on `/free-quiz/register` | `guest.quiz.register.store` | Both checkboxes | `guest_register` | Reject | Reject |

## Responsibilities

- **`App\Support\Recaptcha\RecaptchaVerifier`:** Checks a token with Google and returns a `RecaptchaResult`. It has no knowledge of forms.
- **`App\Support\Recaptcha\RecaptchaResult`:** An enum with `PASSED`, `REJECTED`, and `UNAVAILABLE`.
- **`GuestQuizController` and `ContactController`:** Apply each form's failure policy.
- **`GuestPrivacyConsent`:** Holds the registration validation rules and maps a validated request to the consent columns.
- **Blade partials:** A shared reCAPTCHA partial, a privacy link partial, and a consent checkbox partial, so both registration forms stay identical.
- **`GuestReportController`:** Shows consent status and records withdrawal.

## Interfaces

### Configuration

Add to `config/services.php` under `recaptcha`:

| Key | Environment variable | Default |
| --- | --- | --- |
| `enabled` | `RECAPTCHA_ENABLED` | `false` |
| `site_key` | `RECAPTCHA_SITE_KEY` | none |
| `secret_key` | `RECAPTCHA_SECRET_KEY` | none |
| `min_score` | `RECAPTCHA_MIN_SCORE` | `0.5` |
| `allowed_hostnames` | `RECAPTCHA_ALLOWED_HOSTNAMES` | `app.eimaths-th.com` |

Add placeholders with empty key values to `.env.example`.

Create `config/privacy.php`:

| Key | Environment variable | Default |
| --- | --- | --- |
| `notice_version` | `PRIVACY_NOTICE_VERSION` | `p1-3-v1` |
| `consent_version` | `PRIVACY_CONSENT_VERSION` | `marketing-v1` |
| `notice_url` | `PRIVACY_NOTICE_URL` | `https://eimaths-th.com/privacy-policy` |

The public website remains the source of the published notice. A notice change must update both repositories' version values.

### Verifier

```php
public function IsEnabled(): bool;
public function Verify(mixed $token, string $action, ?string $ip = null): RecaptchaResult;
```

- `IsEnabled` returns true only when `enabled` is true and both keys are present. Enabled with a missing key logs an error and returns false.
- `Verify` calls `https://www.google.com/recaptcha/api/siteverify` with a 3-second timeout.

| Condition | Result |
| --- | --- |
| Token is null, empty, not a string, or over 4,000 characters | `REJECTED`, without calling Google |
| Any HTTP client or transfer failure, timeout, or non-2xx response | `UNAVAILABLE` |
| `success` is false with `missing-input-secret` or `invalid-input-secret` | `UNAVAILABLE` |
| Any other `success` false | `REJECTED` |
| Hostname, action, or score fails | `REJECTED` |
| All checks pass | `PASSED` |

### Requests

- All four forms send `recaptcha_token`. The verifier validates it, so a malformed token is a rejection on the form that sent it and is never flashed to the session.
- Registration adds `pdpa` (`accepted`), `privacy_notice_version` (must equal the configured version), and `marketing_consent` (nullable, and true only when the value is `1`).
- A stale notice version returns to the form with a refresh message.

### Routes

Add `POST /guest-report/marketing-consent/withdraw`, named `guest.report.marketing.withdraw`, in `routes/web.php` with `id` in the body. It requires central access and the existing `student-edit` permission through `authorizeCentralAccess`. Repeating the request for an already withdrawn record succeeds without another change.

### Rate Limits

Define these in `RouteServiceProvider` and attach them to the routes.

| Limiter | Limit |
| --- | --- |
| `student-login` | 30 per minute by IP, and 6 per minute by hashed `code` |
| `guest-login` | 30 per minute by IP, and 6 per minute by hashed lowercase `guest_email` |
| `guest-register` | 5 per minute by IP |

A limited request returns to the form with a localized message and keeps non-sensitive input. Non-browser JSON responses are not required for these routes.

### Data

Add nullable columns to `guest_quizzers`:

| Column | Type |
| --- | --- |
| `privacy_notice_version` | `string(50)` |
| `privacy_acknowledged_at` | `timestamp` |
| `marketing_consent` | `boolean` |
| `marketing_consent_version` | `string(50)` |
| `marketing_consent_recorded_at` | `timestamp` |

Add the columns to `$fillable`, and cast the boolean and timestamps. The migration is additive. Dropping the columns would destroy consent evidence, so `down` throws a `RuntimeException`, as the existing consent migrations do.

Existing rows keep null values. Only `true` means consent; both `false` and `null` mean no consent.

A new registration writes all five columns. `marketing_consent` is `true` or `false`, never null. Withdrawal sets `marketing_consent` to `false` and updates `marketing_consent_recorded_at`.

### Guest Report

Add a marketing column with these text labels:

- `true`: consented.
- `false`: not consented.
- `null`: not recorded.

Show a withdraw button only for `true` and only to staff with `student-edit`. Labels are text, so meaning does not depend on color.

## User Interface

Registration forms:

```text
[ existing fields ]
[ ] As the applicant or parent, I have read the privacy notice. *
[ ] I want course news and promotions by LINE, phone, and email. (optional)
This page uses Google reCAPTCHA.
[ Register for trial ]
```

Login forms keep their layout and add a privacy notice link and the reCAPTCHA notice below the submit button.

Neither checkbox is preselected on first render. After a failed submission, each checkbox shows the applicant's own previous choice through `old()`. The notice link opens in a new tab. All new text uses keys in `lang/th/students.php` and `lang/en/students.php`. The checkboxes have associated labels and a visible focus state. Errors use the existing error areas.

The default reCAPTCHA badge stays visible, and the notice text is present on every protected form.

## Data Flow

1. The page loads the reCAPTCHA script only when `IsEnabled` is true.
2. On submit, the script requests a token for the form's action, fills `recaptcha_token`, and submits once. Double submission is ignored while a token is pending.
3. If the script is unavailable, throws, or gives no token within 10 seconds, the form submits without a token and the server rejects it with retry guidance. Restoring a page from the browser cache clears the token and allows a new submit.
4. Rate limiting runs first, then request validation, then reCAPTCHA, then the password check or registration.
5. A registration stores the consent columns in the same `GuestQuizzer::create` call as the account.

When `IsEnabled` is false, no script loads and the server skips the reCAPTCHA check. Rate limits and privacy requirements stay active.

## Failure Handling

- Each reCAPTCHA failure message explains the action to take and shows no technical detail.
- Login failures use the existing `error` and `guest_error` keys, so the correct panel reopens.
- Registration failures use a new `registration` error key, so the registration panel reopens and input is kept.
- `password`, `guest_password`, and `recaptcha_token` are never returned to the form.

## Security and Privacy

- Secret keys stay in the environment and are never rendered.
- The check runs on the server for every protected request.
- Logs use the event `RECAPTCHA_CHECK` with `recaptcha_action`, `recaptcha_result`, and `recaptcha_score`. Passed and rejected checks log at info, so score distribution can be measured, and unavailable checks log at warning.
- Logs never contain tokens, passwords, codes, email addresses, phone numbers, or IP addresses.
- Withdrawal logs the event `MARKETING_CONSENT_WITHDRAWN` with the actor ID and guest ID.
- Only `marketing_consent = true` may be used for promotions.

## Testing

Use feature tests under `tests/Feature/Guest` and `tests/Feature/Contact`, and fake Google with `Http::fake`.

- **Verifier:** Each row of the verifier table, plus `IsEnabled` with missing keys.
- **Login forms:** Passed, rejected, missing token, unavailable, and disabled reCAPTCHA. A rejected check must not start a session, and an unavailable check must still require a correct password.
- **Registration forms:** Passed, rejected, unavailable, disabled reCAPTCHA, missing `pdpa`, stale notice version, marketing consent present, and marketing consent absent, checking the stored columns. A failed submission restores the applicant's checkbox choices.
- **Rate limits:** The attempt after each limit is refused, and an attempt from the same IP for another account stays allowed under the per-account limit.
- **Guest Report:** Consent labels, withdrawal, repeated withdrawal, and refusal without central access or `student-edit`.
- **Migration:** Existing rows keep null values.
- **Rendering:** Both registration forms render both checkboxes unchecked, and both login forms render the privacy link and no checkbox.

The existing 65 backend test failures are outside this work. Success means no new failures.

## Rollout

1. The user creates or updates a reCAPTCHA v3 key for `app.eimaths-th.com`.
2. A legal reviewer approves the notice and wording, including children's data, parental acknowledgement, the 10-day trial account, quiz results, and Google reCAPTCHA. Marketing consent must not go live before this approval.
3. Confirm that `$request->ip()` on production returns each visitor's own address. If it returns a proxy address, configure `App\Http\Middleware\TrustProxies` for that proxy before deploying, because otherwise all visitors would share one rate-limit budget.
4. Deploy with `RECAPTCHA_ENABLED=false` during a short maintenance window: run `php artisan down`, update the code, run the migration, then run `php artisan up`. Registration writes the new columns, so the code must not serve traffic before the migration has run.
5. Enable reCAPTCHA and test all four forms on production.
6. Watch `RECAPTCHA_CHECK` results after enabling. Set `RECAPTCHA_ENABLED=false` to roll back.

Each production step needs the user's confirmation.

## Unknowns

- The final privacy notice text and version, which depend on the legal review.
- The share of real students with scores below `0.5`, which is unknown until production use.
- Whether any school or home network blocks Google. Such a network cannot log in while reCAPTCHA is enabled.
- Whether a server-level content security policy on production restricts Google scripts. The application sets none for these forms, so this check belongs to rollout.
- Whether production runs behind a proxy. `TrustProxies::$proxies` is unset, and rollout step 3 settles this.
