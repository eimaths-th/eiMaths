# Affiliate Configuration UI Implementation Plan

> **REQUIRED EXECUTION SKILL:** Use `executing-plans` for task-by-task execution.

- **Goal:** Provide an authorized HTML administration page for the current Affiliate domain that edits commission rate, commission holding days, attribution duration, and minimum withdrawal in business-friendly units, while preserving the existing JSON API and Affiliate runtime boundaries.
- **Architecture:** Keep `affiliate_configurations` as the single source of truth. Add a page request/controller/view adapter around `AffiliateConfigurationService`; keep persistence, validation, cache invalidation, and audit consistency in the domain service; keep the existing JSON controller and route contract; leave the legacy Affiliate module untouched.
- **Tech Stack:** Laravel 10, PHP, Blade, Laravel FormRequest validation, Spatie permission middleware, Eloquent, database transactions, PHPUnit 10.
- **Source:** `docs/specs/2026-08-18-affiliate-configuration-ui-design.md` (approved by the user on 2026-08-18)

## Global Constraints

- Implement only the current Affiliate domain under `app/Domain/Affiliate`; do not modify the legacy `public/Modules/Affiliate` configuration path or its `affiliateConfig()` helper.
- Reuse the existing `affiliate-config-edit` permission on both page routes, the page request, and the Admin menu. Do not introduce another permission.
- Expose only four editable UI settings: `commission_rate_percent`, `commission_holding_days`, `attribution_duration_days`, and `minimum_withdrawal_baht`.
- Keep `withdrawal_approval_required` mandatory. Do not render an editable switch; the domain and JSON API must reject any update that would set it false, and a stored false value must fail closed.
- Use exact integer conversion only: percentage to basis points and baht to minor units must not use floating-point arithmetic.
- Preserve the existing JSON routes and response shape: `GET` and `PUT` `/admin/affiliate-finance/configuration` keep their current route names and raw configuration response.
- Add the approved HTML routes: `GET` and `POST` `/admin/affiliate-finance/configuration/edit` with route names `affiliate.finance.configuration.edit` and `affiliate.finance.configuration.page.update`.
- Keep updates transactional and write the successful configuration audit record inside the same transaction as the setting updates.
- Do not add core Affiliate business settings to `.env`, do not add a migration, and do not recalculate historical orders, attributions, commissions, release dates, wallets, or withdrawal requests.
- Use English for repository documents and Thai/English translation keys consistently in `lang/th/affiliate.php` and `lang/en/affiliate.php`.
- Preserve unrelated working-tree changes. Use `--do-not-cache-result` for PHPUnit commands because the repository's PHPUnit result-cache file is not writable in this workspace.

## Task 1: Enforce the typed configuration and atomic audit domain contract

**Files:**

- Modify: `app/Domain/Affiliate/Services/AffiliateConfigurationService.php` at `CommissionRateBasisPoints`, `IsWithdrawalApprovalRequired`, `UpdateApprovedSettings`, `Setting`, and `ValidateSettingValue`.
- Modify: `app/Http/Requests/Affiliate/AffiliateConfigurationUpdateRequest.php` at `rules` for the mandatory approval invariant.
- Modify: `app/Http/Controllers/Affiliate/AffiliateFinanceAdminController.php` at `UpdateConfiguration` and its constructor dependencies.
- Test: `tests/Unit/Affiliate/AffiliateConfigurationServiceTest.php`.

- **Consumes:** The existing five database-backed configuration keys, the existing `AffiliateAuditService::Record` signature, the existing `AffiliateDomainException::ConfigurationInvalid` contract, and the current JSON configuration controller.
- **Produces:** `AffiliateConfigurationService::CurrentSettings(): array` with canonical typed values for all five keys; transactional updates that audit changed keys with prior and resulting canonical stored values; rejection of optional withdrawal approval.
- **Checkpoint:** Stop after the focused unit suite is green and verify that the existing JSON configuration route names and raw response path remain unchanged before starting page work.

**Actions:**

