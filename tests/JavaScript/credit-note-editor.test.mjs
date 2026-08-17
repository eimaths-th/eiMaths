import test from 'node:test';
import assert from 'node:assert/strict';
import { DecimalToMinor, PreviewTotals } from '../../public/js/credit-note-editor.js';

test('DecimalToMinor uses exact decimal strings', () => {
  assert.equal(DecimalToMinor('12').toString(), '1200');
  assert.equal(DecimalToMinor('12.3').toString(), '1230');
  assert.equal(DecimalToMinor('12.34').toString(), '1234');
});

test('PreviewTotals sums BigInt values without floating point drift', () => {
  assert.equal(PreviewTotals(['0.10', '0.20', '90071992547409.91']).toString(), '9007199254741021');
});

test('invalid decimal and more than two fractional digits are rejected', () => {
  assert.throws(() => DecimalToMinor('-1.00'));
  assert.throws(() => DecimalToMinor('1.001'));
});
