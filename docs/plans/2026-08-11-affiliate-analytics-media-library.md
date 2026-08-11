# Affiliate Analytics and Media Library Implementation Plan

> **REQUIRED EXECUTION SKILL:** Use `executing-plans` for task-by-task execution.

**Plan status:** Awaiting explicit user approval

- **Goal:** Add first-party Affiliate click analytics, a scoped analytics dashboard, and a managed media library whose public claims match the implemented behavior, while preserving the existing wallet, commission, withdrawal, and first-touch attribution contracts.
- **Architecture:** Extend the current `App\Domain\Affiliate` boundary with append-only click evidence, permanent daily aggregates, read-only scoped queries, and focused content administration. Keep current-domain commissions as the only sales and income evidence. Do not import from or write to the legacy Affiliate tracking tables.
- **Tech stack:** PHP 8.2.12, Laravel 10.50.2, Eloquent, the locally configured MySQL connection, SQLite in PHPUnit, Blade, Bootstrap/AdminLTE, local Chart.js 2.9.4, browser-native JavaScript ES modules, Node's built-in test runner, PHPUnit 10. The production database engine remains an external unknown until staging/deployment evidence is supplied.
- **Approved specification:** `docs/specs/2026-08-11-affiliate-analytics-media-library-design.md`

## Verified Baseline

The following facts were verified in the repository on 2026-08-11:

- `routes/affiliate.php` is loaded by `App\Providers\RouteServiceProvider`; `php artisan route:list --path=affiliate` currently reports 38 Affiliate-related routes.
- `AffiliateDashboardQuery::ForUser` currently returns only the active profile, wallet, commissions, and withdrawals.
- `AffiliateAttributionService` currently enforces first-touch ownership through an encrypted HttpOnly, SameSite=Lax cookie and supports the `courses_pennding` bridge.
- `AffiliateCommissionService::CreateForConfirmedPayment` creates one current-domain commission only from a confirmed receipt/order and persists integer minor-unit evidence.
- The current content administration routes are authenticated but do not have a dedicated permission; their delete action physically deletes the record and file.
- The legacy `affiliate_links` and `affiliate_link_visit_track_users` tables are not part of the current Affiliate domain. Local inspection found no visit rows, but that is not evidence about production data.
- `public/plugins/chart.js/Chart.min.js` contains Chart.js 2.9.4; no new chart dependency is required.
- PHP `intl` is not enabled in the verified CLI configuration. The implementation must not require it.
- The focused baseline command passed with 15 tests and 64 assertions:

```powershell
C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --do-not-cache-result tests\Feature\Affiliate\AffiliateReferralCaptureTest.php tests\Feature\Affiliate\AffiliateFinanceNavigationTest.php tests\Feature\Images\ExtendedImageUploadWebpTest.php
```

- The existing JavaScript poller baseline passed with five tests:

```powershell
node --test tests\JavaScript\book-shipment-alert-poller.test.mjs
```

## Verified Implementation Inference

The approved specification requires campaign evidence to survive the current course-enquiry flow. Repository inspection shows that `AffiliateAttributionService::PersistPendingBridge` currently copies only profile and expiry fields into `courses_pennding`, and `BindPendingToContact` reconstructs attribution from those fields later. Therefore the additive attribution-context migration must also add the approved optional click/campaign snapshots to `courses_pennding`. This does not add a new product behavior; it prevents the existing bridge from discarding the approved first-touch campaign context.

## Known External Unknowns

- Production row counts, traffic shape, database engine version, scheduler health, and deployment alert ownership are not evidenced locally. Staging verification is required before enabling a feature flag.
- The repository has structured logging but no verified metrics backend. The implementation will emit bounded structured measures; external dashboards and alert ownership remain deployment work.
- Real concurrent-row behavior cannot be proved by SQLite. A MySQL-only integration check is included and must run on staging before rollout.

## Frontend Direction

The approved text-only design is implemented as an evidence-ledger interface for Affiliates who need to distinguish promotional activity from confirmed financial evidence.

- **Palette:** ink `#24364B`, primary blue `#1266AB`, evidence blue `#CEE8FF`, confirmation green `#397A37`, caution amber `#9A6500`, and paper `#F7FAFC`.
- **Type roles:** the already bundled Poppins family is used sparingly for Latin headings; the existing Thai-capable system sans-serif stack is used for body copy; `ui-monospace` is used only for referral and campaign codes.
- **Layout:** flat bordered regions, a clear reading order, generous data-table spacing, and no decorative shadows or gradients.
- **Signature:** every KPI has a short evidence label such as “Filtered click activity” or “Confirmed receipt evidence,” so traffic and money never appear interchangeable.
- **Motion:** only the refresh state changes; reduced-motion preferences disable nonessential transitions.

The first generic card-grid direction was rejected during self-review because it could fit any dashboard. The evidence labels and ledger rules tie the interface to the Affiliate proof model. No wireframe is included because the approved design explicitly selected text-only review.

## Global Constraints

- Keep the public commission rate driven by current configuration (currently 15%) and do not reintroduce the deprecated pilot label anywhere.
- Add `click_tracking`, `analytics`, and `media_library` feature flags disabled by default.
- Preserve existing route names for dashboard, payout accounts, withdrawals, and content administration.
- Keep first-touch attribution: a later valid click may count for the later Affiliate, but it must not replace an unexpired existing owner.
- Treat current-domain receipt-backed commissions as the only sales evidence. Calculate eligible sales from `eligible_tuition_minor` and net income from `commission_minor - reversed_minor`.
- Store and calculate money in integer minor units. Serialize money as decimal strings.
- Never persist or log raw IP addresses, full user agents, visitor hashes, referral cookies, arbitrary incoming query strings, captions, or destination URLs.
- Do not import from, reconcile with, or write to legacy Affiliate click/link tables.
- Derive every authenticated Affiliate scope from the current user and `AffiliateActiveProfileQuery`; never accept an Affiliate profile ID from a web request.
- Use UTC for new event timestamps and Asia/Bangkok for report-day boundaries and daily metric dates.
- Keep raw click events for 13 calendar months, visitor windows for 48 hours, and daily aggregates permanently.
- Render only enabled, unarchived, currently published media. Campaign codes are globally unique, lowercase, ASCII, and immutable after creation.
- Permit only relative application paths beginning with `/` or absolute HTTPS destinations whose exact host is allowlisted.
- Add all new visible copy to both `lang/en/affiliate.php` and `lang/th/affiliate.php`; Thai is the Affiliate default, while an authenticated `en` or `th` preference wins.
- Follow repository naming requirements: PascalCase functions/methods/tests/callbacks, snake_case variables/properties/payload keys, UPPER_CASE constants, and kebab-case routes, files, CSS selectors, and IDs unless framework contracts require otherwise.
- Preserve unrelated dirty-worktree changes. Do not commit, push, enable production flags, or run production migrations as part of implementation without separate authorization.

## Task 1: Add fail-closed feature, privacy, and abuse configuration

**Files:**

- Modify: `config/affiliate.php` at the returned configuration array
- Modify: `.env.example` at the Affiliate feature-flag block
- Modify: `phpunit.xml` at the forced Affiliate environment variables
- Modify: `app/Domain/Affiliate/Services/AffiliatePreflightService.php` at `Checks`
- Modify: `tests/Feature/Affiliate/AffiliatePreflightCommandTest.php`

- **Consumes:** Existing `AffiliateFeatureGate`, `AffiliatePreflightService`, and the fail-closed flag pattern.
- **Produces:** The exact configuration contract below.
- **Checkpoint:** None.

```php
'features' => [
    // Existing keys remain unchanged.
    'click_tracking' => env('AFFILIATE_FEATURE_CLICK_TRACKING', false),
    'analytics' => env('AFFILIATE_FEATURE_ANALYTICS', false),
    'media_library' => env('AFFILIATE_FEATURE_MEDIA_LIBRARY', false),
],
'default_locale' => env('AFFILIATE_DEFAULT_LOCALE', 'th'),
'supported_locales' => ['th', 'en'],
'visitor_hash_key' => env('AFFILIATE_VISITOR_HASH_KEY'),
'allowed_destination_hosts' => array_values(array_filter(array_map(
    'trim',
    explode(',', (string) env('AFFILIATE_ALLOWED_DESTINATION_HOSTS', parse_url((string) env('APP_URL'), PHP_URL_HOST) ?: '')),
))),
'abuse' => [
    'registration_attempts_per_minute' => env('AFFILIATE_REGISTRATION_ATTEMPTS_PER_MINUTE', 5),
    'click_attempts_per_minute' => env('AFFILIATE_CLICK_ATTEMPTS_PER_MINUTE', 120),
    'known_bot_tokens' => ['bot', 'crawler', 'spider', 'slurp', 'bingpreview', 'facebookexternalhit', 'whatsapp', 'telegrambot', 'discordbot'],
],
'batch_sizes' => [
    // Existing keys remain unchanged.
    'click_retention' => 1000,
    'click_reconciliation' => 1000,
],
```

