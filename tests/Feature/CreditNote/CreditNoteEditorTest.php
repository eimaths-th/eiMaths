<?php

namespace Tests\Feature\CreditNote;

use App\Domain\CreditNote\Models\CreditNote;
use App\Models\Department;
use App\Models\Receipt;
use App\Models\receipt_details;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class CreditNoteEditorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('financial-documents.credit_note.enabled', true);
    }

    #[Test]
    public function draft_detail_is_editable_has_watermark_and_escapes_buyer_text(): void
    {
        $centre = Department::query()->create(['name' => 'EDIT', 'code' => 'EDT', 'status' => 1]);
        $actor = User::factory()->create(['department_id' => $centre->id, 'password' => Hash::make('secret')]);
        $actor->givePermissionTo(Permission::findOrCreate('credit-note-show', 'web'));
        $actor->givePermissionTo(Permission::findOrCreate('credit-note-edit', 'web'));
        $receipt = Receipt::create(['oid' => null, 'cid' => null, 'centre' => $centre->id, 'receipt_number' => 'EDIT-1', 'payment_date' => now()->toDateString(), 'status' => 1, 'total_fee' => '10.00']);
        $note = CreditNote::create(['receipt_id' => $receipt->id, 'centre_id' => $centre->id, 'status' => 'draft', 'currency' => 'THB', 'buyer_name' => '<b>buyer</b>']);

        $this->actingAs($actor)->get('/credit-notes/' . $note->id)
            ->assertOk()->assertSee('credit-note-page-shell', false)->assertSee('credit-note-editor-header', false)
            ->assertSee('credit-note-workspace-copy', false)
            ->assertSee('DRAFT')->assertSee('&lt;b&gt;buyer&lt;/b&gt;', false)->assertDontSee('<b>buyer</b>', false)
            ->assertSee('lock_version');
    }

    #[Test]
    public function draft_detail_lists_receipt_source_lines_for_selection(): void
    {
        $centre = Department::query()->create(['name' => 'EDIT2', 'code' => 'ED2', 'status' => 1]);
        $actor = User::factory()->create(['department_id' => $centre->id, 'password' => Hash::make('secret')]);
        $actor->givePermissionTo(Permission::findOrCreate('credit-note-show', 'web'));
        $receipt = Receipt::create(['oid' => null, 'cid' => null, 'centre' => $centre->id, 'receipt_number' => 'EDIT-2', 'payment_date' => now()->toDateString(), 'status' => 1, 'total_fee' => '10.00']);
        $detail = new receipt_details();
        $detail->receipt_id = $receipt->id;
        $detail->des = 'Tuition source line';
        $detail->quantity = '1.00';
        $detail->price = '10.00';
        $detail->discount = '0.00';
        $detail->line_type = 'tuition';
        $detail->tax_treatment = 'vat_inclusive';
        $detail->vat_rate_basis_points = 700;
        $detail->save();
        $note = CreditNote::create(['receipt_id' => $receipt->id, 'centre_id' => $centre->id, 'status' => 'draft', 'currency' => 'THB']);

        $this->actingAs($actor)->get('/credit-notes/' . $note->id)
            ->assertOk()
            ->assertSee('Tuition source line')
            ->assertSee('items[0][credit_amount]');
    }

    #[Test]
    public function draft_save_flash_is_visible_after_returning_to_the_editor(): void
    {
        $centre = Department::query()->create(['name' => 'FLASH', 'code' => 'FLS', 'status' => 1]);
        $actor = User::factory()->create(['department_id' => $centre->id, 'password' => Hash::make('secret')]);
        $actor->givePermissionTo(Permission::findOrCreate('credit-note-show', 'web'));
        $actor->givePermissionTo(Permission::findOrCreate('credit-note-edit', 'web'));
        $receipt = Receipt::create([
            'oid' => null,
            'cid' => null,
            'centre' => $centre->id,
            'receipt_number' => 'FLASH-1',
            'payment_date' => now()->toDateString(),
            'status' => 1,
            'total_fee' => '10.00',
        ]);
        $note = CreditNote::create([
            'receipt_id' => $receipt->id,
            'centre_id' => $centre->id,
            'status' => 'draft',
            'currency' => 'THB',
        ]);

        $this->actingAs($actor)
            ->withSession(['success' => 'Credit Note Draft saved.'])
            ->get('/credit-notes/' . $note->id)
            ->assertOk()
            ->assertSee('credit-note-feedback-success', false)
            ->assertSee('Credit Note Draft saved.');

        $this->from('/credit-notes/' . $note->id)
            ->actingAs($actor)
            ->put('/credit-notes/' . $note->id, [
                'lock_version' => 0,
                'buyer_name' => '',
                'buyer_is_vat_registered' => 0,
                'buyer_branch_type' => 'head_office',
                'reason_code' => 'product_return',
                'adjustment_event_date' => '2026-08-12',
                'items' => [],
            ])
            ->assertRedirect('/credit-notes/' . $note->id);

        $this->actingAs($actor)
            ->get('/credit-notes/' . $note->id)
            ->assertOk()
            ->assertSee('credit-note-feedback-error', false)
            ->assertSee('The buyer name field is required.');

        $this->actingAs($actor)
            ->withSession([
                'credit_note_error_code' => 'CREDIT_NOTE_ISSUER_CONFIGURATION_INVALID',
                'errors' => (new ViewErrorBag())->put(
                    'default',
                    new MessageBag(['credit_note' => 'Credit Note issuer configuration is incomplete.']),
                ),
            ])
            ->get('/credit-notes/' . $note->id)
            ->assertOk()
            ->assertSee('credit-note-issuer-config-details', false)
            ->assertSee('CREDIT_NOTE_ISSUER_LEGAL_NAME');
    }
}
