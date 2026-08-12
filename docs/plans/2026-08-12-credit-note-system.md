# Credit Note System Implementation Plan

> **REQUIRED EXECUTION SKILL:** Use `executing-plans` for task-by-task execution.

**Plan status:** Approved for implementation in the `demo` branch on 2026-08-12

**Execution status:** Tasks 1–26 are implemented on `demo`; Task 27 has the explicit opt-in guard and local skip contract, while its authorized MySQL worker scenarios remain deferred. Task 28 focused and regression verification passed and the verification record was written. The complete repository runner was inconclusive after hanging for more than twenty minutes; MySQL concurrency and production enablement gates remain intentionally open. See `docs/verification/2026-08-12-credit-note-system.md`.

- **Goal:** Add an auditable Thai tax Credit Note workflow linked to one original receipt, supporting partial/full credits, immutable Issue and append-only Cancellation, exact VAT calculations, Affiliate commission adjustment, print/PDF, scoped administration, export, and gross/credit/cancellation/net reporting.
- **Architecture:** Add a dedicated `App\Domain\CreditNote` boundary. Treat Receipt and Order as read-only source evidence after a numbered Credit Note exists. Keep exact financial values as integer satang and quantities as fixed-scale decimal strings. Route all mutations through `CreditNoteCommandService`; route reads through `CreditNoteQuery`; store report effects in append-only postings; and delegate tuition-only commission effects to a focused Affiliate adapter.
- **Tech stack:** PHP 8.2.12, Laravel 10.50.2, Eloquent, MySQL for the local application configuration, SQLite in-memory for PHPUnit, PHPUnit 10, Spatie Permission 5.x, Blade, Bootstrap/AdminLTE 3.2, jQuery/DataTables, DOMPDF 2.x, Laravel Excel 3.1, and Node.js 24.16.0 for isolated JavaScript tests.
- **Approved specification:** `docs/specs/2026-08-12-credit-note-system-design.md`

## Verified Baseline

The following facts were verified in the repository on 2026-08-12:

- `receipts` and `receipt_details` are the current receipt header and item tables. `receipt_details` has `quantity`, `price`, and `discount` as `DECIMAL(12,2)`, a legacy integer `tax` flag, and no line type or persisted VAT rate.
- `orders.orther_fee` is `DECIMAL(12,2)`. Receipt rendering adds it outside `receipt_details`.
- `ReceiptController::show` calculates taxable value with a hard-coded 7 percent VAT-inclusive formula and currently writes generated PDFs under `public/file_store`.
- Receipt creation writes standard Thai descriptions in both `OrderController::update` and `ReceiptController::gen_receipt_data`. Receipt editing deletes and recreates detail rows in `ReceiptController::update`.
- Current receipt numbers use a centre/year/month counter, but `ReceiptRunningNumber::generate` does not lock the sequence row. The Credit Note generator must therefore be independent.
- Current dashboard day/month/year methods in `DashboardController` and public branch endpoints in `ReceiptController` aggregate `receipts.total_fee` directly. Their existing `total_income`, `total_revenue`, and `grand_total` fields are gross values.
- `routes/web2.php` owns the day/month/year dashboard routes. `routes/api.php` owns the two branch revenue endpoints. The route provider also loads feature route files from `routes/*.php` with web middleware.
- Spatie permissions and `all-centre` are the established access mechanisms. A user without `all-centre` is scoped by `users.department_id` against receipt/credit-note centre.
- Affiliate commission evidence is receipt-backed and append-only. External confirmed refunds update `affiliate_commissions.reversed_minor`, add immutable reversal evidence, and create an available-wallet ledger entry only when appropriate.
- `AffiliateWalletService::ApplyEntry` already provides payload-aware idempotency and locks `affiliate_wallet_balances`.
- `AffiliateFinancialSourceGuard` already blocks changes to sources that have an Affiliate commission. The Credit Note feature must add its own source lock without weakening that guard.
- The application has `lang/en` and `lang/th`; new visible Credit Note strings belong in matching `credit-note.php` files.
- DOMPDF exposes `stream(string $filename)`, so Credit Note PDFs can be streamed without writing a public file.
- `config/app.php` uses `Asia/Bangkok`.
- Repository search found no current Credit Note domain and no direct Receipt controller feature tests. This is a search result, not proof that no indirect receipt coverage exists.
- `C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --without-tty --filter=AffiliateRefundServiceTest` passed 2 tests and 6 assertions.
- `C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --without-tty tests\Feature\Affiliate\AffiliateGovernancePersistenceTest.php` passed 3 tests and 17 assertions.
- `node --test tests/JavaScript/affiliate-analytics-page.test.mjs` passed 6 tests.

## Known External Unknowns

- Production row counts, historical receipt reconciliation results, unknown line descriptions, database version, isolation settings, and lock/deadlock behavior have not been verified locally.
- The legal issuer name, registered address, tax identifier, and final printed Head Office wording are not present in approved repository configuration. They must remain blank and fail closed until accounting supplies and approves them.
- Accounting sign-off for historical 7 percent VAT, service-charge tax treatment, reason labels, and delivered-document cancellation procedure remains outstanding by design.
- The structured Contact record has an address but no verified buyer tax identifier or VAT branch fields. Drafts may prefill only available structured values; Issue must remain blocked until the user supplies valid required buyer values.
- Existing public branch-revenue endpoints have no authentication middleware. This plan preserves that existing contract; API authentication hardening is a separate optional security change and is not authorized here.
- SQLite tests cannot prove MySQL row-lock ordering or concurrent sequence allocation. The plan includes opt-in isolated MySQL process tests and treats them as a production gate.

## Implementation Contracts

### Feature and issuer configuration

Create `config/financial-documents.php` with this stable shape:

```php
<?php

return [
    'credit_note' => [
        'enabled' => env('CREDIT_NOTE_FEATURE_ENABLED', false),
        'financial_actions_enabled' => env('CREDIT_NOTE_FINANCIAL_ACTIONS_ENABLED', false),
        'issuer' => [
            'legal_name' => env('CREDIT_NOTE_ISSUER_LEGAL_NAME'),
            'legal_address' => env('CREDIT_NOTE_ISSUER_LEGAL_ADDRESS'),
            'tax_id' => env('CREDIT_NOTE_ISSUER_TAX_ID'),
            'branch_type' => env('CREDIT_NOTE_ISSUER_BRANCH_TYPE', 'head_office'),
            'branch_code' => env('CREDIT_NOTE_ISSUER_BRANCH_CODE', '00000'),
        ],
        'limits' => [
            'list_per_page' => 50,
            'reconciliation_batch' => 100,
            'source_audit_batch' => 500,
            'transaction_attempts' => 3,
        ],
    ],
];
```

`enabled` gates all Credit Note routes. `financial_actions_enabled` additionally gates Issue and Cancel. A staged read-only rollout is achieved by enabling the module and assigning only list/show permissions. Both flags default to false. Tests set them explicitly; implementation never changes a deployed environment value.

### Stored values

Use these exact enum values:

- `CreditNoteStatus`: `draft`, `issued`, `cancelled`.
- `ReceiptLineType`: `tuition`, `book`, `deposit`, `registration_fee`, `equipment`, `other`, `service_charge`, `unknown`.
- `TaxTreatment`: `vat_inclusive`, `vat_exempt`, `out_of_scope`.
- `CreditNoteSourceType`: `receipt_detail`, `receipt_service_charge`.
- `CreditNotePostingType`: `issue`, `cancellation`.
- `CreditNoteEventOperation`: `created`, `updated`, `issued`, `cancelled`.
- `BuyerBranchType`: `head_office`, `branch`, `not_applicable`.
- `AffiliateCreditNoteEventType`: `issue`, `cancellation`.
- `CreditNoteReasonCode`: `product_price_overstated`, `service_price_overstated`, `product_defect_damage_or_shortage`, `service_nonconformity_or_shortage`, `product_return`, `statutory_compensation`, `advance_deposit_or_reservation_return`, `commercial_return_or_exchange`, `defective_service_termination`, `service_not_provided_termination`. Do not add a generic `other` value.

### Exact values and rounding

- HTTP money is a non-negative decimal string with at most two fractional digits. Convert it to signed 64-bit satang before calculation.
- Source and credit quantities use at most two fractional digits because the verified source schema is `DECIMAL(12,2)`. Represent them as integer hundredths inside calculations.
- Do not cast financial input to float and do not use JavaScript `Number` for authoritative money.
- Source line total is `round_half_up(quantity_hundredths * unit_price_minor / 100) - discount_minor`.
- VAT for each rate group is `round_half_up(vat_inclusive_minor * rate_basis_points / (10000 + rate_basis_points))`.
- Avoid overflow by quotient/remainder multiplication with explicit `PHP_INT_MAX` guards. Do not require BCMath at runtime even though it is installed locally, because it is not declared in `composer.json`.
- Allocate a VAT-group one-satang residual in ascending persisted `credit_note_items.id` order.
- Store all header/item/posting/Affiliate money in integer minor-unit columns. Convert to a two-decimal string only at HTTP, Blade, PDF, and export boundaries.

### Draft, Issue, and Cancel

- `CreateDraft` stores only a header/source hash/event. It does not create item rows.
- `UpdateDraft` replaces Draft items atomically and increments `lock_version` after validating the submitted version.
- Use nullable unique `active_draft_receipt_id` as a portable database race guard: set it to `receipt_id` in Draft and clear it on Issue or Cancel.
- `Issue` locks in this order: receipt, credit note, number row, Affiliate commission, Affiliate wallet. It stores number, immutable snapshots, totals, one negative posting, optional Affiliate adjustment, and one event in one transaction.
- `Cancel` locks receipt before credit note. A Draft gets no posting. An Issued note gets one exact positive reversal posting and one exact Affiliate restoration where applicable.
- Use `DB::transaction($callback, 3)` for command deadlock retries. Idempotency and unique constraints must prevent duplicate effects across retries.
- Issued and Cancelled data has no update/delete route. Models reject disallowed updates and every delete.

