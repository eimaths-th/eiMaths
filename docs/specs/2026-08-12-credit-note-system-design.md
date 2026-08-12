# Credit Note System Design

- Date: 2026-08-12
- Status: Approved
- Scope: Credit note MVP for the existing receipt, reporting, and Affiliate commission systems
- Design approval: Approved in conversation on 2026-08-12
- Specification approval: Approved in conversation on 2026-08-12

## 1. Purpose

This specification defines a tax credit note system for the existing Laravel application. A credit note is a separate financial adjustment document linked to one original receipt. It preserves the original receipt, reduces reportable revenue through append-only postings, and adjusts Affiliate commission only for credited tuition.

The feature must support partial and full credits, immutable issued documents, auditable cancellation, printable and PDF documents, existing historical receipts that pass eligibility checks, centre-scoped authorization, and exact monetary calculations.

This document specifies behavior and boundaries. It is not an implementation plan and does not authorize implementation before written specification approval.

## 2. Evidence and Authoritative References

### 2.1 Repository evidence

The following facts were verified in the repository:

1. The application uses Laravel 10, PHP 8.1 or later, Blade, Bootstrap/AdminLTE, jQuery, DataTables, DOMPDF, Spatie permissions, and Laravel Excel.
2. Production configuration uses MySQL. PHPUnit uses SQLite in-memory.
3. Receipts are stored in the receipts table and line items are stored in receipt_details.
4. Paid-order processing creates receipts and standard receipt lines.
5. Receipt numbers are generated per centre, calendar year, and month.
6. Receipt presentation supports list, edit, print, and PDF operations.
7. Current receipt tax presentation treats selected lines as VAT-inclusive at a hard-coded rate of 7 percent and calculates VAT by dividing the VAT-inclusive amount by 107.
8. The current receipt total adds the order service charge outside receipt_details.
9. Current branch and dashboard revenue queries aggregate receipts.total_fee directly.
10. Affiliate commissions use receipts as immutable financial source evidence.
11. Existing Affiliate external-refund reversals are append-only and can affect the Affiliate wallet ledger.
12. receipt_details has a free-form description and a tax flag but no structured line type or persisted VAT rate.
13. receipts.billing_details is a free-form text snapshot. Contact.defaultBillingDetails() only composes a parent name and telephone number and does not provide structured buyer tax fields.
14. The repository has English and Thai locale directories.
15. The repository search performed during design did not find an existing credit note module or direct ReceiptController feature tests. This search result is not proof that no indirect coverage exists.

Relevant repository locations include:

- app/Http/Controllers/ReceiptController.php
- app/Http/Controllers/OrderController.php
- app/Models/Receipt.php
- app/Models/receipt_details.php
- app/Models/ReceiptRunningNumber.php
- app/Domain/Affiliate/Services/AffiliateCommissionService.php
- app/Domain/Affiliate/Models/AffiliateCommissionReversal.php
- app/Http/Controllers/DashboardController.php
- resources/views/templates/print_receipt.blade.php
- database/migrations/2023_09_12_105620_create_receipts_table.php
- database/migrations/2024_03_21_155235_create_receipt_details_table.php
- database/migrations/2026_07_31_000000_add_billing_details_to_receipts_table.php
- phpunit.xml

### 2.2 Tax references

The document design follows the information requirements described by the Thai Revenue Department:

- Revenue Department Order Por. 80/2542:
  https://www.rd.go.th/3574.html
- Director-General Notification on VAT No. 195:
  https://www.rd.go.th/48231.html

The verified requirements include a prominent credit note label, issuer identity, buyer identity, issue date, original tax invoice reference, original value, corrected value, difference, returned VAT, and a concise issue reason. The Revenue Department guidance also assigns the VAT effect to the tax month in which the credit note is issued.

This software specification does not replace accounting or legal advice. Production enablement requires the accounting sign-offs defined in Section 31.

## 3. Approved Product Decisions

The following decisions are approved:

1. A credit note is a linked adjustment document, not the cash-refund workflow.
2. One credit note references exactly one original receipt.
3. A credit note copies source receipt lines and permits partial or full credit by quantity and amount.
4. The lifecycle is Draft, Issued, and Cancelled.
5. An issued document is immutable.
6. The original receipt is never reduced, replaced, or deleted.
7. Receipt credit status is derived as Not Credited, Partially Credited, or Fully Credited.
8. An issued credit note automatically adjusts Affiliate commission for tuition only.
9. Cancelling an issued credit note restores only the Affiliate effect created by that credit note.
10. A cancellation posts its financial reversal in the cancellation month and never rewrites the issue month.
11. The document is a formal tax credit note intended to follow Revenue Code section 86/10 requirements.
12. Buyer details are copied as a snapshot from the receipt and may be corrected only while the document is Draft.
13. The user selects a controlled reason code and must enter reason details.
14. Numbers use the format {CENTRE}CN{YY}{MM}{NNN}.
15. Number sequences are independent per centre and month.
16. Credit note permissions are independent from receipt permissions.
17. Reports expose Gross Revenue, Issued Credit Notes, Cancelled Credit Notes, and Net Revenue.
18. Existing API gross fields retain their current meaning.
19. The issue date is the application date at Issue time. The adjustment event date is stored separately.
20. Historical receipts are eligible only when their source document and line data pass validation.
21. One receipt may have only one active Draft at a time.
22. One receipt may have multiple sequential issued partial credit notes.
23. Tax treatment and VAT rate are copied from the source and cannot be changed on the credit note.
24. All historical taxable receipt lines in scope use a confirmed 7 percent VAT rate.
25. The MVP supports print and PDF only. It excludes email delivery and e-Tax integration.
26. Every centre issues the document in the name of the same head office. Centre remains relevant to numbering, authorization, and reporting.
27. The credit note domain uses separate tables and services instead of reusing the receipts table as a second document type.
28. receipt_details gains a structured line_type.
29. A service charge is exposed as a synthetic creditable source line without modifying the original receipt.
30. No credit note data has a hard-delete operation.

## 4. Goals

The MVP must:

1. Create a Draft from an eligible receipt.
2. Support controlled partial and full credit at source-line level.
3. prevent cumulative credit from exceeding the original receipt or a source line.
4. Generate a concurrency-safe credit note number only at Issue time.
5. Persist immutable document, tax, buyer, issuer, and amount snapshots.
6. Produce a printable tax credit note and an inline PDF.
7. Preserve historical reporting through append-only financial postings.
8. Adjust Affiliate commission without double-reversing amounts already covered by external refunds.
9. Restore the exact credit-note Affiliate effect when the credit note is cancelled.
10. Preserve current gross API contracts while exposing net values.
11. Enforce permission and centre scope on every operation.
12. Provide structured audit evidence, correlation, reconciliation, and actionable failures.
13. Preserve all unrelated receipt, order, contact, report, and Affiliate behavior.

