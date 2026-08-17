<?php

namespace App\Domain\CreditNote\Services;

use App\Domain\CreditNote\Commands\IssueCreditNote;
use App\Domain\CreditNote\Commands\CancelCreditNote;
use App\Domain\CreditNote\Commands\UpdateCreditNoteDraft;
use App\Domain\CreditNote\Contracts\AffiliateCreditNoteAdjustment;
use App\Domain\CreditNote\Enums\CreditNoteEventOperation;
use App\Domain\CreditNote\Enums\CreditNotePostingType;
use App\Domain\CreditNote\Enums\CreditNoteSourceType;
use App\Domain\CreditNote\Enums\CreditNoteStatus;
use App\Domain\CreditNote\Exceptions\CreditNoteDomainException;
use App\Domain\CreditNote\Models\CreditNote;
use App\Domain\CreditNote\Models\CreditNotePosting;
use App\Domain\CreditNote\Rules\CreditNoteEventDateRule;
use App\Domain\CreditNote\ValueObjects\CreditNoteLineCalculationInput;
use App\Domain\CreditNote\ValueObjects\CreditNoteSourceLine;
use App\Domain\CreditNote\ValueObjects\FixedQuantity;
use App\Domain\CreditNote\ValueObjects\CreditNoteSourceSnapshot;
use App\Domain\CreditNote\ValueObjects\ThaiTaxIdentifier;
use App\Models\Receipt;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

final class CreditNoteCommandService
{
    public function __construct(
        private readonly CreditNoteSourceReader $source_reader,
        private readonly CreditNoteSourceHasher $source_hasher,
        private readonly CreditNoteEligibilityService $eligibility_service,
        private readonly CreditNoteEventRecorder $event_recorder,
        private readonly CreditNoteCalculator $calculator,
        private readonly CreditNoteNumberGenerator $number_generator,
        private readonly ?AffiliateCreditNoteAdjustment $affiliate_adjustment = null,
    ) {
    }

    public function CreateDraft(Receipt $receipt, User $actor, string $correlation_id): CreditNote
    {
        return DB::transaction(function () use ($receipt, $actor, $correlation_id): CreditNote {
            $locked_receipt = Receipt::query()->whereKey($receipt->id)->lockForUpdate()->firstOrFail();
            $snapshot = $this->source_reader->Read($locked_receipt);
            $this->eligibility_service->AssertCanCreateDraft($locked_receipt, $snapshot);

            $source_hash = $this->source_hasher->Hash($snapshot);
            $source_calculation = $this->CalculateCreditNote($this->BuildFullCalculationInputs(
                $snapshot,
                [],
                $this->PriorNetCreditBySource($locked_receipt->id, 0),
            ));
            $contact = $locked_receipt->contact()->first();
            try {
                $credit_note = CreditNote::create([
                    'receipt_id' => $locked_receipt->id,
                    'centre_id' => $locked_receipt->centre,
                    'active_draft_receipt_id' => $locked_receipt->id,
                    'status' => CreditNoteStatus::DRAFT,
                    'currency' => 'THB',
                    'source_snapshot_hash' => $source_hash,
                    'source_receipt_updated_at' => $locked_receipt->updated_at,
                    'original_receipt_number' => $snapshot->receipt_number,
                    'original_receipt_date' => $snapshot->receipt_date !== '' ? $snapshot->receipt_date : null,
                    'original_billing_details_text' => $snapshot->billing_details,
                    'buyer_name' => $contact?->name,
                    'buyer_address' => $contact?->address,
                    'created_by' => $actor->id,
                    'updated_by' => $actor->id,
                    'lock_version' => 0,
                ]);
                $this->AssignTotals($credit_note, $source_calculation);
                $credit_note->save();
            } catch (QueryException $exception) {
                if ($this->IsUniqueConflict($exception)) {
                    throw CreditNoteDomainException::DraftExists();
                }
                throw $exception;
            }

            $this->event_recorder->Record(
                $credit_note,
                CreditNoteEventOperation::CREATED,
                null,
                CreditNoteStatus::DRAFT,
                0,
                $actor,
                null,
                $correlation_id,
                ['version' => 0],
            );

            return $credit_note->fresh();
        }, (int) config('financial-documents.credit_note.limits.transaction_attempts', 3));
    }