Add empty/disabled defaults to `.env.example`. Add disabled feature flags and a non-secret test-only visitor key of at least 32 characters to `phpunit.xml`.

Extend preflight with `VisitorHashKeyCheck` and `DestinationHostsCheck`. A missing/short independent visitor key is `BLOCKED` only when click tracking is enabled. An empty host allowlist is `BLOCKED` when the media library is enabled. Do not print the key or host values.

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Add PascalCase tests `EnabledClickTrackingRequiresIndependentVisitorHashKey`, `DisabledClickTrackingDoesNotRequireVisitorHashKey`, and `EnabledMediaLibraryRequiresDestinationHost`.
- [ ] Run `C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --do-not-cache-result tests\Feature\Affiliate\AffiliatePreflightCommandTest.php`; expect RED because the checks do not exist.
- [ ] Implement the exact keys and preflight checks without exposing secret material.
- [ ] Rerun the same command; expect exit code 0 and all local preflight tests to pass.
- [ ] Refactor duplicate check construction only through the existing `BuildCheck` boundary, then rerun the file.

## Task 2: Resolve the Affiliate locale without changing the global application default

**Files:**

- Create: `app/Http/Middleware/SetAffiliateLocale.php`
- Modify: `app/Http/Kernel.php` at `$middlewareAliases`
- Modify: `app/Domain/Affiliate/Services/AffiliateAccountService.php` at `Register`
- Modify: `resources/views/layouts/app.blade.php` at `<html lang>` and `Content-Language`
- Create: `tests/Feature/Affiliate/AffiliateLocaleTest.php`
- Modify: `tests/Feature/Affiliate/AffiliateRegistrationTest.php`

- **Consumes:** `User::language_code`, `config('affiliate.default_locale')`, and the current `layouts.app` language attributes.
- **Produces:** Middleware alias `affiliate.locale` and scoped locale behavior.
- **Checkpoint:** None.

`SetAffiliateLocale::handle(Request $request, Closure $next): Response` follows Laravel's required middleware method name. It must select an authenticated user's preference only when it is exactly `th` or `en`; otherwise it must select `affiliate.default_locale`, falling back to `th`. It must call `app()->setLocale($selected_locale)` before the next middleware and restore the prior locale in `finally` after the response is created so tests and long-running workers do not leak request state. It must not change `config/app.php`'s global `en` default.

`AffiliateAccountService::Register` must set `language_code` to the Affiliate default because the verified registration request has no locale field. Do not add a new registration input in this scope. Existing users whose value is explicitly `en` remain English.

The layout language attributes must use `app()->getLocale()`. Existing book-shipment middleware or controllers that set Thai continue to render Thai; unrelated routes remain on the global English locale.

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Write `#[Test]` methods `AffiliateDefaultsToThaiWhenPreferenceIsMissing`, `AuthenticatedEnglishPreferenceWins`, `UnsupportedPreferenceFallsBackToThai`, `UnrelatedRouteKeepsGlobalEnglishLocale`, and `NewAffiliateRegistrationUsesThaiDefault`.
- [ ] Run `C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --do-not-cache-result tests\Feature\Affiliate\AffiliateLocaleTest.php tests\Feature\Affiliate\AffiliateRegistrationTest.php`; expect RED because the middleware and default do not exist.
- [ ] Implement the middleware, alias, account default, and dynamic language attributes.
- [ ] Rerun the command; expect exit code 0 and explicit `lang`/`Content-Language` assertions for `th` and `en` to pass.
- [ ] Refactor locale selection into one private PascalCase method and rerun both files.

## Task 3: Create the click-event, unique-window, and daily-metric schema

**Files:**

- Create: `database/migrations/2026_08_11_000001_create_affiliate_analytics_tables.php`
- Create: `app/Domain/Affiliate/Models/AffiliateClickEvent.php`
- Create: `app/Domain/Affiliate/Models/AffiliateVisitorWindow.php`
- Create: `app/Domain/Affiliate/Models/AffiliateClickDailyMetric.php`
- Create: `tests/Feature/Affiliate/AffiliateAnalyticsPersistenceTest.php`

- **Consumes:** `affiliate_profiles` and the existing `affiliate_contents` table.
- **Produces:** Three current-domain analytics tables and focused Eloquent models.
- **Checkpoint:** None.

The migration must create exactly these persistence contracts:

```text
affiliate_click_events
  id
  affiliate_profile_id FK RESTRICT
  affiliate_content_id nullable FK NULL ON DELETE
  campaign_code nullable varchar(96)
  source_type varchar(16)
  destination_target varchar(2048)
  visitor_hash char(64)
  unique_window_key char(26)
  is_unique boolean
  clicked_at datetime
  created_at timestamp

affiliate_visitor_windows
  id
  affiliate_profile_id FK CASCADE
  visitor_hash char(64)
  unique_window_key char(26)
  last_unique_at datetime
  last_seen_at datetime
  created_at / updated_at

affiliate_click_daily_metrics
  id
  affiliate_profile_id FK RESTRICT
  metric_date date
  dimension_key varchar(128)
  affiliate_content_id nullable FK NULL ON DELETE
  campaign_code nullable varchar(96)
  total_clicks unsigned bigint default 0
  unique_clicks unsigned bigint default 0
  created_at / updated_at
```

Create the indexes and unique constraints from specification sections 9.1–9.3, including unique `(affiliate_profile_id, visitor_hash)` and unique `(affiliate_profile_id, metric_date, dimension_key)`. Give long indexes explicit names that fit MySQL limits.

`AffiliateClickEvent` must set `UPDATED_AT = null`, cast identifiers/count flags/timestamps, expose only `AffiliateProfile` and `AffiliateContent` relations, reject model updates and ordinary model deletes with `LogicException`, and document that query-builder deletion is reserved for retention. The other two models expose only relations and casts needed by their services.

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Write complete persistence tests for all columns, foreign keys, unique constraints, integer casts, UTC event timestamps, and append-only model guards.
- [ ] Run `C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --do-not-cache-result tests\Feature\Affiliate\AffiliateAnalyticsPersistenceTest.php`; expect RED because the tables and models do not exist.
- [ ] Implement the migration and the three models.
- [ ] Rerun the command; expect exit code 0, unique-window duplicates to fail, and event updates/deletes through the model to throw.
- [ ] Refactor relation return types without changing behavior and rerun the file.

## Task 4: Add durable click/campaign snapshots to attribution and the current pending bridge

**Files:**

- Create: `database/migrations/2026_08_11_000002_add_affiliate_click_context_to_attributions.php`
- Modify: `app/Domain/Affiliate/Models/AffiliateAttribution.php`
- Modify: `app/Models/CoursePending.php`
- Modify: `tests/Feature/Affiliate/AffiliateAttributionPersistenceTest.php`

- **Consumes:** Task 3 click events, `affiliate_contents`, current `affiliate_attributions`, and `courses_pennding`.
- **Produces:** Optional evidence snapshots that survive click pruning and media archival.
- **Checkpoint:** None.

Add the following nullable columns to both `affiliate_attributions` and `courses_pennding`, using the `affiliate_` prefix already used by the bridge:

```text
affiliate_click_event_id FK NULL ON DELETE
affiliate_content_id FK NULL ON DELETE
affiliate_campaign_code varchar(96)
affiliate_source_type varchar(16)
affiliate_unique_window_key char(26)
affiliate_captured_destination varchar(2048)
```

Index the attribution campaign snapshot and unique-window key. Keep snapshot strings when event/content foreign keys become null. Keep all new bridge fields guarded from mass assignment and add only the required integer/date casts and relations.

The migration `down` path must drop foreign keys before columns and must preserve the existing profile/time bridge fields.

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Add PascalCase persistence tests that create an event/content snapshot, delete the event through the retention query boundary, hard-delete a disposable content row through the database relationship, and assert the nullable foreign keys clear while attribution snapshot strings remain. Task 5 separately covers archive behavior.
- [ ] Add a bridge test proving all six optional values persist without becoming fillable request fields.
- [ ] Run `C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --do-not-cache-result tests\Feature\Affiliate\AffiliateAttributionPersistenceTest.php`; expect RED on missing columns.
- [ ] Implement the additive migration and model metadata.
- [ ] Rerun the command; expect exit code 0 and snapshot survival assertions to pass.
- [ ] Refactor duplicated column lists inside the migration into a private migration closure only if rollback remains clear; rerun the file.

## Task 5: Extend Affiliate content safely and add dedicated administration permission

**Files:**

