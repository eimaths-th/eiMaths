# Affiliate Analytics and Media Library Design

**Date:** 2026-08-11

**Design approval:** Approved by the user on 2026-08-11

**Specification approval:** Approved by the user on 2026-08-11

**Implementation status:** Not started

## 1. Purpose

This specification defines the Affiliate analytics, click tracking, dashboard summary, and marketing media library that must exist before the public Affiliate page advertises those capabilities. The design extends the current Affiliate domain and does not reactivate the packaged legacy Affiliate module.

The feature provides:

- a 30-day analytics summary on the existing Affiliate dashboard;
- a dedicated Affiliate analytics page;
- a dedicated Affiliate media library;
- first-party tracking links for direct referrals and media campaigns;
- verified sales and net commission metrics sourced from the current commission domain; and
- near-real-time display updates every 60 seconds.

The phrase "near real time" means that accepted clicks are persisted during the tracking request and the authenticated dashboards request refreshed data every 60 seconds. The product must not describe this behavior as instantaneous real-time streaming.

## 2. Verified Current State

Repository and local runtime inspection on 2026-08-11 established the following:

- `AffiliateDashboardQuery` currently returns an active profile, wallet, commissions, and withdrawals only.
- The current Affiliate dashboard renders available and reserved balances, commission history, and withdrawal history.
- `AffiliateContent`, its management controller, its management page, and an Affiliate-facing media view already exist.
- The Affiliate-facing media controller is not connected to a runtime Affiliate route and resolves referral links through the legacy `Modules\Affiliate\Entities\AffiliateLink` model.
- The current referral service reads `?ref=...`, issues a first-touch cookie, binds an attribution after a contact is identified, and snapshots that attribution to an order. It does not persist every anonymous click.
- The legacy `affiliate_links` and `affiliate_link_visit_track_users` tables are separate from `AffiliateProfile` and the current attribution domain. The legacy tracker matches complete URLs and persists raw IP and user-agent-derived values.
- The local database contained 27 legacy Affiliate links, zero legacy visit rows, one Affiliate content row, zero current attributions, and zero current commissions at inspection time. These local counts are not evidence of Production data.
- Runtime route inspection showed no authenticated Affiliate media-library route.
- The existing Affiliate content-management routes are protected by authentication but do not have a dedicated content-management permission.

Because Production legacy click volume and quality are unknown, this design does not import or merge legacy click counts. Existing current-domain commissions remain visible, while historical commissions without campaign evidence are categorized as `Direct / Unknown`.

## 3. Approved Product Decisions

The user approved the following decisions during design review:

1. Deliver the complete MVP: dashboard summary, analytics page, and media library.
2. Treat only receipt-backed commission records as confirmed Affiliate sales.
3. Update analytics automatically every 60 seconds, with manual refresh and last-updated feedback.
4. Show total clicks and rolling 24-hour unique visitors.
5. Exclude known bots and authenticated owner self-clicks from accepted metrics.
6. Give every media item an administrator-controlled destination and a stable campaign code.
7. Build new tracking and analytics inside the current Affiliate domain instead of reusing or dual-writing the legacy module.
8. Preserve first-touch attribution.
9. Use click-cohort conversion rather than dividing unrelated activity from the same calendar period.
10. Retain raw click events for 13 months and permanent daily aggregate counts.
11. Support Thai and English through locale-based translation files, with Thai as the default locale.
12. Use text-only design review; no wireframe is required.

## 4. Goals

- Let an active Affiliate see trustworthy clicks, confirmed sales, eligible sales value, and net commission income.
- Keep click analytics informational while preserving commission and receipt records as the financial source of truth.
- Attribute conversions to the first valid referral campaign without overwriting an unexpired first touch.
- Give Affiliates approved images and videos with safe, referral-aware share links.
- Keep every Affiliate query scoped to the authenticated user's active profile.
- Preserve existing wallet, commission, payout-account, and withdrawal behavior.
- Minimize personal data and never store raw IP addresses, full user agents, or arbitrary incoming query strings in analytics tables.
- Keep long-term daily trends after raw click-event retention expires.
- Permit a controlled, independently reversible rollout through feature flags.