    public function UpdateDraft(
        CreditNote $credit_note,
        UpdateCreditNoteDraft $command,
        User $actor,
        string $correlation_id,
    ): CreditNote {
        return DB::transaction(function () use ($credit_note, $command, $actor, $correlation_id): CreditNote {
            $locked_receipt = Receipt::query()->whereKey($credit_note->receipt_id)->lockForUpdate()->firstOrFail();
            $locked_note = CreditNote::query()->whereKey($credit_note->id)->lockForUpdate()->firstOrFail();
            if ($locked_note->status !== CreditNoteStatus::DRAFT) {
                throw CreditNoteDomainException::InvalidState();
            }
            if ((int) $locked_note->lock_version !== $command->lock_version) {
                throw CreditNoteDomainException::StaleDraft();
            }

            $snapshot = $this->source_reader->Read($locked_receipt);
            $source_hash = $this->source_hasher->Hash($snapshot);
            if ((string) $locked_note->source_snapshot_hash !== $source_hash) {
                throw CreditNoteDomainException::SourceChanged();
            }

            $prior_net_credit = $this->PriorNetCreditBySource($locked_receipt->id, $locked_note->id);
            [$inputs, $selected_lines] = $this->BuildCalculationInputs($snapshot, $command->items, $prior_net_credit);
            $calculation = $this->CalculateCreditNote($inputs);
            $full_calculation = $this->CalculateCreditNote($this->BuildFullCalculationInputs($snapshot, $selected_lines, $prior_net_credit));

            foreach ($locked_note->Items()->get() as $existing_item) {
                $existing_item->delete();
            }
            foreach ($selected_lines as $sort_order => $selected_line) {
                $allocation = $calculation->LineAllocations()[$selected_line['calculation_item_id']];
                $locked_note->Items()->create([
                    'sort_order' => $sort_order + 1,
                    'source_type' => $selected_line['source_type'],
                    'source_id' => $selected_line['source_id'],
                    'line_type' => $selected_line['line']->line_type,
                    'description' => $selected_line['line']->description,
                    'unit' => $selected_line['line']->unit,
                    'tax_treatment' => $selected_line['line']->tax_treatment,
                    'vat_rate_basis_points' => $selected_line['line']->vat_rate_basis_points,
                    'original_quantity' => $selected_line['line']->original_quantity->Decimal(),
                    'original_unit_price_minor' => $selected_line['line']->original_unit_price_minor,
                    'original_discount_minor' => $selected_line['line']->original_discount_minor,
                    'original_line_total_minor' => $selected_line['line']->original_line_total_minor,
                    'credit_quantity' => $selected_line['credit_quantity'],
                    'credit_amount_minor' => $allocation['credit_amount_minor'],
                    'credit_taxable_base_minor' => $allocation['credit_taxable_base_minor'],
                    'credit_vat_minor' => $allocation['credit_vat_minor'],
                    'credit_exempt_minor' => $allocation['credit_exempt_minor'],
                    'credit_out_of_scope_minor' => $allocation['credit_out_of_scope_minor'],
                ]);
            }

            $this->AssignTotals($locked_note, $full_calculation);
            $locked_note->buyer_name = $command->buyer_fields['buyer_name'] ?? null;
            $locked_note->buyer_address = $command->buyer_fields['buyer_address'] ?? null;
            $locked_note->buyer_tax_id = $command->buyer_fields['buyer_tax_id'] ?? null;
            $locked_note->buyer_is_vat_registered = $command->buyer_fields['buyer_is_vat_registered'] ?? null;
            $locked_note->buyer_branch_type = $command->buyer_fields['buyer_branch_type'] ?? null;
            $locked_note->buyer_branch_code = $command->buyer_fields['buyer_branch_code'] ?? null;
            $locked_note->reason_code = $command->reason_code;
            $locked_note->reason_details = trim($command->reason_details);
            $locked_note->adjustment_event_date = $command->adjustment_event_date;
            $locked_note->late_issue_reason = $command->late_issue_reason === null ? null : trim($command->late_issue_reason);
            $locked_note->source_snapshot_hash = $source_hash;
            $locked_note->updated_by = $actor->id;
            $locked_note->lock_version++;
            $locked_note->save();

            $this->event_recorder->Record(
                $locked_note,
                CreditNoteEventOperation::UPDATED,
                CreditNoteStatus::DRAFT,
                CreditNoteStatus::DRAFT,
                $calculation->Difference()->grand_total_minor,
                $actor,
                $command->reason_code,
                $correlation_id,
                [
                    'version' => $locked_note->lock_version,
                    'item_ids' => array_map(static fn (array $line): int => $line['source_id'], $selected_lines),
                    'field_names' => ['buyer', 'reason', 'adjustment_event_date', 'items'],
                ],
            );

            return $locked_note->fresh();
        }, (int) config('financial-documents.credit_note.limits.transaction_attempts', 3));
    }

