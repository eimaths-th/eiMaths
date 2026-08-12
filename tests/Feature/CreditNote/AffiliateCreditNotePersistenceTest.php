<?php

namespace Tests\Feature\CreditNote;

use App\Domain\Affiliate\Enums\AffiliateCommissionStatus;
use App\Domain\Affiliate\Enums\AffiliateLedgerEntryType;
use App\Domain\Affiliate\Models\AffiliateAttribution;
use App\Domain\Affiliate\Models\AffiliateCommission;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Domain\CreditNote\Enums\AffiliateCreditNoteEventType;
use App\Domain\CreditNote\Enums\CreditNoteStatus;
use App\Domain\CreditNote\Models\CreditNote;
use App\Domain\Affiliate\Models\AffiliateCommissionCreditNoteAdjustment;
use App\Models\Contact;
use App\Models\Order;
use App\Models\Receipt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AffiliateCreditNotePersistenceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function PersistsSignedAdjustmentEvidenceAndNamedLedgerTypes(): void
    {
        $user = User::factory()->create(['password' => Hash::make('secret')]);
        $department_id = DB::table('departments')->insertGetId(['code' => 'BKK', 'name' => 'Bangkok']);
        $profile = AffiliateProfile::create(['user_id' => $user->id, 'referral_code' => 'AFF-CN-' . $user->id, 'status' => 'ACTIVE']);
        $contact = Contact::create(['name' => 'Student', 'centre' => $department_id]);
        $attribution = AffiliateAttribution::create(['affiliate_profile_id' => $profile->id, 'contact_id' => $contact->id, 'captured_at' => now(), 'expires_at' => now()->addDay(), 'source_context' => 'course_referral']);
        $order = Order::create(['cid' => $contact->id, 'status' => 1]);
        $receipt = Receipt::create(['oid' => $order->id, 'cid' => $contact->id, 'centre' => $department_id, 'receipt_number' => 'R-CN', 'payment_date' => now()->toDateString(), 'status' => 1, 'total_fee' => '100.00']);
        $commission = AffiliateCommission::create(['affiliate_profile_id' => $profile->id, 'affiliate_attribution_id' => $attribution->id, 'contact_id' => $contact->id, 'order_id' => $order->id, 'receipt_id' => $receipt->id, 'eligible_tuition_minor' => 10000, 'rate_basis_points' => 2000, 'commission_minor' => 2000, 'status' => AffiliateCommissionStatus::AVAILABLE, 'release_at' => now()->addDay(), 'source_key' => 'affiliate:commission:test:' . $user->id]);
        $credit_note = CreditNote::create(['receipt_id' => $receipt->id, 'centre_id' => $department_id, 'status' => CreditNoteStatus::ISSUED, 'number' => 'BKKCN2608001', 'currency' => 'THB']);

        $adjustment = AffiliateCommissionCreditNoteAdjustment::create([
            'affiliate_commission_id' => $commission->id,
            'credit_note_id' => $credit_note->id,
            'event_type' => AffiliateCreditNoteEventType::ISSUE,
            'tuition_reversal_delta_minor' => 5000,
            'commission_reversal_delta_minor' => 1000,
            'status_before' => AffiliateCommissionStatus::AVAILABLE,
            'status_after' => AffiliateCommissionStatus::AVAILABLE,
            'idempotency_key' => 'credit-note:affiliate:issue:' . $credit_note->id,
            'recorded_by' => $user->id,
            'occurred_at' => now(),
        ]);

        $this->assertSame(5000, $adjustment->fresh()->tuition_reversal_delta_minor);
        $this->assertSame(AffiliateCreditNoteEventType::ISSUE, $adjustment->fresh()->event_type);
        $this->assertSame(1, $commission->fresh()->CreditNoteAdjustments()->count());
        $this->assertSame(1, $credit_note->fresh()->AffiliateAdjustments()->count());
        $this->assertSame(AffiliateLedgerEntryType::CREDIT_NOTE_COMMISSION_REVERSAL, AffiliateLedgerEntryType::from('CREDIT_NOTE_COMMISSION_REVERSAL'));
        $this->assertSame(AffiliateLedgerEntryType::CREDIT_NOTE_COMMISSION_RESTORATION, AffiliateLedgerEntryType::from('CREDIT_NOTE_COMMISSION_RESTORATION'));

        $this->expectException(\LogicException::class);
        $adjustment->commission_reversal_delta_minor = 1;
        $adjustment->save();
    }
}
