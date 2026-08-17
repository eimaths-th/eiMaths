# Employee Affiliate Enrollment Implementation Plan

> **REQUIRED EXECUTION SKILL:** Use `executing-plans` for task-by-task execution.

**Plan status:** Awaiting explicit user approval

- **Goal:** Let an eligible employee request Affiliate access with the existing staff account, skip employee email verification, keep administrator approval mandatory, and preserve the external Affiliate registration contract.
- **Architecture:** Treat `AffiliateProfile` as the Affiliate capability and keep `Affiliate-user` as an external Affiliate-only account classification. Centralize employee-role eligibility and portal authorization in focused domain services, add one idempotent enrollment transaction, and enforce one Affiliate profile per user in the database.
- **Tech stack:** PHP 8.2.12, Laravel 10.50.2, Eloquent, Spatie Permission 5.x, Blade, Bootstrap/AdminLTE, MySQL in the local application, SQLite in PHPUnit, and PHPUnit 10. The production database engine and production data shape remain externally unverified.
- **Approved specification:** `docs/specs/2026-08-11-employee-affiliate-enrollment-design.md`

## Verified Baseline

The following facts were verified in the repository on 2026-08-11:

- Public Affiliate registration in `AffiliateAccountService::Register` creates a new `User`, sets `affiliate_request = 1`, assigns `Affiliate-user` when that role exists, and creates a `PENDING_EMAIL` `AffiliateProfile`.
- `VerificationController` supports guest signed verification, and `AffiliateAccountService::MarkEmailVerified` moves an external profile from `PENDING_EMAIL` to `PENDING_APPROVAL`.
- `User` implements `MustVerifyEmail`, uses Spatie roles, and exposes one `AffiliateProfile` relationship.
- `affiliate_profiles.user_id` has a foreign key but no unique index. `affiliate_profiles.referral_code` is unique.
- `RedirectIfAffiliateUser`, `LoginController`, `AffiliateAuthController`, and the root route currently classify every `Affiliate-user` role assignment as Affiliate-only.
- Member-facing Affiliate routes currently use Laravel's generic `verified` middleware. Administrator Affiliate routes do not.
- `AffiliateAccountAdminQuery::Paginate` already paginates the existing profile administration table, so employee requests will appear in the current approval list without a second table or endpoint.
- Existing dashboard, analytics, media, payout-account, and withdrawal queries derive ownership from the authenticated user's active Affiliate profile.
- Local roles are `Admin`, `User`, `Teacher`, `SystemAdmin`, `BranchAdmin`, `Affiliate-user`, and `blog`.
- Local aggregate inspection found 33 users with an approved internal role and all 33 had null `email_verified_at`. This confirms that employee portal authorization cannot depend on email verification.
- Local aggregate inspection found no duplicate `affiliate_profiles.user_id` group. Production duplicates remain unknown.
- `php artisan route:list --path=affiliate --except-vendor` currently reports 43 Affiliate-related routes.
- The focused baseline command passed with 27 tests and 113 assertions:

```powershell
C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --do-not-cache-result tests\Feature\Affiliate\AffiliateRegistrationTest.php tests\Feature\Affiliate\AffiliateProfilePersistenceTest.php tests\Feature\Affiliate\AffiliateFinanceNavigationTest.php
```

## Known External Unknowns

- Production duplicate-profile ownership, role assignments, row counts, database version, and lock behavior are not evidenced locally.
- SQLite cannot prove deployed row-lock or duplicate-key race behavior. The plan includes an opt-in isolated MySQL concurrency test that must run in staging before rollout.
- Real employee role changes after enrollment are operational events outside this MVP. Portal eligibility is re-evaluated on every request, but profile lifecycle state is not mutated automatically.
- Email values on employee accounts may be placeholders or non-deliverable. The approved employee flow therefore sends no email and never modifies `email_verified_at`.

## Implementation Contracts

### Employee identity and eligibility

- The authenticated session user is the only enrollment subject; no route accepts a user or profile identifier.
- The case-sensitive allowlist is `Admin`, `SystemAdmin`, `BranchAdmin`, `Teacher`, and `blog`.
- `User`, Affiliate-only, unknown-role, and roleless accounts are ineligible.
- A user with an allowlisted role remains an employee even if legacy data also assigns `Affiliate-user`.
- The employee flow never assigns or removes a role.

### Email behavior

- Employee enrollment does not call `sendEmailVerificationNotification`, dispatch `Registered`, or modify `email` or `email_verified_at`.
- A new employee profile starts at `PENDING_APPROVAL`.
- An eligible employee's existing `PENDING_EMAIL` profile is normalized to `PENDING_APPROVAL` without marking the email verified.
- External registration and signed verification remain unchanged.

### Portal behavior

- Portal access requires an `ACTIVE` Affiliate profile.
- An eligible employee may access the portal without verified email.
- An external Affiliate-only user must still have verified email.
- An eligible employee with a non-active profile is redirected to the employee status page for ordinary HTML GET navigation.
- Ineligible users, JSON requests, and unsafe requests receive a localized `403` response rather than a redirect.

### Persistence and idempotency

