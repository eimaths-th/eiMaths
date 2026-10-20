# Localized FAQ URLs Execution Record

## Approved scope and execution method

- Plan/branch approval: after both completed plans were presented, the user approved them and explicitly requested implementation on main (2026-10-20). The subsequent affirmative reply approved the separately described temporary MariaDB checkpoint; it does not broaden deployment authority.
- Execution method: Native. The root agent implements; independent preflight/final reviewers may inspect without implementation delegation.
- Plan 0005 SHA256: 2D0C6AA75D281658C124AB4897AEF8CA35688A9E0121806DF9570D2A12751E7A.
- Plan 0006 SHA256: 796976CE3BE15F6093ED953765BB2F547B6E7A53FA3716FA0771F57ABAEE7702.
- Approved Source 0004 SHA256: 594B41F825B68929467B0FC569EFF257907B94FF634B004349FAA41CE7400479.
- No commit, push, production migration, backfill, pruning, activation, deployment, or real .env edit is authorized by these plans.

## Source/revisions

- Backend: C:/Users/user/Desktop/eimaths/eiMaths, main worktree, branch main, start 893a312f987b2a5c019e1cf5c96a04bbcc0d3c44.
- Public: C:/Users/user/Desktop/eimaths/eimaths_th_new, main worktree, branch main, start 266e96624ac03a1ee74ec42e4c09628df636919a.
- Both origin/HEAD references identify main. Protection status is unknown; the user explicitly authorized main. No branch/worktree creation or switching is needed.
- Initial dirty state: only approved spec 0004 and plans 0005/0006 are untracked in backend. Public is clean. Preserve all three documents.

## Isolation

- Test baseline/isolation checks completed; both cached configuration files were verified absent. See the chronological evidence for each observed baseline and subsequent result.
- Ordinary PHP tests use APP_ENV=testing, SQLite :memory:, array sessions/cache, and localized mode false by default; individual owned tests may opt in.
- Intended-engine checkpoint approved and verified on an owned temporary MariaDB10.4.32 instance, loopback13307, fresh empty schema and non-root schema-only account. Real .env, production, existing3306 and existing/root credentials remain outside scope.

## Per-task RED/GREEN

| Plan | Task | State | Evidence |
| --- | --- | --- | --- |
| Backend 0005 | 1 schema/config | completed | RED missing table (1 failure); GREEN 1 test / 16 assertions; legacy FaqSlugTest 5 / 21. |
| Backend 0005 | 2 creation | completed | RED 7 behavioral failures / 8 cases; GREEN 8 tests / 37 assertions; legacy slug 5 / 21 and detail API 1 / 13. |
| Backend 0005 | 3 editing/concurrency | completed | Feature RED4 then GREEN4/22; engine RED1vs9 then GREEN1/121, MariaDB10.4.32 REPEATABLE-READ, 3x8 workers; legacy creation/slug passed. |
| Backend 0005 | 4 maintenance | completed | RED3 owned failures; GREEN3/23, >500 aliases, nonzero analytics/receipt snapshots retained; legacy slug5/21, creation8/37, editing4/22 passed. |
| Backend 0005 | 5 legacy projection | completed | Assertion-quality correction then RED2 missing metadata; GREEN2/18; legacy API4/18 and detail1/13 passed. |
| Backend 0005 | 6 resolution API | completed | RED4 then GREEN4/60; entire FAQ/CMS subset68/707 passed. Includes maintenance error-contract regression and no GET analytics inserts. |
| Backend 0005 | 7 verification record | completed | Final focused68/707 and guarded engine1/119 pass; full1143/36101 retains exactly historical63 failures/1 error/3 skips; final review1/1 closed, limitations recorded. |
| Public 0006 | 1 default config | completed | RED null config; GREEN 1 / 1, legacy detail 4 / 33 and boundary 8 / 30. |
| Public 0006 | 2 list boundary | completed | RED 2 behavioral failures / 3 cases; GREEN 3 / 8; legacy boundary/detail unchanged. |
| Public 0006 | 3 resolution | completed | Corrected complete fake, RED 200 vs 301; GREEN 4 / 42, legacy detail/boundary unchanged. |
| Public 0006 | 4 language identity | completed | RED old referer slug; GREEN 3 / 22, URL regression 4 / 42. |
| Public 0006 | 5 SEO/sitemap | completed | RED 3 metadata/sitemap failures; GREEN 3 / 19; canonical 1 / 43, sitemap 3 / 21. |
| Public 0006 | 6 fixture characterization | completed | Existing 10 browser URL cases passed before and after transport changes. PHP/Node syntax passed. Localized fixture uses validated loopback server per ruling 2. |
| Public 0006 | 7 accordion language context | completed | RED 5 owned missing-ID failures / 6 browser cases, real alias301 already passed; GREEN all6 plus affected URL/search/read suites40/40. |
| Public 0006 | 8 final verification | completed | After final cache correction, full PHP278/1892 and browser83/83 pass; combined contract review/final independent review1/1 complete; deployment remains outside scope. |

## Backend functional results