## 5. Non-Goals

The MVP does not include:

1. Cash refund initiation, approval, settlement, or bank/payment integration.
2. Email delivery.
3. e-Tax Invoice and e-Receipt submission.
4. A customer-facing credit note portal.
5. A credit note that references multiple receipts.
6. Debit notes.
7. A generalized financial-document platform.
8. Replacement or reissue of the original receipt.
9. Automatic repair or guessed reconstruction of incomplete historical receipt evidence.
10. Automatic assignment of Issue or Cancel permission to ordinary roles.
11. Hard deletion of Draft, Issued, or Cancelled documents.
12. Retroactive rewriting of prior-period revenue or VAT summaries.
13. Reinterpretation of current source tax treatment without accounting approval.

## 6. Approaches Considered

### 6.1 Dedicated Credit Note domain

This approach creates separate credit note tables, services, policies, requests, queries, views, and reporting postings linked to receipts.

Benefits:

- Preserves receipt semantics and existing query contracts.
- Keeps credit note lifecycle and immutability explicit.
- Limits changes to the legacy ReceiptController.
- Supports append-only reporting and Affiliate evidence.

Cost:

- Requires dedicated presentation and persistence.
- Requires a small amount of shared financial calculation infrastructure.

Decision: Approved.

### 6.2 Reuse receipts and receipt_details

This approach would add a parent receipt reference and new document types to receipts.

Benefits:

- Faster initial reuse of legacy screens and templates.

Risks:

- Existing report queries can accidentally count credit notes as receipts.
- The existing type field already has application meaning.
- Receipt status, order relationships, and Affiliate-source assumptions do not match the credit note lifecycle.
- Every receipt query would require new filters.

Decision: Rejected.

### 6.3 Refactor all financial documents into a common platform

This approach would replace or migrate current receipt and invoice structures into a generalized document model.

Benefits:

- Strong long-term normalization.

Risks:

- Broad production-data migration.
- High regression risk to receipts, reports, and Affiliate evidence.
- Materially exceeds the approved MVP.

Decision: Rejected.

## 7. Domain Boundary

The new domain is located under app/Domain/CreditNote.

The public application boundary consists of:

- CreditNoteCommandService
- CreditNoteQuery
- CreditNoteCalculator
- CreditNoteNumberGenerator
- CreditNoteReconciliationService
- AffiliateCreditNoteAdjustmentService

Controllers must remain thin. They:

1. Resolve the authenticated actor.
2. Authorize the resource and operation.
3. Validate input through Form Requests.
4. Supply an idempotency key and correlation ID.
5. Call one public service method.
6. Translate the result or domain exception into an HTTP response.

No controller, Blade template, export, or JavaScript module may directly mutate Credit Note domain persistence.

The Receipt domain remains the read-only source. The only approved Receipt persistence change is additive metadata on receipt_details for line classification and tax-rate snapshotting.

## 8. Lifecycle

### 8.1 States

Persisted status values are:

- draft
- issued
- cancelled

Application enum members use:

- DRAFT
- ISSUED
- CANCELLED

Allowed transitions are:

- Draft to Issued
- Draft to Cancelled
- Issued to Cancelled

Cancelled is terminal.

### 8.2 Draft behavior

A Draft:

- Has no credit note number.
- Has no financial posting.
- Has no Affiliate effect.
- May be edited by an authorized actor in the same centre.
- May provide a preview carrying a Draft watermark.
- Holds buyer, reason, event date, and selected source-line input.

Only one active Draft may exist for a receipt. The service enforces this under a receipt row lock.

### 8.3 Issued behavior

An Issued credit note:

- Has a unique number.
- Has issue_date and issued_at.
- Has immutable header, buyer, issuer, source, line, tax, and monetary snapshots.
- Has exactly one Issue financial posting.
- Has at most one Affiliate Issue adjustment row when an Affiliate commission exists.
- Can be printed and rendered to PDF.
- Cannot be edited or deleted.

### 8.4 Cancelled behavior

A Cancelled Draft:

- Retains its audit history.
- Has no number, posting, or Affiliate effect.

A Cancelled Issued document:

- Retains its number and all issued snapshots.
- Has cancellation date, actor, and reason.
- Has exactly one Cancellation financial posting in the cancellation period.
- Has an Affiliate Cancellation adjustment that restores the exact Issue adjustment, when applicable.
- Remains printable with a Cancelled watermark.
- Cannot return to Draft or Issued.

## 9. Receipt Eligibility

A receipt is eligible when all applicable conditions pass:

1. It exists and is visible to the actor's centre scope.
2. It has a non-empty receipt number.
3. It has a payment date.
4. It represents an issued/paid source:
   - receipt.status is 1; or
   - the linked order status is 1 and the receipt has its number and date.
5. It has at least one receipt detail or a positive source service charge.
6. Every source line has a non-negative quantity, price, and discount.
7. Every source-line net total can be calculated exactly.
8. The detail total reconciles to receipts.total_fee within one satang.
9. The source Grand Total reconciles after adding the service charge.
10. It has remaining creditable value.
11. It has no existing Draft.
12. Its source data is not otherwise in a state that makes an exact snapshot impossible.

Historical zero-value lines may contain a `NULL` unit price in legacy data. When quantity and discount are present and the exact calculated line total is zero, the source reader normalizes that unit price to zero for the immutable snapshot. A missing unit price on a non-zero line remains ineligible.

Receipt type alone does not determine eligibility. Any current receipt type may be used if all evidence and reconciliation checks pass.

Incomplete historical receipts are not repaired or reconstructed by this feature. The user receives an actionable block reason.

## 10. Source Receipt Metadata

### 10.1 New receipt_details fields

Add the following backward-compatible fields:

| Field | Type | Rules |
| --- | --- | --- |
| line_type | string(32) | Required after backfill; defaults to unknown |
| tax_treatment | string(32) | Required after backfill |
| vat_rate_basis_points | unsigned integer | 700 for VAT-inclusive historical lines, 0 for non-taxable lines |

Keep the existing tax field unchanged for backward compatibility.

### 10.2 Line types

Application enum members and stored values are:

| Enum member | Stored value |
| --- | --- |
| TUITION | tuition |
| BOOK | book |
| DEPOSIT | deposit |
| REGISTRATION_FEE | registration_fee |
| EQUIPMENT | equipment |
| OTHER | other |
| SERVICE_CHARGE | service_charge |
| UNKNOWN | unknown |

