# Bulk Branch Transfers Implementation Plan

> **REQUIRED EXECUTION SKILL:** After the user approves this plan, use `executing-plans` for task-by-task execution.

Status: Approved for execution on `main` in conversation on 2026-09-21. Implementation is present in the working tree. Focused verification passed; the full PHPUnit suite remains non-green and is recorded in the Task 6 handoff.

- **Goal:** Transfer up to 100 explicitly selected students or users to one branch in one atomic operation, using basic filters and a paginated checkbox table.
- **Architecture:** Extend the existing controller and routes; share transfer rules in one application service; retain existing branch guards and audits; isolate browser selection/request state from DOM rendering.
- **Tech Stack:** Existing Laravel 10, PHP 8.1-compatible source, Blade, native JavaScript modules, PHPUnit 10, and Node's built-in test runner. No new packages.
- **Source:** `docs/specs/0002-bulk-branch-transfers-design.md`, explicitly approved in conversation on 2026-09-21.

Execution evidence (2026-09-21): the focused PHP command passed with 48 tests and 17,042 assertions; all JavaScript tests passed (82 tests), including syntax checks for both branch-transfer modules. The full PHPUnit command completed with 755 tests, 21,146 assertions, 1 error, 55 failures, and 3 skipped tests; the known stock-workflow logging error and other non-feature failures remain outside this implementation's focused verification. No full-suite pass is claimed.

## Global Constraints

The following requirements are copied from the approved specification:

- "Students and users, one type per batch"
- "Source branch and name search; student search also matches student code"
- "Retain selection across pages; clear it when the record type or applied filters change"
- "All selected records transfer successfully together, or none transfer"
- "25 records per page and at most 100 selected records per batch"
- "Do not duplicate business rules between single-record and batch processing."
- "The expected-source map is a concurrency check, never evidence of authorization."
- "Return success only after commit."
- "Render names, email addresses, codes, and reasons as text rather than executable HTML."

One plan is appropriate: candidate search, selection, review, and persistence are dependent stages of the same approved transfer workflow, not independently requested subsystems. Do not add advanced filters, background jobs, database migrations, live transfer probes, external writes, commits, or unrelated fixes.

Preserve existing route action names and PSR-4 class filenames. Use PascalCase for new functions/methods, snake_case for local identifiers and payload fields, and kebab-case for browser files and DOM identifiers. Document prose stays English; escaped Unicode in translation code represents Thai runtime strings.

Before execution, use `git-worktrees`, then `executing-plans` as required by `writing-plans`. Preserve these approved documents and any user edits when isolating work. Do not prepare a worktree before this plan is approved.

## Verified baseline and commands

Repository revision inspected: `62fa4935`. Before planning, the only untracked file was the new approved specification. No implementation file was modified.

- `php --version`: PHP 8.2.12 at `C:\xampp\php\php.exe`.
- `node --version`: v24.21.0. The repository already declares `type: module` and uses `node:test`.
- `php vendor/phpunit/phpunit/phpunit --filter 'BranchTransferLocalizedPageTest|MenuOrganizationTest|UiTranslationStructureTest|test_student_transfer_preserves_issued_document_branch_and_clears_current_enrolment|test_transfer_search_hides_central_accounts_and_finds_branch_staff_by_name' --do-not-cache-result`: exit 0, 30 tests, 16,712 assertions.
- `node --test tests/JavaScript/book-shipment-alert-poller.test.mjs`: exit 0, one test passed.
- The broader baseline command `php vendor/phpunit/phpunit/phpunit --filter 'BranchTransferLocalizedPageTest|BranchWorkflowsTest|MenuOrganizationTest|UiTranslationStructureTest' --do-not-cache-result` exited 1: 53 tests, 17,347 assertions, one error in `BranchWorkflowsTest::test_stock_adjustment_error_is_written_to_the_application_log_without_mutation`. Its expected warning log was called zero times. This is observed before implementation; its underlying cause is not established by this task. Do not change that test or its stock implementation without authorization.
- `phpunit.xml` forces SQLite `:memory:`; `bootstrap/cache/config.php` is absent. `.github/workflows/tests.yml` runs the full PHPUnit suite. Use `--do-not-cache-result` for local commands.
- `EnforceBranchContext::handle` scopes requests; `BranchServiceProvider::boot` installs save/audit hooks. `BranchAccess::assertBranch`, `assertManageUser`, and `assertAssignableRole` remain the security boundaries.
- The layout supplies `meta[name=csrf-token]` and `@yield('script')`. Existing pages load JavaScript modules from `public/js`; no Vite bundle change is needed.

Commands for new test paths below use these verified runners. Their expected failures and successes are execution criteria, not claimed planning results.

## Task 1: Paginated, scoped candidate search

**Files:**

- Modify: `config/branch.php` at the returned configuration array.
- Modify: `app/Http/Controllers/BranchTransferController.php` at `targets`.
- Create/Test: `tests/Feature/Branch/BranchTransferBulkTest.php`.

- **Consumes:** Existing routes, model scopes, and `BranchAccess::assertBranch`.
- **Produces:** Opt-in `paginated=1` candidate response with fixed 25-row pages; unchanged legacy search response.
- **Checkpoint:** None after plan approval. Stop if an existing scope or response contract differs from this verified baseline.

### Production behavior changes

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development` and its `writing-good-tests.md` reference.

- [ ] Create the test class below, initially with only `SourceOnlySearchPaginatesWithoutLeakingOtherBranches`.
- [ ] Run `php vendor/phpunit/phpunit/phpunit tests/Feature/Branch/BranchTransferBulkTest.php --filter SourceOnlySearchPaginatesWithoutLeakingOtherBranches --do-not-cache-result`. Expect HTTP 422 instead of 200 because the old endpoint requires `q`.
- [ ] Add the configuration and replace `targets` with the complete method below. Run the same command; require exit 0 and a 25/6 split of 31 source-branch records.
- [ ] Add `CodeSearchStaysInsideTheSourceAndLegacyShapeIsPreserved` and run it. If it already passes, label it compatibility characterization, not RED. Run both new tests and the existing transfer-search test.
- [ ] Refactor only while these checks pass.

Test class; later tasks append methods to this same class before its closing brace:

```php
<?php

namespace Tests\Feature\Branch;

use App\Models\Contact;
use App\Models\Department;
use App\Models\Order;
use App\Models\Position;
use App\Models\User;
use App\Support\Branch\BranchContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BranchTransferBulkTest extends TestCase
{
    use RefreshDatabase;

    private Department $source_branch;
    private Department $destination_branch;
    private User $actor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        Http::preventStrayRequests();
        Mail::fake();
        Notification::fake();
        $headquarters = Department::forceCreate([
            'id' => 1, 'name' => 'HQ', 'code' => 'HQ', 'status' => 1, 'branch_type' => 'hq',
        ]);
        $this->source_branch = Department::create(['name' => 'Source', 'code' => 'SRC', 'status' => 1]);
        $this->destination_branch = Department::create(['name' => 'Destination', 'code' => 'DST', 'status' => 1]);
        $this->actor = User::factory()->create([
            'department_id' => $headquarters->id, 'disable' => 0, 'language_code' => 'en',
        ]);
        $role = Role::findOrCreate('SystemAdmin', 'web');
        $role->givePermissionTo(Permission::findOrCreate('branch-transfer', 'web'));
        $this->actor->assignRole($role);
    }

    private function Student(string $name = 'Student', ?int $branch_id = null): Contact
    {
        return Contact::create(['name' => $name, 'centre' => $branch_id ?? $this->source_branch->id]);
    }

    private function Payload(array $records, string $type = 'student'): array
    {
        $sources = [];
        foreach ($records as $record) {
            $sources[$record->id] = (int) ($type === 'user' ? $record->department_id : $record->centre);
        }
        return [
            'type' => $type,
            'record_ids' => array_map(fn ($record) => $record->id, $records),
            'expected_from_branch_ids' => $sources,
            'to_branch_id' => $this->destination_branch->id,
            'reason' => 'Moving to another branch',
        ];
    }

    #[Test]
    public function SourceOnlySearchPaginatesWithoutLeakingOtherBranches(): void
    {
        for ($student_index = 1; $student_index <= 31; $student_index++) {
            $this->Student(sprintf('Student %02d', $student_index));
        }
        $this->Student('Foreign Student', $this->destination_branch->id);
        $url = '/branch-transfers/targets?paginated=1&type=student&from_branch_id='.$this->source_branch->id;
        $this->actingAs($this->actor)->getJson($url)
            ->assertOk()->assertJsonCount(25, 'data')->assertJsonPath('meta.total', 31)
            ->assertJsonPath('data.0.name', 'Student 01');
        $this->getJson($url.'&page=2')->assertOk()->assertJsonCount(6, 'data')
            ->assertJsonPath('data.0.name', 'Student 26');
    }

    #[Test]
    public function CodeSearchStaysInsideTheSourceAndLegacyShapeIsPreserved(): void
    {
        $local = $this->Student('Local Student');
        $local->update(['code' => 'MATCH-01']);
        $foreign = $this->Student('Foreign Student', $this->destination_branch->id);
        $foreign->update(['code' => 'MATCH-02']);
        $url = '/branch-transfers/targets?paginated=1&type=student&q=MATCH&from_branch_id='.$this->source_branch->id;
        $this->actingAs($this->actor)->getJson($url)->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $local->id);
        $this->getJson('/branch-transfers/targets?type=student&q=Local')->assertExactJson([
            'data' => [['id' => $local->id, 'label' => 'Local Student (MATCH-01)'." \u{2014} ".'Source']],
        ]);
        $this->getJson('/branch-transfers/targets?type=student')->assertUnprocessable();
    }
}
```

Add this entry to the existing array in `config/branch.php`:

```php
'transfers' => ['page_size' => 25, 'max_batch_size' => 100],
```

Complete replacement for `BranchTransferController::targets`:

```php
public function targets(Request $request)
{
    abort_unless(BranchAccess::isCentral($request->user()), 403);
    $request->validate(['paginated' => 'sometimes|boolean']);
    $is_paginated = $request->boolean('paginated');
    if ($is_paginated && is_string($request->input('q'))) {
        $search_query = trim($request->input('q'));
        $request->merge(['q' => $search_query === '' ? null : $search_query]);
    }
    $filters = $request->validate([
        'type' => 'required|in:user,student',
        'q' => ($is_paginated ? 'nullable' : 'required').'|string|min:2|max:100',
        'from_branch_id' => $is_paginated ? 'nullable|integer|min:1' : 'exclude',
        'page' => $is_paginated ? 'sometimes|integer|min:1' : 'exclude',
    ]);
    $is_user = $filters['type'] === 'user';
    $query = $is_user
        ? User::query()->whereDoesntHave('roles', fn ($roles) => $roles->whereIn('name', ['SystemAdmin', 'Admin']))
        : Contact::query();
    $branch_column = $is_user ? 'department_id' : 'centre';
    if (isset($filters['from_branch_id'])) {
        BranchAccess::assertBranch($request->user(), $filters['from_branch_id']);
        $query->where($branch_column, $filters['from_branch_id']);
    }
    if (isset($filters['q'])) {
        $query->where(function ($names) use ($filters, $is_user) {
            $names->where('name', 'like', '%'.$filters['q'].'%');
            if (!$is_user) {
                $names->orWhere('code', 'like', '%'.$filters['q'].'%');
            }
        });
    }
    $query->orderBy('name')->orderBy('id');
    $page = $is_paginated ? $query->paginate((int) config('branch.transfers.page_size')) : null;
    $records = $page ? $page->getCollection() : $query->limit(30)->get();
    $branches = Department::whereIn('id', $records->pluck($branch_column))->pluck('name', 'id');
    $results = $records->map(function ($record) use ($is_user, $branch_column, $branches, $is_paginated) {
        $branch_name = $branches[$record->$branch_column] ?? '';
        $result = [
            'id' => $record->id,
            'label' => $record->name.(!$is_user ? ' ('.$record->code.')' : '')." \u{2014} ".$branch_name,
        ];
        return $is_paginated ? $result + [
            'name' => $record->name,
            'code' => $is_user ? null : $record->code,
            'email' => $is_user ? $record->email : null,
            'from_branch_id' => (int) $record->$branch_column,
            'from_branch_name' => $branch_name,
        ] : $result;
    });
    $response = ['data' => $results];
    if ($page) {
        $response['meta'] = [
            'current_page' => $page->currentPage(), 'per_page' => $page->perPage(),
            'total' => $page->total(), 'last_page' => $page->lastPage(),
        ];
    }
    return response()->json($response);
}
```

## Task 2: One shared atomic transfer operation

**Files:**

- Create: `app/Support/Branch/BranchTransferRejected.php`.
- Create: `app/Support/Branch/BranchTransferService.php`.
- Modify: `app/Http/Controllers/BranchTransferController.php` at `store`; add `StoreBatch`.
- Modify/Test: `tests/Feature/Branch/BranchTransferBulkTest.php`.

- **Consumes:** Task 1 limits, existing permission guards, model hooks, and legacy store validation.
- **Produces:** `BranchTransferService::Transfer(User $actor, array $input, bool $is_batch): array`; batch JSON/redirect responses and unchanged legacy responses.
- **Checkpoint:** Do not proceed to UI integration while atomicity, source conflicts, or compatibility checks fail.

### Production behavior changes

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Append the tests below one behavior at a time. Run `php vendor/phpunit/phpunit/phpunit tests/Feature/Branch/BranchTransferBulkTest.php --do-not-cache-result` after each newly added method. The first student batch test must fail because the existing route requires `record_id`. Subsequent RED cycles must expose the relevant missing guard/side effect, not test setup errors.
- [ ] Install only the service/controller portions needed for each observed failure. The complete target code below defines the final implementation; do not install untested branches ahead of their owning cycle.
- [ ] After each cycle, rerun the named test and the existing legacy transfer/localization tests. Preserve their original status codes and response shapes.
- [ ] Proceed to Task 3 for the catalogs and independent localized-text assertions; do not begin UI integration until both tasks pass.
- [ ] Require the complete `BranchTransferBulkTest` to pass, including the later persistence-failure and security cases. Refactor only while checks remain green.

Append these tests:

```php
#[Test]
public function StudentsMoveTogetherAndKeepIssuedOrdersAtTheSource(): void
{
    $students = [$this->Student('First'), $this->Student('Second')];
    $orders = [];
    foreach ($students as $student) {
        $order = Order::create(['cid' => $student->id, 'centre' => $this->source_branch->id, 'status' => 2]);
        $student->update(['order' => $order->id]);
        $orders[] = $order;
    }
    $this->actingAs($this->actor)->postJson('/branch-transfers', $this->Payload($students))
        ->assertOk()->assertJsonPath('transferred_count', 2)->assertJsonCount(2, 'data');
    foreach ($students as $student) {
        $this->assertSame($this->destination_branch->id, (int) $student->fresh()->centre);
        $this->assertSame(0, (int) $student->fresh()->order);
    }
    foreach ($orders as $order) {
        $this->assertSame($this->source_branch->id, (int) $order->fresh()->centre);
    }
    $this->assertDatabaseCount('branch_transfers', 2);
}

