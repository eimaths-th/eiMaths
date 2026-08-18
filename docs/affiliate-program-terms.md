# eiMaths Affiliate Program Terms and Operating Conditions

> Status: repository-backed operational documentation
> Last reviewed: 2026-08-18
> Audience: Affiliate participants, support staff, finance staff, administrators, and deployment owners

This document consolidates the Affiliate program conditions currently expressed by the application. It is written from the implementation and the public Affiliate policy view; it is not a substitute for the live database configuration, a legal agreement, or a production readiness approval.

## 1. Scope and source of truth

The Affiliate domain is implemented under `app/Domain/Affiliate` and is configured through `config/affiliate.php`. The public policy is rendered by `resources/views/affiliate/index.blade.php`.

The following distinctions are important:

- **Implementation rule** means the application currently enforces the condition in code, validation, middleware, or a database constraint.
- **Configured default** means the value is supplied by a migration, configuration file, or `.env.example`; it may have been changed in the live database or environment.
- **Workspace observation** means a value read from the current local workspace. It is not evidence of the production value.
- **Open item** means the value or operational decision still needs to be confirmed before external publication or production launch.

Unless a section says otherwise, a participant must satisfy all applicable feature gates, authentication, profile-status, ownership, permission, validation, and maintenance-mode checks.

## 2. Program model

An Affiliate receives commission for eligible tuition sales that are attributed to the Affiliate and later confirmed as paid. Attribution is first-touch: the first valid Affiliate context remains the owner for its validity period. Commission is not created merely because a visitor clicks a link or an order is created.

The financial source of truth is the receipt-backed commission and wallet ledger. Clicks, visitor counts, and analytics are informational and do not create a payment obligation by themselves.

## 3. Eligibility and registration

### 3.1 External Affiliate registration

An external applicant must:

1. Provide a name, email address, Thai phone number, password, and password confirmation.
2. Use a unique email address and unique phone number.
3. Use a password of at least eight characters.
4. Verify the email address through the signed verification link.
5. Wait for the Affiliate team or administrator to approve the application.

The request validation currently requires a string name and phone number containing 10–15 digits. Email addresses are normalized to lowercase and trimmed before the user record is created. The registration creates a user, an Affiliate profile, a referral code, and the `Affiliate-user` role when that role exists.

Email verification does not make the account active. The normal lifecycle is:

`PENDING_EMAIL` → `PENDING_APPROVAL` → `ACTIVE`

In production, registration is rejected when mail delivery is unavailable or the mail driver is still configured as `log`. The application dispatches the verification notification after the registration transaction commits.

### 3.2 Employee enrollment

Employee enrollment is a separate flow for an existing staff account:

- No new user is created.
- The existing staff email, email verification state, and password are not changed.
- The account must have an allowed employee role. The role allowlist is case-sensitive and defaults to `Admin`, `SystemAdmin`, `BranchAdmin`, `Teacher`, and `blog` in `config/affiliate.php`.
- The employee must use an authenticated session.
- The `employee_enrollment` feature must be enabled and writes must be available.
- The resulting Affiliate profile still requires administrator approval.
- A repeated request is idempotent and concurrent requests are protected by profile locking and the unique user/profile constraint.

If an existing profile is `PENDING_EMAIL`, employee enrollment normalizes it to `PENDING_APPROVAL`. A non-empty existing referral code is preserved.

The employee enrollment feature is disabled when its environment flag is absent because Affiliate feature defaults fail closed. It must be explicitly enabled before this flow can be used.

### 3.3 Registration and abuse limits

The configured defaults are:

| Operation | Default limit | Key |
| --- | ---: | --- |
| External registration | 5 per minute per IP | `affiliate-registration` |
| Verification resend | 6 per minute | `affiliate-verification-resend` |
| Employee enrollment | 6 per minute per authenticated user | `affiliate-employee-enrollment` |
| Affiliate click tracking | 120 per minute per IP/user-agent hash | `affiliate-click` |

