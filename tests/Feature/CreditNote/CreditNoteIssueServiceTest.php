<?php

namespace Tests\Feature\CreditNote;

use App\Domain\CreditNote\Commands\IssueCreditNote;
use App\Domain\CreditNote\Contracts\AffiliateCreditNoteAdjustment;
use App\Domain\CreditNote\Exceptions\CreditNoteDomainException;
use App\Domain\CreditNote\Models\CreditNote;
use App\Domain\CreditNote\Models\CreditNotePosting;
use App\Domain\CreditNote\Services\CreditNoteCommandService;
use App\Domain\CreditNote\ValueObjects\AffiliateCreditNoteAdjustmentResult;
use App\Models\Receipt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreditNoteIssueServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(\Carbon\Carbon::parse('2026-08-12 12:00:00', 'Asia/Bangkok'));
    }

    #[Test]
    public function IssuesDraftWithNumberImmutableSnapshotsNegativePostingAndIdempotentRetry(): void
    {
        [$credit_note, $actor] = $this->CreateDraft();
        $service = app(CreditNoteCommandService::class);
        $issued = $service->Issue($credit_note, new IssueCreditNote($credit_note->lock_version, 'issue-key-1'), $actor, 'corr-issue');

        $this->assertSame('issued', $issued->status->value);
        $this->assertSame('BKKCN2608001', $issued->number);
        $this->assertNotNull($issued->issuer_name);
        $this->assertSame(10700, $issued->difference_grand_total_minor);
        $this->assertSame(-10700, CreditNotePosting::query()->where('credit_note_id', $issued->id)->value('grand_total_delta_minor'));
        $this->assertSame('issue', CreditNotePosting::query()->where('credit_note_id', $issued->id)->value('posting_type')->value);
        $this->assertSame(1, CreditNotePosting::query()->where('credit_note_id', $issued->id)->count());

        $retry = $service->Issue($issued->fresh(), new IssueCreditNote($issued->lock_version, 'issue-key-1'), $actor, 'corr-retry');
        $this->assertSame($issued->number, $retry->number);
        $this->assertSame(1, CreditNotePosting::query()->where('credit_note_id', $issued->id)->count());
    }

    #[Test]
    public function DifferentIdempotencyPayloadAndStaleIssueAreRejected(): void
    {
        [$credit_note, $actor] = $this->CreateDraft();
        $service = app(CreditNoteCommandService::class);
        $issued = $service->Issue($credit_note, new IssueCreditNote($credit_note->lock_version, 'issue-key-1'), $actor, 'corr-issue');

        try {
            $service->Issue($issued->fresh(), new IssueCreditNote($issued->lock_version, 'different-key'), $actor, 'corr-conflict');
            $this->fail('A different key on an issued document must be rejected.');
        } catch (CreditNoteDomainException $exception) {
            $this->assertSame('CREDIT_NOTE_IDEMPOTENCY_CONFLICT', $exception->ErrorCode());
        }

        [$draft, $second_actor] = $this->CreateDraft();
        $this->expectException(CreditNoteDomainException::class);
        $service->Issue($draft, new IssueCreditNote(99, 'issue-key-stale'), $second_actor, 'corr-stale');
    }

    #[Test]
    public function MissingIssuerConfigurationBlocksIssueBeforeAnyFinancialEvidence(): void
    {
        [$credit_note, $actor] = $this->CreateDraft();
        config([
            'financial-documents.credit_note.issuer.legal_name' => '',
            'financial-documents.credit_note.issuer.legal_address' => '',
            'financial-documents.credit_note.issuer.tax_id' => '',
        ]);

        $this->expectException(CreditNoteDomainException::class);
        app(CreditNoteCommandService::class)->Issue($credit_note, new IssueCreditNote($credit_note->lock_version, 'issue-key-1'), $actor, 'corr-issue');
        $this->assertSame('draft', $credit_note->fresh()->status->value);
        $this->assertSame(0, CreditNotePosting::query()->count());
    }

    #[Test]
    public function invalid_adjustment_event_date_blocks_issue_before_numbering(): void
    {
        [$credit_note, $actor] = $this->CreateDraft();
        $credit_note->adjustment_event_date = '2026-06-01';
        $credit_note->late_issue_reason = null;
        $credit_note->save();

        try {
            app(CreditNoteCommandService::class)->Issue($credit_note->fresh(), new IssueCreditNote($credit_note->lock_version, 'issue-date-key'), $actor, 'corr-issue-date');
            $this->fail('An unexplained older adjustment event date must be rejected.');
        } catch (CreditNoteDomainException $exception) {
            $this->assertSame('CREDIT_NOTE_INVALID_EVENT_DATE', $exception->ErrorCode());
        }

        $this->assertSame('draft', $credit_note->fresh()->status->value);
        $this->assertNull($credit_note->fresh()->number);
        $this->assertSame(0, CreditNotePosting::query()->count());
    }

    private function CreateDraft(): array
    {
        $department_id = \App\Models\Department::firstOrCreate(['code' => 'BKK'], ['name' => 'Bangkok', 'status' => 1])->id;
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
            'email' => 'actor-' . uniqid() . '@example.com',
            'password' => 'secret',
            'department_id' => $department_id,
        ]);

        $receipt = Receipt::findOrFail($receipt_id);
        $actor = User::findOrFail($actor_id);
        $source_id = (int) DB::table('receipt_details')->where('receipt_id', $receipt_id)->value('id');
        $service = app(CreditNoteCommandService::class);
        $credit_note = $service->CreateDraft($receipt, $actor, 'corr-create-' . uniqid());
        $service->UpdateDraft(
            $credit_note,
            new \App\Domain\CreditNote\Commands\UpdateCreditNoteDraft(
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
                \Carbon\CarbonImmutable::parse('2026-08-12', 'Asia/Bangkok'),
                null,
                [[
                    'source_type' => 'receipt_detail',
                    'source_id' => $source_id,
                    'credit_quantity' => '1.00',
                    'credit_amount_minor' => 10700,
                ]],
            ),
            $actor,
            'corr-update-' . uniqid(),
        );

        return [$credit_note->fresh(), $actor];
    }
}
