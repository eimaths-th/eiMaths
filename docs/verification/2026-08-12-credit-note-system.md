# Credit Note System Verification Record

Date: 2026-08-12
Branch: `demo`
Environment: Windows, PHP 8.2.12, Laravel 10.50.2, PHPUnit 10, Node.js 24.16.0

## Scope

This record covers the approved demo implementation described in `docs/specs/2026-08-12-credit-note-system-design.md`. The implementation adds a separate Credit Note domain with Draft, Issue, and Cancellation states; exact integer-minor-unit calculations; receipt source snapshots; Affiliate tuition-only adjustments; scoped permissions; print/PDF; export; reporting; audit; observability; and guarded concurrency tests.

No production migration, permission assignment, environment change, commit, push, or deployment was performed.

## Confirmed configuration gates

- `CREDIT_NOTE_FEATURE_ENABLED=false` remains the `.env.example` default; the local demo `.env` currently overrides it to `true` for demo verification.
- `CREDIT_NOTE_FINANCIAL_ACTIONS_ENABLED=false` remains the `.env.example` default; the local demo `.env` currently overrides it to `true` for demo verification.
- Issuer legal identity and tax identifier remain blank by default.
- MySQL concurrency execution remains opt-in and is skipped unless the explicit isolated-database guard is satisfied.
- The permission migration creates the six approved web permissions but does not grant any role.
- Existing receipt/order metadata writers skip the new metadata columns until the additive metadata migration is present; Credit Note source reads and financial routes remain blocked by the feature gate until the migration/configuration gates are satisfied.

## Automated verification

| Command | Result |
| --- | --- |
| `php artisan test --without-tty tests\\Unit\\CreditNote` | 23 passed, 98 assertions |
| `php artisan test --without-tty tests\\Feature\\CreditNote` plus the affected Affiliate finance/wallet/foundation tests | 71 passed, 337 assertions, 1 explicit concurrency skip |
| Final rerun: Credit Note unit/feature tests plus the affected Affiliate tests and source-lock/refund regressions | 102 passed, 460 assertions, 1 explicit concurrency skip |
| `php artisan test --without-tty tests\\Unit\\Affiliate` | 29 passed, 336 assertions |
| `php artisan test --without-tty tests\\Feature\\Affiliate` | 239 passed, 934 assertions, 2 explicit SQLite/concurrency skips |
| `node --test tests/JavaScript/credit-note-editor.test.mjs tests/JavaScript/credit-note-revenue.test.mjs tests/JavaScript/affiliate-analytics-page.test.mjs` | 12 passed |
| PHP lint over the modified/new Credit Note, Financial, controller, request, migration, route, export, test, and Affiliate adapter files | 40 files passed |
| `git diff --check` | Passed |

Focused coverage includes exact satang arithmetic and VAT allocation, full original/cumulative corrected totals for partial credit, source metadata and eligibility, historical receipt/detail audit gaps, persistence immutability, Draft/Issue/Cancellation idempotency, number generation, Affiliate reversal/restoration, source locking, centre authorization, HTTP error/correlation contracts, register/editor escaping, PDF streaming, export scope, revenue compatibility, translation parity, PII-safe observability, audit/reconciliation commands, and feature-disabled behavior.

## Route and structural checks

`php artisan route:list --path=credit-notes --except-vendor -v` reports exactly nine routes:

1. Credit Note register
2. Export
3. Show
4. Draft update
5. Cancel
6. Issue
7. PDF
8. Print
9. Create from a receipt

Every route has authentication, correlation, feature, response handling, and the dedicated permission middleware. Issue and Cancel additionally require the financial-actions gate. No DELETE route is registered.

The Credit Note financial boundary contains no `float` or `double` token, and the PDF controller streams DOMPDF output without writing a public file. Existing gross receipt fields and receipt counts are preserved by the compatibility tests.

## Inconclusive or deferred checks

- The complete repository command (`php artisan test --without-tty`) was attempted after the focused suites. The runner produced no final result and remained idle for more than 20 minutes; only the PHP processes launched by that test run were terminated. Therefore, no complete-suite pass is claimed. The earlier complete-suite attempt exposed an Affiliate enum expectation that was updated to include the two new named ledger types before the focused regression run.
- MySQL row-lock/concurrency scenarios were not run locally because the explicit opt-in and isolated database guard were intentionally left disabled. SQLite cannot prove MySQL locking behavior.
- The source-audit and reconciliation commands were verified against PHPUnit fixtures through their command tests. They were not run against the local application database because applying the new migrations there was outside the approved demo scope.

## Required handoff gates before financial enablement

Accounting/operations must still approve and evidence the issuer legal identity, address, tax identifier and Head Office code; historical 7 percent VAT treatment; financially relevant line-type backfill; synthetic service-charge tax treatment; reason labels and usage; delivered-document cancellation procedure; representative Draft/Issued/Cancelled print/PDF samples; permission assignments; source-audit results; MySQL concurrency results; and reconciliation results.

Until those gates are signed off, production and shared environments must remain fail-closed and the demo must not be treated as production-ready financial functionality.

## Demo migration repair (2026-08-12)

The demo MySQL migration for `affiliate_commission_credit_note_adjustments` initially failed because Laravel's generated foreign-key and unique-index names exceeded MySQL's identifier-length limit. The migration now uses explicit short names and can complete a partially created table without dropping data. The demo rerun completed migrations `2026_08_12_000006` and `2026_08_12_000007`; schema inspection confirmed all four foreign keys and both approved unique indexes, and a second migrate reported `Nothing to migrate`.

The three existing dashboard receipt-summary actions were invoked against the demo MySQL database after the repair and returned HTTP 200. No production database or deployment was changed.
