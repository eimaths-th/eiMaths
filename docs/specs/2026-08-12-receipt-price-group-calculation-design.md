# Receipt Price Group Calculation Design

- Date: 2026-08-12
- Status: Approved
- Scope: Correct Receipt line generation for orders containing multiple tuition or book prices
- Design approval: Approved in conversation on 2026-08-12
- Specification approval: Approved in conversation on 2026-08-12

This specification defines the behavior and boundaries for correcting Receipt line calculation. It is not an implementation plan and does not authorize code or database changes until the implementation plan is explicitly approved.

## 1. Purpose

The current Receipt generation logic applies the maximum tuition price and maximum book price to every source row. This produces an incorrect Receipt when one order contains more than one price tier.

The change will generate Receipt lines from the actual `order_detail_lists` price groups. New Receipts and Receipt detail regeneration will use the same calculation. Existing demo data will not be repaired in this scope.

## 2. Verified Evidence

The following facts were verified against the repository and local database:

1. Receipt `RP2608015` stores `receipts.total_fee = 30,950.00` and its linked order stores `orders.total_price = 30,950.00`.
2. Its five source rows contain tuition prices `3,000.00, 4,000.00, 4,000.00, 4,000.00, 3,000.00` and book prices `1,500.00, 2,900.00, 2,900.00, 2,900.00, 1,500.00`.
3. The source totals are tuition `18,000.00`, books `11,700.00`, registration `750.00`, and equipment `500.00`, for a total of `30,950.00`.
4. The existing maximum-price formula produces `4,000.00 * 5 + 2,900.00 * 5 + 750.00 + 500.00 = 35,750.00`.
5. `OrderController` and `ReceiptController::gen_receipt_data` both contain the maximum-price calculation.
6. The Affiliate commission for this order uses the source tuition rows and records eligible tuition of `18,000.00` and commission of `2,700.00`. Affiliate commission calculation is not the source of the Receipt mismatch.
7. Other visible demo Receipts with one effective price group currently reconcile correctly. This is evidence that the defect is conditional on multiple price groups, not proof that all historical data is correct.

Relevant locations:

- `app/Http/Controllers/OrderController.php`
- `app/Http/Controllers/ReceiptController.php`
- `app/Models/OrderDetailList.php`
- `app/Models/receipt_details.php`
- `app/Domain/Affiliate/Services/AffiliateCommissionCalculator.php`
- `resources/views/templates/edit_receipt.blade.php`

## 3. Goals

The implementation must:

1. Generate Receipt lines using actual tuition and book unit prices.
2. Group equal prices into compact lines while preserving the source order of the first group occurrence.
3. Keep the final Receipt total equal to the stored order and Receipt total.
4. Preserve current VAT, line type, fee, and service-charge semantics.
5. Use one calculation boundary for both new Receipt generation and Receipt detail regeneration.
6. Preserve Affiliate commission calculation and Affiliate source evidence.
7. Add regression coverage for multiple price groups, single price groups, discounts, and both generation paths.
8. Avoid changing existing demo data, Receipt totals, order totals, or commission records.

## 4. Non-Goals

This change does not include:

1. Repairing `RP2608014`, `RP2608015`, or any other existing Receipt data.
2. Changing Affiliate commission rates, eligible tuition, source items, release behavior, or wallet entries.
3. Changing Receipt list, edit, print, or PDF layout beyond the natural addition of correctly grouped lines.
4. Adding a historical Receipt repair command or bypassing the financial source lock.
5. Adding a new database table or migration.
6. Changing the existing business treatment of service charges.
7. Reconstructing incomplete historical Receipt details.

## 5. Approved Design

### 5.1 Shared calculation service

Create a focused financial-domain service responsible for building Receipt detail drafts from an order:

```php
BuildForOrder(Order $order): Collection
```

The service is the public calculation boundary. Controllers remain responsible for authorization, request handling, persistence, and response formatting. The service must not read request fields or mutate HTTP responses.

### 5.2 Source rows and ordering

The service reads `order_detail_lists` for the order, ordered by ascending `id`.

Each source row contributes:

- `price` to the tuition groups
- `bprice` to the book groups
- one unit of quantity for each corresponding group

Missing or invalid source rows are a calculation failure. The service must not fall back to the maximum-price formula.

### 5.3 Price grouping

Tuition rows are grouped by normalized monetary `price`. Book rows are grouped by normalized monetary `bprice`.

For each group:

- `quantity` is the number of source rows in the group.
- `price` is the group's unit price.
- The group order is the order in which the first source row for that price appears.
- Tuition descriptions retain the current course description convention.
- Book descriptions retain the current book description convention.