All receipt creation and receipt-detail editing paths must persist line_type, tax_treatment, and vat_rate_basis_points.

### 10.3 Backfill mapping

Backfill only deterministic standard descriptions generated by the existing application:

| Existing description rule | line_type |
| --- | --- |
| Description begins with the existing Thai course-fee prefix | tuition |
| Existing standard book-fee description | book |
| Existing standard deposit description | deposit |
| Existing standard initial-registration description | registration_fee |
| Existing standard learning-equipment description | equipment |
| Anything else | unknown |

The migration or backfill command must use the exact current source literals from OrderController. It must not use fuzzy matching.

An unknown source line may be credited only when it cannot affect an existing Affiliate commission. If an Affiliate commission exists and a selected or financially relevant source line is unknown, Issue fails with CREDIT_NOTE_UNKNOWN_LINE_TYPE.

### 10.4 Tax treatment backfill

Based on the approved historical-rate decision:

- tax = 1 maps to vat_inclusive with 700 basis points.
- tax = 0 maps to vat_exempt with 0 basis points.
- The synthetic service charge maps to the source document's current separate, zero-VAT presentation as out_of_scope with 0 basis points.

The service-charge mapping preserves current behavior; accounting approval remains a production enablement gate.

## 11. Persistence

### 11.1 credit_notes

The credit_notes table stores the document header and immutable snapshots.

Required columns:

| Group | Columns |
| --- | --- |
| Identity | id, receipt_id, centre_id, status, number, currency |
| Reason | reason_code, reason_details, adjustment_event_date, late_issue_reason |
| Issue | issue_date, issued_at, issue_idempotency_key |
| Cancellation | cancellation_effective_date, cancelled_at, cancellation_reason, cancel_idempotency_key |
| Source integrity | source_snapshot_hash, source_receipt_updated_at |
| Original reference | original_receipt_number, original_receipt_date, original_billing_details_text |
| Buyer snapshot | buyer_name, buyer_address, buyer_tax_id, buyer_is_vat_registered, buyer_branch_type, buyer_branch_code |
| Issuer snapshot | issuer_name, issuer_address, issuer_tax_id, issuer_branch_type, issuer_branch_code |
| Original totals | original_taxable_base_minor, original_vat_minor, original_exempt_minor, original_out_of_scope_minor, original_grand_total_minor |
| Corrected totals | corrected_taxable_base_minor, corrected_vat_minor, corrected_exempt_minor, corrected_out_of_scope_minor, corrected_grand_total_minor |
| Difference totals | difference_taxable_base_minor, difference_vat_minor, difference_exempt_minor, difference_out_of_scope_minor, difference_grand_total_minor |
| Actors | created_by, updated_by, issued_by, cancelled_by |
| Concurrency | lock_version |
| Timestamps | created_at, updated_at |

Rules:

- receipt_id references receipts with restrict-on-delete.
- centre_id references departments with restrict-on-delete.
- number is nullable and globally unique.
- issue_idempotency_key and cancel_idempotency_key are nullable and independently unique.
- Currency is THB in the MVP.
- All monetary columns use integer minor units.
- No soft-delete or hard-delete column exists.
- User foreign keys use null-on-delete so historical evidence survives account removal.

### 11.2 Structured buyer fields

The application must not parse receipts.billing_details into tax fields.

At Draft creation:

- original_billing_details_text receives the exact receipt snapshot for evidence.
- buyer_name may be prefilled from the receipt/contact source.
- buyer_address, buyer_tax_id, and branch fields are prefilled only from structured data when such data exists.
- Missing structured fields remain explicit and visible.

Before Issue:

- buyer_name is required.
- buyer_address is required.
- buyer_tax_id must be canonicalized and validated for the applicable Thai identifier format.
- buyer_is_vat_registered is required.
- buyer_branch_type is required.
- buyer_branch_code is required and five digits when branch_type is branch.
- branch_code is 00000 when a VAT-registered buyer is marked as head office.
- branch fields may be not_applicable only when the buyer is not VAT registered and the accounting rules permit that document.

Editing these fields never updates Contact or Receipt.

### 11.3 Issuer configuration

Issuer identity is centralized in config/financial-documents.php and backed by environment-specific configuration:

- legal name
- legal address
- tax ID
- branch type
- branch code
- credit note feature enablement

All centres use head_office and branch code 00000. Centre code remains part of document numbering and report scope.

Issuer configuration is validated at Issue time and copied into the immutable credit note snapshot. Invalid or missing issuer configuration blocks Issue.

### 11.4 credit_note_items

Required columns:

| Group | Columns |
| --- | --- |
| Identity | id, credit_note_id, sort_order |
| Source | source_type, source_id, line_type |
| Display snapshot | description, unit |
| Tax snapshot | tax_treatment, vat_rate_basis_points |
| Original snapshot | original_quantity, original_unit_price_minor, original_discount_minor, original_line_total_minor |
| Credit input | credit_quantity, credit_amount_minor |
| Credit allocation | credit_taxable_base_minor, credit_vat_minor, credit_exempt_minor, credit_out_of_scope_minor |
| Timestamps | created_at, updated_at |

source_type values are:

- receipt_detail
- receipt_service_charge

For receipt_detail, source_id is receipt_details.id.

For receipt_service_charge:

- source_id is the linked order ID.
- line_type is service_charge.
- original quantity is 1.
- original line total is the snapshotted service charge.
- the original receipt is not modified.

Constraints:

- credit_note_id references credit_notes with cascade-on-delete at the database level, but application deletion is forbidden.
- A unique key prevents duplicate source_type and source_id within one credit note.
- Issued and Cancelled item models reject updates and deletes.
- Description and unit are plain text.

### 11.5 credit_note_running_numbers

Columns:

- id
- centre_id
- year
- month
- number
- created_at
- updated_at

Constraints:

- Unique key on centre_id, year, and month.
- centre_id references departments.

The generator locks the sequence row in the Issue transaction. The displayed sequence is padded to a minimum of three digits but may grow beyond 999 without collision.

### 11.6 credit_note_postings

This table is append-only and is the reporting source for credit-note financial effects.

Columns:

- id
- credit_note_id
- centre_id
- posting_type
- effective_date
- taxable_base_delta_minor
- vat_delta_minor
- exempt_delta_minor
- out_of_scope_delta_minor
- grand_total_delta_minor
- created_by
- correlation_id
- occurred_at
- created_at
- updated_at

posting_type values are:

- issue
- cancellation

Issue deltas are negative. Cancellation deltas are positive and exactly negate the Issue posting.

Constraints:

- Unique key on credit_note_id and posting_type.
- Posting models reject updates and deletes.
- centre_id and effective_date are indexed for aggregate reports.

