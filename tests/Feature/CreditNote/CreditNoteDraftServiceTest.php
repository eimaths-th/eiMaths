<?php

namespace Tests\Feature\CreditNote;

use App\Domain\CreditNote\Commands\UpdateCreditNoteDraft;
use App\Domain\CreditNote\Exceptions\CreditNoteDomainException;
use App\Domain\CreditNote\Models\CreditNote;
use App\Domain\CreditNote\Models\CreditNoteEvent;
use App\Domain\CreditNote\Services\CreditNoteCommandService;
use App\Domain\CreditNote\Services\CreditNoteSourceReader;
use App\Models\Receipt;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreditNoteDraftServiceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function CreateDraftPrefillsStructuredBuyerEvidenceAndCreatesNoItemsOrNumber(): void
    {
        [$receipt, $actor] = $this->CreateReceiptAndActor();

        $credit_note = app(CreditNoteCommandService::class)->CreateDraft($receipt, $actor, 'corr-create');

        $this->assertSame('draft', $credit_note->status->value);
        $this->assertNull($credit_note->number);
        $this->assertSame('Student', $credit_note->buyer_name);
        $this->assertSame('Address', $credit_note->buyer_address);
        $this->assertSame(0, $credit_note->Items()->count());
        $this->assertSame($receipt->id, $credit_note->active_draft_receipt_id);
        $this->assertSame('created', CreditNoteEvent::query()->where('credit_note_id', $credit_note->id)->value('operation')->value);
    }

    #[Test]
    public function OnlyOneActiveDraftCanBeCreatedForTheReceipt(): void
    {
        [$receipt, $actor] = $this->CreateReceiptAndActor();
        $service = app(CreditNoteCommandService::class);
        $service->CreateDraft($receipt, $actor, 'corr-create-1');

        $this->expectException(CreditNoteDomainException::class);
        $service->CreateDraft($receipt->fresh(), $actor, 'corr-create-2');
    }

    #[Test]
    public function UpdateDraftReplacesItemsComputesTotalsAndIncrementsLockVersion(): void
    {
        [$receipt, $actor] = $this->CreateReceiptAndActor();
        $service = app(CreditNoteCommandService::class);
        $credit_note = $service->CreateDraft($receipt, $actor, 'corr-create');
        $command = new UpdateCreditNoteDraft(
            0,
            [
                'buyer_name' => 'Student',
                'buyer_address' => 'Address',
                'buyer_tax_id' => null,
                'buyer_is_vat_registered' => false,
                'buyer_branch_type' => 'not_applicable',
                'buyer_branch_code' => null,
            ],
            'product_return',
            'Approved return',
            CarbonImmutable::parse('2026-08-12', 'Asia/Bangkok'),
            null,
            [[
                'source_type' => 'receipt_detail',
                'source_id' => 1,
                'credit_quantity' => '1.00',
                'credit_amount_minor' => 10700,
            ]],
        );

        $updated = $service->UpdateDraft($credit_note, $command, $actor, 'corr-update');

        $this->assertSame(1, $updated->lock_version);
        $this->assertSame(1, $updated->Items()->count());
        $this->assertSame(10700, $updated->difference_grand_total_minor);
        $this->assertSame('product_return', $updated->reason_code->value);
        $this->assertSame('Approved return', $updated->reason_details);
        $this->assertSame(1, CreditNoteEvent::query()->where('credit_note_id', $updated->id)->where('operation', 'updated')->count());
        $this->assertSame('Address', DB::table('contacts')->where('id', $receipt->cid)->value('address'));
    }

    #[Test]
    public function partial_credit_keeps_full_original_and_cumulative_corrected_totals(): void
    {
        [$receipt, $actor] = $this->CreateReceiptAndActor();
        DB::table('receipt_details')->insert([
            'receipt_id' => $receipt->id,
            'des' => 'ค่าหนังสือ',
            'unit' => 'book',
            'quantity' => '1.00',
            'price' => '10.00',
            'discount' => '0.00',
            'tax' => 0,
            'line_type' => 'book',
            'tax_treatment' => 'vat_exempt',
            'vat_rate_basis_points' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $receipt->forceFill(['total_fee' => '117.00'])->save();

        $service = app(CreditNoteCommandService::class);
        $credit_note = $service->CreateDraft($receipt->fresh(), $actor, 'corr-full-total-create');
        $updated = $service->UpdateDraft($credit_note, new UpdateCreditNoteDraft(
            0,
            [
                'buyer_name' => 'Student',
                'buyer_address' => 'Address',
                'buyer_tax_id' => null,
                'buyer_is_vat_registered' => false,
                'buyer_branch_type' => 'not_applicable',
                'buyer_branch_code' => null,
            ],
            'product_return',
            'Approved partial return',
            CarbonImmutable::parse('2026-08-12', 'Asia/Bangkok'),
            null,
            [[
                'source_type' => 'receipt_detail',
                'source_id' => 1,
                'credit_quantity' => '1.00',
                'credit_amount_minor' => 10700,
            ]],
        ), $actor, 'corr-full-total-update');

        $this->assertSame(11700, $updated->original_grand_total_minor);
        $this->assertSame(1000, $updated->corrected_grand_total_minor);
        $this->assertSame(10700, $updated->difference_grand_total_minor);
    }

    #[Test]
    public function StaleVersionAndSourceChangeAreRejectedWithoutMutatingDraft(): void
    {
        [$receipt, $actor] = $this->CreateReceiptAndActor();
        $service = app(CreditNoteCommandService::class);
        $credit_note = $service->CreateDraft($receipt, $actor, 'corr-create');
        $command = new UpdateCreditNoteDraft(
            9,
            [],
            'product_return',
            'Approved return',
            CarbonImmutable::parse('2026-08-12', 'Asia/Bangkok'),
            null,
            [],
        );

        try {
            $service->UpdateDraft($credit_note, $command, $actor, 'corr-stale');
            $this->fail('Stale Draft version must be rejected.');
        } catch (CreditNoteDomainException $exception) {
            $this->assertSame('CREDIT_NOTE_STALE_DRAFT', $exception->ErrorCode());
        }

        DB::table('receipt_details')->where('receipt_id', $receipt->id)->update(['des' => 'Changed source']);
        $source_changed_command = new UpdateCreditNoteDraft(
            0,
            [],
            'product_return',
            'Approved return',
            CarbonImmutable::parse('2026-08-12', 'Asia/Bangkok'),
            null,
            [],
        );
        $this->expectException(CreditNoteDomainException::class);
        $service->UpdateDraft($credit_note->fresh(), $source_changed_command, $actor, 'corr-source');
    }

    private function CreateReceiptAndActor(): array
    {
        $department_id = DB::table('departments')->insertGetId(['code' => 'BKK', 'name' => 'Bangkok']);
        $contact_id = DB::table('contacts')->insertGetId([
            'centre' => $department_id,
            'name' => 'Student',
            'address' => 'Address',
        ]);
        $order_id = DB::table('orders')->insertGetId([
            'centre' => $department_id,
            'cid' => $contact_id,
            'status' => 1,
            'total_price' => '107.00',
            'orther_fee' => '0.00',
        ]);
        $receipt_id = DB::table('receipts')->insertGetId([
            'centre' => $department_id,
            'cid' => $contact_id,
            'oid' => $order_id,
            'status' => 1,
            'payment_date' => '2026-08-12',
            'receipt_number' => 'R-1',
            'total_fee' => '107.00',
            'billing_details' => 'Student billing',
        ]);
        DB::table('receipt_details')->insert([
            'receipt_id' => $receipt_id,
            'des' => 'ค่าคอร์สเรียน Primary',
            'unit' => 'course',
            'quantity' => '1.00',
            'price' => '107.00',
            'discount' => '0.00',
            'tax' => 1,
            'line_type' => 'tuition',
            'tax_treatment' => 'vat_inclusive',
            'vat_rate_basis_points' => 700,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $actor_id = DB::table('users')->insertGetId([
            'name' => 'Actor',
            'email' => 'actor@example.com',
            'password' => 'secret',
            'department_id' => $department_id,
        ]);

        return [Receipt::findOrFail($receipt_id), User::findOrFail($actor_id)];
    }
}
