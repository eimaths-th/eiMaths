# TH/EN Backoffice Language System

Status: Approved in conversation on 2026-09-18; implementation completed in the current main worktree.

## 1. Purpose

Introduce a predictable Thai/English language preference for the authenticated staff interface. A user should be able to change the interface language from the shared top bar, keep that choice across requests and sessions, and see the same choice applied before the main layout is rendered.

This specification covers the first implementation slice only: the shared application shell, Dashboard, and Students Management screens. Other legacy screens may remain English or continue to use their existing translations until they are migrated.

## 2. Repository context and evidence

- 'config/app.php' currently uses 'en' as both the application locale and fallback locale.
- The 'users' table already has a nullable 'language_code' column with an English default, and 'App\Models\User' already allows 'language_code' to be assigned.
- 'SetAffiliateLocale' already applies a user/affiliate locale, but it is scoped to Affiliate routes and is not a general application locale middleware.
- The main 'layouts.app', header, and menu contain hardcoded English labels. The layout already emits 'app()->getLocale()' as the HTML language value, so resolving the locale before view rendering is sufficient to keep that attribute in sync.
- 'lang/en' and 'lang/th' contain feature-specific Affiliate and Credit Note files, but there is no complete core translation namespace for the shared shell, Dashboard, and Students Management.
- A legacy 'public/Modules/Localization' module exists, but it is not the active, reliable integration point for the main backoffice route tree. The first slice therefore uses Laravel's normal 'lang/{locale}' files and the existing 'users.language_code' field instead of adding a second language store.

## 3. Goals

1. Support exactly two interface locales: 'en' and 'th'.
2. Persist the authenticated user's preference in 'users.language_code'.
3. Resolve and apply the preference before any backoffice view is rendered.
4. Fall back safely to English when the stored value is absent, invalid, or unsupported.
5. Provide an accessible TH/EN switcher in the shared top bar.
6. Translate the shared navigation/top bar/footer plus Dashboard and Students Management labels in the first slice.
7. Preserve existing Affiliate locale behavior and existing public/API contracts.
8. Make locale and translation behavior testable without depending on browser automation.

## 4. Non-goals

- Translating every legacy view, customer-facing content, database value, or user-entered text in this slice.
- Replacing the Affiliate translation namespaces or changing the business timezone/date-format rules.
- Building a database-backed translation editor or depending on the legacy Localization module.
- Introducing per-record language settings or changing authorization rules.
- Automatically changing the language based on browser headers; the persisted user preference is authoritative for authenticated staff.

## 5. User-visible behavior

### 5.1 Language switcher

The shared top bar displays two clearly labelled controls, 'ไทย' and 'English', or an equivalent compact selector. The current locale has an active state that does not rely on color alone. Controls must be keyboard reachable, have visible focus, and include an accessible name.

Changing the selection submits a CSRF-protected request for the authenticated user. On success, the user returns to the page they were viewing and the selected locale is used for that response and subsequent requests. The control must work without requiring client-side JavaScript.

### 5.2 Locale resolution

For each web request:

1. If the authenticated user has a supported 'language_code', use it.
2. Otherwise use the configured default locale, 'en'.
3. Set Laravel's current locale before downstream route handlers and views execute.
4. Restore the previous locale when the middleware completes, so tests, queued in-process work, and long-running workers cannot inherit a request-specific value accidentally.

The general UI locale middleware runs in the normal 'web' middleware pipeline. The existing Affiliate locale middleware remains route-scoped and may override the locale for an Affiliate request according to its current rules; this preserves the Affiliate module's established behavior.

### 5.3 Persistence endpoint

Add an authenticated route named 'locale.update' at 'POST /settings/locale' (or an equivalent route that follows the existing route conventions). The request accepts one field, 'locale', and validates it against the configured supported list. Invalid values return the framework's normal validation error response and must not modify the user record. Valid values update only the current authenticated user's 'language_code', then redirect safely back to the originating page with a fallback to the main dashboard.

The endpoint must not accept a user identifier, must not permit changing another user's preference, and must use the normal web authentication and CSRF protections.

## 6. Proposed application boundaries

### 6.1 Configuration

Add 'config/ui-localization.php' with a single source of truth for:

- 'supported_locales': ['en', 'th']
- 'default_locale': 'en'
- optional display metadata used by the switcher (label and direction, if needed)

Validation and middleware must read this configuration rather than duplicating literal locale lists.

### 6.2 Locale middleware

Add 'App\Http\Middleware\SetUiLocale' with one responsibility: resolve the current request locale, call 'app()->setLocale(...)' before the next middleware, and restore the previous value in a 'finally' block. It must handle an unauthenticated request and a null/invalid user value without throwing.

