# FAQ Search, Stable URLs, and Read Analytics

Status: The user approved the combined conversational design, this written specification, and the move to implementation planning on 2026-10-20. Implementation plans still require approval before execution. Implementation has not started.

## 1. Shared brief and approved product decisions

The intended outcome is to help public-site visitors find FAQ answers and open or share an individual question, while allowing authorized eiMaths staff to identify the questions opened most often during a selected date range.

The public website is `eimaths_th_new`; content administration, the FAQ API, and analytics persistence belong to the sibling `eiMaths` repository. This specification covers both repositories.

The user approved the following decisions in conversation:

| Concern | Approved behavior |
| --- | --- |
| Read metric | Count the first opening of each FAQ in one existing website session. Repeated openings in that session do not add another read. |
| Reading surfaces | Keep expandable answers on `/faq` and add `/faq/{slug}`. Both surfaces share the counting rule. |
| Search | Search the displayed question and answer in the active language, combined with category selection. Display a result count and empty-result message. |
| Stable links | Generate a slug once and preserve it when the question changes. English uses `?lang=en`, following the existing website convention. |
| SEO | Server-render individual questions and answers, with individual metadata and canonical URLs. Include published questions in the sitemap. |
| Reporting | FAQ staff can choose 7 days, 30 days, or custom dates. Default to 30 days and sort by recorded reads, descending. |
| Report columns | Question, category, and read count for the selected range. |
| Time boundaries | Group and select dates using `Asia/Bangkok`. Collection starts when enabled; historical visits cannot be reconstructed. |
| Failure behavior | Recording failure must not prevent reading. Reporting failure appears as an error, not a successful zero-count report. |
| Verification | Cover search, localization, publication, repeated reads, concurrent tabs, retries, new sessions, local-date boundaries, and report authorization. |

Success means passing section 12's acceptance checks. A recorded opening is not proof of reading to completion, a distinct human, or a bot-free audience. SEO acceptance means crawlable, correctly described individual URLs; indexing and ranking decisions remain outside the implementation's control.

Preserve existing content, categories, publication controls, Thai/English fallback, editorial ordering, permissions, and unrelated changes. Reuse both applications and existing UI styles. No external analytics account or search service is required.

## 2. Evidence, terminology, and implementation decisions

### 2.1 Verified repository baseline

Prepared against backend HEAD `4fb62623` and public-site HEAD `2851b91`. Both working trees were clean before this specification was created. These are static inspection results, not runtime verification.

| Repository evidence | Existing behavior |
| --- | --- |
| Backend `app/Http/Controllers/FaqController.php`, `app/Models/Faq.php` | CRUD, localization, publication filtering, Markdown answers, and an unpaginated list API exist. The inspected model/serializer have no FAQ slug. |
| Backend `routes/api.php` | `GET /api/faqs` is the public FAQ contract. |
| Backend `routes/web.php`, `resources/views/faqs/index.blade.php` | The authenticated editorial list uses `faq-list`, category filtering, and 20-row pagination. |
| Backend FAQ migrations and `tests/Feature/FaqApiTest.php` | Categories, English content, publication restrictions, and fallback have implementation and tests. Fixture counts are not production-volume evidence. |
| Public FAQ controller and `resources/views/faq.blade.php` | `/faq` requests the backend list, groups categories, and renders expandable answers and category controls. |
| Public `app/Http/Middleware/SetLocale.php` | Valid `lang` takes precedence over stored session language. |
| Public layout, `config/seo.php`, and `SitemapController.php` | Configured-origin canonicals, language alternates, metadata sections, a static `/faq` sitemap entry, and blog entries exist. |
| Public `config/session.php` | Lifetime is configurable; its source default is 120 idle minutes. The deployed value is unverified and must not be hard-coded. |
| Backend/public `config/app.php` | Backend uses `Asia/Bangkok`; public application uses `UTC`. Analytics must explicitly select storage/reporting time zones. |
| Backend HTTP kernel and route provider | API requests normally share a caller/IP limiter. Forwarded reads must not accidentally share a low visitor-level write budget across all visitors. |
| Public browser fixture helpers | Fixtures render isolated pages, abort unmocked requests, and currently do not allow FAQ fixtures. They cannot prove real cross-request session behavior. |

