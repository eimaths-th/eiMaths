# Implementation Plan: Receipt Price-Group Calculation

- Date: 2026-08-12
- Status: Executed on 2026-08-12; targeted verification passed; full suite has unrelated pre-existing failures
- Related specification: `docs/specs/2026-08-12-receipt-price-group-calculation-design.md`

> **REQUIRED EXECUTION SKILL:** Use `executing-plans` for task-by-task execution of this plan.

## 1. Goal

Correct the Receipt detail calculation for newly generated Receipts and explicit Receipt detail regeneration when one order contains more than one tuition or book unit price.

The observable result for the approved `RP2608015` source pattern is six generated lines:

| Line category | Quantity | Unit price | Discount | Net amount |
|---|---:|---:|---:|---:|
| Tuition | 2 | 3,000.00 | 0.00 | 6,000.00 |
| Tuition | 3 | 4,000.00 | 0.00 | 12,000.00 |
| Book | 2 | 1,500.00 | 0.00 | 3,000.00 |
| Book | 3 | 2,900.00 | 0.00 | 8,700.00 |
| Registration fee | 1 | 750.00 | 0.00 | 750.00 |
| Equipment | 1 | 500.00 | 0.00 | 500.00 |

The generated detail total must be `30,950.00`, and the stored `orders.total_price`, `receipts.total_fee`, and Affiliate financial records must remain unchanged. Existing demo records, including `RP2608014` and `RP2608015`, are outside the implementation scope.

## 2. Verified repository context

- The application is Laravel 10.10 on PHP `^8.1`, with Eloquent models and PHPUnit 10.1.
- Runtime financial values are stored as MySQL `DECIMAL(12,2)` strings; the test suite uses Laravel's SQLite test database and `RefreshDatabase`.
- `OrderController::update` creates a Receipt for a paid order and currently generates tuition/book details with `max('oprice')` and `max('obprice')`.
- `ReceiptController::gen_receipt_data` repeats the same maximum-price calculation when regenerating details.
- `ReceiptController::show` reads persisted `receipt_details` for presentation totals and must not receive a new calculation path.
- `ReceiptLineMetadataResolver` already maps legacy tax flags to the existing line type, tax treatment, and VAT basis-point metadata.
- `AffiliateCommissionCalculator` already sums each source row's tuition `price`; Affiliate code is not part of this change.
- The routes and authorization middleware remain unchanged: no new route, permission, migration, table, or historical repair command is required.

## 3. Architecture and data flow

Add one financial-domain calculation boundary:

```text
Order
  -> ReceiptDetailBuilder::BuildForOrder()
  -> ordered ReceiptDetailDraft collection
  -> controller authorization and persistence
  -> receipt_details
  -> existing ReceiptController::show presentation totals
```

`ReceiptDetailBuilder` will:

1. Load `order_detail_lists` for the order in ascending `id` order.
2. Parse all monetary inputs into integer minor units without floating-point arithmetic.
3. Build tuition and book price groups independently, preserving the first occurrence order within each category.
4. Allocate order-level tuition and book discounts proportionally in minor units using deterministic largest-remainder rounding, with a cap at each group's gross amount.
5. Append the existing deposit, registration, and equipment fee lines when their source amounts are non-zero.
6. Leave `orther_fee` as the existing summary-level service charge.
7. Validate `sum(generated line net amounts) + orther_fee === order.total_price` before returning drafts.
8. Throw a dedicated calculation exception for missing rows, invalid/negative prices, invalid discounts, or reconciliation failure. It must never fall back to the maximum-price formula.

`ReceiptDetailDraft` will carry the persistence-neutral fields needed by both controllers: description, unit, quantity, unit price in minor units, total discount in minor units, legacy tax flag, and `ReceiptLineType`. It will expose deterministic minor-unit total and decimal-format helpers; it will not write to the database.

Controllers will remain responsible for authorization, existing financial-source guards, deleting/replacing generated details where the current regeneration flow permits it, applying `ReceiptLineMetadataResolver`, saving rows, and returning the existing response. The builder must be invoked before regeneration deletes existing details so a failed calculation cannot silently erase the prior detail set.