#[Test]
public function AllBlockedStudentsAreReportedWithoutMovingAnyone(): void
{
    $students = [$this->Student('Ready'), $this->Student('Pending'), $this->Student('Unknown status'), $this->Student('Zero status')];
    Order::create(['cid' => $students[1]->id, 'centre' => $this->source_branch->id, 'status' => 1]);
    Order::create(['cid' => $students[2]->id, 'centre' => $this->source_branch->id, 'status' => null]);
    Order::create(['cid' => $students[3]->id, 'centre' => $this->source_branch->id, 'status' => 0]);
    $this->actingAs($this->actor)->postJson('/branch-transfers', $this->Payload($students))
        ->assertUnprocessable()->assertJsonCount(3, 'record_errors')
        ->assertJsonPath('record_errors.0.code', 'PENDING_ORDERS')
        ->assertJsonPath('record_errors.1.code', 'PENDING_ORDERS')
        ->assertJsonPath('record_errors.2.code', 'PENDING_ORDERS');
    foreach ($students as $student) {
        $this->assertSame($this->source_branch->id, (int) $student->fresh()->centre);
    }
    $this->assertDatabaseCount('branch_transfers', 0);
}

#[Test]
public function StaleSourcesAndRepeatedRequestsDoNotWriteAnotherTransfer(): void
{
    $first = $this->Student('First');
    $second = $this->Student('Second');
    $payload = $this->Payload([$first, $second]);
    DB::table('contacts')->where('id', $second->id)->update(['centre' => $this->destination_branch->id]);
    $this->actingAs($this->actor)->postJson('/branch-transfers', $payload)->assertStatus(409)
        ->assertJsonPath('record_errors.0.code', 'SOURCE_CHANGED');
    $this->assertSame($this->source_branch->id, (int) $first->fresh()->centre);
    $this->assertDatabaseCount('branch_transfers', 0);
    $single = $this->Payload([$first]);
    $this->postJson('/branch-transfers', $single)->assertOk();
    $this->postJson('/branch-transfers', $single)->assertStatus(409);
    $this->assertDatabaseCount('branch_transfers', 1);
}

#[Test]
public function UserBatchesApplyTheDepartmentAndRevokeTokens(): void
{
    $users = User::factory()->count(2)->create(['department_id' => $this->source_branch->id, 'disable' => 0])->all();
    $role = Role::findOrCreate('Teacher', 'web');
    foreach ($users as $user) {
        $user->assignRole($role);
        $user->createToken('transfer-test');
    }
    $department = Position::create(['name' => 'Teaching', 'department_id' => $this->destination_branch->id, 'status' => 1]);
    $payload = $this->Payload($users, 'user') + ['position_id' => $department->id];
    $this->actingAs($this->actor)->postJson('/branch-transfers', $payload)->assertOk()->assertJsonPath('transferred_count', 2);
    foreach ($users as $user) {
        $this->assertSame($this->destination_branch->id, (int) $user->fresh()->department_id);
        $this->assertSame($department->id, (int) $user->fresh()->position_id);
        $this->assertTrue($user->fresh()->hasRole('Teacher'));
        $this->assertSame(0, $user->tokens()->count());
    }
    $this->assertDatabaseCount('branch_transfers', 2);
}

#[Test]
public function LaterDatabaseFailureRollsBackUsersHistoryAndTokens(): void
{
    $users = User::factory()->count(2)->create(['department_id' => $this->source_branch->id, 'disable' => 0])->all();
    foreach ($users as $user) {
        $user->createToken('keep-on-rollback');
    }
    $audit_count = DB::table('branch_audit_logs')->count();
    $blocked_id = (int) $users[1]->id;
    DB::statement("CREATE TEMP TRIGGER reject_transfer BEFORE UPDATE ON users WHEN NEW.id = {$blocked_id} BEGIN SELECT RAISE(ABORT, 'planned write failure'); END");
    $this->withoutExceptionHandling();
    try {
        $this->actingAs($this->actor)->postJson('/branch-transfers', $this->Payload($users, 'user'));
        $this->fail('Expected the controlled second user update to fail.');
    } catch (\Illuminate\Database\QueryException $exception) {
        $this->assertStringContainsString('planned write failure', $exception->getMessage());
    } finally {
        DB::statement('DROP TRIGGER reject_transfer');
    }
    foreach ($users as $user) {
        $this->assertSame($this->source_branch->id, (int) $user->fresh()->department_id);
        $this->assertSame(1, $user->tokens()->count());
    }
    $this->assertDatabaseCount('branch_transfers', 0);
    $this->assertSame($audit_count, DB::table('branch_audit_logs')->count());
    $this->assertFalse(app(BranchContext::class)->allowTransfer);
}

#[Test]
public function BatchSizeAndMalformedSelectionsNeverPartiallyExecute(): void
{
    $students = [];
    for ($student_index = 0; $student_index < 101; $student_index++) {
        $students[] = $this->Student('Batch '.$student_index);
    }
    $this->actingAs($this->actor)->postJson('/branch-transfers', $this->Payload($students))->assertUnprocessable();
    $base = $this->Payload([$students[0]]);
    $bad_payloads = [
        array_replace($base, ['record_ids' => []]),
        array_replace($base, ['record_ids' => [$students[0]->id, $students[0]->id]]),
        array_replace($base, ['record_ids' => [0]]),
        array_replace($base, ['record_ids' => ['not-an-id']]),
        array_replace($base, ['expected_from_branch_ids' => []]),
        array_replace($base, ['expected_from_branch_ids' => [$students[0]->id => 0]]),
        $base + ['record_id' => $students[0]->id],
    ];
    foreach ($bad_payloads as $bad_payload) {
        $this->postJson('/branch-transfers', $bad_payload)->assertUnprocessable();
    }
    $this->assertDatabaseCount('branch_transfers', 0);
    $this->postJson('/branch-transfers', $this->Payload(array_slice($students, 0, 100)))
        ->assertOk()->assertJsonPath('transferred_count', 100);
    $this->assertSame($this->source_branch->id, (int) $students[100]->fresh()->centre);
}
```

Complete `app/Support/Branch/BranchTransferRejected.php`:

```php
<?php

namespace App\Support\Branch;

final class BranchTransferRejected extends \RuntimeException
{
    public function __construct(public readonly array $record_errors, public readonly int $http_status)
    {
        parent::__construct(__('centre-management.transfers.bulk.failed'));
    }
}
```

Complete `app/Support/Branch/BranchTransferService.php`:

```php
<?php

namespace App\Support\Branch;

use App\Models\Contact;
use App\Models\Position;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