### Source integrity

- Canonical source serialization uses ordered keys, normalized two-decimal strings, ascending source IDs, `JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR`, and SHA-256.
- Include receipt ID/number/date/header totals/billing snapshot, linked order service charge, and every detail's ID, description, unit, quantity, price, discount, tax flag, line type, tax treatment, and VAT rate.
- `Issue` recomputes the hash under the receipt lock. A mismatch leaves the Draft unchanged.
- Any Credit Note that has a number permanently locks financially relevant Receipt/Order source edits, including after Credit Note cancellation. A cancelled unnumbered Draft does not lock its source.

### Affiliate adjustment

- Only selected `tuition` lines contribute to Affiliate tuition reversal.
- Remaining eligible tuition equals commission eligible tuition minus external refunded tuition minus net Credit Note tuition reversal deltas.
- Calculate commission through the existing `CommissionCalculation::FromValues`; do not introduce a second rate formula.
- Issue records positive reversal deltas; Cancellation records their exact negative values.
- Available commissions receive ledger types `CREDIT_NOTE_COMMISSION_REVERSAL` and `CREDIT_NOTE_COMMISSION_RESTORATION`. Pending/Held adjustments change the commission projection without a wallet entry.
- External refund validation must include net Credit Note tuition deltas so either workflow cannot consume the same eligible tuition twice.

### HTTP and authorization

- Use exactly these nine route contracts. Define `/credit-notes/export` before `/{credit_note}`. There is no DELETE route.

| Method | Path | Name |
| --- | --- | --- |
| GET | `/credit-notes` | `credit-notes.index` |
| POST | `/receipts/{receipt}/credit-notes` | `credit-notes.store` |
| GET | `/credit-notes/export` | `credit-notes.export` |
| GET | `/credit-notes/{credit_note}` | `credit-notes.show` |
| PUT | `/credit-notes/{credit_note}` | `credit-notes.update` |
| POST | `/credit-notes/{credit_note}/issue` | `credit-notes.issue` |
| POST | `/credit-notes/{credit_note}/cancel` | `credit-notes.cancel` |
| GET | `/credit-notes/{credit_note}/print` | `credit-notes.print` |
| GET | `/credit-notes/{credit_note}/pdf` | `credit-notes.pdf` |
- Each route has `auth`, Credit Note feature, Credit Note correlation, and Credit Note response middleware plus its dedicated Spatie permission.
- Permission mapping is exact: index/export use `credit-note-list`; store uses `credit-note-create`; show/print/PDF use `credit-note-show`; update uses `credit-note-edit`; Issue uses `credit-note-issue`; and Cancel uses `credit-note-cancel`.
- Resolve receipt and Credit Note IDs through `CreditNoteQuery` with centre scope before policy checks. Cross-centre IDs return 404, not 403.
- Successful JSON is `{ "success": true, "data": ..., "correlation_id": "..." }`.
- Failed JSON is `{ "success": false, "error": { "code": "...", "message": "...", "field_errors": {} }, "correlation_id": "..." }`.
- Normal browser forms remain usable without JavaScript and use redirect-with-flash responses. JSON callers receive the stable contract.

### Reporting compatibility

- Existing `grand_total`, `total_revenue`, `total_income`, `total_fee_sum`, and receipt-count fields keep gross semantics and their existing names.
- Add `issued_credit_note_total`, `cancelled_credit_note_total`, `net_revenue`, `total_credit_notes_issued`, and `total_credit_notes_cancelled` at the corresponding aggregate and branch/group row levels.
- Credit Note effects are grouped by `credit_note_postings.effective_date`, never receipt date.
- `net_revenue = gross + signed posting deltas`, equivalent to gross minus Issue plus Cancellation.

## Global Constraints

- Preserve the approved specification and all unrelated user work. Do not rename or drop receipt/order fields or change existing gross response meanings.
- Do not commit, push, migrate a non-test database, enable either Credit Note flag, populate legal issuer values, assign production permissions, or repair production data without separate authorization.
- Do not implement cash refunds, email delivery, e-Tax, debit notes, multi-receipt Credit Notes, a generalized document framework, or configurable reasons.
- Do not persist PDFs under `public`, `file_store`, or another shared path.
- Do not log buyer name, address, tax identifier, telephone, billing text, item descriptions, or complete source/document snapshots.
- Do not auto-grant `credit-note-issue` or `credit-note-cancel`; this plan creates permissions only.
- New PHP class files use framework-required PSR-4 names. New Blade, JavaScript, CSS, routes, documentation, and translation filenames use kebab-case. Use PascalCase for new application methods/tests, snake_case for variables/properties/payload fields, and UPPER_CASE for constants/enum members.
- Every production behavior task uses `test-driven-development`. Every completion claim uses `verification-before-completion`.

## Task 1: Add fail-closed configuration, enums, and domain errors

**Files:**

- Create: `config/financial-documents.php`
- Modify: `.env.example`
- Modify: `phpunit.xml`
- Create: `app/Domain/CreditNote/Enums/CreditNoteStatus.php`
- Create: `app/Domain/CreditNote/Enums/ReceiptLineType.php`
- Create: `app/Domain/CreditNote/Enums/TaxTreatment.php`
- Create: `app/Domain/CreditNote/Enums/CreditNoteSourceType.php`
- Create: `app/Domain/CreditNote/Enums/CreditNotePostingType.php`
- Create: `app/Domain/CreditNote/Enums/CreditNoteEventOperation.php`
- Create: `app/Domain/CreditNote/Enums/CreditNoteReasonCode.php`
- Create: `app/Domain/CreditNote/Enums/BuyerBranchType.php`
- Create: `app/Domain/CreditNote/Exceptions/CreditNoteDomainException.php`
- Create: `app/Domain/CreditNote/Services/CreditNoteFeatureGate.php`
- Create: `tests/Feature/CreditNote/CreditNoteFeatureConfigurationTest.php`
- Create: `tests/Unit/CreditNote/CreditNoteEnumTest.php`

- **Consumes:** Approved stored values, current config conventions, and the application's forced PHPUnit environment variables.
- **Produces:** Fail-closed feature/financial gates and one source of truth for lifecycle, source, tax, branch, posting, event, and reason values.
- **Checkpoint:** No schema, route, or mutation exists.

`CreditNoteDomainException` exposes `ErrorCode()`, `StatusCode()`, and `SafeTranslationKey()` and provides named factories for these exact codes: `CREDIT_NOTE_DISABLED`, `CREDIT_NOTE_SOURCE_INCOMPLETE`, `CREDIT_NOTE_SOURCE_TOTAL_MISMATCH`, `CREDIT_NOTE_DRAFT_EXISTS`, `CREDIT_NOTE_FULLY_CREDITED`, `CREDIT_NOTE_STALE_DRAFT`, `CREDIT_NOTE_SOURCE_CHANGED`, `CREDIT_NOTE_UNKNOWN_LINE_TYPE`, `CREDIT_NOTE_OVER_CREDIT`, `CREDIT_NOTE_INVALID_STATE`, `CREDIT_NOTE_INVALID_EVENT_DATE`, `CREDIT_NOTE_BUYER_DETAILS_INCOMPLETE`, `CREDIT_NOTE_ISSUER_CONFIGURATION_INVALID`, `CREDIT_NOTE_AFFILIATE_ADJUSTMENT_FAILED`, `CREDIT_NOTE_IDEMPOTENCY_CONFLICT`, `CREDIT_NOTE_PDF_GENERATION_FAILED`, `CREDIT_NOTE_FINANCIAL_SOURCE_LOCKED`, and `CREDIT_NOTE_INTERNAL_ERROR`. Never place raw SQL, paths, or PII in its safe message.

`CreditNoteFeatureGate` exposes:

```php
public function AssertEnabled(): void;
public function AssertFinancialActionsEnabled(): void;
public function IsEnabled(): bool;
public function AreFinancialActionsEnabled(): bool;
```

Add blank issuer values and false flags to `.env.example`; add false forced values and synthetic test issuer values to `phpunit.xml`. Do not insert real legal or personal data.

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Write enum round-trip, exact reason-set, unknown-value, default-disabled, blank-issuer, and gate tests.
- [ ] Run `C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --without-tty tests\Feature\CreditNote\CreditNoteFeatureConfigurationTest.php tests\Unit\CreditNote\CreditNoteEnumTest.php`; expect RED because the config/classes do not exist.
- [ ] Add the exact config and enum/error/gate contracts above.
- [ ] Rerun the command; expect exit code 0 and no implicit feature enablement.
- [ ] Refactor only duplicated enum-label plumbing and rerun both files.

## Task 2: Implement exact money, quantity, date, and Thai tax identifier values

**Files:**

- Create: `app/Domain/CreditNote/ValueObjects/CreditNoteMoney.php`
- Create: `app/Domain/CreditNote/ValueObjects/FixedQuantity.php`
- Create: `app/Domain/CreditNote/ValueObjects/ExactRatio.php`
- Create: `app/Domain/CreditNote/ValueObjects/ThaiTaxIdentifier.php`
- Create: `app/Domain/CreditNote/Rules/CreditNoteEventDateRule.php`
- Create: `tests/Unit/CreditNote/CreditNoteMoneyTest.php`
- Create: `tests/Unit/CreditNote/FixedQuantityTest.php`
- Create: `tests/Unit/CreditNote/ThaiTaxIdentifierTest.php`
- Create: `tests/Unit/CreditNote/CreditNoteEventDateRuleTest.php`

- **Consumes:** PHP signed 64-bit integers, source `DECIMAL(12,2)`, Asia/Bangkok, and the approved current/prior-month event rule.
- **Produces:** Float-free parsing/formatting, checked rational half-up math, a 13-digit checksum validator, and deterministic event-date validation.
- **Checkpoint:** Pure unit behavior only.

