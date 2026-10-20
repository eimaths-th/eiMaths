# Localized FAQ Slug Updates and One-Year Redirect History

Status: The user approved the conversational design, this written specification, and proceeding to implementation planning on 2026-10-20. The resulting implementation plans require separate approval and execution-method selection before execution. Implementation and production changes have not started.

## 1. Shared brief and approved scope

The outcome is to let authorized eiMaths staff edit a FAQ's Thai and English question titles and automatically update the corresponding public URLs. Visitors following an earlier URL receive a permanent redirect to the latest URL of the same FAQ and language for one year. After that year, that earlier URL returns 404 and its route-history record can be deleted.

Content administration, persistence, and the API belong to the eiMaths repository. Public FAQ rendering, language navigation, and SEO belong to the sibling eimaths_th_new repository. Intended users are existing FAQ editors, public visitors following current or previously shared links, and crawlers retrieving public content.

Preserve FAQ IDs, read statistics, receipt deduplication, permissions, content, categories, publication rules, ordering, search, and the existing accordion interaction. The one-year retention decision applies only to URL aliases, not analytics records. Indexing or ranking outcomes are not acceptance criteria.

The user approved:

- Separate Thai and English slugs. Thai canonical URLs are /faq/{slug}; English canonical URLs are /faq/{slug}?lang=en.
- Regenerate only when that language's normalized question-title base changes. Answer-only edits and saves that do not change the normalized base keep their URLs.
- Preserve the existing Thai fallback for an empty English title. In that case, a Thai-title change can change both localized URLs.
- Append the FAQ ID and a monotonically increasing per-language revision to new slugs, such as course-age-42-2, including on initial assignment.
- Keep old URLs for one year per URL, measured from retirement, without renewing older expiry dates on later edits.
- Redirect directly to the latest current URL of the same FAQ and language, rather than storing redirect chains.
- Initialize both localized slugs for existing FAQs, retaining earlier public links for one year from initialization.
- Save content, current slugs, revisions, and history atomically.
- Return 404 for missing or nonpublic content, and 503 for unavailable or invalid upstream content.
- Delete expired alias rows daily; request-time expiry applies even before physical deletion.
- Keep existing editing permissions, restrict public redirects to the public site's configured origin, and prevent storage of redirect responses with Cache-Control: no-store.
- Verify language-specific editing, fallback, repeated renaming, duplicate titles, concurrency, expiry boundaries, browser navigation, and preservation of read counts.

This specification supersedes the immutable-single-slug and same-path language-switch rules in 0003-faq-search-seo-analytics-design.md, and its FAQ sitemap URL policy, only where explicitly changed here. The original search, analytics, sanitization, session, and authorization contracts otherwise remain applicable. Do not rewrite that approved historical specification.

Excluded: a manual slug field, a redirect-history administration UI, analytics retention changes, redirects to unrelated pages, new external services, unrelated refactoring, a separate glossary file or ADR, and deployment execution.

## 2. Verified baseline and decisions made for this specification

Static inspection was performed against backend HEAD 893a312f987b2a5c019e1cf5c96a04bbcc0d3c44 and public HEAD 266e96624ac03a1ee74ec42e4c09628df636919a. Both working trees were clean before this document was written. This is repository evidence, not a claim about production configuration or a new test run.

| Evidence | Current behavior |
| --- | --- |
| Backend app/Services/Faq/FaqSlugWriter.php | Creation assigns one English-first/Thai-fallback slug; Assign preserves nonempty values. |
| Backend app/Http/Controllers/FaqController.php | Store calls the writer; Update changes content without regenerating the slug. Existing FAQ permissions protect administration. |
| Backend app/Models/Faq.php | English question and answer fallback are field-specific. Editor fillable fields exclude slug. |
| Backend app/Console/Commands/BackfillFaqSlugs.php | The existing command only fills missing legacy slugs and preserves content and timestamps. |
| Backend FaqPublicContent.php and routes/api.php | The list and detail share a public-content projection; FAQ and category must both be published. |
| Backend app/Console/Kernel.php | A scheduler registration point already exists. Actual production cron/Scheduler execution has not been verified. |
| Public FaqContentClient.php | HTTP redirects are disabled; detail validation currently requires the returned legacy slug to equal the requested slug. Simply adding backend HTTP redirects would not satisfy this client. |
| Public FaqController.php and layouts/app.blade.php | Canonicals, alternate links, and language navigation currently assume the same slug path for both languages. |
| Public public/js/faq-search.js | Opening an answer updates the address using history.replaceState without document navigation; closing/filtering restores the original list address. |
| Public SitemapController.php | FAQ entries currently use Thai legacy URLs and actual FAQ updated_at values. |
| Public FaqReadKey.php and backend analytics migration | Read identity uses FAQ ID and website session, independent of locale or URL. |
| Application configuration | Backend timezone is Asia/Bangkok; public timezone is UTC. |