- `affiliate_profiles.user_id` receives the explicit unique index `affiliate_profiles_user_id_unique`.
- The migration stops before adding the index if duplicate owners exist; it never merges or deletes data.
- Enrollment locks the user row, reads the profile under the transaction, and relies on the unique index as the final race boundary.
- Repeated requests return the existing profile without resetting lifecycle state or recording duplicate success audits.
- Concurrent duplicate-key recovery is allowed only when the exception identifies the user-profile unique constraint and the winning profile can be reloaded.

## Global Constraints

- Preserve unrelated dirty-worktree changes. Several Affiliate implementation and test files already contain user work.
- Do not commit, push, enable production flags, repair production data, or run production migrations without separate authorization.
- Do not add a second employee account, a second approval table, a profile-source column, or an employee notification workflow.
- Do not alter commission, attribution, wallet, payout-account, withdrawal, analytics, or media calculations.
- Preserve current route names and current external Affiliate login, registration, resend, verification, waiting, and lifecycle contracts.
- Keep money and financial authorization behavior unchanged.
- Add every visible string to both `lang/en/affiliate.php` and `lang/th/affiliate.php`, with matching leaves and placeholders.
- Follow repository naming rules: PascalCase methods/tests/callbacks, snake_case variables/properties/payload keys, UPPER_CASE constants, and kebab-case new routes and Blade files unless a framework convention takes precedence.

## Task 1: Add fail-closed employee enrollment configuration and central eligibility

**Files:**

- Modify: `config/affiliate.php`
- Modify: `.env.example`
- Modify: `phpunit.xml`
- Create: `app/Domain/Affiliate/Services/AffiliateEmployeeEligibility.php`
- Create: `tests/Feature/Affiliate/AffiliateEmployeeEligibilityTest.php`
- Modify: `tests/Feature/Affiliate/AffiliateFeatureConfigurationTest.php`

- **Consumes:** Spatie `HasRoles`, the existing fail-closed Affiliate feature pattern, and the approved role allowlist.
- **Produces:** One source of truth for employee eligibility and Affiliate-only classification.
- **Checkpoint:** No HTTP route or mutation exists yet.

Add this configuration contract while leaving existing keys unchanged:

```php
'features' => [
    // Existing feature keys remain unchanged.
    'employee_enrollment' => env('AFFILIATE_FEATURE_EMPLOYEE_ENROLLMENT', false),
],

'employee_roles' => array_values(array_filter(array_map(
    static fn (string $role_name): string => trim($role_name),
    explode(',', (string) env(
        'AFFILIATE_EMPLOYEE_ROLES',
        'Admin,SystemAdmin,BranchAdmin,Teacher,blog',
    )),
))),

'abuse' => [
    // Existing limits remain unchanged.
    'employee_enrollment_attempts_per_minute' => env(
        'AFFILIATE_EMPLOYEE_ENROLLMENT_ATTEMPTS_PER_MINUTE',
        6,
    ),
],
```

Add explicit disabled/default values to `.env.example` and forced test values to `phpunit.xml`. An empty role list must fail closed rather than granting access.

`AffiliateEmployeeEligibility` exposes only these public decisions:

```php
final class AffiliateEmployeeEligibility
{
    public function IsEligible(User $user): bool;

    public function IsAffiliateOnly(User $user): bool;
}
```

`IsAffiliateOnly` returns true only when the user has `Affiliate-user` and is not an eligible employee. Do not cache role results across requests.

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Write PascalCase tests covering each approved internal role, `User`, Affiliate-only, unknown-role, roleless, empty configuration, and a legacy internal-plus-`Affiliate-user` account.
- [ ] Run `C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --do-not-cache-result tests\Feature\Affiliate\AffiliateEmployeeEligibilityTest.php tests\Feature\Affiliate\AffiliateFeatureConfigurationTest.php`; expect RED because the service and keys do not exist.
- [ ] Implement the configuration and the two eligibility decisions without changing user roles.
- [ ] Rerun the same command; expect exit code 0 and every approved role case to pass.
- [ ] Refactor role normalization into one private PascalCase method, then rerun both files.

## Task 2: Enforce one Affiliate profile per user and block unsafe migration

**Files:**

- Create: `database/migrations/2026_08_11_000005_add_unique_user_to_affiliate_profiles.php`
- Modify: `app/Domain/Affiliate/Services/AffiliatePreflightService.php` at `Checks`
- Modify: `tests/Feature/Affiliate/AffiliateProfilePersistenceTest.php`
- Modify: `tests/Feature/Affiliate/AffiliatePreflightCommandTest.php`
- Create: `tests/Feature/Affiliate/AffiliateProfileOwnershipMigrationTest.php`

- **Consumes:** The existing singular `User::AffiliateProfile` relationship and current `affiliate_profiles` table.
- **Produces:** A database-enforced one-profile-per-user invariant and deployment evidence before migration.
- **Checkpoint:** The migration is additive and contains no data repair.

The migration must:

1. return safely when `affiliate_profiles` does not exist;
2. query `user_id` groups with `COUNT(*) > 1` before schema mutation;
3. throw a clear `RuntimeException` without listing email, referral, or financial data when any duplicate exists;
4. add `affiliate_profiles_user_id_unique` only when that unique index is absent; and
5. drop only that named index in `down()`.

Use `Schema::hasIndex(..., 'unique')` because that convention already exists in the 2026-08-11 migrations. Do not select a winner or delete a profile.

