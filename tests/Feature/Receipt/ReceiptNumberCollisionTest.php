<?php

namespace Tests\Feature\Receipt;

use App\Models\Contact;
use App\Models\Department;
use App\Models\Order;
use App\Models\Receipt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ReceiptNumberCollisionTest extends TestCase
{
    use RefreshDatabase;

    private ?string $temporary_public_path = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        Http::preventStrayRequests();
        Mail::fake();
        Notification::fake();
        Department::create(['id' => 1, 'name' => 'HQ', 'code' => 'HQ', 'branch_type' => 'hq', 'status' => 1]);
    }

    protected function tearDown(): void
    {
        if ($this->temporary_public_path !== null) {
            $allowed_path = realpath(storage_path('framework/testing'));
            $resolved_path = realpath($this->temporary_public_path);
            if ($allowed_path !== false && $resolved_path !== false
                && str_starts_with($resolved_path, $allowed_path.DIRECTORY_SEPARATOR)
                && str_starts_with(basename($resolved_path), 'receipt-upload-isolation-')) {
                File::deleteDirectory($resolved_path);
            }
        }
        parent::tearDown();
    }

    #[Test]
    public function ReceiptEditorRejectsANumberAlreadyIssuedInItsBranch(): void
    {
        [$actor, $receipt] = $this->CreateEditableReceipt();
        Receipt::create(['centre' => $receipt->centre, 'receipt_number' => 'ALREADY-ISSUED']);

        $response = $this->actingAs($actor)->putJson(route('receipts.save', $receipt->id), $this->EditPayload('ALREADY-ISSUED'));

        $response->assertOk()->assertJsonStructure(['errors'])->assertJsonMissing(['success' => 'Save Data completed']);
        $this->assertSame('ORIGINAL-RECEIPT', $receipt->fresh()->receipt_number);
        $this->assertDatabaseCount('receipt_details', 0);
    }

    #[Test]
    public function UnchangedLegacyDuplicateNumbersRemainEditableWithoutRenumbering(): void
    {
        [$actor, $receipt] = $this->CreateEditableReceipt();
        $legacy_receipt_id = DB::table('receipts')->insertGetId([
            'centre' => $receipt->centre, 'receipt_number' => 'ORIGINAL-RECEIPT',
        ]);

        $this->actingAs($actor)->putJson(route('receipts.save', $receipt->id), $this->EditPayload('ORIGINAL-RECEIPT'))
            ->assertOk()->assertJsonMissingPath('errors');

        $this->assertSame('ORIGINAL-RECEIPT', $receipt->fresh()->receipt_number);
        $this->assertDatabaseHas('receipts', ['id' => $legacy_receipt_id, 'receipt_number' => 'ORIGINAL-RECEIPT']);
        $this->assertDatabaseCount('receipt_details', 1);
    }

    #[Test]
    public function ReceiptNumberValidationUsesTheDocumentBranchWhenHqEditsIt(): void
    {
        [$actor, $receipt] = $this->CreateEditableReceipt();
        $hq = Department::findOrFail(1);
        $actor->department_id = $hq->id;
        $actor->save();
        $actor->givePermissionTo(Permission::findOrCreate('all-centre', 'web'));
        $actor->assignRole(\Spatie\Permission\Models\Role::findOrCreate('Admin', 'web'));
        Receipt::create(['centre' => $receipt->centre, 'receipt_number' => 'BRANCH-ISSUED']);

        $this->actingAs($actor)->putJson(route('receipts.save', $receipt->id), $this->EditPayload('BRANCH-ISSUED'))
            ->assertOk()->assertJsonStructure(['errors']);

        $this->assertSame('ORIGINAL-RECEIPT', $receipt->fresh()->receipt_number);
        $this->assertDatabaseCount('receipt_details', 0);
    }

    #[Test]
    public function ACompletedAttachmentKeepsItsAssociationWhenALaterAttachmentFails(): void
    {
        [$actor, $receipt] = $this->CreateEditableReceipt();
        $this->temporary_public_path = storage_path('framework/testing/receipt-upload-isolation-'.bin2hex(random_bytes(8)));
        File::makeDirectory($this->temporary_public_path.'/file_upload', 0755, true);
        File::makeDirectory($this->temporary_public_path.'/file_store', 0755, true);
        $this->app->usePublicPath($this->temporary_public_path);
        $upload = \App\Models\FileUpload::forceCreate([
            'oldname' => 'first.txt', 'filename' => 'first-stored.txt',
            'branch_id' => $receipt->centre, 'uploaded_by' => $actor->id,
        ]);
        $source_path = public_path('file_upload/first-stored.txt');
        $destination_path = public_path('file_store/first-stored.txt');
        file_put_contents($source_path, 'Owned test attachment');
        $payload = $this->EditPayload('ORIGINAL-RECEIPT');
        $payload['img'] = ['first.txt', 'missing.txt'];

        $this->actingAs($actor)->putJson(route('receipts.save', $receipt->id), $payload)->assertStatus(500);

        $this->assertFileExists($destination_path);
        $this->assertFileDoesNotExist($source_path);
        $this->assertDatabaseHas('file_stores', [
            'module' => 'orders', 'module_id' => $receipt->oid, 'filename' => 'first-stored.txt',
        ]);
        $this->assertDatabaseMissing('file_uploads', ['id' => $upload->id]);
        $this->assertDatabaseCount('receipt_details', 1);
    }

    private function CreateEditableReceipt(): array
    {
        $branch = Department::create(['name' => 'Branch A', 'code' => 'AA', 'status' => 1]);
        $actor = User::factory()->create(['department_id' => $branch->id, 'disable' => 0, 'password' => bcrypt('test-password')]);
        $actor->givePermissionTo(Permission::findOrCreate('receipt-edit', 'web'));
        $student = Contact::create(['centre' => $branch->id, 'code' => 'AA0001', 'name' => 'Receipt Student']);
        $order = Order::create(['centre' => $branch->id, 'cid' => $student->id, 'order_number' => 'ORDER-1', 'total_price' => 100]);
        $receipt = Receipt::create([
            'centre' => $branch->id, 'cid' => $student->id, 'oid' => $order->id,
            'receipt_number' => 'ORIGINAL-RECEIPT', 'student_name' => 'Receipt Student',
            'payment_date' => '2026-10-06', 'total_fee' => 100,
        ]);
        return [$actor, $receipt];
    }

    private function EditPayload(string $receipt_number): array
    {
        return [
            'receipt_number' => $receipt_number, 'receipt_date' => '2026-10-06',
            'total' => 100, 'payment' => 1, 'des' => ['Edited tuition'],
            'amount' => [1], 'price' => [100], 'unit' => ['course'],
            'discount' => [0], 'tax' => [1],
        ];
    }
}