For `RP2608015`, the generated groups are expected to be:

| Line category | Quantity | Unit price | Gross amount |
|---|---:|---:|---:|
| Tuition | 2 | 3,000.00 | 6,000.00 |
| Tuition | 3 | 4,000.00 | 12,000.00 |
| Book | 2 | 1,500.00 | 3,000.00 |
| Book | 3 | 2,900.00 | 8,700.00 |
| Registration fee | 1 | 750.00 | 750.00 |
| Equipment | 1 | 500.00 | 500.00 |

The expected total is `30,950.00`.

### 5.4 Discount allocation

The order-level tuition discount is allocated across tuition groups. The order-level book discount is allocated across book groups.

Allocation rules:

1. Perform the allocation in integer minor units.
2. Allocate proportionally to each group's gross amount.
3. Apply deterministic rounding so allocated discounts sum exactly to the source discount.
4. Never allocate more discount to a group than its gross amount.
5. Preserve the existing total after discounts.

For `RP2608015`, both discounts are zero, so no discount allocation is applied.

### 5.5 Tax and fee semantics

Generated line metadata remains consistent with the existing system:

- Tuition is VAT-inclusive at the existing 7 percent rate.
- Registration and equipment fees retain their current VAT-inclusive treatment.
- Books retain their current VAT-exempt treatment.
- Other service charges remain handled by the existing summary-level service-charge field.

The reconciliation invariant is:

```text
sum(receipt_detail line totals) + other service charge = receipts.total_fee
```

The service must calculate and validate this invariant before callers persist generated lines.

### 5.6 Integration points

The shared service is used by:

1. The paid-order Receipt generation path in `OrderController`.
2. `ReceiptController::gen_receipt_data` when Receipt details are regenerated.

`ReceiptController::show` continues to calculate presentation totals from persisted Receipt details. No new calculation is added to the Blade template or JavaScript.

The existing `receipts.total_fee` and `orders.total_price` remain the authoritative stored totals. The implementation must not overwrite them as a side effect of line grouping.

## 6. Failure Handling

The implementation must fail explicitly when:

- the order cannot be found;
- no source detail rows exist;
- a source price is invalid or negative;
- a discount cannot be allocated without violating the stated constraints; or
- the generated detail total does not reconcile with the stored order total.

No maximum-price fallback is permitted. Error handling must prevent silently persisting a second inconsistent calculation.

## 7. Security and Data Protection

1. No new public route or permission is required.
2. Existing order and Receipt authorization remains unchanged.
3. Existing Affiliate and Credit Note financial locks remain enforced.
4. The calculation service must not log billing details, student contact data, credentials, tokens, or payment evidence.
5. Diagnostic output may contain only Receipt/order identifiers and monetary reconciliation fields needed for troubleshooting.

## 8. Testing Requirements

### 8.1 Unit tests

Add focused tests for the shared calculation service:

1. Multiple tuition and book prices produce separate grouped lines and the exact expected total.
2. A single price group preserves the current quantity and amount semantics.
3. Proportional discount allocation sums exactly to the source discount after rounding.
4. VAT and exempt line metadata remain correct.
5. Missing rows, invalid prices, and reconciliation mismatches fail explicitly.

### 8.2 Feature tests

Cover both integration paths:

1. Paid order creation with multiple price groups persists correct Receipt details.
2. Receipt detail regeneration uses the same grouped calculation.
3. `receipts.total_fee` and `orders.total_price` remain unchanged.
4. Affiliate commission values and source items remain unchanged for an attributed order.
5. Existing demo rows are not modified by the implementation or test setup.

### 8.3 Acceptance criteria

The change is accepted when:

1. A new order with the `RP2608015` source price pattern generates a Receipt total of `30,950.00`, not `35,750.00`.
2. Equal-price orders continue to produce their existing totals.
3. Both Receipt generation paths use the shared calculation.
4. The Affiliate commission regression tests remain green.
5. No existing Receipt or Affiliate financial record is changed during deployment verification.

## 9. Deferred Improvements

The following observations are intentionally deferred:

1. Repairing existing incomplete or incorrectly generated demo Receipt details.
2. Making Receipt header creation and Receipt detail persistence one atomic transaction for all historical flows.
3. Adding a general-purpose historical Receipt reconciliation and repair tool.

## 10. Approval Gate

Implementation must not begin until this written specification is explicitly approved. After specification approval, the next required step is to create an implementation plan using the repository's `writing-plans` skill. The approved scope remains code and regression tests only; existing demo data is excluded.