- Create: `database/migrations/2026_08_11_000003_extend_affiliate_contents_for_campaigns.php`
- Create: `database/migrations/2026_08_11_000004_add_affiliate_content_manage_permission.php`
- Modify: `app/Models/AffiliateContent.php`
- Create: `tests/Feature/Affiliate/AffiliateContentPersistenceTest.php`
- Modify: `tests/Feature/Affiliate/AffiliateRolePermissionTest.php`

- **Consumes:** Existing `affiliate_contents`, `SystemAdmin`/`Admin` permission-assignment convention, and task 3 foreign keys.
- **Produces:** Campaign metadata, publication state, archival evidence, soft deletion, and `affiliate-content-manage`.
- **Checkpoint:** None.

Add `destination_target`, globally unique `campaign_code`, nullable `publish_starts_at`/`publish_ends_at`, nullable `updated_by`/`archived_by`, nullable `archived_at`, and `deleted_at`. Add foreign keys to users with `nullOnDelete`. Backfill every existing row deterministically with destination `/` and campaign `legacy-{id}` before the unique index is created. The backfill must be chunked, idempotent, and must not change existing enabled/disabled status.

Use `SoftDeletes` in `AffiliateContent`. Cast publication/archive/delete timestamps as immutable datetimes. Add `Updater` and `Archiver` relations. Campaign code and destination are not set through legacy mass-assignment paths until the service in Task 8 owns validation.

The permission migration must create one `web` permission named `affiliate-content-manage`, assign it only to existing `SystemAdmin` and `Admin` roles, clear Spatie's permission cache before and after, and remove only that permission on rollback.

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Test deterministic backfill by running `up` twice against representative pre-existing rows and asserting stable values.
- [ ] Test campaign uniqueness across active and soft-deleted rows, publication casts, actor relations, and default soft-delete exclusion.
- [ ] Test that only existing `SystemAdmin` and `Admin` receive the permission and `Affiliate-user` does not.
- [ ] Run `C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --do-not-cache-result tests\Feature\Affiliate\AffiliateContentPersistenceTest.php tests\Feature\Affiliate\AffiliateRolePermissionTest.php`; expect RED.
- [ ] Implement both migrations and the model changes.
- [ ] Rerun the command; expect exit code 0 and no changes to unrelated permissions.
- [ ] Refactor migration constants without changing generated values; rerun both files.

## Task 6: Define the report range and metric result contracts

**Files:**

- Create: `app/Domain/Affiliate/ValueObjects/AffiliateAnalyticsRange.php`
- Create: `app/Domain/Affiliate/ValueObjects/AffiliateAnalyticsResult.php`
- Create: `tests/Unit/Affiliate/AffiliateAnalyticsRangeTest.php`

- **Consumes:** CarbonImmutable, Asia/Bangkok report boundaries, and UTC event storage.
- **Produces:** Validated immutable input/output contracts for all analytics queries.
- **Checkpoint:** None.

Implement these public contracts:

```php
final class AffiliateAnalyticsRange
{
    public static function Default(?DateTimeInterface $now = null): self;
    public static function FromStrings(string $from, string $to, ?DateTimeInterface $now = null): self;
    public function FromDate(): CarbonImmutable;
    public function ToDate(): CarbonImmutable;
    public function EventFromUtc(): CarbonImmutable;
    public function EventToUtcExclusive(): CarbonImmutable;
    public function CommissionFromLocal(): CarbonImmutable;
    public function CommissionToLocalExclusive(): CarbonImmutable;
    public function Dates(): array;
    public function ToArray(): array;
}
```

`Default` includes the current Bangkok date plus the preceding 29 dates. `FromStrings` accepts strict `Y-m-d`, rejects future dates, rejects `from > to`, and rejects inclusive ranges longer than 366 days. UTC boundaries convert local midnight correctly. Invalid input raises `InvalidArgumentException` with a stable, non-sensitive English domain message; the Form Request later supplies localized web feedback.

`AffiliateAnalyticsResult` is an immutable DTO with snake_case constructor properties for `range`, `kpis`, `daily_series`, `recent_sales`, `sales_pagination`, `top_media`, and `generated_at`. All money inside the DTO remains integer minor units until the HTTP resource serializes it.

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Write exact-boundary tests for 7/30/90/366 days, 367-day rejection, future rejection, reversed dates, Bangkok-to-UTC conversion, and default 30-day inclusion.
- [ ] Run `C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --do-not-cache-result tests\Unit\Affiliate\AffiliateAnalyticsRangeTest.php`; expect RED because the value objects do not exist.
- [ ] Implement the smallest immutable contracts.
- [ ] Rerun the command; expect exit code 0 and exact ISO boundary assertions to pass.
- [ ] Refactor repeated validation messages into private UPPER_CASE constants and rerun.

## Task 7: Derive private visitor identity and classify excluded traffic

**Files:**

- Create: `app/Domain/Affiliate/Services/AffiliateVisitorHasher.php`
- Create: `app/Domain/Affiliate/Services/AffiliateTrafficClassifier.php`
- Create: `tests/Unit/Affiliate/AffiliateVisitorHasherTest.php`
- Create: `tests/Unit/Affiliate/AffiliateTrafficClassifierTest.php`

- **Consumes:** Task 1 privacy configuration and active Affiliate profiles.
- **Produces:** HMAC identity and stable rejection reason codes without persisted source data.
- **Checkpoint:** None.

Implement:

```php
final class AffiliateVisitorHasher
{
    public function HashRequest(Request $request): string;
}

final class AffiliateTrafficClassifier
{
    public const REASON_KNOWN_BOT = 'known_bot';
    public const REASON_AUTHENTICATED_OWNER = 'authenticated_owner';

    public function RejectionReason(Request $request, AffiliateProfile $profile): ?string;
}
```

`HashRequest` must validate a visitor key of at least 32 characters, normalize IP with `FILTER_VALIDATE_IP`, lowercase/collapse whitespace in at most the first 512 user-agent characters, and return `hash_hmac('sha256', $normalized_ip."\n".$normalized_user_agent, $key)`. The source strings must not leave method scope.

The classifier returns a reason for an authenticated user whose ID equals `profile.user_id` or when a configured known-bot token appears in a normalized user agent. It returns null for accepted traffic. It must not log request data. Classification is explicitly heuristic.

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Prove deterministic hashes, key rotation changing the digest, no raw source substring in the digest, invalid-key rejection, case-insensitive bot detection, owner rejection, another authenticated user acceptance, and anonymous acceptance.
- [ ] Run `C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --do-not-cache-result tests\Unit\Affiliate\AffiliateVisitorHasherTest.php tests\Unit\Affiliate\AffiliateTrafficClassifierTest.php`; expect RED.
- [ ] Implement both services.
- [ ] Rerun the command; expect exit code 0 and no logging expectations to be violated.
- [ ] Refactor normalization into private PascalCase methods and rerun both files.

## Task 8: Validate campaign destinations and store marketing media behind a service boundary

**Files:**

- Create: `app/Domain/Affiliate/Services/AffiliateDestinationValidator.php`
- Create: `app/Domain/Affiliate/ValueObjects/StoredAffiliateMedia.php`
- Create: `app/Domain/Affiliate/Services/AffiliateMediaStorageService.php`
- Create: `app/Http/Requests/Affiliate/StoreAffiliateContentRequest.php`
- Create: `app/Http/Requests/Affiliate/UpdateAffiliateContentRequest.php`
- Create: `app/Http/Requests/Affiliate/ToggleAffiliateContentStatusRequest.php`
- Create: `tests/Unit/Affiliate/AffiliateDestinationValidatorTest.php`
- Create: `tests/Feature/Affiliate/AffiliateContentRequestTest.php`

- **Consumes:** Task 1 host allowlist, existing `ImageUploadStorageService`, and current 50 MB content limit.
- **Produces:** Normalized safe destinations, validated request payloads, and reversible image/video storage.
- **Checkpoint:** None.

`AffiliateDestinationValidator::Normalize(string $destination_target): string` must:

- accept a relative path starting with one `/` but reject protocol-relative `//`, backslashes, control characters, and empty values;
- accept absolute `https` URLs only when they have no user-info and their lowercase exact host appears in `affiliate.allowed_destination_hosts`;
- reject `http`, unsupported schemes, missing hosts, fragments containing control characters, and non-allowlisted hosts;
- return a normalized relative path or HTTPS URL and never accept a destination from a public tracking request.

`AffiliateMediaStorageService::Store(UploadedFile $file, string $type): StoredAffiliateMedia` must delegate images to `ImageUploadStorageService` and store videos beneath `public/uploads/affiliate/video` with a UUID filename and an extension derived from server-observed MIME. `Delete(StoredAffiliateMedia $media): void` is used only to clean up a failed create; archival does not delete files.