## 5. Non-Goals

- WebSocket, Server-Sent Events, or other push-streaming infrastructure
- Importing, reconciling, or displaying legacy Affiliate click counts
- Reactivating or extending the packaged legacy Affiliate module
- Last-touch, multi-touch, or fractional conversion attribution
- Changing commission, receipt, refund, wallet, or withdrawal business rules
- Treating clicks as financial evidence
- Guaranteeing detection of every automated client or anonymous owner self-click
- Letting Affiliates upload or edit marketing assets
- External social-platform API integrations or storage of social-platform credentials
- Building a general-purpose campaign-management or UTM analytics product
- Recalculating historical commission records

## 6. User Experience

### 6.1 Shared Affiliate navigation

Authenticated Affiliate pages use one shared navigation with these destinations:

- Dashboard
- Analytics
- Media
- Payout accounts
- Withdrawals

Navigation items are rendered only when the corresponding feature is enabled and the authenticated user has an active Affiliate profile.

### 6.2 Affiliate dashboard

`GET /affiliate/dashboard` retains the current wallet, commission-history, and withdrawal-history behavior. It adds:

- the active profile's referral link and a copy action;
- a 30-day summary for total clicks, unique clicks, confirmed sales, eligible sales value, and net commission income;
- a last-updated timestamp;
- a manual refresh action; and
- a link to the full analytics page.

Only the summary region refreshes automatically. The existing paginated commission and withdrawal tables are not replaced or reset during polling.

### 6.3 Affiliate analytics

`GET /affiliate/analytics` provides:

- date presets for 7, 30, and 90 days;
- an inclusive custom date range of at most 366 days;
- a default 30-day range;
- KPI cards for total clicks, unique clicks, confirmed sales, eligible sales value, net commission income, and click-cohort conversion;
- a daily trend chart;
- the latest 25 confirmed sales for the selected range;
- the ten highest-performing media campaigns; and
- an accessible empty state when no data exists.

The default 30-day range includes the current Asia/Bangkok date and the preceding 29 dates. Date boundaries use `Asia/Bangkok`. Persisted event timestamps remain UTC. The daily trend contains total clicks, unique clicks, confirmed sales, and net commission income for each date; its accessible table exposes the same series.

### 6.4 Affiliate media library

`GET /affiliate/media` provides:

- text search;
- image/video filtering;
- an asset preview;
- title and approved caption;
- download;
- caption copy;
- tracking-link copy; and
- browser share with copy/download fallbacks.

The page shows only enabled, non-archived content whose publication window contains the current time. The application generates the tracking link; an Affiliate cannot replace the configured destination.

### 6.5 Affiliate content administration

The existing `/affiliate-content-manage` interface remains the administration entry point. It adds:

- destination target;
- stable campaign code;
- optional publication start and end times;
- edit support; and
- archive behavior instead of destructive record deletion.

A campaign code is required, unique, lowercase, and limited to ASCII letters, digits, and hyphens. It may be chosen at creation and becomes immutable after the record is first saved so previously distributed links remain valid.

The destination target is either:

- a relative application path beginning with `/`; or
- an absolute HTTPS URL whose host is in `affiliate.allowed_destination_hosts`.

The application host is allowed by default. No other host is allowed by default.

## 7. Application Responsibilities

### 7.1 `AffiliateClickTrackingService`

This service:

- validates the active Affiliate profile and optional active media campaign;
- classifies known bots and authenticated owner self-clicks;
- derives a keyed visitor hash without persisting its source values;
- assigns or reuses a rolling 24-hour unique-window key;
- appends an accepted click event;
- increments the corresponding permanent daily metric;
- returns optional click context for the referral cookie; and
- never changes an existing valid first-touch owner.