Fresh baseline: PHP 8.2.12 / PHPUnit 10.5.63, `vendor/bin/phpunit --do-not-cache-result --filter 'Faq|ContentLocalization'`, exit 0, 42 tests / 465 assertions. Tasks 1-2 checks passed as recorded above. Historical full-suite failures remain separately disclosed.

## Intended-engine results

Verified bounded test conditions: user-approved owned MariaDB10.4.32 at127.0.0.1:13307, strict mode/InnoDB/REPEATABLE-READ, non-root schema-only account, empty tables/views guard. Engine RED produced revision1 rather than9 before edit implementation; GREEN completed three rounds of eight workers, 1 test/121 assertions, preserving every committed transition. Final bounded engine verification also passed1/119; the temporary instance and private profiles were then removed under the approved cleanup, with ownership/path checks recorded below. No production database was used. This does not establish universal concurrency correctness or production-runtime readiness.

## Public functional/browser results

Verified final controlled public results: full PHP278 tests/1892 assertions and full browser83 cases passed after the cache correction. Earlier baseline254/1760, pre-correction268/1852 and10 browser URL cases are retained as chronological evidence. These suites use fake backend transport and controlled loopback documents; real deployed backend/browser/server readiness remains unverified.

## Full-suite limitations

Public final full PHP278/1892 and browser83/83 passed. Backend full PHP completed:1143/36101, exit2,63 failures/1 error/3 skips. Every current nonpassing identity/outcome matches the committed historical67-item inventory; no new or changed outcome was observed. Full backend suite is NOT passing and unrelated repairs remain outside scope. See the final evidence and complete inventory below.

## Cross-plan contract review

Three static preflight reviews completed and all three rulings were resolved within the approved Source. The combined serializer/adapter, redirect, language, SEO and analytics seams were statically traced in final review; controlled runtime results and limits appear below.

## Independent final review

Final combined independent review round1/1 identified one Low public cache-contract issue. The Native implementation was corrected after behavioral RED, focused GREEN passed, and the same reviewer closed the finding on recheck. No blocking issue remains in the inspected scope; fresh full validation evidence and its limitations are recorded below.

## Deployment prerequisites still unverified

Production migration/activation, old-writer draining, deployed backfill/API smoke checks, and real scheduler execution remain unverified and require separate authorization. Intended test database isolation is verified, but does not substitute for production-runtime evidence.

## Recovery constraints

Keep stable legacy identifiers, collected metadata/revisions, current localized routes, aliases until their independent one-year deadlines, and analytics/receipts. Do not use blanket flag-off rollback after public URL exposure.

### Preflight rulings (2026-10-20)

1. Backend cache middleware placement: reviewer found app/Http/Kernel.php maintenance middleware can return/throw before the proposed final global middleware entry. Source section 6 requires JSON/no-store on new API errors. Resolution for Task 6: place the focused middleware before maintenance, retaining namespace scope, and add an HTTP regression using a fake maintenance dependency without changing real maintenance state. This corrects the plan's internal placement instruction; scope, wire contract, task order, and acceptance remain unchanged. Cost if wrong: new endpoint errors may leak the old error/cache shape. Implemented and regression-verified; see chronological task evidence below.
2. Public browser redirect transport: reviewer verified installed Playwright Chromium source and a bounded isolated Chrome/loopback probe. Both page.route and context.route intercept only the initial redirected request; probe route_hits=[/redirect], server_hits=[/target], statuses=[301,200]. Proposed fake-origin-only fulfillment cannot prove the specified real browser follow. Resolution for Task 6: preserve the legacy fixture, use an ephemeral loopback HTTP server for localized document requests, forward actual PHP kernel status/headers/body, pass a validated dynamic loopback origin into PHP, and close owned resources on teardown. Localized expectations use that origin. Source's real 301/302 proof, application contracts, task order, and checkpoint are unchanged. Cost if wrong: browser evidence could depend on external DNS or fail to exercise redirect following. Implemented and regression-verified; see chronological task evidence below.
3. Intended-engine environment: missing test profile was an external checkpoint, not a passing concurrency result. The ruling prohibited use of3306, real .env and existing/root credentials. After explicit user approval, the owned13307 instance/profile satisfied the checkpoint and the behavioral RED/GREEN ran. Cost if wrong: stress evidence could be invalid or an unrelated database could be affected; ownership, empty-schema and non-root guards were verified before proceeding.

### Commands and TDD provenance

- Backend baseline: `C:/xampp/php/php.exe vendor/bin/phpunit --do-not-cache-result --filter 'Faq|ContentLocalization'`, exit 0, 42 tests / 465 assertions.
- Public baseline: `C:/xampp/php/php.exe vendor/bin/phpunit --do-not-cache-result`, exit 0, 254 tests / 1760 assertions.
- B1 RED: FaqLocalizedSchemaTest, exit 1, hasTable assertion false at line 17. No setup error.
- B1 GREEN: same file, exit 0, 1 / 16. FaqSlugTest exit 0, 5 / 21.
- B2 RED: FaqLocalizedCreationTest, exit 1, seven failures showing absent metadata/current routes; disabled legacy case passed.
- B2 GREEN: same file exit 0, 8 / 37. FaqSlugTest 5 / 21 and FaqDetailApiTest 1 / 13, both exit 0.
- B3 feature RED: FaqLocalizedEditingTest, exit 1, 4 failures: unchanged initial slugs and 302 rather than required 503 on failure/overflow. No edit implementation added; intended-engine RED not run because checkpoint profile is unavailable.