Add an unconditional `affiliate_profile_ownership` preflight check:

- `PASS` when the table exists and no duplicate `user_id` group exists;
- `BLOCKED` when a duplicate exists or the query cannot establish safety; and
- `UNKNOWN` only when the Affiliate profile table is not present in an environment where migrations have not been installed.

The migration test must use an isolated in-memory SQLite connection, create a minimal representative `affiliate_profiles` table, insert duplicate owners, load the migration object, and assert that `up()` throws before index creation. This avoids dropping an index from PHPUnit's shared migrated schema.

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Add PascalCase tests `AffiliateProfileUserIdIsUnique`, `DuplicateOwnersBlockTheMigrationWithoutDeletingRows`, `PreflightPassesWithoutDuplicateOwners`, and `PreflightBlocksDuplicateOwners`.
- [ ] Run `C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --do-not-cache-result tests\Feature\Affiliate\AffiliateProfilePersistenceTest.php tests\Feature\Affiliate\AffiliateProfileOwnershipMigrationTest.php tests\Feature\Affiliate\AffiliatePreflightCommandTest.php`; expect RED because the unique index and check do not exist.
- [ ] Implement the duplicate probe, named unique index, guarded rollback, and preflight result without exposing user data.
- [ ] Rerun the same command; expect exit code 0, a second profile for one user to raise `QueryException`, and representative duplicates to remain intact after the migration refusal.
- [ ] Run `C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan affiliate:preflight --json`; expect an `affiliate_profile_ownership` check and no mutation.

## Task 3: Extract one referral-code generator for both registration paths

**Files:**

- Create: `app/Domain/Affiliate/Services/AffiliateReferralCodeGenerator.php`
- Modify: `app/Domain/Affiliate/Services/AffiliateAccountService.php` at the constructor and `Register`
- Create: `tests/Unit/Affiliate/AffiliateReferralCodeGeneratorTest.php`
- Modify: `tests/Feature/Affiliate/AffiliateRegistrationTest.php`

- **Consumes:** The existing `AFF-` plus ten uppercase random characters format and unique `affiliate_profiles.referral_code` column.
- **Produces:** A focused generator reusable by employee enrollment without changing public registration.
- **Checkpoint:** External registration still owns new-user creation and email verification dispatch.

The generator contract is:

```php
final class AffiliateReferralCodeGenerator
{
    public function Generate(): string;
}
```

`Generate` retains the existing query-before-return loop. The database unique index remains the final collision boundary. Inject this service into `AffiliateAccountService` and remove only its private `GenerateReferralCode` method. Do not change external registration fields, status, role assignment, event dispatch, or audit operation.

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Add PascalCase tests for the exact `AFF-[A-Z0-9]{10}` format and for external registration retaining `PENDING_EMAIL`, `Affiliate-user`, and verification notification behavior.
- [ ] Run `C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --do-not-cache-result tests\Unit\Affiliate\AffiliateReferralCodeGeneratorTest.php tests\Feature\Affiliate\AffiliateRegistrationTest.php`; expect RED because the generator class does not exist.
- [ ] Implement the generator and inject it into external registration.
- [ ] Rerun the same command; expect exit code 0 and no external registration behavior change.
- [ ] Remove the old private generator and rerun the tests after the refactor.

## Task 4: Implement idempotent employee enrollment without email mutation

**Files:**

- Create: `app/Domain/Affiliate/Services/AffiliateEmployeeEnrollmentService.php`
- Modify: `app/Domain/Affiliate/Exceptions/AffiliateDomainException.php`
- Create: `tests/Feature/Affiliate/AffiliateEmployeeEnrollmentServiceTest.php`

- **Consumes:** `AffiliateFeatureGate`, `AffiliateConfigurationService`, `AffiliateEmployeeEligibility`, `AffiliateReferralCodeGenerator`, `AffiliateAuditService`, and the unique user-profile index.
- **Produces:** One transactional enrollment method with no controller or view concerns.
- **Checkpoint:** No route calls the service yet.

Expose this application boundary:

```php
final class AffiliateEmployeeEnrollmentService
{
    public function Enroll(User $user, string $correlation_id): AffiliateProfile;
}
```

`Enroll` must perform this ordered algorithm:

1. assert `employee_enrollment` through `AffiliateFeatureGate`;
2. assert Affiliate writes are available through `AffiliateConfigurationService`;
3. reject a non-eligible user with `AffiliateDomainException::EmployeeEnrollmentDenied()` and HTTP status 403;
4. start a database transaction and lock the authenticated user row;
5. load that user's Affiliate profile with `lockForUpdate()`;
6. return existing `PENDING_APPROVAL`, `ACTIVE`, `SUSPENDED`, or `DEACTIVATED` unchanged;
7. change an existing `PENDING_EMAIL` to `PENDING_APPROVAL`, leave email fields unchanged, and record `affiliate.employee-enrollment.email-bypassed` once;
8. when no profile exists, generate a profile code, create `PENDING_APPROVAL`, set `users.referral` only when null or blank, and record `affiliate.employee-enrollment.created` once; and
9. commit the profile and audit together.

Use UPPER_CASE constants for the operation names. Audit state contains only lifecycle status and does not contain email, role lists, password, session data, or referral URLs.