final class BranchTransferService
{
    public function Transfer(User $actor, array $input, bool $is_batch): array
    {
        $record_ids = $is_batch ? $input['record_ids'] : [(int) $input['record_id']];
        return DB::transaction(function () use ($actor, $input, $is_batch, $record_ids) {
            try {
                BranchAccess::assertBranch($actor, $input['to_branch_id'], true);
            } catch (HttpExceptionInterface $exception) {
                if (!$is_batch) { throw $exception; }
                if ($exception->getStatusCode() === 403) {
                    abort(403, __('centre-management.transfers.bulk.forbidden'));
                }
                throw ValidationException::withMessages(['to_branch_id' => __('centre-management.transfers.bulk.destination_invalid')]);
            }
            if ($is_batch && $input['type'] === 'user' && isset($input['position_id'])) {
                $is_valid = Position::query()->whereKey($input['position_id'])
                    ->where('department_id', $input['to_branch_id'])->where('status', 1)->exists();
                if (!$is_valid) {
                    throw ValidationException::withMessages(['position_id' => __('centre-management.transfers.bulk.department_invalid')]);
                }
            }
            $record_class = $input['type'] === 'user' ? User::class : Contact::class;
            $query = $record_class::query();
            if ($input['type'] === 'user') { $query->with('roles.permissions'); }
            $records = $is_batch
                ? $query->whereIn('id', $record_ids)->orderBy('id')->lockForUpdate()->get()->keyBy('id')
                : collect([$query->lockForUpdate()->findOrFail($record_ids[0])])->keyBy('id');
            $pending_orders = [];
            if ($input['type'] === 'student' && $records->isNotEmpty()) {
                $orders = DB::table('orders')->whereIn('cid', $records->keys())
                    ->where(fn ($statuses) => $statuses->whereNull('status')->orWhereIn('status', ['0', '1']))
                    ->get(['cid', 'centre']);
                foreach ($orders as $order) {
                    $pending_orders[(int) $order->cid.':'.(int) $order->centre] = true;
                }
            }
            $problems = [];
            foreach ($record_ids as $record_id) {
                $record = $records->get($record_id);
                $problem = $record
                    ? $this->InspectRecord($actor, $record, $input, $is_batch, $pending_orders)
                    : $this->Problem($record_id, 'RECORD_UNAVAILABLE', __('centre-management.transfers.bulk.unavailable'), 422);
                if ($problem !== null) { $problems[] = $problem; }
            }
            if ($problems !== []) {
                if (!$is_batch) { abort($problems[0]['http_status'], $problems[0]['message']); }
                $statuses = array_column($problems, 'http_status');
                $http_status = in_array(403, $statuses, true) ? 403 : (in_array(409, $statuses, true) ? 409 : 422);
                $record_errors = array_map(function ($problem) {
                    unset($problem['http_status']);
                    return $problem;
                }, $problems);
                throw new BranchTransferRejected($record_errors, $http_status);
            }
            $context = app(BranchContext::class);
            $previous_allow_transfer = $context->allowTransfer;
            $results = [];
            try {
                $context->allowTransfer = true;
                foreach ($records as $record) {
                    $branch_column = $record instanceof User ? 'department_id' : 'centre';
                    $source_branch_id = (int) $record->getAttribute($branch_column);
                    if ($record instanceof User) {
                        $record->position_id = $input['position_id'] ?? null;
                    } else {
                        $record->order = 0;
                    }
                    $record->setAttribute($branch_column, $input['to_branch_id']);
                    if (!$record->save()) { throw new \RuntimeException('Branch transfer save was rejected.'); }
                    DB::table('branch_transfers')->insert([
                        'subject_type' => $record_class, 'subject_id' => $record->id,
                        'from_branch_id' => $source_branch_id, 'to_branch_id' => $input['to_branch_id'],
                        'actor_id' => $actor->id, 'reason' => $input['reason'], 'created_at' => now(),
                    ]);
                    if ($record instanceof User) { $record->tokens()->delete(); }
                    $results[$record->id] = [
                        'id' => $record->id, 'from_branch_id' => $source_branch_id,
                        'to_branch_id' => (int) $input['to_branch_id'],
                    ];
                }
            } finally {
                $context->allowTransfer = $previous_allow_transfer;
            }
            return array_map(fn ($record_id) => $results[$record_id], $record_ids);
        });
    }

    private function InspectRecord(User $actor, User|Contact $record, array $input, bool $is_batch, array $pending_orders): ?array
    {
        $source_branch_id = (int) ($record instanceof User ? $record->department_id : $record->centre);
        try {
            BranchAccess::assertBranch($actor, $source_branch_id);
        } catch (HttpExceptionInterface $exception) {
            return $this->Problem($record->id, 'SOURCE_UNAVAILABLE', $is_batch
                ? __('centre-management.transfers.bulk.unavailable') : $exception->getMessage(), $exception->getStatusCode());
        }
        if ($is_batch && $source_branch_id !== $input['expected_from_branch_ids'][$record->id]) {
            return $this->Problem($record->id, 'SOURCE_CHANGED', __('centre-management.transfers.bulk.source_changed'), 409);
        }
        if ($source_branch_id === (int) $input['to_branch_id']) {
            return $this->Problem($record->id, 'SAME_BRANCH', __('centre-management.transfers.errors.different_destination'), 422);
        }
        if ($record instanceof User) {
            try {
                BranchAccess::assertManageUser($actor, $record);
                abort_if($record->hasAnyRole(['SystemAdmin', 'Admin']), 422, 'Central administrators must remain in HQ.');
                foreach ($record->roles as $role) {
                    BranchAccess::assertAssignableRole($actor, $role, (int) $input['to_branch_id']);
                }
            } catch (HttpExceptionInterface $exception) {
                return $this->Problem($record->id, 'ROLE_NOT_ALLOWED', $is_batch
                    ? __('centre-management.transfers.bulk.role_invalid') : $exception->getMessage(), $exception->getStatusCode());
            }
        } elseif (isset($pending_orders[$record->id.':'.$source_branch_id])) {
            return $this->Problem($record->id, 'PENDING_ORDERS', __('centre-management.transfers.errors.pending_orders'), 422);
        }
        return null;
    }