### Public task evidence (2026-10-20)

All PHP commands use C:/xampp/php/php.exe, vendor/bin/phpunit --do-not-cache-result, APP_ENV=testing, SQLite :memory:, array cache/session. Tests opt in via config only.

- P1 RED: FaqLocalizedDefaultTest exit1, null is not false. GREEN exit0 1 / 1. Existing FaqDetailPageTest 4 / 33 and FaqContentBoundaryTest 8 / 30 passed.
- P2 RED: FaqLocalizedClientTest exit1, legacy URL instead of current Thai and malformed metadata accepted. GREEN exit0 3 / 8; boundary 8 / 30 and detail 4 / 33 passed.
- P3 setup correction: first RED returned503 because the plan's legacy-endpoint fake omitted active localized metadata added by Task2. Corrected that fake to the full published localized record (no production edit). Repeated RED returned200 rather than301, the intended missing behavior. GREEN FaqLocalizedUrlTest exit0 4 / 42; detail/boundary passed unchanged.
- P4 RED: language route used localhost + Thai referer slug rather than configured-origin current English URL. GREEN FaqLocalizedLanguageTest exit0 3 / 22 and URL regression 4 / 42.
- P5 RED: three cases failed on wrong Thai alternate, missing current sitemap entries, and missing encoded Unicode. GREEN FaqLocalizedSeoTest exit0 3 / 19, CanonicalMetadataTest 1 / 43, FaqSitemapTest 3 / 21.
- P6 characterization: `node node_modules/@playwright/test/cli.js test tests/browser/faq-url.spec.mjs`, installed Chrome, PHP_BINARY=C:/xampp/php/php.exe, exit0 10/10 before and after fixture changes. PHP -l and node --check passed. Test-runner NO_COLOR/FORCE_COLOR warning was cosmetic environment output, not an application warning.
- P6 ruling2 implemented: fresh loopback port0 server forwards real PHP response status/headers/body, validates origin in PHP, aborts child rendering on disconnected responses, and closes server/connections on page teardown. Legacy mode retains fixed fake origin/data and old positional arguments. This fixture still does not prove persistent cookies, a real backend, or CDN visual layout.

### Public browser and verification evidence (2026-10-20)

- P7 RED: new localized browser file exit1, five failures due to header links lacking faq_id=11. All four language/viewport cases first reached their correct localized address. The sixth case already passed through the real PHP301 and automatic browser follow; no DNS/external fixture failure was counted as RED.
- P7 GREEN: after the JS snapshot/SetAnswerUrl and Blade data attributes, new localized file exit0 six passed. Combined FAQ URL/search/read/localized command exit0, 40 passed. Both desktop/mobile language controls restore exact original hrefs, preserve history length, and reach the same FAQ across languages.
- Public full PHP suite: `C:/xampp/php/php.exe vendor/bin/phpunit --do-not-cache-result`, exit0, 268 tests / 1852 assertions (9.309s). This proves controlled PHP contracts, not real backend deployment.
- Scoped diff checks passed after removing whitespace introduced in two class-member insertions. The test comment now names the actual loopback server transport.
- Correction review: the preflight reviewer re-read the actual two helper hashes and narrow HTTP/SEO seams; no new material issue found statically. It confirmed loopback document continuation, validated CLI-only origin, child cancellation/timeouts, teardown, and preserved legacy fixtures. This is not the pending final combined review.

### Pending intended-engine permission proposal

Read-only inspection verified installed C:/xampp/mysql/bin/{mysqld,mysql,mysqladmin,mysql_install_db}.exe and MariaDB10.4.32; port13307 had no TCP listener and C:/Users/user/AppData/Local/Temp/faq-slug-db-20261020-13307 did not exist in the inspected snapshot. No new instance, files, schema, credentials, service, or DB connection were created by inspection. The initializer syntax still needs safe confirmation before actual provisioning; mysql_install_db does not accept --no-defaults.

Proposed separately authorized action: create an owned temporary MariaDB instance bound only to 127.0.0.1:13307 in a new verified Temp tree, with explicit private datadir/log/PID, a fresh empty faq_slug_test_* schema and fresh non-root schema-only test account. Generate test credentials privately, expose no credential through argv/output, and set FAQ_SLUG_TEST_* only on test processes. Run the planned pre-implementation concurrency RED, then the owned edit implementation/GREEN and remaining tasks. Stop only the owned child instance and safely remove only its verified Temp tree afterward. Do not register a Windows service or touch the existing3306 service. User permission/profile is pending; no provisioning has occurred.

### Checkpoint handoff (2026-10-20)