The exact effective limit is a deployment concern if the application configuration has been overridden.

## 4. Account lifecycle and access

| Status | Meaning | Allowed transitions exposed by the service |
| --- | --- | --- |
| `PENDING_EMAIL` | Registration exists but email verification is incomplete | Email verification → `PENDING_APPROVAL`; employee enrollment may normalize it |
| `PENDING_APPROVAL` | Email is verified or employee enrollment is complete; approval is pending | Approve → `ACTIVE` |
| `ACTIVE` | Eligible for Affiliate portal activity | Suspend → `SUSPENDED`; deactivate → `DEACTIVATED` |
| `SUSPENDED` | Temporarily blocked from new Affiliate activity | Reactivate → `ACTIVE`; deactivate → `DEACTIVATED` |
| `DEACTIVATED` | Closed Affiliate profile | No deactivated-to-active transition is exposed by the lifecycle service |

Suspension and deactivation require a non-empty reason in the normal administrative flow. Lifecycle changes are audited. A suspended or deactivated account cannot create new financial activity, while historical records remain available for audit and reconciliation.

Portal access requires an owned `ACTIVE` profile. Affiliate-only users also require verified email. Eligible employees may access the portal through their eligible staff role without a second email-verification requirement. Employee enrollment does not disable the staff account or alter its staff roles.

## 5. Referral attribution

### 5.1 Accepted referral entry points

The application supports:

- Direct referral links: `/r/{referral_code}`
- Campaign/media links: `/r/{referral_code}/{campaign_code}`
- Compatibility capture from a valid `?ref={referral_code}` query parameter on referral-enabled pages

Referral codes must match `[A-Za-z0-9-]{3,64}`. Campaign codes must use lowercase ASCII letters, digits, and hyphens.

Invalid referral input does not block the requested page and does not create an attribution or click event.

### 5.2 First-touch rules

The first valid referral context is stored in the first-party `eimaths_affiliate_referral` cookie. The default attribution duration is 180 days, but the effective duration is read from the Affiliate configuration. The cookie is HttpOnly and uses SameSite `Lax`.

The application applies these rules:

- The referral code must belong to an active Affiliate profile.
- A valid, unexpired existing context remains the owner.
- A later valid link does not replace an existing unexpired first-touch attribution.
- A later click may still be counted for click analytics even when it cannot replace order ownership.
- When an order is snapshotted, the attribution must still be valid and the Affiliate profile must still be active.
- Attribution is bound to the customer contact under lock; an existing unexpired contact attribution is not overwritten.

For an eligible order, the order stores the attribution identifier and the commission-rate snapshot. Later policy changes therefore apply to new orders only; they do not silently recalculate an existing order's rate.

## 6. Referral links and click tracking

### 6.1 Link validation

Direct links require an active Affiliate profile. Media links additionally require an enabled, non-archived media item whose publication window is active:

- `publish_start_at` must be at or before the current time.
- `publish_end_at`, when present, must be after the current time.
- The campaign code must match the media item.
- The destination must pass the destination validator.

Destinations may be relative paths beginning with `/`. Absolute destinations must use HTTPS, contain no credentials, and use a host in `AFFILIATE_ALLOWED_DESTINATION_HOSTS`. Control characters, backslashes, and protocol-relative `//` destinations are rejected.

An invalid or inactive target redirects to `/`. A valid target records a click on a fail-soft basis, sets the referral context, and redirects with HTTP 302.

### 6.2 Click classification and uniqueness

The click tracker excludes:

- Authenticated self-clicks by the Affiliate who owns the target.
- Known bot user-agent tokens configured by the application.

The visitor identity is an HMAC-SHA256 hash of the IP address and normalized user-agent, using `AFFILIATE_VISITOR_HASH_KEY`. Raw IP addresses and raw user-agent strings are not stored. The key must be at least 32 characters for click-tracking preflight to pass.

