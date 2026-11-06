# Student Login Bot Protection and Privacy Consent Execution Record

- **Plan:** `docs/plans/0016-student-login-bot-protection-and-privacy-consent.md`
- **Source:** `docs/specs/0004-student-login-bot-protection-and-privacy-consent-design.md`
- **Plan approval:** User reply "ok" to the combined request covering the plan, direct work on `demo`, and native execution.
- **Method:** Native execution with one fresh independent final review.
- **Branch:** `demo`, started at `2628c784`, no worktree.

## Progress

| Task | State | Evidence |
| --- | --- | --- |
| 0. Baseline | Complete | Reused the full run of the same code as `2628c784`: 1,171 tests, 65 failures. |
| 1. Verifier | Implemented | RED: missing classes. GREEN: 18 passed. |
| 2. Student login | Implemented | RED: 4 failed. GREEN: 8 passed. |
| 3. Free Trial login | Implemented | RED: 3 failed. GREEN: 6 passed. |
| 4. Registration consent | Implemented | RED: 12 failed. GREEN: 13 passed. |
| 5. Rate limits | Implemented | RED: 4 failed. GREEN: 5 passed. |
| 6. Guest Report | Implemented | RED: 8 failed. GREEN: 9 passed. |
| 7. Whole-change verification | Complete | Full suite: 1,242 tests with the same 65 baseline failures and no new failures. JavaScript: 11 passed. Focused PHP: 71 passed. Route check: one route. |
| 8. Production rollout | Pending | Every step waits for its user checkpoint. |

## Review Findings

Final review round 1 of 1, independent reviewer.

| Severity | Finding | Disposition | Cost if ruling is wrong |
| --- | --- | --- | --- |
| High | Non-connect Google failures returned HTTP 500. | Fixed: catches `HttpClientException` and Guzzle `TransferException`. Tested. | Not applicable |
| High | The form hung when `ready` never fired or `execute` threw, including with an invalid site key. | Fixed: try/catch, 10-second timeout, and `Promise.resolve`. Tested in `tests/JavaScript/recaptcha-form.test.mjs`. | Not applicable |
| Medium | Back-navigation left the form unable to submit. | Fixed: `pageshow` resets state and clears the token. Tested. | Not applicable |
| Medium | The e-book API is a student password check with no reCAPTCHA, and its account limit includes the IP. | User decision: option B, no change in this work. | Bots can still guess student passwords through that route. |
| Low | A rejected student lost the code field. | Fixed: `old('code')` and `withInput`. Tested. | Not applicable |
| Low | `guest_password` and `recaptcha_token` were flashed to the session. | Fixed: limited `withInput` calls and extended `$dontFlash`. Tested. | Not applicable |
| Low | An oversized token reopened the wrong panel. | Fixed: the verifier rejects malformed tokens, so the form's own error key is used. Tested. | Not applicable |
| Advisory | `ebook-login` and `student-login` limiters share one shape. | Ruling: kept separate, because their key policies differ. | Minor duplication. |

## Declined to Judge

Dispositions: each is a ruling that the behavior is set by the specification, belongs to rollout, or is pre-existing.

- Per-IP 30/min shared by a school network: specified; stays open until rollout step 3.
- Six bad attempts locking a known code for a minute: specified by the account-only key.
- `TrustProxies` unset: rollout step 3.
- An early click before `api.js` loads is rejected with guidance: specified.
- Validation before reCAPTCHA reveals whether an email exists: the specification fixes the order.
- A stale notice version keeps `pdpa` ticked: specified.
- A 302 with no `Retry-After` on limits: specified.
- `IsEnabled` logging up to three times per page on a missing key: specified behavior, minor noise.
- A blank `RECAPTCHA_MIN_SCORE=` becomes 0.0 so every score passes: not specified. The rollout must set the value. Cost if wrong: login protection silently weakens.
- A non-JSON 200 from Google counts as rejected: no specification row. Cost if wrong: a rare block of login.
- A non-array `error-codes` raising a `TypeError`: Google's contract is an array.
- Accent-insensitive MySQL collation evading the hashed account key: untested, needs the real collation.
- `code[]=` array input sharing one key: no bypass.
- Multi-tab no-Referer redirect: framework `back()` behavior.
- A 3-second synchronous wait during an outage: specified timeout.
- Privacy link clipping on short viewports: browser check at rollout.
- A CDN failure of lucide or gsap: pre-existing.
- Guest deletion removes consent evidence: pre-existing `destroy`, not in this plan.
- Phone as password and retained phone input: specification non-goal.
- Production content security policy: rollout check.
- Legal wording: awaits a human reviewer.

## Not Verified

- A real reCAPTCHA key, real MySQL, and browsers other than the reviewer's headless Chrome.
- Whether the migration's chained `AFTER` clauses run on real MySQL. Run it on a disposable MySQL database before production.

## Preflight Rulings

- Task 0 reused the 2026-11-06 full run instead of a fresh 18-minute run, because that run tested the same code as `2628c784`. Cost if wrong: a pre-existing failure could be misread as new, which a rerun would settle.

## Execution Rulings

- `Http::failedConnection()` does not exist on the installed Laravel 10 version, so tests simulate an outage by throwing `ConnectionException` from `Http::fake`. The production code path is unchanged.
- `RateLimiter` tests use the array cache already set in `phpunit.xml`, so no extra cache setup is needed.
- `Verify` accepts `mixed` and validates the token itself, replacing the `recaptcha_token` request rules from Tasks 2 to 4, so a malformed token is rejected on the form that sent it. The specification was updated to match.
- `tests/JavaScript/recaptcha-form.test.mjs` was added after review, following the project's pattern of extracting Blade scripts for `node --test`.

## Correction Re-Review

The final review round limit was reached. These dispositions adjudicate the re-review of the corrections.

| Finding | Disposition | Cost if ruling is wrong |
| --- | --- | --- |
| `code[]` caused a 500 on the next `/students-login` render, introduced by `old('code')`. | Fixed with a string guard. Tested. | Not applicable |
| `guest_email[]` caused the same 500. It was pre-existing, on the same page. | Fixed with the same guard. Tested. | Not applicable |
| A wrong password loses the code field. | Deferred optional work: pre-existing and outside the plan. | Minor inconvenience. |
| The inner try/catch and `Promise.resolve` lacked discriminating tests. | Fixed: added tests for an asynchronous `ready` and a plain token value. | Not applicable |
| Bare `withInput()` passed the suite. | Fixed: tests now assert the token and password are not flashed. | Not applicable |
| `guest_password` in `$dontFlash` was inert. | Removed, because guest login uses `Validator::make`. | Not applicable |
| The first `pageshow` could reset the pending guard. | Fixed: only `persisted` restores reset state. Tested. | Not applicable |

Declined to judge: real Google, browser, and Guzzle failures use fakes and are covered by rollout checks. `public/Modules` views were not searched, because the reviewer's search timed out, and they stay unverified.