**For production behavior changes:**

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Add failing unit tests before production edits. Cover: the typed current-settings snapshot; a successful update that records `configuration.update` with the actor, correlation ID, prior values, resulting values, and only changed setting names; an audit-write exception that rolls every setting update back; a false stored approval value that raises `AffiliateDomainException::ConfigurationInvalid`; and a direct update attempt with `withdrawal_approval_required` set to false that leaves the database unchanged.
- [ ] Run `php artisan test --do-not-cache-result --filter=AffiliateConfigurationServiceTest`; expect the new tests to fail because `CurrentSettings` and the mandatory approval/atomic-audit behavior are not implemented yet, while the pre-existing configuration tests continue to identify the baseline behavior.
- [ ] Add constructor injection for `AffiliateAuditService` and implement `CurrentSettings` by composing the existing typed accessors. Make `IsWithdrawalApprovalRequired` accept only true-like values and raise the existing configuration exception for false-like, malformed, missing, duplicated, or null values.
- [ ] Refactor `UpdateApprovedSettings` so validation, exact-row lookup, prior-state capture, normalized persistence, and `AffiliateAuditService::Record('configuration.update', 'affiliate_configurations', null, ...)` all execute within one `DB::transaction`. Store canonical string values in the audit states (`'1'` for mandatory approval and decimal integer strings for the other keys), clear `resolved_values` only after the transaction succeeds, and let any audit failure roll back the setting changes.
- [ ] Keep the approved key allow-list and numeric boundaries: commission rate `0..10000` basis points, holding days `0..3650`, attribution duration `1..3650`, and minimum withdrawal at least `1` minor unit. Preserve exact-one-row fail-closed behavior and reject unknown keys.
- [ ] Narrow the existing JSON request's `withdrawal_approval_required` validation to accept only an enabled/true value, while retaining the service invariant so callers that bypass the request cannot disable approval. Remove the controller's post-transaction duplicate audit call and its now-unused audit dependency; the JSON response must still come from `Configuration()` with the same fields and ordering.
- [ ] Run `php artisan test --do-not-cache-result --filter=AffiliateConfigurationServiceTest`; expect exit code 0, including the rollback and audit assertions. Run `php artisan route:list --name=affiliate.finance.configuration`; expect the existing `affiliate.finance.configuration` and `affiliate.finance.configuration.update` routes to remain present.
- [ ] Refactor only after the behavior is green. Re-run the focused unit suite and `php -l app/Domain/Affiliate/Services/AffiliateConfigurationService.php` with exit code 0.

## Task 2: Add the protected Affiliate configuration page and exact unit conversion

**Files:**

- Create: `app/Http/Controllers/Affiliate/AffiliateConfigurationPageController.php` with `Edit` and `Update` actions.
- Create: `app/Http/Requests/Affiliate/UpdateAffiliateConfigurationPageRequest.php` with request authorization, UI validation, unknown-field rejection, and `ApprovedSettings` normalization.
- Create: `resources/views/affiliate/finance/configuration.blade.php`.
- Modify: `routes/affiliate.php` inside the existing `admin/affiliate-finance` middleware group.
- Modify: `lang/en/affiliate.php` with the configuration page labels, help text, validation messages, status text, and mandatory-approval notice.
- Modify: `lang/th/affiliate.php` with the matching Thai keys and structure.
- Test: `tests/Feature/Affiliate/AffiliateConfigurationPageTest.php`.

- **Consumes:** `AffiliateConfigurationService::CurrentSettings`, `AffiliateConfigurationService::UpdateApprovedSettings`, `Money::FromDecimal`, the existing Affiliate correlation request attribute, and the existing `AffiliateDomainException` renderable that redirects non-JSON failures back with localized errors.
- **Produces:** A GET form with business-unit values and a CSRF-protected POST update that redirects to the form after success; the request produces only canonical internal keys for the four mutable settings.
- **Checkpoint:** Stop after the page feature suite is green and inspect the rendered form to confirm that no `withdrawal_approval_required` input or approval toggle is present.

**Actions:**

**For production behavior changes:**

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Add failing feature tests before implementing the page. Cover: a user without `affiliate-config-edit` receiving `403` for both GET and POST; an authorized administrator seeing the current values in percentage, days, and baht units; a valid POST persisting `17.5`, `0`, `90`, and `12.34` as `1750`, `0`, `90`, and `1234`; invalid/over-precision/out-of-range/unknown fields leaving every setting unchanged; the success message and audit record; and the absence of an editable approval field.
- [ ] Run `php artisan test --do-not-cache-result --filter=AffiliateConfigurationPageTest`; expect a route/controller or view failure because the approved HTML routes and page implementation do not yet exist.
- [ ] Implement `UpdateAffiliateConfigurationPageRequest::authorize` with `user()?->can('affiliate-config-edit') ?? false`. Validate `commission_rate_percent` as a decimal string from `0` through `100` with at most two fractional digits, holding days as an integer `0..3650`, attribution duration as an integer `1..3650`, and `minimum_withdrawal_baht` as a non-negative decimal string with at most two fractional digits that converts to at least one minor unit. Reject every unknown request field except the CSRF token, and provide localized field names/messages through the Affiliate translation namespace.
- [ ] Implement exact normalization in `ApprovedSettings`: parse percentage digits as `whole * 100 + two_digit_fraction` basis points without a float; pass the baht string to `Money::FromDecimal($amount, true)` and reject a result below one minor unit; return only `commission_rate_basis_points`, `commission_holding_days`, `attribution_duration_days`, and `minimum_withdrawal_minor`. Do not accept or synthesize `withdrawal_approval_required` from the page form.
- [ ] Implement `AffiliateConfigurationPageController::Edit` using `CurrentSettings`, formatting basis points with integer division/modulo and formatting minor units with `Money::FromMinor(...)->Decimal()`. Pass display-safe values and the fixed mandatory-approval state to the view. Implement `Update` by calling `ApprovedSettings`, the existing actor, and the request correlation ID, then redirecting to `affiliate.finance.configuration.edit` with the localized success flash message. Allow the verified Affiliate domain exception handler to provide localized back-with-errors behavior for domain failures.
- [ ] Add the two HTML routes inside the existing `auth`, `affiliate.correlation`, `affiliate.feature:administration`, and `permission:affiliate-config-edit` route group. Keep the existing JSON GET/PUT routes unchanged and use POST plus CSRF for the page update.
- [ ] Build the Blade view with the existing `layouts.app` and Affiliate finance conventions. Include a page title, effect-boundary explanation, labeled inputs with allowed ranges and units, `@csrf`, `old()` values, field-associated `@error` output, a session success message, a keyboard-accessible submit control, and a read-only notice that withdrawal approval is mandatory. Do not include a hidden or visible approval input.
- [ ] Add matching English and Thai translation keys under a new `configuration` section without changing unrelated Affiliate locale keys.
- [ ] Run `php artisan test --do-not-cache-result --filter=AffiliateConfigurationPageTest`; expect exit code 0 with authorization, conversion, validation, persistence, audit, redirect, and no-toggle assertions. Run `php -l app/Http/Controllers/Affiliate/AffiliateConfigurationPageController.php`, `php -l app/Http/Requests/Affiliate/UpdateAffiliateConfigurationPageRequest.php`, and `git diff --check`; expect exit code 0.
- [ ] Refactor only after the page behavior is green. Re-run the page suite and verify the route list contains `affiliate.finance.configuration.edit` and `affiliate.finance.configuration.page.update` in addition to the two preserved JSON routes.