Unique visitors are counted once per Affiliate and visitor hash within a 24-hour window. The visitor window key is ULID-based. Raw click events are retained for 13 months; visitor windows not seen for 48 hours are pruned; daily metrics are retained permanently.

If click recording is unavailable, the public redirect and referral ownership path remain fail-soft where possible. This preserves the user journey but means click analytics may be incomplete.

## 7. Commission eligibility and calculation

### 7.1 Conditions for commission creation

Commission creation requires all of the following:

- The `commission_creation` feature is enabled.
- Affiliate writes are available and maintenance mode is not blocking writes.
- The order has a valid, still-eligible Affiliate attribution.
- The attribution belongs to the order's customer contact.
- Payment is confirmed by a receipt with status `1`, a non-empty receipt number, and a payment date; or by the supported equivalent confirmed order state with receipt number and payment date.
- The order has not already produced the same receipt/order commission.

Commission creation is idempotent and locks the receipt/order records before evaluating the financial conditions.

### 7.2 Eligible amount and formula

The calculator uses integer minor units and half-up rounding:

```text
gross eligible tuition = sum(order detail tuition prices)
eligible tuition = max(gross eligible tuition - order discount, 0)
commission = round_half_up(eligible tuition × rate_basis_points ÷ 10,000)
```

The calculation excludes books, fees, shipping, tax, and refunded tuition. Book prices (`bprice`) are not part of the eligible tuition source.

The migration default is `commission_rate_basis_points = 1500`, which equals 15%. The allowed configuration range is 0–10,000 basis points (0–100%). The live value must be read from the Affiliate settings before publishing a participant-facing rate.

### 7.3 Holding and release

The default holding period is 10 days after payment confirmation. A newly created commission is `PENDING` and receives a `release_at` timestamp. When the holding period matures and `commission_release` is enabled:

- A positive remaining commission becomes `AVAILABLE` and is appended to the Affiliate wallet.
- A zero or fully reversed commission becomes `REVERSED`.
- A commission remains auditable through its source items and ledger entries.

The scheduled release command runs every minute in the deployment runbook. Administrators may place a pending commission on hold with a reason and later release the hold back to `PENDING`.

Commission states are `PENDING`, `HELD`, `AVAILABLE`, and `REVERSED`.

## 8. Refunds, credit notes, and financial integrity

### 8.1 Confirmed external refunds

Confirmed refund recording requires:

- The `confirmed_refunds` feature to be enabled.
- A positive refund amount.
- A non-empty external reference, reason, and idempotency key.
- The refund to be tied to the receipt-backed commission.
- Cumulative refunded tuition and credit-note adjustments not to exceed eligible tuition.

The reversal uses the same rate snapshot as the original commission and is capped at the commission's remaining amount. If the commission is already available, the corresponding wallet amount is deducted. A fully reversed commission becomes `REVERSED`.

### 8.2 Credit notes and source locking

Credit-note adjustments apply to tuition only and cannot exceed the remaining eligible tuition. If the related commission is available, the adjustment creates a wallet reversal. Cancelling the credit note restores the exact prior adjustment impact.

Once a commission exists, the financial source guard protects the order and receipt fields used for commission calculation. The wallet ledger is append-only; corrective activity is represented by additional entries rather than editing history.

If refunds or reversals make an Affiliate wallet negative, new commissions first offset the negative balance. Withdrawals remain blocked until the wallet is positive and the minimum withdrawal threshold is met.

## 9. Wallet, payout accounts, and withdrawals

### 9.1 Wallet rules

Wallet balances are maintained through an append-only ledger with available and reserved amounts. A withdrawal reserves funds before approval. Reserved funds cannot be double-spent. In normal operation, available balance cannot become negative except for explicitly supported commission-release, reversal, credit-note, or adjustment paths.

### 9.2 Withdrawal request conditions

An Affiliate may request a withdrawal only when:

- `wallet_withdrawals` is enabled and writes are available.
- The owner has an active Affiliate profile.
- The amount is non-negative, has no more than two decimal places, and meets the configured minimum.
- The wallet is not negative and has enough available funds.
- The owner has an active payout account of the requested type.
- The payout-account cooldown has expired.
- The request key is new, or the existing request is the same idempotent request for the same profile.

The migration default is `minimum_withdrawal_minor = 1000`, displayed by the public policy as 10.00 baht. The effective minimum is a live Affiliate setting.

Creating a request snapshots the payout details in encrypted form and creates a `REQUESTED` withdrawal while reserving the amount.

### 9.3 Payout account requirements

Supported payout types are PromptPay and Thai bank accounts.

- PromptPay is normalized by removing spaces and hyphens and must contain 10 or 13 digits.
- Bank name and account name are required and limited to 255 characters.
- Bank account numbers are normalized by removing spaces and hyphens and must contain 6–20 digits.
- Each payout type is unique per Affiliate profile.
- Payout details are encrypted at rest and masked in normal displays.
- Changing payout details starts a 24-hour payout cooldown and sends a notification.
- The payout-account request includes `current_password`; changes to existing details are password-checked by the service.

### 9.4 Approval, payment, cancellation, and rejection

The service flow is:

`REQUESTED` → `APPROVED` → `PAID`

The following rules apply:

- Approval and payment are separate operations with idempotency keys.
- Payment requires a non-empty payment reference.
- The approval actor ordinarily cannot be the payment actor.
- Self-payment is allowed only with the explicit `affiliate-withdrawal-pay-self` permission; broad `Admin` and `SystemAdmin` roles alone do not grant this exception.
- The owner may cancel a request with a non-empty reason while it is `REQUESTED` or `APPROVED`.
- An administrator may reject a request with a non-empty reason.
- Cancellation or rejection releases the reserved amount through a ledger entry.
- Payment evidence is immutable after payment.

The migration/configuration value `withdrawal_approval_required` defaults to enabled and is displayed by the public policy. The current withdrawal service path still enforces `REQUESTED` → `APPROVED` → `PAID`; therefore, the setting must not be treated as a production bypass without verifying the target runtime behavior.

## 10. Analytics and media library

### 10.1 Analytics

Analytics requires an active Affiliate profile and the `analytics` feature. The supported range behavior is:

- Default range: the last 30 days, inclusive.
- Presets: 7, 30, or 90 days.
- Custom range: maximum 366 days, no future dates, and `from <= to`.
- Reporting timezone: `Asia/Bangkok`.

The dashboard can report total clicks, unique clicks, confirmed sales, eligible sales value, net income (`commission_minor - reversed_minor`), click-cohort conversion, daily series, the 25 most recent confirmed sales, and the top 10 campaigns.

Click-cohort conversion uses unique visitor windows and confirmed commissions. It does not turn a click into a payable commission. The UI verification notes specify 60-second polling for the live analytics view.

### 10.2 Media library

The media library requires an active Affiliate profile and the `media_library` feature. Only enabled, non-archived media items within their publication window are exposed to Affiliates. The library supports search and type filters and paginates at 24 items per page. Tracking URLs are generated by the server.

Content management requires the `affiliate-content-manage` permission and the `administration` feature. Content rules include:

- Title: required, maximum 255 characters.
- Description: maximum 1,000 characters.
- Type: `image` or `video`.
- File: maximum 50 MB (`51,200 KB`).
- Destination: maximum 2,048 characters and must pass the safe-destination validator.
- Campaign code: required, unique, maximum 96 characters, lowercase ASCII letters/digits/hyphens only.
- An end publication time must be after the start time when both are set.
- Supported image types: JPEG, PNG, GIF, and WebP.
- Supported video types: MP4, QuickTime/MOV, AVI, and WMV.
- Campaign code and uploaded file are immutable after creation. Destination can be updated.
- Archiving is soft deletion; historical references remain available for audit.

## 11. Administration and permissions