### 11.7 credit_note_events

This table is an append-only audit trail.

Columns:

- id
- credit_note_id
- operation
- status_before
- status_after
- amount_minor
- actor_id
- actor_name_snapshot
- reason_code
- correlation_id
- metadata
- occurred_at
- created_at

metadata may include IDs, field names, versions, and non-sensitive state. It must not duplicate buyer name, address, tax ID, telephone number, or full billing snapshots.

Event models reject updates and deletes.

### 11.8 Affiliate credit note adjustments

Create affiliate_commission_credit_note_adjustments as append-only evidence distinct from external cash-refund evidence.

Columns:

- id
- affiliate_commission_id
- credit_note_id
- event_type
- tuition_reversal_delta_minor
- commission_reversal_delta_minor
- commission_status_before
- commission_status_after
- ledger_entry_id
- idempotency_key
- recorded_by
- correlation_id
- occurred_at
- created_at

event_type values are:

- issue
- cancellation

Positive reversal deltas consume remaining eligible tuition/commission. Negative deltas restore the Issue adjustment on cancellation.

Constraints:

- Unique key on credit_note_id and event_type.
- Unique idempotency_key.
- Records reject updates and deletes.
- Foreign keys use restrict-on-delete except recorded_by, which uses null-on-delete.

Add Affiliate ledger entry types:

- CREDIT_NOTE_COMMISSION_REVERSAL
- CREDIT_NOTE_COMMISSION_RESTORATION

Affiliate reconciliation must include these adjustments when verifying AffiliateCommission.reversed_minor and wallet balances.

## 12. Reason Codes

The MVP reason enum is controlled in source code and is not user-configurable.

Supported values:

| Enum member | Stored value | Intended use |
| --- | --- | --- |
| PRODUCT_PRICE_OVERSTATED | product_price_overstated | Product price was calculated above the correct value |
| SERVICE_PRICE_OVERSTATED | service_price_overstated | Service price was calculated above the correct value |
| PRODUCT_DEFECT_DAMAGE_OR_SHORTAGE | product_defect_damage_or_shortage | Product failed agreed conditions, was damaged, or was short |
| SERVICE_NONCONFORMITY_OR_SHORTAGE | service_nonconformity_or_shortage | Service failed agreed conditions or was short |
| PRODUCT_RETURN | product_return | Product was returned under an eligible condition |
| STATUTORY_COMPENSATION | statutory_compensation | Compensation or equivalent payment is legally owed |
| ADVANCE_DEPOSIT_OR_RESERVATION_RETURN | advance_deposit_or_reservation_return | Advance, deposit, reservation, or equivalent amount is returned |
| COMMERCIAL_RETURN_OR_EXCHANGE | commercial_return_or_exchange | Eligible commercial return or exchange |
| DEFECTIVE_SERVICE_TERMINATION | defective_service_termination | Service contract terminated for defective/nonconforming service |
| SERVICE_NOT_PROVIDED_TERMINATION | service_not_provided_termination | Service contract terminated because service was not provided |

Every reason requires reason_details. The document prints the selected reason label and concise details.

There is no generic Other reason in the MVP. A new legally supported reason requires an approved product/specification change and accounting review.

## 13. Exact Money and Tax Calculation

### 13.1 Numeric representation

- HTTP monetary input is accepted as validated decimal strings.
- Domain money uses signed 64-bit integer minor units.
- Database money uses BIGINT-compatible integer columns.
- Floating-point numbers are forbidden in financial domain calculations.
- Quantities use an exact fixed-scale decimal representation.

### 13.2 Source-line total

For a receipt detail:

    original_line_total =
        round_half_up(original_quantity * original_unit_price)
        - original_discount

All intermediate calculations use exact decimal or rational arithmetic before conversion to minor units.

The source detail total must equal receipts.total_fee within one satang.

The source Grand Total is:

    receipt detail total + synthetic service charge

### 13.3 Credit line

For every selected source line:

- credit_quantity must be positive when present.
- credit_quantity cannot exceed remaining quantity.
- credit_amount_minor must be greater than zero.
- credit_amount_minor cannot exceed remaining source-line value.
- credit_amount_minor is the authoritative financial value.
- quantity is descriptive and supports product/service evidence.

The UI may propose a proportional amount from quantity, but the server validates and persists the submitted amount.

### 13.4 VAT-inclusive calculation

For each VAT-rate group:

    vat_minor =
        round_half_up(
            vat_inclusive_credit_minor
            * vat_rate_basis_points
            / (10000 + vat_rate_basis_points)
        )

    taxable_base_minor =
        vat_inclusive_credit_minor - vat_minor

At 7 percent, vat_rate_basis_points is 700.

VAT is rounded at the document-rate-group level. If a persisted per-line allocation needs a one-satang residual, allocation follows stable credit_note_item.id order so recalculation is deterministic.

### 13.5 Original, Corrected, and Difference

The document stores:

- Original totals from the immutable receipt snapshot.
- Difference totals for this credit note.
- Corrected totals after all net credit-note postings through this Issue.

For each money category:

    corrected =
        original
        + cumulative issued negative deltas
        + cumulative cancellation positive deltas

The current Difference is always presented as a positive reduction on the document even though the reporting Issue posting is stored as a negative delta.

### 13.6 Remaining credit

Remaining source-line value is:

    original source-line value
    + net source-line issue/cancellation deltas

Remaining receipt value is the sum of all remaining source lines.

Issue rechecks every remaining amount while holding the source receipt lock. Client calculations never authorize a value.

## 14. Source Snapshot Integrity

CreateDraft computes a canonical SHA-256 source_snapshot_hash over:

- receipt identity
- receipt number and date
- relevant receipt header values
- billing_details
- linked order service charge
- ordered source detail IDs and their quantity, price, discount, description, unit, tax, line_type, tax_treatment, and VAT rate

Canonical serialization must have stable field order and decimal formatting.

Issue recomputes the hash under a lock. A mismatch returns CREDIT_NOTE_SOURCE_CHANGED and leaves the Draft unchanged.

No force-issue operation bypasses this check.

## 15. Command Workflows

### 15.1 CreateDraft

CreditNoteCommandService.CreateDraft performs:

1. Authorize Create for the receipt and centre.
2. Start a database transaction.
3. Lock the source receipt.
4. Evaluate eligibility and reconciliation.
5. Confirm no Draft exists.
6. Calculate remaining source values.
7. Create a Draft header with source hash and source reference snapshots.
8. Record a created audit event.
9. Commit and return the Draft.

It does not create item rows until the user saves selected lines.

### 15.2 UpdateDraft

CreditNoteCommandService.UpdateDraft performs:

1. Authorize Edit.
2. Validate the request allowlist.
3. Start a transaction.
4. Lock the credit note.
5. Require Draft status.
6. Require submitted lock_version to match.
7. Validate event date, reason, buyer fields, and selected source lines.
8. Recalculate all totals on the server.
9. Replace Draft item rows only.
10. Increment lock_version.
11. Record a sanitized updated event.
12. Commit.

No issued data can enter this path.

### 15.3 Issue

CreditNoteCommandService.Issue requires an idempotency key and performs:

1. Authorize Issue.
2. Resolve the receipt ID without mutating state.
3. Start a database transaction.
4. Lock the receipt.
5. Lock the credit note.
6. Return the prior successful result if the same idempotency key was already applied.
7. Reject reuse of the key with a different command payload.
8. Require Draft status and at least one positive item.
9. Recompute and validate source hash.
10. Recompute remaining values and reject over-credit.
11. Validate structured buyer and issuer snapshots.
12. Validate reason and event date.
13. Lock or create the centre/month number row.
14. Generate the unique number.
15. Calculate and persist immutable Original, Corrected, and Difference totals.
16. Set issue_date to the current Asia/Bangkok application date and issued_at to the current application time.
17. Set status to Issued.
18. Create the negative Issue posting.
19. Apply the Affiliate adjustment when applicable.
20. Record the Issue audit event.
21. Commit.

Failure rolls back the status, number increment, posting, Affiliate aggregate, wallet entry, and audit event.

### 15.4 Cancel

CreditNoteCommandService.Cancel requires a reason and idempotency key.

For a Draft:

1. Authorize Cancel.
2. Lock the receipt and Draft.
3. Set status to Cancelled.
4. Store cancellation actor, time, effective date, and reason.
5. Record an event.
6. Commit.

For an Issued document:

1. Authorize Cancel.
2. Start a transaction.
3. Lock the receipt and credit note.
4. Apply idempotency checks.
5. Require Issued status.
6. Create the positive Cancellation posting dated with the current Asia/Bangkok application date.
7. Append the Affiliate restoration for the exact Issue adjustment.
8. Set status to Cancelled without changing issued snapshots.
9. Record the cancellation event.
10. Commit.

Cancellation never deletes the Issue posting and never changes its effective date.

## 16. Event Date Rules

- adjustment_event_date is required.
- It cannot be in the future.
- It may be in the Issue month.
- It may be in the immediately preceding calendar month only when late_issue_reason is non-empty.
- An earlier date is rejected by the MVP.
- issue_date is not user-editable.
- cancellation_effective_date is the current application date at Cancel time.

All date boundaries use config('app.timezone'), verified as Asia/Bangkok.

## 17. Affiliate Adjustment

### 17.1 Applicable amount

Only credit note items with line_type tuition contribute to requested tuition reversal.

The remaining Affiliate-eligible tuition is:

    eligible_tuition_minor
    - sum(existing external refunded_tuition_minor)
    - net credit-note tuition reversal deltas

The applied tuition reversal is:

    min(requested credit-note tuition, remaining eligible tuition)

This prevents duplicate reversal when an external refund already consumed some or all eligible tuition.

### 17.2 Commission calculation

AffiliateCreditNoteAdjustmentService uses the existing Affiliate commission rate and commission calculation rules. It must not implement an independent percentage formula.

The applied commission reversal is capped at the remaining unreversed commission.

### 17.3 Commission status and wallet

The adjustment records status before and after.

- For Available commission, Issue appends a negative available-wallet ledger entry.
- Cancellation appends the exact positive restoration ledger entry.
- For Pending or Held commission, aggregate reversal is updated without an Available wallet delta.
- A fully reversed commission becomes Reversed.
- Cancellation restores a valid state based on its stored prior status and current lifecycle rules.

If no Affiliate commission exists and no attributed commission is applicable, credit note Issue succeeds and records Affiliate as not applicable in the credit note event.

If an Affiliate commission exists and the required adjustment fails, Issue fails atomically and the Draft remains retryable.

### 17.4 Append-only evidence

Existing external-refund reversal rows are never modified.

Credit note cancellation creates a new Affiliate adjustment row. It never updates or deletes the Issue adjustment.

## 18. Numbering

Format:

    {CENTRE}CN{YY}{MM}{NNN}

Example:

    RPCN2608001

Rules:

- CENTRE uses the existing Department code, uppercased.
- YY and MM use the Issue date in Asia/Bangkok.
- NNN is a minimum three-digit sequence.
- The sequence is scoped by centre, Gregorian year, and month.
- Drafts do not reserve a number.
- The sequence row is locked in the Issue transaction.
- A unique credit_notes.number constraint is the final collision guard.
- A rolled-back Issue does not consume the number.

## 19. HTTP Interfaces

All routes are inside the authenticated web group.

| Method | Path | Purpose |
| --- | --- | --- |
| GET | /credit-notes | List and filter credit notes |
| POST | /receipts/{receipt}/credit-notes | Create Draft |
| GET | /credit-notes/{credit_note} | Show document or Draft |
| PUT | /credit-notes/{credit_note} | Update Draft |
| POST | /credit-notes/{credit_note}/issue | Issue Draft |
| POST | /credit-notes/{credit_note}/cancel | Cancel Draft or Issued document |
| GET | /credit-notes/{credit_note}/print | Printable HTML |
| GET | /credit-notes/{credit_note}/pdf | Inline PDF |
| GET | /credit-notes/export | Filtered/selected Excel export |

There is no DELETE route.

Route names use:

- credit-notes.index
- credit-notes.store
- credit-notes.show
- credit-notes.update
- credit-notes.issue
- credit-notes.cancel
- credit-notes.print
- credit-notes.pdf
- credit-notes.export

Issue and Cancel require an X-Idempotency-Key or an equivalent validated hidden UUID generated for the browser form.

## 20. Response and Failure Contract

Successful JSON follows:

    {
      "success": true,
      "data": {},
      "correlation_id": "..."
    }

Failed JSON follows:

    {
      "success": false,
      "error": {
        "code": "CREDIT_NOTE_OVER_CREDIT",
        "message": "The requested credit exceeds the remaining amount.",
        "field_errors": {}
      },
      "correlation_id": "..."
    }

HTTP semantics:

| Status | Meaning |
| --- | --- |
| 200 | Successful read, update, Issue retry, or Cancel retry |
| 201 | Draft created |
| 403 | Permission denied |
| 404 | Resource missing or outside centre scope |
| 409 | Domain state, concurrency, source, amount, or idempotency conflict |
| 422 | Request validation failure |
| 500 | Unexpected internal failure |

Stable domain error codes include:

- CREDIT_NOTE_DISABLED
- CREDIT_NOTE_SOURCE_INCOMPLETE
- CREDIT_NOTE_SOURCE_TOTAL_MISMATCH
- CREDIT_NOTE_DRAFT_EXISTS
- CREDIT_NOTE_FULLY_CREDITED
- CREDIT_NOTE_STALE_DRAFT
- CREDIT_NOTE_SOURCE_CHANGED
- CREDIT_NOTE_UNKNOWN_LINE_TYPE
- CREDIT_NOTE_OVER_CREDIT
- CREDIT_NOTE_INVALID_STATE
- CREDIT_NOTE_INVALID_EVENT_DATE
- CREDIT_NOTE_BUYER_DETAILS_INCOMPLETE
- CREDIT_NOTE_ISSUER_CONFIGURATION_INVALID
- CREDIT_NOTE_AFFILIATE_ADJUSTMENT_FAILED
- CREDIT_NOTE_IDEMPOTENCY_CONFLICT
- CREDIT_NOTE_PDF_GENERATION_FAILED

User messages explain impact and corrective action. They never expose stack traces, SQL, paths, secrets, or internal class names.

## 21. Authorization

Create permissions:

- credit-note-list
- credit-note-show
- credit-note-create
- credit-note-edit
- credit-note-issue
- credit-note-cancel

Authorization is enforced by policy and middleware where appropriate.

Rules:

1. Every action requires its dedicated permission.
2. An actor without all-centre may access only credit notes whose centre_id matches the actor's department.
3. Source receipt authorization is checked before Draft creation.
4. Route-model binding does not bypass centre scope.
5. Cross-centre resources return 404 to avoid disclosure.
6. Issue and Cancel are distinct from Edit.
7. Export applies the same permission and centre filters as the list.
8. PDF and print require Show permission.

No UI visibility rule is accepted as authorization.

## 22. User Experience

### 22.1 Navigation and receipt actions

Add Credit Note Management to the authenticated navigation.

Receipt rows expose:

- Create Credit Note when eligible.
- View Credit Notes when at least one exists.
- Derived Not Credited, Partially Credited, or Fully Credited status.

Unavailable actions display an actionable reason and do not rely on color alone.

### 22.2 Credit note list

Filters include:

- Credit note number
- Original receipt number
- Issue date
- Adjustment event date
- Centre
- Customer/student
- Status
- Reason code

Columns include:

- Credit note number
- Original receipt
- Centre
- Customer/student
- Status
- Credit amount
- VAT adjustment
- Affiliate adjustment status
- Issue/cancellation dates
- Actions

The list uses server-side pagination and filtering.

### 22.3 Draft editor

Sections are:

1. Read-only source receipt summary.
2. Editable buyer tax snapshot.
3. Event date and controlled reason.
4. Source-line credit table.
5. Original, Corrected, and Difference summary.
6. Save Draft, Preview, Issue, and Cancel Draft actions.

Each source row shows:

- Original quantity/value
- Previously credited quantity/value
- Remaining quantity/value
- Current credit quantity/value
- Read-only tax treatment and VAT rate

Draft preview displays:

    DRAFT - NOT A TAX DOCUMENT

### 22.4 Issued and Cancelled view

Issued and Cancelled documents are read-only.

Actions include:

- Print
- PDF
- Cancel, only when Issued and authorized

The view includes an audit timeline. A Cancelled document displays:

    CANCELLED

### 22.5 Accessibility

- Use semantic headings, tables, labels, and buttons.
- Preserve logical keyboard order.
- Provide visible focus.
- Associate validation messages with fields.
- Announce save, Issue, Cancel, loading, and failure states through an aria-live region.
- Do not communicate status by color alone.
- Ensure controls have practical pointer and touch target sizes.
- Respect reduced-motion preferences.

## 23. Print and PDF

The document heading prominently displays:

    ใบลดหนี้ / CREDIT NOTE

The issued document includes:

1. Issuer legal name, address, tax ID, and Head Office designation.
2. Buyer name, address, tax ID, and branch designation when applicable.
3. Credit note number and date.
4. Original tax invoice/receipt number and date.
5. Credited item descriptions, quantities, units, and values.
6. Original value.
7. Corrected value.
8. Difference.
9. Returned VAT.
10. VAT-exempt/out-of-scope value.
11. Grand Total difference.
12. Controlled reason label and concise details.
13. Student/customer reference where available.
14. Prepared/authorized signature areas consistent with the current business document style.

All user-controlled text is escaped. The template does not render raw item HTML.

PDF is generated on demand and streamed inline. It is not persisted under public or file_store.

PDF generation failure does not modify document state.

## 24. Reporting and Export

### 24.1 Revenue formula

For a selected period:

    Net Revenue =
        Gross Receipt Revenue
        + sum(Credit Note posting Grand Total deltas)

Equivalent presentation:

    Net Revenue =
        Gross Receipt Revenue
        - Issued Credit Notes
        + Cancelled Credit Note Reversals

Credit note dates use credit_note_postings.effective_date, not the original receipt date.

### 24.2 Existing API compatibility

Existing fields retain gross semantics:

- grand_total
- total_revenue
- total_income

Add:

- issued_credit_note_total
- cancelled_credit_note_total
- net_revenue
- total_credit_notes_issued
- total_credit_notes_cancelled

Add equivalent values to each branch row where the endpoint returns branch data.

Existing receipt counts remain receipt counts.

### 24.3 Dashboard

Daily, monthly, and yearly Dashboard summaries display:

- Gross Revenue
- Issued Credit Notes
- Cancelled Credit Notes
- Net Revenue

The implementation must avoid replacing existing gross values silently.

### 24.4 Credit note register export

Export respects active list filters and selected IDs.

Columns include:

- Credit note number
- Status
- Issue date
- Cancellation date
- Adjustment event date
- Centre
- Original receipt number/date
- Buyer name and tax ID
- Reason
- Original value
- Corrected value
- Difference
- Taxable base difference
- VAT difference
- Exempt/out-of-scope difference
- Affiliate adjustment
- Created, issued, and cancelled actor IDs

Export requires authorization and centre scope.

## 25. Concurrency and Idempotency

### 25.1 Canonical lock order

Commands acquire locks in this order when applicable:

1. Receipt
2. Credit note
3. Number sequence
4. Affiliate commission
5. Affiliate wallet balance

All command implementations use the same order to reduce deadlock risk.

### 25.2 One Draft

CreateDraft locks the receipt before checking and creating a Draft. Concurrent requests serialize on the same receipt. The second request returns CREDIT_NOTE_DRAFT_EXISTS.

### 25.3 Over-credit