Required public contracts:

```php
CreditNoteMoney::FromDecimal(string $amount, bool $require_non_negative = false): CreditNoteMoney;
CreditNoteMoney::FromMinor(int $amount_minor): CreditNoteMoney;
CreditNoteMoney::Minor(): int;
CreditNoteMoney::Decimal(): string;

FixedQuantity::FromDecimal(string $quantity): FixedQuantity;
FixedQuantity::Hundredths(): int;
FixedQuantity::Decimal(): string;

ExactRatio::MultiplyDivideHalfUp(int $left, int $right, int $divisor): int;

ThaiTaxIdentifier::FromInput(string $tax_id): ThaiTaxIdentifier;
ThaiTaxIdentifier::Digits(): string;

CreditNoteEventDateRule::Validate(
    CarbonImmutable $event_date,
    CarbonImmutable $issue_date,
    ?string $late_issue_reason,
): void;
```

Normalize tax input by removing ASCII spaces and hyphens, then require exactly 13 digits and the Thai weighted checksum. Reject malformed Unicode digits rather than silently converting them. Event dates cannot be future, can be in the Issue month, and can be in the immediately preceding calendar month only with a non-empty late reason.

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Test `0`, valid zero fractions, maximum/minimum supported money, invalid signs/scales, overflow, quantity scale, ratio ties, VAT-scale ratios, valid/invalid checksum, leap/year boundaries, future dates, and late-reason trimming.
- [ ] Run `C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --without-tty tests\Unit\CreditNote\CreditNoteMoneyTest.php tests\Unit\CreditNote\FixedQuantityTest.php tests\Unit\CreditNote\ThaiTaxIdentifierTest.php tests\Unit\CreditNote\CreditNoteEventDateRuleTest.php`; expect RED for missing value objects/rule.
- [ ] Implement the smallest checked integer/string logic; no float and no undeclared extension.
- [ ] Rerun the command; expect exit code 0.
- [ ] Refactor overflow checks into `ExactRatio` and rerun all four files.

## Task 3: Implement document tax and total calculation

**Files:**

- Create: `app/Domain/CreditNote/ValueObjects/CreditNoteLineCalculationInput.php`
- Create: `app/Domain/CreditNote/ValueObjects/CreditNoteTotals.php`
- Create: `app/Domain/CreditNote/ValueObjects/CreditNoteCalculation.php`
- Create: `app/Domain/CreditNote/Services/CreditNoteCalculator.php`
- Create: `tests/Unit/CreditNote/CreditNoteCalculatorTest.php`

- **Consumes:** Task 2 exact values and approved Original/Corrected/Difference/VAT formulas.
- **Produces:** Deterministic per-line allocation and document totals in satang.
- **Checkpoint:** No persistence.

`CreditNoteCalculator::Calculate` accepts ordered source/current-credit/prior-net inputs and returns:

```php
public function Calculate(array $line_inputs): CreditNoteCalculation;
```

Each input includes stable item ID, source line amount, selected credit amount, tax treatment, and VAT basis points. The result contains per-item taxable-base/VAT/exempt/out-of-scope allocations plus `original`, `corrected`, and positive `difference` `CreditNoteTotals`. Group VAT-inclusive items by rate, compute one rounded group VAT amount, compute each provisional allocation, and assign any residual satang by ascending item ID. Reject negative corrected totals, invalid rate/treatment combinations, and duplicate item IDs.

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Test taxable 7 percent, exempt, out-of-scope service charge, mixed treatments, multiple rates, partial amount, prior issue/cancellation net, one-satang residual, stable ordering, zero/negative input, and overflow.
- [ ] Run `C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --without-tty tests\Unit\CreditNote\CreditNoteCalculatorTest.php`; expect RED.
- [ ] Implement exact group and allocation behavior.
- [ ] Rerun; expect exit code 0 and exact satang assertions.
- [ ] Refactor group accumulation without changing output; rerun the file.

## Task 4: Add and backfill receipt source metadata

**Files:**

- Create: `database/migrations/2026_08_12_000000_add_credit_note_metadata_to_receipt_details.php`
- Create: `tests/Feature/CreditNote/ReceiptLineMetadataMigrationTest.php`

- **Consumes:** Existing `receipt_details.tax` and exact standard descriptions at `OrderController::update` and `ReceiptController::gen_receipt_data`.
- **Produces:** Additive `line_type`, `tax_treatment`, and `vat_rate_basis_points` columns plus deterministic bounded backfill.
- **Checkpoint:** Existing quantity, price, discount, total, description, number, date, and billing values are byte-for-byte untouched.

The migration adds nullable/backward-compatible metadata first, then processes ascending ID chunks. Use these exact current source literals, not fuzzy matching:

```php
private const TUITION_PREFIX = 'ค่าคอร์สเรียน ';
private const BOOK_DESCRIPTION = 'ค่าหนังสือ';
private const DEPOSIT_DESCRIPTION = 'ค่ามัดจำ';
private const REGISTRATION_DESCRIPTION = 'ค่าลงทะเบียนแรกเข้า';
private const EQUIPMENT_DESCRIPTION = 'ค่าอุปกรณ์การเรียน';
```

Map `tax = 1` to `vat_inclusive/700` and `tax = 0` to `vat_exempt/0`. Map the tuition prefix to `tuition`, the four exact non-tuition descriptions to `book`, `deposit`, `registration_fee`, and `equipment` respectively, and every other description to `unknown`. Keep columns nullable at the database layer for safe rolling deployment; application writes and Issue eligibility require non-null tax metadata. Set `line_type` default to `unknown` for legacy writers. `down()` drops only the three added columns and is allowed only before real Credit Notes exist under the rollout contract.

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Build an isolated migration test with each exact description, a lookalike description, both tax flags, and sentinel source values.
- [ ] Run `C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --without-tty tests\Feature\CreditNote\ReceiptLineMetadataMigrationTest.php`; expect RED.
- [ ] Add the migration and chunked exact mapping.
- [ ] Rerun; expect exact types/rates and unchanged sentinels.
- [ ] Run `git diff --check`; expect no whitespace errors.

## Task 5: Persist metadata in every receipt-detail write path

**Files:**

- Create: `app/Domain/CreditNote/ValueObjects/ReceiptLineMetadata.php`
- Create: `app/Domain/CreditNote/Services/ReceiptLineMetadataResolver.php`
- Modify: `app/Models/receipt_details.php`
- Modify: `app/Http/Controllers/OrderController.php` at `update`
- Modify: `app/Http/Controllers/ReceiptController.php` at `gen_receipt_data` and `update`
- Modify: `resources/views/templates/edit_receipt.blade.php`
- Create: `tests/Feature/CreditNote/ReceiptLineMetadataWriteTest.php`

- **Consumes:** Task 1 enums, Task 4 columns, and the existing receipt editing request.
- **Produces:** Structured metadata on generated and manually edited receipt lines without breaking existing clients that omit `line_type`.
- **Checkpoint:** Receipt values and legacy `tax` behavior remain unchanged.

`ReceiptLineMetadataResolver` exposes:

```php
public function ResolveGenerated(ReceiptLineType $line_type, int $legacy_tax): ReceiptLineMetadata;
public function ResolveEdited(string $description, int $legacy_tax, ?string $submitted_line_type): ReceiptLineMetadata;
```

Generated rows pass explicit types. Edited rows accept a valid submitted type; an omitted type falls back to the same exact-description resolver for backward compatibility; unmatched rows become `unknown`. Tax treatment/rate always derive from the legacy tax flag and cannot be client-selected. Add an accessible line-type `<select name="line_type[]">` to each edit row while retaining existing field names.

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Test all five generated row types, edited explicit `other`, omitted legacy field, invalid type, taxable/non-taxable metadata, and unchanged receipt totals.
- [ ] Run `C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --without-tty tests\Feature\CreditNote\ReceiptLineMetadataWriteTest.php`; expect RED.
- [ ] Add resolver/model casts and modify the three verified write locations.
- [ ] Rerun; expect exit code 0.
- [ ] Run the focused existing Affiliate source-lock test; expect no regression.

## Task 6: Create Credit Note header and item persistence

**Files:**

- Create: `database/migrations/2026_08_12_000001_create_credit_notes_table.php`
- Create: `database/migrations/2026_08_12_000002_create_credit_note_items_table.php`
- Create: `app/Domain/CreditNote/Models/CreditNote.php`
- Create: `app/Domain/CreditNote/Models/CreditNoteItem.php`
- Modify: `app/Models/Receipt.php`
- Create: `tests/Feature/CreditNote/CreditNotePersistenceTest.php`

- **Consumes:** The approved header/item contract reproduced below and Task 1 enum values.
- **Produces:** Header/item schema, casts, relationships, portable active-Draft uniqueness, and lifecycle-aware immutability.
- **Checkpoint:** No command can create a document yet.

Create these exact header columns:

- identity: `id`, `receipt_id`, `centre_id`, `status`, `number`, `currency`;
- reason: `reason_code`, `reason_details`, `adjustment_event_date`, `late_issue_reason`;
- issue/cancellation: `issue_date`, `issued_at`, `issue_idempotency_key`, `cancellation_effective_date`, `cancelled_at`, `cancellation_reason`, `cancel_idempotency_key`;
- source/original reference: `source_snapshot_hash`, `source_receipt_updated_at`, `original_receipt_number`, `original_receipt_date`, `original_billing_details_text`;
- buyer snapshot: `buyer_name`, `buyer_address`, `buyer_tax_id`, `buyer_is_vat_registered`, `buyer_branch_type`, `buyer_branch_code`;
- issuer snapshot: `issuer_name`, `issuer_address`, `issuer_tax_id`, `issuer_branch_type`, `issuer_branch_code`;
- original totals: `original_taxable_base_minor`, `original_vat_minor`, `original_exempt_minor`, `original_out_of_scope_minor`, `original_grand_total_minor`;
- corrected totals: `corrected_taxable_base_minor`, `corrected_vat_minor`, `corrected_exempt_minor`, `corrected_out_of_scope_minor`, `corrected_grand_total_minor`;
- difference totals: `difference_taxable_base_minor`, `difference_vat_minor`, `difference_exempt_minor`, `difference_out_of_scope_minor`, `difference_grand_total_minor`;
- actors/concurrency/timestamps: `created_by`, `updated_by`, `issued_by`, `cancelled_by`, `lock_version`, `created_at`, `updated_at`; and
- implementation guards: `active_draft_receipt_id`, `issue_payload_hash`, `cancel_payload_hash`.

