# Affiliate Configuration UI Design

**Date:** 2026-08-18

**Design approval:** Approved by the user on 2026-08-18

**Specification approval:** Approved by the user on 2026-08-18

**Implementation status:** Not started

## 1. Purpose

Provide a protected administration page for the current Affiliate finance domain so authorized administrators can change business settings without editing `.env` files or running deployment commands. The page will use the existing database-backed Affiliate configuration and the existing `affiliate-config-edit` permission.

The page is for the current Affiliate domain under `app/Domain/Affiliate`. The legacy Affiliate module and its separate configuration keys remain outside this change.

## 2. Verified Current State

Repository inspection on 2026-08-18 established the following:

- `config/affiliate.php` contains feature, security, locale, and rate-limit configuration, but the core financial settings are read from the `affiliate_configurations` table.
- `AffiliateConfigurationService` reads and validates these core settings: `commission_rate_basis_points`, `commission_holding_days`, `attribution_duration_days`, `minimum_withdrawal_minor`, and `withdrawal_approval_required`.
- The existing finance API is available at `/admin/affiliate-finance/configuration` and is protected by `affiliate-config-edit`, but it does not render an HTML settings page.
- The `affiliate-config-edit` permission is created by the existing Affiliate configuration migration and is granted to the `Admin` and `SystemAdmin` roles when those roles exist.
- Commission rate is snapshot into an order when Affiliate attribution is persisted. Changing the setting therefore affects new order snapshots only.
- Commission holding days is used to calculate `release_at` when a new commission is created. Existing commissions retain their persisted release time.
- Attribution duration is used when new referral cookies and attributions are captured.
- Minimum withdrawal is checked when a new withdrawal request is created.
- `withdrawal_approval_required` is currently read for public policy presentation, while the withdrawal workflow still requires the `REQUESTED` to `APPROVED` to `PAID` transitions.
- A legacy Affiliate module also reads the same table with different keys such as `commission_amount` and `min_withdraw`. That module is explicitly out of scope for this specification.

Local repository evidence does not establish production role assignments, production configuration values, or which legacy Affiliate routes are enabled in every deployment.

## 3. Approved Product Decisions

1. Core Affiliate settings are managed through a protected UI backed by the database.
2. The existing `affiliate-config-edit` permission is reused; no new permission is introduced.
3. The UI covers the current Affiliate domain settings for commission rate, commission holding days, attribution duration, and minimum withdrawal.
4. Withdrawal approval remains mandatory and is not editable through the UI.
5. The current JSON configuration API remains available and continues to use the same service and validation path.
6. The legacy Affiliate module is not migrated, aliased, or changed in this MVP.
7. Existing orders, attributions, commissions, and withdrawal requests are not recalculated when a setting changes.
8. No new Affiliate business settings are added to `.env`.

## 4. Goals

- Give authorized administrators a clear page for the four mutable core settings.
- Enforce authorization on the page, the update endpoint, and the request object.
- Display business-friendly units while storing the existing exact integer representations.
- Reuse one domain service for reads, validation, persistence, and cache invalidation behavior.
- Keep updates transactional and auditable.
- Preserve the existing JSON API contract and current Affiliate runtime behavior.
- Make the effect of a changed value clear for settings that are snapshotted or persisted into future records.

## 5. Non-Goals

- Adding core financial settings to `.env`.
- Replacing the `affiliate_configurations` table.
- Editing feature flags, visitor hash keys, destination hosts, maintenance mode, or rate limits from this page.
- Changing the actual withdrawal approval workflow.
- Changing the legacy Affiliate module, its routes, its `affiliateConfig()` helper, or its old setting names.
- Recalculating historical orders, attributions, commissions, release dates, wallets, or withdrawal requests.
- Adding multi-tenant configuration semantics that are not present in the current domain service.

## 6. Approaches Considered

### 6.1 Environment-only configuration

This would make deployment configuration easy to version, but it would require application restarts or configuration-cache refreshes, provide no business-user editing flow, and create a second source of truth beside the existing database settings. It is rejected for this change.

### 6.2 Database UI over the current domain service

This reuses the existing table, service validation, API, permission, and audit path. It provides immediate operational control while keeping the source of truth in one place for the current Affiliate domain. This is the approved approach.

