# Bulk Branch Transfers with Basic Filters

Status: The combined design and this written specification were approved in conversation on 2026-09-21. Implementation was completed in the working tree on `main`; focused verification passed. The full PHPUnit suite remains non-green, so no full-suite pass is claimed.

## 1. Purpose and approved decisions

Extend the existing branch transfer page so central administrators can find and select several students or users, review the selection, and transfer the entire selection in one operation.

The user approved the following decisions:

| Concern | Approved behavior |
| --- | --- |
| Record types | Students and users, one type per batch |
| Filters | Source branch and name search; student search also matches student code |
| Selection | A paginated table with individual checkboxes and select-all for the current page |
| Selection lifetime | Retain selection across pages; clear it when the record type or applied filters change |
| Destination | One destination branch and one reason per batch; one optional destination department for user batches |
| Failure policy | All selected records transfer successfully together, or none transfer |
| Review | Show selected names, count, and destination before submission |
| Limits | 25 records per page and at most 100 selected records per batch |
| Compatibility | Preserve existing single-record requests and existing document ownership |
| Persistence | Reuse existing transfer history tables |
| Languages | Thai and English |

## 2. Verified repository context

The following facts were verified by reading the repository; they describe the existing implementation, not completed bulk functionality.

- `routes/branch-access.php` defines the page, target search, and submission routes at `/branch-transfers`, `/branch-transfers/targets`, and `POST /branch-transfers`.
- `app/Http/Controllers/BranchTransferController.php` requires authentication, the `branch-transfer` permission, and central-administrator eligibility. Search currently requires a name/code query of 2-100 characters, returns at most 30 records, and excludes users with `SystemAdmin` or `Admin` roles.
- The controller currently accepts one `record_id`, locks that record inside a transaction, checks source and destination access, updates the branch, and writes one `branch_transfers` row.
- A student cannot transfer while an order at the current source branch has a null status or status `0` or `1`. A successful student transfer sets `contacts.order` to `0`; issued documents are not reassigned.
- A user transfer validates the user's roles against the destination, sets the optional destination `position_id`, and deletes the user's personal access tokens inside the transaction.
- `app/Support/Branch/BranchAccess.php` defines central status, cross-branch access, assignable roles, and manageable users. Central status alone does not grant unrestricted cross-branch access.
- `app/Support/Branch/BranchContext.php` and `app/Providers/BranchServiceProvider.php` apply scoped reads, validate model writes, and write branch audit entries. Branch changes require the temporary `allowTransfer` context.
- `resources/views/branches/transfers.blade.php` contains the existing single-record form and inline search behavior. It uses translations from `lang/en/centre-management.php` and `lang/th/centre-management.php`.
- `database/migrations/2026_09_08_000001_add_branch_access_foundation.php` defines transfer history with one row per subject. It can represent a batch using multiple rows without a schema change.
- Existing regression coverage includes `tests/Feature/Branch/BranchWorkflowsTest.php`, `tests/Feature/Localization/BranchTransferLocalizedPageTest.php`, and `tests/Feature/Navigation/MenuOrganizationTest.php`.
- `tests/JavaScript` already uses Node's built-in test runner for browser behavior modules. `phpunit.xml` uses an in-memory SQLite database for PHP tests.

Project terminology in this specification follows the existing UI: a branch/centre is stored in `departments`; a user's department within a branch is stored in `positions`. These are different concepts despite their database names.

## 3. Scope and exclusions

The change covers candidate filtering, explicit multi-selection, batch validation and transfer, localized feedback, and focused verification of these behaviors.

It does not add advanced filters, mixed student/user batches, multiple destinations in one batch, partial success, select-all across every filtered page, background jobs, CSV import, a new history screen, new permissions, or a new database table. It does not change document ownership, settle pending orders, reassign roles automatically, or refactor unrelated branch or financial workflows. No separate glossary or architectural decision record is required.

## 4. User interaction

### 4.1 Candidate list and filters