The store request requires title, type, file, destination, campaign code, and valid publication order. Campaign uses `/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/D`, maximum 96, and database uniqueness including archived rows. The update request permits title, description, destination, status, and publication dates but prohibits campaign-code and file replacement. Validation must cross-check selected type with observed MIME and the current image/video allowlists. The toggle request requires an existing unarchived content ID.

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Test accepted local paths and exact HTTPS hosts plus rejected protocol-relative, HTTP, credentialed, control-character, and lookalike-subdomain targets.
- [ ] Test mismatched declared type/MIME, oversize upload, unsafe extension, invalid campaign, duplicate archived campaign, invalid publication window, and prohibited update fields.
- [ ] Run `C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --do-not-cache-result tests\Unit\Affiliate\AffiliateDestinationValidatorTest.php tests\Feature\Affiliate\AffiliateContentRequestTest.php`; expect RED.
- [ ] Implement validator, DTO, storage service, and Form Requests.
- [ ] Rerun the command; expect exit code 0 and storage cleanup tests to leave no orphan test files.
- [ ] Refactor MIME maps into private UPPER_CASE constants and rerun both files.

## Task 9: Make content creation, editing, status changes, and archive auditable

**Files:**

- Create: `app/Domain/Affiliate/Services/AffiliateContentService.php`
- Modify: `app/Http/Controllers/AffiliateContentManageController.php`
- Create: `tests/Feature/Affiliate/AffiliateContentServiceTest.php`

- **Consumes:** Tasks 5 and 8, `AffiliateAuditService`, and the current content controller entry point.
- **Produces:** One write boundary for all Affiliate marketing content.
- **Checkpoint:** None.

Implement these methods:

```php
public function Create(array $validated, UploadedFile $file, User $actor, string $correlation_id): AffiliateContent;
public function Update(AffiliateContent $content, array $validated, User $actor, string $correlation_id): AffiliateContent;
public function ToggleStatus(AffiliateContent $content, User $actor, string $correlation_id): AffiliateContent;
public function Archive(AffiliateContent $content, User $actor, string $correlation_id): void;
```

Create must normalize the destination, store the file, and create the row in a database transaction; any database failure deletes only the newly stored file. Update must lock the row, reject archived records and campaign mutations, normalize the destination, and set `updated_by`. Toggle must lock, reject archived rows, and switch only `enabled`/`disabled`. Archive must lock, set `archived_by`/`archived_at`, and soft-delete without removing the file.

Emit `content.created`, `content.updated`, and `content.archived` through the Affiliate audit boundary using only correlation ID, outcome, actor/profile-safe IDs, campaign code, and status. Do not include title, caption, path, or destination.

Refactor the controller to use the three Form Requests and service. Keep controller method names and route contracts stable where possible. All user-facing messages must use translation keys.

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Test successful create/update/toggle/archive, campaign immutability, file cleanup on failed create, preserved file on archive, locked-row status behavior, and sanitized signals.
- [ ] Run `C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --do-not-cache-result tests\Feature\Affiliate\AffiliateContentServiceTest.php tests\Feature\Images\ExtendedImageUploadWebpTest.php`; expect RED in the new file while the existing WebP regression remains green.
- [ ] Implement the service and thin controller delegation.
- [ ] Rerun the command; expect exit code 0, existing WebP storage to remain green, and no orphaned files.
- [ ] Refactor shared content loading/locking into one private PascalCase method and rerun.

## Task 10: Define resolved tracking targets and append clicks atomically

**Files:**

- Create: `app/Domain/Affiliate/ValueObjects/AffiliateClickContext.php`
- Create: `app/Domain/Affiliate/ValueObjects/AffiliateTrackingTarget.php`
- Create: `app/Domain/Affiliate/Services/AffiliateClickTrackingService.php`
- Create: `tests/Feature/Affiliate/AffiliateClickTrackingServiceTest.php`

- **Consumes:** Tasks 3, 5, 7, and 8 plus `AffiliateFeatureGate` and `AffiliateAuditService`.
- **Produces:** Valid direct/media resolution and one transactional click-write algorithm.
- **Checkpoint:** None.

Use immutable value objects with these fields:

```text
AffiliateTrackingTarget
  affiliate_profile
  affiliate_content nullable
  campaign_code nullable
  source_type: direct|media
  destination_target

AffiliateClickContext
  click_event_id
  content_id nullable
  campaign_code nullable
  source_type
  unique_window_key
  destination_target
```

Implement:

```php
public function ResolveDirect(string $referral_code, string $destination_target): ?AffiliateTrackingTarget;
public function ResolveMedia(string $referral_code, string $campaign_code): ?AffiliateTrackingTarget;
public function Record(Request $request, AffiliateTrackingTarget $target, string $correlation_id): ?AffiliateClickContext;
```

Resolution accepts only an active profile. Media additionally requires enabled, unarchived content whose UTC publication window contains now. It always uses the administrator-stored destination. Direct uses a normalized application path supplied by trusted middleware/controller code.

`Record` returns null without writing when `click_tracking` is disabled or traffic is classified as bot/owner. For accepted traffic it must:

1. derive the visitor hash before entering persistence;
2. begin one transaction;
3. `insertOrIgnore` the `(profile, visitor_hash)` window row and then lock it;
4. create a new ULID window at or after the exact 24-hour boundary, otherwise reuse the current key;
5. append one click event;
6. `insertOrIgnore` the daily `(profile, Bangkok date, dimension)` row, lock it, and increment total by one and unique by zero/one;
7. update `last_seen_at`, commit, and return context.

The dimension is exactly `direct` or `media:{campaign_code}`. Retry duplicate-key races a maximum of three times. Any final exception is propagated to the HTTP fail-soft boundary. Signals must use stable reason codes and exclude request identity.

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Test direct/media resolution boundaries, disabled flag, bot/owner rejection, first unique click, repeat inside 24 hours, exact 24-hour renewal, profile-specific windows, campaign dimension assignment, Bangkok midnight, and transaction rollback.
- [ ] Test two sequential accepted clicks produce `total_clicks=2`, `unique_clicks=1`, and two immutable raw events.
- [ ] Run `C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --do-not-cache-result tests\Feature\Affiliate\AffiliateClickTrackingServiceTest.php`; expect RED.
- [ ] Implement the DTOs and transaction exactly as specified.
- [ ] Rerun the command; expect exit code 0 and exact aggregate assertions.
- [ ] Refactor window/daily locking into focused private PascalCase methods, preserving one outer transaction; rerun.

## Task 11: Extend the referral cookie and attribution service without breaking old first-touch data

**Files:**

- Modify: `app/Domain/Affiliate/ValueObjects/AffiliateReferralCookie.php`
- Modify: `app/Domain/Affiliate/Services/AffiliateAttributionService.php`
- Modify: `app/Domain/Affiliate/Models/AffiliateAttribution.php`
- Modify: `app/Models/CoursePending.php`
- Modify: `tests/Feature/Affiliate/AffiliateReferralCaptureTest.php`
- Modify: `tests/Feature/Affiliate/AffiliateOrderAttributionTest.php`

- **Consumes:** Tasks 4 and 10 plus the existing encrypted first-touch cookie and pending bridge.
- **Produces:** Backward-compatible optional click context in cookies, contacts, pending enquiries, and order attribution.
- **Checkpoint:** None.

Extend `AffiliateReferralCookie` with nullable constructor parameters, all defaulting to null:

```php
?int $click_event_id = null,
?int $content_id = null,
?string $campaign_code = null,
?string $source_type = null,
?string $unique_window_key = null,
?string $destination_target = null,
```

`FromArray` must continue to accept the old three-field payload. `ToArray` must always write the three required fields and include optional keys only when non-null. Validate positive optional IDs, `direct|media` source types, ULID-shaped window keys, and consistent media campaign/content context. A malformed optional context invalidates the cookie safely.

Add this orchestration method while retaining `CaptureReferral(Request $request)` as a compatibility entry point:

```php
public function CaptureResolvedReferral(
    Request $request,
    AffiliateProfile $profile,
    ?AffiliateClickContext $click_context = null,
): ?AffiliateReferralCookie;
```

It must verify referral capture is enabled and the supplied profile is active. It must read and return an existing valid active first-touch cookie before constructing a replacement. Otherwise it issues the same encrypted, HttpOnly, SameSite=Lax cookie with optional click context.

`BindToContact` must copy optional fields only when it creates a new attribution. It must not change them when it returns a current attribution. `PersistPendingBridge` and `BindPendingToContact` must copy/reconstruct all optional fields so the existing enquiry path does not lose campaign context. Add model protection that rejects updates to the six attribution snapshot fields while still allowing existing lifecycle fields such as `ended_at`.

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Add tests `OldCookieShapeRemainsReadable`, `NewCookieRoundTripsClickContext`, `MalformedOptionalContextIsRejected`, `LaterClickCountsButDoesNotReplaceFirstTouch`, `PendingBridgePreservesCampaignContext`, and `AttributionContextIsImmutable`.
- [ ] Run `C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --do-not-cache-result tests\Feature\Affiliate\AffiliateReferralCaptureTest.php tests\Feature\Affiliate\AffiliateOrderAttributionTest.php`; expect RED only on new context assertions.
- [ ] Implement cookie compatibility, the resolved-profile method, attribution writes, bridge writes, and context guards.
- [ ] Rerun the command; expect exit code 0 and all original first-touch assertions to remain green.
- [ ] Refactor context-to-array mapping into one private PascalCase method in the service and rerun both files.

