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

class CreditNoteRegisterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('financial-documents.credit_note.enabled', true);
    }

    #[Test]
    public function register_filters_by_status_and_centre_scope(): void
    {
        $centre = Department::query()->create(['name' => 'REG', 'code' => 'REG', 'status' => 1]);
        $other = Department::query()->create(['name' => 'OTHER', 'code' => 'OTH', 'status' => 1]);
        $actor = $this->CreateActor($centre->id, ['credit-note-list']);
        $draft = $this->CreateNote($centre->id, 'draft');
        $issued = $this->CreateNote($centre->id, 'issued');
        $this->CreateNote($other->id, 'issued');

        $response = $this->actingAs($actor)->getJson('/credit-notes?status=issued');
        $response->assertOk()->assertJsonCount(1, 'data.data')->assertJsonPath('data.data.0.id', $issued->id);
        $this->assertNotSame($draft->id, $response->json('data.data.0.id'));
    }

    #[Test]
    public function html_register_escapes_source_text_and_has_textual_status(): void
    {
        $centre = Department::query()->create(['name' => 'REG2', 'code' => 'RG2', 'status' => 1]);
        $actor = $this->CreateActor($centre->id, ['credit-note-list']);
        $note = $this->CreateNote($centre->id, 'draft');
        $note->forceFill(['original_receipt_number' => '<script>alert(1)</script>'])->save();

        $response = $this->actingAs($actor)->get('/credit-notes');
        $response->assertOk()->assertSee('Draft')->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
    }

    private function CreateActor(int $department_id, array $permissions): User
    {
        $actor = User::factory()->create(['department_id' => $department_id, 'password' => Hash::make('secret')]);
        foreach ($permissions as $permission) {
            $actor->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        }
        return $actor;
    }

    private function CreateNote(int $centre_id, string $status): CreditNote
    {
        $receipt = Receipt::create(['oid' => null, 'cid' => null, 'centre' => $centre_id, 'receipt_number' => 'REG-' . uniqid(), 'payment_date' => now()->toDateString(), 'status' => 1, 'total_fee' => '10.00']);
        return CreditNote::create(['receipt_id' => $receipt->id, 'centre_id' => $centre_id, 'status' => $status, 'currency' => 'THB', 'original_receipt_number' => 'R-' . $receipt->id, 'difference_grand_total_minor' => 1000]);
    }
}
