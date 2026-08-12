# Employee Affiliate Enrollment Verification

**Verification date:** 2026-08-11

**Report generated:** 2026-08-11T22:15:30+07:00 (Asia/Bangkok)

**Scope:** Local implementation verification for the approved employee Affiliate enrollment design.

**Result:** The employee enrollment implementation passes the focused local and Affiliate regression checks. Rollout readiness is not verified because the local preflight is blocked, staging MySQL concurrency evidence is unavailable, the approved production duplicate query was not run, and no browser connection was available for an interactive check.

The feature remains disabled by default in `config/affiliate.php`, `.env.example`, and `phpunit.xml`. The previously requested existing work was committed as `0490894a`; the employee enrollment work and this report remain uncommitted.

## Test evidence

### Executed commands

```powershell
C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --do-not-cache-result tests\Feature\Affiliate\AffiliateEmployeeEligibilityTest.php tests\Feature\Affiliate\AffiliateProfilePersistenceTest.php tests\Feature\Affiliate\AffiliateProfileOwnershipMigrationTest.php tests\Unit\Affiliate\AffiliateReferralCodeGeneratorTest.php tests\Feature\Affiliate\AffiliateEmployeeEnrollmentServiceTest.php tests\Feature\Affiliate\AffiliateEmployeeAccessHttpTest.php tests\Feature\Affiliate\AffiliatePortalAccessTest.php tests\Feature\Affiliate\AffiliateEmployeeRedirectTest.php tests\Feature\Affiliate\AffiliateEmployeeAdministrationTest.php tests\Unit\Affiliate\AffiliateTranslationStructureTest.php
```

```powershell
C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --do-not-cache-result tests\Feature\Affiliate\AffiliateRegistrationTest.php tests\Feature\Affiliate\AffiliateAccountLifecycleTest.php tests\Feature\Affiliate\AffiliateFinanceNavigationTest.php tests\Feature\Affiliate\AffiliatePayoutAccountApiTest.php tests\Feature\Affiliate\AffiliateWithdrawalUiTest.php tests\Feature\Affiliate\AffiliateAnalyticsApiTest.php tests\Feature\Affiliate\AffiliateMediaLibraryTest.php
```

```powershell
C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --do-not-cache-result tests\Feature\Affiliate tests\Unit\Affiliate
C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --do-not-cache-result tests\Feature\Affiliate\AffiliateEmployeeEnrollmentConcurrencyTest.php
C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --do-not-cache-result
```

| Command | Exit code | Result |
| --- | ---: | --- |
| Focused employee enrollment command from the approved plan | 0 | 64 passed, 449 assertions |
| Preserved external and financial regression command from the approved plan | 0 | 46 passed, 235 assertions |
| `artisan test --do-not-cache-result tests/Feature/Affiliate tests/Unit/Affiliate` | 0 | 268 passed, 2 skipped, 1,270 assertions |
| `artisan test --do-not-cache-result tests/Feature/Affiliate/AffiliateEmployeeEnrollmentConcurrencyTest.php` | 0 | 1 explicit SQLite skip, 0 assertions; no worker was started |
| `artisan test --do-not-cache-result` | 1 | 291 passed, 2 skipped, 3 failures, 1,384 assertions |

The full repository failures are outside this feature scope:

- `Tests\Feature\ExampleTest` expects an unauthenticated `/` response of 200, while the existing committed root route redirects.
- Two `Tests\Feature\ParentAuthTest` cases fail because the existing SQLite schema requires `parents.student_id` while the test fixture omits it.

These files and the parent schema are not part of the employee enrollment change.

## Static and structural checks

- `php -l` passed for every changed or newly added PHP file.
- `git diff --check` passed.
- `artisan route:list --path=affiliate --except-vendor -v` passed and listed 45 routes.
- `GET /affiliate/employee-access` without authentication returned `302` to `/login` in a local HTTP smoke check.
- Both employee routes are present with authentication, locale, correlation, employee eligibility, and feature middleware. The POST route also has the named employee enrollment rate limiter.
- Affiliate analytics, media, payout-account, withdrawal, dashboard, and `my_affiliate` member routes use `affiliate.portal-access`. Administrator routes do not use this middleware.
- Employee enrollment production files contain no `Affiliate-user` assignment, verification notification, or `email_verified_at` mutation.
- The feature configuration and test configuration are fail-closed by default.

## Concurrency and data safety

The concurrency test uses `DatabaseMigrations`, requires MySQL, requires `AFFILIATE_RUN_CONCURRENCY_TESTS=true`, requires an exact `AFFILIATE_CONCURRENCY_DATABASE` match, refuses production-like database names, and starts no workers before those gates pass.

The local run used SQLite and therefore skipped before worker startup. No authorized isolated MySQL staging database was supplied for this verification session, so the two-worker result remains unknown.

The unique `affiliate_profiles.user_id` migration includes a duplicate-owner preflight. The local preflight reported the ownership check as `PASS`. The approved operational production duplicate query was not run because no approved production access channel was available. The migration must not be applied to production until that query returns no rows and the migration has a separate reviewed rollout approval.

## Preflight and browser evidence

`artisan affiliate:preflight --json` exited 1. The relevant sanitized results were:

- `maintenance_mode`: `PASS`
- `affiliate_settings`: `PASS`
- `destination_hosts`: `PASS`
- `affiliate_profile_ownership`: `PASS`
- `visitor_hash_key`: `BLOCKED` because click tracking lacks an independent visitor hash key of at least 32 characters
- `registration_mail_driver`: `WARN` because the local mail driver is `log`
- `queue_driver`: `WARN` because the local queue driver is `sync`
- mail delivery, scheduler host, access review, and finance policy: `UNKNOWN` pending external evidence

The browser runtime reported no available browser connections (`[]`), so an interactive browser check could not be completed. The local HTTP smoke check above is the available non-interactive evidence.

## Rollout handoff

Do not enable `AFFILIATE_FEATURE_EMPLOYEE_ENROLLMENT` or run the ownership migration against production from this result alone. Before rollout, obtain isolated MySQL staging evidence for the concurrency test, run the approved duplicate-owner query through the operational channel, resolve the local preflight blocker, and complete a browser check with an authenticated eligible employee account. The design specification intentionally remains marked `Not started` until those required verification gates are complete.