    private function Problem(int $record_id, string $code, string $message, int $http_status): array
    {
        return compact('record_id', 'code', 'message', 'http_status');
    }
}
```

Controller integration is an exact scoped edit, not a full-controller rewrite:

1. Add imports for `App\Support\Branch\BranchTransferService`, `App\Support\Branch\BranchTransferRejected`, and `Illuminate\Validation\ValidationException`. Remove `BranchContext` and `DB` imports once unused.
2. In `store`, immediately after the existing central-actor check, insert:

```php
if ($request->exists('record_ids')) {
    return $this->StoreBatch($request);
}
```

3. Keep the existing legacy `$request->validate` rules/messages unchanged. Replace the old `BranchAccess::assertBranch` plus transaction assignment through its closing `});` with:

```php
$result = app(BranchTransferService::class)->Transfer($actor, $data, false)[0];
```

4. Keep the existing legacy response expression unchanged. Add this complete method:

```php
private function StoreBatch(Request $request)
{
    if ($request->exists('record_id')) {
        throw ValidationException::withMessages(['record_ids' => __('centre-management.transfers.bulk.invalid_input')]);
    }
    $input = $request->validate([
        'type' => 'required|in:student,user',
        'record_ids' => 'required|array|min:1|max:'.config('branch.transfers.max_batch_size'),
        'record_ids.*' => 'required|integer|min:1|distinct',
        'expected_from_branch_ids' => 'required|array',
        'expected_from_branch_ids.*' => 'required|integer|min:1',
        'to_branch_id' => 'required|integer|min:1',
        'position_id' => 'nullable|integer|min:1|prohibited_unless:type,user',
        'reason' => 'required|string|min:5|max:1000',
    ], [
        'required' => __('centre-management.transfers.bulk.invalid_input'),
        'array' => __('centre-management.transfers.bulk.invalid_input'),
        'integer' => __('centre-management.transfers.bulk.invalid_input'),
        'in' => __('centre-management.transfers.bulk.invalid_input'),
        'min' => __('centre-management.transfers.bulk.invalid_input'),
        'max' => __('centre-management.transfers.bulk.invalid_input'),
        'string' => __('centre-management.transfers.bulk.invalid_input'),
        'distinct' => __('centre-management.transfers.bulk.invalid_input'),
        'prohibited_unless' => __('centre-management.transfers.bulk.invalid_input'),
        'record_ids.max' => __('centre-management.transfers.bulk.limit'),
        'reason.min' => __('centre-management.transfers.validation.reason_min'),
        'reason.max' => __('centre-management.transfers.validation.reason_max'),
    ]);
    if (!array_is_list($input['record_ids'])) {
        throw ValidationException::withMessages(['record_ids' => __('centre-management.transfers.bulk.invalid_input')]);
    }
    $input['record_ids'] = array_map('intval', $input['record_ids']);
    $expected_keys = array_map('strval', $input['record_ids']);
    $actual_keys = array_map('strval', array_keys($input['expected_from_branch_ids']));
    sort($expected_keys, SORT_STRING);
    sort($actual_keys, SORT_STRING);
    if ($expected_keys !== $actual_keys) {
        throw ValidationException::withMessages(['expected_from_branch_ids' => __('centre-management.transfers.bulk.invalid_input')]);
    }
    $input['expected_from_branch_ids'] = array_map('intval', $input['expected_from_branch_ids']);
    try {
        $results = app(BranchTransferService::class)->Transfer($request->user(), $input, true);
    } catch (BranchTransferRejected $exception) {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false, 'message' => $exception->getMessage(), 'record_errors' => $exception->record_errors,
            ], $exception->http_status);
        }
        return back()->withInput()->withErrors(array_column($exception->record_errors, 'message'));
    }
    $message = __('centre-management.transfers.bulk.success', ['count' => count($results)]);
    return $request->expectsJson()
        ? response()->json(['success' => true, 'message' => $message, 'transferred_count' => count($results), 'data' => $results])
        : back()->with('success', $message);
}
```

## Task 3: Localized contracts and security boundaries

**Files:**

- Modify: `lang/en/centre-management.php` and `lang/th/centre-management.php` at `transfers.bulk`.
- Modify/Test: `tests/Feature/Branch/BranchTransferBulkTest.php`.
- Test: `tests/Feature/Localization/BranchTransferLocalizedPageTest.php`.
- Test: `tests/Unit/Localization/UiTranslationStructureTest.php`.

- **Consumes:** Task 2 error codes and existing locale middleware.
- **Produces:** Matching localized catalogs and behavioral evidence for destination, role, visibility, and batch limits.
- **Checkpoint:** None. Existing unrelated stock-test failure remains outside this plan's repair scope.

### Production behavior changes

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Add `BatchSuccessUsesTheSelectedLocale` first and run `php vendor/phpunit/phpunit/phpunit tests/Feature/Branch/BranchTransferBulkTest.php --filter BatchSuccessUsesTheSelectedLocale --do-not-cache-result`. Expect a translation key instead of the independently specified success sentence until these catalogs exist.
- [ ] Insert the exact `bulk` entries below inside each existing `transfers` array, preserving every existing key.
- [ ] Run the same test and `php vendor/phpunit/phpunit/phpunit --filter 'BranchTransferLocalizedPageTest|UiTranslationStructureTest' --do-not-cache-result`; expect exit 0.
- [ ] Add the additional security/boundary tests below one at a time. Run the complete bulk test class after each addition. Where earlier Task 2 code already handles a case, report that as characterization, not a newly observed RED.
- [ ] Require source filtering and transfer rejection to avoid revealing foreign identities. Refactor only with the affected tests green.

The following snippets are complete added entries. Unicode escapes keep this English document readable as source while yielding Thai text at runtime.

For `lang/en/centre-management.php`:

```php
'bulk' => [
    'source_branch' => "Source branch",
    'all_branches' => "All accessible branches",
    'name' => "Name",
    'identifier' => "Student code / email",
    'select_page' => "Select this page",
    'select_person' => "Select :name",
    'selected' => "Selected: :count",
    'page' => "Page :page of :pages (:total records)",
    'review' => "Review selection",
    'review_title' => "Review branch transfer",
    'back' => "Back to editing",
    'remove' => "Remove",
    'cleared' => "Selection cleared because the filters changed.",
    'limit' => "Select at most :max people.",
    'submitting' => "Transferring the selected people.",
    'success' => "Transferred: :count. Existing documents remain with the issuing branch.",
    'failed' => "No transfers were saved. Review the listed problems.",
    'unavailable' => "This record is unavailable. Remove it or refresh the list.",
    'source_changed' => "The source branch changed. Refresh and select this person again.",
    'role_invalid' => "This user's role cannot be transferred with your permissions.",
    'destination_invalid' => "Choose an active destination branch you can access.",
    'department_invalid' => "Choose an active department in the destination branch.",
    'invalid_input' => "Check the selected records and transfer details.",
    'forbidden' => "You do not have permission for this transfer.",
    'uncertain' => "The transfer outcome could not be confirmed. Refresh before trying again.",
    'user_search' => "Search by user name",
    'search_help' => "Leave empty to browse, or enter 2-100 characters.",
    'selection_empty' => "Select at least one person.",
    'search_invalid' => "Enter 2-100 characters, or leave the search empty.",
],
```

For `lang/th/centre-management.php`:

```php
'bulk' => [
    'source_branch' => "\u{e2a}\u{e32}\u{e02}\u{e32}\u{e15}\u{e49}\u{e19}\u{e17}\u{e32}\u{e07}",
    'all_branches' => "\u{e17}\u{e38}\u{e01}\u{e2a}\u{e32}\u{e02}\u{e32}\u{e17}\u{e35}\u{e48}\u{e21}\u{e35}\u{e2a}\u{e34}\u{e17}\u{e18}\u{e34}\u{e4c}",
    'name' => "\u{e0a}\u{e37}\u{e48}\u{e2d}",
    'identifier' => "\u{e23}\u{e2b}\u{e31}\u{e2a}\u{e19}\u{e31}\u{e01}\u{e40}\u{e23}\u{e35}\u{e22}\u{e19} / \u{e2d}\u{e35}\u{e40}\u{e21}\u{e25}",
    'select_page' => "\u{e40}\u{e25}\u{e37}\u{e2d}\u{e01}\u{e17}\u{e31}\u{e49}\u{e07}\u{e2b}\u{e21}\u{e14}\u{e43}\u{e19}\u{e2b}\u{e19}\u{e49}\u{e32}\u{e19}\u{e35}\u{e49}",
    'select_person' => "\u{e40}\u{e25}\u{e37}\u{e2d}\u{e01} :name",
    'selected' => "\u{e40}\u{e25}\u{e37}\u{e2d}\u{e01}\u{e41}\u{e25}\u{e49}\u{e27}: :count \u{e04}\u{e19}",
    'page' => "\u{e2b}\u{e19}\u{e49}\u{e32} :page \u{e08}\u{e32}\u{e01} :pages (\u{e17}\u{e31}\u{e49}\u{e07}\u{e2b}\u{e21}\u{e14} :total \u{e23}\u{e32}\u{e22}\u{e01}\u{e32}\u{e23})",
    'review' => "\u{e15}\u{e23}\u{e27}\u{e08}\u{e17}\u{e32}\u{e19}\u{e23}\u{e32}\u{e22}\u{e0a}\u{e37}\u{e48}\u{e2d}",
    'review_title' => "\u{e15}\u{e23}\u{e27}\u{e08}\u{e17}\u{e32}\u{e19}\u{e01}\u{e32}\u{e23}\u{e22}\u{e49}\u{e32}\u{e22}\u{e2a}\u{e32}\u{e02}\u{e32}",
    'back' => "\u{e01}\u{e25}\u{e31}\u{e1a}\u{e44}\u{e1b}\u{e41}\u{e01}\u{e49}\u{e44}\u{e02}",
    'remove' => "\u{e19}\u{e33}\u{e2d}\u{e2d}\u{e01}",
    'cleared' => "\u{e25}\u{e49}\u{e32}\u{e07}\u{e23}\u{e32}\u{e22}\u{e0a}\u{e37}\u{e48}\u{e2d}\u{e17}\u{e35}\u{e48}\u{e40}\u{e25}\u{e37}\u{e2d}\u{e01}\u{e41}\u{e25}\u{e49}\u{e27} \u{e40}\u{e19}\u{e37}\u{e48}\u{e2d}\u{e07}\u{e08}\u{e32}\u{e01}\u{e15}\u{e31}\u{e27}\u{e01}\u{e23}\u{e2d}\u{e07}\u{e40}\u{e1b}\u{e25}\u{e35}\u{e48}\u{e22}\u{e19}",
    'limit' => "\u{e40}\u{e25}\u{e37}\u{e2d}\u{e01}\u{e44}\u{e14}\u{e49}\u{e2a}\u{e39}\u{e07}\u{e2a}\u{e38}\u{e14} :max \u{e04}\u{e19}",
    'submitting' => "\u{e01}\u{e33}\u{e25}\u{e31}\u{e07}\u{e22}\u{e49}\u{e32}\u{e22}\u{e2a}\u{e32}\u{e02}\u{e32}\u{e04}\u{e19}\u{e17}\u{e35}\u{e48}\u{e40}\u{e25}\u{e37}\u{e2d}\u{e01}",
    'success' => "\u{e22}\u{e49}\u{e32}\u{e22}\u{e41}\u{e25}\u{e49}\u{e27}: :count \u{e04}\u{e19} \u{e40}\u{e2d}\u{e01}\u{e2a}\u{e32}\u{e23}\u{e40}\u{e14}\u{e34}\u{e21}\u{e22}\u{e31}\u{e07}\u{e2d}\u{e22}\u{e39}\u{e48}\u{e01}\u{e31}\u{e1a}\u{e2a}\u{e32}\u{e02}\u{e32}\u{e1c}\u{e39}\u{e49}\u{e2d}\u{e2d}\u{e01}",
    'failed' => "\u{e22}\u{e31}\u{e07}\u{e44}\u{e21}\u{e48}\u{e44}\u{e14}\u{e49}\u{e22}\u{e49}\u{e32}\u{e22}\u{e43}\u{e04}\u{e23} \u{e01}\u{e23}\u{e38}\u{e13}\u{e32}\u{e15}\u{e23}\u{e27}\u{e08}\u{e2a}\u{e2d}\u{e1a}\u{e1b}\u{e31}\u{e0d}\u{e2b}\u{e32}\u{e17}\u{e35}\u{e48}\u{e41}\u{e08}\u{e49}\u{e07}",
    'unavailable' => "\u{e44}\u{e21}\u{e48}\u{e2a}\u{e32}\u{e21}\u{e32}\u{e23}\u{e16}\u{e43}\u{e0a}\u{e49}\u{e23}\u{e32}\u{e22}\u{e01}\u{e32}\u{e23}\u{e19}\u{e35}\u{e49}\u{e44}\u{e14}\u{e49} \u{e01}\u{e23}\u{e38}\u{e13}\u{e32}\u{e19}\u{e33}\u{e2d}\u{e2d}\u{e01}\u{e2b}\u{e23}\u{e37}\u{e2d}\u{e42}\u{e2b}\u{e25}\u{e14}\u{e23}\u{e32}\u{e22}\u{e0a}\u{e37}\u{e48}\u{e2d}\u{e43}\u{e2b}\u{e21}\u{e48}",
    'source_changed' => "\u{e2a}\u{e32}\u{e02}\u{e32}\u{e15}\u{e49}\u{e19}\u{e17}\u{e32}\u{e07}\u{e40}\u{e1b}\u{e25}\u{e35}\u{e48}\u{e22}\u{e19}\u{e41}\u{e25}\u{e49}\u{e27} \u{e01}\u{e23}\u{e38}\u{e13}\u{e32}\u{e42}\u{e2b}\u{e25}\u{e14}\u{e23}\u{e32}\u{e22}\u{e0a}\u{e37}\u{e48}\u{e2d}\u{e43}\u{e2b}\u{e21}\u{e48}\u{e41}\u{e25}\u{e30}\u{e40}\u{e25}\u{e37}\u{e2d}\u{e01}\u{e04}\u{e19}\u{e19}\u{e35}\u{e49}\u{e2d}\u{e35}\u{e01}\u{e04}\u{e23}\u{e31}\u{e49}\u{e07}",
    'role_invalid' => "\u{e1a}\u{e17}\u{e1a}\u{e32}\u{e17}\u{e02}\u{e2d}\u{e07}\u{e1c}\u{e39}\u{e49}\u{e43}\u{e0a}\u{e49}\u{e19}\u{e35}\u{e49}\u{e44}\u{e21}\u{e48}\u{e2a}\u{e32}\u{e21}\u{e32}\u{e23}\u{e16}\u{e22}\u{e49}\u{e32}\u{e22}\u{e44}\u{e14}\u{e49}\u{e14}\u{e49}\u{e27}\u{e22}\u{e2a}\u{e34}\u{e17}\u{e18}\u{e34}\u{e4c}\u{e02}\u{e2d}\u{e07}\u{e04}\u{e38}\u{e13}",
    'destination_invalid' => "\u{e01}\u{e23}\u{e38}\u{e13}\u{e32}\u{e40}\u{e25}\u{e37}\u{e2d}\u{e01}\u{e2a}\u{e32}\u{e02}\u{e32}\u{e1b}\u{e25}\u{e32}\u{e22}\u{e17}\u{e32}\u{e07}\u{e17}\u{e35}\u{e48}\u{e40}\u{e1b}\u{e34}\u{e14}\u{e43}\u{e0a}\u{e49}\u{e07}\u{e32}\u{e19}\u{e41}\u{e25}\u{e30}\u{e04}\u{e38}\u{e13}\u{e21}\u{e35}\u{e2a}\u{e34}\u{e17}\u{e18}\u{e34}\u{e4c}\u{e40}\u{e02}\u{e49}\u{e32}\u{e16}\u{e36}\u{e07}",
    'department_invalid' => "\u{e01}\u{e23}\u{e38}\u{e13}\u{e32}\u{e40}\u{e25}\u{e37}\u{e2d}\u{e01}\u{e41}\u{e1c}\u{e19}\u{e01}\u{e17}\u{e35}\u{e48}\u{e40}\u{e1b}\u{e34}\u{e14}\u{e43}\u{e0a}\u{e49}\u{e07}\u{e32}\u{e19}\u{e43}\u{e19}\u{e2a}\u{e32}\u{e02}\u{e32}\u{e1b}\u{e25}\u{e32}\u{e22}\u{e17}\u{e32}\u{e07}",
    'invalid_input' => "\u{e01}\u{e23}\u{e38}\u{e13}\u{e32}\u{e15}\u{e23}\u{e27}\u{e08}\u{e2a}\u{e2d}\u{e1a}\u{e23}\u{e32}\u{e22}\u{e0a}\u{e37}\u{e48}\u{e2d}\u{e41}\u{e25}\u{e30}\u{e23}\u{e32}\u{e22}\u{e25}\u{e30}\u{e40}\u{e2d}\u{e35}\u{e22}\u{e14}\u{e01}\u{e32}\u{e23}\u{e22}\u{e49}\u{e32}\u{e22}",
    'forbidden' => "\u{e04}\u{e38}\u{e13}\u{e44}\u{e21}\u{e48}\u{e21}\u{e35}\u{e2a}\u{e34}\u{e17}\u{e18}\u{e34}\u{e4c}\u{e22}\u{e49}\u{e32}\u{e22}\u{e2a}\u{e32}\u{e02}\u{e32}\u{e23}\u{e32}\u{e22}\u{e01}\u{e32}\u{e23}\u{e19}\u{e35}\u{e49}",
    'uncertain' => "\u{e22}\u{e31}\u{e07}\u{e22}\u{e37}\u{e19}\u{e22}\u{e31}\u{e19}\u{e1c}\u{e25}\u{e01}\u{e32}\u{e23}\u{e22}\u{e49}\u{e32}\u{e22}\u{e44}\u{e21}\u{e48}\u{e44}\u{e14}\u{e49} \u{e01}\u{e23}\u{e38}\u{e13}\u{e32}\u{e42}\u{e2b}\u{e25}\u{e14}\u{e23}\u{e32}\u{e22}\u{e0a}\u{e37}\u{e48}\u{e2d}\u{e43}\u{e2b}\u{e21}\u{e48}\u{e01}\u{e48}\u{e2d}\u{e19}\u{e25}\u{e2d}\u{e07}\u{e2d}\u{e35}\u{e01}\u{e04}\u{e23}\u{e31}\u{e49}\u{e07}",
    'user_search' => "\u{e04}\u{e49}\u{e19}\u{e2b}\u{e32}\u{e0a}\u{e37}\u{e48}\u{e2d}\u{e1c}\u{e39}\u{e49}\u{e43}\u{e0a}\u{e49}",
    'search_help' => "\u{e40}\u{e27}\u{e49}\u{e19}\u{e27}\u{e48}\u{e32}\u{e07}\u{e40}\u{e1e}\u{e37}\u{e48}\u{e2d}\u{e14}\u{e39}\u{e23}\u{e32}\u{e22}\u{e0a}\u{e37}\u{e48}\u{e2d} \u{e2b}\u{e23}\u{e37}\u{e2d}\u{e01}\u{e23}\u{e2d}\u{e01} 2-100 \u{e15}\u{e31}\u{e27}\u{e2d}\u{e31}\u{e01}\u{e29}\u{e23}",
    'selection_empty' => "\u{e01}\u{e23}\u{e38}\u{e13}\u{e32}\u{e40}\u{e25}\u{e37}\u{e2d}\u{e01}\u{e2d}\u{e22}\u{e48}\u{e32}\u{e07}\u{e19}\u{e49}\u{e2d}\u{e22}\u{e2b}\u{e19}\u{e36}\u{e48}\u{e07}\u{e04}\u{e19}",
    'search_invalid' => "\u{e01}\u{e23}\u{e38}\u{e13}\u{e32}\u{e01}\u{e23}\u{e2d}\u{e01} 2-100 \u{e15}\u{e31}\u{e27}\u{e2d}\u{e31}\u{e01}\u{e29}\u{e23} \u{e2b}\u{e23}\u{e37}\u{e2d}\u{e40}\u{e27}\u{e49}\u{e19}\u{e0a}\u{e48}\u{e2d}\u{e07}\u{e04}\u{e49}\u{e19}\u{e2b}\u{e32}\u{e27}\u{e48}\u{e32}\u{e07}",
],
```

Append these complete methods to `BranchTransferBulkTest`:

```php
#[Test]
public function BatchSuccessUsesTheSelectedLocale(): void
{
    $expected_messages = [
        'en' => 'Transferred: 1. Existing documents remain with the issuing branch.',
        'th' => "\u{e22}\u{e49}\u{e32}\u{e22}\u{e41}\u{e25}\u{e49}\u{e27}: 1 \u{e04}\u{e19} \u{e40}\u{e2d}\u{e01}\u{e2a}\u{e32}\u{e23}\u{e40}\u{e14}\u{e34}\u{e21}\u{e22}\u{e31}\u{e07}\u{e2d}\u{e22}\u{e39}\u{e48}\u{e01}\u{e31}\u{e1a}\u{e2a}\u{e32}\u{e02}\u{e32}\u{e1c}\u{e39}\u{e49}\u{e2d}\u{e2d}\u{e01}",
    ];
    foreach ($expected_messages as $locale => $expected_message) {
        $this->actor->update(['language_code' => $locale]);
        $student = $this->Student('Localized '.$locale);
        $this->actingAs($this->actor)->postJson('/branch-transfers', $this->Payload([$student]))
            ->assertOk()->assertJsonPath('message', $expected_message);
    }
}