Administrative routes are protected by authentication, the `administration` feature where applicable, and explicit permissions. The principal permissions are:

| Permission | Purpose |
| --- | --- |
| `affiliate-list` | List Affiliate accounts |
| `affiliate-approve` | Approve pending Affiliates |
| `affiliate-suspend` | Suspend or manage lifecycle status |
| `affiliate-config-edit` | Edit Affiliate financial settings |
| `affiliate-commission-hold` | Hold or release pending commissions |
| `affiliate-refund-record` | Record confirmed external refunds |
| `affiliate-withdrawal-list` | View withdrawal requests |
| `affiliate-withdrawal-approve` | Approve withdrawals |
| `affiliate-withdrawal-pay` | Pay approved withdrawals |
| `affiliate-payout-details-view` | Reveal approved withdrawal payout details |
| `affiliate-withdrawal-pay-self` | Explicit self-payment exception |
| `affiliate-reconciliation` | Run or review wallet reconciliation |
| `affiliate-content-manage` | Create, update, and archive Affiliate media |

Feature gates fail closed when disabled. Administration, finance, payout, and content actions must not be exposed solely because a user has a broad role; the route permission must also be present.

## 12. Privacy and promotional conduct

The public Affiliate policy states that personal data is used for program administration and service improvement and is not disclosed without consent except where legally required.

Affiliate promotional activity must comply with applicable law and program rules. Deceptive claims, spam, and other methods that could mislead visitors or damage the service are not permitted. The Affiliate is responsible for its own promotional activity and resulting damage where applicable.

For click analytics, the implementation stores a keyed visitor hash rather than raw IP/user-agent values. The key is operationally sensitive and must be stored as an environment secret; it must not be committed or logged.

## 13. Required configuration and operations

### 13.1 Feature flags

The feature flags in `config/affiliate.php` fail closed and include:

The following is a safe baseline example. Keep employee enrollment disabled until the employee approval and support process is ready.

```dotenv
AFFILIATE_FEATURE_REFERRAL_CAPTURE=true
AFFILIATE_FEATURE_COMMISSION_CREATION=true
AFFILIATE_FEATURE_COMMISSION_RELEASE=true
AFFILIATE_FEATURE_CONFIRMED_REFUNDS=true
AFFILIATE_FEATURE_WALLET_WITHDRAWALS=true
AFFILIATE_FEATURE_ADMINISTRATION=true
AFFILIATE_FEATURE_CLICK_TRACKING=true
AFFILIATE_FEATURE_ANALYTICS=true
AFFILIATE_FEATURE_MEDIA_LIBRARY=true
AFFILIATE_FEATURE_EMPLOYEE_ENROLLMENT=false
```

The values above are a configuration example, not a requirement that every environment enable every capability. Enable a feature only after its dependencies, permissions, monitoring, and operational owner are ready.

### 13.2 Production preflight

Before enabling the program in production, verify all of the following:

- `APP_URL` is the intended public HTTPS origin.
- `AFFILIATE_VISITOR_HASH_KEY` is present, secret, and at least 32 characters long.
- `AFFILIATE_ALLOWED_DESTINATION_HOSTS` contains only approved hosts.
- Production mail delivery is configured and is not `log`.
- Queue, database, storage, and encryption configuration are production-ready.
- Live Affiliate settings have been read and approved: rate, holding days, attribution days, minimum withdrawal, and approval behavior.
- Admin permissions are assigned according to least privilege.
- Withdrawal payout-detail reveal is restricted and audited.
- The release, reconciliation, and retention jobs are scheduled and monitored.

The deployment runbook also provides an environment matrix, preflight command, permission review, and rollback notes: [`affiliate-deployment-runbook.md`](affiliate-deployment-runbook.md).

### 13.3 Scheduled jobs

The current deployment runbook requires these scheduled operations:

```text
* * * * *  affiliate:release-commissions
01:30      affiliate:reconcile-wallets
02:15      affiliate:prune-click-events
```