Issue calculates remaining source values only after locking the receipt. It includes all committed Issue and Cancellation evidence. A conflicting Issue fails without partial writes.

### 25.4 Idempotency

Issue and Cancel store separate unique keys.

- Repeating the same command and payload returns the existing result.
- Reusing a key for another resource or payload returns CREDIT_NOTE_IDEMPOTENCY_CONFLICT.
- No duplicate number, posting, audit Issue event, Affiliate adjustment, or ledger entry may be produced.

### 25.5 Database portability

Production row locking is verified with MySQL integration tests. SQLite feature tests verify state and uniqueness but are not treated as proof of MySQL lock behavior.

Do not introduce a MySQL-only partial unique index that cannot be represented in SQLite tests.

## 26. Security and Privacy

1. Use Form Requests with explicit allowlists.
2. Validate string length, date range, enum membership, decimal scale, positive quantities, and monetary bounds.
3. Use parameterized ORM/query-builder operations.
4. Apply CSRF protection to all mutations.
5. Encode every user-controlled value in HTML and PDF.
6. Store descriptions as plain text.
7. Stream PDFs through an authorized route.
8. Do not store generated PDFs publicly.
9. Do not log buyer names, addresses, tax IDs, telephone numbers, or full snapshots.
10. Validate and bound incoming correlation and idempotency keys.
11. Record Issue and Cancel as security-relevant financial events.
12. Prohibit hard delete in models, services, routes, and UI.
13. Protect mass assignment and keep state/total snapshot fields internal to the domain service.

## 27. Localization

Add matching translation keys under:

- lang/en/credit-note.php
- lang/th/credit-note.php

Translation namespaces cover:

- Navigation
- Status labels
- Reason labels
- Form labels
- Validation messages
- Domain errors
- User actions
- Print labels
- Accessibility announcements

Serialized API field names and error codes remain stable and are not translated.

## 28. Observability and Audit

### 28.1 Correlation

Credit note routes accept X-Correlation-ID when it matches the approved character and length allowlist; otherwise the application generates a UUID.

The response returns X-Correlation-ID and correlation_id.

### 28.2 Structured logs

Log fields:

- correlation_id
- actor_id
- centre_id
- receipt_id
- credit_note_id
- operation
- outcome
- error_code
- amount_minor when relevant
- duration_ms

Logs exclude buyer PII and document snapshots.

### 28.3 Metrics

Measure:

- Credit Note Issue count, success rate, and latency
- Credit Note Cancel count, success rate, and latency
- State conflicts
- Over-credit attempts
- Source-total mismatches
- Unknown line-type blocks
- Affiliate adjustment failures
- PDF failures
- Reconciliation mismatches

Alerts are reserved for repeated operational failures or reconciliation differences that require action.

### 28.4 Reconciliation

Provide a read-only batched command that verifies:

1. Header totals equal item totals.
2. Issue and Cancellation postings exactly match document snapshots.
3. Remaining source values never fall below zero.
4. Number uniqueness.
5. Issued documents have exactly one Issue posting.
6. Cancelled Issued documents have exactly one Issue and one Cancellation posting.
7. Affiliate adjustment deltas reconcile to AffiliateCommission.reversed_minor.
8. Wallet ledger effects reconcile when an Available commission was affected.

The command:

- Does not repair data.
- Emits structured discrepancy records/logs.
- Returns a non-zero exit code when differences exist.
- Processes records in bounded batches.

## 29. Failure and Recovery

### 29.1 Validation and domain conflicts

Validation failures leave persistence unchanged.

Domain conflicts return an actionable error:

- Source incomplete: repair the source evidence.
- Source total mismatch: reconcile the receipt before creating a credit note.
- Source changed: review and recreate/refresh the Draft.
- Stale Draft: reload the latest Draft.
- Draft exists: open the existing Draft.
- Over-credit: reduce the requested value.
- Unknown line type with Affiliate effect: classify the source through an authorized process.

There is no force or ignore option for these checks.

### 29.2 Unexpected database or Affiliate failure

Issue and Cancel are all-or-nothing transactions. A failure:

- Leaves the prior state intact.
- Does not consume a number.
- Does not leave a posting without a document state.
- Does not leave a wallet entry without Affiliate evidence.
- Returns a generic message and correlation ID.
- Emits an error log without sensitive details.

### 29.3 PDF failure

PDF failure:

- Does not modify the credit note.
- Returns CREDIT_NOTE_PDF_GENERATION_FAILED.
- Leaves print HTML available when safe.
- Records an operational signal.

## 30. Migration and Rollout

### 30.1 Additive schema deployment

Migrations:

1. Add receipt_details classification/tax fields.
2. Create credit_notes.
3. Create credit_note_items.
4. Create credit_note_running_numbers.
5. Create credit_note_postings.
6. Create credit_note_events.
7. Create affiliate_commission_credit_note_adjustments.
8. Add Affiliate ledger entry enum support.
9. Add permissions.

Do not rename or drop existing receipt fields.

### 30.2 Backfill

Backfill runs in bounded batches and:

- Classifies only exact standard receipt descriptions.
- Assigns unknown otherwise.
- Maps historical tax flags to the approved 7 percent snapshot.
- Does not modify receipt quantities, prices, discounts, totals, numbers, dates, or billing details.

### 30.3 Pre-enable audit

Before enabling Issue:

1. Count all receipt details by line_type.
2. Report unknown lines.
3. Report source-total mismatches.
4. Report receipts missing number/date/details.
5. Confirm issuer configuration.
6. Run Credit Note reconciliation on any seeded/test documents.
7. Review representative PDFs with accounting.

### 30.4 Permission rollout

Seed all six permissions.

Do not grant credit-note-issue or credit-note-cancel automatically to ordinary roles. Role assignment is an explicit administrative deployment action.

### 30.5 Feature enablement

The list and read-only views may be deployed before Issue is enabled.

Issue and Cancel remain disabled until Section 31 production gates pass.

### 30.6 Rollback

Before real documents exist, normal migration rollback is permitted in a controlled non-production environment.

After any real document exists:

- Disable the feature.
- Roll back application code only when necessary.
- Preserve all credit note, posting, audit, and Affiliate evidence.
- Do not drop financial tables.

## 31. Production Enablement Gates

All gates are mandatory:

1. Accounting confirms the head-office legal name, address, tax ID, Head Office label, and branch code.
2. Accounting confirms the approved historical 7 percent VAT assumption.
3. Accounting confirms line_type backfill results for financially relevant records.
4. Accounting confirms that the synthetic service charge retains the correct source tax treatment.
5. Accounting confirms the reason-code labels and usage.
6. Accounting confirms the operational procedure for cancelling a delivered/reported credit note.
7. Accounting approves representative Draft, Issued, and Cancelled print/PDF samples.
8. Permission assignments are reviewed.
9. Migration audit reports no unaccepted source mismatch.
10. Automated tests pass.
11. MySQL concurrency tests pass.
12. Reconciliation passes.