- Latest public full PHP verification after formatting cleanup: exit0, 268 tests / 1852 assertions (7.921s).
- Latest full public browser run: `node node_modules/@playwright/test/cli.js test`, Chrome channel, exit0, 83 passed (1.1m), including the real loopback PHP301/302 navigation. No browser warning remained after removing the conflicting NO_COLOR test-process variable.
- Latest backend completed-task checks: schema exit0 1 / 16 and creation exit0 8 / 37. Editing still has the four expected RED assertions recorded above; edit production code is intentionally not added before the intended-engine pre-implementation gate.
- Backend Tasks4-6 and combined final review remain pending. Public Task8 remains in_progress because cross-plan implementation/review and activation readiness are not complete. Do not claim the overall feature complete.
- Both feature flags remain default false; real .env files and production state were not changed. No commit/push was performed.
- Await one concrete checkpoint decision: permission to provision/use/clean up the proposed private temporary MariaDB instance/profile, or an operator-supplied verified dedicated profile. No credentials should be sent in chat.

### Intended-engine checkpoint authorization and resume (2026-10-20)

- The user explicitly approved the concrete temporary MariaDB provisioning/use/cleanup proposal in the latest affirmative reply. This authorizes only the owned loopback13307 instance, fresh schema/account and Temp resources described above, not the existing3306 service or production data.
- Resume retains both approved plan/spec hashes and main branches; no repository drift outside recorded task files was found.
- Root retains Native feature/test implementation. An infrastructure helper provisions only the separately authorized external Temp test environment; it must not edit repository implementation or real .env.
- Tasks1-2 and public Tasks1-7 retain recorded evidence. Backend Task3 resumes before its engine RED step. Final combined independent review remains round0/1.

### Backend resumed task evidence (2026-10-20)

- Checkpoint ownership independently verified before engine RED: listener127.0.0.1:13307 belongs to PID18932, executable C:/xampp/mysql/bin/mysqld.exe, --no-defaults and owned Temp datadir command line. Private profile uses fresh non-root schema-only account; no credentials logged or copied into repository.
- B3 engine RED: `php vendor/bin/phpunit --do-not-cache-result tests/Integration/FaqSlugConcurrencyTest.php`, exit1, actual revision1 rather than9 after eight successful controller saves. 42 assertions; this was behavioral RED, not worker/bootstrap failure. Environment MariaDB10.4.32, InnoDB, strict SQL mode, REPEATABLE-READ.
- B3 production members then added: Synchronize/transactional Update and controller delegation, with existing permissions/validation retained. Feature GREEN4 tests/22 assertions. Engine GREEN1/121, three rounds of eight workers, revisions9/17/25 and history8/16/24 validated, English current unchanged. This is finite stress evidence under recorded conditions, not universal race-freedom proof.
- B3 regression creation8/37 and legacy slug5/21 passed.
- B4 RED: three owned failures (no metadata backfill, command not registered, schedule absent). Maintenance test strengthened within Source acceptance: preexisting read_count7 and receipt retained, plus501 extra expired aliases to exercise more than one500-row batch. GREEN3/23, then legacy slug5/21, creation8/37, editing4/22 all exit0. Actual production backfill/prune were not run.
- B5 initial test had undefined-array access for missing metadata and null-path assertions could accept absent fields. Corrected tests before production to assert key/structure. Repeated RED exit1, two missing-localized_slugs assertions. Serializer/API change then GREEN2/18, FaqApiTest4/18, FaqDetailApiTest1/13.
- B6 RED: four owned failures: missing new routes/current resolution and incomplete-state status, plus HTML maintenance503 violating JSON. Added resolver/by-ID/controller/cache middleware and routes. Per Source-based preflight ruling1, focused global cache middleware is before maintenance rather than appended after it. Fake MaintenanceMode service exercises thrown and returned503 without any real maintenance state mutation. GREEN4/60.
- Source and plan artifact hashes remain unchanged. Corrections above resolve internal test/placement defects without changing approved behavior, checks, order, or operational checkpoints.

### Combined verification entry (2026-10-20)

- Backend focused command expanded to include the exact existing ContentManagementLocalizedPage class: `php vendor/bin/phpunit --do-not-cache-result --filter 'Faq|ContentManagementLocalizedPage'`, exit0, 68 tests / 707 assertions (35.435s). The narrower baseline regex had42 cases; the four additional CMS-language cases are now explicitly covered.
- Historical main nonpassing inventory is available in committed docs/plans/0003-0004-faq-execution-record.md:167-233 (63failure/1error/3skip identities), plus the intermittent BranchIsolation header/sorting failure at398. Current full suite starts with complete console/JUnit capture for per-identity comparison.
- Backend full source remains stable during that run and the independent review. Current verification logs are owned OS Temp artifacts; final identity comparisons and material results will be copied here before any related cleanup.
- Final combined independent review round1/1 is requested under reviewing-code. It covers both uncommitted application/test diffs and new files, approved Source/plan contracts, recorded preflight rulings, and trust/data/rollback boundaries. No production operation is implied by a review verdict.