#[Test]
public function InvalidDestinationsDepartmentsAndRolesCannotMoveUsers(): void
{
    $user = User::factory()->create(['department_id' => $this->source_branch->id, 'disable' => 0]);
    $user->createToken('preserve');
    $role = Role::findOrCreate('SourceOnly', 'web');
    $role->forceFill(['branch_id' => $this->source_branch->id])->save();
    $user->assignRole($role);
    $payload = $this->Payload([$user], 'user');
    $this->destination_branch->update(['status' => 0]);
    $this->actingAs($this->actor)->postJson('/branch-transfers', $payload)->assertUnprocessable();
    $this->destination_branch->update(['status' => 1]);
    $this->postJson('/branch-transfers', array_replace($payload, ['to_branch_id' => 999999]))->assertUnprocessable();
    $wrong_department = Position::create(['name' => 'Source department', 'department_id' => $this->source_branch->id, 'status' => 1]);
    $this->postJson('/branch-transfers', $payload + ['position_id' => $wrong_department->id])->assertUnprocessable();
    $inactive_department = Position::create(['name' => 'Inactive department', 'department_id' => $this->destination_branch->id, 'status' => 0]);
    $this->postJson('/branch-transfers', $payload + ['position_id' => $inactive_department->id])->assertUnprocessable();
    $this->postJson('/branch-transfers', $payload)->assertForbidden()
        ->assertJsonPath('record_errors.0.code', 'ROLE_NOT_ALLOWED');
    $this->assertSame($this->source_branch->id, (int) $user->fresh()->department_id);
    $this->assertSame(1, $user->tokens()->count());
    $this->assertDatabaseCount('branch_transfers', 0);
}

#[Test]
public function RestrictedCentralActorsCannotDiscoverForeignRecords(): void
{
    $restricted = User::factory()->create(['department_id' => 1, 'disable' => 0, 'language_code' => 'en']);
    $restricted->assignRole(Role::findOrCreate('Admin', 'web'));
    $restricted->givePermissionTo(Permission::findOrCreate('branch-transfer', 'web'));
    $foreign = $this->Student('Confidential Name');
    $this->actingAs($restricted)->getJson('/branch-transfers/targets?paginated=1&type=student&from_branch_id='.$this->source_branch->id)
        ->assertForbidden();
    $payload = array_replace($this->Payload([$foreign]), ['to_branch_id' => 1]);
    $response = $this->postJson('/branch-transfers', $payload)->assertUnprocessable()
        ->assertJsonPath('record_errors.0.code', 'RECORD_UNAVAILABLE');
    $this->assertStringNotContainsString('Confidential Name', $response->getContent());
    $this->assertDatabaseCount('branch_transfers', 0);
    $this->assertSame($this->source_branch->id, (int) $foreign->fresh()->centre);
}

#[Test]
public function CentralAccountsAreExcludedAndCannotBeTransferred(): void
{
    $this->actingAs($this->actor)->getJson('/branch-transfers/targets?paginated=1&type=user')
        ->assertOk()->assertJsonMissing(['id' => $this->actor->id]);
    $this->postJson('/branch-transfers', $this->Payload([$this->actor], 'user'))->assertUnprocessable();
    $this->assertSame(1, (int) $this->actor->fresh()->department_id);
    $this->assertDatabaseCount('branch_transfers', 0);
}

#[Test]
public function QueryBoundariesAndInactiveSourcesFollowTheContract(): void
{
    $student = $this->Student('Ready');
    $this->actingAs($this->actor);
    foreach (['0', 'A', str_repeat('A', 101)] as $query) {
        $this->getJson('/branch-transfers/targets?paginated=1&type=student&q='.urlencode($query))->assertUnprocessable();
    }
    $this->getJson('/branch-transfers/targets?paginated=1&type=student&q=')->assertOk();
    $this->getJson('/branch-transfers/targets?paginated=1&type=student&page=0')->assertUnprocessable();
    $this->postJson('/branch-transfers', array_replace($this->Payload([$student]), ['to_branch_id' => $this->source_branch->id]))
        ->assertUnprocessable();
    $this->source_branch->update(['status' => 0]);
    $this->postJson('/branch-transfers', $this->Payload([$student]))->assertOk();
}

#[Test]
public function LegacySingleRecordJsonAndBatchRedirectKeepTheirContracts(): void
{
    $first = $this->Student('Legacy');
    $second = $this->Student('Form');
    $this->actingAs($this->actor)->postJson('/branch-transfers', [
        'type' => 'student', 'record_id' => $first->id,
        'to_branch_id' => $this->destination_branch->id, 'reason' => 'Legacy transfer',
    ])->assertExactJson(['success' => true, 'data' => [
        'id' => $first->id, 'from_branch_id' => $this->source_branch->id, 'to_branch_id' => $this->destination_branch->id,
    ]]);
    $this->post('/branch-transfers', $this->Payload([$second]))->assertRedirect()
        ->assertSessionHas('success', 'Transferred: 1. Existing documents remain with the issuing branch.');
}

#[Test]
public function GuestsAndBranchActorsCannotTransfer(): void
{
    $student = $this->Student();
    $this->postJson('/branch-transfers', $this->Payload([$student]))->assertUnauthorized();
    $branch_actor = User::factory()->create(['department_id' => $this->source_branch->id, 'disable' => 0]);
    $branch_actor->assignRole(Role::findOrCreate('BranchAdmin', 'web'));
    $branch_actor->givePermissionTo(Permission::findOrCreate('branch-transfer', 'web'));
    $this->actingAs($branch_actor)->postJson('/branch-transfers', $this->Payload([$student]))->assertForbidden();
    $this->getJson('/branch-transfers/targets?paginated=1&type=student')->assertForbidden();
    $this->assertSame($this->source_branch->id, (int) $student->fresh()->centre);
    $this->assertDatabaseCount('branch_transfers', 0);
}

#[Test]
public function MixedRecordProblemsKeepAuthorizationPrecedence(): void
{
    $users = User::factory()->count(2)->create(['department_id' => $this->source_branch->id, 'disable' => 0])->all();
    $role = Role::findOrCreate('LocalOnly', 'web');
    $role->forceFill(['branch_id' => $this->source_branch->id])->save();
    $users[0]->assignRole($role);
    $payload = $this->Payload($users, 'user');
    DB::table('users')->where('id', $users[1]->id)->update(['department_id' => 1]);
    $this->actingAs($this->actor)->postJson('/branch-transfers', $payload)->assertForbidden()
        ->assertJsonCount(2, 'record_errors')->assertJsonPath('record_errors.0.code', 'ROLE_NOT_ALLOWED')
        ->assertJsonPath('record_errors.1.code', 'SOURCE_CHANGED');
    $this->assertSame($this->source_branch->id, (int) $users[0]->fresh()->department_id);
    $this->assertDatabaseCount('branch_transfers', 0);
}
```

## Task 4: Explicit selection and request lifecycle

**Files:**

- Create: `public/js/branch-transfer-state.js`.
- Create/Test: `tests/JavaScript/branch-transfer-state.test.mjs`.

- **Consumes:** The approved 100-record limit supplied by the page, candidate rows, and batch payload/response contracts from Tasks 1-3.
- **Produces:** `CreateTransferSelection(max_count)` and `CreateTransferRequests(dependencies)`.
- **Checkpoint:** None. Do not introduce a browser framework or persistent selection storage.

### Production behavior changes

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Add the test file below one test at a time. Run `node --test tests/JavaScript/branch-transfer-state.test.mjs` after each addition.
- [ ] Establish importable function boundaries before counting a failure as RED. The meaningful failures are wrong selection contents, a missing limit/reset, stale response adoption, duplicate sending, or falsely confirmed outcomes; a missing module alone is setup failure.
- [ ] Implement the corresponding behavior using the complete module below; require exit 0 after each cycle.
- [ ] Run all tests in this file once together. Refactor only with the same behaviors passing.

Complete test file:

```javascript
import test from 'node:test';
import assert from 'node:assert/strict';
import { CreateTransferSelection, CreateTransferRequests } from '../../public/js/branch-transfer-state.js';

const FIRST = { id: 101, name: 'First', from_branch_id: 2 };
const SECOND = { id: 102, name: 'Second', from_branch_id: 2 };
const THIRD = { id: 103, name: 'Third', from_branch_id: 3 };

test('RetainsSelectionAcrossPagesAndClearsOnlyWhenAppliedFiltersChange', () => {
    const selection = CreateTransferSelection(100);
    selection.ApplyFilters({ type: 'student', from_branch_id: '2', q: '' });
    selection.Toggle(FIRST, true);
    selection.Toggle(SECOND, true);
    assert.deepEqual(selection.Snapshot().map(record => record.id), [101, 102]);
    assert.equal(selection.ApplyFilters({ type: 'student', from_branch_id: '2', q: ' ' }), false);
    assert.equal(selection.Count(), 2);
    assert.equal(selection.ApplyFilters({ type: 'student', from_branch_id: '2', q: 'New' }), true);
    assert.equal(selection.Count(), 0);
    selection.Toggle(FIRST, true);
    selection.ApplyFilters({ type: 'user', from_branch_id: '2', q: 'New' });
    assert.equal(selection.Count(), 0);
});

