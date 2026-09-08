<?php

namespace Tests\Feature\CreditNote;

use App\Domain\CreditNote\Commands\CancelCreditNote;
use App\Domain\CreditNote\Commands\IssueCreditNote;
use App\Domain\CreditNote\Enums\CreditNoteStatus;
use App\Domain\CreditNote\Exceptions\CreditNoteDomainException;
use App\Domain\CreditNote\Models\CreditNotePosting;
use App\Domain\CreditNote\Services\CreditNoteCommandService;
use App\Models\Receipt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreditNoteCancellationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(\Carbon\Carbon::parse('2026-08-12 12:00:00', 'Asia/Bangkok'));
    }

    #[Test]
    public function CancelsDraftWithoutPostingAndClearsActiveDraftGuard(): void
    {
        [$draft, $actor] = $this->CreateDraft();
        $cancelled = app(CreditNoteCommandService::class)->Cancel(
            $draft,
            new CancelCreditNote('User cancelled before Issue', 'cancel-key-1'),
            $actor,
            'corr-cancel',
        );

        $this->assertSame(CreditNoteStatus::CANCELLED, $cancelled->status);
        $this->assertNull($cancelled->active_draft_receipt_id);
        $this->assertSame('User cancelled before Issue', $cancelled->cancellation_reason);
        $this->assertSame(0, CreditNotePosting::query()->count());
    }

    #[Test]
    public function CancelsIssuedDocumentWithExactPositiveReversalAndIdempotentRetry(): void
    {
        [$draft, $actor] = $this->CreateDraft();
        $service = app(CreditNoteCommandService::class);
        $issued = $service->Issue($draft, new IssueCreditNote($draft->lock_version, 'issue-key-1'), $actor, 'corr-issue');
        $cancelled = $service->Cancel(
            $issued,
            new CancelCreditNote('Approved cancellation', 'cancel-key-1'),
            $actor,
            'corr-cancel',
        );

        $this->assertSame(CreditNoteStatus::CANCELLED, $cancelled->status);
        $this->assertSame('BKKCN2608001', $cancelled->number);
        $this->assertSame(2, CreditNotePosting::query()->where('credit_note_id', $cancelled->id)->count());
        $this->assertSame(10700, CreditNotePosting::query()->where('credit_note_id', $cancelled->id)->where('posting_type', 'cancellation')->value('grand_total_delta_minor'));

        $retry = $service->Cancel(
            $cancelled->fresh(),
            new CancelCreditNote('Approved cancellation', 'cancel-key-1'),
            $actor,
            'corr-retry',
        );
        $this->assertSame($cancelled->number, $retry->number);
        $this->assertSame(2, CreditNotePosting::query()->where('credit_note_id', $cancelled->id)->count());
    }

    #[Test]
    public function DifferentCancellationKeyOrBlankReasonIsRejected(): void
    {
        [$draft, $actor] = $this->CreateDraft();
        $service = app(CreditNoteCommandService::class);
        $issued = $service->Issue($draft, new IssueCreditNote($draft->lock_version, 'issue-key-1'), $actor, 'corr-issue');
        $cancelled = $service->Cancel($issued, new CancelCreditNote('Approved cancellation', 'cancel-key-1'), $actor, 'corr-cancel');

        try {
            $service->Cancel($cancelled->fresh(), new CancelCreditNote('Different reason', 'different-key'), $actor, 'corr-conflict');
            $this->fail('A different cancellation key must conflict.');
        } catch (CreditNoteDomainException $exception) {
            $this->assertSame('CREDIT_NOTE_IDEMPOTENCY_CONFLICT', $exception->ErrorCode());
        }

        [$draft_two, $actor_two] = $this->CreateDraft();
        $this->expectException(CreditNoteDomainException::class);
        $service->Cancel($draft_two, new CancelCreditNote('   ', 'cancel-key-empty'), $actor_two, 'corr-empty');
    }

    private function CreateDraft(): array
    {
        $department_id = \App\Models\Department::firstOrCreate(['code' => 'BKK'], ['name' => 'Bangkok', 'status' => 1])->id;
        $contact_id = DB::table('contacts')->insertGetId(['centre' => $department_id, 'name' => 'Student', 'address' => 'Address']);
        $order_id = DB::table('orders')->insertGetId(['centre' => $department_id, 'cid' => $contact_id, 'status' => 1, 'total_price' => '107.00', 'orther_fee' => '0.00']);
        $receipt_id = DB::table('receipts')->insertGetId(['centre' => $department_id, 'cid' => $contact_id, 'oid' => $order_id, 'status' => 1, 'payment_date' => '2026-08-12', 'receipt_number' => 'R-1', 'total_fee' => '107.00']);
        $source_id = DB::table('receipt_details')->insertGetId([
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
        $actor_id = DB::table('users')->insertGetId(['name' => 'Actor', 'email' => 'actor-' . uniqid() . '@example.com', 'password' => 'secret', 'department_id' => $department_id]);
        $receipt = Receipt::findOrFail($receipt_id);
        $actor = User::findOrFail($actor_id);
        $service = app(CreditNoteCommandService::class);
        $draft = $service->CreateDraft($receipt, $actor, 'corr-create-' . uniqid());
        $service->UpdateDraft(
            $draft,
            new \App\Domain\CreditNote\Commands\UpdateCreditNoteDraft(
                0,
                ['buyer_name' => 'Student', 'buyer_address' => 'Address', 'buyer_tax_id' => null, 'buyer_is_vat_registered' => false, 'buyer_branch_type' => 'not_applicable', 'buyer_branch_code' => null],
                'product_return',
                'Approved return',
                \Carbon\CarbonImmutable::parse('2026-08-12', 'Asia/Bangkok'),
                null,
                [['source_type' => 'receipt_detail', 'source_id' => $source_id, 'credit_quantity' => '1.00', 'credit_amount_minor' => 10700]],
            ),
            $actor,
            'corr-update-' . uniqid(),
        );

        return [$draft->fresh(), $actor];
    }
}