Register it in the normal 'web' pipeline so all layouts see a resolved locale. Keep 'SetAffiliateLocale' unchanged except where a small compatibility adjustment is required by ordering or tests.

### 6.3 Locale controller

Add 'App\Http\Controllers\UiLocaleController' with one action for the update endpoint. The action validates the exact supported values, updates the current authenticated user, and returns a safe redirect. It must not contain translation strings or view-rendering logic.

### 6.4 Translation namespaces

Create matching locale-first files:

    lang/
      en/
        common.php
        navigation.php
        dashboard.php
        students.php
      th/
        common.php
        navigation.php
        dashboard.php
        students.php

Keys should describe intent and remain identical between locales. Placeholder names and nesting must match. Shared labels belong in 'common.php' or 'navigation.php'; Dashboard-only labels belong in 'dashboard.php'; Student List and Students Management labels belong in 'students.php'. Existing feature files remain in place and are not renamed as part of this slice.

### 6.5 Views

Replace hardcoded labels in the shared shell and the approved first-slice pages with translation calls. Keep domain data, student names, course names, and other user-entered values unchanged. The top-bar switcher should render from the configured locale list and expose the active locale to the view through 'app()->getLocale()'.

## 7. Fallback and compatibility rules

- 'en' is the default for new sessions and for users whose 'language_code' is null or invalid.
- Translation keys introduced in this slice must have complete English entries. Missing Thai entries may temporarily resolve through Laravel's configured fallback locale, but the parity test should prevent new omissions from being accepted silently.
- Existing hardcoded labels on screens outside the first slice may remain English; this is an intentional staged rollout, not a language-resolution failure.
- Existing 'language_code' values other than 'en' and 'th' are treated as invalid and are not trusted for locale selection. They may remain stored for data compatibility until explicitly corrected by the user or an administrative cleanup.
- Affiliate routes continue to use 'SetAffiliateLocale' and their current translation files. The new shared middleware must not remove or rename Affiliate keys.
- No migration is required because the existing 'users.language_code' column is reused.

## 8. Security, accessibility, and operational requirements

- Require authentication and CSRF protection for preference updates.
- Validate locale values against the allow-list; never pass an arbitrary request value to 'setLocale'.
- Update only the authenticated user's row.
- Use a same-site redirect/back response with a known dashboard fallback; do not redirect to an arbitrary URL supplied by the request body.
- Keep the switcher usable by keyboard and screen readers, with text labels, focus styles, and a non-color active indicator.
- Do not log or expose sensitive user data while changing language.
- Keep request locale state isolated and restore it after the request.

## 9. Verification and acceptance criteria

The feature is ready for implementation review when all of the following are true:

1. A request by a user with 'language_code = th' renders the first-slice shell with 'app()->getLocale() === th'.
2. A request by a user with 'language_code = en', a null value, or an unsupported value resolves to English as specified.
3. The middleware restores the locale that existed before it ran, including when downstream code throws.
4. Unauthenticated requests use the configured English default without an exception.
5. The update endpoint rejects unsupported values, requires authentication, honors CSRF, and persists only 'en' or 'th' for the current user.
6. The switcher shows the active locale, preserves the current page after a successful update, and remains keyboard accessible.
7. English and Thai translation files have matching key paths and placeholder names for the first slice.
8. The shared layout's 'lang' attribute follows the resolved locale, and translated navigation/Dashboard/Student List labels appear for both locales.
9. Existing Affiliate locale tests and behavior continue to pass.
10. Focused PHPUnit tests, view compilation/cache checks, and 'git diff --check' pass before the implementation is reported complete.

## 10. Test plan

Add focused tests alongside the existing test conventions:

- Unit tests for supported-locale resolution, invalid/null fallback, and locale restoration.
- Feature tests for authentication, validation, persistence, safe redirect, and protection against updating another user.
- Translation structure tests that compare English and Thai key paths and placeholder sets for the new namespaces.
- View/feature tests that assert the shared layout and switcher use the current locale and that first-slice labels are translated.
- Regression coverage for Affiliate locale middleware and existing translation structure tests.

## 11. Rollout and recovery

The default behavior remains English, so deployment does not require a data backfill. Existing users can opt into Thai from the switcher; users with no preference continue to see English. If a translation file introduces a rendering problem, removing the affected view key calls and leaving the English hardcoded label is a safe short-term rollback; the database column and endpoint can remain backward-compatible.

## 12. Approved scope assumption

The approved first release is deliberately limited to the shared shell, Dashboard, and Students Management. Translation of Affiliate, Finance, CRM, and remaining legacy pages is a follow-up scope and should be planned separately after this foundation is verified.
