# Employee Affiliate Enrollment Design

**Date:** 2026-08-11

**Design approval:** Approved by the user on 2026-08-11

**Specification approval:** Approved by the user on 2026-08-11

**Implementation status:** Not started

## 1. Purpose

This design lets an existing employee request Affiliate access without creating a second user account. The employee keeps the existing staff identity, roles, department, position, password, and login behavior. Affiliate participation is added through the current `AffiliateProfile` domain and remains subject to administrator approval.

Employee email addresses are not an identity or delivery requirement for this flow. Some employee accounts intentionally do not use a real email address. Employee identity is therefore established by the authenticated staff session and the existing administrator approval step. The external Affiliate registration flow continues to require email verification.

## 2. Verified Current State

Repository and local runtime inspection on 2026-08-11 established the following:

- `User` uses Spatie roles and exposes a `hasOne` relationship to `AffiliateProfile`.
- Public Affiliate registration always creates a new `User`, validates email and phone uniqueness against `users`, assigns the `Affiliate-user` role when available, and creates a `PENDING_EMAIL` profile.
- The existing external flow moves a profile from `PENDING_EMAIL` to `PENDING_APPROVAL` only after email verification.
- Login, root-route, and `affiliate.redirect` behavior currently treat any user with the `Affiliate-user` role as an Affiliate-only account and redirect that user away from staff pages.
- Staff user administration currently removes every role assignment before assigning the one selected role. It is not safe to use an additional `Affiliate-user` role as the employee Affiliate capability.
- `affiliate_profiles.user_id` has a foreign key but no database unique constraint, despite the model relationship being singular.
- Current Affiliate portal queries derive ownership from the authenticated user's active `AffiliateProfile`.
- The current Affiliate navigation requires both a verified email and an active profile.
- Local roles are `Admin`, `User`, `Teacher`, `SystemAdmin`, `BranchAdmin`, `Affiliate-user`, and `blog`.
- Local aggregate inspection found 33 users with an internal role and all 33 have a null `email_verified_at` value.
- Local aggregate inspection found zero user groups with duplicate Affiliate profiles.

Production role assignments, duplicate-profile counts, and data shape remain externally unverified. Local absence of duplicate profiles is not evidence that production contains none.

## 3. Approved Product Decisions

1. An employee uses the existing user account and does not register a second Affiliate account.
2. An employee Affiliate relationship is represented by `AffiliateProfile`, not by assigning the `Affiliate-user` role.
3. Eligible employees may submit their own Affiliate access request.
4. Every employee request requires administrator approval.
5. Employee requests skip email verification entirely.
6. The system must not send an employee verification email or alter `email_verified_at` to simulate verification.
7. Employee login continues to land in the staff system.
8. Affiliate suspension or deactivation affects only Affiliate behavior and does not disable the employee account.
9. External Affiliate registration, email verification, waiting, and approval behavior remain unchanged.

## 4. Goals

- Prevent duplicate identities, credentials, email conflicts, and fragmented audit history.
- Preserve every existing staff role and staff permission during Affiliate enrollment.
- Give eligible employees a clear request and status experience.
- Reuse the existing Affiliate lifecycle and administrator approval interface.
- Make one Affiliate profile per user a database-enforced invariant.
- Keep employee access independent of email authenticity or delivery.
- Preserve current external Affiliate security and verification behavior.
- Make repeated and concurrent requests safe and idempotent.

## 5. Non-Goals

- Replacing or redesigning the external Affiliate registration flow
- Automatically approving employee Affiliate requests
- Giving employees the `Affiliate-user` role
- Making employee email addresses real, unique, deliverable, or verified
- Sending employee request or approval notifications by email
- Changing commission, attribution, wallet, payout-account, or withdrawal calculations
- Automatically changing Affiliate lifecycle state when a staff role changes
- Adding employee-versus-external Affiliate reporting or filters in this MVP
- Redesigning general staff role administration

## 6. Approaches Considered

### 6.1 Separate Affiliate account

This requires the least architectural change but conflicts with current unique email and phone validation. It also creates two credentials, two identities, and fragmented account history. This approach is rejected.

### 6.2 Add `Affiliate-user` as a second employee role

Spatie supports multiple roles, but current application behavior does not. Affiliate redirects treat the role as exclusive, and staff role editing deletes all current role assignments. This approach creates avoidable regressions and is rejected.

### 6.3 Add an Affiliate profile capability to the existing employee account

