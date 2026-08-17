<?php

namespace Tests\Feature\CreditNote;

use App\Domain\CreditNote\Models\CreditNote;
use App\Models\Department;
use App\Models\Receipt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class CreditNoteExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('financial-documents.credit_note.enabled', true);
    }

    #[Test]
    public function export_uses_list_scope_and_selected_ids_cannot_broaden_centre_scope(): void
    {
        Excel::fake();
        $centre = Department::query()->create(['name' => 'EXP', 'code' => 'EXP', 'status' => 1]);
        $other = Department::query()->create(['name' => 'EXP2', 'code' => 'E2', 'status' => 1]);
        $actor = User::factory()->create(['department_id' => $centre->id, 'password' => Hash::make('secret')]);
        $actor->givePermissionTo(Permission::findOrCreate('credit-note-list', 'web'));
        $visible = $this->CreateNote($centre->id);
        $hidden = $this->CreateNote($other->id);

        $response = $this->actingAs($actor)->get('/credit-notes/export?ids[]=' . $visible->id . '&ids[]=' . $hidden->id);
        $response->assertOk();
        Excel::assertDownloaded('credit-notes.xlsx', function ($export) use ($actor): bool {
            return $export->query()->where('centre_id', $actor->department_id)->count() === 1
                && count($export->headings()) === 22;
        });
    }

    private function CreateNote(int $centre_id): CreditNote
    {
        $receipt = Receipt::create(['oid' => null, 'cid' => null, 'centre' => $centre_id, 'receipt_number' => 'EXP-' . uniqid(), 'payment_date' => now()->toDateString(), 'status' => 1, 'total_fee' => '10.00']);
        return CreditNote::create(['receipt_id' => $receipt->id, 'centre_id' => $centre_id, 'status' => 'issued', 'currency' => 'THB', 'number' => 'EXPCN' . $receipt->id, 'difference_grand_total_minor' => 1234]);
    }
}