Author-selected technical decisions below make the approved behavior concrete: an additive API namespace for localized detail resolution, a unified current/alias route registry, persistent per-language revision state, explicit activation flags, UTC timestamp storage with Bangkok anniversary calculation, and narrowly scoped FAQ language-switch handling. These are part of this written specification's approval, not additional product features.

Planning may select class decomposition, migration filenames, helper names, localized error wording, and SQL primitives while preserving the contracts below. No unresolved product choice is delegated. Follow project naming and PSR-4/framework exceptions.

## 3. Responsibilities and storage

Backend feature services under app/Services/Faq own title normalization, allocation, transactions, route resolution, and expiry. Extend the existing FAQ editor write path and CLI command; do not depend on a browser event, queue worker, or a later backfill to complete an ordinary create or edit.

Keep faqs.slug as the stable legacy API identifier. Never change a nonempty value when editing or backfilling. For a new FAQ, or an existing row with an empty legacy slug, assign it to the initially generated English current slug after both localized routes are allocated. It remains unchanged thereafter.

Add these feature tables. They do not contain question/answer snapshots, visitor identifiers, or read counts.

| Table | Required state and constraints |
| --- | --- |
| faq_localized_slug_states | One row per (faq_id, locale), uniquely constrained. Store last_revision as a positive integer, source_base as at most 120 Unicode characters, and initialized_at. Locale is th or en. faq_id references faqs with cascade deletion. |
| faq_slug_routes | Store id, faq_id, locale, slug (at most 255 characters), revision (positive for generated routes, null for a compatibility alias of the stable legacy identifier), current_marker, retired_at, and expires_at. faq_id references faqs with cascade deletion. |
| Route identity | Unique (locale, slug) across current and alias rows. A Thai URL and an English URL may share a slug, because language is part of URL identity. |
| Current route | current_marker is 1 for the current row and null for aliases. Unique (faq_id, locale, current_marker) enforces at most one current route per FAQ/language using the target databases' nullable-unique behavior. Current rows have no retired_at or expires_at. |
| Alias route | current_marker is null; retired_at and expires_at are required. Alias rows identify the owning FAQ and language, not another route or a stored destination URL. |
| Lookup/pruning indexes | Index route identity, current-route ownership, and (expires_at, id). Index foreign keys as required by the database. |

Use migration constraints and service assertions for the listed invariants; test the nullable-unique scheme on the supported MySQL/MariaDB runtime as well as the isolated test database. Never rely solely on an application pre-check for uniqueness. Do not introduce a second competing current-slug source of truth.

All route/state times are UTC instants at microsecond precision. The application converts explicitly for anniversary calculation. Deleting a FAQ removes its URL metadata; analytics retention and deleted-question reporting keep their existing behavior. Publication changes do not reset revisions, alter URLs, or extend expiry.

Only aliases grow with URL changes. Current routes and revision state remain bounded at two of each per FAQ. Physical history size still depends on editing volume, slug lengths, indexes, and database overhead; this design makes no unmeasured disk-size or performance guarantee.

## 4. Slug generation and write transactions

For each language, choose the displayed question title: question for th, and filled question_en or otherwise question for en. Use the existing framework notion of filled, so valid text such as "0" is not treated as absent.

Normalize by stripping markup, lowercasing Unicode, replacing each run outside Unicode letters/combining marks/numbers with a hyphen, trimming boundary hyphens, truncating the base to 120 Unicode characters, and trimming boundary hyphens again. Use faq if the resulting base is empty. No transliteration service or new dependency is required.

A generated slug is base-faq_id-revision, where the last two components are the owning FAQ's positive decimal ID and that language's positive decimal revision. Examples: course-age-42-1 and revised-course-age-42-2. Begin initial allocation at revision 1 and later allocation at last_revision + 1; collision skips may leave gaps. The revision is per FAQ/language, not a database row ID or a timestamp.

Never reuse a revision, including after its alias is pruned or a title returns to an earlier value. Retain the revision counter for the FAQ's lifetime. Generated slugs for different FAQ IDs cannot share the same final two numeric components. Existing legacy values in faqs.slug are reserved while their FAQ exists; check that indexed column as well as route-registry uniqueness before accepting a generated candidate, even if an associated legacy alias has expired.

