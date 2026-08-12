<?php

namespace App\Domain\CreditNote\Services;

use App\Domain\CreditNote\Enums\TaxTreatment;
use App\Domain\CreditNote\ValueObjects\CreditNoteCalculation;
use App\Domain\CreditNote\ValueObjects\CreditNoteLineCalculationInput;
use App\Domain\CreditNote\ValueObjects\CreditNoteTotals;
use App\Domain\CreditNote\ValueObjects\ExactRatio;

final class CreditNoteCalculator
{
    public function Calculate(array $line_inputs): CreditNoteCalculation
    {
        $sorted_inputs = [];
        foreach ($line_inputs as $line_input) {
            if (!$line_input instanceof CreditNoteLineCalculationInput) {
                throw new \InvalidArgumentException('Credit Note calculation input is invalid.');
            }

            if (array_key_exists($line_input->item_id, $sorted_inputs)) {
                throw new \InvalidArgumentException('Credit Note item IDs must be unique.');
            }

            $this->AssertLineInput($line_input);
            $sorted_inputs[$line_input->item_id] = $line_input;
        }

        ksort($sorted_inputs, SORT_NUMERIC);
        $original_lines = [];
        $corrected_lines = [];
        $difference_lines = [];
        foreach ($sorted_inputs as $item_id => $line_input) {
            $remaining_amount_minor = $line_input->original_line_total_minor
                - $line_input->prior_net_credit_amount_minor
                - $line_input->credit_amount_minor;

            if ($remaining_amount_minor < 0) {
                throw new \InvalidArgumentException('Credit Note amount exceeds the remaining source value.');
            }

            $original_lines[$item_id] = [
                'amount_minor' => $line_input->original_line_total_minor,
                'tax_treatment' => $line_input->tax_treatment,
                'vat_rate_basis_points' => $line_input->vat_rate_basis_points,
            ];
            $corrected_lines[$item_id] = [
                'amount_minor' => $remaining_amount_minor,
                'tax_treatment' => $line_input->tax_treatment,
                'vat_rate_basis_points' => $line_input->vat_rate_basis_points,
            ];
            $difference_lines[$item_id] = [
                'amount_minor' => $line_input->credit_amount_minor,
                'tax_treatment' => $line_input->tax_treatment,
                'vat_rate_basis_points' => $line_input->vat_rate_basis_points,
            ];
        }

        $line_allocations = $this->BuildCreditAllocations($difference_lines);

        return new CreditNoteCalculation(
            $line_allocations,
            $this->BuildTotals($original_lines),
            $this->BuildTotals($corrected_lines),
            $this->BuildTotals($difference_lines),
        );
    }

    private function AssertLineInput(CreditNoteLineCalculationInput $line_input): void
    {
        if ($line_input->item_id < 1 || $line_input->original_line_total_minor < 0 || $line_input->credit_amount_minor < 0) {
            throw new \InvalidArgumentException('Credit Note line values must be non-negative.');
        }

        if ($line_input->prior_net_credit_amount_minor < 0) {
            throw new \InvalidArgumentException('Prior net credit cannot be negative.');
        }

        if (
            $line_input->tax_treatment === TaxTreatment::VAT_INCLUSIVE
            && $line_input->vat_rate_basis_points <= 0
        ) {
            throw new \InvalidArgumentException('VAT-inclusive lines require a positive VAT rate.');
        }

        if (
            $line_input->tax_treatment !== TaxTreatment::VAT_INCLUSIVE
            && $line_input->vat_rate_basis_points !== 0
        ) {
            throw new \InvalidArgumentException('Non-VAT lines must have a zero VAT rate.');
        }
    }

    private function BuildTotals(array $lines): CreditNoteTotals
    {
        $vat_groups = [];
        $exempt_minor = 0;
        $out_of_scope_minor = 0;

        foreach ($lines as $line) {
            $amount_minor = $line['amount_minor'];
            if ($line['tax_treatment'] === TaxTreatment::VAT_INCLUSIVE) {
                $rate = $line['vat_rate_basis_points'];
                $vat_groups[$rate] = ($vat_groups[$rate] ?? 0) + $amount_minor;
            } elseif ($line['tax_treatment'] === TaxTreatment::VAT_EXEMPT) {
                $exempt_minor += $amount_minor;
            } else {
                $out_of_scope_minor += $amount_minor;
            }
        }

        $taxable_base_minor = 0;
        $vat_minor = 0;
        foreach ($vat_groups as $rate => $vat_inclusive_minor) {
            $group_vat_minor = ExactRatio::MultiplyDivideHalfUp(
                $vat_inclusive_minor,
                (int) $rate,
                10000 + (int) $rate,
            );
            $vat_minor += $group_vat_minor;
            $taxable_base_minor += $vat_inclusive_minor - $group_vat_minor;
        }

        $grand_total_minor = $taxable_base_minor + $vat_minor + $exempt_minor + $out_of_scope_minor;

        return new CreditNoteTotals(
            $taxable_base_minor,
            $vat_minor,
            $exempt_minor,
            $out_of_scope_minor,
            $grand_total_minor,
        );
    }

    private function BuildCreditAllocations(array $lines): array
    {
        $allocations = [];
        $vat_groups = [];
        foreach ($lines as $item_id => $line) {
            $allocations[$item_id] = [
                'credit_amount_minor' => $line['amount_minor'],
                'credit_taxable_base_minor' => 0,
                'credit_vat_minor' => 0,
                'credit_exempt_minor' => 0,
                'credit_out_of_scope_minor' => 0,
            ];
            if ($line['tax_treatment'] === TaxTreatment::VAT_INCLUSIVE) {
                $rate = $line['vat_rate_basis_points'];
                $vat_groups[$rate][] = $item_id;
            } elseif ($line['tax_treatment'] === TaxTreatment::VAT_EXEMPT) {
                $allocations[$item_id]['credit_exempt_minor'] = $line['amount_minor'];
            } else {
                $allocations[$item_id]['credit_out_of_scope_minor'] = $line['amount_minor'];
            }
        }

        foreach ($vat_groups as $rate => $item_ids) {
            $group_total_minor = 0;
            $provisional_vat_minor = 0;
            foreach ($item_ids as $item_id) {
                $amount_minor = $lines[$item_id]['amount_minor'];
                $group_total_minor += $amount_minor;
                $provisional_vat = ExactRatio::MultiplyDivideHalfUp($amount_minor, (int) $rate, 10000 + (int) $rate);
                $allocations[$item_id]['credit_vat_minor'] = $provisional_vat;
                $provisional_vat_minor += $provisional_vat;
            }

            $group_vat_minor = ExactRatio::MultiplyDivideHalfUp($group_total_minor, (int) $rate, 10000 + (int) $rate);
            $residual_minor = $group_vat_minor - $provisional_vat_minor;
            foreach ($item_ids as $item_id) {
                if ($residual_minor === 0) {
                    break;
                }

                $adjustment_minor = $residual_minor > 0 ? 1 : -1;
                $allocations[$item_id]['credit_vat_minor'] += $adjustment_minor;
                $residual_minor -= $adjustment_minor;
            }

            foreach ($item_ids as $item_id) {
                $allocations[$item_id]['credit_taxable_base_minor'] =
                    $lines[$item_id]['amount_minor'] - $allocations[$item_id]['credit_vat_minor'];
            }
        }

        return $allocations;
    }
}