- Default to the student record type. Offer a source-branch filter whose empty value means all branches the actor may access.
- Include accessible inactive source branches so records can leave them. Destination choices remain restricted to accessible active branches.
- Allow an empty search query. A source branch alone, or the all-branches filter, can load candidates without entering a name.
- Trim search input. A nonempty query must contain 2-100 characters. Search student name or code, and user name only. Do not add email as a search criterion.
- Combine the source-branch constraint with the grouped name/code search; an alternative code match must not bypass branch scope.
- Sort by name and then ID for stable pagination. Show 25 records per page, result count, and previous/next controls.
- Display name and current branch; include student code for students and email for users to distinguish similar names. These are display fields, not additional filters.
- Show localized loading, empty-result, and failed-search states. Ignore obsolete responses when a newer search, page request, or record-type change supersedes them.

### 4.2 Selection

- Maintain explicit selected IDs and their displayed identity/source-branch snapshots in page memory. Do not persist selection across a full page reload.
- Selecting all on a page selects only its visible rows. Its checkbox reflects checked, unchecked, or mixed state based on that page's selection.
- Preserve selected records when moving between pages of the same applied filter. Show the total selected count and a clear-selection action.
- Enforce the 100-record limit in both the UI and the server. Disable adding individual records at the limit. If selecting an entire page would exceed the limit, leave the selection unchanged and explain the limit.
- Applying a different source branch or normalized search query clears selection, resets the page to 1, and announces the reset. Changing record type also clears selection. Typing without applying a filter does not clear it; reapplying identical filters preserves it.
- Changing the destination or reason preserves selection. Changing the destination resets the optional department to unassigned and reloads its applicable choices.
- Keep search and selection controls unavailable during submission so the displayed review cannot diverge from the submitted batch.

### 4.3 Transfer details and review

- Require a destination branch and a reason of 5-1000 characters, matching the current reason constraints.
- For user batches, allow one active department belonging to the destination branch, or no assigned department. Apply that choice to every selected user; do not copy departments from source branches.
- For student batches, hide and disable the department control and send no department assignment.
- Require at least one selected record. Before submission, present a review containing every selected name, identifying code/email, source branch, selected count, destination, reason, and destination department when applicable.
- Provide explicit confirm and back-to-edit actions. Use the existing application's visual components and spacing.
- On a confirmed success, show the transferred count, clear selection, and refresh the current candidate list. If the current page becomes empty, load the last available page.
- On a rejected batch, keep selection and form values. Show authorized per-record problems and allow individual records to be removed from the review before retrying. A stale-source problem requires reloading and reselecting the affected record; do not silently replace its expected source.
- On a network failure or an unreadable response, say that the outcome could not be confirmed. Keep the review available, provide a refresh action, and do not automatically resubmit or claim rollback.

### 4.4 Accessibility and localization

Use semantic table headers, labelled checkboxes, keyboard-operable controls, visible focus, and status/error announcements. Show selection and failure information in text as well as through styling. Keep the review readable at narrow widths, and manage focus when entering or leaving it. All new labels, counts, errors, and review messages must have matching Thai/English translation keys and placeholders. Render names, email addresses, codes, and reasons as text rather than executable HTML.

## 5. Application responsibilities

| Component | Responsibility |
| --- | --- |
| `BranchTransferController` | Preserve existing route actions; validate request modes, enforce entry authorization, provide page options, and adapt results to HTTP responses |
| A focused branch-transfer application service under `app/Support/Branch` | Share single-record and batch transfer rules; own transaction boundaries, selected-record locking, domain validation, writes, and transfer history |
| `BranchAccess`, `BranchContext`, and existing model hooks | Remain the authority for scoped access, role eligibility, protected writes, and normal audit events |
| `resources/views/branches/transfers.blade.php` | Render filters, candidate table, transfer details, review, and translated configuration |
| A branch-transfer browser module under `public/js` | Own selection state, request ordering, pagination, review interaction, and submission feedback; expose behavior suitable for existing Node tests |
| `lang/{en,th}/centre-management.php` | Keep the existing transfer namespace and add matching localized UI and batch error messages |

Keep helpers focused on this feature. Retain existing route names and public action names. New PHP class files follow the repository's PSR-4 conventions; new JavaScript files use kebab-case. New methods/functions use PascalCase, local identifiers and payload fields use snake_case, and named limits use UPPER_CASE. Do not duplicate business rules between single-record and batch processing.

## 6. HTTP contracts

### 6.1 Candidate search

Keep `GET /branch-transfers/targets` and add an explicit paginated mode used by the new page.