Creation, editing, and initialization follow these invariants:

1. Validate authorization and editorial input using the established permissions/rules. Do not accept slug, revision, alias, or expiry fields from editor input.
2. Open one transaction and lock the FAQ row, obtaining fresh persisted content and publication state. Serialize every supported write for that FAQ through this boundary.
3. For first initialization, allocate both states/current routes from the saved titles. Use one initialization instant. Register a nonempty legacy slug as an alias in each language where it is not already that language's current slug. An imported legacy alias expires one year from initialization.
4. For ordinary edits, compare each normalized displayed-title base with its stored source_base. If equal, retain the current slug and revision. Formatting-only changes producing the same base do not add history.
5. For each changed base, retire that language's current route and allocate a new revision/current route in the same transaction. Store the new source_base and last_revision. Other language state is unchanged unless its fallback source also changed.
6. Retired generated routes keep their original revision. Each retirement records its own retirement instant and anniversary deadline. Later changes update neither those fields nor older aliases.
7. Save content and all affected routes/states together. Compute publication timestamps from the locked row using existing semantics. Any failure rolls back the whole operation.
8. On a candidate collision with a reserved legacy value or route identity, advance the revision and retry within the transaction/savepoint. Limit allocation to 32 candidates per language; exhaustion or revision overflow aborts the write rather than looping indefinitely. Only expected slug-identity unique conflicts are retryable; unrelated database failures propagate to a safe error path.
9. Current rows and source_base must agree with state, with exactly one current route per initialized FAQ/language. A later reader never repairs this silently.

Preserve established last-committed-save behavior for overlapping editorial fields; this work adds no stale-form interface or optimistic-locking product flow. The concurrency guarantee here is atomic, serialized slug/history updates for every committed save, not a promise that stale full-form submissions merge independent editorial intentions.

While the backend feature is active, new application-created legacy values use the generated English initial slug, rather than generating additional unsuffixed legacy names. Drain old FAQ writer processes before activating the new writer. Direct SQL changes or manually reused FAQ IDs are outside the supported write contract and must not be used for deployment.

## 5. One-year history and route resolution

One year means one calendar anniversary in Asia/Bangkok, not a fixed 365-day interval. Preserve local clock time and clamp February 29 to February 28 when the next year has no February 29; then store the deadline as UTC.

An alias is valid only while server_now is strictly earlier than expires_at. At expires_at it returns 404, even if the pruning command has not run. Visits, repeated backfills, subsequent edits, and publication toggles never renew its lifetime.

Example: A retires when B is created at t1; B retires when C is created at t2. Both unexpired aliases resolve directly to C, in the same requested language. A expires at the anniversary of t1 and B at the anniversary of t2.

Resolve only the exact (locale, decoded slug) key in faq_slug_routes. Enforce exact string equality after database lookup rather than accepting collation-equivalent spellings. Never infer ownership from the numeric suffix, search another language after a miss, fall back to faqs.slug after a registry miss, or resurrect an expired alias through the legacy API.

Require the FAQ and its category to be published for both current and alias resolution. Missing, expired, deleted, and unpublished content all return 404 without disclosing the latest target. A missing/inconsistent current route for an otherwise valid owner is a service failure, not a guessed redirect.

A generated localized URL is never reallocated after pruning because its owning ID and monotonic revision are not reused. Imported legacy values retain the baseline faqs.slug reservation for as long as their FAQ exists. This policy does not attempt to reconstruct unknown URLs belonging to records deleted before this feature.

## 6. API contracts and compatibility

Add FAQ_LOCALIZED_URLS_ENABLED=false to each application's focused configuration and .env.example during implementation. It is an independent flag in each project, separate from read tracking; enabling it requires that application's rollout checks. No credential is added or rotated.

With the backend flag disabled, retain existing legacy writer/list/detail behavior and avoid accessing new tables from ordinary requests. Explicit migration/backfill commands may operate after their required tables exist.

With the backend flag enabled, the new writer is authoritative. Keep these old routes compatible:

| Route | Contract |
| --- | --- |
| GET /api/faqs?locale=th or en | Existing root array, stable legacy slug, public fields, localization/fallback, ordering, and publication filtering remain. Add localized_slugs with th/en current values; use null values only for rows not yet initialized during rollout. No per-record query loop. |
| GET /api/faqs/{slug}?locale=th or en | Continue resolving by stable faqs.slug with the same public record shape as the list element. It does not become a redirect endpoint. Add the same localized_slugs field. |