### Final-review correction within round 1/1 (2026-10-20)

- Independent combined review used the most capable available review model, read-only, against both current main diffs, new files, Source0004, plans0005/0006 and recorded preflight rulings. No additional Critical/High/Medium issue was identified in that inspected scope.
- One Low cache-contract finding: the public exception-only callback excluded HEAD, and returned maintenance templates bypassed it entirely. Source0004 section6 requires no-store on localized-detail errors; installed Laravel registers GET+HEAD and its maintenance middleware may return503 directly. Cost if left unresolved: the declared error-cache policy would be inconsistent for those requests; actual shared-cache impact was not established.
- Root added ten parameterized real HTTP boundary cases: GET/HEAD crossed with malformed404, upstream404, upstream503, thrown-maintenance503 and returned-template503. RED: six missing-no-store failures, 10 tests/28 assertions, exit1; no setup error. The four existing GET paths remained passing.
- The bounded correction replaces the exception callback with PreventFaqDetailErrorCaching, prepended before maintenance. It only applies in localized mode to GET/HEAD error responses in the FAQ-detail namespace or explicit FAQ language-switch errors, preserving status/body and unrelated paths. Installed Routing Pipeline renders exceptions into responses before outer middleware unwinds.
- GREEN: FaqLocalizedUrlTest plus FaqLocalizedLanguageTest, exit0, 17 tests/104 assertions. The same independent reviewer rechecked the three changed files within round1, closed the finding, and found no blocking issue remaining. Fresh full public checks follow; no second review round was started.
- Verification limits retained: the engine harness stresses writers, not simultaneous resolver readers. Consistent-read behavior is supported by static locking/query analysis, not a runtime reader-race proof. Public browser documents use real PHP301/302 via loopback and a fake backend; they do not establish real two-server HTTP, persistent-cookie, CDN or production behavior. Rename read-dedup continuity is supported by the unchanged FAQ-ID/session key and existing tracking regressions, not a new real-server end-to-end test.

### Final verification and contract handoff (2026-10-20)

All results below were gathered on the final application/test snapshot, after the review correction where applicable. Backend source stayed unchanged throughout its full run and review; the public correction did not change backend code. Root checked all command exit codes, available result summaries, diagnostics and the complete structured nonpassing inventory. No deployment, commit or push was performed.

| Scope | Command | Observed result | Status |
| --- | --- | --- | --- |
| Backend full PHP | C:/xampp/php/php.exe vendor/bin/phpunit --do-not-cache-result --log-junit <owned Temp XML> | exit2; 1143 tests/36101 assertions, 63 failures,1 error,3 skips; 557.447s | Run verified; full suite NOT passing |
| Backend FAQ/CMS languages | C:/xampp/php/php.exe vendor/bin/phpunit --do-not-cache-result --filter 'Faq\|ContentManagementLocalizedPage' | exit0;68 tests/707 assertions;61.947s; no failures/errors/skips/warnings | Verified in controlled tests |
| Backend intended-engine writers | C:/xampp/php/php.exe vendor/bin/phpunit --do-not-cache-result tests/Integration/FaqSlugConcurrencyTest.php | exit0;1 test/119 assertions;14.492s; MariaDB10.4.32,REPEATABLE-READ,3 rounds x8 workers | Verified bounded writer stress |
| Public full PHP | C:/xampp/php/php.exe vendor/bin/phpunit --do-not-cache-result | exit0;278 tests/1892 assertions;30.015s; no failures/errors/skips/warnings | Verified in controlled tests |
| Public full browser | node node_modules/@playwright/test/cli.js test | exit0;83 passed;1.4m; installed Chrome, controlled loopback PHP documents/fake backend | Verified within fixture scope |
| Scoped diffs | git diff --check in each repository | both exit0; public bootstrap has only Git's CRLF-to-LF normalization notice | Verified whitespace checks |

The engine assertion count differs from the earlier121 because the startup-barrier polling loop makes timing assertions per iteration. The same three rounds/eight workers and every revision/history invariant ran again; this is not reduced scenario coverage.

Full-suite comparison was independently repeated by root and the read-only inventory reviewer. Every current nonpassing identity/outcome exactly matches the67-item committed historical inventory at docs/plans/0003-0004-faq-execution-record.md:167-233: added0,changed0. The additional intermittent BranchIsolation header/sorting failure recorded for an older main run at398 passed now with18 assertions. Do not infer its root cause or permanent resolution from one passing run. Historical diagnostics are not complete enough to assert equality of every stack trace. The Ebook error still reports unavailable pdfinfo; the Affiliate translation mismatch still involves English/Thai privacy heading keys. Existing failures remain outside this approved repair.

The three skipped cases are the existing Affiliate click/enrollment and Credit Note concurrency checks. They require their own isolated MySQL/opt-in settings; the FAQ-only temporary database authorization was not broadened to run them.

Backend full artifacts retained outside the disposable database tree:

- XML: C:/Users/user/AppData/Local/Temp/faq-localized-backend-final-20261020.xml; SHA256 4B9FC7CBC5495A2314D3ECB4B67A2144067798379CF5F6E7E9542C323EBAC81B.
- Console log: C:/Users/user/AppData/Local/Temp/faq-localized-backend-final-20261020.log; SHA256 1A60C55E951D21B59D27BF728F25A68943C3B809F38A368EB78E9C7C2A4B5078.
- All67 nonpassing identities are copied below so the record survives temporary artifact cleanup.

Final snapshot identity:

- B main HEAD893a312f987b2a5c019e1cf5c96a04bbcc0d3c44;30 changed feature files excluding docs; manifest SHA256 7D34E2B1CB82438AD335736BFFDE9FBF0498887A60372B341C1162EDC41BD1C7.
- P main HEAD266e96624ac03a1ee74ec42e4c09628df636919a;21 changed feature files; manifest SHA256 8F7F2BD70204F5D2CF06710EE6AC771029E111D4744908242F261A8935571BFC.
- Each manifest hashes UTF-8 LF-joined sorted relative-path|file-SHA256 lines from git ls-files --modified --others --exclude-standard, excluding docs/. These identify the uncommitted tested files rather than pretending HEAD contains the implementation.
- Approved Source/plan hashes rechecked unchanged. Neither repository has bootstrap/cache/config.php. Both flags remain default false and real .env files were not edited.

Combined static contract trace, independently reviewed:

- Backend serialization, resolver envelope and public adapter agree on exact locale/requested slug/positive FAQ identity, both current ID+revision suffixes, current-versus-alias shape and UTC expiry semantics.
- Redirects use the configured public origin and current same-language destination. Language switching uses FAQ identity; paired SEO/sitemap use current localized paths.
- Existing FAQ IDs, read counters, receipts and FAQ-ID/session deduplication keys remain the analytics identity. New server-side GET resolver paths do not call read recorders/forwarders. Maintenance snapshots include nonzero analytics and receipts.
- Required production behavior is implemented and controlled tests above pass within their stated scopes. Full-system, real two-server HTTP, persistent-cookie, production migration/activation/draining, production database/runtime and real scheduler execution are NOT verified. These remain the separate operational checkpoint; do not treat this record as deployment approval.

Final combined independent review round1/1 concluded no unresolved code finding in the inspected snapshot after the single Low correction. No optional unrelated change was implemented. The already-authorized temporary DB cleanup was subsequently completed and independently checked in the final cleanup entry below.

### Complete backend nonpassing inventory for the final run