| Field | Paginated mode |
| --- | --- |
| `paginated` | `1` to opt into the new response mode |
| `type` | Required: `student` or `user` |
| `from_branch_id` | Optional positive integer; must identify an accessible existing branch |
| `q` | Optional; empty after trimming means no name/code condition; otherwise 2-100 characters |
| `page` | Positive integer, default 1 |

The server fixes page size at 25. It returns `data` with `id`, the existing `label`, `name`, `code`, `email`, `from_branch_id`, and `from_branch_name`. Inapplicable `code` or `email` values are null. Return only these approved fields. Add `meta` containing `current_page`, `per_page`, `total`, and `last_page`.

Without `paginated=1`, preserve the original search contract: `type` and `q` are required, the original 2-100 character query rule applies, results remain limited to 30, and each `data` entry retains its original `id` and `label` shape. Do not make legacy clients opt into pagination implicitly.

Apply normal model scopes and central-account exclusions in both modes. An inaccessible explicit branch filter is forbidden; do not broaden the query or reveal other branches' counts.

### 6.2 Submission

Keep `POST /branch-transfers`. Accept exactly one of the following request modes:

| Field | Legacy single-record mode | Batch mode |
| --- | --- | --- |
| `type` | Required `student` or `user` | Same |
| `record_id` | Required integer, preserving existing behavior | Must be absent |
| `record_ids` | Must be absent | Required list of 1-100 distinct positive integer IDs |
| `expected_from_branch_ids` | Not required | Required ID-to-source-branch map covering exactly the selected IDs |
| `to_branch_id` | Existing required integer field | Required positive integer |
| `position_id` | Existing optional integer field | Optional positive integer for users; absent or null for students |
| `reason` | Existing string of 5-1000 characters | Same |

Reject requests containing both `record_id` and `record_ids`, duplicate IDs, extra or missing source-map entries, invalid identifiers, or oversized batches. Do not silently truncate or partially process them. The expected-source map is a concurrency check, never evidence of authorization.

For a successful legacy JSON request, preserve the existing object shape, for example `{ "success": true, "data": { "id": 101, "from_branch_id": 2, "to_branch_id": 3 } }`. These numbers are illustrative identifiers. Preserve its existing redirect and localized success flash for a normal form request.

For a successful batch JSON request, return HTTP 200 with `success: true`, a localized `message`, `transferred_count`, and `data` as a list of the same per-record result objects. Keep this list in submitted selection order. A normal batch form request redirects with a localized count-aware success flash.

Authentication and CSRF failures follow the existing web middleware. Entry permission failures are HTTP 403. Structural validation uses the framework's HTTP 422 `message` and `errors` response. For a structurally valid batch that fails record validation, return `success: false`, a localized `message`, and `record_errors`, containing `record_id`, stable uppercase `code`, and localized `message` for each reportable problem. Existing access or role authorization denials retain HTTP 403; otherwise a source mismatch uses HTTP 409, and other record/business validation failures use HTTP 422. When failures are mixed, precedence is 403, then 409, then 422. Shared destination/department problems use field validation errors.

Report problems for all accessible selected records that can be evaluated safely. A deleted, hidden, or inaccessible record receives the same generic unavailable message without exposing its name, branch, roles, or existence. Legacy single-record errors retain their existing status behavior. Unexpected exceptions use the application's normal exception handling and logging; do not convert them into successful responses or expose technical exception details.

## 7. Atomic processing, rules, and concurrency

1. Authenticate the actor, enforce central status and `branch-transfer`, and validate request structure before creating any mutations.
2. Start one database transaction for the entire normalized selection. Acquire selected-record locks in ascending ID order to make competing transfer requests use a consistent lock order. Read records through the actor's normal scopes.
3. Confirm every requested record is available. For batches, compare each locked record's current source branch with its submitted expected source. A mismatch prevents the entire batch from committing.
4. Revalidate access to every source and the destination. The destination must exist, be active, and differ from each selected record's source. A source branch may be inactive, matching the existing transfer rule.
5. For students, query pending orders for the selected students and their current source branches in a batch. A null status or status `0` or `1` blocks that student. Do not treat missing, zero, and completed statuses as interchangeable.
6. For users, apply existing manage-user and assignable-role rules to each user. Central administrators remain in HQ and cannot transfer. Batch requests validate the chosen department's existence, active status, and destination ownership; legacy requests retain their existing department field and relation validation behavior. Load shared role/permission information together where practical.
7. Collect reportable validation problems before starting record writes. If any exist, end the transaction without a transfer. Do not skip invalid records.
8. Temporarily enable `BranchContext::allowTransfer`, preserving and restoring its prior value in a `finally` block. Save through model hooks. Update the appropriate branch field; clear `contacts.order` for students or apply the chosen `position_id` for users.
9. Insert one existing-format `branch_transfers` row per transferred record with its original source, destination, actor, reason, and timestamp. Keep normal branch audit hooks. Delete transferred users' personal access tokens within the same transaction, as the current workflow does.
10. Commit only after every write and audit operation succeeds. Any exception during persistence rolls back record changes, transfer history, branch audit rows, and token deletion together. Return success only after commit.