The server scheduler should invoke `php artisan schedule:run` every minute. The exact cron wrapper and deployment path are environment-specific and remain defined by the deployment runbook.

## 14. Current workspace observations

The following values were observed in the current local `.env` on 2026-08-18. They are development settings only:

- `APP_ENV=local` and `APP_URL=http://localhost:8000`.
- Referral capture, commission creation/release, confirmed refunds, wallet withdrawals, administration, click tracking, analytics, media library, and employee enrollment are enabled.
- `AFFILIATE_FEATURE_EMPLOYEE_ENROLLMENT=true`.
- `AFFILIATE_EMPLOYEE_ROLES=Admin,SystemAdmin,BranchAdmin,Teacher,Blog`. Role matching is case-sensitive, so `Blog` is a distinct configured value from the `blog` fallback in `config/affiliate.php`.
- `AFFILIATE_VISITOR_HASH_KEY` is set with an observed length of 64 characters, satisfying the 32-character minimum and the runbook's recommended 64-character hexadecimal format. The value is intentionally omitted; it must remain secret. This removes the missing-key blocker, but full preflight and production readiness still require verification.
- `AFFILIATE_ALLOWED_DESTINATION_HOSTS=localhost`.
- `AFFILIATE_DEFAULT_LOCALE=th`.
- `AFFILIATE_CLICK_ATTEMPTS_PER_MINUTE=120`.
- `AFFILIATE_MAINTENANCE_MODE=false`.
- `MAIL_MAILER=log` and `QUEUE_CONNECTION=sync`.

The current database settings were not verified from this workspace. The migration defaults listed in this document must therefore be treated as configured defaults, not as current production values.

## 15. Open items before external publication

The program owner should close these items before publishing participant-facing terms or enabling production payouts:

1. Read and approve the live database values for commission rate, holding period, attribution duration, minimum withdrawal, and approval behavior.
2. Keep `AFFILIATE_VISITOR_HASH_KEY` in a secure environment-specific secret store, and verify a length of at least 32 characters in every environment where click tracking is enabled.
3. Confirm production SMTP, queue workers, storage, encryption keys, database connectivity, and scheduler monitoring.
4. Confirm the approved destination-host allowlist and campaign/content moderation process.
5. Confirm the finance policy for refunds, credit notes, negative wallets, reconciliation, and payout evidence.
6. Confirm least-privilege role assignments for account, finance, content, payout-detail, and self-payment permissions.
7. If employee enrollment remains enabled, verify the exact case-sensitive role allowlist and an operational approval owner.
8. Run the deployment preflight and the focused Affiliate verification suites against the target release.

## 16. Repository references

- [`config/affiliate.php`](../config/affiliate.php) — feature flags, limits, locales, roles, visitor hashing, and destination hosts.
- [`app/Domain/Affiliate`](../app/Domain/Affiliate) — Affiliate services, policies, lifecycle, attribution, tracking, commission, wallet, payout, and analytics logic.
- [`routes/web2.php`](../routes/web2.php) — registration, verification, login, and referral-enabled web routes.
- [`routes/affiliate.php`](../routes/affiliate.php) — Affiliate portal, tracking, administration, finance, media, and withdrawal routes.
- [`resources/views/affiliate/index.blade.php`](../resources/views/affiliate/index.blade.php) — public Affiliate policy and dynamic financial values.
- [`docs/affiliate-deployment-runbook.md`](affiliate-deployment-runbook.md) — deployment environment, preflight, scheduler, security, and rollback guidance.
- [`docs/verification/2026-08-11-affiliate-analytics-media-library.md`](verification/2026-08-11-affiliate-analytics-media-library.md) — focused analytics/media verification record.
- [`docs/verification/2026-08-11-employee-affiliate-enrollment.md`](verification/2026-08-11-employee-affiliate-enrollment.md) — employee-enrollment verification record and readiness caveats.