Create these exact item columns: `id`, `credit_note_id`, `sort_order`, `source_type`, `source_id`, `line_type`, `description`, `unit`, `tax_treatment`, `vat_rate_basis_points`, `original_quantity`, `original_unit_price_minor`, `original_discount_minor`, `original_line_total_minor`, `credit_quantity`, `credit_amount_minor`, `credit_taxable_base_minor`, `credit_vat_minor`, `credit_exempt_minor`, `credit_out_of_scope_minor`, `created_at`, and `updated_at`.

Use named foreign keys, nullable unique Issue/Cancel keys, globally unique nullable `number`, unique nullable `active_draft_receipt_id`, indexes on `(centre_id,status)`, `(status,issue_date)`, and `(receipt_id,status)`, and a unique item key on `(credit_note_id,source_type,source_id)`.

Header monetary snapshots are unsigned BIGINT-compatible columns; posting deltas are introduced later as signed values. User foreign keys are nullable/null-on-delete; receipt and department are restrict-on-delete. `CreditNote::booted` rejects deletion, rejects Draft-field changes after Issue, and permits only the exact `issued -> cancelled` cancellation field allowlist. `CreditNoteItem` allows create/update/delete only while its parent is Draft; deletion remains forbidden once its parent is Issued or Cancelled.

Add `Receipt::CreditNotes(): HasMany`. Do not alter existing relationship names or fillable receipt fields.

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Test FKs, unique number/key/draft guard, casts, Draft item replacement, issued/cancelled immutability, no header delete, no issued item delete, and user null-on-delete.
- [ ] Run `C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --without-tty tests\Feature\CreditNote\CreditNotePersistenceTest.php`; expect RED.
- [ ] Add both migrations and models/relationship.
- [ ] Rerun; expect exit code 0 on SQLite.
- [ ] Refactor model allowlists into named constants and rerun.

## Task 7: Create number, posting, and audit-event evidence

**Files:**

- Create: `database/migrations/2026_08_12_000003_create_credit_note_running_numbers_table.php`
- Create: `database/migrations/2026_08_12_000004_create_credit_note_postings_table.php`
- Create: `database/migrations/2026_08_12_000005_create_credit_note_events_table.php`
- Create: `app/Domain/CreditNote/Models/CreditNoteRunningNumber.php`
- Create: `app/Domain/CreditNote/Models/CreditNotePosting.php`
- Create: `app/Domain/CreditNote/Models/CreditNoteEvent.php`
- Create: `app/Domain/CreditNote/Services/CreditNoteEventRecorder.php`
- Modify: `app/Domain/CreditNote/Models/CreditNote.php`
- Create: `tests/Feature/CreditNote/CreditNoteEvidencePersistenceTest.php`

- **Consumes:** Approved numbering, posting, and sanitized event contracts.
- **Produces:** One sequence per centre/year/month, append-only signed postings, and append-only audit events.
- **Checkpoint:** Tables contain no records until command tasks.

Use a unique `(centre_id,year,month)` sequence key, a unique `(credit_note_id,posting_type)` posting key, and report indexes `(centre_id,effective_date)` and `(posting_type,effective_date)`. Posting delta columns are signed BIGINT-compatible values. Events have JSON `metadata`, no `updated_at`, and nullable/null-on-delete actor ID. All posting/event model updates and deletes throw `LogicException`.

`CreditNoteEventRecorder::Record` accepts IDs, operation/state, amount, actor, reason code, correlation ID, and a pre-sanitized metadata allowlist. It removes keys matching password/token/secret, buyer/contact/tax/address/telephone/billing/description/snapshot before persistence.

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Test uniqueness, signed deltas, report indexes through schema inspection, append-only behavior, relationships, and event sanitization.
- [ ] Run `C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --without-tty tests\Feature\CreditNote\CreditNoteEvidencePersistenceTest.php`; expect RED.
- [ ] Add migrations/models/recorder.
- [ ] Rerun; expect exit code 0.
- [ ] Attempt prohibited update/delete paths in tests and confirm they fail.

## Task 8: Read, normalize, hash, and validate receipt sources

**Files:**

- Create: `app/Domain/CreditNote/ValueObjects/CreditNoteSourceLine.php`
- Create: `app/Domain/CreditNote/ValueObjects/CreditNoteSourceSnapshot.php`
- Create: `app/Domain/CreditNote/ValueObjects/CreditNoteEligibility.php`
- Create: `app/Domain/CreditNote/Services/CreditNoteSourceReader.php`
- Create: `app/Domain/CreditNote/Services/CreditNoteSourceHasher.php`
- Create: `app/Domain/CreditNote/Services/CreditNoteEligibilityService.php`
- Create: `tests/Unit/CreditNote/CreditNoteSourceHasherTest.php`
- Create: `tests/Feature/CreditNote/CreditNoteEligibilityServiceTest.php`

- **Consumes:** Receipt/Order/Contact/Department models, Task 2 exact values, and Tasks 4–5 metadata.
- **Produces:** One immutable source representation, deterministic SHA-256, actionable eligibility, remaining line values, and a synthetic service-charge line.
- **Checkpoint:** Read-only behavior.

`CreditNoteSourceReader::Read(Receipt $receipt): CreditNoteSourceSnapshot` loads receipt details ascending by ID, the linked order, contact, and department without N+1 queries. A positive `orders.orther_fee` adds one `receipt_service_charge` line with source ID equal to order ID, quantity `1.00`, `service_charge`, `out_of_scope`, and rate `0`; it never writes `receipt_details`.

`CreditNoteEligibilityService` exposes `Evaluate`, `AssertCanCreateDraft`, and `RemainingLines`. It checks source visibility outside this service, then checks number/date/paid state, non-negative exact detail values, detail sum against `receipts.total_fee` within one satang, computed grand total, remaining value, active Draft, and source completeness. When an Affiliate commission exists, any positive financially relevant `unknown` source line blocks Issue. Lack of an Affiliate commission does not turn unknown evidence into tuition.

The source hash must remain identical across eager-loading order and PHP process boundaries and must change for every listed source field.

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Test eligible sources, each missing field, paid-state alternatives, one-satang tolerance, two-satang mismatch, negative/overflow values, synthetic charge, active Draft, fully credited receipt, unknown-with/without-Affiliate, stable canonical hash, and every hash input mutation.
- [ ] Run `C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --without-tty tests\Unit\CreditNote\CreditNoteSourceHasherTest.php tests\Feature\CreditNote\CreditNoteEligibilityServiceTest.php`; expect RED.
- [ ] Implement reader/hasher/eligibility without persistence mutations.
- [ ] Rerun; expect exit code 0.
- [ ] Inspect query count in the multi-line case and remove N+1 access before rerunning.

## Task 9: Create and update Drafts atomically

**Files:**

- Create: `app/Domain/CreditNote/Commands/UpdateCreditNoteDraft.php`
- Create: `app/Domain/CreditNote/Services/CreditNoteCommandService.php`
- Create: `tests/Feature/CreditNote/CreditNoteDraftServiceTest.php`

- **Consumes:** Tasks 3, 6–8 and authenticated `User` actors.
- **Produces:** `CreateDraft` and `UpdateDraft` with source locks, optimistic versioning, buyer/reason/event validation, item replacement, totals, and sanitized events.
- **Checkpoint:** Drafts have no number, posting, or Affiliate effect.

Required service signatures:

```php
public function CreateDraft(
    Receipt $receipt,
    User $actor,
    string $correlation_id,
): CreditNote;

public function UpdateDraft(
    CreditNote $credit_note,
    UpdateCreditNoteDraft $command,
    User $actor,
    string $correlation_id,
): CreditNote;
```

`CreateDraft` locks the receipt, runs eligibility, sets `active_draft_receipt_id`, copies original reference/billing text, prefills buyer name from receipt and buyer address only from `Contact::address`, stores the source hash/update timestamp, and writes one `created` event. It creates no items.

`UpdateDraft` locks receipt then note, compares `lock_version`, re-reads source, accepts only source type/ID plus optional quantity and required positive amount for selected rows, rejects duplicates/non-source IDs/remaining overage, replaces Draft items, computes provisional allocations/totals, increments version, and records only changed field names and item IDs in event metadata. It never updates Contact or Receipt.

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Test one Draft, duplicate conflict, source mismatch, prefill behavior, no source mutation, selected-line allowlist, partial/full values, duplicate IDs, over-credit, stale version, item replacement, event sanitization, and transaction rollback.
- [ ] Run `C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --without-tty tests\Feature\CreditNote\CreditNoteDraftServiceTest.php`; expect RED.
- [ ] Implement `CreateDraft` only; rerun its focused tests to GREEN.
- [ ] Implement `UpdateDraft`; rerun the entire file to GREEN.
- [ ] Refactor transaction-local validation into private PascalCase methods; rerun.

## Task 10: Generate concurrency-safe Credit Note numbers

**Files:**

- Create: `app/Domain/CreditNote/Services/CreditNoteNumberGenerator.php`
- Create: `tests/Feature/CreditNote/CreditNoteNumberGeneratorTest.php`

- **Consumes:** Task 7 sequence table, `Department::code`, and Asia/Bangkok Issue date.
- **Produces:** `{CENTRE}CN{YY}{MM}{NNN}` inside the caller's transaction.
- **Checkpoint:** The generator cannot commit independently.

