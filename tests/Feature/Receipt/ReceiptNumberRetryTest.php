<?php

namespace Tests\Feature\Receipt;

use App\Models\Contact;
use App\Models\Department;
use App\Models\FileUpload;
use App\Models\Order;
use App\Models\Receipt;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use PDOException;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ReceiptNumberRetryTest extends TestCase
{
    use DatabaseMigrations;

    private ?string $temporary_public_path = null;

    public function runDatabaseMigrations(): void
    {
        if (config('database.default') !== 'sqlite' || config('database.connections.sqlite.database') !== ':memory:') {
            throw new \RuntimeException('Retry tests may migrate only the isolated in-memory SQLite database.');
        }
        $this->beforeRefreshingDatabase();
        $this->refreshTestDatabase();
        $this->afterRefreshingDatabase();
        // This disposable database needs no down migrations; consent history cannot be rolled back.
        $this->beforeApplicationDestroyed(function (): void {
            RefreshDatabaseState::$migrated = false;
        });
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->assertSame(0, DB::transactionLevel(), 'Exercise the real outer transaction retry policy.');
        Http::preventStrayRequests();
        Mail::fake();
        Notification::fake();
        $this->temporary_public_path = storage_path('framework/testing/receipt-retry-isolation-'.bin2hex(random_bytes(8)));
        File::makeDirectory($this->temporary_public_path.'/file_upload', 0755, true);
        File::makeDirectory($this->temporary_public_path.'/file_store', 0755, true);
        $this->app->usePublicPath($this->temporary_public_path);
        Department::create(['id' => 1, 'name' => 'HQ', 'code' => 'HQ', 'status' => 1]);
    }

    protected function tearDown(): void
    {
        if ($this->temporary_public_path !== null) {
            $allowed_path = realpath(storage_path('framework/testing'));
            $resolved_path = realpath($this->temporary_public_path);
            if ($allowed_path !== false && $resolved_path !== false
                && str_starts_with($resolved_path, $allowed_path.DIRECTORY_SEPARATOR)
                && str_starts_with(basename($resolved_path), 'receipt-retry-isolation-')) {
                File::deleteDirectory($resolved_path);
            }
        }
        parent::tearDown();
    }

    #[Test]
    public function ATransientReceiptWriteConflictRetriesAndMovesTheAttachmentOnce(): void
    {
        [$actor, $receipt, $upload, $payload] = $this->CreateEditableReceiptWithUpload();
        $write_attempts = 0;
        $this->InjectReceiptWriteConflicts($write_attempts, 1);
        File::partialMock()->shouldReceive('move')->once()->passthru();

        $response = $this->actingAs($actor)->putJson(route('receipts.save', $receipt->id), $payload);

        $response->assertOk()->assertJsonMissingPath('errors');
        $this->assertSame(2, $write_attempts);
        $this->assertSame(1, DB::table('branch_audit_logs')->where('category', 'activity')
            ->where('subject_type', Receipt::class)->where('subject_id', $receipt->id)->count());
        $this->assertDatabaseMissing('branch_audit_logs', ['action' => 'request.failed']);
        $this->assertSame(250, (int) $receipt->fresh()->total_fee);
        $this->assertDatabaseCount('receipt_details', 1);
        $this->assertDatabaseHas('file_stores', ['module_id' => $receipt->oid, 'filename' => 'owned-retry.txt']);
        $this->assertDatabaseMissing('file_uploads', ['id' => $upload->id]);
        $this->assertFileDoesNotExist(public_path('file_upload/owned-retry.txt'));
        $this->assertSame('Owned retry attachment', file_get_contents(public_path('file_store/owned-retry.txt')));
    }

    #[Test]
    public function ARepeatedReceiptWriteConflictStopsAfterThreeAttemptsWithoutMovingFiles(): void
    {
        [$actor, $receipt, $upload, $payload] = $this->CreateEditableReceiptWithUpload();
        $write_attempts = 0;
        $this->InjectReceiptWriteConflicts($write_attempts, PHP_INT_MAX);
        File::partialMock()->shouldReceive('move')->never();

        $this->actingAs($actor)->putJson(route('receipts.save', $receipt->id), $payload)->assertStatus(500);

        $this->assertSame(3, $write_attempts);
        $this->assertSame(0, DB::table('branch_audit_logs')->where('category', 'activity')->where('outcome', 'success')->count());
        $this->assertSame(1, DB::table('branch_audit_logs')->where('action', 'request.failed')->count());
        $this->assertSame(100, (int) $receipt->fresh()->total_fee);
        $this->assertDatabaseCount('receipt_details', 0);
        $this->assertDatabaseCount('file_stores', 0);
        $this->assertDatabaseHas('file_uploads', ['id' => $upload->id]);
        $this->assertSame('Owned retry attachment', file_get_contents(public_path('file_upload/owned-retry.txt')));
        $this->assertFileDoesNotExist(public_path('file_store/owned-retry.txt'));
    }

    private function InjectReceiptWriteConflicts(int &$write_attempts, int $conflict_count): void
    {
        DB::connection()->beforeExecuting(function (string $query, array $bindings) use (&$write_attempts, $conflict_count): void {
            if (!str_starts_with(strtolower(ltrim($query)), 'update "receipts"')) {
                return;
            }
            $write_attempts++;
            if ($write_attempts <= $conflict_count) {
                $database_error = new PDOException('Simulated serialization conflict', 40001);
                $database_error->errorInfo = ['40001', 1213, 'Simulated serialization conflict'];
                throw new QueryException('sqlite', $query, $bindings, $database_error);
            }
        });
    }

    private function CreateEditableReceiptWithUpload(): array
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
        $upload = FileUpload::forceCreate([
            'oldname' => 'owned.txt', 'filename' => 'owned-retry.txt',
            'branch_id' => $branch->id, 'uploaded_by' => $actor->id,
        ]);
        file_put_contents(public_path('file_upload/owned-retry.txt'), 'Owned retry attachment');
        $payload = [
            'receipt_number' => 'ORIGINAL-RECEIPT', 'receipt_date' => '2026-10-06',
            'total' => 250, 'payment' => 1, 'des' => ['Edited tuition'],
            'amount' => [1], 'price' => [250], 'unit' => ['course'],
            'discount' => [0], 'tax' => [1], 'img' => ['owned.txt'],
        ];
        return [$actor, $receipt, $upload, $payload];
    }
}