`ContactLinks::Html` adds contact links; its inspected implementation is not a general sanitizer. Existing `BlogHtmlSanitizer` provides an allowlist sanitizer callable by FAQ rendering without changing blog behavior.

### 2.2 Terms

- **FAQ:** one persistent question record, independent of language and reading surface.
- **Website session:** the public application's server-validated session identifier. Expiry or regeneration creates a new scope. Closing a tab alone need not create a new session.
- **Read receipt:** a durable deduplication record for one FAQ/session pair, represented by a keyed digest rather than a raw session ID.
- **Recorded read:** the first successfully committed receipt for that pair.
- **Read date:** the Bangkok calendar date at first successful backend acceptance.
- **Daily aggregate:** a per-question, per-read-date count used for reporting.

### 2.3 Author-selected implementation defaults

These author-selected choices make the approved behavior implementable. They are proposals for written-specification approval, not details previously approved individually by the user:

- Filter already-rendered FAQ content in the browser; no external engine or search-request logging.
- Use one immutable slug per FAQ and a separate statistics page within FAQ administration.
- Resolve new queryless detail URLs to Thai even when the existing session uses English; explicit `?lang=en` selects English. Preserve session-language behavior on the existing list.
- Include existing FAQs with zero recorded reads in the selected reporting range.
- Derive the idempotency key from session and FAQ ID on the public server; enforce uniqueness in the backend database.
- Retain minimal receipts and daily aggregates without automatic expiration in this release. This preserves deduplication for still-active sessions and selectable history. Section 8 defines retained fields and consequences.
- Keep counts for unpublished/deleted questions, using editorial snapshots for deleted content.
- Use 20-row report pagination and configurable request limits. Exclude charts, exports, live polling, visitor profiles, search-term analytics, manual slug editing, redirect-history management, additional authoring fields, a separate glossary, and an ADR.

Planning may choose exact class decomposition, SQL primitives, test filenames, and migration filenames while preserving these contracts/invariants. Metric, retention, URL policy, permissions, or product-scope changes require specification revision.

## 3. Responsibilities and affected boundaries

| Area | Responsibility and expected location |
| --- | --- |
| Backend content | Extend `Faq`, creation, migrations, and a focused slug helper to assign stable slugs and consistently serialize localized content. |
| Backend public API | Extend the FAQ controller or a focused API controller with detail retrieval, preserving the list response and locale behavior. |
| Backend analytics | Focused services under `app/Services/Faq` own receipt uniqueness, transactions, aggregates, historical labels, and reporting queries. |
| Backend HTTP | Separate read/statistics controllers validate integration requests and enforce existing backoffice access and report inputs. |
| Backend views/configuration | Add statistics beside `resources/views/faqs`, link from the editorial list, and add matching `lang/{en,th}/content-management.php` keys. Centralize limits and service credentials. |
| Public content | A focused service under `app/Services/Faq` centralizes API calls, timeouts, response validation, and safe answer presentation. |
| Public UI | Extend FAQ routes/controller; retain `resources/views/faq.blade.php`; add detail/answer partials under `resources/views/faq` and a browser module under `public/js`. |
| Public recording | A controller/service derives the idempotency key and forwards authenticated requests. Secrets and raw session IDs never reach JavaScript. |
| Public SEO/localization | Extend the sitemap and narrow metadata hooks without changing unrelated pages. Add matching `lang/en/faq.php` and `lang/th/faq.php`. |

New classes/functions use PascalCase, variables and serialized fields snake_case, constants UPPER_CASE. PHP class filenames follow PSR-4; framework-required names remain. Ordinary new files/browser assets use kebab-case.

## 4. Public interaction and search

### 4.1 Search

