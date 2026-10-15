# Branch Activity Log

## Scope

`User & Access → Activity Log` (`/activity-logs`) provides two read-only tabs:

- Back-office staff sign-in, sign-out, unsuccessful sign-in, and denied sign-in.
- User and role management, permissions, centres/departments, student/parent records, teaching history/periods, branch transfers, orders, receipts, invoices, and credit notes.

Affiliate, CRM Leads, Book Shipment Centre, incoming shipments, quiz, content, and eBook workflows are excluded from the new feed. Their existing logs remain unchanged. Automated jobs with no authenticated branch actor are not presented as staff actions.

This release does not implement live presence, session-expiration inference, export, or automatic log deletion. Closing a browser is not a recorded logout. Existing historical login information cannot be reconstructed.

## Storage and accuracy

The migration extends `branch_audit_logs`. New rows have `category = authentication` or `activity`; old rows have a null category and are retained without being mixed into the new feed, because their branch field represents the actor's branch rather than the record's branch.

New rows separately record the actor, actor branch, affected branch, related source branch for transfers, action, outcome, timestamp, and allowlisted before/after values. IP and user agent accompany the event. Password values, password hashes, remember tokens, session identifiers, signatures, arbitrary request bodies, and exception messages are not copied into the feed. Password updates are represented by a boolean marker.

Observer entries use the same database connection/transaction as their changes. Existing domain transactions, receipt retries, after-commit callbacks, and attachment persistence boundaries are preserved. The explicit `transactional_actions` allowlist additionally wraps legacy database-only writes; their rejected/failed requests roll back both data and success entries. Read requests are not wrapped. Failed requests are recorded separately after rollback. When a workflow has already committed data before a later attachment failure, the feed correctly retains those committed changes alongside the request failure. History/signature workflows also preserve their existing file/database boundaries; the logger does not claim to undo filesystem writes.

Bulk update/delete paths in the student/order/receipt controllers dispatch individual model events. A request failure deliberately contains no submitted target IDs or input values, which could belong to another branch.

Login listeners record credential failures as unverified, central-only events, with no user or branch inferred from submitted email. Known authenticated staff blocked by an inactive branch or disabled account are recorded as denied, not successful sign-ins. The automatic logout during disabled-account rejection does not create a misleading second event.

## Access

- `activity-log-list` controls the menu, index, and details. It belongs to the Users module and is distinct from the pre-existing teaching `view-log` permission.
- The migration grants it to existing standard `SystemAdmin`, `Admin`, and `BranchAdmin` roles. Custom roles must be enabled deliberately through Role Management. Teachers do not receive it automatically.
- Standard role templates include the permission for Admin and BranchAdmin.
- Branch users see only permitted events affecting their branch, including non-owner transfers into/out of it. A forged branch filter is rejected, and inaccessible detail IDs return 404.
- Cross-branch access requires the existing central-role and `all-centre` rules; adding `all-centre` to a branch role cannot bypass them.
- HQ SystemAdmins can read all branches and each other's logs. The existing HQ ownership requirement remains in force; assigning the role outside HQ does not grant this access.
- All other viewers, including HQ Admins with `all-centre`, cannot see an entire event when its actor is SystemAdmin or its subject is a SystemAdmin account. The same server-side query protects lists, totals, pagination, actor filters, and direct detail URLs. Inaccessible detail IDs return 404.
- Recorded owner flags retain protection after demotion or deletion; current SystemAdmin membership also hides older events from before promotion. Unknown-actor failed sign-ins remain visible within the existing central scope.
- SystemAdmin events are still recorded in full and retained for authorized owners. Hiding them does not delete logs or reverse the underlying business change. Incidental references to a current SystemAdmin teacher in an otherwise permitted, non-owner event remain anonymized.
- The feature exposes GET routes only, with no update/delete action.

## Deployment

Deploy the code and migration together during the normal deployment window. From the backend directory:

```sh
php artisan migrate --path=database/migrations/2026_10_12_000001_extend_branch_activity_logs.php --force
php artisan optimize:clear
php artisan permission:cache-reset
```

`optimize:clear` clears application caches; follow the environment's standard cache/rebuild procedure afterwards. If a long-running application worker is used, reload it as part of the existing deployment process.

