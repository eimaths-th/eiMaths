<?php

namespace Tests\Feature\Localization;

use App\Domain\CreditNote\Models\CreditNote;
use App\Models\Department;
use App\Models\Receipt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class CreditNoteLocalizedPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('financial-documents.credit_note.enabled', true);
    }

    public static function LocalizedCreditNoteLabels(): array
    {
        return [
            ['en', 'Credit Note register', 'Refresh', 'Financial documents', 'Buyer profile', 'Source receipt lines', 'Audit trail', 'Saving Credit Note Draft…', 'Print', 'Close'],
            ['th', 'ทะเบียนใบลดหนี้', 'รีเฟรช', 'เอกสารการเงิน', 'ข้อมูลผู้ซื้อ', 'รายการจากใบเสร็จต้นทาง', 'ประวัติการตรวจสอบ', 'กำลังบันทึกฉบับร่างใบลดหนี้…', 'พิมพ์', 'ปิด'],
        ];
    }

    #[Test]
    #[DataProvider('LocalizedCreditNoteLabels')]
    public function credit_note_register_detail_and_print_use_the_selected_locale(
        string $locale,
        string $register_label,
        string $refresh_label,
        string $financial_documents_label,
        string $buyer_profile_label,
        string $source_lines_label,
        string $audit_trail_label,
        string $saving_label,
        string $print_label,
        string $close_label,
    ): void {
        $centre = Department::query()->create(['name' => 'CN Locale', 'code' => 'CNL', 'status' => 1]);
        $actor = User::factory()->create([
            'department_id' => $centre->id,
            'language_code' => $locale,
            'disable' => 0,
            'password' => Hash::make('secret'),
        ]);
        foreach (['credit-note-list', 'credit-note-show', 'credit-note-edit'] as $permission) {
            $actor->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        }

        $receipt = Receipt::create([
            'oid' => null,
            'cid' => null,
            'centre' => $centre->id,
            'receipt_number' => 'CN-LOCALE-1',
            'payment_date' => now()->toDateString(),
            'status' => 1,
            'total_fee' => '10.00',
        ]);
        $credit_note = CreditNote::create([
            'receipt_id' => $receipt->id,
            'centre_id' => $centre->id,
            'status' => 'draft',
            'currency' => 'THB',
            'original_receipt_number' => 'CN-LOCALE-1',
        ]);

        $this->actingAs($actor)->get(route('credit-notes.index'))
            ->assertOk()
            ->assertSee($register_label)
            ->assertSee($refresh_label);

        $this->actingAs($actor)->get(route('credit-notes.show', $credit_note))
            ->assertOk()
            ->assertSee($financial_documents_label)
            ->assertSee($buyer_profile_label)
            ->assertSee($source_lines_label)
            ->assertSee($audit_trail_label)
            ->assertSee('data-saving-message="' . e($saving_label) . '"', false)
            ->assertSee($print_label)
            ->assertSee('credit-note-editor', false);

        $this->actingAs($actor)->get(route('credit-notes.print', $credit_note))
            ->assertOk()
            ->assertSee($print_label)
            ->assertSee($close_label);
    }

}