## Task 12: Wire public tracking routes and direct `?ref=` compatibility with fail-soft analytics

**Files:**

- Create: `app/Http/Controllers/Affiliate/AffiliateTrackingController.php`
- Modify: `app/Http/Middleware/CaptureAffiliateReferral.php`
- Modify: `app/Providers/RouteServiceProvider.php` at `boot`
- Modify: `routes/affiliate.php`
- Create: `tests/Feature/Affiliate/AffiliateClickTrackingTest.php`
- Modify: `tests/Feature/Affiliate/AffiliateReferralCaptureTest.php`

- **Consumes:** Tasks 10 and 11, Affiliate correlation IDs, and Laravel named rate limiters.
- **Produces:** Public direct/media redirects plus one click attempt per direct referral request.
- **Checkpoint:** None.

Add named routes:

```php
Route::get('/r/{referral_code}', [AffiliateTrackingController::class, 'Direct'])
    ->middleware(['affiliate.correlation', 'throttle:affiliate-click'])
    ->where('referral_code', '[A-Za-z0-9-]{3,64}')
    ->name('affiliate.tracking.direct');

Route::get('/r/{referral_code}/{campaign_code}', [AffiliateTrackingController::class, 'Media'])
    ->middleware(['affiliate.correlation', 'throttle:affiliate-click'])
    ->where('referral_code', '[A-Za-z0-9-]{3,64}')
    ->where('campaign_code', '[a-z0-9]+(?:-[a-z0-9]+)*')
    ->name('affiliate.tracking.media');
```

The `affiliate-click` limiter must use `affiliate.abuse.click_attempts_per_minute` and a one-way digest of its transient client key; it must never log or store raw IP or user-agent values in application logs. The limiter digest is not an analytics visitor hash and must still work when click tracking and its independent visitor key are disabled. A 429 response is generic and includes no existence detail.

`Direct` resolves `/` as its trusted destination. `Media` uses only the stored destination. For valid targets, each action attempts `Record`, then calls `CaptureResolvedReferral`, then returns `302`. When click tracking is disabled, rejected, or fails, the valid referral/redirect journey continues without fabricated click context. Invalid/inactive/unpublished targets create no event or cookie and redirect to `/`.

Refactor `CaptureAffiliateReferral::Handle` so a non-empty `ref` resolves and attempts the direct click before asking the attribution service to preserve or issue first touch. Use only `$request->getPathInfo()` as the direct destination snapshot; do not persist the incoming query string. This ordering is required so a later Affiliate's click can count without replacing the earlier cookie.

Catch only at the HTTP orchestration boundary. On a click-write exception, emit `affiliate.click.failed` with correlation ID, operation, outcome, profile ID, optional campaign code, exception class code (not message), and timestamp; then continue referral capture. Do not catch redirect/validation failures that should remain invalid.

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Test valid direct/media redirects, invalid profile, suspended profile, disabled/archived/early/expired media, rate limiting, flag-disabled referral continuity, and no arbitrary query persistence.
- [ ] Test a forced analytics transaction failure still sets/preserves a valid referral cookie and redirects, while storing no event/campaign evidence.
- [ ] Test a later valid Affiliate request records that click but retains the first Affiliate cookie.
- [ ] Run `C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --do-not-cache-result tests\Feature\Affiliate\AffiliateClickTrackingTest.php tests\Feature\Affiliate\AffiliateReferralCaptureTest.php`; expect RED.
- [ ] Implement the controller, limiter, routes, and middleware orchestration.
- [ ] Rerun the command; expect exit code 0, exact 302 locations, and zero-row assertions on all rejected paths.
- [ ] Refactor controller duplication into one private PascalCase method without moving business rules out of the service; rerun.

## Task 13: Query click, commission, and cohort evidence with bounded database work

**Files:**

- Create: `app/Domain/Affiliate/Queries/AffiliateClickAnalyticsQuery.php`
- Create: `app/Domain/Affiliate/Queries/AffiliateCommissionAnalyticsQuery.php`
- Create: `app/Domain/Affiliate/Queries/AffiliateAnalyticsQuery.php`
- Create: `tests/Feature/Affiliate/AffiliateAnalyticsQueryTest.php`

- **Consumes:** Tasks 3, 4, and 6 plus current-domain commissions and reversals.
- **Produces:** A scoped `AffiliateAnalyticsResult` with approved KPIs, daily series, recent sales, cohort conversion, and top media.
- **Checkpoint:** None.

Public entry point:

```php
public function ForUser(
    User $user,
    AffiliateAnalyticsRange $range,
    int $sales_page = 1,
): AffiliateAnalyticsResult;
```

It must resolve the active profile through `AffiliateActiveProfileQuery`. Controllers cannot call a profile-ID variant. Internal collaborators may accept the already authorized `AffiliateProfile` model.

Implement the formulas exactly:

```text
total_clicks = sum daily total_clicks in selected metric dates
unique_clicks = sum daily unique_clicks in selected metric dates
confirmed_sales = count commissions created in selected confirmation range
eligible_sales_value_minor = sum eligible_tuition_minor
net_commission_income_minor = sum commission_minor - reversed_minor
conversion denominator = unique click-event window keys opened in event-time range
conversion numerator = distinct attribution contact IDs in that cohort with any confirmed commission by generation time
conversion_rate = numerator / denominator * 100, or 0.00 when denominator is zero
```

Use integer/database aggregation for money and counts. Do not load all events or commissions. Click-event predicates use the range's UTC boundaries. Existing commission `created_at` predicates use the range's Bangkok application-time boundaries, matching the verified application timezone rather than pretending the legacy timestamp column has an embedded timezone. Produce exactly one daily point for every date in the range, including zero days. Daily series contains `metric_date`, `total_clicks`, `unique_clicks`, `confirmed_sales`, and `net_commission_income_minor`.

Recent sales are ordered by commission `created_at` then ID descending, 25 per page, and expose no contact PII. Each row contains commission ID, confirmed timestamp, persisted status, derived `reversal_status` (`NONE`, `PARTIAL`, or `FULL`), eligible value, gross commission, reversed commission, and net commission.

Top-media implementation must use a database `UNION` of known campaign codes found in click aggregates and attribution snapshots, left-join grouped click/sale subqueries, order by net income descending, confirmed sales descending, campaign code ascending, and limit to ten. Join content only for a current/historical title; never infer a missing snapshot from current content. Query Direct/Unknown separately and return it only when non-zero so it cannot displace a known campaign.

All relations required for recent rows must be eager loaded in bounded queries. Record duration and result counts through sanitized structured signals when the query exceeds a configurable log threshold; do not include range query strings or user data.

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Seed two Affiliates, direct/media/different-window clicks, current/historical campaign snapshots, confirmed commissions, partial/full reversals, and a later conversion outside the click range.
- [ ] Prove strict owner scoping, zero states, complete day filling, receipt-backed sales count, reversal formulas, cohort distinct-contact behavior, greater-than-100% signal behavior, top-ten ranking, and Direct/Unknown separation.
- [ ] Assert query-count bounds to prevent N+1 behavior on recent sales and top media.
- [ ] Run `C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --do-not-cache-result tests\Feature\Affiliate\AffiliateAnalyticsQueryTest.php`; expect RED.
- [ ] Implement the two focused evidence queries and the composing query.
- [ ] Rerun the command; expect exit code 0 and exact integer/decimal-independent assertions.
- [ ] Refactor repeated aggregate expressions into private query-builder methods and rerun.

## Task 14: Publish the authenticated analytics JSON contract

**Files:**

- Create: `app/Http/Requests/Affiliate/AffiliateAnalyticsRangeRequest.php`
- Create: `app/Http/Resources/Affiliate/AffiliateAnalyticsResource.php`
- Create: `app/Http/Controllers/Affiliate/AffiliateAnalyticsController.php`
- Modify: `routes/affiliate.php`
- Create: `tests/Feature/Affiliate/AffiliateAnalyticsApiTest.php`

- **Consumes:** Tasks 2, 6, and 13 plus existing auth/verified/correlation/feature middleware.
- **Produces:** `GET /affiliate/analytics`, `GET /affiliate/analytics/data`, and the stable snake_case response.
- **Checkpoint:** None.