Keep bulk reads bounded to the 100 selected records and batch related lookups instead of issuing new avoidable per-record candidate or order queries. Candidate requests must paginate on the server, not load every matching record into browser memory. Keep the 25/100 limits defined once and expose the same limits to the page and validation.

Disable duplicate submission while the request is pending. Repeating a completed batch with its original source snapshots must fail before additional writes, because the records are now at a different branch. This is source-conflict protection, not a durable idempotency-key service. A record that leaves and later returns to the same source is outside the detection guarantee of a source-only snapshot.

The locking guarantee covers competing transfers that use this workflow. This change does not introduce global coordination with unrelated order creation, role-editing, or branch-management writers. Preserve their existing safeguards and do not claim that transfer locks alone serialize those workflows. SQLite tests establish atomic rollback behavior but do not establish production-engine row-lock semantics.

## 8. Verification and acceptance criteria

Use test-driven development for the later implementation. Add behavior tests that fail because the required bulk functionality is absent, then implement the smallest scoped changes that make them pass. This specification does not claim that those tests have been written or run.

| Area | Required evidence |
| --- | --- |
| Student success | Several students move together; current order pointers reset; issued orders and historical documents retain their source; one transfer history entry exists per student |
| User success | Several users move together; shared destination department is applied; existing roles remain; transferred users' tokens are revoked |
| Business rejection | A blocked record prevents all transfers; all safely reportable blocked records have useful errors; no transfer history or token deletion remains |
| Persistence failure | An injected later write failure rolls back earlier record updates, both audit stores, and token deletion |
| Input boundaries | Empty, duplicate, malformed, mixed-mode, missing-source-map, and 101-record requests reject without mutation; 1-record and 100-record batches are supported |
| Access | Unauthorized actors, out-of-scope filters/records, protected central accounts, and invalid roles cannot bypass existing restrictions or disclose hidden details |
| Destination | Same-source destinations, inactive or nonexistent destinations, and invalid destination departments prevent the batch |
| Stale selection | A source changed after selection yields a conflict and no new mutations; repeated successful submissions do not create a second transfer |
| Filtering | Source-only browsing, name search, student-code search, combined filters, empty queries, and query-length boundaries behave as specified |
| Pagination | Stable ordering, 25-record pages, correct totals, and no legacy 30-record truncation in paginated mode |
| Browser selection | Select/unselect page, mixed checkbox state, cross-page retention, 100-record boundary, clear selection, and applied-filter resets match the design |
| Browser requests | Obsolete responses are ignored; confirmation does not send twice; failures preserve review; uncertain network outcomes are not described as confirmed failures |
| Compatibility | Existing single-record request/response shape, transfer history protections, navigation, and legacy search shape remain covered |
| Localization | Existing locale coverage plus new success/error/review messages passes in Thai and English; translation leaves and placeholders match |
| Accessibility | Keyboard selection/review, focus restoration, live feedback, text encoding, and narrow-screen readability are checked in the rendered page |

Extend the existing PHP feature coverage with focused batch tests and use the existing `tests/JavaScript` convention for selection/request behavior tests. Reuse `UiTranslationStructureTest` for matching translation catalogs. Run the relevant branch, localization, navigation, and browser-module checks after implementation. Record the actual commands and outcomes during execution, distinguishing focused evidence from broader coverage. Verify row-lock behavior separately on an isolated compatible database if available; do not run transfer probes against live user records.

## 9. Review boundary

This artifact records the approved product direction and its contracts and verification criteria. The user approved this written specification and moving to implementation planning using `writing-plans`. Review of the resulting plan is separate from authorization to implement. No production code, database migration, live record, or external system is changed by writing this specification.