## Task 3: Expose the settings entry in the Admin menu and complete regression verification

**Files:**

- Modify: `resources/views/layouts/menu.blade.php` after the existing Affiliate administration entries, guarded by `@can('affiliate-config-edit')`.
- Modify: `tests/Feature/Affiliate/AffiliateConfigurationPageTest.php` with menu visibility assertions using an existing application-layout administration page.
- Test: `tests/Feature/Affiliate/AffiliateLandingPageTest.php`.
- Test: `tests/Unit/Affiliate` and `tests/Feature/Affiliate` relevant Affiliate suites.

- **Consumes:** The `affiliate.finance.configuration.edit` route, the `affiliate-config-edit` permission, the existing AdminLTE application layout/sidebar, and the existing database-backed consumer tests for public Affiliate policy values.
- **Produces:** A permission-gated Admin menu link to the settings page and evidence that the new settings page does not regress current Affiliate consumers or finance behavior.
- **Checkpoint:** Stop and report any unrelated pre-existing failure separately; do not broaden the implementation to the legacy Affiliate module or unrelated menu permissions.

**Actions:**

**For production behavior changes:**

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Add failing menu assertions to the page feature suite. Use the existing `affiliate-content-manage.index` application-layout route as the render boundary: an authenticated user with an unrelated administration permission but without `affiliate-config-edit` must not receive the settings URL, while a user with `affiliate-config-edit` must receive the localized settings link and the active route state.
- [ ] Run `php artisan test --do-not-cache-result --filter=AffiliateConfigurationPageTest`; expect the new menu assertions to fail because the sidebar has no configuration entry yet.
- [ ] Add a standalone menu item guarded by `@can('affiliate-config-edit')`, linking to `route('affiliate.finance.configuration.edit')`, using the new localized label, and marking the link active for `affiliate.finance.configuration.*`. Keep it independent of Affiliate portal access so the permission alone controls visibility, while server-side route middleware remains authoritative.
- [ ] Run `php artisan test --do-not-cache-result --filter=AffiliateConfigurationPageTest`; expect exit code 0 and no settings link for users without the permission.
- [ ] Run `php artisan test --do-not-cache-result --filter=AffiliateConfigurationServiceTest`; expect exit code 0 for typed reads, mandatory approval, atomic audit, and rollback behavior.
- [ ] Run `php artisan test --do-not-cache-result --filter=AffiliateLandingPageTest`; expect exit code 0 and continued rendering of the current commission, holding, attribution, and minimum-withdrawal policy values from the database.
- [ ] Run `php artisan test --do-not-cache-result tests/Unit/Affiliate tests/Feature/Affiliate`; expect exit code 0 for the relevant Affiliate unit and feature suites, including existing finance, withdrawal, landing-page, preflight, and governance coverage. If a failure predates this change, record the exact test and evidence rather than changing unrelated code.
- [ ] Run `php artisan route:list --name=affiliate.finance.configuration`; expect four configuration routes: the two existing JSON routes plus `affiliate.finance.configuration.edit` and `affiliate.finance.configuration.page.update`, all under the approved administration and permission middleware.
- [ ] Run `php -l` on every modified PHP source/request/controller file and `git diff --check`; expect exit code 0. Inspect the final diff to confirm there is no `.env` change, no migration, no legacy-module change, no editable approval toggle, and no unrelated file modification.