For a concurrent unique conflict, catch `QueryException` outside the failed transaction. Recover only when:

- the SQLSTATE is an integrity/unique violation supported by MySQL, SQLite, or PostgreSQL;
- the message identifies `affiliate_profiles_user_id_unique` or `affiliate_profiles.user_id`; and
- the winning profile can be reloaded for the same user.

Otherwise rethrow the original exception. This prevents an unrelated audit or persistence failure from being hidden as idempotency.

Snapshot and assert these user values before and after enrollment: `email`, `email_verified_at`, password hash, `phone`, `department_id`, `position_id`, `affiliate_request`, `disable`, and role names. Use `Notification::fake()` and assert that no notification is sent.

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Write PascalCase tests `EligibleEmployeeCreatesPendingApprovalWithoutEmailVerification`, `PendingEmailEmployeeIsNormalizedWithoutEmailMutation`, `RepeatedEnrollmentReturnsOneProfileAndOneCreationAudit`, `ExistingLifecycleStateIsNeverReset`, `ExistingLegacyReferralIsNotOverwritten`, `IneligibleUserCannotEnroll`, and `DisabledOrMaintenanceModeCannotMutate`.
- [ ] Run `C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --do-not-cache-result tests\Feature\Affiliate\AffiliateEmployeeEnrollmentServiceTest.php`; expect RED because the service and employee denial do not exist.
- [ ] Implement the transaction, exact state handling, audit calls, and narrowly scoped duplicate recovery.
- [ ] Rerun the same command; expect exit code 0, exactly one profile and creation audit after repeats, no notification, and unchanged user identity/staff fields.
- [ ] Refactor state branching into private PascalCase methods only after the tests pass, then rerun the file.

## Task 5: Publish authenticated employee access routes with dedicated throttling

**Files:**

- Create: `app/Http/Controllers/Affiliate/AffiliateEmployeeAccessController.php`
- Create: `app/Http/Middleware/EnsureAffiliateEmployeeEligibility.php`
- Modify: `app/Http/Kernel.php` at `$middlewareAliases`
- Modify: `app/Providers/RouteServiceProvider.php` at Affiliate rate limiters
- Modify: `app/Http/Middleware/RequireAffiliateFeature.php`
- Modify: `routes/affiliate.php`
- Create: `tests/Feature/Affiliate/AffiliateEmployeeAccessHttpTest.php`

- **Consumes:** The enrollment service, eligibility service, Affiliate locale/correlation middleware, web session authentication, and feature gate.
- **Produces:** The approved GET/POST web contract and Post/Redirect/Get behavior.
- **Checkpoint:** The status view is added in Task 6.

Add these routes before member portal routes:

```php
Route::middleware([
    'auth',
    'affiliate.locale',
    'affiliate.correlation',
    'affiliate.employee-eligibility',
    'affiliate.feature:employee_enrollment',
])
    ->prefix('affiliate/employee-access')
    ->group(function (): void {
        Route::get('/', [AffiliateEmployeeAccessController::class, 'Show'])
            ->name('affiliate.employee-access.show');
        Route::post('/', [AffiliateEmployeeAccessController::class, 'Store'])
            ->middleware('throttle:affiliate-employee-enrollment')
            ->name('affiliate.employee-access.store');
    });
```

Register `EnsureAffiliateEmployeeEligibility` as `affiliate.employee-eligibility`. It calls the shared eligibility service and, for a non-eligible authenticated user, returns `AFFILIATE_EMPLOYEE_ENROLLMENT_DENIED` with localized status 403 as JSON when JSON is expected and as a plain/web 403 otherwise. The enrollment service still rechecks eligibility inside the transaction boundary.

The controller must use only `Show(Request $request): View` and `Store(Request $request): RedirectResponse`. `Show` passes the authenticated user and current profile to the view. `Store` passes the authenticated user plus request correlation ID to `Enroll`, then redirects to the GET route with a localized status flash. It accepts no request fields and contains no role or email decision.

Register `affiliate-employee-enrollment` with a per-authenticated-user key and `affiliate.abuse.employee_enrollment_attempts_per_minute`. Its 429 body must use `affiliate.employee_access.rate_limited`, include standard rate-limit headers, and log only `user_id`, route, correlation ID, and operation. Do not log email or IP.

Update `RequireAffiliateFeature` to render `__($exception->SafeTranslationKey())` instead of the internal reason. Preserve status code and `error_code` so disabled employee enrollment returns a localized 503 without mutation and existing feature contracts remain stable.

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Write PascalCase HTTP tests for guest redirect to login, eligible GET, ineligible 403, POST PRG, no accepted user identifier, disabled feature 503, maintenance no mutation, and the seventh request receiving localized 429 when the configured limit is six.
- [ ] Run `C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --do-not-cache-result tests\Feature\Affiliate\AffiliateEmployeeAccessHttpTest.php`; expect RED because the routes/controller/limiter do not exist.
- [ ] Implement the eligibility middleware/alias, controller, route group, limiter, and safe feature message.
- [ ] Rerun the same command; expect exit code 0 and no enrollment for guest, ineligible, disabled, maintenance, or throttled requests.
- [ ] Run `C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan route:list --name=affiliate.employee-access`; expect exactly the approved GET and POST routes.

## Task 6: Build the localized employee status page and global entry point