    public function Issue(
        CreditNote $credit_note,
        IssueCreditNote $command,
        User $actor,
        string $correlation_id,
    ): CreditNote {
        return DB::transaction(function () use ($credit_note, $command, $actor, $correlation_id): CreditNote {
            $locked_receipt = Receipt::query()->whereKey($credit_note->receipt_id)->lockForUpdate()->firstOrFail();
            $locked_note = CreditNote::query()->whereKey($credit_note->id)->lockForUpdate()->firstOrFail();
            $payload_hash = $this->HashPayload([
                'credit_note_id' => $locked_note->id,
                'idempotency_key' => $command->idempotency_key,
                'lock_version' => $command->lock_version,
            ]);

            if ($locked_note->issue_idempotency_key !== null) {
                if ($locked_note->issue_idempotency_key !== $command->idempotency_key || $locked_note->issue_payload_hash !== $payload_hash) {
                    throw CreditNoteDomainException::IdempotencyConflict();
                }
                if ($locked_note->status === CreditNoteStatus::ISSUED || $locked_note->status === CreditNoteStatus::CANCELLED) {
                    return $locked_note->fresh();
                }
            }

            if ($locked_note->status !== CreditNoteStatus::DRAFT) {
                throw CreditNoteDomainException::InvalidState();
            }
            if ((int) $locked_note->lock_version !== $command->lock_version) {
                throw CreditNoteDomainException::StaleDraft();
            }
            $this->AssertIssuerConfiguration();
            $this->AssertBuyerConfiguration($locked_note);
            $issue_date = now((string) config('app.timezone', 'Asia/Bangkok'))->toImmutable();
            if ($locked_note->adjustment_event_date === null) {
                throw CreditNoteDomainException::InvalidEventDate();
            }
            try {
                CreditNoteEventDateRule::Validate(
                    $locked_note->adjustment_event_date->toImmutable(),
                    $issue_date,
                    $locked_note->late_issue_reason,
                );
            } catch (\InvalidArgumentException) {
                throw CreditNoteDomainException::InvalidEventDate();
            }

            $snapshot = $this->source_reader->Read($locked_receipt);
            if ($this->source_hasher->Hash($snapshot) !== (string) $locked_note->source_snapshot_hash) {
                throw CreditNoteDomainException::SourceChanged();
            }
            $this->eligibility_service->AssertCanIssue($locked_receipt, $snapshot);

            $items = $locked_note->Items()->get();
            if ($items->isEmpty()) {
                throw CreditNoteDomainException::OverCredit();
            }
            $source_lines = [];
            foreach ($snapshot->lines as $line) {
                $source_lines[$line->source_type->value . ':' . $line->source_id] = $line;
            }
            $prior_net_credit = $this->PriorNetCreditBySource($locked_receipt->id, $locked_note->id);
            $inputs = [];
            foreach ($items as $item) {
                $source_key = $item->source_type->value . ':' . $item->source_id;
                $source_line = $source_lines[$source_key] ?? null;
                if ($source_line === null || $item->credit_amount_minor <= 0) {
                    throw CreditNoteDomainException::OverCredit();
                }
                $inputs[] = new CreditNoteLineCalculationInput(
                    (int) $item->id,
                    $source_line->original_line_total_minor,
                    (int) $item->credit_amount_minor,
                    $source_line->tax_treatment,
                    (int) $source_line->vat_rate_basis_points,
                    $prior_net_credit[$source_key] ?? 0,
                );
            }
            $calculation = $this->CalculateCreditNote($inputs);
            $selected_lines = [];
            foreach ($items as $item) {
                $source_key = $item->source_type->value . ':' . $item->source_id;
                $selected_lines[] = [
                    'source_type' => $item->source_type,
                    'source_id' => (int) $item->source_id,
                    'line' => $source_lines[$source_key],
                    'credit_quantity' => $item->credit_quantity,
                    'credit_amount_minor' => (int) $item->credit_amount_minor,
                ];
            }
            $full_calculation = $this->CalculateCreditNote($this->BuildFullCalculationInputs($snapshot, $selected_lines, $prior_net_credit));

            foreach ($items as $item) {
                $allocation = $calculation->LineAllocations()[$item->id];
                $item->credit_taxable_base_minor = $allocation['credit_taxable_base_minor'];
                $item->credit_vat_minor = $allocation['credit_vat_minor'];
                $item->credit_exempt_minor = $allocation['credit_exempt_minor'];
                $item->credit_out_of_scope_minor = $allocation['credit_out_of_scope_minor'];
                $item->save();
            }

            $number = $this->number_generator->Next((int) $locked_note->centre_id, $issue_date);
            $issuer = (array) config('financial-documents.credit_note.issuer', []);
            $this->AssignTotals($locked_note, $full_calculation);
            $locked_note->number = $number;
            $locked_note->issue_idempotency_key = $command->idempotency_key;
            $locked_note->issue_payload_hash = $payload_hash;
            $locked_note->issue_date = $issue_date->toDateString();
            $locked_note->issued_at = $issue_date;
            $locked_note->issued_by = $actor->id;
            $locked_note->issuer_name = trim((string) ($issuer['legal_name'] ?? ''));
            $locked_note->issuer_address = trim((string) ($issuer['legal_address'] ?? ''));
            $locked_note->issuer_tax_id = ThaiTaxIdentifier::FromInput((string) ($issuer['tax_id'] ?? ''))->Digits();
            $locked_note->issuer_branch_type = (string) ($issuer['branch_type'] ?? 'head_office');
            $locked_note->issuer_branch_code = (string) ($issuer['branch_code'] ?? '00000');
            $locked_note->active_draft_receipt_id = null;
            $locked_note->status = CreditNoteStatus::ISSUED;
            $locked_note->save();

            CreditNotePosting::create([
                'credit_note_id' => $locked_note->id,
                'centre_id' => $locked_note->centre_id,
                'posting_type' => CreditNotePostingType::ISSUE,
                'effective_date' => $issue_date->toDateString(),
                'taxable_base_delta_minor' => -$calculation->Difference()->taxable_base_minor,
                'vat_delta_minor' => -$calculation->Difference()->vat_minor,
                'exempt_delta_minor' => -$calculation->Difference()->exempt_minor,
                'out_of_scope_delta_minor' => -$calculation->Difference()->out_of_scope_minor,
                'grand_total_delta_minor' => -$calculation->Difference()->grand_total_minor,
                'created_by' => $actor->id,
                'correlation_id' => $correlation_id,
                'occurred_at' => $issue_date,
            ]);

            $commission = $locked_receipt->AffiliateCommissionRecords()->first();
            if ($commission !== null && $this->affiliate_adjustment === null) {
                throw CreditNoteDomainException::AffiliateAdjustmentFailed();
            }
            if ($this->affiliate_adjustment !== null) {
                $this->affiliate_adjustment->ApplyIssue($locked_note, $actor, $correlation_id);
            }

            $this->event_recorder->Record(
                $locked_note,
                CreditNoteEventOperation::ISSUED,
                CreditNoteStatus::DRAFT,
                CreditNoteStatus::ISSUED,
                -$calculation->Difference()->grand_total_minor,
                $actor,
                $locked_note->reason_code,
                $correlation_id,
                ['version' => $locked_note->lock_version, 'posting_type' => 'issue'],
            );

            return $locked_note->fresh();
        }, (int) config('financial-documents.credit_note.limits.transaction_attempts', 3));
    }