## 4. Technology and repository constraints

- PHP 8.1-compatible syntax and Laravel 10 conventions.
- Existing Eloquent models: `Order`, `OrderDetailList`, `Receipt`, and `receipt_details`.
- Existing enums and resolver: `ReceiptLineType` and `ReceiptLineMetadataResolver`.
- PHPUnit attributes and method names in PascalCase for new tests, consistent with the repository instructions.
- Fixed-point integer arithmetic for all monetary calculations. Do not introduce a new package or migration.
- Keep existing HTTP routes, permissions, financial locks, Affiliate events, commission formulas, source snapshots, UI templates, print/PDF layout, and service-charge semantics unchanged.
- Do not repair, rewrite, or normalize any existing production/demo row.
- Do not log student contact data, billing details, credentials, payment evidence, or full source payloads from the calculation service.

## 5. Implementation tasks

### Task 1 — Add the calculation domain boundary and unit coverage

Estimated effort: four short implementation steps, each independently verifiable.

Files to create:

- `app/Domain/Financial/Exceptions/ReceiptDetailCalculationException.php`
- `app/Domain/Financial/ValueObjects/ReceiptDetailDraft.php`
- `app/Domain/Financial/Services/ReceiptDetailBuilder.php`
- `tests/Unit/Financial/ReceiptDetailBuilderTest.php`

TDD sequence:

1. Create the unit test fixture for an order with five source rows using the approved price pattern, plus zero discounts, `register_fee=750.00`, `access_fee=500.00`, `orther_fee=0.00`, and `total_price=30950.00`. Assert six drafts, the exact quantities/prices/order, tax flags, line types, and a `3095000` minor-unit sum.
2. Add tests for a single tuition/book price group, deterministic proportional discount allocation with a residual minor unit, VAT-inclusive tuition/fees, VAT-exempt books, no source rows, negative/invalid prices, an over-sized discount, and a total reconciliation mismatch.
3. Run the focused test before production code exists. Expected result: a non-zero PHPUnit failure caused by the missing domain classes or methods.

```powershell
php artisan test tests/Unit/Financial/ReceiptDetailBuilderTest.php
```

4. Implement the exception, immutable draft, and builder. Keep public APIs limited to `BuildForOrder(Order $order): Collection` and the draft's read-only value/total helpers. Use ordered arrays keyed by integer minor-unit prices, not database grouping or `max()`.
5. Re-run the focused test. Expected result: all builder tests pass. Refactor only after the red/green result while preserving the exact output order and exception behavior.

Checkpoint:

- No controller or database schema changes are made in this task.
- The builder alone can reproduce `30,950.00` from the approved source rows.
- A failed build has no database side effect because the service only reads the order and returns drafts or throws.

### Task 2 — Use the builder in both Receipt generation paths

Estimated effort: five short implementation steps, each independently verifiable.

Files to modify:

- `app/Http/Controllers/OrderController.php`
- `app/Http/Controllers/ReceiptController.php`
- `tests/Feature/Receipt/ReceiptPriceGroupCalculationTest.php`

TDD sequence:

1. Add a `RefreshDatabase` feature fixture that creates a department, a contact, an order, and five `OrderDetailList` rows with the approved price pattern. Grant the actor only the existing permission needed by the paid order update route, and use the actor's department for the existing centre-scope check.
2. Add a failing paid-order test that submits `PUT /orders/save/{id}` with `status=1`, `total_cost=30950.00`, the approved fee fields, and zero discounts. Assert the response succeeds, the new Receipt is linked to the order, and persisted details contain the six grouped lines. Before implementation, this test must fail or observe the old `35,750.00` detail total.
3. Add a failing regeneration test that creates a non-affiliated Receipt for the same order, invokes `ReceiptController::gen_receipt_data($receipt->id)` through the container, and asserts the regenerated details are the same six grouped lines. Verify that the stored Receipt header total and order total are unchanged.
4. Inject `ReceiptDetailBuilder` into both controllers. Replace both maximum-price tuition/book blocks with the builder's drafts, persist each draft using the existing `receipt_details` model, and apply metadata through the existing `ApplyReceiptLineMetadata`/`ReceiptLineMetadataResolver` path. Preserve existing fee descriptions and line types. Build drafts before the regeneration delete operation.
5. Re-run the feature test. Expected result: both generation paths pass with a `30,950.00` detail total, no maximum-price fallback remains in either path, and the stored header totals are unchanged. Refactor only after the green result; do not alter routes or templates.

