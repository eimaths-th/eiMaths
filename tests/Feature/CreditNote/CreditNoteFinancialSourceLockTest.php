<?php

namespace Tests\Feature\CreditNote;

use App\Domain\CreditNote\Models\CreditNote;
use App\Domain\CreditNote\Services\CreditNoteFinancialSourceGuard;
use App\Domain\Financial\Services\FinancialSourceGuard;
use App\Domain\CreditNote\Enums\CreditNoteStatus;
use App\Domain\CreditNote\Exceptions\CreditNoteDomainException;
use App\Models\Contact;
use App\Models\Order;
use App\Models\Receipt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreditNoteFinancialSourceLockTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function NoEvidenceAndUnnumberedDraftAllowNormalSourceChanges(): void
    {
        [$order, $receipt, $contact] = $this->CreateSources();
        $guard = app(CreditNoteFinancialSourceGuard::class);

        $this->assertFalse($guard->IsOrderLocked($order));
        $this->assertFalse($guard->IsReceiptLocked($receipt));
        $guard->AssertOrderUpdateAllowed($order, ['total_price' => '100.00']);
        $guard->AssertReceiptUpdateAllowed($receipt, ['total_fee' => '100.00'], true);
        $guard->AssertContactDeletionAllowed($contact);

        CreditNote::create([
            'receipt_id' => $receipt->id,
            'centre_id' => $receipt->centre,
            'status' => CreditNoteStatus::DRAFT,
            'currency' => 'THB',
        ]);
        $this->assertFalse($guard->IsReceiptLocked($receipt->fresh()));
    }

    #[Test]
    public function AnyNumberedCreditNoteLocksReceiptOrderAndContactIncludingCancelledIssued(): void
    {
        [$order, $receipt, $contact] = $this->CreateSources();
        $credit_note = CreditNote::create([
            'receipt_id' => $receipt->id,
            'centre_id' => $receipt->centre,
            'status' => CreditNoteStatus::ISSUED,
            'number' => 'BKKCN2608001',
            'currency' => 'THB',
        ]);
        $guard = app(CreditNoteFinancialSourceGuard::class);

        $this->assertTrue($guard->IsOrderLocked($order->fresh()));
        $this->assertTrue($guard->IsReceiptLocked($receipt->fresh()));

        foreach ([
            static fn () => $guard->AssertOrderUpdateAllowed($order->fresh(), ['total_price' => '101.00']),
            static fn () => $guard->AssertReceiptUpdateAllowed($receipt->fresh(), ['total_fee' => '101.00'], true),
            static fn () => $guard->AssertContactDeletionAllowed($contact->fresh()),
        ] as $assertion) {
            try {
                $assertion();
                $this->fail('Numbered Credit Note evidence must lock the source.');
            } catch (CreditNoteDomainException $exception) {
                $this->assertSame('CREDIT_NOTE_FINANCIAL_SOURCE_LOCKED', $exception->ErrorCode());
            }
        }

        $credit_note->status = CreditNoteStatus::CANCELLED;
        $credit_note->cancellation_reason = 'Cancelled';
        $credit_note->cancellation_effective_date = '2026-08-12';
        $credit_note->cancel_idempotency_key = 'cancel';
        $credit_note->cancel_payload_hash = hash('sha256', 'cancel');
        $credit_note->save();
        $this->assertTrue($guard->IsReceiptLocked($receipt->fresh()));
    }

    #[Test]
    public function CompositeGuardRetainsAffiliateMethodsAndAddsCreditNoteLock(): void
    {
        [$order, $receipt] = $this->CreateSources();
        CreditNote::create([
            'receipt_id' => $receipt->id,
            'centre_id' => $receipt->centre,
            'status' => CreditNoteStatus::ISSUED,
            'number' => 'BKKCN2608002',
            'currency' => 'THB',
        ]);

        $guard = app(FinancialSourceGuard::class);
        $this->assertTrue($guard->IsOrderLocked($order));
        $this->assertTrue($guard->IsReceiptLocked($receipt));
    }

    private function CreateSources(): array
    {
        $department_id = DB::table('departments')->insertGetId(['code' => 'BKK', 'name' => 'Bangkok']);
        $contact_id = DB::table('contacts')->insertGetId(['centre' => $department_id, 'name' => 'Student']);
        $order_id = DB::table('orders')->insertGetId(['centre' => $department_id, 'cid' => $contact_id, 'status' => 1, 'total_price' => '100.00']);
        $receipt_id = DB::table('receipts')->insertGetId(['centre' => $department_id, 'cid' => $contact_id, 'oid' => $order_id, 'status' => 1, 'payment_date' => '2026-08-12', 'total_fee' => '100.00']);

        return [Order::findOrFail($order_id), Receipt::findOrFail($receipt_id), Contact::findOrFail($contact_id)];
    }
}