    public function Cancel(
        CreditNote $credit_note,
        CancelCreditNote $command,
        User $actor,
        string $correlation_id,
    ): CreditNote {
        return DB::transaction(function () use ($credit_note, $command, $actor, $correlation_id): CreditNote {
            $reason = trim($command->reason);
            if ($reason === '') {
                throw CreditNoteDomainException::InvalidState();
            }

            $locked_receipt = Receipt::query()->whereKey($credit_note->receipt_id)->lockForUpdate()->firstOrFail();
            $locked_note = CreditNote::query()->whereKey($credit_note->id)->lockForUpdate()->firstOrFail();
            $payload_hash = $this->HashPayload([
                'credit_note_id' => $locked_note->id,
                'reason' => $reason,
            ]);

            if ($locked_note->cancel_idempotency_key !== null) {
                if ($locked_note->cancel_idempotency_key !== $command->idempotency_key || $locked_note->cancel_payload_hash !== $payload_hash) {
                    throw CreditNoteDomainException::IdempotencyConflict();
                }
                if ($locked_note->status === CreditNoteStatus::CANCELLED) {
                    return $locked_note->fresh();
                }
            }

            if (!in_array($locked_note->status, [CreditNoteStatus::DRAFT, CreditNoteStatus::ISSUED], true)) {
                throw CreditNoteDomainException::InvalidState();
            }

            $status_before = $locked_note->status;
            $cancel_date = now((string) config('app.timezone', 'Asia/Bangkok'))->toImmutable();
            $locked_note->cancellation_effective_date = $cancel_date->toDateString();
            $locked_note->cancelled_at = $cancel_date;
            $locked_note->cancellation_reason = $reason;
            $locked_note->cancel_idempotency_key = $command->idempotency_key;
            $locked_note->cancel_payload_hash = $payload_hash;
            $locked_note->cancelled_by = $actor->id;
            $locked_note->active_draft_receipt_id = null;
            $locked_note->status = CreditNoteStatus::CANCELLED;
            $locked_note->save();

            if ($status_before === CreditNoteStatus::ISSUED) {
                $issue_posting = CreditNotePosting::query()
                    ->where('credit_note_id', $locked_note->id)
                    ->where('posting_type', CreditNotePostingType::ISSUE)
                    ->firstOrFail();
                CreditNotePosting::create([
                    'credit_note_id' => $locked_note->id,
                    'centre_id' => $locked_note->centre_id,
                    'posting_type' => CreditNotePostingType::CANCELLATION,
                    'effective_date' => $cancel_date->toDateString(),
                    'taxable_base_delta_minor' => -(int) $issue_posting->taxable_base_delta_minor,
                    'vat_delta_minor' => -(int) $issue_posting->vat_delta_minor,
                    'exempt_delta_minor' => -(int) $issue_posting->exempt_delta_minor,
                    'out_of_scope_delta_minor' => -(int) $issue_posting->out_of_scope_delta_minor,
                    'grand_total_delta_minor' => -(int) $issue_posting->grand_total_delta_minor,
                    'created_by' => $actor->id,
                    'correlation_id' => $correlation_id,
                    'occurred_at' => $cancel_date,
                ]);

                $commission = $locked_receipt->AffiliateCommissionRecords()->first();
                if ($commission !== null && $this->affiliate_adjustment === null) {
                    throw CreditNoteDomainException::AffiliateAdjustmentFailed();
                }
                if ($this->affiliate_adjustment !== null) {
                    $this->affiliate_adjustment->ApplyCancellation($locked_note, $actor, $correlation_id);
                }
            }

            $this->event_recorder->Record(
                $locked_note,
                CreditNoteEventOperation::CANCELLED,
                $status_before,
                CreditNoteStatus::CANCELLED,
                $status_before === CreditNoteStatus::ISSUED ? $locked_note->difference_grand_total_minor : 0,
                $actor,
                $locked_note->reason_code,
                $correlation_id,
                ['posting_type' => $status_before === CreditNoteStatus::ISSUED ? 'cancellation' : 'none'],
            );

            return $locked_note->fresh();
        }, (int) config('financial-documents.credit_note.limits.transaction_attempts', 3));
    }