Persistence rules for this task:

- Keep `receipts.total_fee` sourced from the existing order total.
- Keep `orders.total_price` authoritative and do not recompute or overwrite it from generated lines.
- Persist discount as the total discount allocated to a line, matching the existing `show` calculation (`price * quantity - discount`).
- Preserve the existing `tax` values and generated line metadata: tuition/deposit/registration/equipment tax flag `1`, book tax flag `0`.
- Do not call the financial-source guard in a way that permits modifying locked Affiliate data; existing guards must remain in place.

Checkpoint:

- Both application paths use the same builder class.
- `ReceiptController::show`, Blade templates, JavaScript, route definitions, and Affiliate services remain untouched.
- A calculation failure is surfaced and no new inconsistent detail rows are silently persisted.

### Task 3 — Add Affiliate and compatibility regression assertions

Estimated effort: four short implementation steps, each independently verifiable.

Files to modify:

- `tests/Feature/Receipt/ReceiptPriceGroupCalculationTest.php`
- Only if required by the test fixture, the smallest existing test helper or factory file; do not change Affiliate production code.

TDD sequence:

1. Extend the paid-order fixture with an Affiliate attribution and commission rate using the same model setup as `tests/Feature/Affiliate/AffiliateCommissionServiceTest.php`.
2. Add a failing assertion that the paid generation path preserves the expected Affiliate commission minor amount and the exact count/source-row identities in `affiliate_commission_source_items`. Also assert the order total, Receipt total, and source `OrderDetailList` prices remain unchanged.
3. Run the new feature test plus the existing Affiliate commission regression test before final cleanup. Expected result before the integration is complete: the new assertion fails if the controller path still calculates/persists the wrong grouped lines; the existing Affiliate test remains the baseline.

```powershell
php artisan test tests/Feature/Receipt/ReceiptPriceGroupCalculationTest.php
php artisan test tests/Feature/Affiliate/AffiliateCommissionServiceTest.php tests/Unit/Affiliate/AffiliateCommissionCalculatorTest.php
```

4. Finalize the fixture and assertions so the new Receipt calculation is the only changed behavior. Expected result: all targeted Receipt and Affiliate tests pass with no Affiliate production-file diff.

Checkpoint:

- Affiliate commission rate, eligible tuition, commission amount, source snapshots, and release behavior are unchanged.
- The test proves the calculation fix does not use Receipt detail grouping as an Affiliate source.
- Existing demo data is not used as mutable test input.

### Task 4 — Run the verification gate and document evidence

Estimated effort: four short verification steps.

Files to inspect or verify:

- `docs/specs/2026-08-12-receipt-price-group-calculation-design.md`
- `docs/plans/2026-08-12-receipt-price-group-calculation.md`
- All implementation/test files listed above.

Verification sequence:

1. Run the complete targeted suite, including metadata compatibility coverage:

```powershell
php artisan test tests/Unit/Financial/ReceiptDetailBuilderTest.php tests/Feature/Receipt/ReceiptPriceGroupCalculationTest.php tests/Feature/Affiliate/AffiliateCommissionServiceTest.php tests/Unit/Affiliate/AffiliateCommissionCalculatorTest.php tests/Feature/CreditNote/ReceiptLineMetadataWriteTest.php
```

Expected result: all targeted tests pass with exit code `0`.

2. Run the complete repository test suite:

```powershell
php artisan test
```

Expected result: exit code `0`. If unrelated pre-existing failures occur, record the exact failing tests and do not broaden the change to fix them.

3. Run static hygiene checks:

```powershell
git diff --check
git status --short
```

Expected result: no whitespace errors; the status shows only the approved implementation, regression tests, and documentation changes, while unrelated pre-existing user changes remain preserved.