The service does not calculate sales or commission.

### 7.2 `AffiliateAttributionService`

The existing service remains the owner of first-touch cookie issuance, contact binding, and order attribution. Its cookie and attribution contracts gain optional click, unique-window, content, and campaign context. Older cookies without these fields remain valid under their existing expiry rules.

Click recording and attribution ownership are separate decisions:

- a valid later referral click may count for the later Affiliate's click analytics; and
- that click must not replace a different Affiliate's unexpired first-touch attribution.

### 7.3 `AffiliateAnalyticsQuery`

This read-only query:

- resolves the active profile from the authenticated user;
- validates and normalizes the selected date range;
- calculates the approved KPI definitions;
- produces daily trend points;
- returns recent confirmed sales and top-media rows; and
- never accepts a caller-selected Affiliate profile identifier.

The query uses bounded result sets and indexed range predicates. It does not mutate aggregates or financial data.

### 7.4 `AffiliateMediaQuery`

This read-only query returns active content and server-generated tracking links for the authenticated Affiliate profile. It does not query `Modules\Affiliate\Entities\AffiliateLink`.

### 7.5 `AffiliateContentService`

This administration service owns:

- content validation;
- safe image/video storage;
- destination validation;
- campaign-code creation and immutability;
- publication scheduling;
- status changes; and
- archival with actor evidence.

Controllers do not move or delete media files directly.

### 7.6 Controllers and commands

- `AffiliateTrackingController` handles public tracking routes and safe redirects.
- `AffiliateAnalyticsController` renders the page and returns scoped refresh data.
- `AffiliateMediaController` renders the active library.
- The existing dashboard controller consumes an added 30-day summary query.
- The content-management controller delegates writes to `AffiliateContentService`.
- A scheduled pruning command deletes raw click events older than 13 calendar months in bounded batches.

## 8. Public Tracking and Attribution Flow

### 8.1 Tracking routes

Add these public GET routes:

- `/r/{referral_code}` for a direct referral; and
- `/r/{referral_code}/{campaign_code}` for a media campaign.

Both routes use bounded route patterns, the `web` middleware stack, Affiliate correlation context, and a dedicated configurable rate limiter.

### 8.2 Successful media request

For a valid active profile and an enabled, published campaign:

1. Resolve and validate the configured destination.
2. Evaluate known-bot and authenticated-owner exclusions.
3. Record an accepted click and update the daily metric in one transaction.
4. Preserve an existing valid first-touch cookie or issue a new referral cookie containing optional click context.
5. Redirect with `302 Found` to the configured destination without adding a second `ref` query parameter.

The destination request must not record the same tracking-route click again.

### 8.3 Direct `?ref=` compatibility

Existing URLs containing `?ref={referral_code}` remain supported. A valid request records a `direct` click once for that HTTP request and then applies the existing first-touch cookie rule. Invalid referral input does not block the requested page and does not create an event or cookie.

### 8.4 Invalid or inactive tracking link

An invalid, inactive, archived, disabled, not-yet-published, or expired tracking link:

- creates no click event;
- creates no referral cookie;
- emits no resource-existence detail to the visitor; and
- redirects with `302 Found` to the application home page.

### 8.5 Analytics-write failure

After the profile and destination have been validated, a click-write failure must not break the visitor journey or discard otherwise valid referral ownership. The boundary:

- logs a structured failure without visitor input or secrets;
- issues or preserves referral context without click/campaign evidence; and
- redirects to the validated destination.

A later confirmed sale from that fail-soft path appears under `Direct / Unknown`.

## 9. Data Model

All new timestamps are stored in UTC. New source-code identifiers and serialized fields follow the repository's naming conventions. Click events are immutable after their creation transaction completes.

### 9.1 `affiliate_click_events`