The page and data routes must use `auth`, `verified`, `affiliate.locale`, `affiliate.correlation`, and `affiliate.feature:analytics`. Active-profile authorization remains in the query. Route names are `affiliate.analytics.index` and `affiliate.analytics.data`.

The Form Request accepts required strict `from`/`to` dates and optional positive `sales_page`, then builds `AffiliateAnalyticsRange`. It returns localized 422 messages for future, reversed, malformed, and >366-day ranges.

The resource must serialize this exact top-level contract, with money converted through `Money::FromMinor(...)->Decimal()` and conversion as a two-decimal string:

```json
{
  "data": {
    "range": {"from":"2026-07-13","to":"2026-08-11","timezone":"Asia/Bangkok"},
    "kpis": {
      "total_clicks": 0,
      "unique_clicks": 0,
      "confirmed_sales": 0,
      "eligible_sales_value": "0.00",
      "net_commission_income": "0.00",
      "conversion_rate": "0.00"
    },
    "daily_series": [],
    "recent_sales": [],
    "sales_pagination": {"current_page":1,"last_page":1,"per_page":25,"total":0},
    "top_media": [],
    "generated_at": "2026-08-11T00:00:00Z"
  }
}
```

Daily and row money keys also use decimal strings. Stable status/campaign codes remain untranslated in JSON; Blade/JavaScript maps them to translation text. On an unexpected refresh exception, emit `analytics.refresh_failed` with sanitized context and return a generic JSON 500 without exception details.

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Test unauthenticated, unverified, inactive, cross-owner, disabled-feature, valid zero, valid populated, invalid range, page bounds, decimal-string, and generic failure responses.
- [ ] Run `C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --do-not-cache-result tests\Feature\Affiliate\AffiliateAnalyticsApiTest.php`; expect RED because routes/classes do not exist.
- [ ] Implement request, resource, controller, and routes.
- [ ] Rerun the command; expect exit code 0 and exact JSON structure/status assertions.
- [ ] Refactor resource money mapping into one private PascalCase method and rerun.

## Task 15: Prune raw click identity state and provide guarded aggregate rebuilding

**Files:**

- Create: `app/Console/Commands/Affiliate/PruneAffiliateClickEventsCommand.php`
- Create: `app/Console/Commands/Affiliate/RebuildAffiliateClickMetricsCommand.php`
- Modify: `app/Console/Kernel.php` at `schedule`
- Create: `tests/Feature/Affiliate/AffiliateClickRetentionTest.php`
- Create: `tests/Feature/Affiliate/AffiliateClickMetricRebuildTest.php`

- **Consumes:** Task 3 tables, Task 6 range, existing command discovery, and `AffiliateAuditService`.
- **Produces:** Retry-safe bounded retention and an explicit repair path.
- **Checkpoint:** Rebuild must refuse to run while click tracking is enabled.

Command contracts:

```text
affiliate:prune-click-events {--batch=1000}
affiliate:rebuild-click-metrics {affiliate_profile_id} {from} {to} {--confirm}
```

Pruning validates batch 1–10,000, deletes events whose `clicked_at` is strictly before UTC now minus 13 calendar months through a query-builder-only retention boundary, deletes visitor windows whose `last_seen_at` is strictly before UTC now minus 48 hours, loops in bounded ID order, and never touches daily metrics or attribution snapshots. It reports event/window counts and duration and emits `click.retention_completed` without identifiers.

Schedule pruning daily at `02:15`, `withoutOverlapping`, only when click tracking is enabled.

Rebuild validates profile existence, strict range, range inside retained raw history, `--confirm`, and disabled click tracking. For each Bangkok day in an explicit profile/range, it replaces only that profile/day's daily dimensions from raw events in a transaction. It records a durable Affiliate audit entry with profile ID, date range, row counts, outcome, and correlation ID. It does not infer campaign codes or restore pruned raw events.

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Test exact 13-month/48-hour boundaries, multiple batches, invalid batches, daily/snapshot survival, retry safety, and schedule conditions.
- [ ] Test rebuild refusal without confirmation or while tracking is enabled, exact direct/media aggregate reconstruction, and idempotent rerun.
- [ ] Run `C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --do-not-cache-result tests\Feature\Affiliate\AffiliateClickRetentionTest.php tests\Feature\Affiliate\AffiliateClickMetricRebuildTest.php`; expect RED.
- [ ] Implement both commands and the schedule.
- [ ] Rerun the command; expect exit code 0 and immutable financial/campaign evidence assertions.
- [ ] Refactor shared batch validation into private PascalCase methods in each command rather than a cross-domain utility; rerun.

## Task 16: Add a shared Affiliate navigation and an independently refreshing dashboard summary

**Files:**

- Create: `resources/views/affiliate/partials/navigation.blade.php`
- Create: `resources/views/affiliate/partials/analytics-summary.blade.php`
- Modify: `app/Domain/Affiliate/Queries/AffiliateDashboardQuery.php` at `ForUser`
- Modify: `resources/views/affiliate/dashboard.blade.php`
- Modify: `resources/views/affiliate/payout_accounts/index.blade.php`
- Modify: `resources/views/affiliate/withdrawals/index.blade.php`
- Modify: `resources/views/layouts/menu.blade.php` at the Affiliate menu
- Modify: `resources/views/affiliate/frontend/header.blade.php` at the authenticated profile links
- Modify: `tests/Feature/Affiliate/AffiliateFinanceNavigationTest.php`
- Create: `tests/Feature/Affiliate/AffiliateDashboardAnalyticsTest.php`

- **Consumes:** Tasks 2, 13, and 14 plus current dashboard/finance behavior.
- **Produces:** One conditional navigation and a server-rendered 30-day summary with referral-copy control.
- **Checkpoint:** Existing wallet, commission, withdrawal pagination and route names must remain unchanged.

The navigation renders only for an authenticated, verified, active Affiliate. Show Dashboard/Payout/Withdrawals when `wallet_withdrawals` is enabled, Analytics when `analytics` is enabled, and Media when `media_library` is enabled. Active states use named-route matching. Menu/header links must follow the same feature predicates and must not be nested under unrelated CMS permission checks.

When analytics is enabled, `AffiliateDashboardQuery::ForUser` must request the default 30-day summary for the already resolved active profile and return it under `analytics_summary`; otherwise it returns `analytics_summary => null` and executes no analytics query. Add `referral_url` generated from `affiliate.tracking.direct`.

The summary partial renders five evidence-labelled KPIs, last-updated `<time>`, a manual refresh button, a polite live warning region, and a full-analytics link. It carries only data/endpoint attributes needed by the JavaScript module. Dashboard tables remain outside the refresh root so polling cannot reset pagination or form state.

Translate all existing visible dashboard/payout/withdrawal strings touched by shared navigation. Continue formatting input `min` values as machine decimals even when display strings are localized.

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Extend navigation tests across all three feature combinations and active/inactive profiles.
- [ ] Test summary hidden/visible behavior, exact 30-day values, server referral URL, zero state, and unchanged paginated finance tables.
- [ ] Run `C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --do-not-cache-result tests\Feature\Affiliate\AffiliateFinanceNavigationTest.php tests\Feature\Affiliate\AffiliateDashboardAnalyticsTest.php`; expect RED only for the new behavior.
- [ ] Implement query composition, partials, and conditional links.
- [ ] Rerun the command; expect exit code 0 and existing finance assertions to remain green.
- [ ] Refactor repeated navigation markup to the one partial and rerun both files.

## Task 17: Implement a tested 60-second analytics poller

**Files:**

- Create: `public/js/affiliate-analytics-poller.js`
- Create: `tests/JavaScript/affiliate-analytics-poller.test.mjs`

- **Consumes:** Existing ES-module/poller dependency-injection convention and Task 14 JSON.
- **Produces:** A reusable lifecycle-safe poller for dashboard and analytics page.
- **Checkpoint:** None.

Export this contract:

```javascript
export const POLL_INTERVAL_MS = 60000;
export const RETRY_INTERVALS_MS = Object.freeze([120000, 300000]);
export function CreateAffiliateAnalyticsPoller(dependencies) { /* tested implementation */ }
```

The returned object exposes `Start`, `Stop`, `Poll`, `RetryNow`, `HandleVisibilityChange`, and `IsStopped`. Start fetches immediately and then every 60 seconds. Hidden tabs clear timers and do not fetch; visibility restoration fetches immediately. Only one request is active. Each request receives an AbortSignal or monotonically increasing request ID; an older response cannot render after a newer response. Failures retain prior UI state, call `HandleError`, and schedule 120 seconds then 300 seconds maximum. Success calls `HandleData`, clears the warning, resets to 60 seconds, and schedules the next request. Authentication/authorization failures stop polling. Manual retry aborts/invalidates any older request and fetches immediately.

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Test immediate start, 60-second success, hidden pause, visible resume, no overlap, stale-response suppression, 120/300 backoff cap, success reset, auth stop, manual retry, and listener cleanup.
- [ ] Run `node --test tests\JavaScript\affiliate-analytics-poller.test.mjs`; expect RED because the module does not exist.
- [ ] Implement the dependency-injected module without DOM coupling.
- [ ] Rerun the command; expect all poller tests to pass with no real timers/network.
- [ ] Refactor timer/request state into closures with PascalCase named callbacks and rerun.