4. Perform deployment verification against the real local data using read-only snapshots before and after the code path is exercised. Capture only identifiers and monetary/source fields for `RP2608014` and `RP2608015`; expected result: no existing Receipt, order, detail, or Affiliate record changes. Do not invoke regeneration for the locked demo Affiliate Receipts.

Suggested read-only command:

```powershell
php artisan tinker --execute="dump(DB::table('receipts')->whereIn('receipt_number', ['RP2608014', 'RP2608015'])->orderBy('receipt_number')->get(['receipt_number', 'oid', 'total_fee'])->toArray());"
```

Checkpoint:

- The acceptance criteria in the approved specification are evidenced by focused tests and a full-suite result.
- Existing demo records are verified unchanged rather than repaired.
- No commit, push, deployment, or external notification is part of this plan.

## 6. Acceptance criteria

The implementation is complete only when all of the following are true:

1. The builder produces the six approved grouped lines and a `30,950.00` total for the RP2608015 source pattern.
2. Single-price orders retain their prior quantities, descriptions, taxes, and totals.
3. Discounts are allocated in integer minor units, sum exactly to each source discount, and never exceed group gross amounts.
4. Both paid-order generation and explicit Receipt detail regeneration use `ReceiptDetailBuilder`.
5. A generated detail total plus summary-level `orther_fee` equals `receipts.total_fee` and `orders.total_price`, or the operation fails explicitly.
6. Existing VAT/tax treatment, line metadata, fee semantics, service-charge semantics, permissions, and financial locks remain unchanged.
7. Affiliate commission values and source evidence remain unchanged.
8. No existing demo Receipt, order, detail, or Affiliate financial row is repaired or modified.
9. Targeted and full test commands pass, and `git diff --check` is clean.

## 7. Risks and mitigations

| Risk | Mitigation |
|---|---|
| Decimal rounding changes the final amount | Parse and allocate in integer minor units; test a residual-minor-unit case. |
| A price group is silently omitted | Fail on missing/invalid source rows and assert the reconciliation invariant before persistence. |
| Regeneration deletes valid details before a failed rebuild | Build and validate drafts before deleting existing details. |
| Affiliate records are coupled to Receipt details | Keep Affiliate services and events unchanged; assert commission/source snapshots in the feature test. |
| A test accidentally touches demo data | Use `RefreshDatabase` fixtures and perform only read-only deployment verification for real demo IDs. |

## 8. Plan self-review

- The plan follows the approved written specification and does not add data repair, migration, route, UI, Affiliate, or historical-reconciliation scope.
- Each production behavior change has a red/green/refactor sequence and an exact verification command.
- The plan names the concrete source, test, and documentation files and describes what each task consumes, produces, and verifies.
- Implementation began only after this plan was explicitly approved by the user.

## 9. Execution record

- The plan was explicitly approved in conversation before implementation began.
- `php artisan test tests/Unit/Financial/ReceiptDetailBuilderTest.php tests/Feature/Receipt/ReceiptPriceGroupCalculationTest.php tests/Feature/Affiliate/AffiliateCommissionServiceTest.php tests/Unit/Affiliate/AffiliateCommissionCalculatorTest.php tests/Feature/CreditNote/ReceiptLineMetadataWriteTest.php` passed with 16 tests and 77 assertions.
- `php artisan test` completed with 389 passing tests, 3 skipped tests, and 3 unrelated pre-existing failures in `Tests\Feature\ExampleTest` and `Tests\Feature\ParentAuthTest`. The failures were the existing root 302-versus-200 expectation and the existing missing `parents.student_id` fixture value; neither is part of this Receipt calculation scope.
- PHP lint passed for all changed PHP files and `git diff --check` passed.
- Read-only local demo snapshots for `RP2608014` and `RP2608015` before and after verification showed unchanged Receipt totals, existing detail rows, and Affiliate commission fields. No regeneration was invoked for the locked demo Affiliate Receipts.
- No commit, push, deployment, or historical data repair was performed.

## Approval gate

The implementation plan was approved and executed in order on the existing non-default `demo` branch. The repository is ready for handoff, with the unrelated full-suite failures explicitly recorded above.