Add these routes, independently of the old detail route:

| Route | Contract |
| --- | --- |
| GET /api/faq-pages/{faq_id}?locale=th or en | Constrain faq_id to a positive integer. Return the ordinary public record with stable legacy slug and two valid localized_slugs, selected localized content, and existing timestamps/category fields. This supports fresh language navigation by FAQ ID. |
| GET /api/faq-pages/resolve/{slug}?locale=th or en | Return the resolution envelope below for an exact, unexpired current/alias key. Resolve content and current route from one consistent read boundary, without following HTTP redirects. |

New endpoint responses when the backend flag is disabled are 503, not a signal to fall back from an expired or missing localized URL. Existing endpoints keep their old behavior. Register routes and constraints so the numeric-ID and resolver paths cannot capture each other.

Resolution envelope:

~~~json
{
  "requested_slug": "earlier-title-42-1",
  "locale": "en",
  "match_type": "alias",
  "alias_expires_at": "2027-10-20T09:00:00.000000Z",
  "faq": {
    "id": 42,
    "slug": "stable-legacy-identifier",
    "localized_slugs": {
      "th": "primary-title-42-2",
      "en": "current-title-42-3"
    },
    "question": "Current English title",
    "answer": "Current answer",
    "answer_html": "<p>Current answer</p>",
    "position": 1,
    "is_published": true,
    "published_at": "2026-10-20T09:00:00.000000Z",
    "created_at": "2026-10-20T09:00:00.000000Z",
    "updated_at": "2026-10-20T09:00:00.000000Z",
    "category": {
      "id": 5,
      "slug": "general",
      "name": "General",
      "description": null,
      "position": 1
    }
  }
}
~~~

For match_type=current, requested_slug equals faq.localized_slugs[locale] and alias_expires_at is null. For alias, the requested slug differs from that current slug and alias_expires_at is a future UTC timestamp. faq.slug always retains its legacy meaning. Return no stored destination URL, editorial-only field, metric, receipt, session identifier, or credential.

Locale accepts scalar th/en; absent, unsupported, or nonscalar input defaults to th, matching existing behavior. Unknown/expired/nonpublic content returns 404. A resolver registry miss is 404, even if a legacy record has not been initialized; the resolver does not diagnose or resolve that miss through faqs.slug. The by-ID endpoint, or a resolver hit with an identifiable owner but missing/inconsistent current state, returns 503 for incomplete metadata. Persistence failures or unavailable service also return 503. Other API middleware and limits keep their existing role.

For the two new API endpoints, error responses are JSON with error_code and message: FAQ_NOT_FOUND for 404 and FAQ_CONTENT_UNAVAILABLE for 503. Messages disclose no editorial content or redirect target. Existing API error contracts remain unchanged.

The public adapter validates the envelope, exact requested slug and locale, positive FAQ ID, both current slugs, publication, timestamp types, and the current/alias relationships before using it. Both generated current slugs must encode the returned FAQ ID and a positive revision; an identity lookup must also return the exact requested FAQ ID. Maintain existing answer sanitization and input length/character validation. A wrong identity, malformed response, unexpected HTTP redirect, connection failure, or upstream non-success other than 404 becomes 503. Never turn invalid metadata into a permissive redirect.

Set Cache-Control: no-store on new resolution/identity API responses and their errors, and on public alias redirects and localized-detail error responses. The old API retains its established caching contract.

## 7. Public pages, language navigation, and SEO

With the public flag disabled, retain existing FAQ behavior. With it enabled, use localized_slugs for all FAQ links, accordion data-faq-url values, detail pages, and sitemap URLs. Missing/invalid localized metadata in this mode is an unavailable dependency, not permission to use a legacy slug or silently omit known FAQ sitemap entries.

For detail, use valid explicit lang=en or lang=th; missing/invalid/nonscalar lang selects th independently of stored session language, as today. Build Thai canonical URLs without a language query and English canonical URLs with only lang=en. Strip unrelated query parameters from redirect destinations and canonical/alternate metadata. A current URL with unrelated query parameters may render normally with clean canonical metadata.

Call the new resolver using the requested slug and selected locale. On a current match, server-render the existing FAQ detail with current localized metadata and sanitized answer. On an alias match, issue public HTTP 301 to that FAQ's current localized path. Construct the absolute Location from config('seo.canonical_origin') and the validated/encoded current slug, never from the Host header or an API-supplied URL.