## Task 18: Build the accessible analytics page and dashboard client renderer

**Files:**

- Create: `resources/views/affiliate/analytics/index.blade.php`
- Create: `public/js/affiliate-analytics-page.js`
- Create: `public/css/affiliate-portal.css`
- Modify: `resources/views/affiliate/dashboard.blade.php` at `style` and `script` sections
- Create: `tests/Feature/Affiliate/AffiliateAnalyticsPageTest.php`
- Create: `tests/JavaScript/affiliate-analytics-page.test.mjs`

- **Consumes:** Tasks 14, 16, and 17 plus local Chart.js 2.9.4.
- **Produces:** Server-first analytics UI with date controls, chart/table parity, pagination, and shared refresh rendering.
- **Checkpoint:** The chart is enhancement only; all daily data remains available in a table.

The Blade page must render the default 30-day result before JavaScript. Include 7/30/90 presets, strict custom dates, six KPI regions, daily canvas, equivalent daily table, recent-sales table with 25-row pagination controls, top-media table plus Direct/Unknown summary, empty states, generated time, manual refresh, and polite live error/status regions.

Load `public/plugins/chart.js/Chart.min.js` before the module through the existing `@section('script')` yield. Serialize initial data and translations with escaped `@json` in `application/json` script elements. Never build HTML from server strings.

`affiliate-analytics-page.js` must:

- parse and validate the embedded JSON shape;
- use `textContent`, DOM creation, and safe URL APIs only;
- format counts, decimal money strings, percentages, and ISO dates through browser `Intl` using the current page language, with deterministic fallbacks;
- update KPI values, evidence labels, tables, pagination, chart, and timestamps from one payload;
- preserve the last data on error and announce a localized warning;
- use the Task 17 poller on both dashboard summary and full page;
- destroy/replace Chart.js instances without leaking handlers;
- respect reduced motion and never communicate status by color alone.

The CSS must implement the verified token palette, visible `:focus-visible`, minimum 44px action targets, horizontal table overflow, mobile single-column flow, flat evidence-ledger borders, and reduced-motion rules. It must stay under the `.affiliate-portal` scope to avoid unrelated AdminLTE changes.

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Feature-test default dates, server KPIs, semantic headings/captions, live regions, local chart asset, table alternative, escaped data, and locale attributes.
- [ ] JavaScript-test formatting, safe text insertion, payload replacement, chart teardown, pagination actions, error retention, and dashboard-only rendering.
- [ ] Run `C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --do-not-cache-result tests\Feature\Affiliate\AffiliateAnalyticsPageTest.php`; expect RED.
- [ ] Run `node --test tests\JavaScript\affiliate-analytics-page.test.mjs`; expect RED.
- [ ] Implement Blade, module, and scoped CSS.
- [ ] Rerun both commands; expect exit code 0 and no DOM tests to require network access.
- [ ] Refactor shared render functions to take explicit roots and rerun PHP/Node tests.

## Task 19: Query and render the authenticated media library

**Files:**

- Create: `app/Http/Requests/Affiliate/AffiliateMediaFilterRequest.php`
- Create: `app/Domain/Affiliate/Queries/AffiliateMediaQuery.php`
- Create: `app/Http/Controllers/Affiliate/AffiliateMediaController.php`
- Create: `resources/views/affiliate/media/index.blade.php`
- Modify: `routes/affiliate.php`
- Create: `tests/Feature/Affiliate/AffiliateMediaLibraryTest.php`

- **Consumes:** Tasks 2, 5, 12, and 16.
- **Produces:** `GET /affiliate/media` with server-controlled campaign links and bounded active media results.
- **Checkpoint:** The query must not reference `Modules\Affiliate\Entities\AffiliateLink`.

The route uses `auth`, `verified`, `affiliate.locale`, `affiliate.correlation`, and `affiliate.feature:media_library`; name it `affiliate.media.index`. The controller delegates ownership to `AffiliateMediaQuery::ForUser(User $user, ?string $search, ?string $type): LengthAwarePaginator`.

Validate search as a trimmed maximum-100-character string and type as null/image/video. The query resolves the active profile, selects enabled and unarchived content whose start is null/past and end is null/future, filters safely, orders newest then ID, and paginates 24. It must generate each tracking URL on the server from the current profile referral code and immutable campaign code. It must never accept a destination or profile ID from the request.

The view includes shared navigation, search, type filter, result count, image/video preview, escaped title/caption, publication status, download link, caption-copy button, tracking-link-copy button, and browser-share button. Empty state tells the user how to clear filters. The direct asset URL is for download/preview only; the distributed URL is always the tracking route.

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Test auth/verification/active-profile/flag gates, owner-specific link generation, search/type, publication boundaries, disabled/archived exclusion, 24-row pagination, safe escaped captions, and absence of legacy link queries.
- [ ] Run `C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --do-not-cache-result tests\Feature\Affiliate\AffiliateMediaLibraryTest.php`; expect RED.
- [ ] Implement request, query, controller, route, and server-rendered page.
- [ ] Rerun the command; expect exit code 0 and only approved current content in results.
- [ ] Refactor publication predicates into a model scope named `PublishedAt` and rerun.

## Task 20: Implement safe media copy, share, preview, and download interactions

**Files:**

- Create: `public/js/affiliate-media-library.js`
- Modify: `resources/views/affiliate/media/index.blade.php` at `script`
- Create: `tests/JavaScript/affiliate-media-library.test.mjs`

- **Consumes:** Task 19 data attributes and browser Clipboard/Share APIs.
- **Produces:** Progressive-enhancement actions with keyboard and accessible feedback.
- **Checkpoint:** Basic preview and download remain available without JavaScript.

Export pure functions `CopyText`, `ShareMedia`, `ResolveMediaAction`, and `InitializeAffiliateMediaLibrary`. Copy uses `navigator.clipboard.writeText` when available and an injected, focus-safe fallback otherwise. Share calls `navigator.share` with title, approved caption, and tracking URL; cancellation is not shown as an error. When share is unavailable or fails for a non-cancellation reason, copy the tracking URL and leave download as a normal anchor.

Buttons must take text/URLs from data attributes generated with safe Blade escaping, never from `innerHTML`. Every result is announced in a per-card or page-level `aria-live="polite"` region. Disable a pressed action only while its promise is pending and restore focus/state afterward.

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Test clipboard success/fallback/failure, native share success/cancel/fallback, caption versus link selection, pending button state, focus restoration, and text-only announcements.
- [ ] Run `node --test tests\JavaScript\affiliate-media-library.test.mjs`; expect RED because the module does not exist.
- [ ] Implement the module and attach it through `@section('script')`.
- [ ] Rerun the command; expect all tests to pass without browser globals unless injected.
- [ ] Refactor API detection into pure PascalCase functions and rerun.

## Task 21: Secure and complete the Affiliate content administration UI

**Files:**

- Modify: `routes/web.php` at the four existing `affiliate-content-manage` routes
- Modify: `routes/affiliate.php`
- Modify: `app/Http/Controllers/AffiliateContentManageController.php` at `Index`, `Store`, `Update`, `Destroy`, and `ToggleStatus`
- Modify: `resources/views/affiliate_content/index.blade.php`
- Modify: `resources/views/layouts/menu.blade.php` at the Affiliate Content link
- Create: `tests/Feature/Affiliate/AffiliateContentManagementTest.php`

- **Consumes:** Tasks 5, 8, and 9 plus the existing route-name contract.
- **Produces:** Permission-protected create/edit/toggle/archive administration with no destructive file delete.
- **Checkpoint:** Existing route names stay stable; the new update route is additive.

Remove the four declarations from `routes/web.php` and declare them once in `routes/affiliate.php` under:

```text
auth
affiliate.locale
affiliate.correlation
affiliate.feature:administration
permission:affiliate-content-manage
```

Keep route names `affiliate-content-manage.index`, `.store`, `.destroy`, and `.toggle-status`. Add `PUT /affiliate-content-manage/{affiliate_content}` as `.update`. Use route-model binding for update/archive. Rename the controller's lowercase `index` method to PascalCase `Index` and keep it thin.

The page must show active and archived rows, immutable campaign code, destination, publication window, status text, preview, create form, edit form, explicit enable/disable action, and an Archive action whose confirmation explains that distributed links stop but history/files remain. Remove physical-delete wording and inline untested AJAX. Use standard forms or the tested module pattern; all actions require CSRF and the route permission.

