# Affiliate Analytics and Media Library Verification Report

Date: 2026-08-11

## Scope

This verification covers the Affiliate analytics, media-library, payout, withdrawal, and content-administration changes, including the visual alignment update requested after desktop screenshots showed that the Affiliate pages did not use the application's AdminLTE content shell.

It also covers the dashboard referral actions: retaining the raw referral code, exposing a usable full course referral link, and copying both values through the shared clipboard helper.

The verification also covers guest email-verification recovery for Affiliate registrations that remain in `PENDING_EMAIL`.

## Verified results

- Affiliate pages now use the shared AdminLTE content structure: `content-header`, `content`, `container-fluid`, Bootstrap cards, Bootstrap tables, grouped forms, and a shared pill navigation.
- Affiliate-specific evidence labels, translated copy, visible focus states, responsive single-column behavior, horizontal table overflow, and reduced-motion rules remain scoped under `.affiliate-portal`.
- The content-management page now uses the same card/table/form structure while preserving archive, edit, status-toggle, campaign-code, destination, and publication-window behavior.
- The content-management page no longer renders the top “Add media” shortcut; the create form and protected create endpoint remain available below the table.
- Content-management rows are server-paginated in ten-row pages through the `content_page` query parameter, and row numbering remains continuous across pages.
- Content-management edit actions open an accessible Bootstrap modal instead of an inline `<details>` toggle; status and archive actions remain standard protected forms.
- The dashboard, analytics, media, payout-account, and withdrawal pages render through the shared shell regression tests.
- The dashboard exposes separate raw-code and full-course-link actions and announces copy success or browser-unavailable feedback through an accessible status region.
- The full referral link targets the real course listing route with the affiliate code as `?ref=...`, so it can be shared without manually finding a course URL.
- The Affiliate waiting page renders a resend-verification form only while the pending Affiliate email is unverified and the profile remains in `PENDING_EMAIL`.
- Registration stores a session-bound pending user reference, allowing the guest waiting page to resend verification without using the login-protected global `verification.resend` route.
- The same waiting page provides an email fallback when no registration session is available; the `POST /affiliate/resend-verification` endpoint accepts only that optional email address, never a user ID, and sends only for an unverified `PENDING_EMAIL` profile.
- The guest resend endpoint uses the existing six-requests-per-minute throttle and returns a generic confirmation to avoid account enumeration.
- Signed Affiliate verification links can now be opened by a guest; the signed hash is checked, the profile moves from `PENDING_EMAIL` to `PENDING_APPROVAL`, and the guest is redirected to the waiting page without being logged in.
- Non-Affiliate users still receive the existing login redirect when opening a verification link as a guest.
- A guest session whose Affiliate profile becomes `ACTIVE` is redirected from the waiting page to Affiliate login with an approval notice; a verified `PENDING_APPROVAL` session keeps a persistent waiting notice and no resend form.
- Media share actions expose a visible success or error status beneath each media card, and light actions in blue card headers retain readable dark text.
- The unreliable media caption-copy and tracking-link-copy buttons are no longer rendered; the full tracking URL remains visible in a read-only field for manual browser copying, while download and share remain available.
- Media copy uses a user-gesture-safe document fallback before the native Clipboard API, then falls back to the injected copy adapter when neither browser path is available.
- The focused Affiliate PHPUnit suite passed: 202 tests, 1,013 assertions, with one documented skip.
- The JavaScript suite passed: 19 tests, 19 passes, 0 failures.
- The media-library feature test passed: 2 tests, 14 assertions.
- The content-management feature test passed: 6 tests, 32 assertions.
- The registration feature test passed: 17 tests, 66 assertions.
- The Affiliate translation parity test passed: 2 tests, 223 assertions.
- The shared clipboard helper is covered for native copy, document fallback, resolved-native fallback ordering, and injected fallback behavior.
- Each media card now renders the full tracking URL in a read-only field so users have a visible manual-copy fallback when browser clipboard access is blocked.
- Blade cache compilation passed with `php artisan view:cache`.
- `php artisan route:list --path=affiliate` completed and showed 42 Affiliate routes.
- `php artisan list affiliate` completed and showed five Affiliate commands.
- PHP syntax checks passed for the changed PHP controllers, requests, domain classes, and Affiliate tests.
- `git diff --check` completed without whitespace errors.

## Verification commands

Environment:

- PHP 8.2.12
- PHPUnit 10.5.64
- Laravel 10.50.2
- Node.js v24.16.0
- Vite 4.3.9

Commands and observed outcomes:

```text
C:\xampp\php\php.exe vendor\bin\phpunit --do-not-cache-result tests\Unit\Affiliate tests\Feature\Affiliate
OK, but some tests were skipped!
Tests: 193, Assertions: 950, Skipped: 1.

node --test tests\JavaScript\affiliate-media-library.test.mjs tests\JavaScript\affiliate-analytics-page.test.mjs tests\JavaScript\affiliate-analytics-poller.test.mjs
tests 19
pass 19
fail 0

npm run build
✓ built in 4.46s

php artisan test tests/Feature/Affiliate/AffiliateMediaLibraryTest.php tests/Feature/Affiliate/AffiliateWithdrawalUiTest.php
Tests: 8, Assertions: 62, Failures: 0

php artisan test tests/Feature/Affiliate/AffiliateContentManagementTest.php tests/Feature/Affiliate/AffiliateMediaLibraryTest.php tests/Feature/Affiliate/AffiliateWithdrawalUiTest.php
Tests: 14, Assertions: 95, Failures: 0

php artisan test tests/Feature/Affiliate/AffiliateRegistrationTest.php
Tests: 17, Assertions: 66, Failures: 0

php artisan test tests/Unit/Affiliate/AffiliateTranslationStructureTest.php
Tests: 2, Assertions: 223, Failures: 0

php artisan view:cache
Blade templates cached successfully.

php artisan route:list --path=affiliate
Showing [42] routes
```

The asset build emitted existing Sass deprecation and CSS-nesting warnings from the application's current AdminLTE/Sass dependency chain. The build completed successfully.

## Concurrency and environment limits

The concurrency test was intentionally skipped on the local SQLite test connection:

```text
SQLite cannot verify deployed row-lock semantics.
```

The MySQL concurrency path was not executed because an isolated database and deployment-like row-lock environment were not supplied. Staging verification is still required for concurrent first-click behavior and transaction rollback behavior.

Browser visual verification was not completed because the available browser integration reported no browser session. The rendered page structure is covered by the Affiliate feature tests and the asset build, but desktop/mobile screenshots have not been collected after this change.

The repository does not contain `tests/Feature/Images/ExtendedImageUploadWebpTest.php`; that specific regression command is therefore unavailable. Affiliate content upload and validation coverage remains included in the focused Affiliate suite.

## Full repository result

The complete PHPUnit run reached 219 tests and reported two errors, one failure, and one skip. The failures were outside the Affiliate test scope:

- `ParentAuthTest::parent_can_login_with_correct_credentials` and `ParentAuthTest::authenticated_parent_can_logout` failed because the SQLite `parents.student_id` column is non-null while the test factory insert omits it.
- `ExampleTest::test_the_application_returns_a_successful_response` received HTTP 302 instead of 200.

These causes are unrelated to the changed Affiliate view, CSS, and test files by their stack traces and affected areas. Their baseline status was not independently established during this verification.

## Deployment notes

No production migration, feature-flag enablement, commit, or push was performed. Existing generated build/cache artifacts remain environment-generated and were not treated as source changes.
