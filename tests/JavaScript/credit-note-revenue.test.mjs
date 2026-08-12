import test from 'node:test';
import assert from 'node:assert/strict';
import { BuildRevenueSeries, FormatRevenueAmount, NormalizeRevenueRows } from '../../public/js/credit-note-revenue.js';

test('normalizes additive fields while preserving gross values', () => {
  const rows = NormalizeRevenueRows([{ payment_date: '2026-08-12', total_fee_sum: '100.00', issued_credit_note_total: '10.00', cancelled_credit_note_total: '2.00', net_revenue: '92.00', total_transactions: 3 }]);
  assert.deepEqual(rows[0], { key: '2026-08-12', gross: '100.00', issued: '10.00', cancelled: '2.00', net: '92.00', receipt_count: 3 });
});

test('builds gross, issue, cancellation, net, and receipt-count series', () => {
  const series = BuildRevenueSeries([{ month: '2026-08', total_fee_sum: '100', issued_credit_note_total: '10.00', cancelled_credit_note_total: '0.00', net_revenue: '90.00', total_transactions: 1 }]);
  assert.deepEqual(series.labels, ['2026-08']);
  assert.deepEqual(series.issued, ['10.00']);
  assert.deepEqual(series.net, ['90.00']);
});

test('malformed amounts are safely formatted as zero', () => {
  assert.equal(FormatRevenueAmount('bad'), '0.00');
  assert.equal(FormatRevenueAmount('-1.20'), '-1.20');
});