This approach preserves the staff identity and uses the current Affiliate ownership and lifecycle model. The `Affiliate-user` role remains an external Affiliate-only account classification. This is the approved approach.

## 7. Employee Eligibility

Employee eligibility is controlled through a case-sensitive configuration allowlist containing:

- `Admin`
- `SystemAdmin`
- `BranchAdmin`
- `Teacher`
- `blog`

A user is an internal employee for this feature when the authenticated user has at least one allowlisted role. A user who has an allowlisted internal role remains an employee even if legacy data also assigns another role. A user with only `User`, only `Affiliate-user`, an unknown role, or no role is not eligible for the employee enrollment endpoint.

Eligibility is evaluated on every employee-enrollment and Affiliate-portal request. Controllers and views must not duplicate the role rules. A focused eligibility service is the single source of truth.

Removing every eligible employee role later denies employee-style portal access but does not mutate an Affiliate profile, referral ownership, or financial records. Lifecycle changes remain explicit administrator actions.

## 8. Employee Experience

### 8.1 Entry point

The global authenticated user menu adds an `My Affiliate` destination for eligible employees. The destination opens a single employee Affiliate access page.

### 8.2 Status states

| Profile state | Employee page behavior |
| --- | --- |
| No profile | Explain the program and show `Request Affiliate access` |
| `PENDING_EMAIL` | Convert to `PENDING_APPROVAL` on an eligible employee request without email verification |
| `PENDING_APPROVAL` | Show a persistent pending-approval message and no repeat action |
| `ACTIVE` | Show `Open Affiliate dashboard` |
| `SUSPENDED` | Explain that Affiliate access is suspended while staff access remains available |
| `DEACTIVATED` | Explain that the employee must contact an administrator; do not allow a new request |

The page uses localized Thai and English strings, semantic status text, visible focus states, and an accessible live status message after a successful request. Status must not rely on color alone.

### 8.3 Staff and Affiliate navigation

An employee continues to land on the staff home page after login. An active employee profile makes the existing Affiliate navigation available. The current staff dashboard and staff menus remain available according to the employee's original roles and permissions.

Affiliate-only external accounts retain the current Affiliate redirect behavior. Redirect decisions must classify an account as Affiliate-only only when it has the `Affiliate-user` role and no allowlisted internal employee role.

## 9. HTTP Interfaces

Add these authenticated web routes:

| Method | Path | Route name | Purpose |
| --- | --- | --- | --- |
| `GET` | `/affiliate/employee-access` | `affiliate.employee-access.show` | Render employee enrollment status |
| `POST` | `/affiliate/employee-access` | `affiliate.employee-access.store` | Submit or normalize an employee request |

Both routes use the `web`, `auth`, Affiliate locale, Affiliate correlation, and employee-enrollment feature middleware. The POST route additionally uses a dedicated employee-enrollment rate limiter. CSRF protection is inherited from the `web` stack.

Neither route accepts a user identifier. The authenticated session user is the only enrollment subject.

POST follows the Post/Redirect/Get pattern and redirects to the GET route with localized success feedback. A repeated valid request returns the current state without creating another profile or another successful-creation audit event.

## 10. Application Responsibilities

### 10.1 `AffiliateEmployeeEligibility`

This focused service:

- reads the configured internal role allowlist;
- decides whether an authenticated user is an eligible employee;
- distinguishes internal employees from Affiliate-only accounts; and
- is reused by enrollment, portal authorization, redirect behavior, and navigation.

### 10.2 `AffiliateEmployeeEnrollmentService`

This service owns the employee request transaction. It:

1. asserts that Affiliate writes and employee enrollment are enabled;
2. rechecks employee eligibility;
3. locks the authenticated user row;
4. loads any existing Affiliate profile under the transaction;
5. returns existing `PENDING_APPROVAL`, `ACTIVE`, `SUSPENDED`, or `DEACTIVATED` state without duplication;
6. moves an existing employee `PENDING_EMAIL` profile to `PENDING_APPROVAL` without touching email verification;
7. creates a new `PENDING_APPROVAL` profile when none exists;
8. sets the legacy `users.referral` field only when it is null or empty and never overwrites an existing value; and
9. records an Affiliate audit event only for an actual creation or `PENDING_EMAIL` normalization.

The service does not assign, remove, or synchronize roles. It does not modify department, position, password, phone, email, `email_verified_at`, `affiliate_request`, or account-disable state.

### 10.3 `AffiliateReferralCodeGenerator`