**Files:**

- Create: `resources/views/affiliate/employee-access.blade.php`
- Modify: `resources/views/layouts/header.blade.php`
- Modify: `lang/en/affiliate.php`
- Modify: `lang/th/affiliate.php`
- Modify: `tests/Unit/Affiliate/AffiliateTranslationStructureTest.php`
- Modify: `tests/Feature/Affiliate/AffiliateEmployeeAccessHttpTest.php`

- **Consumes:** Existing `layouts.app`, `public/css/affiliate-portal.css`, employee eligibility, and the current profile enum.
- **Produces:** One consistent status page and one `My Affiliate` user-menu link for eligible employees.
- **Checkpoint:** Pending employees still cannot enter active member pages until Task 7.

Add an `employee_access` translation namespace in both locales with matching leaves for:

- title, introduction, menu label, and request action;
- no-profile, pending-approval, active, suspended, and deactivated status text;
- open-dashboard and contact-administrator actions;
- request success, normalized-email-bypass success, forbidden, unavailable, and rate-limited feedback; and
- an accessible status region label.

The Blade page must:

- extend `layouts.app` and load the existing `affiliate-portal.css`;
- use the same `content-header`, `content`, `container-fluid`, `card card-primary`, `card-header`, `card-body`, and `card-footer` structure as the current Affiliate finance pages;
- render one POST form only when no profile exists or the profile is `PENDING_EMAIL`;
- render no repeat form for `PENDING_APPROVAL`;
- link to `affiliate.dashboard` only for `ACTIVE`;
- explain `SUSPENDED` and `DEACTIVATED` without disabling staff access;
- show session feedback with `role="status"` and `aria-live="polite"`; and
- use escaped Blade output and semantic headings/buttons with visible focus behavior inherited from Bootstrap/AdminLTE.

Inject `AffiliateEmployeeEligibility` in `layouts/header.blade.php`. Show the `My Affiliate` dropdown item only when the feature flag is enabled and the authenticated user is eligible. The link always goes to the employee status page; that page decides whether to show request, pending status, or dashboard access.

Do not add a new stylesheet, shadow, gradient, animation, JavaScript, or table for this page.

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Add rendering tests for all six page states, hidden action rules, accessible live feedback, and user-menu visibility for eligible/ineligible/disabled-feature cases.
- [ ] Add `employee_access` to the required translation namespaces and keep English/Thai leaves and placeholders identical.
- [ ] Run `C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --do-not-cache-result tests\Feature\Affiliate\AffiliateEmployeeAccessHttpTest.php tests\Unit\Affiliate\AffiliateTranslationStructureTest.php`; expect RED before the view and translations exist.
- [ ] Implement the page, strings, and header entry point.
- [ ] Rerun the same command; expect exit code 0 and no missing translation keys.
- [ ] Perform a keyboard-only browser check at `/affiliate/employee-access` for focus order, visible focus, status announcement, and responsive layout at 320px and desktop widths.

## Task 7: Replace generic email verification with profile-aware portal authorization

**Files:**

- Create: `app/Domain/Affiliate/Services/AffiliatePortalAccess.php`
- Create: `app/Http/Middleware/EnsureAffiliatePortalAccess.php`
- Modify: `app/Http/Kernel.php` at `$middlewareAliases`
- Modify: `routes/affiliate.php` at member-facing route middleware
- Create: `tests/Feature/Affiliate/AffiliatePortalAccessTest.php`
- Modify: `tests/Feature/Affiliate/AffiliateFinanceNavigationTest.php`
- Modify: `tests/Feature/Affiliate/AffiliateAnalyticsApiTest.php`
- Modify: `tests/Feature/Affiliate/AffiliateMediaLibraryTest.php`
- Modify: `tests/Feature/Affiliate/AffiliatePayoutAccountApiTest.php`
- Modify: `tests/Feature/Affiliate/AffiliateWithdrawalUiTest.php`

- **Consumes:** `AffiliateEmployeeEligibility`, `AffiliateProfileStatus::ACTIVE`, `User::hasVerifiedEmail`, and active-profile ownership checks inside existing queries/services.
- **Produces:** Middleware alias `affiliate.portal-access` and one reusable view authorization decision.
- **Checkpoint:** Administrator Affiliate routes remain unchanged.

The domain decision is:

```php
final class AffiliatePortalAccess
{
    public function CanAccess(User $user): bool;
}
```

`CanAccess` returns true only when the current user owns an `ACTIVE` profile and either:

- `AffiliateEmployeeEligibility::IsEligible($user)` is true; or
- `AffiliateEmployeeEligibility::IsAffiliateOnly($user)` is true and `hasVerifiedEmail()` is true.

`EnsureAffiliatePortalAccess::handle` must:

1. let `auth` remain responsible for unauthenticated requests;
2. continue when `CanAccess` is true;
3. redirect an eligible employee's safe, non-JSON page request to `affiliate.employee-access.show`;
4. redirect an Affiliate-only external user's safe, non-JSON page request to `affiliate.waiting`;
5. return localized JSON 403 for JSON requests; and
6. return localized plain/web 403 for ineligible or unsafe non-JSON requests.

Replace only `verified` on these member routes/groups with `affiliate.portal-access`, keeping locale, correlation, and feature middleware:

- `affiliate.analytics.index` and `affiliate.analytics.data`;
- `affiliate.media.index`;
- payout-account update routes;
- withdrawal create/cancel routes;
- `affiliate.dashboard`;
- `affiliate.payout-accounts.index`;
- `affiliate.withdrawals.index`; and
- `my_affiliate.index`.

Place middleware in this order: `auth`, `affiliate.locale`, `affiliate.correlation`, `affiliate.portal-access`, then the route's feature gate. Do not apply portal middleware to `/admin/affiliates`, Affiliate finance administration, withdrawal administration, reconciliation, content administration, registration, resend, verification, waiting, or public tracking.

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Add PascalCase tests for active unverified employee access, active verified external access, active unverified external denial, pending/suspended/deactivated employee status redirects, ineligible 403, JSON 403, unsafe-request 403, and unchanged administrator access.
- [ ] Include representative successful employee requests for dashboard, analytics data, media, payout-account update, and withdrawal behavior so the test proves the request reaches existing ownership/domain validation rather than Laravel's `verified` redirect.
- [ ] Run `C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --do-not-cache-result tests\Feature\Affiliate\AffiliatePortalAccessTest.php tests\Feature\Affiliate\AffiliateFinanceNavigationTest.php tests\Feature\Affiliate\AffiliateAnalyticsApiTest.php tests\Feature\Affiliate\AffiliateMediaLibraryTest.php tests\Feature\Affiliate\AffiliatePayoutAccountApiTest.php tests\Feature\Affiliate\AffiliateWithdrawalUiTest.php`; expect RED before the middleware exists.
- [ ] Implement the decision, middleware alias, and exact route replacements.
- [ ] Rerun the same command; expect exit code 0, employee email bypass only for eligible roles, and external email enforcement unchanged.
- [ ] Run `C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan route:list --path=affiliate --except-vendor -v` and inspect middleware on every listed member and administrator route.

## Task 8: Preserve staff landing behavior and external Affiliate-only redirects

**Files:**

- Modify: `app/Http/Middleware/RedirectIfAffiliateUser.php`
- Modify: `app/Http/Controllers/Auth/LoginController.php`
- Modify: `app/Http/Controllers/AffiliateAuthController.php`
- Modify: `routes/web.php` at the root closure
- Create: `tests/Feature/Affiliate/AffiliateEmployeeRedirectTest.php`
- Modify: `tests/Feature/Affiliate/AffiliateRegistrationTest.php`

- **Consumes:** `AffiliateEmployeeEligibility::IsAffiliateOnly` and current profile status redirects.
- **Produces:** Staff-first navigation for employees and unchanged external Affiliate redirect behavior.
- **Checkpoint:** No role or profile mutation occurs during redirect classification.

Inject or resolve `AffiliateEmployeeEligibility` at all four redirect sites. Replace direct `hasRole('Affiliate-user')` classification with `IsAffiliateOnly($user)`.

Required outcomes:

- an eligible employee, including a legacy employee who also has `Affiliate-user`, is allowed through `affiliate.redirect` and lands in the staff system;
- an active Affiliate-only external account still redirects to `affiliate.dashboard`;
- a non-active Affiliate-only external account still redirects to `affiliate.waiting`;
- an authenticated employee visiting `/affiliate/login` returns to the staff home route;
- an employee visiting `/affiliate/waiting` is redirected to `affiliate.employee-access.show`; and
- the root route preserves the existing `User`-role welcome behavior for non-employees.

Do not alter disabled-user login checks, password authentication, Socialite behavior, or the guest waiting-session flow.

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Write PascalCase tests for internal-only, internal-plus-`Affiliate-user`, active external, pending external, disabled employee, and root/login/waiting navigation.
- [ ] Run `C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --do-not-cache-result tests\Feature\Affiliate\AffiliateEmployeeRedirectTest.php tests\Feature\Affiliate\AffiliateRegistrationTest.php`; expect RED for legacy dual-role employee cases.
- [ ] Implement service-based classification at every verified redirect site.
- [ ] Rerun the same command; expect exit code 0 and all existing external Affiliate redirect tests to remain green.
- [ ] Run `rg -n "hasRole\('Affiliate-user'\)" app\Http\Middleware\RedirectIfAffiliateUser.php app\Http\Controllers\Auth\LoginController.php app\Http\Controllers\AffiliateAuthController.php routes\web.php`; expect no redirect decision to bypass the eligibility service.

## Task 9: Make navigation visibility match portal authorization

**Files:**

- Modify: `resources/views/layouts/menu.blade.php`
- Modify: `resources/views/affiliate/partials/navigation.blade.php`
- Modify: `resources/views/affiliate/frontend/header.blade.php`
- Modify: `tests/Feature/Affiliate/AffiliateFinanceNavigationTest.php`
- Modify: `tests/Feature/Affiliate/AffiliateEmployeeAccessHttpTest.php`

- **Consumes:** `AffiliatePortalAccess::CanAccess` and employee feature/eligibility decisions.
- **Produces:** Navigation that cannot advertise a route the middleware will deny.
- **Checkpoint:** Route authorization remains authoritative even when a view is bypassed.