    private function CalculateCreditNote(array $inputs): \App\Domain\CreditNote\ValueObjects\CreditNoteCalculation
    {
        try {
            return $this->calculator->Calculate($inputs);
        } catch (\InvalidArgumentException) {
            throw CreditNoteDomainException::OverCredit();
        }
    }

    private function BuildCalculationInputs(CreditNoteSourceSnapshot $snapshot, array $items, array $prior_net_credit = []): array
    {
        if ($items === []) {
            throw CreditNoteDomainException::OverCredit();
        }

        $source_lines = [];
        foreach ($snapshot->lines as $line) {
            $source_lines[$line->source_type->value . ':' . $line->source_id] = $line;
        }

        $inputs = [];
        $selected_lines = [];
        $seen = [];
        foreach ($items as $item) {
            $source_type = CreditNoteSourceType::tryFrom((string) ($item['source_type'] ?? ''));
            $source_id = (int) ($item['source_id'] ?? 0);
            if ($source_type === null || $source_id < 1) {
                throw CreditNoteDomainException::SourceIncomplete();
            }
            $key = $source_type->value . ':' . $source_id;
            if (isset($seen[$key]) || !isset($source_lines[$key])) {
                throw CreditNoteDomainException::OverCredit();
            }
            $seen[$key] = true;
            $line = $source_lines[$key];
            if ($line->line_type === null || $line->tax_treatment === null || $line->vat_rate_basis_points === null) {
                throw CreditNoteDomainException::SourceIncomplete();
            }
            $credit_amount_minor = (int) ($item['credit_amount_minor'] ?? 0);
            if ($credit_amount_minor <= 0 || $credit_amount_minor > $line->original_line_total_minor) {
                throw CreditNoteDomainException::OverCredit();
            }
            $credit_quantity = $item['credit_quantity'] ?? null;
            if ($credit_quantity !== null) {
                $credit_quantity = FixedQuantity::FromDecimal((string) $credit_quantity)->Decimal();
            }
            $inputs[] = new CreditNoteLineCalculationInput(
                count($inputs) + 1,
                $line->original_line_total_minor,
                $credit_amount_minor,
                $line->tax_treatment,
                $line->vat_rate_basis_points,
                $prior_net_credit[$key] ?? 0,
            );
            $selected_lines[] = [
                'source_type' => $source_type,
                'source_id' => $source_id,
                'calculation_item_id' => count($inputs),
                'line' => $line,
                'credit_quantity' => $credit_quantity,
                'credit_amount_minor' => $credit_amount_minor,
            ];
        }

        return [$inputs, $selected_lines];
    }