- failure: Tests.Unit.Affiliate.AffiliateTranslationStructureTest::EnglishAndThaiAffiliateTranslationLeavesAndPlaceholdersMatch
- failure: Tests.Unit.CreditNote.CreditNoteEnumTest::ReasonEnumContainsExactlyTheApprovedTenStoredValues
- failure: Tests.Feature.Affiliate.AffiliateAnalyticsApiTest::test_active_owner_receives_stable_zero_contract
- failure: Tests.Feature.Affiliate.AffiliateAnalyticsApiTest::test_invalid_ranges_return_localized_validation_errors
- failure: Tests.Feature.Affiliate.AffiliateAnalyticsApiTest::test_disabled_analytics_feature_is_not_exposed
- failure: Tests.Feature.Affiliate.AffiliateAnalyticsPageTest::test_page_renders_default_evidence_controls_and_accessible_table
- failure: Tests.Feature.Affiliate.AffiliateAnalyticsPageTest::AffiliateAnalyticsUsesTheAdminLteContentShell
- skipped: Tests.Feature.Affiliate.AffiliateClickConcurrencyTest::ConcurrentFirstClicksProduceTwoEventsOneUniqueWindowAndAnAtomicDailyTotal
- failure: Tests.Feature.Affiliate.AffiliateCourseApplicationPermissionTest::BranchStaffNeedTheNewListPermissionToOpenCourseApplications
- failure: Tests.Feature.Affiliate.AffiliateDashboardAnalyticsTest::test_dashboard_shows_the_analytics_summary_only_when_enabled
- failure: Tests.Feature.Affiliate.AffiliateEmployeeAdministrationTest::NewEmployeeProfileAppearsInExistingApprovalList
- skipped: Tests.Feature.Affiliate.AffiliateEmployeeEnrollmentConcurrencyTest::ConcurrentFirstEnrollmentsCreateOneProfileAndOneAuditWithoutChangingStaffIdentity
- failure: Tests.Feature.Affiliate.AffiliateErrorTranslationTest::test_affiliate_payout_errors_have_user_facing_translations_in_thai
- failure: Tests.Feature.Affiliate.AffiliateFinanceNavigationTest::test_active_affiliate_can_render_dashboard_payout_and_withdrawal_pages
- failure: Tests.Feature.Affiliate.AffiliateFinanceNavigationTest::test_withdrawal_page_renders_zero_wallet_balances_when_projection_is_missing
- failure: Tests.Feature.Affiliate.AffiliateFinanceNavigationTest::test_inactive_affiliate_cannot_render_finance_pages
- failure: Tests.Feature.Affiliate.AffiliateFinanceNavigationTest::test_finance_pages_only_expose_the_authenticated_affiliate_profile
- failure: Tests.Feature.Affiliate.AffiliateFinanceNavigationTest::test_active_affiliate_navigation_contains_separate_finance_entries
- failure: Tests.Feature.Affiliate.AffiliateFinanceNavigationTest::ActiveUnverifiedEmployeeNavigationMatchesPortalAccess
- failure: Tests.Feature.Affiliate.AffiliateGuideTest::active_affiliate_can_read_the_guide_and_open_the_source_document
- failure: Tests.Feature.Affiliate.AffiliateGuideTest::active_affiliate_can_browse_every_curriculum_pdf
- failure: Tests.Feature.Affiliate.AffiliateGuideTest::unknown_curriculum_level_returns_not_found
- failure: Tests.Feature.Affiliate.AffiliateGuideTest::inactive_affiliate_is_redirected_away_from_the_guide_and_document
- failure: Tests.Feature.Affiliate.AffiliateGuideTest::affiliate_finance_navigation_links_to_the_guide
- failure: Tests.Feature.Affiliate.AffiliateGuideTest::affiliate_finance_sidebar_uses_the_english_guide_label
- failure: Tests.Feature.Affiliate.AffiliateIdentityVerificationTest::RegistrationStoresConsentEvidenceAndEmailVerificationStartsIdentityStep
- failure: Tests.Feature.Affiliate.AffiliateIdentityVerificationTest::AffiliateCanSubmitEncryptedIdentityAndPrivateDocument
- failure: Tests.Feature.Affiliate.AffiliateIdentityVerificationTest::InvalidNationalIdAndWrongDocumentTypeAreRejected
- failure: Tests.Feature.Affiliate.AffiliateIdentityVerificationTest::AdminCanReviewRejectResubmissionAndApproveWhileEmployeeApprovalStillWorks
- failure: Tests.Feature.Affiliate.AffiliateLocaleTest::AffiliateDefaultsToThaiWhenPreferenceIsMissing
- failure: Tests.Feature.Affiliate.AffiliateLocaleTest::AuthenticatedEnglishPreferenceWins
- failure: Tests.Feature.Affiliate.AffiliateLocaleTest::UnsupportedPreferenceFallsBackToThai
- failure: Tests.Feature.Affiliate.AffiliatePendingApprovalAlertTest::AuthorizedAffiliateListUserCanReadCurrentPendingApprovalSummary
- failure: Tests.Feature.Affiliate.AffiliatePendingApprovalAlertTest::AuthorizedAffiliateListUserSeesPendingApprovalAlertHooksInTheBackendLayout
- failure: Tests.Feature.Affiliate.AffiliatePortalAccessTest::ActiveUnverifiedEmployeeCanOpenDashboard
- failure: Tests.Feature.Affiliate.AffiliatePortalAccessTest::ActiveVerifiedExternalCanOpenDashboard
- failure: Tests.Feature.Affiliate.AffiliatePortalAccessTest::ActiveUnverifiedExternalIsRedirectedToWaiting
- failure: Tests.Feature.Affiliate.AffiliatePortalAccessTest::NonActiveEmployeeIsRedirectedToEmployeeStatusPage
- failure: Tests.Feature.Affiliate.AffiliatePortalAccessTest::IneligibleUserReceivesPlainForbiddenResponse
- failure: Tests.Feature.Affiliate.AffiliatePortalAccessTest::IneligibleJsonRequestReceivesLocalizedForbiddenResponse
- failure: Tests.Feature.Affiliate.AffiliatePortalAccessTest::UnsafeRequestIsForbiddenInsteadOfRedirecting
- failure: Tests.Feature.Affiliate.AffiliatePortalAccessTest::AdministratorRoutesRemainUnchanged
- failure: Tests.Feature.Affiliate.AffiliatePortalAccessTest::EmployeePortalRequestsReachExistingDomainAndFeatureBoundaries
- failure: Tests.Feature.Affiliate.AffiliateRegistrationTest::PendingEmailAffiliateSeesGuestResendActionOnWaitingPage
- failure: Tests.Feature.Affiliate.AffiliateRegistrationTest::ActiveAffiliateIsRedirectedFromWaitingPageToDashboard
- failure: Tests.Feature.Affiliate.AffiliateRegistrationTest::PendingAffiliateCanViewWaitingPage
- failure: Tests.Feature.Affiliate.AffiliateRolePermissionTest::affiliate_user_without_cms_permissions_does_not_see_cms_menu_entries
- failure: Tests.Feature.Affiliate.AffiliateSecurePayoutRevealTest::test_admin_endpoint_reveals_to_authorized_user_without_email_verification
- failure: Tests.Feature.Affiliate.AffiliateSecurePayoutRevealTest::test_admin_list_html_and_json_keep_payout_details_masked_by_default
- failure: Tests.Feature.Affiliate.AffiliateSecurePayoutRevealTest::test_admin_page_only_renders_reveal_control_for_approved_rows_with_permission
- failure: Tests.Feature.Affiliate.AffiliateWithdrawalUiTest::test_active_affiliate_dashboard_exposes_summary_links_without_mutation_controls
- failure: Tests.Feature.Affiliate.AffiliateWithdrawalUiTest::test_affiliate_html_payout_and_withdrawal_forms_redirect_and_update_state
- failure: Tests.Feature.Affiliate.AffiliateWithdrawalUiTest::test_dashboard_does_not_render_withdrawal_cancel_forms
- failure: Tests.Feature.Affiliate.AffiliateWithdrawalUiTest::test_html_validation_returns_to_the_originating_finance_page
- failure: Tests.Feature.Affiliate.AffiliateWithdrawalUiTest::test_admin_withdrawal_page_renders_and_html_transitions_preserve_json_api
- failure: Tests.Feature.Affiliate.AffiliateWithdrawalUiTest::test_json_payout_and_withdrawal_endpoints_remain_json_contracts
- failure: Tests.Feature.Branch.BranchSaveActionsTest::test_history_comment_persists_for_single_book_enrolment
- skipped: Tests.Feature.CreditNote.CreditNoteConcurrencyTest::mysql_concurrency_scenarios_are_explicitly_skipped_without_opt_in
- error: Tests.Feature.EbookApiTest::test_real_pdf_is_converted_into_private_page_images
- failure: Tests.Feature.ExampleTest::test_the_application_returns_a_successful_response
- failure: Tests.Feature.Localization.UiLocalizedPagesTest::StudentsManagementPagesRenderInBothLocales with data set #12
- failure: Tests.Feature.Localization.UiLocalizedPagesTest::StudentsManagementPagesRenderInBothLocales with data set #13
- failure: Tests.Feature.Localization.UiLocalizedPagesTest::SecondaryStudentPagesExposeLocalizedLabels with data set #3
- failure: Tests.Feature.Navigation.MenuOrganizationTest::OperationalPagesAreGroupedByTheirWorkArea
- failure: Tests.Feature.Navigation.PendingWorkMenuBadgeTest::AffiliateManagementOwnsAffiliateAccountsCourseApplicationsWithdrawalsAndPendingBadges
- failure: Tests.Feature.Navigation.PendingWorkMenuBadgeTest::AffiliateBadgeIsNoLongerAttachedToUserAccess
- failure: Tests.Feature.Navigation.PendingWorkMenuBadgeTest::StockAndLogisticsParentShowsCentreBookShipmentBadge