Inject `AffiliatePortalAccess` in each relevant Blade view and replace direct `hasVerifiedEmail()` plus active-profile checks with `CanAccess(auth()->user())`. Preserve each existing feature flag around dashboard, analytics, media, payout-account, and withdrawal links.

In `affiliate/frontend/header.blade.php`, change only Affiliate member-link visibility and classification. Preserve unrelated profile, module, and join-program behavior. Eligible employees without an active profile should see the employee status destination, not active dashboard/finance links.

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Add rendering tests proving active unverified employees see permitted Affiliate navigation, unverified external users do not, pending employees see only the employee status entry point, and disabled feature flags still hide their own destinations.
- [ ] Run `C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --do-not-cache-result tests\Feature\Affiliate\AffiliateFinanceNavigationTest.php tests\Feature\Affiliate\AffiliateEmployeeAccessHttpTest.php`; expect RED for employee navigation.
- [ ] Implement the shared portal decision in the three views without duplicating role lists.
- [ ] Rerun the same command; expect exit code 0 and navigation/middleware parity.
- [ ] Run `rg -n "hasVerifiedEmail\(\).*AffiliateProfile|AffiliateProfile.*hasVerifiedEmail\(" resources\views\layouts\menu.blade.php resources\views\affiliate`; expect no remaining combined portal authorization condition in the scoped views.

## Task 10: Prove administrator lifecycle behavior for employee profiles

**Files:**

- Modify: `tests/Feature/Affiliate/AffiliateAccountLifecycleTest.php`
- Create: `tests/Feature/Affiliate/AffiliateEmployeeAdministrationTest.php`
- Inspect only: `app/Domain/Affiliate/Queries/AffiliateAccountAdminQuery.php`
- Inspect only: `app/Http/Controllers/Affiliate/AffiliateAccountAdminController.php`
- Inspect only: `resources/views/affiliate/admin/index.blade.php`

- **Consumes:** The existing paginated administration query and lifecycle service.
- **Produces:** Regression evidence that employee requests use the existing approval process safely.
- **Checkpoint:** No second administration UI is introduced.

Tests must prove:

- a new employee `PENDING_APPROVAL` profile appears in the existing paginated admin list;
- the existing approve endpoint changes only the profile to `ACTIVE` and does not assign `Affiliate-user`;
- suspension and deactivation do not set `users.disable`, remove staff roles, or alter employee identity fields;
- a deactivated profile remains terminal under current lifecycle rules; and
- pagination links remain present when more than 50 representative profiles exist.

If these tests pass against existing administration code, make no production-code change in this task. A failing test may authorize only the smallest behavior-preserving fix required by the approved specification; do not redesign the admin table.

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Add PascalCase lifecycle and administration tests with staff-field snapshots.
- [ ] Run `C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --do-not-cache-result tests\Feature\Affiliate\AffiliateAccountLifecycleTest.php tests\Feature\Affiliate\AffiliateEmployeeAdministrationTest.php`; expect current administration to pass most assertions and expose only genuine integration gaps.
- [ ] If RED, implement only the verified minimum fix; if GREEN, leave production code untouched.
- [ ] Rerun the same command; expect exit code 0, no employee role assignment, and pagination metadata/links for the second page.

## Task 11: Verify concurrent first enrollment on an isolated MySQL database

**Files:**

- Create: `tests/Feature/Affiliate/AffiliateEmployeeEnrollmentConcurrencyTest.php`
- Create: `tests/Feature/Affiliate/Fixtures/affiliate-employee-enrollment-worker.php`
- Modify: `.env.example` only if the existing concurrency opt-in variables are not already documented

- **Consumes:** The enrollment service, user-row locking, named unique index, and existing opt-in concurrency-test safety convention.
- **Produces:** Staging evidence for two simultaneous first requests.
- **Checkpoint:** The test skips on SQLite and never runs against an unapproved database.

Mirror the safety gates in `AffiliateClickConcurrencyTest`:

- skip on SQLite;
- require `AFFILIATE_RUN_CONCURRENCY_TESTS=true`;
- require `AFFILIATE_CONCURRENCY_DATABASE` to exactly equal the current isolated database name; and
- never accept a production-like or implicit database target.

Use `DatabaseMigrations` rather than a parent transaction so both worker processes can see committed setup rows. Start two PHP workers, wait until both have bootstrapped, release one temporary barrier, and have both call `AffiliateEmployeeEnrollmentService::Enroll` for the same eligible user with distinct correlation IDs. Clean up the barrier and processes in `finally`.

Assert that both workers succeed and return the same profile ID, while the database contains exactly:

- one Affiliate profile for the user;
- one `affiliate.employee-enrollment.created` audit;
- zero `Affiliate-user` assignment; and
- unchanged email and `email_verified_at`.

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Add the guarded test and worker; run it under PHPUnit's default SQLite configuration and expect one explicit skip rather than a false pass.
- [ ] Run `C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --do-not-cache-result tests\Feature\Affiliate\AffiliateEmployeeEnrollmentConcurrencyTest.php`; expect the documented SQLite skip locally.
- [ ] On an authorized isolated MySQL staging database, set both opt-in variables and rerun the same file; expect exit code 0 and one profile/one audit.
- [ ] Intentionally omit or mismatch `AFFILIATE_CONCURRENCY_DATABASE`; expect the test to skip before starting workers.