### 6.3 One page that maps both current and legacy Affiliate settings

The two systems use different names, units, and commission semantics. The legacy system also supports a `Percentage` versus `Flat` commission type while the current domain stores basis points. Automatic mapping could silently change financial behavior. This approach is deferred until a separate migration and compatibility design is approved.

## 7. Settings Contract

| UI field | UI representation | Stored setting | Validation | Effect boundary |
| --- | --- | --- | --- | --- |
| Commission rate | Percentage from 0 to 100 with at most two decimal places | `commission_rate_basis_points` | 0–10,000 basis points | New order attribution snapshots |
| Commission holding days | Integer days | `commission_holding_days` | 0–3,650 | New commissions and their `release_at` values |
| Attribution duration | Integer days | `attribution_duration_days` | 1–3,650 | New referral cookies and attributions |
| Minimum withdrawal | Baht amount with at most two decimal places | `minimum_withdrawal_minor` | At least 0.01 baht | New withdrawal requests |
| Withdrawal approval | Fixed mandatory policy; no editable field | `withdrawal_approval_required` | Must remain true | Existing approval workflow and policy text |

The commission percentage and minimum withdrawal amount must be converted using exact fixed-point logic. Floating-point arithmetic must not be used for the conversion. For example, 15% becomes 1,500 basis points and 10.00 baht becomes 1,000 minor units.

## 8. HTTP and View Interfaces

Keep the existing JSON endpoints unchanged:

| Method | Path | Route name | Purpose |
| --- | --- | --- | --- |
| `GET` | `/admin/affiliate-finance/configuration` | `affiliate.finance.configuration` | Return configuration data for existing API consumers |
| `PUT` | `/admin/affiliate-finance/configuration` | `affiliate.finance.configuration.update` | Update validated configuration data for existing API consumers |

Add a separate HTML page so the JSON contract is not changed:

| Method | Path | Route name | Purpose |
| --- | --- | --- | --- |
| `GET` | `/admin/affiliate-finance/configuration/edit` | `affiliate.finance.configuration.edit` | Render the settings form |
| `POST` | `/admin/affiliate-finance/configuration/edit` | `affiliate.finance.configuration.page.update` | Validate, persist, audit, and redirect back to the form |

All four routes use authentication, Affiliate correlation context, the Affiliate administration feature gate, and `permission:affiliate-config-edit`. The page update uses CSRF protection through the web middleware and follows Post/Redirect/Get after a successful update.

The HTML page uses a dedicated `AffiliateConfigurationPageController` so page rendering and redirect behavior do not get mixed into the existing finance JSON controller. It uses a dedicated page update request for business-friendly input names and exact unit conversion. Both the page controller and the JSON controller delegate persistence to `AffiliateConfigurationService`.

## 9. Application Responsibilities

### 9.1 `AffiliateConfigurationService`

The service remains the single domain boundary for configuration. It will expose a typed current-settings representation for the page and will continue to:

- reject missing, duplicated, or null settings;
- validate numeric ranges and boolean invariants;
- update only approved setting keys;
- update all requested values in one transaction; and
- clear its resolved-value cache after a successful update.

The service will treat withdrawal approval as a mandatory invariant. The UI will not send that setting, and any attempted domain or API update that would set it to false will be rejected. An unexpected stored false value must fail closed rather than claim that approval is optional.

### 9.2 Page controller and request

The page controller will:

- request typed settings from the domain service;
- render the Affiliate finance settings view;
- pass only display-safe values to the view;
- delegate normalized values to the existing update service; and
- return localized success or validation feedback.

The page request will authorize the current user with `affiliate-config-edit`, validate all four mutable fields, normalize the percentage and baht amount using exact fixed-point rules, and reject unknown fields. Authorization is repeated in route middleware and the request object for defense in depth.

### 9.3 Audit

Successful changes will record the actor, correlation ID, operation, changed setting names, prior values, resulting values, and outcome. No passwords, tokens, visitor hash keys, or unrelated configuration values are included. A failed validation or failed transaction must not create a successful configuration audit entry.

## 10. User Interface

The page will use the existing application layout and Affiliate finance visual conventions. It will include:

- a clear page title and explanation that values affect future Affiliate transactions according to the effect boundary;
- labeled inputs with units and allowed ranges;
- a commission rate field displayed as a percentage rather than basis points;
- a minimum withdrawal field displayed in baht rather than minor units;
- a read-only notice that withdrawal approval is mandatory;
- inline validation messages associated with each input;
- a success message after a completed update; and
- a visible link or menu entry only for users who can edit the settings.

English and Thai translation keys will be added consistently to the existing Affiliate locale files. The page will use semantic labels, keyboard-accessible controls, visible focus states, and non-color error feedback.

The Admin menu will show the settings entry only when the authenticated user has `affiliate-config-edit`. A user without the permission must not receive a usable settings link, but server-side authorization remains authoritative.

## 11. Security and Failure Handling

- A user without `affiliate-config-edit` receives `403 Forbidden` for both page access and updates.
- A user with permission but without the Affiliate administration feature receives the existing feature-gate response.
- CSRF protection applies to the HTML update.
- The server does not trust displayed values or hidden fields; every value is validated server-side.
- Invalid percentages, amounts, days, unknown fields, and attempts to disable approval are rejected without partial persistence.
- Missing, duplicated, or invalid database settings fail closed through the existing Affiliate configuration exception contract.
- Maintenance mode preserves the current write-blocking behavior unless a later design explicitly changes it.
- Technical logs include the operation and correlation ID but no secrets or full request payloads.
- All settings changes are transactionally consistent with the corresponding audit record.

## 12. Testing Strategy

Add or extend tests for the following:

### 12.1 Service and conversion tests

- Read the current typed values from the database.
- Convert `15`, `15.5`, and `10.00` using exact fixed-point rules.
- Reject negative, excessive, malformed, and over-precision values.
- Preserve valid zero for holding days and a valid false-like input only where the domain permits it.
- Reject any attempt to make withdrawal approval optional.
- Preserve duplicate and missing-setting fail-closed behavior.

### 12.2 Authorization and page tests

- A user without `affiliate-config-edit` cannot load or update the page.
- An authorized administrator can render the current values.
- A valid update persists all four values atomically and creates an audit record.
- A validation failure leaves every setting unchanged.
- The page does not render an editable approval switch.
- The settings menu entry is visible only to a permitted user.

### 12.3 Integration and regression tests

- The public Affiliate policy renders the updated commission, holding, attribution, and minimum-withdrawal values.
- New order attribution uses the updated commission rate.
- New commissions use the updated holding period.
- New withdrawal requests use the updated minimum.
- Existing snapshots and persisted release dates remain unchanged.
- The existing JSON configuration endpoints continue to enforce permission and validation.
- Existing Affiliate feature, finance, withdrawal, landing-page, preflight, and governance tests remain green.

## 13. Implementation Scope

Expected implementation files include:

- `app/Domain/Affiliate/Services/AffiliateConfigurationService.php`
- `app/Http/Controllers/Affiliate/AffiliateConfigurationPageController.php`
- `app/Http/Requests/Affiliate/UpdateAffiliateConfigurationPageRequest.php`
- `app/Http/Controllers/Affiliate/AffiliateFinanceAdminController.php` where shared response or invariant handling is required
- `routes/affiliate.php`
- `resources/views/affiliate/finance/configuration.blade.php`
- `resources/views/layouts/menu.blade.php`
- `lang/en/affiliate.php`
- `lang/th/affiliate.php`
- `tests/Unit/Affiliate/AffiliateConfigurationServiceTest.php`
- `tests/Feature/Affiliate/AffiliateConfigurationPageTest.php`

No database migration is expected because the required permission and canonical configuration rows already exist in the repository migrations. No `.env` change is expected for the four business settings.

## 14. Acceptance Criteria

The feature is accepted when:

- an authorized Admin or SystemAdmin can open the Affiliate settings page;
- an unauthorized user receives `403` and cannot update settings;
- the four mutable settings are displayed in business units and stored in the existing exact representations;
- invalid input cannot partially update the configuration;
- successful changes are recorded in the Affiliate audit log;
- withdrawal approval remains mandatory and cannot be disabled from the page or update API;
- updated values affect only the future transactions described by the effect boundary;
- the existing JSON configuration API remains available;
- the legacy Affiliate module remains behaviorally unchanged; and
- the focused and relevant Affiliate test suites pass.