test('SelectPageHonorsTheLimitWithoutPartialSelection', () => {
    const selection = CreateTransferSelection(2);
    selection.Toggle(FIRST, true);
    assert.equal(selection.SelectPage([SECOND, THIRD], true), false);
    assert.deepEqual(selection.Snapshot().map(record => record.id), [101]);
    assert.deepEqual(selection.PageState([FIRST, SECOND]), { checked: false, mixed: true });
    assert.equal(selection.SelectPage([FIRST, SECOND], true), true);
    assert.deepEqual(selection.PageState([FIRST, SECOND]), { checked: true, mixed: false });
    assert.equal(selection.Toggle(THIRD, true), false);
    selection.SelectPage([FIRST], false);
    assert.deepEqual(selection.Snapshot().map(record => record.id), [102]);
});

test('PreservesOriginalSourcesAndDoesNotExposeMutableSelection', () => {
    const selection = CreateTransferSelection(100);
    selection.Toggle(FIRST, true);
    selection.Toggle({ ...FIRST, from_branch_id: 9 }, true);
    selection.Snapshot()[0].from_branch_id = 7;
    assert.deepEqual(selection.BuildPayload({ to_branch_id: '3', reason: 'Move now' }), {
        type: 'student', record_ids: [101], expected_from_branch_ids: { 101: 2 },
        to_branch_id: 3, reason: 'Move now',
    });
});

test('IgnoresAnOlderSearchAfterANewerSearchCompletes', async () => {
    const resolvers = [];
    const requests = CreateTransferRequests({
        FetchTargets: () => new Promise(resolve => resolvers.push(resolve)),
        SendTransfer: async () => { throw new Error('Unexpected submit'); },
    });
    const first_request = requests.Load({ type: 'student', q: 'First' }, 1);
    const second_request = requests.Load({ type: 'student', q: 'Second' }, 1);
    resolvers[1]({ data: [SECOND], meta: { current_page: 1 } });
    assert.equal((await second_request).payload.data[0].id, 102);
    resolvers[0]({ data: [FIRST], meta: { current_page: 1 } });
    assert.equal((await first_request).kind, 'obsolete');
});

test('PreventsDuplicateSubmitAndReturnsBusinessErrorsWithoutRetrying', async () => {
    let resolve_response;
    let send_count = 0;
    const requests = CreateTransferRequests({
        FetchTargets: async () => ({ data: [] }),
        SendTransfer: () => {
            send_count += 1;
            return new Promise(resolve => { resolve_response = resolve; });
        },
    });
    const payload = { record_ids: [101] };
    const pending = requests.Submit(payload);
    assert.equal((await requests.Submit(payload)).kind, 'ignored');
    resolve_response({ ok: false, status: 409, json: async () => ({
        success: false, record_errors: [{ record_id: 101, code: 'SOURCE_CHANGED', message: 'Reload' }],
    }) });
    assert.equal((await pending).kind, 'rejected');
    assert.equal(send_count, 1);
    assert.equal(requests.IsSubmitting(), false);
});

test('SeparatesConfirmedSuccessFromUnknownNetworkOutcomes', async () => {
    let send_count = 0;
    const requests = CreateTransferRequests({
        FetchTargets: async () => ({ data: [] }),
        SendTransfer: async () => {
            send_count += 1;
            if (send_count === 1) { throw new Error('Connection interrupted'); }
            return { ok: true, status: 200, json: async () => ({
                success: true, transferred_count: 1, data: [{ id: 101 }], message: 'Done',
            }) };
        },
    });
    assert.equal((await requests.Submit({ record_ids: [101] })).kind, 'uncertain');
    assert.equal(send_count, 1);
    assert.equal((await requests.Submit({ record_ids: [101] })).kind, 'success');
});
```

Complete module:

```javascript
export function CreateTransferSelection(max_count) {
    const selected_records = new Map();
    let filters = { type: 'student', from_branch_id: '', q: '' };

    function ApplyFilters(next_filters) {
        const normalized = {
            type: next_filters.type,
            from_branch_id: String(next_filters.from_branch_id ?? ''),
            q: String(next_filters.q ?? '').trim(),
        };
        const has_changed = JSON.stringify(normalized) !== JSON.stringify(filters);
        if (has_changed) { selected_records.clear(); }
        filters = normalized;
        return has_changed;
    }

    function Toggle(record, is_checked) {
        if (!is_checked) { selected_records.delete(Number(record.id)); return true; }
        if (selected_records.has(Number(record.id))) { return true; }
        if (selected_records.size >= max_count) { return false; }
        selected_records.set(Number(record.id), Object.freeze({ ...record }));
        return true;
    }

    function SelectPage(records, is_checked) {
        const new_ids = new Set(records.filter(record => !selected_records.has(Number(record.id))).map(record => Number(record.id)));
        if (is_checked && selected_records.size + new_ids.size > max_count) { return false; }
        records.forEach(record => Toggle(record, is_checked));
        return true;
    }

    function PageState(records) {
        const selected_count = records.filter(record => selected_records.has(Number(record.id))).length;
        return { checked: records.length > 0 && selected_count === records.length, mixed: selected_count > 0 && selected_count < records.length };
    }

    function Snapshot() { return [...selected_records.values()].map(record => ({ ...record })); }

    function BuildPayload(details) {
        const records = Snapshot();
        const payload = {
            type: filters.type,
            record_ids: records.map(record => Number(record.id)),
            expected_from_branch_ids: Object.fromEntries(records.map(record => [record.id, Number(record.from_branch_id)])),
            to_branch_id: Number(details.to_branch_id),
            reason: details.reason,
        };
        if (filters.type === 'user') {
            payload.position_id = details.position_id === '' || details.position_id == null ? null : Number(details.position_id);
        }
        return payload;
    }

    return {
        ApplyFilters, Toggle, SelectPage, PageState, Snapshot, BuildPayload,
        Count: () => selected_records.size,
        Has: record_id => selected_records.has(Number(record_id)),
        Remove: record_id => selected_records.delete(Number(record_id)),
        Clear: () => selected_records.clear(),
        Filters: () => ({ ...filters }),
    };
}

export function CreateTransferRequests(dependencies) {
    let search_revision = 0;
    let search_controller = null;
    let is_submitting = false;

    function Invalidate() {
        search_revision += 1;
        search_controller?.abort();
    }

    async function Load(filters, page_number) {
        Invalidate();
        search_controller = new AbortController();
        const revision = search_revision;
        try {
            const payload = await dependencies.FetchTargets(filters, page_number, search_controller.signal);
            if (revision !== search_revision) { return { kind: 'obsolete' }; }
            return { kind: 'success', payload };
        } catch {
            return { kind: revision === search_revision ? 'error' : 'obsolete' };
        }
    }

    async function Submit(payload) {
        if (is_submitting) { return { kind: 'ignored' }; }
        is_submitting = true;
        Invalidate();
        try {
            const response = await dependencies.SendTransfer(payload);
            const result = await response.json();
            if (response.ok && result?.success === true && result.transferred_count === payload.record_ids.length
                && Array.isArray(result.data) && result.data.length === payload.record_ids.length) {
                return { kind: 'success', payload: result };
            }
            if (!response.ok && [401, 403, 409, 419, 422].includes(response.status) && result && typeof result === 'object') {
                return { kind: 'rejected', payload: result, status: response.status };
            }
            return { kind: 'uncertain' };
        } catch {
            return { kind: 'uncertain' };
        } finally {
            is_submitting = false;
        }
    }

    return { Load, Submit, Invalidate, IsSubmitting: () => is_submitting };
}
```

## Task 5: Render and connect the checkbox/review page

**Files:**

- Modify: `app/Http/Controllers/BranchTransferController.php` at `index`.
- Modify: `resources/views/branches/transfers.blade.php` at its existing content and script sections.
- Create: `public/js/branch-transfer-page.js`.
- Modify/Test: `tests/Feature/Branch/BranchTransferBulkTest.php`.
- Test: `tests/Feature/Localization/BranchTransferLocalizedPageTest.php` and `tests/JavaScript/branch-transfer-state.test.mjs`.

- **Consumes:** Tasks 1-4, the existing layout's CSRF token, and existing Bootstrap/form conventions.
- **Produces:** The approved selectable table, source/name filters, cross-page count, editable review, localized failures, and safe batch submission.
- **Checkpoint:** Use an authenticated test session and synthetic test records for browser verification; never execute a real transfer solely to verify this implementation.

### Production behavior changes

**REQUIRED IMPLEMENTATION SKILL:** Use `test-driven-development`.

- [ ] Append `PageOffersInactiveSourcesButOnlyActiveDestinations` below. Run `php vendor/phpunit/phpunit/phpunit tests/Feature/Branch/BranchTransferBulkTest.php --filter PageOffersInactiveSourcesButOnlyActiveDestinations --do-not-cache-result`; expect failure because the current page has no source selector.
- [ ] Replace `index` and the Blade view with the complete code below. Require that test and the existing localized-page tests to pass.
- [ ] Add the page adapter below, using the already behavior-tested selection/request module. Exercise each interaction against the controlled browser checklist in Task 6; do not substitute source-text assertions for rendered behavior.
- [ ] Run `node --check public/js/branch-transfer-page.js` and the state tests; require exit 0. Run the complete focused PHP command in Task 6.
- [ ] Refactor only after the relevant behavior checks pass.

Append this test; it checks rendered option sets, not the Blade source:

```php
#[Test]
public function PageOffersInactiveSourcesButOnlyActiveDestinations(): void
{
    $this->source_branch->update(['status' => 0]);
    $response = $this->actingAs($this->actor)->get('/branch-transfers')->assertOk();
    $document = new \DOMDocument();
    $previous_errors = libxml_use_internal_errors(true);
    try { $document->loadHTML($response->getContent()); }
    finally { libxml_clear_errors(); libxml_use_internal_errors($previous_errors); }
    $xpath = new \DOMXPath($document);
    $source_id = (int) $this->source_branch->id;
    $destination_id = (int) $this->destination_branch->id;
    $this->assertSame(1, $xpath->query('//select[@id="transfer-source"]/option[@value="'.$source_id.'"]')->length);
    $this->assertSame(0, $xpath->query('//select[@id="branch-id"]/option[@value="'.$source_id.'"]')->length);
    $this->assertSame(1, $xpath->query('//select[@id="branch-id"]/option[@value="'.$destination_id.'"]')->length);
    $this->assertSame(0, $xpath->query('//select[@name="record_id"]')->length);
}
```

Complete replacement for `index`:

```php
public function index(Request $request)
{
    abort_unless(BranchAccess::isCentral($request->user()), 403);
    $source_branches = Department::query()->orderBy('name')->get(['id', 'name', 'status']);
    $branches = $source_branches->where('status', 1)->values();
    $positions = \App\Models\Position::query()->where('status', 1)->get(['id', 'name', 'department_id']);
    $transfer_config = [
        'targets_url' => route('branch-transfers.targets'),
        'store_url' => route('branch-transfers.store'),
        'max_batch_size' => (int) config('branch.transfers.max_batch_size'),
        'positions' => $positions,
        'ui' => __('centre-management.transfers'),
    ];
    return view('branches.transfers', compact('source_branches', 'branches', 'transfer_config'));
}
```

Complete Blade replacement:

```blade
@extends('layouts.app')