### Authorized temporary-database cleanup (2026-10-20)

- The user received the results/limitations and durable record link before cleanup. Provisioning/use/cleanup had been explicitly approved at the intended-engine checkpoint; no new deployment authority was inferred.
- The infrastructure helper revalidated ownership: PID18932/start time, executable, --no-defaults, owned datadir and loopback13307 listener. The fresh test account verified zero tables/views after the final engine test.
- Graceful mysqladmin shutdown used the private client configuration without exposing credentials in output/argv. The owned process/listener disappeared.
- Before recursive deletion, the absolute Temp target and115 descendants were checked for path containment and reparse points. Only C:/Users/user/AppData/Local/Temp/faq-slug-db-20261020-13307 was removed, including its private profiles/credentials; no Windows service was registered.
- Existing3306 was not connected to. Observable listener metadata before/after matched PID4240 and CIM creation time2026-10-20T02:23:38.2452690Z. Its executable was unavailable, so no claim is made about that field or uninspected database contents.
- Root independently verified afterward: owned tree absent, PID18932 absent, listener13307 count0; execution record and separate full-suite log/XML preserved. Both artifact SHA256 hashes still match the final verification entry.
- Root recomputed both feature manifests after cleanup: unchanged7D34E2B1... for B and8F7F2BD7... for P, with the exact main HEADs retained. Both final git diff --check commands exited0; only the recorded public bootstrap normalization notice remains.
- Approved implementation/verification tasks and authorized Temp cleanup are complete. The source changes remain uncommitted on each main working tree. Production operations, real HTTP integration, scheduler activation and commit/push remain outside this execution.

### Subsequent commit authorization (2026-10-20)

- After receiving the final implementation report, the user explicitly requested committing all changes. This separately authorizes staging and committing the observed FAQ implementation, tests and approved documentation in both main repositories; push and production operations remain outside scope.
- Fresh pre-commit inspection found only the recorded FAQ files. Both application/test manifests exactly match the final tested snapshots above: B7D34E2B1CB82438AD335736BFFDE9FBF0498887A60372B341C1162EDC41BD1C7 and P8F7F2BD70204F5D2CF06710EE6AC771029E111D4744908242F261A8935571BFC. The approved Source and both plan hashes also remain unchanged.
- No application/test behavior was changed for this commit request. Existing passing focused/public/browser/engine evidence and the disclosed backend full-suite baseline remain unchanged. Fresh diff and staged-content checks are used for the commit boundary; the historical full suite is not relabeled as passing.
- Commit one cohesive FAQ feature snapshot per repository, including this record. Git history supplies the resulting commit identities; no self-referential commit hash is embedded here.