Referral-code generation moves from the private registration method into one focused generator used by both external registration and employee enrollment. It keeps the existing `AFF-` format and checks `affiliate_profiles.referral_code` uniqueness. The database unique constraint remains the final concurrency boundary.

### 10.4 Employee access controller

The controller has only show and store responsibilities. It delegates eligibility and state mutation to the focused services, returns localized user-facing feedback, and contains no role, referral-code, or lifecycle implementation logic.

## 11. Portal Authorization

Add an `affiliate.portal-access` middleware for member-facing Affiliate pages and state-changing Affiliate endpoints. It replaces the generic `verified` middleware on the dashboard, analytics, media library, payout-account, withdrawal, and compatibility routes. It is not applied to Affiliate administration routes.

The middleware grants portal access only when:

1. the request has an authenticated user;
2. the authenticated user owns an `ACTIVE` Affiliate profile; and
3. either:
   - the user is an eligible internal employee, with no email requirement; or
   - the user is an external Affiliate account with a verified email.

The middleware and the existing active-profile query are defense-in-depth boundaries. The active-profile query remains the ownership source inside domain queries and services.

An ineligible user receives `403 Forbidden`. An eligible employee with a non-active profile is directed to the employee access status page for ordinary GET page navigation. JSON requests and state-changing requests return a localized forbidden response and do not redirect. External non-active Affiliate accounts retain their waiting-page behavior.

Navigation visibility must call the same portal-access decision rather than directly checking `hasVerifiedEmail()`. This prevents employee menus from disagreeing with route authorization.

## 12. Email Semantics

Employee enrollment never depends on the value, authenticity, uniqueness, ownership, verification, or deliverability of the employee email address beyond the existing user account's ability to authenticate through current application behavior.

The employee flow must:

- send no verification email;
- send no approval email;
- require no email input;
- leave `email` unchanged;
- leave `email_verified_at` unchanged; and
- use in-application status feedback.

The external Affiliate flow continues to create `PENDING_EMAIL`, send signed verification links, require verification before `PENDING_APPROVAL`, and enforce verified-email portal access.

## 13. Persistence and Migration

Add a unique index on `affiliate_profiles.user_id` with an explicit, portable index name. This makes the existing singular model relationship a database invariant.

Before adding the index, the migration must query for duplicate `user_id` groups. If any duplicates exist, deployment stops with a clear technical error. The migration must not select a winner, merge financial records, or delete data automatically. Duplicate remediation requires a separately reviewed data-repair procedure because profiles may own attribution, commission, wallet, payout, withdrawal, and audit records.

Local inspection found no duplicate groups on 2026-08-11. Production remains unknown and requires preflight evidence before migration.

The employee request transaction relies on both row locking and the unique index. If concurrent first requests race at profile insertion, the losing request resolves the already-created profile and returns its state. It must not expose a database exception to the employee or emit a duplicate success audit event.

No new profile source column is added in this MVP. Enrollment behavior is determined by authenticated internal-role eligibility, profile state, and existing external account classification. The employee-specific audit action preserves request provenance for operational investigation.

## 14. Lifecycle and Administration

New employee profiles enter `PENDING_APPROVAL`. They appear in the existing paginated Affiliate administration query because that query reads Affiliate profiles rather than `users.affiliate_request`.

The existing approval, suspension, reactivation, and deactivation transitions remain authoritative:

- `PENDING_APPROVAL` to `ACTIVE`
- `ACTIVE` to `SUSPENDED`
- `SUSPENDED` to `ACTIVE`
- `ACTIVE` or `SUSPENDED` to `DEACTIVATED`

Approving an employee profile does not assign `Affiliate-user`. Suspending or deactivating an employee profile does not disable the user or remove staff roles. Existing commission and audit history is preserved.

## 15. Failure Handling

- Ineligible requests return a localized `403` response.
- Disabled employee enrollment returns a localized service-unavailable response without mutation.
- Repeated requests return the existing state safely.
- Validation and domain errors contain actionable user-facing text without model names, SQL, stack traces, or internal identifiers.
- Unexpected persistence failures roll back the complete transaction and return generic corrective guidance.
- Technical logs include correlation ID, operation, outcome, and authorized user/profile identifiers. They exclude passwords, email contents, referral links, session values, and secrets.
- Audit writes occur in the same transaction as the state change so an enrollment state cannot commit without its required audit record.

## 16. Security