Wrap the menu link in `@can('affiliate-content-manage')` even when its broader Affiliate Management parent is visible. Authorization comes from routes, not menu visibility.

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Test unauthenticated redirect, ordinary authenticated 403, admin-without-permission 403, permitted Admin/SystemAdmin access, valid create/edit/toggle/archive, invalid destination/upload/campaign, immutable code, and historical file preservation.
- [ ] Assert all route names and middleware with `Route::getRoutes()` so moving declarations does not break callers.
- [ ] Run `C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --do-not-cache-result tests\Feature\Affiliate\AffiliateContentManagementTest.php tests\Feature\Images\ExtendedImageUploadWebpTest.php`; expect RED in new authorization/update/archive cases while WebP remains green.
- [ ] Implement route cutover, controller/view changes, and menu authorization.
- [ ] Rerun the command; expect exit code 0, one route declaration per name, and preserved upload regression behavior.
- [ ] Refactor repeated form fields into one Blade partial only after behavior passes; rerun.

## Task 22: Complete bilingual copy, conditional landing claims, and translation-structure checks

**Files:**

- Modify: `lang/en/affiliate.php`
- Modify: `lang/th/affiliate.php`
- Modify: `app/Http/Controllers/AffiliateController.php` at `fontend`
- Modify: `resources/views/affiliate/index.blade.php` at the capability and review guidance copy
- Modify: all Affiliate Blade files touched in Tasks 16, 18, 19, and 21
- Create: `tests/Unit/Affiliate/AffiliateTranslationStructureTest.php`
- Modify: `tests/Feature/Affiliate/AffiliateLandingPageTest.php`
- Modify: `tests/Feature/Affiliate/AffiliateErrorTranslationTest.php`

- **Consumes:** Tasks 1, 2, 16, 18, 19, and 21 plus the currently accurate 15% policy copy.
- **Produces:** Matching English/Thai translation trees and claims that advertise only enabled capabilities.
- **Checkpoint:** Do not alter public financial policy wording beyond capability accuracy.

Organize both locale files under matching namespaces: `navigation`, `dashboard`, `analytics`, `media`, `content_admin`, `payout_accounts`, `withdrawals`, `statuses`, `validation`, `feedback`, and `landing`. The recursive key paths and placeholder names must match exactly.

`AffiliateController::fontend` must pass boolean `affiliate_capabilities` for analytics and media. Public landing copy follows these exact rules:

- analytics off: no claims about click, sales, income analytics, charts, or 60-second refresh;
- analytics on: state that filtered click activity and confirmed receipt-backed sales/income analytics update near real time every 60 seconds; do not call them instantaneous or guaranteed human traffic;
- media off: no banner/media-library claim;
- media on: state that approved image/video assets, captions, downloads, and server-generated campaign links are available;
- both off: keep the currently accurate wallet/commission/withdrawal description and the guidance not to treat unavailable analytics as confirmed evidence.

Keep the public configured commission percentage and the established test that rejects the deprecated pilot label in both languages. Do not hardcode 15 in new copy.

Because PHP `intl` is unavailable, server rendering uses `Money::Decimal`, translated currency labels, Carbon locale formatting where supported, and ISO `<time datetime>` values. Client rendering uses browser `Intl` with a deterministic fallback.

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Write a recursive test that compares every leaf path and interpolation placeholder between English and Thai.
- [ ] Add landing tests for all four analytics/media flag combinations, 15% configuration behavior, 60-second wording only when enabled, and absence of the deprecated pilot label.
- [ ] Run `C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --do-not-cache-result tests\Unit\Affiliate\AffiliateTranslationStructureTest.php tests\Feature\Affiliate\AffiliateLandingPageTest.php tests\Feature\Affiliate\AffiliateErrorTranslationTest.php`; expect RED on missing keys/conditions.
- [ ] Add matching translations and replace touched hardcoded strings.
- [ ] Rerun the command; expect exit code 0 and exact key/placeholder parity.
- [ ] Refactor repeated landing conditionals into clear Blade branches, not a new generic CMS abstraction; rerun.

## Task 23: Prove concurrency, regression safety, build integrity, and staged rollout readiness

**Files:**

- Create: `tests/Feature/Affiliate/AffiliateClickConcurrencyTest.php`
- Create: `docs/verification/2026-08-11-affiliate-analytics-media-library.md`
- No production file changes are permitted in this task unless a failing test identifies an in-scope defect and the task returns to the owning RED–GREEN step.

- **Consumes:** All prior tasks.
- **Produces:** Evidence-backed handoff with no flags enabled.
- **Checkpoint:** Stop and report any pre-existing or new failure separately; never label an unverified failure as a regression.

The MySQL integration test must be explicitly opt-in through `AFFILIATE_RUN_CONCURRENCY_TESTS=true`. It opens two independent connections against an isolated test database, coordinates two first-click transactions for the same profile/hash, and proves two events, one unique event/window, and a daily total of two/unique one. It also proves an event and daily increment roll back together. SQLite runs must mark this test skipped with the exact reason that SQLite cannot verify deployed row-lock semantics.

Run verification in this order:

1. Lint every changed PHP file:

```powershell
rg --files app\Domain\Affiliate app\Http\Controllers\Affiliate app\Http\Middleware app\Http\Requests\Affiliate app\Http\Resources\Affiliate app\Console\Commands\Affiliate database\migrations tests\Feature\Affiliate tests\Unit\Affiliate -g '*.php' | ForEach-Object { C:\xampp\php\php.exe -c C:\xampp\php\php.ini -l $_ }
```

2. Run all new Affiliate unit/feature tests and existing Affiliate regression suite:

```powershell
C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --do-not-cache-result tests\Unit\Affiliate tests\Feature\Affiliate
```

3. Run existing/new image regressions:

```powershell
C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --do-not-cache-result tests\Feature\Images\ExtendedImageUploadWebpTest.php
```

4. Run all JavaScript tests:

```powershell
node --test tests\JavaScript\*.test.mjs
```

5. Verify route contracts and command discovery:

```powershell
C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan route:list --path=affiliate
C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan list affiliate
```

6. Build existing frontend assets:

```powershell
npm run build
```

7. Run the complete PHP suite only after focused suites pass:

```powershell
C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --do-not-cache-result
```

8. In a staging database with all new flags disabled, run migrations, verify deterministic content backfill/counts/indexes/permissions, run `affiliate:preflight --json`, and perform rollback rehearsal. This requires separate deployment authorization and must not be run against production from the implementation session.

9. With separate staging authorization, enable in order: click tracking, analytics, media library. Verify one first click, one repeat, one confirmed sale, one reversal, one media share/redirect/attribution flow, retention dry evidence, and sanitized logs.

10. Use the in-app browser workflow to smoke-test Thai and English at mobile and desktop widths. Verify keyboard-only navigation, visible focus, chart/table equivalence, live refresh warning, copy/share fallback, and no console error. Do not claim browser verification unless screenshots/observations were actually collected.

11. Review `git diff --check`, `git status --short`, migration ordering, translation parity, and all test outputs. Confirm no legacy tracking write, raw visitor data, obsolete commission-rate copy, or deprecated pilot label was introduced.

12. Write actual commands, counts, pass/fail outcomes, skipped MySQL evidence, browser evidence, and remaining external unknowns to the verification document. Keep the approved specification unchanged. Do not mark implementation complete while any required in-scope check is failing or unrun without a documented reason.

## Requirement Traceability

| Approved requirement | Owning tasks |
| --- | --- |
| Disabled-by-default rollout and privacy configuration | 1, 12, 23 |
| Thai default and English preference | 2, 22 |
| Raw events, rolling unique windows, daily aggregates | 3, 7, 10 |
| Attribution/campaign snapshot durability | 4, 11, 15 |
| Managed destination/campaign/publication/archive | 5, 8, 9, 21 |
| Direct and media tracking links | 10, 12 |
| First-touch compatibility and old cookies | 11, 12 |
| Approved sales/net-income/cohort/top-media definitions | 6, 13, 14 |
| Dashboard 30-day summary and shared navigation | 16–18 |
| Analytics page and 60-second resilient refresh | 14, 17, 18 |
| Media library search/filter/copy/share/download | 19, 20 |
| Dedicated content permission | 5, 21 |
| 13-month retention and permanent aggregates | 15 |
| Conditional public claims | 22 |
| Accessibility, responsive behavior, and chart alternative | 18–20, 23 |
| No legacy import/write | Global constraints, 13, 19, 23 |
| Concurrency and full regression evidence | 10, 15, 23 |

## Approval and Execution Handoff

This plan changes no production implementation by itself. After explicit plan approval, start execution in a separate isolated worktree using `git-worktrees`, then use `executing-plans`. Every production-behavior task must use `test-driven-development`, and completion claims must use `verification-before-completion`.