| Column | Purpose |
| --- | --- |
| `id` | Primary key |
| `affiliate_profile_id` | Required credited profile; restrictive foreign key |
| `affiliate_content_id` | Nullable media relationship; null on a future hard delete |
| `campaign_code` | Nullable immutable campaign snapshot |
| `source_type` | `direct` or `media` |
| `destination_target` | Normalized destination snapshot without arbitrary incoming query data |
| `visitor_hash` | Keyed HMAC digest; never returned by application interfaces |
| `unique_window_key` | Random opaque key shared by accepted clicks in one rolling window |
| `is_unique` | Whether this event opened a new 24-hour window |
| `clicked_at` | Authoritative event time |
| `created_at` | Persistence time |

Required indexes are:

- `(affiliate_profile_id, clicked_at)`;
- `(affiliate_profile_id, is_unique, clicked_at)`;
- `(affiliate_profile_id, campaign_code, clicked_at)`;
- `(affiliate_profile_id, visitor_hash, clicked_at)`; and
- `unique_window_key`.

No update or ordinary delete application method is exposed for this model. Retention deletion is the only allowed deletion path.

### 9.2 `affiliate_visitor_windows`

| Column | Purpose |
| --- | --- |
| `affiliate_profile_id` | Profile-specific uniqueness scope |
| `visitor_hash` | Keyed visitor digest |
| `unique_window_key` | Current opaque cohort key |
| `last_unique_at` | Start of the current rolling window |
| `last_seen_at` | Last accepted click time |

`(affiliate_profile_id, visitor_hash)` is unique. The service locks this row when assigning uniqueness. A click at or after `last_unique_at + 24 hours` opens a new window and receives `is_unique=true`; an earlier click reuses the current key and receives `is_unique=false`. Duplicate-key conflicts during first creation use a bounded retry.

Rows not seen for 48 hours may be pruned. This table is operational deduplication state, not reporting history.

### 9.3 `affiliate_click_daily_metrics`

| Column | Purpose |
| --- | --- |
| `affiliate_profile_id` | Credited profile |
| `metric_date` | Calendar date in Asia/Bangkok |
| `dimension_key` | Non-null stable key: `direct` or `media:{campaign_code}` |
| `affiliate_content_id` | Nullable current media relationship |
| `campaign_code` | Nullable campaign snapshot |
| `total_clicks` | Non-negative integer total |
| `unique_clicks` | Non-negative integer total |
| timestamps | Aggregate lifecycle timestamps |

`(affiliate_profile_id, metric_date, dimension_key)` is unique. The click transaction uses an atomic increment/upsert. Summing all dimension rows produces the profile total for a date. A repeated visitor contributes a unique click only to the campaign that opened the current Affiliate-specific unique window.

### 9.4 `affiliate_attributions` additions

Add nullable fields:

- `affiliate_click_event_id`, with a foreign key that becomes null when retention deletes the event;
- `affiliate_content_id`, with a nullable media relationship;
- `affiliate_campaign_code`, as an immutable snapshot;
- `affiliate_source_type`;
- `affiliate_unique_window_key`; and
- `affiliate_captured_destination`.

These values are written only when a new attribution is created. They are not overwritten when the first-touch attribution remains valid. Snapshot fields survive raw event retention and media archival.

### 9.5 `affiliate_contents` additions

Add:

- `destination_target`;
- `campaign_code`, unique;
- `publish_starts_at`, nullable;
- `publish_ends_at`, nullable;
- `updated_by`, nullable;
- `archived_by`, nullable;
- `archived_at`, nullable; and
- soft-delete timestamps.

Existing rows are backfilled with `/` as the safe destination and `legacy-{id}` as the stable campaign code. Existing enabled status remains unchanged. Backfill is deterministic and idempotent.

An archived record remains available to historical analytics but does not appear in the Affiliate media library and does not accept new campaign clicks.

### 9.6 Referral cookie additions

The encrypted first-party referral cookie retains its current required profile and time fields and adds optional:

- `click_event_id`;
- `content_id`;
- `campaign_code`;
- `source_type`;
- `unique_window_key`; and
- `destination_target`.

Deserialization accepts the old cookie shape. Missing optional values map to `Direct / Unknown`; a malformed or expired cookie remains invalid under the existing behavior.

## 10. Metric Definitions

All money remains integer minor units internally and is formatted using the application's configured currency and locale.

### 10.1 Click metrics

- `total_clicks` is the count of accepted click events in the selected click-time range.
- `unique_clicks` is the count of accepted events where `is_unique=true` in the selected click-time range.
- Rejected known-bot and authenticated-owner requests do not increment either value.

### 10.2 Confirmed sales metrics

- `confirmed_sales` is the count of `affiliate_commissions` created in the selected confirmation-time range.
- `eligible_sales_value_minor` is the sum of `eligible_tuition_minor` for those commission records.
- `net_commission_income_minor` is the sum of `commission_minor - reversed_minor` for those commission records.

A reversal does not erase a confirmed sale or its original eligible sales value. It reduces net commission income and is presented with a reversed or partially reversed status where applicable.

### 10.3 Click-cohort conversion

The selected range defines a cohort of `unique_window_key` values opened by unique clicks during that range. The numerator is the count of distinct attributed contacts in that cohort that have at least one confirmed Affiliate commission by report-generation time. The denominator is the number of unique windows in the cohort.

```text
conversion_rate = converted_distinct_contacts / unique_clicks * 100
```

When `unique_clicks` is zero, conversion is `0%`. The result is not artificially capped. A value above 100% is possible only when one browser cohort produces multiple distinct contacts and must be treated as a data-quality or abuse signal.

This conversion value may increase after the selected click period ends when cohort members complete a later purchase. The UI explains that behavior in the metric help text.

### 10.4 Top media

Top-media rows group by the immutable attribution campaign snapshot and report:

- total clicks;
- unique clicks;
- distinct converted contacts;
- confirmed sales;
- eligible sales value; and
- net commission income.

Click columns use click time inside the selected range. Converted contacts, confirmed sales, eligible sales value, and net commission income use commissions confirmed inside the selected range and group them by their first-touch attribution snapshot. Rows rank by net commission income descending, then confirmed sales descending, then campaign code ascending. The endpoint returns the first ten media campaigns. When non-zero, `Direct / Unknown` is returned as a separately labelled summary row and is not allowed to displace a known media campaign from the top ten. Historical records without a snapshot are never assigned to a current media item by inference.

## 11. Authenticated Interfaces

### 11.1 Page routes

- `GET /affiliate/analytics` requires `auth`, `verified`, Affiliate correlation context, an active profile, and the `analytics` feature.
- `GET /affiliate/media` requires `auth`, `verified`, Affiliate correlation context, an active profile, and the `media_library` feature.
- Existing dashboard, payout-account, and withdrawal route names remain stable.

### 11.2 Analytics data endpoint

`GET /affiliate/analytics/data` accepts:

- `from`: required `YYYY-MM-DD`;
- `to`: required `YYYY-MM-DD`; and
- `sales_page`: optional positive integer.

The authenticated user's active profile is the only ownership source. `from` must be on or before `to`, neither date may be after the current Asia/Bangkok date, and the inclusive range may not exceed 366 days.

The successful JSON response uses snake_case keys:

```json
{
  "data": {
    "range": {
      "from": "2026-07-13",
      "to": "2026-08-11",
      "timezone": "Asia/Bangkok"
    },
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
    "top_media": [],
    "generated_at": "2026-08-11T00:00:00Z"
  }
}
```

Money is serialized as a decimal string. The endpoint returns at most 366 daily points, 25 sales rows per page, and ten top-media rows.

### 11.3 Administration interfaces

The existing content-management routes gain the dedicated `affiliate-content-manage` permission. Add an authenticated `PUT /affiliate-content-manage/{affiliate_content}` route for editable metadata. Existing delete behavior becomes archive behavior while preserving its route contract where practical.