- Authentication and the server-side session establish the employee identity.
- Administrator approval remains mandatory and is protected by the existing `affiliate-approve` permission.
- Controllers never accept caller-selected user or profile ownership identifiers for employee enrollment.
- Eligibility is rechecked inside the mutation service to prevent view or route bypass.
- CSRF and a dedicated rate limiter protect request submission.
- Database uniqueness and transaction locking protect concurrent enrollment.
- Blade output remains escaped and status feedback uses translations.
- Employee email bypass is limited to allowlisted internal roles. It does not weaken external Affiliate verification.
- Existing payout-account password confirmation, withdrawal ownership, and financial authorization remain unchanged.

## 17. Configuration and Rollout

Add:

- `affiliate.features.employee_enrollment`, controlled by `AFFILIATE_FEATURE_EMPLOYEE_ENROLLMENT`;
- `affiliate.employee_roles`, containing the approved role allowlist; and
- `affiliate.abuse.employee_enrollment_attempts_per_minute` for the dedicated rate limiter.

The feature is disabled by default. Deployment order is:

1. inspect production for duplicate profile ownership;
2. resolve any duplicates through a separately approved repair if required;
3. deploy the unique-index migration and application code with the feature disabled;
4. run focused registration, employee enrollment, authorization, lifecycle, and financial regression tests in staging;
5. enable employee enrollment; and
6. monitor enrollment failures, authorization denials, and duplicate-key conflicts.

Disabling the feature flag removes the employee request entry point and blocks new employee requests without deleting profiles or affecting existing active Affiliate and financial records.

## 18. Testing Strategy

### 18.1 Eligibility tests

- Every configured internal role is eligible.
- `User`, Affiliate-only, unknown-role, and roleless accounts are ineligible.
- A legacy multi-role account with an internal role is treated as an employee.
- Role configuration is the single eligibility source.

### 18.2 Enrollment feature tests

- An authenticated eligible employee with null `email_verified_at` can submit a request.
- A request creates exactly one `PENDING_APPROVAL` profile.
- No verification notification is sent.
- Email and `email_verified_at` remain unchanged.
- Roles, department, position, password, phone, `affiliate_request`, and disable state remain unchanged.
- Existing `PENDING_EMAIL` is normalized to `PENDING_APPROVAL` for an eligible employee.
- Repeated and concurrent requests return one profile and one creation audit event.
- Existing active, suspended, and deactivated profiles are not recreated or reset.
- Ineligible and unauthenticated requests create no profile.
- Disabled feature and Affiliate maintenance conditions create no profile.

### 18.3 Portal authorization tests

- An active employee with null `email_verified_at` can access every member-facing Affiliate page and mutation allowed by existing ownership rules.
- An external Affiliate with null `email_verified_at` remains blocked.
- An active verified external Affiliate retains access.
- Pending, suspended, and deactivated profiles cannot access active portal behavior.
- Employee login and root navigation continue to the staff system.
- Affiliate-only external login retains current Affiliate redirect behavior.
- Navigation visibility matches middleware authorization.

### 18.4 Persistence tests

- `affiliate_profiles.user_id` rejects a second profile.
- The migration aborts without deleting data when representative duplicates exist.
- Referral-code uniqueness remains enforced.
- A pre-existing non-empty `users.referral` value is not overwritten.

### 18.5 Regression tests

- Public Affiliate registration still requires unique email and phone values.
- External registration still creates `PENDING_EMAIL` and sends verification.
- Guest signed verification still moves an external profile to `PENDING_APPROVAL`.
- Existing administrator lifecycle transitions still work for employee and external profiles.
- Suspension and deactivation do not disable employee staff access.
- Existing attribution, commission, wallet, payout-account, withdrawal, analytics, and media-library suites remain green.

## 19. Acceptance Criteria

The feature is accepted when all of the following are true:

- an eligible employee requests Affiliate access using the existing authenticated account;
- no duplicate user account, password, email, phone, or Affiliate profile is created;
- the employee request reaches `PENDING_APPROVAL` without email verification or email delivery;
- administrator approval activates the same Affiliate profile without adding `Affiliate-user`;
- the employee continues to land in and use the staff system;
- an active employee can open the Affiliate portal despite a null `email_verified_at` value;
- an unverified external Affiliate remains blocked from active portal access;
- employee staff roles and staff account fields remain unchanged;
- Affiliate suspension and deactivation do not disable staff access;
- repeated and concurrent enrollment attempts are idempotent;
- production duplicate ownership is detected before the unique index is applied;
- the external Affiliate registration and verification contract is preserved; and
- focused and full Affiliate regression tests pass.