Preserve HTTP redirect disabling, timeouts, response validation, and safe failure messages in the content client. Backend resolution returns JSON rather than a Location that the client must follow. Retain exact identity checks appropriate to the new envelope instead of deleting them to accept mismatched slugs.

Keep one open answer and history.replaceState behavior without a document navigation. Closing, searching, clearing, or changing category restores the exact original list address and associated language-link state. Preserve existing accessibility, no-JavaScript heading links, and the absence of a separate "Open question page" control.

Language switching must work on both server-rendered detail and an open accordion whose address now looks like a detail URL:

- Extend existing language.switch handling only when an explicit positive faq_id is supplied in localized mode. Resolve that ID using the new identity API, set the selected session locale, and issue a temporary redirect to its current canonical URL.
- Render both desktop/mobile FAQ detail language links with the same FAQ ID and target language. Do not reuse the current slug path for the other language.
- On the list, opening an answer supplies that FAQ ID to both language controls; closing/filtering restores their original list links. Invalid or missing FAQ identity retains the original list behavior.
- Without FAQ context, preserve existing language-switch handling for all other pages. Do not broaden acceptance of arbitrary redirect URLs or modify unrelated navigation.
- An explicitly supplied faq_id that is not a positive decimal integer within the supported ID range, including array input or overflow, returns 404. Missing/nonpublic content returns 404 and unavailable identity lookup returns 503. Do not fall back to another FAQ or the home page.
- Do not resolve a miss in one language through a current/alias row in the other language: an expired English URL must not survive merely because the Thai current path happens to share its slug.

Use per-FAQ current URL pairs for canonical, Open Graph URL, hreflang th/en, and x-default (Thai). Extend FAQ-specific layout hooks without changing unrelated pages' metadata. English remains represented separately even when its fields fall back to Thai, consistent with the approved two-language URL policy.

Include both current canonical language URLs per public FAQ in the sitemap, with actual FAQ updated_at dates. Include no aliases or expired URLs. Preserve static/blog entries and existing 503 behavior for failed FAQ sitemap dependencies. No GET, redirect, sitemap, or language-switch operation records a read; existing qualifying browser events still deduplicate by unchanged FAQ ID/session.

## 8. Backfill, expiry deletion, and rollout

Extend faq:backfill-slugs to scan all FAQs requiring localized initialization, not only rows with an empty legacy slug, in ID-ordered batches of 200. Operate transactionally per FAQ and support restart after interruption.

Preserve nonempty legacy slugs, question/answer/category content, IDs, timestamps, order, and publication. Already initialized consistent rows are no-ops: do not change current URLs, counters, initialization times, or alias expiry. Report counts and failures without printing content or secrets. Inconsistent/partially initialized metadata must cause a nonzero result and a bounded diagnostic, not silent reconstruction of unknown history.

Add faq:prune-slug-history. Delete only alias rows with expires_at <= the command's UTC cutoff in bounded batches of 500; never delete current routes, revision state, FAQ content, receipts, or daily aggregates. Repeated execution is safe. Register it daily at 02:30 Asia/Bangkok using the existing scheduler and without overlapping executions; enable the scheduled task only when the backend feature is active. Failure is visible through a nonzero command result and structured operation/error context, not a successful deletion count.

Operational order, to be expanded in the implementation plan:

1. Record backups and current runtime configuration without exposing secrets. Deploy code with both flags false so old behavior does not access uncreated tables.
2. Apply only the new additive migrations through the established deployment process. Do not run unrelated pending migrations as part of these instructions.
3. Drain old FAQ writers, activate the backend flag, refresh relevant configuration/workers, and ensure all subsequent editor writes use the transactional writer. Old/new FAQ writers must not overlap during activation/backfill.
4. Run the extended backfill; rerun it to establish idempotence. Verify two states/current routes per FAQ, uniqueness, finite deadlines for aliases, preserved legacy identifiers/content/timestamps, and unchanged analytics totals.
5. Deploy public code with its flag false, verify API contract compatibility with old-client behavior, and verify the new API with controlled fixtures.
6. Verify actual Scheduler execution and time synchronization on production; registration alone is insufficient. Confirm migrations/backfill and representative Thai/English current/old URLs before activating the public flag.
7. Enable the public feature and refresh its configuration. Check redirects, same-FAQ language navigation, metadata, sitemap, and preserved analytics in the authorized deployment window.

No production command or write is performed by writing this specification. Production Scheduler, database collation, process-drain mechanism, backup recovery procedure, and actual storage footprint remain environment facts to establish during planning/deployment, not assumed successes.