## Task 12: Run full regression, static checks, and rollout preflight

**Files:**

- Modify: `docs/specs/2026-08-11-employee-affiliate-enrollment-design.md` only to set implementation status after every required check passes
- Create: `docs/verification/2026-08-11-employee-affiliate-enrollment.md`

- **Consumes:** All prior tasks.
- **Produces:** Reproducible verification evidence and a guarded rollout handoff.
- **Checkpoint:** The feature remains disabled by default.

Run focused behavior first:

```powershell
C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --do-not-cache-result tests\Feature\Affiliate\AffiliateEmployeeEligibilityTest.php tests\Feature\Affiliate\AffiliateProfilePersistenceTest.php tests\Feature\Affiliate\AffiliateProfileOwnershipMigrationTest.php tests\Unit\Affiliate\AffiliateReferralCodeGeneratorTest.php tests\Feature\Affiliate\AffiliateEmployeeEnrollmentServiceTest.php tests\Feature\Affiliate\AffiliateEmployeeAccessHttpTest.php tests\Feature\Affiliate\AffiliatePortalAccessTest.php tests\Feature\Affiliate\AffiliateEmployeeRedirectTest.php tests\Feature\Affiliate\AffiliateEmployeeAdministrationTest.php tests\Unit\Affiliate\AffiliateTranslationStructureTest.php
```

Then run the preserved external and financial regressions:

```powershell
C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --do-not-cache-result tests\Feature\Affiliate\AffiliateRegistrationTest.php tests\Feature\Affiliate\AffiliateAccountLifecycleTest.php tests\Feature\Affiliate\AffiliateFinanceNavigationTest.php tests\Feature\Affiliate\AffiliatePayoutAccountApiTest.php tests\Feature\Affiliate\AffiliateWithdrawalUiTest.php tests\Feature\Affiliate\AffiliateAnalyticsApiTest.php tests\Feature\Affiliate\AffiliateMediaLibraryTest.php
```

Run the complete Affiliate suites and then the complete repository suite:

```powershell
C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --do-not-cache-result tests\Feature\Affiliate tests\Unit\Affiliate
C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --do-not-cache-result
```

Run non-mutating structural checks:

```powershell
C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan route:list --path=affiliate --except-vendor -v
C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan affiliate:preflight --json
git diff --check
```

Before staging migration, run a read-only production duplicate query through the approved operational channel:

```sql
SELECT user_id, COUNT(*) AS profile_count
FROM affiliate_profiles
GROUP BY user_id
HAVING COUNT(*) > 1;
```

Do not run the migration when this query returns any row. Duplicate remediation requires a separate reviewed plan because profile-owned financial and attribution records may exist.

The verification document must record commands, timestamps, exit codes, pass/fail/skip counts, route middleware evidence, preflight output with secrets removed, MySQL concurrency evidence, browser checks, and remaining external unknowns. It must not claim production readiness without staging MySQL evidence and a clean duplicate query.

- [ ] Run all focused and regression commands and record exact evidence.
- [ ] Run the complete suite; investigate failures without overwriting unrelated work.
- [ ] Confirm both employee routes exist, every member route uses `affiliate.portal-access`, and administrator routes do not.
- [ ] Confirm the feature is disabled by default and the global menu hides the entry when disabled.
- [ ] Confirm no employee path assigns `Affiliate-user`, sends verification email, or writes `email_verified_at`.
- [ ] Confirm external registration still sends verification and creates `PENDING_EMAIL`.
- [ ] Run the approved staging MySQL concurrency check and production duplicate preflight before enabling the feature.
- [ ] Only after all required local checks pass, update the specification implementation status and write the verification report.

## Requirement Traceability

| Approved requirement | Plan coverage |
| --- | --- |
| Existing employee account, no duplicate identity | Tasks 1, 4, 8 |
| No employee `Affiliate-user` role | Tasks 4, 8, 10, 11 |
| Internal role allowlist | Tasks 1, 5, 7 |
| Employee email bypass with no fake verification | Tasks 4, 7, 10, 11 |
| Administrator approval remains mandatory | Tasks 4, 6, 10 |
| External email verification unchanged | Tasks 3, 7, 8, 12 |
| Staff login remains staff-first | Task 8 |
| Active employee portal access without verified email | Tasks 7, 9 |
| Suspended/deactivated Affiliate does not disable staff | Tasks 6, 10 |
| One profile per user | Tasks 2, 4, 11 |
| Repeated/concurrent requests are idempotent | Tasks 4, 11 |
| Existing paginated administration is reused | Task 10 |
| Feature-disabled safe rollout | Tasks 1, 5, 6, 12 |
| Localized and accessible status experience | Tasks 5, 6, 7, 9 |
| Production duplicate safety | Tasks 2, 12 |

## Approval and Execution Handoff

This plan makes no implementation change by itself. After explicit approval:

1. use `git-worktrees` to create an isolated implementation worktree because the current worktree contains unrelated user changes;
2. use `executing-plans` to execute tasks in dependency order;
3. use `test-driven-development` for every production behavior task; and
4. use `verification-before-completion` before claiming completion.

Do not start implementation, create a worktree, stage files, commit, push, migrate production, or enable `AFFILIATE_FEATURE_EMPLOYEE_ENROLLMENT` until the user explicitly approves this plan.