Expose:

```php
public function Next(int $centre_id, CarbonImmutable $issue_date): string;
```

Require an existing non-empty department code, uppercase it, and validate it against a conservative `[A-Z0-9-]{1,16}` allowlist before formatting. Lock the existing sequence row. When absent, insert `0`, catch only the named unique-key race, then reselect with `lockForUpdate`. Increment once, use a minimum three-digit pad, and allow values over 999. A rolled-back surrounding transaction must restore the counter.

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Test first/next values, per-centre/month/year isolation, uppercase, invalid/missing code, values over 999, unique collision recovery, and outer rollback.
- [ ] Run `C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --without-tty tests\Feature\CreditNote\CreditNoteNumberGeneratorTest.php`; expect RED.
- [ ] Implement without calling `DB::transaction` inside `Next`.
- [ ] Rerun; expect exact strings and exit code 0.
- [ ] Leave simultaneous process behavior for Task 27.

## Task 11: Issue a Draft as one immutable financial transaction

**Files:**

- Create: `app/Domain/CreditNote/Commands/IssueCreditNote.php`
- Create: `app/Domain/CreditNote/Contracts/AffiliateCreditNoteAdjustment.php`
- Create: `app/Domain/CreditNote/ValueObjects/AffiliateCreditNoteAdjustmentResult.php`
- Modify: `app/Domain/CreditNote/Services/CreditNoteCommandService.php` at `Issue`
- Create: `tests/Feature/CreditNote/CreditNoteIssueServiceTest.php`

- **Consumes:** Tasks 1–3 and 6–10; an Affiliate adapter is injected behind the contract created in this task.
- **Produces:** Idempotent Issue, immutable number/snapshots/totals, one negative posting, one event, and atomic rollback.
- **Checkpoint:** No HTTP endpoint yet.

Add:

```php
public function Issue(
    CreditNote $credit_note,
    IssueCreditNote $command,
    User $actor,
    string $correlation_id,
): CreditNote;
```

The command carries `idempotency_key` and submitted `lock_version`. Hash the canonical command payload separately from the source hash. Under receipt-then-note locks, return the prior result for the same key/hash even if the document is now Cancelled; reject the key with another hash; require Draft/non-empty items/current version; recompute source hash/remaining/totals; validate buyer fields, Thai tax ID, branch rules, reason/date, and non-empty issuer config; allocate the number; snapshot issuer; clear `active_draft_receipt_id`; set Bangkok issue date/time; create one negative posting; call the Affiliate adapter; record Issue; and commit. Every failure leaves Draft/version/sequence/posting/Affiliate/wallet/event unchanged.

`AffiliateCreditNoteAdjustment` exposes `ApplyIssue` and `ApplyCancellation`. Task 11 and Task 12 service tests inject explicit fakes: the no-commission fake returns `AffiliateCreditNoteAdjustmentResult::NotApplicable()`, and the existing-commission fake throws `CREDIT_NOTE_AFFILIATE_ADJUSTMENT_FAILED`. Do not add a production no-op binding; Task 15 binds the real fail-closed implementation before any HTTP controller resolves the command service.

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Test issuer/buyer requirements, branch `00000`, reason/date, current version, source-hash mismatch, partial/full/sequential notes, over-credit, exact VAT, unique number, posting signs/date, immutability, same-key retry, conflicting-key payload, and rollback after number allocation/posting/Affiliate failure.
- [ ] Run `C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --without-tty tests\Feature\CreditNote\CreditNoteIssueServiceTest.php`; expect RED.
- [ ] Implement validation and snapshotting first; rerun focused validation tests.
- [ ] Implement number/posting/state/event in one transaction; rerun all tests.
- [ ] Inject explicit test fakes through the new adapter contract; allow a no-effect result only for receipts with no Affiliate commission and never silently ignore an existing commission.

## Task 12: Cancel Draft and Issued documents append-only

**Files:**

- Create: `app/Domain/CreditNote/Commands/CancelCreditNote.php`
- Modify: `app/Domain/CreditNote/Services/CreditNoteCommandService.php` at `Cancel`
- Create: `tests/Feature/CreditNote/CreditNoteCancellationServiceTest.php`

- **Consumes:** Issued evidence and the Affiliate cancellation contract from Task 11.
- **Produces:** Terminal Draft cancellation or exact Issued reversal in the current Bangkok date.
- **Checkpoint:** Cancellation never deletes or rewrites Issue evidence.

Add:

```php
public function Cancel(
    CreditNote $credit_note,
    CancelCreditNote $command,
    User $actor,
    string $correlation_id,
): CreditNote;
```

Require a trimmed reason and idempotency key. Hash note ID/reason, excluding derived current date. Lock receipt then note. For Draft, clear the active-Draft guard and store cancellation fields/event only. For Issued, copy the Issue posting with every delta sign inverted, effective on the current Bangkok date, restore the exact Affiliate adjustment, transition to Cancelled, and append the event. Same key/hash returns the existing terminal result; a different payload or repeated cancellation key conflicts. A new Draft may be created only after cancellation and only when net remaining value is positive.

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Test Draft cancellation, same/cross-month Issued cancellation, exact sign reversal, retained Issue posting/number/snapshots, terminal state, idempotent retry, conflict, missing reason, atomic rollback, and subsequent Draft eligibility.
- [ ] Run `C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --without-tty tests\Feature\CreditNote\CreditNoteCancellationServiceTest.php`; expect RED.
- [ ] Implement Draft path; rerun its tests.
- [ ] Implement Issued path and exact posting copy; rerun all tests.
- [ ] Confirm no UPDATE targets `credit_note_postings` or Issue events.

## Task 13: Lock receipt/order source changes after a numbered Credit Note

**Files:**

- Create: `app/Domain/CreditNote/Services/CreditNoteFinancialSourceGuard.php`
- Create: `app/Domain/Financial/Services/FinancialSourceGuard.php`
- Modify: `app/Http/Controllers/ReceiptController.php` at existing Affiliate guard calls
- Modify: `app/Http/Controllers/OrderController.php` at existing Affiliate guard calls
- Modify: `app/Http/Controllers/ContactController.php` at existing Affiliate guard calls
- Create: `tests/Feature/CreditNote/CreditNoteFinancialSourceLockTest.php`
- Modify: `tests/Feature/Affiliate/AffiliateFinancialSourceLockTest.php`

- **Consumes:** Existing `AffiliateFinancialSourceGuard` and numbered Credit Note evidence.
- **Produces:** One cross-cutting guard that preserves both Affiliate and Credit Note source invariants.
- **Checkpoint:** No unrelated source behavior changes when neither evidence type exists.

`FinancialSourceGuard` composes, rather than replaces, the Affiliate and Credit Note guards and retains the existing public operations: order update/cancel, tuition replacement, receipt update, contact deletion, `IsOrderLocked`, and `IsReceiptLocked`. Replace controller injections/calls with the composite. `CreditNoteFinancialSourceGuard` locks when the receipt/order has any Credit Note with non-null number, regardless of current Issued/Cancelled status. It permits metadata that cannot change source hash only when explicitly allowlisted. It blocks detail replacement, service-charge/financial order changes, receipt financial changes, order/receipt deletion, and contact deletion that would orphan a referenced source.

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Test no-evidence allowance, active Draft allowance, issued lock, cancelled-issued lock, cancelled-Draft allowance, Affiliate-only lock, combined evidence, and safe user-facing error.
- [ ] Run both source-lock test files; expect the new file RED and existing Affiliate tests GREEN before implementation.
- [ ] Add composite guard and replace only verified call sites.
- [ ] Rerun both files; expect exit code 0 and preserved Affiliate behavior.
- [ ] Verify controller constructors resolve through Laravel's container.

## Task 14: Add Affiliate Credit Note evidence and ledger types

**Files:**

- Create: `database/migrations/2026_08_12_000006_create_affiliate_commission_credit_note_adjustments_table.php`
- Create: `app/Domain/Affiliate/Enums/AffiliateCreditNoteEventType.php`
- Modify: `app/Domain/Affiliate/Enums/AffiliateLedgerEntryType.php`
- Create: `app/Domain/Affiliate/Models/AffiliateCommissionCreditNoteAdjustment.php`
- Modify: `app/Domain/Affiliate/Models/AffiliateCommission.php`
- Modify: `app/Domain/CreditNote/Models/CreditNote.php`
- Create: `tests/Feature/CreditNote/AffiliateCreditNotePersistenceTest.php`

- **Consumes:** Existing Affiliate commission/ledger tables and Task 6 Credit Notes.
- **Produces:** Append-only signed Affiliate adjustment evidence and two named ledger types.
- **Checkpoint:** No commission is changed yet.

Use signed BIGINT `tuition_reversal_delta_minor` and `commission_reversal_delta_minor`, unique `(credit_note_id,event_type)`, unique `idempotency_key`, restrict-on-delete financial FKs, nullable/null-on-delete `recorded_by`, nullable `ledger_entry_id`, and status-before/status-after snapshots. The model rejects every update/delete. Add `CreditNoteAdjustments(): HasMany` to commission and `AffiliateAdjustments(): HasMany` to Credit Note.

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Test signed values, unique event/key, relationships, FK deletion rules, append-only behavior, and enum casts.
- [ ] Run `C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --without-tty tests\Feature\CreditNote\AffiliateCreditNotePersistenceTest.php`; expect RED.
- [ ] Add migration/model/enums/relationships.
- [ ] Rerun; expect exit code 0.
- [ ] Run existing Affiliate wallet persistence tests; expect no enum regression.

## Task 15: Apply and restore Affiliate commission effects exactly once

**Files:**