- Place a labelled input above category controls; limit queries to 200 Unicode characters.
- Match a normalized literal substring of either the displayed question or plain-text answer. Normalize Unicode using the browser standard, case, outer whitespace, and repeated whitespace. Never interpret regex, HTML, or search operators.
- Search rendered language, including existing Thai fallback. Do not search hidden translations.
- Apply search AND selected category. Empty query removes search only; all categories removes category filtering only.
- Preserve editorial order. Hide categories without matches; report matching questions, not categories.
- For a nonempty matching query, expand matching category containers to reveal headings but keep answers closed. This automatic category reveal does not count a read.
- Changing normalized query/category closes answers and recomputes results. Clearing search restores ordinary category presentation for the selected category.
- Provide clear-search, localized count, and empty state. Query text stays in page memory; no new URL, network search request, or stored analytics field.

### 4.2 Reading

Keep at most one answer open. Opening changes its real state and `aria-expanded` to true; closing sets both false. The current open handler leaves `aria-expanded` false: correct this directly related mismatch and verify transitions.

Each FAQ gets a visible normal anchor to its individual page, separate from the toggle. Preserve existing `faq-{id}` answer IDs. Loading the list, opening a category, searching, seeing a heading, and closing an answer do not record reads.

Detail pages show question, category, answer, and a back-to-list link. Answers exist in initial HTML. With JavaScript disabled, individual links/answers remain usable but browser-event analytics are not collected.

### 4.3 Accessibility and safe rendering

Use labels, real buttons/anchors, accurate expanded states, visible focus, and keyboard support. Announce result changes politely without moving focus. Preserve mobile and zoom usability.

Escape questions, query text, labels, and metadata. Apply the existing allowlist sanitizer to rich FAQ HTML before contact-link transformation; use the same pipeline in list/detail views. Plain-text fallback stays escaped. Preserve safe formatting and phone/email links. Existing FAQ JSON-LD must match displayed content and use safe JSON encoding; no new rich-result feature is promised.

## 5. Slugs, individual URLs, and SEO

### 5.1 Slug assignment

- Add a unique `slug` column to `faqs`. It may be nullable during rollout for old-writer compatibility; complete backfill and the new writer must produce nonempty slugs before individual URLs are exposed.
- Use the nonempty English question initially, otherwise Thai. Strip markup, lowercase letters, replace runs outside Unicode letters/marks/digits with a hyphen, trim boundary hyphens, and limit the base to 120 Unicode characters.
- If empty, use `faq-{id}`. On collision append `-{id}` to the base; if that candidate also exists, add an increasing numeric suffix until unused. Retain database uniqueness protection and handle concurrent conflicts without changing an existing slug. Store the result in a column allowing 255 characters.
- Backfill in bounded ID-ordered batches without changing content, IDs, timestamps, order, or publication. New FAQ creation assigns its slug within the creation transaction.
- Preserve slugs on question, answer, category, language-field, and publication changes. No manual slug editor or automatic rename-on-edit.
- Encode path segments correctly; never treat decoded slugs as filesystem paths or allow embedded path separators.

### 5.2 Language and URL behavior

Public routes are `/faq` and `/faq/{slug}`. English links carry `?lang=en`; Thai links are queryless. The existing list continues honoring session language, preserving its current contract.

For the new detail route, valid explicit `lang` selects language; missing/invalid values resolve to Thai so a queryless shared detail URL has stable content language. Apply this only within the detail boundary, not the global middleware. Preserve field-level Thai fallback when English is absent. Language switches preserve the slug.

A FAQ is public only if it and its category are published. Unknown/unpublished questions or categories return 404 on detail API/page. Upstream timeout, malformed response, or service failure returns 503 on the public FAQ page, never a fabricated missing question or successful empty page.

### 5.3 Metadata and discoverability