Do not backfill missing before/after values or reinterpret old actor branches as target branches. No purge is scheduled; choose a retention/archive policy with the operator before implementing one. Database administration and backups remain separate from read-only application access.

## Verification

The new `tests/Feature/Branch/ActivityLogTest.php` exercises:

- English/Thai rendering and empty branches.
- Permission/module enforcement, direct detail access, forged filters, and central access rules.
- Whole-event exclusion for SystemAdmin actors/accounts, historical role changes, owner-to-owner visibility, and incidental teacher-reference anonymization.
- Actual login/logout, bad passwords, disabled accounts, and inactive branches.
- Role and permission changes with before/after values.
- Password redaction, HTML escaping, and no-op suppression.
- Data/log rollback, failure logging, bulk deletions, and transfers.
- Finance/teaching/branch changes, filters, pagination, legacy/excluded workflow isolation.
- Rejection of attempts to edit/delete the log through HTTP.

Run the focused and integration suites against `phpunit.xml`'s isolated SQLite database:

```sh
php vendor/bin/phpunit tests/Feature/Branch/ActivityLogTest.php
php vendor/bin/phpunit tests/Feature/Branch tests/Feature/Receipt tests/Feature/CreditNote tests/Feature/Localization/UserAccessLocalizedPageTest.php
```

On a PHP installation with SQLite extensions available but disabled, prepend `-d extension=pdo_sqlite -d extension=sqlite3` to these PHP commands; no `php.ini` or application `.env` change is needed.

Before releasing on Test, check with a branch account that the menu appears only when enabled, another branch's detail URL is rejected, a normal login/logout appears in the authentication tab, and a student/receipt edit has the correct values and branch. The local automated suite does not establish the deployed Test server's behavior.


### Initial targeted results — 12 October 2026

The final result for each of the following suites is passing: **75 tests, 548 assertions** across two targeted runs after correcting the transaction integration. The earlier wider 250-test run identified five regressions; the affected cases were fixed and rerun. This is not a claim that every test in the repository, or the deployed Test environment, was rerun.

| Suite | Passing tests |
| --- | ---: |
| ActivityLogTest | 26 |
| BranchTransferBulkTest | 29 |
| ReceiptNumberRetryTest | 2 |
| ReceiptNumberCollisionTest | 4 |
| BranchSaveActionsTest | 8 |
| CreditNoteHttpContractTest | 3 |
| CreditNoteFinancialSourceLockTest | 3 |

Run these selected suites together:

```sh
php vendor/bin/phpunit tests/Feature/Branch/ActivityLogTest.php tests/Feature/Branch/BranchTransferBulkTest.php tests/Feature/Receipt/ReceiptNumberRetryTest.php tests/Feature/Receipt/ReceiptNumberCollisionTest.php tests/Feature/Branch/BranchSaveActionsTest.php tests/Feature/CreditNote/CreditNoteHttpContractTest.php tests/Feature/CreditNote/CreditNoteFinancialSourceLockTest.php
```

Local browser verification covered the new menu, both empty tabs, clearing filters, and a date filter with only an end date. The local migration was applied. No code was committed/pushed or deployed to the Test server by this change. Frontend application source in `eimaths_th_new` was not changed; the UI is rendered by backend Blade templates.

### Visibility update — 15 October 2026

The focused ActivityLogTest suite passed **32 tests, 220 assertions**, using the isolated in-memory SQLite database on PHP 8.4.8. PHP syntax checks also passed for the controller and test file.

The regression cases verify that HQ SystemAdmins can view each other and all branches; non-owner roles cannot access SystemAdmin events through lists, pagination totals, actor filters, or direct detail URLs. Coverage includes sign-in/sign-out, normal branch writes, transfers, current versus historical roles, deleted accounts, protected account subjects with non-owner actors, and central failed sign-ins without a verified actor. Existing permission/module and cross-branch restrictions still apply. Logs remain recorded, and HTTP update/delete routes remain unavailable even to SystemAdmin.

```sh
php -d extension=pdo_sqlite -d extension=sqlite3 vendor/bin/phpunit tests/Feature/Branch/ActivityLogTest.php --testdox
```

This visibility-only update requires no additional migration beyond the original Activity Log migration. Changes are limited to the backend controller, regression tests, and this document. Frontend application source was not changed. The code has not been committed, pushed, or deployed to Test by this update; automated local results do not confirm deployed server behavior.