- Create: `app/Domain/Affiliate/Services/AffiliateCreditNoteAdjustmentService.php`
- Modify: `app/Domain/Affiliate/Services/AffiliateCommissionService.php` at `RecordConfirmedExternalRefund`
- Modify: `app/Domain/Affiliate/Services/AffiliateWalletService.php` at the negative-available allowlist
- Modify: `app/Providers/AppServiceProvider.php`
- Create: `tests/Feature/CreditNote/AffiliateCreditNoteAdjustmentTest.php`
- Modify: `tests/Feature/Affiliate/AffiliateRefundServiceTest.php`
- Modify: `tests/Feature/Affiliate/AffiliateWalletServiceTest.php`

- **Consumes:** Task 11 adapter contract, Task 14 persistence, existing commission calculation, external reversals, and wallet idempotency.
- **Produces:** A production adapter binding, tuition-only Issue reversal, exact Cancellation restoration, and cross-workflow double-consumption prevention.
- **Checkpoint:** All effects remain inside the caller's transaction.

`AffiliateCreditNoteAdjustmentService` implements the Task 11 contract. Bind the interface to this service in `AppServiceProvider::register`. `ApplyIssue` locks the receipt commission after receipt/note locks, sums external refunded tuition, sums net Credit Note tuition deltas, caps requested tuition and commission, records status before/after, and uses deterministic keys `credit-note:affiliate:issue:{credit_note_id}`. `ApplyCancellation` loads the exact Issue adjustment, negates its two deltas, and uses `credit-note:affiliate:cancellation:{credit_note_id}`.

Status restoration rules:

1. If resulting `reversed_minor >= commission_minor`, status is `REVERSED`.
2. If current status is not `REVERSED`, retain the current status.
3. If current status is `REVERSED` and the Issue adjustment caused that state, restore its stored prior `PENDING`, `HELD`, or `AVAILABLE` state.
4. Add a positive wallet restoration when the restored commission is currently available, including an Issue made while pending that was later released; do not add wallet value while pending/held.

Modify external refund remaining-tuition validation to subtract net Credit Note tuition adjustments before accepting a refund. Keep external reversal rows untouched.

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Test no commission, Pending, Held, Available, fully Reversed, partial/full tuition, non-tuition lines, external refund before/after Credit Note, Issue-before-release then Cancellation-after-release, exact status restoration, exact ledger entries, retries, and injected failures.
- [ ] Run the new test plus both modified Affiliate tests; expect RED only for new behavior.
- [ ] Implement Issue adjustment and external refund cap; rerun focused tests.
- [ ] Implement Cancellation restoration; rerun all three files.
- [ ] Confirm one transaction rollback removes note transition, posting, adjustment, ledger, wallet projection, and commission projection together.

## Task 16: Create permissions and centre-scoped policy/query boundaries

**Files:**

- Create: `database/migrations/2026_08_12_000007_add_credit_note_permissions.php`
- Create: `app/Policies/CreditNotePolicy.php`
- Modify: `app/Providers/AuthServiceProvider.php`
- Create: `app/Domain/CreditNote/Queries/CreditNoteQuery.php`
- Create: `tests/Feature/CreditNote/CreditNoteAuthorizationTest.php`

- **Consumes:** Spatie Permission, `all-centre`, `users.department_id`, Credit Note/Receipt centre IDs, and Task 8 eligibility.
- **Produces:** Six permissions, policy decisions, filtered list query, and non-disclosing resource resolution.
- **Checkpoint:** Permissions are created but granted to no role.

Create exactly `credit-note-list`, `credit-note-show`, `credit-note-create`, `credit-note-edit`, `credit-note-issue`, and `credit-note-cancel`, all with `web` guard. `up()` does not grant roles; `down()` removes only these names after clearing permission cache.

`CreditNotePolicy` covers list/show/create/edit/issue/cancel/export. `CreditNoteQuery` exposes `ForList`, `FindVisibleOrFail`, `FindReceiptVisibleOrFail`, `Document`, and batched receipt summaries. An actor with `all-centre` sees all; otherwise require a non-null department and exact centre match. Resolve through scoped query before policy authorization so cross-centre and missing records both become 404.

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Test migration names/guard/no grants, every permission, same centre, cross-centre 404 resolution, all-centre, null department, export scope, and print/PDF show scope.
- [ ] Run `C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --without-tty tests\Feature\CreditNote\CreditNoteAuthorizationTest.php`; expect RED.
- [ ] Add migration, policy registration, and query scopes.
- [ ] Rerun; expect exit code 0.
- [ ] Run existing Affiliate role permission tests; expect no role-assignment regression.

## Task 17: Add correlation, error, request, route, and controller interfaces

**Files:**

- Create: `app/Http/Middleware/AttachCreditNoteCorrelationId.php`
- Create: `app/Http/Middleware/RequireCreditNoteFeature.php`
- Create: `app/Http/Middleware/HandleCreditNoteResponses.php`
- Modify: `app/Http/Kernel.php`
- Create: `app/Http/Requests/CreditNote/CreditNoteIndexRequest.php`
- Create: `app/Http/Requests/CreditNote/UpdateCreditNoteRequest.php`
- Create: `app/Http/Requests/CreditNote/IssueCreditNoteRequest.php`
- Create: `app/Http/Requests/CreditNote/CancelCreditNoteRequest.php`
- Create: `app/Http/Requests/CreditNote/CreditNoteExportRequest.php`
- Create: `app/Http/Controllers/CreditNoteController.php`
- Create: `routes/credit-notes.php`
- Create: `tests/Feature/CreditNote/CreditNoteHttpContractTest.php`

- **Consumes:** Tasks 1, 9, 11–12, 15, and 16.
- **Produces:** All non-document/non-export approved endpoints, stable JSON errors, normal form redirects, CSRF, and bounded IDs.
- **Checkpoint:** Document/export endpoints return temporary 404 until their owning tasks, rather than fake success.

Accept `X-Correlation-ID` only when it matches `[A-Za-z0-9._:-]{1,96}`; otherwise generate UUID. Put it in request attribute `credit_note_correlation_id`, response header, and JSON body. The response middleware wraps validation/domain/authorization/not-found/unexpected failures only for Credit Note routes; HTML reuses safe translated flash/form errors. Log unexpected exceptions with correlation, actor/centre/resource IDs and no request payload.

Request contracts:

- Update: `lock_version`, structured buyer fields, reason code/details, adjustment date, optional late reason, and selected item rows containing only source type/ID, quantity, and decimal-string amount.
- Issue: `lock_version` and an idempotency key matching `[A-Za-z0-9._:-]{1,120}` from `X-Idempotency-Key` or the browser form's `idempotency_key` field.
- Cancel: required reason and the same header/form key rule. Reject a request when both key locations are present but differ.
- Index/export: exact filter/date/status/reason/centre allowlists; `per_page` capped at 100; selected IDs capped at 500.

Controller methods are `Index`, `Store`, `Show`, `Update`, `Issue`, and `Cancel`. They resolve visible resources, authorize, map requests to commands, call one public service method, and return HTML/JSON without direct model writes.

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Test route methods/names/middleware, CSRF, request allowlists, 201/200/403/404/409/422/500 JSON shape, correlation preservation/generation, HTML redirects, feature disabled, financial actions disabled, and no DELETE route.
- [ ] Run `C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --without-tty tests\Feature\CreditNote\CreditNoteHttpContractTest.php`; expect RED.
- [ ] Add middleware/requests/routes/controller incrementally and rerun after each endpoint group.
- [ ] Run `C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan route:list --path=credit-notes --except-vendor -v`; expect exactly nine named routes after Tasks 20–21 finish and dedicated permission middleware on each.

## Task 18: Build the paginated Credit Note register and Receipt-row actions

**Files:**

- Modify: `app/Domain/CreditNote/Queries/CreditNoteQuery.php`
- Modify: `app/Http/Controllers/CreditNoteController.php` at `Index`
- Modify: `app/Http/Controllers/ReceiptController.php` at `index`
- Create: `resources/views/credit-notes/index.blade.php`
- Create: `resources/views/credit-notes/partials/status-badge.blade.php`
- Create: `resources/views/credit-notes/partials/list-actions.blade.php`
- Create: `resources/views/credit-notes/partials/receipt-actions.blade.php`
- Create: `public/js/credit-note-index.js`
- Create: `public/css/credit-notes.css`
- Modify: `resources/views/receipts/index.blade.php`
- Modify: `resources/views/receipts/script.blade.php`
- Modify: `resources/views/layouts/menu.blade.php`
- Create: `tests/Feature/CreditNote/CreditNoteRegisterTest.php`

- **Consumes:** Task 16 scoped query and existing AdminLTE/DataTables receipt/register patterns.
- **Produces:** Server-side list/filter/pagination, non-color statuses, menu entry, create/view actions, and derived receipt status.
- **Checkpoint:** Every mutation remains a CSRF form; UI visibility is not authorization.

The list query performs server-side filters for number, original number, issue/event date ranges, centre, customer/student, status, and reason. Eager-load all displayed relationships and aggregate Affiliate status without per-row queries. Receipt summaries are batched by receipt IDs and derive `not_credited`, `partially_credited`, or `fully_credited` from original value and net Issued/Cancelled item evidence. Render Create only when permission and eligibility allow it; otherwise render a disabled control with textual reason. Render View when any note exists.

Use Blade partials to produce escaped action/status HTML rather than concatenating user data in controller strings. The DataTables module handles loading/errors and announces them through `aria-live`. Add the menu under Receipt Management when feature enabled and `credit-note-list` is allowed.

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Test every filter, pagination, centre scope, derived statuses, active Draft action, fully credited action, incomplete source reason, menu visibility, HTML escaping, and bounded query count.
- [ ] Run `C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --without-tty tests\Feature\CreditNote\CreditNoteRegisterTest.php`; expect RED.
- [ ] Implement query/controller response first; rerun JSON assertions.
- [ ] Add Blade/JS/CSS/Receipt integration; rerun presentation assertions.
- [ ] Confirm keyboard focus, labels, target sizes, and textual status cues in rendered HTML.