    private function BuildFullCalculationInputs(CreditNoteSourceSnapshot $snapshot, array $selected_lines, array $prior_net_credit): array
    {
        $selected_by_key = [];
        foreach ($selected_lines as $selected_line) {
            $key = $selected_line['source_type']->value . ':' . $selected_line['source_id'];
            $selected_by_key[$key] = (int) ($selected_line['credit_amount_minor'] ?? 0);
        }

        $inputs = [];
        foreach ($snapshot->lines as $index => $line) {
            if ($line->line_type === null || $line->tax_treatment === null || $line->vat_rate_basis_points === null) {
                throw CreditNoteDomainException::SourceIncomplete();
            }
            $key = $line->source_type->value . ':' . $line->source_id;
            $inputs[] = new CreditNoteLineCalculationInput(
                $index + 1,
                $line->original_line_total_minor,
                $selected_by_key[$key] ?? 0,
                $line->tax_treatment,
                (int) $line->vat_rate_basis_points,
                $prior_net_credit[$key] ?? 0,
            );
        }
        if ($inputs === []) {
            throw CreditNoteDomainException::FullyCredited();
        }

        return $inputs;
    }

    private function AssignTotals(CreditNote $credit_note, \App\Domain\CreditNote\ValueObjects\CreditNoteCalculation $calculation): void
    {
        foreach (['original' => $calculation->Original(), 'corrected' => $calculation->Corrected(), 'difference' => $calculation->Difference()] as $prefix => $totals) {
            foreach (['taxable_base', 'vat', 'exempt', 'out_of_scope', 'grand_total'] as $category) {
                $credit_note->{$prefix . '_' . $category . '_minor'} = $totals->{$category . '_minor'};
            }
        }
    }