- Detail title: localized question followed by site brand. Description: whitespace-normalized, markup-free excerpt of the rendered answer, at most 160 Unicode characters.
- Absolute URLs use `config('seo.canonical_origin')`, not a caller-controlled Host header.
- Each detail canonical identifies itself: queryless Thai or `?lang=en` English. Never canonicalize all questions to `/faq`. Exclude tracking/unrelated parameters.
- Open Graph URL/title/description agree with detail metadata. Preserve existing Thai/English alternate-link conventions and field-level fallback.
- Add one Thai canonical sitemap entry per public FAQ; English is discoverable through language links/alternates. Use actual FAQ `updated_at` for `lastmod`, not today's date for every entry.
- Retain static `/faq` and blog entries. If the FAQ sitemap dependency cannot be retrieved/validated, return temporary 503 rather than an apparently complete sitemap missing all FAQ detail URLs. A successfully retrieved empty published collection is valid.
- Content GETs, sitemap generation, metadata rendering, and crawlers merely retrieving HTML never write analytics.

Google recommends descriptive URLs and explains that fragments generally do not provide separately crawlable content: [URL structure guidance](https://developers.google.com/search/docs/crawling-indexing/url-structure). Canonical signals identify preferred URLs but do not guarantee indexing/ranking: [Canonical guidance](https://developers.google.com/search/docs/crawling-indexing/consolidate-duplicate-urls).

FAQ rich results stopped appearing on May 7, 2026: [Google Search documentation updates](https://developers.google.com/search/updates#may-2026). Existing structured data may remain; a FAQ rich result is not an acceptance criterion.

## 6. HTTP contracts

| Application and route | Contract |
| --- | --- |
| Backend `GET /api/faqs?locale={locale}` | Preserve existing array/fields, publication, ordering, and locale fallback. Add `slug`. Do not expose analytics/receipt data. |
| Backend `GET /api/faqs/{slug}?locale={locale}` | One object with the same public fields as a list element. Unknown/nonpublic content returns 404; no list redirect. |
| Public `GET /faq/{slug}` | Server-render detail using the API and section 5's language rules. |
| Public `POST /faq/{faq_id}/read` | Same-origin, CSRF-protected, existing-session request; positive integer route ID. Accept no client session ID, key, timestamp, count, or integration credential. Acknowledged new/duplicate receipts return 204. |
| Backend `POST /api/faq-reads` | Dedicated server integration. JSON: `faq_id` positive integer; `event_key` exactly 64 lowercase hexadecimal characters. Authenticate with the dedicated bearer token. |
| Backend `GET /faqs/statistics` | Existing authenticated backoffice access plus `faq-list`; server-rendered paginated report with section 9's date validation. |

The `locale` query accepts `th` or `en`, defaulting to `th` for an absent/unsupported value as in the existing API. Register `/faqs/statistics` before conflicting dynamic routes; constrain numeric IDs. Preserve current route names and CRUD fields.

Integration responses: 204 for newly committed or matching duplicate receipt; 401 for absent/invalid credential; 404 for currently unknown/unpublished FAQ/category; 422 for invalid fields; 409 for a key already bound to another FAQ; 429 for throttling; 503 for temporary persistence failure. Responses expose no totals, session data, or credentials.

The public adapter uses safe localized failures with `error_code` and `message`. Map unavailable/misconfigured upstream authentication to 503, not a visitor sign-in requirement. Preserve appropriate CSRF/validation statuses. Browser recording failures do not replace the FAQ content.

## 7. Read collection and deduplication

### 7.1 Qualifying events

On the list, send after a user changes an answer from closed to open. On detail, send once when the answer is rendered and the document is visible; hidden tabs wait until visible. Do not use a timer to imply completed reading.

Send asynchronously to the same origin using the existing session cookie and CSRF token. Add no analytics cookies, localStorage identifiers, fingerprinting, account lookup, or cross-site browser call. In-page suppression is an optimization, never the deduplication authority.

Allow at most one automatic retry after transient network error/5xx, with a bounded short delay. Do not automatically retry 4xx or 429. Navigation may cancel pending requests; no offline queue or guaranteed background delivery.

### 7.2 Stable key and forwarding

After session middleware, the public server derives an HMAC-SHA-256 key over a versioned, unambiguous encoding of the actual session ID and decimal FAQ ID using `FAQ_READ_HASH_KEY`. Output lowercase hexadecimal. Locale, date, URL, client-supplied IDs, and reading surface are not inputs.

One FAQ/session therefore produces the same key across reloads, tabs, languages, midnight, and retries. Different sessions/questions produce different keys. Database uniqueness arbitrates concurrent submissions; do not rely on session-array writes or client flags.

Configure `FAQ_READ_INTEGRATION_TOKEN` on both applications for dedicated server authentication, separate from CRM credentials. Keep `FAQ_READ_HASH_KEY` only on the public server, stable across ordinary deployments. Bearer-token rotation must not change the hash key. Hash-key replacement requires deliberate session invalidation/transition because changing it for active sessions changes counting keys.

Forward only `faq_id`/`event_key` over configured HTTPS with certificate verification. Do not follow credential-bearing redirects to another destination. Use configurable 1-second connection and 3-second total timeouts. Do not add HTTP-client retries on top of the browser's bounded retry.

### 7.3 Backend acceptance invariant

For an accepted request:

1. Authenticate/validate, then start a transaction.
2. Check current FAQ/category publication, coordinating with concurrent publication changes through appropriate locks.
3. Resolve the Bangkok read date from backend time. Find/create the unique FAQ/date aggregate.
4. Insert the unique receipt, referencing that aggregate.
5. Only for a new receipt, atomically increment the aggregate and maintain its editorial snapshots. Commit both together.

If another request committed the same key, roll back tentative work and return 204 only after verifying the existing receipt belongs to the same FAQ. A duplicate after midnight must not increment or leave an empty aggregate for the later day. Treat only the expected uniqueness conflict as a duplicate; propagate other database failures. A failed transaction leaves neither a receipt nor an increment.

A response lost after commit is safely retried with the same key. The first successful backend acceptance determines the date, not a browser timestamp. If first successful delivery occurs after midnight, it belongs to that later date.

## 8. Persistence, retention, and historical integrity

Use focused backend tables with these logical fields/constraints:

| Table | Required data and constraints |
| --- | --- |
| `faq_daily_reads` | Primary ID; immutable `faq_reference_id`; Bangkok `read_date`; nonnegative integer `read_count`; Thai/English question and category-name snapshots; unique `(faq_reference_id, read_date)`; range index beginning with `read_date`. |
| `faq_read_receipts` | Unique/primary `event_key`; `daily_read_id` foreign key to aggregate with restrictive deletion; first recorded UTC timestamp. The aggregate supplies FAQ/date without duplicating identity fields. |

`faq_reference_id` is a historical reference to the original FAQ ID, not a cascading FK to current content. This preserves existing hard-delete behavior without erasing history. Receipt-to-aggregate integrity uses a real FK and the transaction above. Do not recycle FAQ IDs during imports/maintenance.

Reports use current localized question/category when the FAQ exists. For deleted questions use latest retained snapshots and a localized deleted-content indicator. Unpublishing preserves history but stops new reads. Do not snapshot answer bodies into analytics.

Retain aggregates and minimal receipts together for this release, without scheduled deletion or an arbitrary deduplication TTL. Existing sessions can stay active through repeated activity; erasing a key could allow another count. The trade-off is storage proportional to recorded FAQ/session pairs. Digests are pseudonymous deduplication data, not fully anonymous; keep them out of reports/operational logs.

New analytics tables store no raw session IDs, IPs, user agents, names, emails, account IDs, referrers, or search queries. This does not claim that existing infrastructure/session logs contain none of those fields. A future retention reduction must preserve aggregates and explicitly settle deduplication consequences before purging.

## 9. Backoffice report

Add a statistics link to the existing FAQ list and `/faqs/statistics`. Reuse `faq-list` and existing account/branch-access middleware; do not broaden permissions or publish counts. Statistics share the existing FAQ administration's global content scope; do not invent visitor-to-branch attribution.

- `range` is `7d`, `30d`, or `custom`; `start_date`/`end_date` are required for custom only; `page` is positive. Default: `30d`.
- Presets include today in Bangkok: start six days earlier for 7 days, 29 earlier for 30 days. Custom endpoints are inclusive Bangkok dates.
- Validate real `YYYY-MM-DD` dates, start not after end, and end not after today. Pre-collection dates are allowed but do not reconstruct visits. Invalid ranges show field errors without running the report.
- Sum daily counts by original FAQ identity in range. Include existing FAQs with zero counts and deleted FAQs with positive retained counts. Obtain labels with bounded/batched queries, not a query per row.
- Sort count descending, immutable FAQ ID ascending on ties. Paginate 20 rows; retain range in links; applying a new range resets page 1.
- Show question, category, recorded count, selected range, and Bangkok timezone. Explain counts represent first recorded openings per session, not distinct people/completed reading.
- Successful empty results differ from report/database failure. Failure shows a localized retry/refresh message with failure status, never synthesized zeros.

Totals may undercount when JavaScript is disabled, session establishment fails, the visitor leaves before delivery, rate limiting rejects a request, or dependencies fail. Visible-browser events/limits reduce incidental noise but neither authenticate humans nor comprehensively detect bots.

## 10. Security, availability, and observability

Dedicated integration middleware requires a nonempty configured token and constant-time comparison. Never reuse it as the hash key. Do not expose credentials or allow direct unauthenticated browser writes to the integration API.

Public recording limits initially: 60 requests/session/minute and 120/IP/minute, configurable. IP can be used transiently for rate limiting but not copied to new analytics tables/feature logs. Backend integration budget initially: 600 authenticated recording requests/minute, configurable. Replace the normal API-group limiter only on this new write route, preserving all other routes' protection. Invalid credentials also require bounded admission. Test actual middleware/limiter ordering.

Content rendering remains independent from recording. Missing secrets/database failure close recording while leaving reading usable. Missing content, temporary service failure, valid empty collections, and empty search results have distinct responses.

Use existing structured application logs for recording/upstream/report failures, with bounded fields such as `operation`, `faq_id`, `outcome`, `error_code`, upstream status, and correlation ID. Exclude request bodies, bearer credentials, session IDs, event keys, and raw visitor identifiers. Do not add an observability subsystem. Describe signals in later implementation verification notes.

Validate upstream shapes; sanitize rich HTML; escape plain text and archived labels; safely encode JSON. Contact-link transformation is not sanitization. Search text never becomes executable HTML/SQL.

## 11. Migration and rollout requirements

These constrain the later implementation plan; they do not authorize migrations/deployment now.

1. Add backend schema compatibly, keeping nullable slug support during old-writer overlap. Do not alter existing content or erase analytics data.
2. Deploy slug-aware creation, additive list fields, detail retrieval, recording, and reporting. Drain old writers before final backfill/verification.
3. Backfill and verify all FAQ slugs are nonempty/unique. The old public frontend continues consuming the compatible list.
4. Provision separate integration/hash secrets outside source control. Recording defaults off until endpoints/credentials are ready. A focused public `FAQ_READ_TRACKING_ENABLED` flag controls browser submissions and acceptance of direct public recording requests without changing content.
5. Deploy public search/detail behavior, then enable recording after isolated integration checks. Legacy list entries lacking slug/valid ID still render; omit detail links/recording for those entries. Detail URLs/sitemap entries require valid slugs.
6. For rollback, disable recording first, roll back application behavior, and retain additive columns/tables. Do not destructively roll back schema after collection starts. Preserve stable slugs/history.

Before enabling, verify deployed session/cache drivers, shared session storage across public instances, stable hash-key configuration, HTTPS destinations, database constraints/indexes, and rate limits. Production configuration/traffic have not been inspected; correctness must not assume a fixed duration, one instance, or fixture content volume.

The system owner must also review the actual privacy notice and retention arrangements for this additional FAQ analytics purpose before enabling collection. The inspected public `lang/en/privacy.php` describes enquiry, trial, and LINE processing and does not establish coverage for these FAQ receipts. Public `docs/privacy/0004-pdpa-operational-readiness.md` treats tracking purposes and retention as separate review matters. Existing notice text or marketing consent is not evidence that the proposed indefinite receipt retention has been approved. Keep recording disabled until that review is resolved. This is an enablement dependency, not a legal determination or a requirement to add a consent interface. Notice edits or changes to the consent model require separate scope approval; a retention decision that changes deduplication/history requires revising this specification. Technical planning can proceed while this operational review remains pending.

## 12. Acceptance and verification contract

Implementation must use test-driven development. This specification does not claim the following checks have run or passed.

| Scenario | Required result |
| --- | --- |
| Query matches answer only | Matching heading/category visible, answer closed until opened. |
| Thai/English, fallback, spaces, case, punctuation, literal markup | Specified normalized substring match on rendered content; no interpretation of code/regex. |
| Search/category combination, no match, clear, keyboard | Correct intersection/count/empty state, restored list, focus, and announcements. |
| Open/close/open the same answer | Accurate expanded state; one recorded read in the session. |
| List then detail; refresh; change language | One receipt/count for the FAQ/session. |
| Concurrent tabs/requests, same existing session | Exactly one receipt/count enforced by database uniqueness. |
| Different sessions, same FAQ | Two reads; client input cannot force another session's key. |
| Session expires/regenerates | New eligible read without hard-coding 120 minutes. |
| Lost response after commit; retry | Duplicate acknowledged without increment. |
| Failure between receipt and counter writes | Full transaction rollback; later retry can count once. |
| Duplicate error versus unrelated DB failure | Only verified matching duplicates receive acknowledgement. |
| Same-session duplicate across Bangkok midnight | Count stays on first acceptance date; no duplicate/empty later aggregate. |
| First successful delivery on later Bangkok date | Later acceptance date owns count despite public application's UTC timezone. |
| Published FAQ in unpublished category | Excluded from list/sitemap; detail/new reads return 404. |
| Unpublish/delete previously read FAQ | Access/new reads stop; history survives with current or archived labels. |
| Slug collision, Unicode, empty source, edited question | Unique stable slug, encoded links, defined fallback, no rename on edit. |
| Detail language, shared queryless URL, metadata, sitemap | Initial HTML answer; correct canonical/alternates; eligible URLs and real modification dates. |
| Script/event-handler/unsafe-link in answer | No execution in list/detail/JSON-LD/metadata; safe contact links preserved. |
| Content API failure/malformed response | Temporary 503, not fabricated 404 or empty success. |
| Unauthorized report | Existing auth/access boundary denies statistics. |
| Dates, leap day, reversed/future ranges, zero/deleted FAQs | Correct validation, inclusive Bangkok range, sorting, labels, pagination. |
| Report query fails | Visible failure, not zero-count success. |
| Bad credentials, forged fields, CSRF, rate limit | Reject without writes/secrets; answers stay usable. |
| Recording disabled or local credentials absent | No browser submission or accepted public recording; content/search available. |
| Upstream integration credential incorrect | Submitted events are rejected without counts or leaked credentials; content/search available. |
| GETs and sitemap generation | No analytics writes. |

Extend backend `FaqApiTest`, `FaqLocalizationTest`, and focused management tests; public `FaqPageTest`, canonical/metadata and contact-link tests. Add focused receipt/report and browser behavior coverage using existing conventions.

Browser helpers must allow only added FAQ fixtures, fake content APIs, and explicitly intercept recording. Preserve the ban on unexpected external requests. Fixture-only tests cannot prove real cross-tab sessions/database concurrency: exercise an isolated application/session store with concurrent requests against the intended engine or an explicitly equivalent transactional setup. Do not report SQLite-only sequential checks as production-concurrency proof.

Tests must not use live backend services, migrate user databases, or send real tracking. Record targeted regression commands/results/limits and required repository checks. Claim builds only when executed. Document preparation requires structural/content review, not application-test results.

## 13. Written-design readiness and handoff

This is the only newly authorized design artifact. Writing it does not authorize an implementation plan, code changes, migration execution, deployment, glossary, or ADR.

An independent reviewer must assess this document against the approved conversation and repository seams, identifying material unanswered questions, contradictions, scope additions, and unsupported assumptions. Explicitly delegated class/SQL/test decomposition is not an unanswered product question.

After corrections and document verification, present the specification for explicit user approval of the written document and move to planning. Use `writing-plans` after that approval. A completed implementation plan needs its own approval before execution.