Failure of a tax-treatment or cancellation-procedure gate requires a specification revision. It is not resolved by bypassing validation.

## 32. Testing Strategy

### 32.1 Unit tests

Test:

- Decimal string validation
- Minor-unit conversion
- Quantity multiplication
- Half-up rounding
- VAT-inclusive extraction
- VAT-exempt and out-of-scope allocation
- Service charge synthetic line
- Original, Corrected, and Difference totals
- Multi-rate grouping
- One-satang allocation determinism
- Reason rules
- Event-date rules
- State transitions
- Source hash canonicalization
- Remaining-value calculation
- Idempotency payload matching

### 32.2 Persistence tests

Test:

- Foreign keys
- Unique number
- Unique Issue and Cancel idempotency keys
- Unique posting per type
- Unique Affiliate adjustment per event type
- Append-only posting, event, external reversal, and credit-note adjustment models
- Issued item/header immutability
- No hard-delete path

### 32.3 Draft feature tests

Test:

- Eligible source creates Draft
- Missing source evidence is blocked
- Source-total mismatch is blocked
- Existing Draft is returned as conflict
- Buyer snapshot does not update Contact or Receipt
- Draft update replaces only Draft items
- lock_version prevents lost update
- Unknown line classification behavior
- Service charge appears as a synthetic source line

### 32.4 Issue feature tests

Test:

- Required permission and centre scope
- Issue assigns one number
- Issue snapshots issuer and buyer fields
- Issue persists exact tax totals
- Issue creates one negative posting
- Issued header/items are immutable
- Duplicate idempotent request returns the same document
- Conflicting idempotency reuse fails
- Source-hash mismatch fails
- Partial and full credit
- Sequential partial credit notes
- Over-credit fails
- Number and posting roll back on failure

### 32.5 Cancellation feature tests

Test:

- Draft cancellation has no posting
- Issued cancellation retains Issue evidence
- Same-month cancellation posts a positive reversal
- Cross-month cancellation posts only in the cancellation month
- Repeated cancellation is idempotent
- Cancellation reason is required
- Cancelled documents are immutable
- A new Draft may be created after cancellation when value remains

### 32.6 Affiliate tests

Test:

- No Affiliate commission is not applicable
- Pending commission reversal
- Held commission reversal
- Available commission wallet reversal
- Fully reversed status
- External refund consumes part of eligible tuition
- External refund consumes all eligible tuition
- Credit note applies only remaining eligible tuition
- Credit note never treats book, deposit, registration, equipment, other, or service charge as tuition
- Cancellation restores exactly the Issue adjustment
- Cancellation does not restore an external refund
- Ledger and Affiliate aggregate rollback atomically on failure
- Reconciliation includes both external and credit-note evidence

### 32.7 Authorization tests

Test every route for:

- Missing permission
- Correct permission
- Same-centre access
- Cross-centre denial
- all-centre access
- Export scoping
- Print/PDF scoping

### 32.8 Reporting tests

Test:

- Existing gross fields are unchanged
- New Issue total
- New Cancellation total
- Net formula
- Daily grouping
- Monthly grouping
- Yearly grouping
- Branch grouping
- Issue and cancellation in different periods
- Receipt counts remain receipt counts
- Export respects filters and centre scope

### 32.9 Presentation and accessibility tests

Test:

- Required tax labels and values appear
- Draft watermark
- Cancelled watermark
- Issued document has no Draft watermark
- User text is escaped
- PDF is streamed and not publicly persisted
- Form labels and errors are associated
- Keyboard-accessible controls
- Focus behavior
- aria-live feedback
- Non-color status cues

### 32.10 Concurrency tests

SQLite feature tests cover deterministic state and uniqueness.

MySQL integration tests cover:

- Concurrent CreateDraft
- Concurrent Issue retry
- Concurrent partial credits on one receipt
- Number row creation race
- Number increment race
- Affiliate wallet locking
- Deadlock retry behavior where supported

## 33. Acceptance Criteria

The feature is accepted when:

1. An authorized user can create one Draft from an eligible receipt.
2. The Draft displays all source lines and a synthetic service charge when present.
3. The user can credit selected lines partially or fully without changing tax classification.
4. The server prevents source-line and receipt over-credit.
5. Issue generates one unique centre/month number.
6. The issued document contains the required issuer, buyer, original-reference, Original, Corrected, Difference, VAT, and reason information.
7. Issued data cannot be edited or deleted.
8. The original receipt remains unchanged.
9. Receipt credit status is correctly derived.
10. Reports retain Gross values and expose Issue, Cancellation, and Net values.
11. Cross-month cancellation preserves the Issue-period posting and creates a cancellation-period reversal.
12. Affiliate commission changes only for tuition and cannot be reversed twice.
13. Affiliate cancellation restoration affects only the associated credit note.
14. Users without permission or centre scope cannot access any route, print, PDF, or export.
15. Historical incomplete data is blocked with an actionable error.
16. PDF output is authorized, escaped, and not stored publicly.
17. Audit events and structured logs contain correlation context without buyer PII.
18. Migration, automated tests, MySQL concurrency tests, and reconciliation pass.
19. Every production enablement gate is signed off.

### 33.1 Demo implementation verification status (2026-08-12)

The approved implementation is present on the `demo` branch. Focused Credit Note tests, affected Affiliate feature/unit tests, JavaScript tests, PHP syntax checks, route inspection, and whitespace checks passed as recorded in `docs/verification/2026-08-12-credit-note-system.md`. The complete repository test command was attempted but did not produce a final result after more than twenty minutes, so a complete-suite pass is not claimed. The MySQL concurrency guard is present and its local run was explicitly skipped because the isolated opt-in was not enabled; the authorized worker scenarios remain a staging gate.

The Credit Note and financial-action feature flags remain disabled, issuer configuration remains blank, and no production permissions, migration, or deployment was performed. The acceptance criteria requiring accounting sign-off, production-shaped migration audit, MySQL concurrency evidence, reconciliation evidence, and final operational samples remain open gates.

## 34. Optional Improvements Deferred

The following are directly related but not included without separate authorization:

1. Email delivery.
2. e-Tax integration.
3. Multiple original receipts per credit note.
4. Customer self-service downloads.
5. Cash refund workflow integration.
6. Debit notes.
7. A generalized financial document framework.
8. Configurable reason-code administration.
9. Automated accounting-period close controls.