The permission migration assigns the new permission only to existing `SystemAdmin` and `Admin` roles, matching the current Affiliate administration migration pattern. No other role receives it automatically. Assignment is verified during deployment; ordinary authenticated users do not inherit it.

## 12. Refresh Behavior

- Server-rendered initial values are authoritative for the first paint.
- Visible dashboard and analytics summary regions request fresh data every 60 seconds.
- Polling pauses while the browser tab is hidden.
- A failure retains the last successful values and exposes an `aria-live="polite"` warning.
- Automatic retries back off to 120 seconds and then 300 seconds.
- A successful response restores the 60-second interval.
- Manual refresh retries immediately without reloading or resetting the whole page.
- Concurrent refresh requests are cancelled or ignored so an older response cannot overwrite a newer response.

## 13. Security and Privacy

- Public tracking routes use a dedicated rate limiter configurable under `affiliate.abuse`.
- Analytics and media queries derive ownership from the authenticated user and never trust request profile IDs.
- Content administration requires a dedicated permission in addition to authentication.
- Destination validation prevents open redirects.
- The visitor digest uses HMAC-SHA-256 with a required `AFFILIATE_VISITOR_HASH_KEY` secret stored outside source control.
- The digest input may use request IP and normalized user-agent information transiently, but neither input is persisted or logged.
- The hash secret is not the application key. Changing it resets deduplication identity without changing historical aggregate totals.
- Cookies remain encrypted, HttpOnly, SameSite=Lax, path `/`, and Secure under HTTPS.
- Bot classification operates on request data before persistence and stores no full user agent.
- Authenticated self-clicks for the same Affiliate profile are rejected. Anonymous or incognito owner activity cannot be identified reliably and may be counted.
- Uploaded files are validated by server-observed MIME type, extension allowlist, and size. Images use the existing image storage service. Videos receive generated filenames and use a focused storage boundary with cleanup on failure.
- Marketing files are public assets by design; they contain no secrets or personalized data.
- Blade output remains escaped. JavaScript receives captions and URLs through safe JSON serialization rather than HTML string interpolation.
- Structured logs exclude raw IPs, visitor hashes, cookies, captions, arbitrary URLs, and secrets.

Known-bot filtering is heuristic. UI help text describes clicks as filtered analytics, not guaranteed human traffic or financial evidence.

## 14. Failure and Edge-Case Rules

- Missing data renders zero-value cards and an explanatory empty state.
- Invalid date ranges return `422 Unprocessable Entity` with a corrective message.
- Missing authentication returns `401`; insufficient lifecycle or permission returns `403`; an unavailable scoped resource returns `404`.
- An unexpected analytics refresh failure returns a non-sensitive `500` response and does not replace the last successful client state.
- A disabled or archived content item is excluded immediately from new media queries and tracking acceptance.
- An existing distributed media URL uses the campaign's current approved destination until that campaign is archived.
- Campaign codes cannot be reassigned to a different content record.
- An Affiliate profile suspension blocks new accepted clicks and media access but does not erase historical events or earned financial records.
- Financial status remains sourced from commission and reversal records; daily click aggregates never alter wallet or commission state.
- Current direct referral links continue to work even when the media-library feature is disabled, provided referral capture remains enabled.

## 15. Retention and Maintenance

- Raw `affiliate_click_events` are retained for 13 calendar months from `clicked_at`.
- A scheduled command prunes expired events daily in bounded batches using indexed timestamps.
- Daily click metrics are retained permanently.
- Attribution campaign snapshots and financial records follow the Affiliate financial-evidence retention policy and are not deleted by click pruning.
- Visitor-window rows not seen for 48 hours may be pruned independently.
- The pruning command is retry-safe and reports deleted counts, duration, and failure without logging visitor identifiers.
- Daily aggregates can be rebuilt from raw events while those events remain inside retention. A protected reconciliation command performs the rebuild for an explicit Affiliate/date range and records an audit event.