    private function IsUniqueConflict(QueryException $exception): bool
    {
        return str_contains(strtolower($exception->getMessage()), 'unique')
            || str_contains(strtolower($exception->getMessage()), 'constraint');
    }

    private function AssertIssuerConfiguration(): void
    {
        $issuer = (array) config('financial-documents.credit_note.issuer', []);
        if (
            trim((string) ($issuer['legal_name'] ?? '')) === ''
            || trim((string) ($issuer['legal_address'] ?? '')) === ''
            || trim((string) ($issuer['tax_id'] ?? '')) === ''
            || trim((string) ($issuer['branch_code'] ?? '')) !== '00000'
            || (string) ($issuer['branch_type'] ?? '') !== 'head_office'
        ) {
            throw CreditNoteDomainException::IssuerConfigurationInvalid();
        }

        try {
            ThaiTaxIdentifier::FromInput((string) $issuer['tax_id']);
        } catch (\InvalidArgumentException) {
            throw CreditNoteDomainException::IssuerConfigurationInvalid();
        }
    }

    private function AssertBuyerConfiguration(CreditNote $credit_note): void
    {
        if (trim((string) ($credit_note->buyer_name ?? '')) === '' || $credit_note->buyer_is_vat_registered === null) {
            throw CreditNoteDomainException::BuyerDetailsIncomplete();
        }
        if (!$credit_note->buyer_is_vat_registered) {
            return;
        }
        try {
            ThaiTaxIdentifier::FromInput((string) ($credit_note->buyer_tax_id ?? ''));
        } catch (\InvalidArgumentException) {
            throw CreditNoteDomainException::BuyerDetailsIncomplete();
        }
        if (!in_array((string) $credit_note->buyer_branch_type, ['head_office', 'branch'], true)) {
            throw CreditNoteDomainException::BuyerDetailsIncomplete();
        }
        if ((string) $credit_note->buyer_branch_type === 'head_office' && (string) $credit_note->buyer_branch_code !== '00000') {
            throw CreditNoteDomainException::BuyerDetailsIncomplete();
        }
        if ((string) $credit_note->buyer_branch_type === 'branch' && !preg_match('/\A[0-9]{5}\z/D', (string) $credit_note->buyer_branch_code)) {
            throw CreditNoteDomainException::BuyerDetailsIncomplete();
        }
    }

    private function PriorNetCreditBySource(int $receipt_id, int $current_credit_note_id): array
    {
        $rows = DB::table('credit_note_items as items')
            ->join('credit_notes as notes', 'notes.id', '=', 'items.credit_note_id')
            ->join('credit_note_postings as postings', 'postings.credit_note_id', '=', 'notes.id')
            ->where('notes.receipt_id', $receipt_id)
            ->where('notes.id', '!=', $current_credit_note_id)
            ->select('items.source_type', 'items.source_id', 'postings.posting_type', DB::raw('SUM(items.credit_amount_minor) as amount_minor'))
            ->groupBy('items.source_type', 'items.source_id', 'postings.posting_type')
            ->get();
        $totals = [];
        foreach ($rows as $row) {
            $key = $row->source_type . ':' . $row->source_id;
            $sign = $row->posting_type === CreditNotePostingType::ISSUE->value ? 1 : -1;
            $totals[$key] = ($totals[$key] ?? 0) + $sign * (int) $row->amount_minor;
        }

        return $totals;
    }

    private function HashPayload(array $payload): string
    {
        ksort($payload);

        return hash('sha256', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }
}
