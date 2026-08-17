<?php

namespace Tests\Feature\CreditNote;

use App\Domain\Affiliate\Enums\AffiliateCommissionStatus;
use App\Domain\Affiliate\Models\AffiliateAttribution;
use App\Domain\Affiliate\Models\AffiliateCommission;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Domain\CreditNote\Enums\CreditNoteSourceType;
use App\Domain\CreditNote\Enums\CreditNoteStatus;
use App\Domain\CreditNote\Models\CreditNote;
use App\Domain\CreditNote\Models\CreditNoteItem;
use App\Domain\Affiliate\Models\AffiliateCommissionCreditNoteAdjustment;
use App\Domain\Affiliate\Models\AffiliateLedgerEntry;
use App\Domain\Affiliate\Services\AffiliateCreditNoteAdjustmentService;
use App\Models\Contact;
use App\Models\Order;
use App\Models\Receipt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AffiliateCreditNoteAdjustmentTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function ApplyIssueReversesTuitionCommissionAndCancellationRestoresItExactly(): void
    {
        [$commission, $credit_note, $actor] = $this->CreateCommissionAndCreditNote(AffiliateCommissionStatus::AVAILABLE);
        $service = app(AffiliateCreditNoteAdjustmentService::class);

        $issue_result = $service->ApplyIssue($credit_note, $actor, 'corr-issue');
        $commission_after_issue = $commission->fresh();
        $this->assertTrue($issue_result->applicable);
        $this->assertSame(1000, $issue_result->commission_reversal_delta_minor);
        $this->assertSame(1000, $commission_after_issue->reversed_minor);
        $this->assertSame(AffiliateCommissionStatus::AVAILABLE, $commission_after_issue->status);
        $this->assertSame(-1000, AffiliateLedgerEntry::query()->where('source_type', 'affiliate_credit_note')->where('source_id', $credit_note->id)->value('available_delta_minor'));

        $cancel_result = $service->ApplyCancellation($credit_note, $actor, 'corr-cancel');
        $commission_after_cancel = $commission->fresh();
        $this->assertSame(-1000, $cancel_result->commission_reversal_delta_minor);
        $this->assertSame(0, $commission_after_cancel->reversed_minor);
        $this->assertSame(AffiliateCommissionStatus::AVAILABLE, $commission_after_cancel->status);
        $this->assertSame(2, AffiliateCommissionCreditNoteAdjustment::query()->where('credit_note_id', $credit_note->id)->count());
        $this->assertSame(0, AffiliateLedgerEntry::query()->where('source_type', 'affiliate_credit_note')->where('source_id', $credit_note->id)->sum('available_delta_minor'));
        $this->assertSame(1000, AffiliateLedgerEntry::query()->where('entry_type', 'CREDIT_NOTE_COMMISSION_RESTORATION')->where('source_id', $credit_note->id)->value('available_delta_minor'));
    }

    #[Test]
    public function PendingAndHeldCommissionsDoNotCreateWalletEntriesAndNonTuitionIsIgnored(): void
    {
        [$commission, $credit_note, $actor] = $this->CreateCommissionAndCreditNote(AffiliateCommissionStatus::PENDING, false);
        $result = app(AffiliateCreditNoteAdjustmentService::class)->ApplyIssue($credit_note, $actor, 'corr-pending');

        $this->assertFalse($result->applicable);
        $this->assertSame(0, $commission->fresh()->reversed_minor);
        $this->assertSame(0, AffiliateLedgerEntry::query()->count());
    }

    private function CreateCommissionAndCreditNote(AffiliateCommissionStatus $status, bool $tuition = true): array
    {
        $actor = User::factory()->create(['password' => Hash::make('secret')]);
        $department_id = DB::table('departments')->insertGetId(['code' => 'BKK', 'name' => 'Bangkok']);
        $profile = AffiliateProfile::create(['user_id' => $actor->id, 'referral_code' => 'AFF-ADJ-' . $actor->id, 'status' => 'ACTIVE']);
        $contact = Contact::create(['name' => 'Student', 'centre' => $department_id]);
        $attribution = AffiliateAttribution::create(['affiliate_profile_id' => $profile->id, 'contact_id' => $contact->id, 'captured_at' => now(), 'expires_at' => now()->addDay(), 'source_context' => 'course_referral']);
        $order = Order::create(['cid' => $contact->id, 'centre' => $department_id, 'status' => 1]);
        $receipt = Receipt::create(['oid' => $order->id, 'cid' => $contact->id, 'centre' => $department_id, 'receipt_number' => 'R-ADJ-' . $actor->id, 'payment_date' => now()->toDateString(), 'status' => 1, 'total_fee' => '100.00']);
        $commission = AffiliateCommission::create(['affiliate_profile_id' => $profile->id, 'affiliate_attribution_id' => $attribution->id, 'contact_id' => $contact->id, 'order_id' => $order->id, 'receipt_id' => $receipt->id, 'eligible_tuition_minor' => 10000, 'rate_basis_points' => 2000, 'commission_minor' => 2000, 'status' => $status, 'release_at' => now()->addDay(), 'source_key' => 'affiliate:commission:adjustment:' . $actor->id]);
        $credit_note = CreditNote::create(['receipt_id' => $receipt->id, 'centre_id' => $department_id, 'status' => CreditNoteStatus::DRAFT, 'currency' => 'THB']);
        CreditNoteItem::create([
            'credit_note_id' => $credit_note->id,
            'sort_order' => 1,
            'source_type' => CreditNoteSourceType::RECEIPT_DETAIL,
            'source_id' => 1,
            'line_type' => $tuition ? 'tuition' : 'book',
            'description' => $tuition ? 'Tuition' : 'Book',
            'tax_treatment' => 'vat_inclusive',
            'vat_rate_basis_points' => 700,
            'original_quantity' => '1.00',
            'original_unit_price_minor' => 10000,
            'original_discount_minor' => 0,
            'original_line_total_minor' => 10000,
            'credit_quantity' => '1.00',
            'credit_amount_minor' => 5000,
            'credit_taxable_base_minor' => 4673,
            'credit_vat_minor' => 327,
            'credit_exempt_minor' => 0,
            'credit_out_of_scope_minor' => 0,
        ]);
        $credit_note->forceFill(['status' => CreditNoteStatus::ISSUED, 'number' => 'BKKCN2608' . str_pad((string) $actor->id, 3, '0', STR_PAD_LEFT)])->save();

        return [$commission, $credit_note, $actor];
    }
}
