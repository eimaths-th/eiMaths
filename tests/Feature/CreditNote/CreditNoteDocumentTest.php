<?php

namespace Tests\Feature\CreditNote;

use App\Domain\CreditNote\Models\CreditNote;
use App\Models\Department;
use App\Models\Receipt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class CreditNoteDocumentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('financial-documents.credit_note.enabled', true);
    }

    #[Test]
    public function print_document_renders_required_labels_and_status_watermark_without_mutation(): void
    {
        $centre = Department::query()->create(['name' => 'DOC', 'code' => 'DOC', 'status' => 1]);
        $actor = User::factory()->create(['department_id' => $centre->id, 'password' => Hash::make('secret')]);
        $actor->givePermissionTo(Permission::findOrCreate('credit-note-show', 'web'));
        $receipt = Receipt::create(['oid' => null, 'cid' => null, 'centre' => $centre->id, 'receipt_number' => 'DOC-1', 'payment_date' => now()->toDateString(), 'status' => 1, 'total_fee' => '10.00']);
        $note = CreditNote::create(['receipt_id' => $receipt->id, 'centre_id' => $centre->id, 'status' => 'draft', 'currency' => 'THB', 'buyer_name' => '<buyer>', 'reason_details' => "Line one\nLine two"]);
        $before = $note->fresh()->toArray();

        $response = $this->actingAs($actor)->get('/credit-notes/' . $note->id . '/print');
        $response->assertOk()->assertSee('credit-note-print-shell', false)->assertSee('credit-note-paper', false)
            ->assertSee('credit-note-document-header', false)->assertSee('credit-note-print-toolbar', false)
            ->assertSee('credit-note-receipt-header', false)->assertSee('credit-note-orange-table', false)
            ->assertSee('credit-note-receipt-summary', false)->assertSee('credit-note-receipt-banner', false)
            ->assertDontSee('main-sidebar', false)->assertSee('ใบลดหนี้ / Credit Note')->assertSee('DRAFT')
            ->assertSee('&lt;buyer&gt;', false)->assertDontSee('<buyer>', false)->assertSee('Original / Corrected / Difference');
        $this->assertSame($before, $note->fresh()->toArray());
    }

    #[Test]
    public function print_header_uses_receipt_branding_when_issuer_profile_is_empty(): void
    {
        config()->set('financial-documents.credit_note.issuer.legal_name', '');
        config()->set('financial-documents.credit_note.issuer.legal_address', '');
        config()->set('financial-documents.credit_note.issuer.tax_id', '');

        $centre = Department::query()->create(['name' => 'DOC3', 'code' => 'D3C', 'status' => 1]);
        $actor = User::factory()->create(['department_id' => $centre->id, 'password' => Hash::make('secret')]);
        $actor->givePermissionTo(Permission::findOrCreate('credit-note-show', 'web'));
        $receipt = Receipt::create(['oid' => null, 'cid' => null, 'centre' => $centre->id, 'receipt_number' => 'DOC-3', 'payment_date' => now()->toDateString(), 'status' => 1, 'total_fee' => '10.00']);
        $note = CreditNote::create(['receipt_id' => $receipt->id, 'centre_id' => $centre->id, 'status' => 'draft', 'currency' => 'THB']);

        $this->actingAs($actor)->get('/credit-notes/' . $note->id . '/print')
            ->assertOk()
            ->assertSee('บริษัท เมต้า โนวเลจ จำกัด(สำนักงานใหญ่)')
            ->assertSee('555 / 9 หมู่ 1 ตำบลบางขนุน')
            ->assertSee('0125565026368')
            ->assertSee('061 620 8666');
    }

    #[Test]
    public function pdf_stream_is_inline_pdf_for_authorised_user(): void
    {
        $centre = Department::query()->create(['name' => 'DOC2', 'code' => 'DC2', 'status' => 1]);
        $actor = User::factory()->create(['department_id' => $centre->id, 'password' => Hash::make('secret')]);
        $actor->givePermissionTo(Permission::findOrCreate('credit-note-show', 'web'));
        $receipt = Receipt::create(['oid' => null, 'cid' => null, 'centre' => $centre->id, 'receipt_number' => 'DOC-2', 'payment_date' => now()->toDateString(), 'status' => 1, 'total_fee' => '10.00']);
        $note = CreditNote::create(['receipt_id' => $receipt->id, 'centre_id' => $centre->id, 'status' => 'issued', 'number' => 'DOCCN2608001', 'currency' => 'THB']);

        $response = $this->actingAs($actor)->get('/credit-notes/' . $note->id . '/pdf');
        $response->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }
}