## Task 19: Build the accessible Draft editor and read-only detail page

**Files:**

- Create: `resources/views/credit-notes/show.blade.php`
- Create: `resources/views/credit-notes/partials/editor.blade.php`
- Create: `resources/views/credit-notes/partials/totals.blade.php`
- Create: `resources/views/credit-notes/partials/timeline.blade.php`
- Create: `public/js/credit-note-editor.js`
- Create: `tests/JavaScript/credit-note-editor.test.mjs`
- Create: `tests/Feature/CreditNote/CreditNoteEditorTest.php`

- **Consumes:** Task 9 commands, Task 17 routes, Task 18 styles, and server-provided remaining lines.
- **Produces:** No-JavaScript-capable forms plus exact BigInt-enhanced preview, Draft watermark, read-only Issued/Cancelled views, audit timeline, and accessible feedback.
- **Checkpoint:** Browser calculations are previews; server calculations remain authoritative.

The editor includes source receipt summary, buyer tax snapshot, event/reason fields, source-line table, Original/Corrected/Difference summary, and Save/Preview/Issue/Cancel forms. Include `lock_version` in Save/Issue and independent hidden UUID idempotency keys for Issue/Cancel. Every source row displays original, previously credited, remaining, current quantity/amount, tax treatment, and rate. Never make tax fields editable.

`credit-note-editor.js` exports pure decimal-to-minor and preview functions using strings/BigInt, never float, and enhances form state/loading/`aria-live`. It must not use `innerHTML` for user content. When JavaScript is absent, browser submission and server validation still work.

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Write Node tests for decimal parsing, exact totals, invalid input, row cap, UUID preservation, and safe text rendering.
- [ ] Write feature tests for labels/errors, Draft watermark, read-only states, action visibility, escaped buyer/reason/item text, timeline, focus target, and non-color statuses.
- [ ] Run `node --test tests/JavaScript/credit-note-editor.test.mjs` and the PHP editor test; expect RED.
- [ ] Implement pure JS first, then Blade integration.
- [ ] Rerun both commands; expect exit code 0.

## Task 20: Render printable HTML and stream PDF without public persistence

**Files:**

- Create: `app/Http/Controllers/CreditNoteDocumentController.php`
- Create: `resources/views/credit-notes/print.blade.php`
- Create: `resources/views/credit-notes/documents/credit-note.blade.php`
- Modify: `routes/credit-notes.php`
- Create: `tests/Feature/CreditNote/CreditNoteDocumentTest.php`

- **Consumes:** Task 16 document query, Task 17 show authorization/correlation, and DOMPDF `stream`.
- **Produces:** Authorized printable HTML and inline PDF for Draft/Issued/Cancelled states.
- **Checkpoint:** Rendering has no state mutation.

`PrintDocument` returns HTML; `PdfDocument` calls `PDF::loadView(...)->stream($filename)`. Use `DRAFT-CN-{id}.pdf` before Issue and the immutable document number afterward. The shared document partial displays the approved bilingual heading, issuer/buyer/branch identity, note and original receipt number/date, items, Original/Corrected/Difference, VAT/exempt/out-of-scope/grand totals, reason, student reference, signature areas, and Draft/Cancelled watermark. Escape every value and render plain text with line breaks safely.

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Test all required labels/values, Draft/Issued/Cancelled watermark rules, show permission, centre 404, escaped hostile input, PDF content type/disposition, generation failure code, and unchanged database state.
- [ ] Snapshot `public/file_store` names before/after PDF tests and assert no new file.
- [ ] Run the document test; expect RED.
- [ ] Implement shared view/controller/routes; rerun and expect exit code 0.

## Task 21: Export the scoped Credit Note register

**Files:**

- Create: `app/Exports/CreditNoteRegisterExport.php`
- Create: `app/Http/Controllers/CreditNoteExportController.php`
- Modify: `routes/credit-notes.php`
- Create: `tests/Feature/CreditNote/CreditNoteExportTest.php`

- **Consumes:** Task 16 list scope and Task 17 export request.
- **Produces:** Authorized filtered/selected Excel export with approved columns.
- **Checkpoint:** Export is read-only and bounded.

Implement `FromQuery`, `WithHeadings`, and `WithMapping`. Use the same scoped/filtered query as the register; selected IDs narrow but never broaden it. Export Credit Note number, status, issue date, cancellation date, adjustment event date, centre, original receipt number/date, buyer name/tax ID, reason, original value, corrected value, difference, taxable-base difference, VAT difference, exempt/out-of-scope difference, Affiliate adjustment, and created/issued/cancelled actor IDs. Convert minor values to two-decimal strings and export actor IDs rather than names. Do not export billing text, phone, logs, or internal metadata.

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Test filter parity, selected IDs, cross-centre exclusion, missing permission, 500-ID cap, headings/order, exact decimals, and no unauthorized PII columns.
- [ ] Run the export test; expect RED.
- [ ] Implement export/controller/route and rerun; expect exit code 0.
- [ ] Verify route order keeps `/credit-notes/export` out of the resource-ID matcher.

## Task 22: Add one reusable Credit Note revenue summary query

**Files:**

- Create: `app/Domain/CreditNote/Queries/CreditNoteRevenueQuery.php`
- Create: `tests/Feature/CreditNote/CreditNoteRevenueQueryTest.php`

- **Consumes:** Signed `credit_note_postings`, receipt gross values, centre/date filters, and Task 2 decimal formatting.
- **Produces:** Batched daily/monthly/yearly/branch Issue, Cancellation, count, and net aggregates.
- **Checkpoint:** Existing controllers are not modified yet.

Expose methods for `ByDate`, `ByMonth`, `ByYear`, `ByBranch`, and `Totals`, all receiving explicit date/centre scope inputs. Return maps keyed exactly like the consuming receipt query. Sum negative Issue postings as a positive displayed Issue amount, sum positive Cancellation as a positive displayed Cancellation amount, and compute signed net delta. Use grouped SQL and no query inside a result loop. Handle groups that contain only Credit Note postings by merging key sets rather than dropping them.

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Test no postings, Issue only, Issue plus same/cross-period Cancellation, centre/date boundaries, branch, daily/monthly/yearly keys, counts, and Credit-Note-only periods.
- [ ] Run the query test; expect RED.
- [ ] Implement grouped queries and deterministic decimal output.
- [ ] Rerun; expect exit code 0 and bounded query count.

## Task 23: Preserve gross APIs while adding Credit Note and net fields

**Files:**

- Modify: `app/Http/Controllers/ReceiptController.php` at `GetRevenueSummaryByBranch` and `GetRevenueSummaryByBranchMonthly`
- Modify: `app/Http/Controllers/DashboardController.php` at `receipt_sum_by_date`, `receipt_sum_by_month`, and `receipt_sum_by_year`
- Create: `tests/Feature/CreditNote/CreditNoteRevenueCompatibilityTest.php`

- **Consumes:** Task 22 aggregates and current response shapes.
- **Produces:** Additive fields at overall and grouped levels with unchanged gross values/counts.
- **Checkpoint:** Public endpoint authentication remains unchanged.

Merge Credit Note aggregates by centre/date/month/year. Keep current `SUM(receipts.total_fee)` expressions and existing field names untouched. Add all five approved fields to every applicable row and overall response. For dashboard row compatibility, retain `total_fee_sum` as gross and add the new fields beside it. Validate monthly API `month/year` inputs before querying; replace raw exception text in 500 responses with the existing generic message plus correlation only when the Credit Note middleware is in scope.

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Capture baseline gross-only fixtures and assert old fields/types/counts exactly.
- [ ] Add Issue/Cancellation fixtures in different periods and assert additive fields/net formula for all five methods.
- [ ] Run the compatibility test; expect RED for missing new fields while old assertions remain GREEN.
- [ ] Modify controllers through the shared query and rerun; expect every compatibility assertion GREEN.

## Task 24: Display Gross, Issue, Cancellation, and Net on the dashboard

**Files:**

- Modify: `resources/views/home.blade.php`
- Modify: `resources/views/script.blade.php` at receipt day/month/year rendering
- Create: `public/js/credit-note-revenue.js`
- Create: `tests/JavaScript/credit-note-revenue.test.mjs`
- Create: `tests/Feature/CreditNote/CreditNoteDashboardPresentationTest.php`

- **Consumes:** Task 23 response fields and current ApexCharts dashboard.
- **Produces:** Explicit Gross/Issued/Cancelled/Net summary cards and series without silently relabeling gross values.
- **Checkpoint:** Existing receipt counts and links remain intact.

Extract pure response normalization/series-building into `credit-note-revenue.js`. Keep amounts as validated decimal strings until chart rendering. Add distinct textual labels and icons, not color-only meaning. Daily/monthly/yearly charts display gross, issued, cancelled, and net series; receipt count remains a count series. New visible strings use translation keys.

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Test valid/missing/malformed additive fields, negative net, cross-period series, gross preservation, and formatter output in Node.
- [ ] Test rendered cards/labels/ARIA and old receipt links/count IDs in PHP.
- [ ] Run both new test files; expect RED.
- [ ] Implement module and Blade integration; rerun and expect exit code 0.
- [ ] Run the existing Affiliate analytics JavaScript test to confirm shared browser conventions remain intact.

## Task 25: Add localization parity

**Files:**

- Create: `lang/en/credit-note.php`
- Create: `lang/th/credit-note.php`
- Modify: all Credit Note controllers/views/JavaScript bootstrap data created in Tasks 17–24 to use these keys
- Create: `tests/Unit/CreditNote/CreditNoteTranslationStructureTest.php`

- **Consumes:** Every visible/status/reason/action/error/print/accessibility string introduced by the feature.
- **Produces:** Matching locale-first translation trees and placeholder parity.
- **Checkpoint:** Serialized field names and error codes remain untranslated.