After localized URLs have been exposed, disabling the resolver or rolling back to the old public detail client can break newly shared links. Keep route metadata and the compatible resolver available during rollback; use a compatibility release or forward fix, and pause FAQ writes if their invariants cannot be maintained. Do not prescribe a blanket feature-flag rollback after exposure or drop tables/reset revisions. Before public exposure, the public flag can safely remain false while backend preparation is completed.

## 9. Failure, security, and acceptance evidence

Preserve authentication/permissions and server-side input validation on editor writes. Encode all path segments and reject separators, invalid shapes, overlong slugs, and mismatched identities. Build redirect targets only from validated local route components and the configured canonical origin. Escape titles/metadata and retain existing HTML sanitization.

A write failure preserves the previously committed FAQ and its URLs and returns a safe retryable error instead of reporting success. Read-time infrastructure failure returns 503; a genuine missing/expired/nonpublic route returns 404. No recovery path updates alias deadlines or turns either case into a redirect to a generic page. Cache-Control: no-store prevents a conforming cache from storing new alias responses; it cannot invalidate a redirect already cached by another release or guarantee behavior of nonconforming intermediaries.

The implementation must obtain fresh evidence for:

| Area | Required checks |
| --- | --- |
| Generation | Initial Thai/English routes; duplicate titles; Unicode/marks; "0"; punctuation-only fallback; truncation; reserved legacy collisions; bounded retry exhaustion and overflow failure. |
| Editing | Thai-only, English-only, both, and English-fallback edits; answer-only and equivalent-normalization saves; no accidental URL or alias creation on category/publication-only changes. |
| Repeated changes | A/B/C all resolve directly to current in their own locale; returning to an old title uses a higher revision; old expiry does not extend. |
| Expiry | Just before, exactly at, and after one-year deadlines; February 29 anniversary; Bangkok/UTC conversion; expiry without pruning; cross-language identical-slug case; safe repeated pruning. |
| Integrity | Injected write failure rolls back content/routes/state; concurrent saves maintain one current route per language, unique identities, and history for each committed transition; test target MariaDB/MySQL semantics as well as test fixtures. |
| Backfill | Existing nonempty legacy values and content/timestamps retained; existing missing slug initialized; hidden FAQs included without public leakage; interruption/restart; second run no-op; inconsistent partial state rejected. |
| API boundary | Legacy list/detail compatibility before/after activation; new current/alias/identity envelopes; disabled and uninitialized behavior; malformed identity/locale/URL data; 404 versus 503; no HTTP redirect following or expiry resurrection. |
| Public browser | Distinct localized links; Unicode encoding; accordion address changes without navigation/history growth; close/filter restoration; same-FAQ language switching from detail and an open accordion; desktop/mobile links and no-JavaScript fallback. |
| SEO/publication | Current self-canonicals, matching Open Graph/hreflang/x-default, both sitemap URLs with real dates; no aliases in sitemap; hidden/deleted FAQ or category returns 404 for old/current paths. |
| Analytics regression | Same FAQ/session key across locales/renames; existing receipts/counts unchanged by backfill/pruning; duplicate read not recounted; content/redirect/language/sitemap GETs never post reads. |
| Operations | Both flags default false; migration/activation order; Scheduler invokes pruning; no-store on relevant responses; compatibility-preserving recovery after exposure. |

Use test-driven development for implementation behavior changes, focused feature/unit checks, relevant existing FAQ and localization regressions, isolated database concurrency coverage, and browser checks. The plan chooses exact commands and fixtures; do not point tests or failure injections at production data. No new tests have been run or implementation correctness claimed by this specification.

## 10. References and planning boundary

- Existing approved design: [FAQ search, stable URLs, and analytics](0003-faq-search-seo-analytics-design.md).
- [Google URL-change guidance](https://developers.google.com/search/docs/crawling-indexing/site-move-with-url-changes#start) recommends keeping redirects as long as possible, generally at least one year, and redirecting to the final destination directly. One-year expiry here is the user's selected retention trade-off: older bookmarks will stop working.
- [RFC 9111, no-store](https://www.rfc-editor.org/rfc/rfc9111.html#section-5.2.2.5) defines the cache directive used for redirect responses.

This file is the only new design record approved for creation. Independent readiness review must confirm that an implementer can plan from it alone, with material findings resolved before handoff. Approval of this written specification and the move to planning is a separate gate. The subsequent implementation plan requires its own approval before execution.