## 16. Internationalization and Accessibility

- New user-visible strings are translation keys with matching Thai and English locale structures.
- Thai is the default locale; authenticated preference controls the selected locale.
- Dates, numbers, and currency follow the selected locale while report boundaries remain Asia/Bangkok.
- Headings use a logical hierarchy and tables have captions or programmatic labels.
- Every action is keyboard accessible and has a visible focus state.
- Success, refresh, empty, and error feedback is announced accessibly.
- Status never relies on color alone.
- Controls maintain usable target size and spacing on mobile.
- Chart data also has a textual/table equivalent so the information is not visual-only.
- Motion respects reduced-motion preferences.

## 17. Performance and Concurrency

- Click insertion, unique-window locking, and daily metric increment occur in one database transaction.
- The visitor-window row is locked before the event and aggregate are committed.
- Duplicate-key conflicts during concurrent first visits use bounded retries.
- Unique constraints remain the final concurrency boundary.
- Click routes perform constant-size indexed reads and writes and do not load historical event collections.
- Analytics uses indexed profile/date filters, paginated sales, bounded top-media output, and no N+1 relationship loading.
- Polling refreshes only the analytics payload, not the complete Blade page.
- Custom ranges are limited to 366 days.
- Retention and reconciliation commands operate in configurable bounded batches and prevent overlapping runs.

## 18. Observability

Required structured events include:

- `affiliate.click.accepted`;
- `affiliate.click.rejected` with a stable non-sensitive reason code;
- `affiliate.click.failed`;
- `affiliate.analytics.refresh_failed`;
- `affiliate.content.created`;
- `affiliate.content.updated`;
- `affiliate.content.archived`; and
- `affiliate.click.retention_completed`.

Every event includes correlation ID, operation, outcome, profile identifier when authorized and appropriate, campaign code when non-sensitive, and server timestamp. It excludes visitor input and secrets.

Required operational measures include:

- accepted/rejected/failed click counts;
- click-write failure rate;
- daily-aggregate freshness;
- analytics query latency and error rate;
- polling error rate;
- raw-event retention backlog; and
- conversion values above 100% as a data-quality signal.

Alert thresholds and owners are deployment configuration, not source-code constants.

## 19. Feature Flags and Rollout

Add disabled-by-default flags:

- `AFFILIATE_FEATURE_CLICK_TRACKING`;
- `AFFILIATE_FEATURE_ANALYTICS`; and
- `AFFILIATE_FEATURE_MEDIA_LIBRARY`.

Rollout order is:

1. Deploy additive schema, code, translations, permissions, and indexes with all three flags disabled.
2. Backfill existing content destination and campaign values.
3. Verify migration counts, constraints, route authorization, and rollback on staging.
4. Enable click tracking and verify accepted/rejected/failure signals plus direct `?ref=` compatibility.
5. Enable analytics and validate one accepted click, one repeat click, one confirmed sale, and one reversal.
6. Enable the media library and validate an active media campaign from copy/share through redirect and attribution.
7. Run browser smoke tests in Thai and English on desktop and mobile widths.
8. Expand public Affiliate landing-page copy only after all relevant flags are enabled and smoke tests pass.

Public copy must say that analytics are updated near real time every 60 seconds. When analytics or media is disabled, public copy must not advertise the unavailable capability.

Rollback disables the affected feature flag and preserves additive data. It does not reactivate legacy click tracking, delete analytics history, or roll back financial records.

## 20. Test Requirements

### 20.1 Unit tests

Unit coverage proves:

- HMAC visitor derivation does not expose source values;
- exact rolling 24-hour boundaries;
- known-bot and authenticated-owner rejection;
- unique-window reuse and renewal;
- date normalization at Asia/Bangkok boundaries;
- destination allowlist validation;
- media publication-window inclusion and exclusion;
- confirmed-sales, eligible-sales, net-commission, and cohort-conversion formulas;
- zero-denominator conversion behavior;
- reversal handling; and
- Thai and English formatting keys remain structurally consistent.