Namespaces cover navigation, statuses, reasons, fields, validation, errors, actions, register, document, reporting, and accessibility. The Thai document heading remains `ใบลดหนี้ / CREDIT NOTE`; English source documents and comments remain English. Pass translated strings to JavaScript through escaped JSON script data, never executable string interpolation.

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Recursively compare leaf keys and placeholders across locales; assert all approved reason codes and stable error codes have safe messages.
- [ ] Search Credit Note views/controllers for newly introduced hard-coded user-facing strings.
- [ ] Run the translation test; expect RED before files are complete.
- [ ] Add keys and replace visible literals; rerun to GREEN.

## Task 26: Add observability, source audit, and reconciliation commands

**Files:**

- Create: `app/Domain/CreditNote/Services/CreditNoteObservabilityService.php`
- Create: `app/Domain/CreditNote/Services/CreditNoteSourceAuditService.php`
- Create: `app/Domain/CreditNote/Services/CreditNoteReconciliationService.php`
- Create: `app/Console/Commands/CreditNote/AuditCreditNoteSourcesCommand.php`
- Create: `app/Console/Commands/CreditNote/ReconcileCreditNotesCommand.php`
- Modify: `app/Domain/Affiliate/Services/AffiliateReconciliationService.php`
- Create: `tests/Feature/CreditNote/CreditNoteObservabilityTest.php`
- Create: `tests/Feature/CreditNote/CreditNoteSourceAuditCommandTest.php`
- Create: `tests/Feature/CreditNote/CreditNoteReconciliationCommandTest.php`
- Modify: `tests/Feature/Affiliate/AffiliateAdminFinanceTest.php`

- **Consumes:** All persistence/effect tables and existing structured-log/Affiliate reconciliation conventions.
- **Produces:** PII-safe operation signals, read-only bounded pre-enable audit, and non-repairing reconciliation with non-zero mismatch exit.
- **Checkpoint:** Commands never mutate financial evidence or repair source data.

Commands:

```text
credit-notes:audit-sources {--batch=500} {--json}
credit-notes:reconcile {--batch=100} {--json}
```

Reject batches outside 1–1000. Audit counts line types, unknown lines, missing metadata/number/date/details, source-total mismatches, and issuer config validity. Reconciliation checks header/item totals, Issue/Cancellation posting cardinality and exact values, non-negative remaining lines, number uniqueness, Affiliate adjustment sum versus `reversed_minor`, and wallet ledger projection. Process ascending IDs in chunks; emit only IDs/counts/codes/amounts and correlation, no buyer/source text.

Add Affiliate commission reconciliation as set-based sums of external reversal amounts plus net Credit Note commission deltas. Open actionable reconciliation issues through the existing service; do not auto-resolve or adjust.

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Test success, every mismatch code, batch bounds, non-zero exit, JSON shape, no writes, PII sanitization, and Affiliate combined evidence.
- [ ] Run the four affected test files; expect RED for new behavior.
- [ ] Implement observability/audit, then reconciliation, rerunning after each.
- [ ] Run commands on the PHPUnit fixture database and assert deterministic exit codes.

## Task 27: Verify MySQL concurrency and canonical lock ordering

**Files:**

- Create: `tests/Feature/CreditNote/CreditNoteConcurrencyTest.php`
- Create: `tests/Feature/CreditNote/Fixtures/credit-note-concurrency-worker.php`
- Modify: `.env.example` to add guarded concurrency variables if Task 1 did not add them

- **Consumes:** CreateDraft, Issue, number allocation, Affiliate wallet locking, named unique constraints, and established process-worker safety patterns.
- **Produces:** Staging evidence for concurrent Draft/Issue/partial-credit/number/wallet behavior.
- **Checkpoint:** Default SQLite execution explicitly skips row-lock tests.

Use `DatabaseMigrations`, two PHP worker processes, a temporary barrier, and these opt-ins:

```text
CREDIT_NOTE_RUN_CONCURRENCY_TESTS=false
CREDIT_NOTE_CONCURRENCY_DATABASE=
```

Skip unless driver is MySQL, app environment is not production, the run flag is true, the configured database exactly equals the approved isolated database, and its name is not production-like. Never accept an implicit target. Pass connection values to workers without printing them and clean processes/barriers in `finally`.

Scenarios:

1. Two `CreateDraft` calls produce one Draft and one conflict.
2. Two Issue retries with the same key return one number/posting/event.
3. Two different partial Issues cannot over-credit one receipt.
4. First sequence-row creation and later increment races produce unique consecutive numbers.
5. Two Affiliate wallet effects preserve projection/ledger consistency.
6. Injected deadlock retry produces no duplicate evidence.

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Add guarded tests/workers and run under default SQLite; expect explicit skips, not false passes.
- [ ] Run `C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --without-tty tests\Feature\CreditNote\CreditNoteConcurrencyTest.php`; expect the documented local skip.
- [ ] On an authorized isolated MySQL staging database, set both opt-ins and rerun; expect one Draft/one number/one posting/no over-credit/reconciled wallet.
- [ ] Omit or mismatch the database opt-in and confirm workers never start.

## Task 28: Run full verification and prepare a gated rollout record

**Files:**

- Modify: `docs/specs/2026-08-12-credit-note-system-design.md` only to record implementation verification status after checks pass
- Create: `docs/verification/2026-08-12-credit-note-system.md`

- **Consumes:** All prior tasks.
- **Produces:** Reproducible local/staging evidence and an explicit accounting/operations handoff.
- **Checkpoint:** Feature and financial-action flags remain false; no production permission is assigned.

Run focused suites first:

```powershell
C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --without-tty tests\Unit\CreditNote tests\Feature\CreditNote
node --test tests/JavaScript/credit-note-editor.test.mjs tests/JavaScript/credit-note-revenue.test.mjs
```

Run preserved Receipt/Affiliate/report regressions:

```powershell
C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --without-tty tests\Feature\Affiliate tests\Unit\Affiliate
C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --without-tty --filter=AffiliateFinancialSourceLockTest
C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --without-tty --filter=AffiliateRefundServiceTest
```

Run the complete repository suite and structural checks:

```powershell
C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan test --without-tty
C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan route:list --path=credit-notes --except-vendor -v
C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan credit-notes:audit-sources --json
C:\xampp\php\php.exe -c C:\xampp\php\php.ini artisan credit-notes:reconcile --json
git diff --check
```

In an authorized staging environment, run migrations against a production-shaped backup, run source audit, execute the opt-in MySQL concurrency file, run reconciliation, and have accounting inspect representative Draft/Issued/Cancelled print/PDF samples. Record command, timestamp, exit code, pass/fail/skip counts, route middleware, sanitized audit/reconciliation output, browser keyboard/zoom checks, and remaining unknowns in the verification document.

- [ ] Confirm all nine routes and exact permission middleware, with no DELETE route.
- [ ] Confirm old gross fields and receipt counts are unchanged.
- [ ] Confirm no floats occur in Credit Note PHP financial calculations and no public PDF file is created.
- [ ] Confirm every issued/cancelled/evidence model rejects prohibited updates/deletes.
- [ ] Confirm correlation logs and events contain no buyer PII or source text.
- [ ] Confirm migration audit has no unaccepted mismatch/unknown line relevant to Affiliate.
- [ ] Obtain explicit evidence for every production gate: approved issuer legal identity/Head Office/`00000`; historical 7 percent VAT; financially relevant `line_type` backfill; synthetic service-charge tax treatment; reason labels/usage; delivered-document cancellation procedure; representative Draft/Issued/Cancelled print/PDF samples; permission assignments; zero unaccepted migration-audit mismatches; passing automated tests; passing MySQL concurrency tests; and passing reconciliation.
- [ ] Only after all gates pass may an authorized operator populate issuer config, assign permissions, enable the module, and then enable financial actions in a separately approved deployment operation.

## Requirement Traceability

| Approved requirement | Plan coverage |
| --- | --- |
| Separate one-receipt Credit Note domain | Tasks 6–12, 16–21 |
| Partial/full source-line credit | Tasks 3, 8–9, 11, 19 |
| Draft → Issued → Cancelled; no hard delete | Tasks 6–7, 9, 11–12 |
| Number only at Issue, centre/month safe sequence | Tasks 7, 10–11, 27 |
| Original receipt unchanged and derived credit status | Tasks 8, 13, 18 |
| Formal buyer/issuer/original/corrected/difference/VAT evidence | Tasks 1–3, 9, 11, 19–20 |
| Historical 7 percent metadata and deterministic classification | Tasks 4–5, 26 |
| Synthetic service charge without receipt mutation | Tasks 3, 8, 19–20 |
| Source snapshot/hash and incomplete-source blocks | Tasks 8–9, 11, 26 |
| One active Draft and sequential partial notes | Tasks 6, 9, 11–12, 27 |
| Exact satang, fixed quantity, half-up VAT | Tasks 2–3, 9, 11 |
| Tuition-only Affiliate reversal/restoration without double use | Tasks 14–15, 26–27 |
| Six permissions and centre scope | Tasks 16–21 |
| Print/PDF only; no public persistence | Task 20 |
| Register/export | Tasks 18, 21 |
| Gross/Issue/Cancellation/Net reporting with compatibility | Tasks 22–24 |
| Stable failures, CSRF, escaping, accessibility | Tasks 17–20, 25 |
| Audit, logs, metrics, reconciliation | Tasks 7, 9, 11–12, 26 |
| Safe additive rollout and mandatory accounting gates | Tasks 1, 4, 6–7, 14, 16, 26–28 |

## Approval and Execution Handoff

This plan changes documentation only. It does not authorize implementation, a worktree, commits, migrations, environment changes, permission assignment, or deployment.

After explicit plan approval:

1. use `git-worktrees` to create an isolated implementation worktree;
2. use `executing-plans` to execute tasks in dependency order;
3. use `test-driven-development` for every behavior change;
4. stop at any accounting/configuration/data/concurrency gate that lacks evidence; and
5. use `verification-before-completion` before reporting the feature complete.