@section('content')
<div class="container py-4" id="branch-transfer-page">
    <h2>{{ __('centre-management.transfers.title') }}</h2>
    <p>{{ __('centre-management.transfers.description') }}</p>
    @if(session('success')) <div class="alert alert-success" role="status">{{ session('success') }}</div> @endif
    @if($errors->any()) <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div> @endif

    <form id="transfer-filters">
        <fieldset id="transfer-filter-fields">
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="transfer-type">{{ __('centre-management.transfers.fields.type') }}</label>
                    <select id="transfer-type" class="form-control">
                        <option value="student">{{ __('centre-management.transfers.options.student') }}</option>
                        <option value="user">{{ __('centre-management.transfers.options.user') }}</option>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label for="transfer-source">{{ __('centre-management.transfers.bulk.source_branch') }}</label>
                    <select id="transfer-source" class="form-control">
                        <option value="">{{ __('centre-management.transfers.bulk.all_branches') }}</option>
                        @foreach($source_branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label id="transfer-search-label" for="transfer-search">{{ __('centre-management.transfers.fields.search') }}</label>
                    <input id="transfer-search" class="form-control" minlength="2" maxlength="100" aria-describedby="transfer-search-help">
                    <small id="transfer-search-help" class="form-text text-muted">{{ __('centre-management.transfers.bulk.search_help') }}</small>
                </div>
            </div>
            <button type="submit" id="find-transfer" class="btn btn-secondary">{{ __('centre-management.transfers.actions.search') }}</button>
        </fieldset>
    </form>

    <p id="transfer-status" role="status" aria-live="polite" class="mt-3"></p>
    <div id="transfer-errors" class="alert alert-danger" role="alert" tabindex="-1" hidden></div>
    <div class="d-flex flex-wrap align-items-center mb-2">
        <span id="transfer-selected-count" role="status" class="mr-3"></span>
        <button type="button" id="transfer-clear" class="btn btn-outline-secondary" disabled>{{ __('common.actions.clear') }}</button>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover" id="transfer-candidates">
            <caption class="sr-only">{{ __('centre-management.transfers.fields.record') }}</caption>
            <thead><tr>
                <th scope="col"><input type="checkbox" id="transfer-select-page" aria-label="{{ __('centre-management.transfers.bulk.select_page') }}" disabled></th>
                <th scope="col">{{ __('centre-management.transfers.bulk.name') }}</th>
                <th scope="col">{{ __('centre-management.transfers.bulk.identifier') }}</th>
                <th scope="col">{{ __('common.fields.centre') }}</th>
            </tr></thead>
            <tbody id="transfer-rows"></tbody>
        </table>
    </div>
    <nav aria-label="{{ __('centre-management.transfers.title') }}" class="d-flex flex-wrap align-items-center mb-3">
        <button type="button" id="transfer-previous" class="btn btn-outline-secondary" disabled>{{ __('common.datatable.previous') }}</button>
        <span id="transfer-page-info" class="mx-3"></span>
        <button type="button" id="transfer-next" class="btn btn-outline-secondary" disabled>{{ __('common.datatable.next') }}</button>
    </nav>

    <form id="transfer-details" method="POST" action="{{ route('branch-transfers.store') }}">
        @csrf
        <fieldset id="transfer-detail-fields">
            <div class="form-group">
                <label for="branch-id">{{ __('centre-management.transfers.fields.destination_branch') }}</label>
                <select id="branch-id" name="to_branch_id" class="form-control" required>
                    <option value="">{{ __('centre-management.messages.select_centre') }}</option>
                    @foreach($branches as $branch)<option value="{{ $branch->id }}">{{ $branch->name }}</option>@endforeach
                </select>
            </div>
            <div class="form-group" id="transfer-position-group" hidden>
                <label for="transfer-position">{{ __('centre-management.transfers.fields.destination_department') }}</label>
                <select id="transfer-position" name="position_id" class="form-control" disabled></select>
            </div>
            <div class="form-group">
                <label for="transfer-reason">{{ __('centre-management.transfers.fields.reason') }}</label>
                <textarea id="transfer-reason" name="reason" minlength="5" maxlength="1000" required class="form-control">{{ old('reason') }}</textarea>
            </div>
            <button type="submit" id="transfer-review-open" class="btn btn-primary" disabled>{{ __('centre-management.transfers.bulk.review') }}</button>
        </fieldset>
    </form>

    <section id="transfer-review" aria-labelledby="transfer-review-title" tabindex="-1" hidden>
        <h3 id="transfer-review-title">{{ __('centre-management.transfers.bulk.review_title') }}</h3>
        <div id="transfer-review-summary"></div>
        <ul id="transfer-review-records" class="list-group mb-3"></ul>
        <button type="button" id="transfer-confirm" class="btn btn-primary">{{ __('centre-management.transfers.actions.confirm') }}</button>
        <button type="button" id="transfer-back" class="btn btn-secondary">{{ __('centre-management.transfers.bulk.back') }}</button>
    </section>
    <button type="button" id="transfer-reload" class="btn btn-outline-secondary mt-3" hidden>{{ __('common.actions.reload') }}</button>
    <script type="application/json" id="transfer-config">@json($transfer_config)</script>
</div>
@endsection

@section('script')
<script type="module">
import { InitializeBranchTransferPage } from '{{ asset('js/branch-transfer-page.js') }}';
InitializeBranchTransferPage();
</script>
@endsection
```

Complete `public/js/branch-transfer-page.js`:

```javascript
import { CreateTransferSelection, CreateTransferRequests } from './branch-transfer-state.js';

export function InitializeBranchTransferPage() {
    const root = document.getElementById('branch-transfer-page');
    if (!root) { return; }
    const config = JSON.parse(document.getElementById('transfer-config').textContent);
    const ui = config.ui;
    const selection = CreateTransferSelection(config.max_batch_size);
    const Find = id => root.querySelector('#' + id);
    const status = Find('transfer-status');
    const errors = Find('transfer-errors');
    const record_type = Find('transfer-type');
    const destination = Find('branch-id');
    const position = Find('transfer-position');
    const details_form = Find('transfer-details');
    let rows = [];
    let meta = { current_page: 1, last_page: 1, total: 0 };
    let is_loading = false;
    let is_reviewing = false;
    let is_uncertain = false;
    let review_details = null;
    const stale_ids = new Set();

    function Format(message, replacements = {}) {
        return Object.entries(replacements).reduce((text, [key, replacement]) => text.replaceAll(':' + key, String(replacement)), message);
    }

    function TextElement(tag, text, class_name = '') {
        const element = document.createElement(tag);
        element.textContent = text ?? '';
        element.className = class_name;
        return element;
    }

    function ShowErrors(messages) {
        errors.replaceChildren(...messages.map(message => TextElement('p', message, 'mb-1')));
        errors.hidden = messages.length === 0;
        if (messages.length) { errors.focus(); }
    }

    const requests = CreateTransferRequests({
        FetchTargets: async (filters, page_number, signal) => {
            const url = new URL(config.targets_url, window.location.href);
            url.searchParams.set('paginated', '1');
            url.searchParams.set('type', filters.type);
            url.searchParams.set('page', String(page_number));
            if (filters.from_branch_id !== '') { url.searchParams.set('from_branch_id', filters.from_branch_id); }
            if (filters.q !== '') { url.searchParams.set('q', filters.q); }
            const response = await fetch(url, { signal, headers: { Accept: 'application/json' } });
            if (!response.ok) { throw new Error('Candidate request failed'); }
            const payload = await response.json();
            if (!Array.isArray(payload.data) || !payload.meta) { throw new Error('Invalid candidate response'); }
            return payload;
        },
        SendTransfer: payload => fetch(config.store_url, {
            method: 'POST',
            headers: {
                Accept: 'application/json', 'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify(payload),
        }),
    });

    function Render() {
        const focused_record_id = document.activeElement?.getAttribute('data-record-id');
        const is_busy = requests.IsSubmitting();
        const is_locked = is_busy || is_reviewing;
        Find('transfer-filter-fields').disabled = is_locked;
        Find('transfer-detail-fields').disabled = is_busy;
        details_form.hidden = is_reviewing;
        Find('transfer-review').hidden = !is_reviewing;
        Find('transfer-selected-count').textContent = Format(ui.bulk.selected, { count: selection.Count() });
        Find('transfer-clear').disabled = is_locked || selection.Count() === 0;
        Find('transfer-review-open').disabled = is_busy || selection.Count() === 0 || is_uncertain;
        Find('transfer-previous').disabled = is_locked || is_loading || meta.current_page <= 1;
        Find('transfer-next').disabled = is_locked || is_loading || meta.current_page >= meta.last_page;
        Find('transfer-page-info').textContent = Format(ui.bulk.page, { page: meta.current_page, pages: meta.last_page, total: meta.total });
        const page_state = selection.PageState(rows);
        const page_checkbox = Find('transfer-select-page');
        page_checkbox.checked = page_state.checked;
        page_checkbox.indeterminate = page_state.mixed;
        page_checkbox.disabled = is_locked || is_loading || rows.length === 0;
        const body = Find('transfer-rows');
        body.replaceChildren();
        for (const record of rows) {
            const table_row = document.createElement('tr');
            const checkbox_cell = document.createElement('td');
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.setAttribute('data-record-id', String(record.id));
            checkbox.checked = selection.Has(record.id);
            checkbox.disabled = is_locked || is_loading || (!checkbox.checked && selection.Count() >= config.max_batch_size);
            checkbox.setAttribute('aria-label', Format(ui.bulk.select_person, { name: record.name }));
            checkbox.addEventListener('change', function HandleRecordSelection() {
                if (!selection.Toggle(record, checkbox.checked)) { ShowErrors([Format(ui.bulk.limit, { max: config.max_batch_size })]); }
                Render();
            });
            checkbox_cell.append(checkbox);
            table_row.append(checkbox_cell, TextElement('td', record.name), TextElement('td', record.code ?? record.email ?? ''), TextElement('td', record.from_branch_name));
            body.append(table_row);
        }
        if (!rows.length && !is_loading) {
            const table_row = document.createElement('tr');
            const cell = TextElement('td', ui.status.no_results);
            cell.colSpan = 4;
            table_row.append(cell);
            body.append(table_row);
        }
        Find('transfer-confirm').disabled = is_busy || is_uncertain || selection.Count() === 0
            || selection.Snapshot().some(record => stale_ids.has(Number(record.id)));
        Find('transfer-back').disabled = is_busy;
        Find('transfer-reload').disabled = is_busy;
        if (is_reviewing) { RenderReview(); }
        if (focused_record_id !== null && focused_record_id !== undefined) {
            body.querySelector('input[data-record-id="' + Number(focused_record_id) + '"]')?.focus();
        }
    }

    function RenderReview() {
        const summary = Find('transfer-review-summary');
        summary.replaceChildren(
            TextElement('p', Format(ui.bulk.selected, { count: selection.Count() })),
            TextElement('p', ui.fields.destination_branch + ': ' + review_details.destination_name),
            TextElement('p', ui.fields.reason + ': ' + review_details.reason),
        );
        if (selection.Filters().type === 'user') {
            summary.append(TextElement('p', ui.fields.destination_department + ': ' + review_details.position_name));
        }
        const list = Find('transfer-review-records');
        list.replaceChildren();
        for (const record of selection.Snapshot()) {
            const list_item = TextElement('li', record.name + ' (' + (record.code ?? record.email ?? '') + ') - ' + record.from_branch_name + ' ', 'list-group-item');
            const remove_button = TextElement('button', ui.bulk.remove, 'btn btn-outline-secondary btn-sm ml-2');
            remove_button.type = 'button';
            remove_button.disabled = requests.IsSubmitting();
            remove_button.addEventListener('click', function HandleRemove() {
                selection.Remove(record.id);
                stale_ids.delete(Number(record.id));
                Render();
                Find('transfer-review').focus();
            });
            list_item.append(remove_button);
            list.append(list_item);
        }
    }

    async function Load(page_number = 1) {
        is_loading = true;
        rows = [];
        status.textContent = ui.status.searching;
        Render();
        const result = await requests.Load(selection.Filters(), page_number);
        if (result.kind === 'obsolete') { return false; }
        is_loading = false;
        if (result.kind === 'error') { status.textContent = ui.status.search_failed; Render(); return false; }
        rows = result.payload.data;
        meta = result.payload.meta;
        if (meta.current_page > meta.last_page) { return Load(meta.last_page); }
        status.textContent = rows.length ? '' : ui.status.no_results;
        Render();
        return true;
    }

    function SetPositions() {
        const is_user = record_type.value === 'user';
        Find('transfer-position-group').hidden = !is_user;
        position.disabled = !is_user;
        position.replaceChildren(new Option(ui.options.no_department, ''));
        config.positions.filter(department => String(department.department_id) === destination.value)
            .forEach(department => position.add(new Option(department.name, department.id)));
        Find('transfer-search-label').textContent = is_user ? ui.bulk.user_search : ui.fields.search;
    }

    async function ApplyFilters(event) {
        event?.preventDefault();
        if (requests.IsSubmitting() || is_reviewing) { return; }
        const query = Find('transfer-search').value.trim();
        const query_length = Array.from(query).length;
        if (query !== '' && (query_length < 2 || query_length > 100)) { ShowErrors([ui.bulk.search_invalid]); return; }
        const had_selection = selection.Count() > 0;
        const changed = selection.ApplyFilters({ type: record_type.value, from_branch_id: Find('transfer-source').value, q: query });
        ShowErrors([]);
        if (changed) { stale_ids.clear(); }
        const did_load = await Load(1);
        if (changed && had_selection) { status.textContent = ui.bulk.cleared + (did_load ? '' : ' ' + ui.status.search_failed); }
    }

    Find('transfer-filters').addEventListener('submit', ApplyFilters);
    record_type.addEventListener('change', async function HandleTypeChange() {
        Find('transfer-search').value = '';
        SetPositions();
        await ApplyFilters();
    });
    destination.addEventListener('change', SetPositions);
    Find('transfer-previous').addEventListener('click', () => Load(meta.current_page - 1));
    Find('transfer-next').addEventListener('click', () => Load(meta.current_page + 1));
    Find('transfer-clear').addEventListener('click', function HandleClear() { selection.Clear(); stale_ids.clear(); Render(); });
    Find('transfer-select-page').addEventListener('change', function HandlePageSelection(event) {
        if (!selection.SelectPage(rows, event.target.checked)) { ShowErrors([Format(ui.bulk.limit, { max: config.max_batch_size })]); }
        Render();
    });
    details_form.addEventListener('submit', function HandleReview(event) {
        event.preventDefault();
        if (!details_form.reportValidity() || requests.IsSubmitting() || is_uncertain) { return; }
        if (!selection.Count()) { ShowErrors([ui.bulk.selection_empty]); return; }
        review_details = {
            to_branch_id: destination.value, position_id: position.value,
            destination_name: destination.selectedOptions[0]?.textContent ?? '',
            position_name: position.selectedOptions[0]?.textContent ?? ui.options.no_department,
            reason: Find('transfer-reason').value,
        };
        is_reviewing = true;
        ShowErrors([]);
        Render();
        Find('transfer-review').focus();
    });
    Find('transfer-back').addEventListener('click', function HandleBack() {
        is_reviewing = false;
        Render();
        if (!Find('transfer-review-open').disabled) { Find('transfer-review-open').focus(); }
        else { destination.focus(); }
    });
    Find('transfer-reload').addEventListener('click', () => window.location.reload());
    Find('transfer-confirm').addEventListener('click', async function HandleConfirm() {
        if (requests.IsSubmitting() || is_uncertain || !selection.Count()
            || selection.Snapshot().some(record => stale_ids.has(Number(record.id)))) { return; }
        const pending = requests.Submit(selection.BuildPayload(review_details));
        is_loading = false;
        status.textContent = ui.bulk.submitting;
        ShowErrors([]);
        Render();
        const result = await pending;
        if (result.kind === 'ignored') { return; }
        if (result.kind === 'success') {
            selection.Clear();
            stale_ids.clear();
            is_reviewing = false;
            const did_refresh = await Load(meta.current_page);
            status.textContent = result.payload.message + (did_refresh ? '' : ' ' + ui.status.search_failed);
            destination.focus();
        } else if (result.kind === 'rejected') {
            const record_map = new Map(selection.Snapshot().map(record => [Number(record.id), record]));
            const messages = [[401, 403, 419].includes(result.status) ? ui.bulk.forbidden : ui.bulk.failed];
            for (const problem of result.payload.record_errors ?? []) {
                const record = record_map.get(Number(problem.record_id));
                messages.push((record ? record.name + ': ' : '') + problem.message);
                if (problem.code === 'SOURCE_CHANGED') { stale_ids.add(Number(problem.record_id)); }
            }
            for (const field_messages of Object.values(result.payload.errors ?? {})) {
                messages.push(...(Array.isArray(field_messages) ? field_messages : [field_messages]).filter(message => typeof message === 'string'));
            }
            status.textContent = '';
            ShowErrors(messages);
            Find('transfer-reload').hidden = false;
        } else {
            is_uncertain = true;
            status.textContent = '';
            ShowErrors([ui.bulk.uncertain]);
            Find('transfer-reload').hidden = false;
        }
        Render();
    });
    SetPositions();
    void ApplyFilters();
}
```

## Task 6: Integrated verification and handoff evidence

**Files:**

- Modify: `docs/plans/0002-bulk-branch-transfers.md` at its status only when execution evidence supports the update.
- Modify: `docs/specs/0002-bulk-branch-transfers-design.md` at its status only when execution evidence supports the update.
- Test: `tests/Feature/Branch/BranchTransferBulkTest.php`, `tests/Feature/Branch/BranchWorkflowsTest.php`, `tests/Feature/Localization/BranchTransferLocalizedPageTest.php`, `tests/Feature/Navigation/MenuOrganizationTest.php`, `tests/Unit/Localization/UiTranslationStructureTest.php`, and `tests/JavaScript/branch-transfer-state.test.mjs`.

- **Consumes:** Completed Tasks 1-5 and the approved acceptance criteria.
- **Produces:** Actual test results, rendered-page evidence, changed-file review, and a truthful completion report.
- **Checkpoint:** Any new failure attributable to this change returns to its owning task. Do not repair unrelated failures, broaden the product scope, or claim full verification while checks are unavailable or failing.

### Nonbehavioral verification and documentation

- [ ] Run the focused PHP command:

```powershell
php vendor/phpunit/phpunit/phpunit --filter 'BranchTransferBulkTest|BranchTransferLocalizedPageTest|MenuOrganizationTest|UiTranslationStructureTest|test_student_transfer_preserves_issued_document_branch_and_clears_current_enrolment|test_transfer_search_hides_central_accounts_and_finds_branch_staff_by_name' --do-not-cache-result
```

Expect exit 0: all new bulk behaviors and the baseline transfer, localization, and navigation cases pass. Do not invent the final test/assertion count; report the runner's result.

- [ ] Run the focused JavaScript checks:

```powershell
node --check public/js/branch-transfer-state.js
node --check public/js/branch-transfer-page.js
node --test tests/JavaScript/branch-transfer-state.test.mjs
```

Expect exit 0 and all six state/request behavior tests passing.

- [ ] Review the exact diff with `git diff --check` and `git diff --stat`. Check new files as well as tracked changes. Confirm that changes are limited to this plan's file manifest, with no migration, dependency, unrelated stock fix, or accidental rename.
- [ ] Run the full PHP suite required by `test-driven-development` and CI, and all existing JavaScript tests:

```powershell
php vendor/phpunit/phpunit/phpunit --do-not-cache-result
node --test tests/JavaScript/*.test.mjs
```

The required final target is exit 0. The stock logging baseline above may still fail; report every actual failure and distinguish it from the focused results. A passing focused suite is not a passing full suite. An unavailable database/browser/runner is unverified coverage, not a pass. Do not run financial or transfer probes against live records.

- [ ] In an authenticated isolated test session, verify the following rendered behavior with synthetic records:

| Scenario | Observable result |
| --- | --- |
| Open student mode without a query | Accessible records appear with source filter and 25-row pagination |
| Select records on pages 1 and 2 | Count and review contain both pages' explicit selections |
| Select-all on page 2 | Only page 2 is added; no hidden filtered pages are selected |
| Cross the 100-record boundary | Individual additions are disabled; overflowing page selection is rejected unchanged |
| Edit but do not apply a search | Selection remains; applying a changed filter clears it and announces why |
| Switch to user mode | Selection resets; user-name label and destination department appear; central accounts are absent |
| Open and cancel review with keyboard | Selected identities, sources, destination, department, and reason are readable; focus returns to editing |
| Select/unselect with keyboard | Focus stays on the corresponding row checkbox after rendering |
| Use narrow viewport and long names/reasons | Controls remain usable, text wraps, and table overflow does not hide actions |
| Render a synthetic name containing HTML characters | Characters display literally; no script or markup executes |
| Submit one blocked record with valid records | Nothing moves; selection remains; authorized per-record errors are visible and announced |
| Remove a blocked record | Remaining selection stays available for a new explicit confirmation |
| Detect a stale source | Confirmation stays disabled for that stale selection until it is removed/reselected |
| Interrupt the response in the test environment | Outcome is described as unconfirmed; no automatic retry occurs; review and reload action remain available |
| Submit twice rapidly | Only one request is sent while the first remains in flight |
| Complete a valid batch | Count-aware success, cleared selection, refreshed candidates, and retained historical document ownership |
| Repeat in Thai and English | No missing translation keys; labels, errors, counts, and review text use the chosen locale |

If no isolated authenticated browser session is available, finish the automated checks and report these browser cases as not verified. Do not move or fabricate real business data to obtain a screenshot. Row-lock behavior is not established by SQLite: only claim it if tested on an isolated compatible engine using synthetic records.

- [ ] Record final commands, exit codes, observed counts, browser coverage, and remaining limitations in the handoff. Update document statuses only to the level actually demonstrated. Do not commit or push unless separately authorized.

## Requirement trace and planned file manifest

| Approved requirement | Owning task / evidence |
| --- | --- |
| Both record types, basic filters, 25-row pages | Task 1 candidate tests; Task 5 type-dependent UI; Task 6 browser checks |
| Explicit cross-page selection and 100-record limit | Task 2 input boundary tests; Task 4 selection tests; Task 6 browser checks |
| One destination/reason and optional common department | Task 2 user/department tests; Task 5 review and payload builder |
| All-or-nothing writes and token/audit rollback | Task 2 student rejection and controlled later-write failure tests |
| Stale-source and duplicate-submit protection | Task 2 conflict/repeat tests; Task 4 request tests |
| Authorization, hidden identities, protected roles | Task 3 access/role/central-account tests plus existing branch scopes |
| Preserve documents and legacy contracts | Task 1 legacy search check; Tasks 2-3 legacy/issued-order tests; existing regression checks |
| Review and failure retention | Tasks 4-5 state and page adapter; Task 6 rendered interaction checks |
| Thai/English, accessible text rendering | Task 3 catalogs/parity; Task 5 semantic controls; Task 6 keyboard and text-encoding checks |

Planned implementation changes: four existing application files (`config/branch.php`, the controller, and the two locale catalogs), one existing Blade view, two new PHP support classes, two new browser modules, one new PHP feature test, and one new JavaScript test. Existing regression tests remain unchanged. Document status updates are the only documentation mutations during implementation.

## Approval and execution handoff

This plan is ready for user review, not approved for execution merely because its source specification is approved. After explicit approval, use `git-worktrees` and `executing-plans`; execute the RED/GREEN cycles in dependency order and retain the baseline failure record. Approval of this unchanged plan authorizes its scoped implementation and verification without repeating approval at each task.