### 20.2 Feature tests

Feature coverage proves:

- direct and media tracking routes accept valid active profiles;
- invalid, inactive, disabled, archived, early, and expired links create no metric or cookie;
- direct `?ref=` compatibility remains intact;
- a later Affiliate click does not replace an unexpired first touch;
- old cookie shapes remain readable;
- tracking-write failure preserves a valid referral journey without fabricating campaign evidence;
- analytics and media access require an authenticated, verified, active Affiliate;
- one Affiliate cannot request another Affiliate's data;
- analytics date validation and result bounds are enforced;
- confirmed sales and reversals use current commission evidence;
- historical records without campaign context are `Direct / Unknown`;
- media search/filter/share links use only active content;
- content administration requires its dedicated permission;
- campaign codes are unique and immutable;
- unsafe destinations and invalid uploads are rejected; and
- archive preserves historical reporting and blocks new campaign clicks.

### 20.3 Database and concurrency tests

Tests prove:

- two concurrent first clicks produce two total events but one unique window;
- an event and its daily increment commit together or roll back together;
- a retry cannot double-increment the daily aggregate;
- unique and foreign-key constraints reject invalid records;
- raw-event pruning leaves daily totals and attribution snapshots intact;
- visitor-window pruning does not alter historical reports; and
- content backfill is deterministic and idempotent.

### 20.4 Browser and JavaScript tests

Tests cover:

- 60-second polling without a full-page reload;
- hidden-tab pause and bounded backoff;
- stale-response protection;
- manual retry;
- referral/caption copy;
- browser-share fallback;
- image and video preview/download;
- Thai and English rendering;
- keyboard navigation, visible focus, accessible announcements, and chart alternatives; and
- responsive behavior at supported mobile and desktop widths.

### 20.5 Regression verification

The focused Affiliate suite must pass. The full relevant suite must confirm no regression to:

- current referral capture;
- commission creation and reversal;
- wallet balances;
- payout accounts;
- withdrawals;
- Affiliate administration; and
- existing content image upload behavior.

Pre-existing failures are reported separately and are not described as new regressions without evidence.

## 21. Acceptance Criteria

The feature is accepted when all of the following are true:

- the shared Affiliate navigation exposes Dashboard, Analytics, Media, Payout accounts, and Withdrawals as permitted by feature state;
- the existing dashboard retains wallet, commission, and withdrawal behavior and adds an independently refreshing 30-day summary;
- the analytics page provides the approved KPI, chart, sales, and top-media data for a validated range;
- the media page exposes only currently published content and generates server-controlled referral/campaign links;
- two accepted clicks by the same visitor and Affiliate inside 24 hours produce `total_clicks=2` and `unique_clicks=1`;
- a later Affiliate click does not replace an unexpired first-touch attribution;
- a receipt-backed commission appears as a confirmed sale and uses persisted eligible tuition and commission values;
- a reversal reduces net commission income without erasing the confirmed sale;
- click-cohort conversion uses unique-window cohorts and distinct converted contacts;
- raw click data contains no raw IP, full user agent, cookie, or arbitrary query string;
- raw events older than 13 months are pruned while daily totals and campaign snapshots remain;
- analytics refreshes every 60 seconds, pauses in hidden tabs, and retains the last successful state on error;
- every authenticated query is scoped to the current Affiliate and passes authorization tests;
- content administration requires explicit permission and rejects unsafe destinations and uploads;
- Thai and English interfaces have matching translation keys and pass accessibility checks;
- legacy click tables are neither imported nor written by the new feature;
- feature flags permit safe enablement and rollback without deleting data; and
- public landing copy advertises analytics and media only after the enabled feature passes smoke verification.
