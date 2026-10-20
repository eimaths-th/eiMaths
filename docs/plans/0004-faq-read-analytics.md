# FAQ Read Analytics Implementation Plan

> **REQUIRED EXECUTION SKILL:** After plan approval and execution-method selection, use `git-worktrees`, then `executing-plans` with the selected method for task-by-task execution.

- **Goal:** Count one successfully recorded opening per FAQ/public website session, and let authorized FAQ staff rank questions by reads over an inclusive Bangkok date range.
- **Architecture:** Browser qualifying event -> same-origin public POST with session/CSRF -> server-derived HMAC key -> authenticated backend POST -> transactionally unique receipt and daily aggregate -> permission-protected paginated report.
- **Tech Stack:** Backend Laravel 10.50.2/PHPUnit 10.5.63; public Laravel 12.58.0/PHPUnit 11.5.55; existing sessions, cache and HTTP client; plain JavaScript/Playwright; existing MySQL-compatible database configuration with isolated SQLite functional tests.
- **Source:** `docs/specs/0003-faq-search-seo-analytics-design.md`, approved in conversation on 2026-10-20; two-plan split subsequently approved.
- **Companion:** `docs/plans/0003-faq-search-seo.md`.
- **Status:** Proposed plan, awaiting approval and execution-method selection. No code, database, deployment, external message or commit is performed by this document.

## Global constraints

Copied requirements from the approved specification:

> Count the first opening of each FAQ in one existing website session. Repeated openings in that session do not add another read.

> Recording failure must not prevent reading. Reporting failure appears as an error, not a successful zero-count report.

> Tests must not use live backend services, migrate user databases, or send real tracking.

A read is an acknowledged first opening, not proof of reading to completion, a unique human, or a bot-free visitor. Count from enablement onward; do not reconstruct earlier visits. Session expiry/regeneration establishes a new scope; midnight, tabs, reloads, language and reading surface do not. Use Bangkok calendar dates and UTC receipt timestamps explicitly.

Keep raw session IDs, IPs, user agents, names, emails, user IDs, referrers, search queries and HMAC event keys out of feature logs and reports. Store only minimal pseudonymous receipts and daily aggregates with editorial snapshots; no automatic expiry in this release. Deleting/unpublishing current FAQ content must not delete analytics history. Retention/notice review by the system owner is required before live enablement, as already approved in specification section 11. Notice/consent changes are outside this implementation scope.

Use existing permissions, global FAQ administration scope, account/branch middleware, styling and localization conventions. Add no visitor profiles, export, charts, live polling, marketing integration, external analytics account or new consent interface. New source naming follows the supplied AGENTS rules, with framework/PSR-4/protocol exceptions.

## Verified baseline, isolation and commands

`B/` means backend `eiMaths/`; `P/` means sibling public `eimaths_th_new/`, resolved to the approved worktrees during execution. Reviewed heads: B `4fb62623`, P `2851b91`. The companion establishes its own API/UI changes; it does not change analytics persistence or authorization.

B `config/database.php` defaults to `mysql`; the deployed engine/version is unverified. B PHPUnit forces SQLite `:memory:`; P PHPUnit selects SQLite `:memory:` and array sessions. Local PHP has `pdo_mysql`/`pdo_sqlite`, but no disposable MySQL database or credentials were supplied/connected during planning. This is an execution prerequisite for the intended-engine concurrency check, not permission to use the user's configured database.

B's API middleware includes `ThrottleRequests::class.':api'` with 60 requests/minute per caller. Remove that exact middleware only from the new integration route and replace it with its own admission policy. Keep other routes untouched. `EnforceBranchContext` bypasses unauthenticated integration callers but still validates authenticated backoffice accounts. `BranchRouteAccess` preserves other controllers' policies; add `faq-list` explicitly to the new statistics controller.

Installed CLI versions/help were checked. Run from the indicated checkout, after verifying test configuration cannot be overridden by a cached config:

```powershell
# B: focused, then complete PHP suite
php vendor/bin/phpunit --do-not-cache-result --filter 'FaqRead|FaqStatistics|FaqApi|FaqLocalization'
php vendor/bin/phpunit --do-not-cache-result
# P: focused PHP/browser, then complete suites
php vendor/bin/phpunit --do-not-cache-result --filter 'FaqRead|FaqPage|FaqDetail'
node node_modules/@playwright/test/cli.js test tests/browser/faq-read-tracking.spec.mjs --reporter=line
php vendor/bin/phpunit --do-not-cache-result
node node_modules/@playwright/test/cli.js test --reporter=line
```

No application test or browser scenario ran during planning. All new-path commands refer to files created by their owning task. Follow the `test-driven-development` skill and `writing-good-tests.md`: one missing behavior at a time, observed assertion failure before implementation, then focused GREEN and required regressions. Class-loading/fixture errors alone do not establish RED.

## Cross-plan contract and ordering

Tasks 1-4 (backend) can be developed and tested without the companion. Task 5's server adapter can also be tested against a fake backend. Task 6's browser wiring consumes the companion's real `FAQ_OPENED` event, `.faq-item[data-faq-id]` and `[data-faq-detail][data-faq-id]`. Do not create another accordion implementation.

The event payload is `{ faq_id: <positive safe integer> }`, emitted only after a user opens an answer. Detail recording waits for a visible document. The same HMAC key is derived for all surfaces from the server's session ID and FAQ ID; JavaScript never chooses the key.

Contracts:

| Boundary | Required behavior |
| --- | --- |
| Public `POST /faq/{faq_id}/read`, route `faq.read` | Existing session + CSRF, numeric positive ID; no browser-provided key/session/time/count; 204 new/duplicate; fail softly |
| Backend `POST /api/faq-reads` | Dedicated bearer token; JSON `faq_id` integer >0 and `event_key` 64 lowercase hex; no extra fields |
| Backend response | 204 new/matching duplicate, 401 bad token, 404 nonpublic content, 409 key belongs to other FAQ, 422 invalid payload, 429 rate limit, 503 unavailable persistence |
| Statistics `GET /faqs/statistics`, route `faqs.statistics` | Existing authenticated `faq-list` access; default 30d; 7d/30d/custom, 20 rows, counts descending then FAQ ID ascending |
| Public adapter failures | Safe localized `error_code`/`message`; upstream credential failure is 503, not a visitor login requirement |

## Task 1: Store receipts and daily totals atomically

**Files:**

- Create: `B/database/migrations/2026_10_20_000002_create_faq_read_analytics_tables.php`.
- Create: `B/app/Services/Faq/FaqReadRecorder.php`, `B/app/Services/Faq/DuplicateFaqRead.php`.
- Test: `B/tests/Feature/FaqReadRecorderTest.php`.

- **Consumes:** Existing FAQ/category publication and localized source fields; database transactions and row locks.
- **Produces:** `FaqReadRecorder::Record(int $faq_id, string $event_key): void`; one durable receipt plus one aggregate increment, or an explicit failure.
- **Checkpoint:** Tests use disposable databases only. No destructive production rollback after collection; preserve additive tables.

**Actions — production behavior changes:**

**REQUIRED IMPLEMENTATION SKILL:** `test-driven-development`.

- [ ] First add a schema-existence assertion to the test below; B `php vendor/bin/phpunit --do-not-cache-result --filter FaqReadRecorderTest`. Observe missing-table assertion failure, then add the migration.
- [ ] Add the first recording test with a loadable no-op recorder boundary, and observe expected 1 versus actual 0. Implement the transaction below. GREEN: repeated key yields one receipt/count, another key yields another count.
- [ ] Add midnight, rollback, hidden/deleted FAQ, key collision and history-retention tests in separate cycles. Check row contents as well as response/exception. Duplicate after midnight must not create an empty later-day aggregate.
- [ ] Run the focused test and companion backend regression tests. Refactor only while the invariants hold. SQLite checks functional transaction behavior; Task 7 owns actual concurrent intended-engine proof.

Migration:

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void
    {
        Schema::create('faq_daily_reads', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('faq_reference_id');
            $table->date('read_date');
            $table->unsignedBigInteger('read_count')->default(0);
            $table->string('question_th');
            $table->string('question_en')->nullable();
            $table->string('category_th');
            $table->string('category_en')->nullable();
            $table->unique(['faq_reference_id', 'read_date'], 'faq_reads_faq_date_unique');
            $table->index(['read_date', 'faq_reference_id'], 'faq_reads_date_faq_index');
        });
        Schema::create('faq_read_receipts', function (Blueprint $table): void {
            $table->char('event_key', 64)->primary();
            $table->foreignId('daily_read_id')->constrained('faq_daily_reads')->restrictOnDelete();
            $table->dateTime('first_recorded_at', 6);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('faq_read_receipts');
        Schema::dropIfExists('faq_daily_reads');
    }
};
```

There is intentionally no FK from historical `faq_reference_id` to live `faqs`. The receipt-to-aggregate FK is real and restrictive. MySQL's unsigned count supplies the nonnegative constraint; SQLite does not enforce unsigned ranges, so verify rejection of negative counts on the intended engine in Task 7. Never accept a caller-supplied increment or rewrite/recycle historical FAQ IDs.

`DuplicateFaqRead.php`:

```php
<?php
namespace App\Services\Faq;
use Illuminate\Database\UniqueConstraintViolationException;
use RuntimeException;
final class DuplicateFaqRead extends RuntimeException
{
    public function __construct(UniqueConstraintViolationException $previous)
    {
        parent::__construct('Duplicate FAQ read receipt', 0, $previous);
    }
}
```

`FaqReadRecorder.php`:

```php
<?php
namespace App\Services\Faq;
use Carbon\CarbonImmutable;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
final class FaqReadRecorder
{
    public function Record(int $faq_id, string $event_key): void
    {
        try {
            DB::transaction(function () use ($faq_id, $event_key): void {
                $faq = DB::table('faqs')->where('id', $faq_id)->lockForUpdate()->first();
                abort_unless($faq && (bool) $faq->is_published, 404);
                $category = DB::table('faq_categories')->where('id', $faq->category_id)->lockForUpdate()->first();
                abort_unless($category && (bool) $category->is_published, 404);
                $accepted_at = CarbonImmutable::now('Asia/Bangkok');
                $read_date = $accepted_at->toDateString();
                $snapshots = ['question_th' => $faq->question, 'question_en' => $faq->question_en,
                    'category_th' => $category->name, 'category_en' => $category->name_en];
                $daily = DB::table('faq_daily_reads')->where('faq_reference_id', $faq_id)
                    ->where('read_date', $read_date)->first();
                $daily_id = $daily?->id ?? DB::table('faq_daily_reads')->insertGetId(array_merge(
                    ['faq_reference_id' => $faq_id, 'read_date' => $read_date, 'read_count' => 0], $snapshots
                ));
                try {
                    DB::table('faq_read_receipts')->insert(['event_key' => $event_key, 'daily_read_id' => $daily_id,
                        'first_recorded_at' => $accepted_at->utc()->format('Y-m-d H:i:s.u')]);
                } catch (UniqueConstraintViolationException $exception) {
                    throw new DuplicateFaqRead($exception);
                }
                DB::table('faq_daily_reads')->where('id', $daily_id)->increment('read_count', 1, $snapshots);
            }, 3);
        } catch (DuplicateFaqRead $exception) {
            // The outer transaction has rolled back before inspecting the committed receipt.
            $existing = DB::table('faq_read_receipts')->join('faq_daily_reads',
                'faq_daily_reads.id', '=', 'faq_read_receipts.daily_read_id')
                ->where('event_key', $event_key)->first(['faq_reference_id']);
            if ($existing === null) {
                throw $exception;
            }
            abort_unless((int) $existing->faq_reference_id === $faq_id, 409);
        }
    }
}
```

Only the receipt insert converts a unique failure to `DuplicateFaqRead`; other uniqueness/database failures must not become successful duplicate acknowledgements. The live FAQ lock serializes competing writes to the same FAQ; the unique receipt remains the final guard across questions/requests. Deadlock retries are bounded at three transaction attempts and do not change the key. A duplicate for content that is now hidden still fails current publication checks.

`B/tests/Feature/FaqReadRecorderTest.php`:

```php
<?php
namespace Tests\Feature;
use App\Models\Faq;
use App\Models\FaqCategory;
use App\Services\Faq\FaqReadRecorder;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
final class FaqReadRecorderTest extends TestCase
{
    use RefreshDatabase;
    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }
    private function PublishedFaq(): Faq
    {
        $category = FaqCategory::create(['name' => 'Primary category', 'name_en' => 'General',
            'slug' => 'reads-fixture', 'is_published' => true]);
        return Faq::create(['category_id' => $category->id, 'question' => 'Primary question',
            'question_en' => 'English question', 'answer' => 'Answer', 'is_published' => true]);
    }
    #[Test] public function SchemaExists(): void
    {
        $this->assertTrue(Schema::hasTable('faq_daily_reads'));
        $this->assertTrue(Schema::hasTable('faq_read_receipts'));
    }
    #[Test] public function DuplicateAcrossMidnightDoesNotAddAnEmptyDay(): void
    {
        $faq = $this->PublishedFaq();
        $recorder = app(FaqReadRecorder::class);
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-10-20T16:59:59Z'));
        $recorder->Record($faq->id, str_repeat('a', 64));
        $this->assertDatabaseHas('faq_daily_reads', ['faq_reference_id' => $faq->id,
            'read_date' => '2026-10-20', 'read_count' => 1]);
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-10-20T17:00:01Z'));
        $recorder->Record($faq->id, str_repeat('a', 64));
        $this->assertDatabaseCount('faq_daily_reads', 1);
        $this->assertDatabaseCount('faq_read_receipts', 1);
        $recorder->Record($faq->id, str_repeat('b', 64));
        $this->assertDatabaseHas('faq_daily_reads', ['faq_reference_id' => $faq->id,
            'read_date' => '2026-10-21', 'read_count' => 1]);
        $faq->delete();
        $this->assertSame(2, (int) DB::table('faq_daily_reads')->sum('read_count'));
        $this->assertDatabaseCount('faq_read_receipts', 2);
    }
}
```

For rollback sensitivity, on the isolated SQLite connection create a temporary trigger that raises `ABORT` on `UPDATE OF read_count`; call `Record`, expect an exception, and assert no receipt/aggregate persists. Drop only that test-owned trigger in `finally`, retry the same key, and expect exactly one count. This faults the database below the real transaction rather than mocking the recorder. Also preseed a real receipt for another FAQ and assert 409 without writes; test unknown/hidden category/hidden FAQ and missing matching receipt after a uniqueness error separately.

## Task 2: Authenticate, limit and validate backend recording requests

**Files:**

- Create: `B/config/faq-analytics.php`.
- Create: `B/app/Http/Middleware/AdmitFaqRead.php`.
- Create: `B/app/Http/Controllers/FaqReadController.php`.
- Modify: `B/routes/api.php` add dedicated POST; `B/.env.example` append blank credential/configuration defaults.
- Test: `B/tests/Feature/FaqReadApiTest.php`.

- **Consumes:** Task 1 recorder and existing API middleware group.
- **Produces:** Authenticated bounded write contract with no default API-budget collision.
- **Checkpoint:** No real secrets in source or tests. Test-only token literals are not deployment credentials.

**Actions — production behavior changes:**

**REQUIRED IMPLEMENTATION SKILL:** `test-driven-development`.

- [ ] Write the first request test; B `php vendor/bin/phpunit --do-not-cache-result --filter FaqReadApiTest`. RED: the new route returns 404 instead of 204 for an authenticated valid record.
- [ ] Add config, middleware, controller and route. GREEN: valid new/duplicate requests return 204 and increment once.
- [ ] Add token absent/wrong/empty configuration, invalid JSON types, overlong/mixed-case key, zero/negative IDs, extra fields, hidden content, mismatched key, DB failure and separate authenticated/invalid-credential budgets in successive cycles.
- [ ] Send 61 valid, distinct keys from one backend caller with the integration budget above 61; all must pass despite the old API 60/minute limiter. Lower the new budget to two and require the third response to be 429 with `Retry-After`. Ensure unrelated API routes still use their existing limiter.
- [ ] Run the complete focused backend group, inspect logs for absence of token/event/session/request-body data, and refactor while green.

`B/config/faq-analytics.php`:

```php
<?php
return [
    'integration_token' => env('FAQ_READ_INTEGRATION_TOKEN', ''),
    'integration_requests_per_minute' => (int) env('FAQ_READ_INTEGRATION_REQUESTS_PER_MINUTE', 600),
    'invalid_requests_per_minute' => (int) env('FAQ_READ_INVALID_REQUESTS_PER_MINUTE', 30),
];
```

`B/app/Http/Middleware/AdmitFaqRead.php`:

```php
<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
final class AdmitFaqRead
{
    public function handle(Request $request, Closure $next)
    {
        $configured_token = (string) config('faq-analytics.integration_token');
        $provided_token = $request->bearerToken();
        $is_authenticated = $configured_token !== '' && is_string($provided_token)
            && hash_equals($configured_token, $provided_token);
        $limit_key = $is_authenticated ? 'faq-read:integration' : 'faq-read:invalid:'.hash('sha256', (string) $request->ip());
        $limit = max(1, (int) config($is_authenticated
            ? 'faq-analytics.integration_requests_per_minute' : 'faq-analytics.invalid_requests_per_minute'));
        if (RateLimiter::hit($limit_key, 60) > $limit) {
            return response()->json(['error_code' => 'FAQ_READ_RATE_LIMITED', 'message' => 'Please try again later.'],
                429, ['Retry-After' => (string) max(1, RateLimiter::availableIn($limit_key))]);
        }
        if ($configured_token === '') {
            return response()->json(['error_code' => 'FAQ_READ_UNAVAILABLE', 'message' => 'Read recording is unavailable.'], 503);
        }
        if (!$is_authenticated) {
            return response()->json(['error_code' => 'FAQ_READ_UNAUTHORIZED', 'message' => 'Authentication is required.'], 401);
        }
        return $next($request);
    }
}
```

`B/app/Http/Controllers/FaqReadController.php`:

```php
<?php
namespace App\Http\Controllers;
use App\Services\Faq\FaqReadRecorder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;
final class FaqReadController extends Controller
{
    public function Store(Request $request, FaqReadRecorder $recorder)
    {
        $payload = $request->json()->all();
        if (!$request->isJson() || array_diff(array_keys($payload), ['faq_id', 'event_key']) !== []
            || !is_int($payload['faq_id'] ?? null) || $payload['faq_id'] <= 0
            || !is_string($payload['event_key'] ?? null)
            || preg_match('/^[a-f0-9]{64}$/D', $payload['event_key']) !== 1) {
            return response()->json(['error_code' => 'FAQ_READ_INVALID', 'message' => 'Invalid recording request.'], 422);
        }
        $request_id = $request->header('X-Request-Id');
        $correlation_id = is_string($request_id) && preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/iD', $request_id)
            ? strtolower($request_id) : (string) Str::uuid();
        try {
            $recorder->Record($payload['faq_id'], $payload['event_key']);
        } catch (Throwable $exception) {
            if ($exception instanceof HttpExceptionInterface && in_array($exception->getStatusCode(), [404, 409], true)) {
                return response()->json(['error_code' => $exception->getStatusCode() === 404 ? 'FAQ_NOT_FOUND' : 'FAQ_READ_CONFLICT',
                    'message' => 'This recording request cannot be accepted.'], $exception->getStatusCode());
            }
            Log::warning('faq.read.failed', ['operation' => 'faq.read.record', 'faq_id' => $payload['faq_id'],
                'outcome' => 'failed', 'error_code' => 'FAQ_READ_UNAVAILABLE', 'correlation_id' => $correlation_id]);
            return response()->json(['error_code' => 'FAQ_READ_UNAVAILABLE', 'message' => 'Read recording is unavailable.'], 503);
        }
        return response()->noContent();
    }
}
```

Add to B `routes/api.php`:

```php
Route::post('/faq-reads', [\App\Http\Controllers\FaqReadController::class, 'Store'])
    ->withoutMiddleware([\Illuminate\Routing\Middleware\ThrottleRequests::class.':api'])
    ->middleware(\App\Http\Middleware\AdmitFaqRead::class);
```

The dedicated middleware admits invalid and authenticated callers into separate quotas before the controller; it does not rely on default middleware priority placing custom authentication before a shared throttle. Add to `.env.example` the three config variables above with an empty token and numeric defaults 600/30.

`B/tests/Feature/FaqReadApiTest.php`:

```php
<?php
namespace Tests\Feature;
use App\Models\Faq;
use App\Models\FaqCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
final class FaqReadApiTest extends TestCase
{
    use RefreshDatabase;
    #[Test] public function IntegrationTokenAndIndependentBudgetProtectWrites(): void
    {
        config(['faq-analytics.integration_token' => 'test-only-faq-token',
            'faq-analytics.integration_requests_per_minute' => 100]);
        Cache::flush();
        $category = FaqCategory::create(['name' => 'General', 'slug' => 'api-reads', 'is_published' => true]);
        $faq = Faq::create(['category_id' => $category->id, 'question' => 'Question', 'answer' => 'Answer', 'is_published' => true]);
        $this->postJson('/api/faq-reads', ['faq_id' => $faq->id, 'event_key' => str_repeat('a', 64)])->assertUnauthorized();
        $this->assertDatabaseCount('faq_read_receipts', 0);
        $this->withToken('test-only-faq-token');
        for ($request_number = 1; $request_number <= 61; $request_number++) {
            $this->postJson('/api/faq-reads', ['faq_id' => $faq->id,
                'event_key' => str_pad(dechex($request_number), 64, '0', STR_PAD_LEFT)])->assertNoContent();
        }
        $this->assertDatabaseHas('faq_daily_reads', ['faq_reference_id' => $faq->id, 'read_count' => 61]);
        $this->postJson('/api/faq-reads', ['faq_id' => $faq->id,
            'event_key' => str_pad('1', 64, '0', STR_PAD_LEFT)])->assertNoContent();
        $this->assertDatabaseCount('faq_read_receipts', 61);
    }
}
```

Flush only the array cache in the isolated test. Never flush a user/shared production cache as test setup.

## Task 3: Query inclusive Bangkok ranges with retained historical labels

**Files:**

- Create: `B/app/Services/Faq/FaqReadRange.php`, `B/app/Services/Faq/FaqReadReport.php`.
- Test: `B/tests/Feature/FaqReadReportTest.php`.

- **Consumes:** Task 1 aggregates/snapshots, existing FAQ/category localization, validated report query parameters.
- **Produces:** `FaqReadRange::From(array $parameters): self`; `FaqReadReport::Build(FaqReadRange $range, string $locale): LengthAwarePaginator`.
- **Checkpoint:** None. Query current FAQ content globally, matching existing FAQ administration; do not invent branch attribution.

**Actions — production behavior changes:**

**REQUIRED IMPLEMENTATION SKILL:** `test-driven-development`.

- [ ] Add each test below separately and run B `php vendor/bin/phpunit --do-not-cache-result --filter FaqReadReportTest`. Establish a loadable boundary first; RED must show incorrect range/count/label, not missing test setup.
- [ ] Implement the range and bounded report queries. GREEN: today's 7d/30d ranges include six/29 preceding Bangkok dates; custom endpoints are inclusive; zero-count live FAQs and positive-count deleted FAQs appear.
- [ ] Add reversed/future dates, invalid leap dates, absent custom bounds, invalid page/range, pre-collection dates, English fallback, tie ordering, latest deleted snapshots outside the selected range, and 21-row pagination cases one at a time.
- [ ] Verify batch label lookup stays bounded as rows increase. Date-range totals use `faq_reads_date_faq_index`; historical label lookup is restricted to the deleted IDs on the current page and uses the FAQ/date unique index. Refactor without changing counts.

`FaqReadRange.php`:

```php
<?php
namespace App\Services\Faq;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Validator;
final class FaqReadRange
{
    public function __construct(public readonly string $range, public readonly string $start_date,
        public readonly string $end_date, public readonly int $page) {}
    public static function From(array $parameters): self
    {
        $today = CarbonImmutable::now('Asia/Bangkok')->startOfDay();
        $validated = Validator::make($parameters, [
            'range' => ['sometimes', 'required', 'in:7d,30d,custom'],
            'page' => ['sometimes', 'required', 'integer', 'min:1'],
        ])->validate();
        $range = $validated['range'] ?? '30d';
        $page = (int) ($validated['page'] ?? 1);
        if ($range !== 'custom') {
            return new self($range, $today->subDays($range === '7d' ? 6 : 29)->toDateString(), $today->toDateString(), $page);
        }
        $dates = Validator::make($parameters, [
            'start_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:end_date'],
            'end_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:'.$today->toDateString()],
        ])->validate();
        return new self($range, $dates['start_date'], $dates['end_date'], $page);
    }
}
```

`FaqReadReport.php`:

```php
<?php
namespace App\Services\Faq;
use App\Models\Faq;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
final class FaqReadReport
{
    public function Build(FaqReadRange $range, string $locale): LengthAwarePaginator
    {
        $totals = DB::table('faq_daily_reads')->select('faq_reference_id')
            ->selectRaw('SUM(read_count) AS read_count')
            ->whereBetween('read_date', [$range->start_date, $range->end_date])->groupBy('faq_reference_id');
        $candidates = DB::table('faqs')->select('id AS faq_reference_id')->union(
            DB::table('faq_daily_reads')->select('faq_reference_id')
                ->whereBetween('read_date', [$range->start_date, $range->end_date])->where('read_count', '>', 0)
        );
        $rows = DB::query()->fromSub($candidates, 'faq_candidates')
            ->leftJoinSub($totals, 'totals', 'totals.faq_reference_id', '=', 'faq_candidates.faq_reference_id')
            ->select('faq_candidates.faq_reference_id')->selectRaw('COALESCE(totals.read_count, 0) AS read_count')
            ->orderByDesc('read_count')->orderBy('faq_candidates.faq_reference_id')
            ->paginate(20, ['*'], 'page', $range->page);
        $faq_ids = $rows->getCollection()->pluck('faq_reference_id')->map(fn ($faq_id): int => (int) $faq_id)->all();
        $current = Faq::with('category')->whereKey($faq_ids)->get()->keyBy('id');
        $deleted_ids = array_values(array_diff($faq_ids, $current->keys()->all()));
        $snapshots = collect();
        if ($deleted_ids !== []) {
            $latest_dates = DB::table('faq_daily_reads')->select('faq_reference_id')
                ->selectRaw('MAX(read_date) AS latest_read_date')->whereIn('faq_reference_id', $deleted_ids)->groupBy('faq_reference_id');
            $snapshots = DB::table('faq_daily_reads AS history')->joinSub($latest_dates, 'latest', function ($join): void {
                $join->on('history.faq_reference_id', '=', 'latest.faq_reference_id')
                    ->on('history.read_date', '=', 'latest.latest_read_date');
            })->select('history.*')->get()->keyBy('faq_reference_id');
        }
        return $rows->through(function ($row) use ($current, $snapshots, $locale): array {
            $faq = $current->get((int) $row->faq_reference_id);
            $snapshot = $snapshots->get((int) $row->faq_reference_id);
            if ($faq === null && $snapshot === null) {
                throw new \RuntimeException('Missing FAQ historical label');
            }
            return ['faq_reference_id' => (int) $row->faq_reference_id, 'read_count' => (int) $row->read_count,
                'question' => $faq !== null ? $faq->LocalizedQuestion($locale)
                    : ($locale === 'en' && filled($snapshot->question_en) ? $snapshot->question_en : $snapshot->question_th),
                'category' => $faq !== null ? ($faq->category?->LocalizedName($locale) ?? '-')
                    : ($locale === 'en' && filled($snapshot->category_en) ? $snapshot->category_en : $snapshot->category_th),
                'is_deleted' => $faq === null];
        });
    }
}
```

`B/tests/Feature/FaqReadReportTest.php`:

```php
<?php
namespace Tests\Feature;
use App\Models\Faq;
use App\Models\FaqCategory;
use App\Services\Faq\FaqReadRange;
use App\Services\Faq\FaqReadRecorder;
use App\Services\Faq\FaqReadReport;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
final class FaqReadReportTest extends TestCase
{
    use RefreshDatabase;
    protected function tearDown(): void { CarbonImmutable::setTestNow(); parent::tearDown(); }
    #[Test] public function PresetsUseBangkokAndIncludeToday(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-10-20T18:00:00Z'));
        $seven = FaqReadRange::From(['range' => '7d']);
        $this->assertSame('2026-10-15', $seven->start_date);
        $this->assertSame('2026-10-21', $seven->end_date);
        $this->assertSame('2026-09-22', FaqReadRange::From([])->start_date);
    }
    #[Test] public function DeletedHistoryAndLiveZeroCountsAppearWithoutDuplicateRows(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-10-20T01:00:00Z'));
        $category = FaqCategory::create(['name' => 'Primary category', 'name_en' => 'General', 'slug' => 'report-fixture', 'is_published' => true]);
        $read_faq = Faq::create(['category_id' => $category->id, 'question' => 'Primary read',
            'question_en' => 'Read question', 'answer' => 'Answer', 'is_published' => true]);
        $zero_faq = Faq::create(['category_id' => $category->id, 'question' => 'Primary zero',
            'question_en' => 'Zero question', 'answer' => 'Answer', 'is_published' => false]);
        app(FaqReadRecorder::class)->Record($read_faq->id, str_repeat('a', 64));
        $read_id = $read_faq->id;
        $read_faq->delete();
        $report = app(FaqReadReport::class)->Build(FaqReadRange::From(['range' => 'custom',
            'start_date' => '2026-10-20', 'end_date' => '2026-10-20']), 'en');
        $rows = $report->getCollection();
        $this->assertSame(['faq_reference_id' => $read_id, 'read_count' => 1,
            'question' => 'Read question', 'category' => 'General', 'is_deleted' => true], $rows->first());
        $this->assertSame(0, $rows->firstWhere('faq_reference_id', $zero_faq->id)['read_count']);
        $this->assertSame($rows->count(), $rows->pluck('faq_reference_id')->unique()->count());
    }
}
```

The repository imports 18 FAQs during migrations. For tests asserting exact pagination positions or a zero-row report, isolate the fixture by removing only test-database content after explicitly verifying SQLite `:memory:`; never assume the imported records do not exist. Alternatively compare requested fixture rows across all paginator pages instead of assuming they lie on page 1.

## Task 4: Expose the authorized backoffice statistics page

**Files:**

- Create: `B/app/Http/Controllers/FaqStatisticsController.php`.
- Create: `B/resources/views/faqs/statistics.blade.php`.
- Modify: `B/routes/web.php` FAQ routes inside existing authenticated group.
- Modify: `B/resources/views/faqs/index.blade.php` existing `@can('faq-list')` actions.
- Modify: `B/lang/en/content-management.php`, `B/lang/th/content-management.php` add `faq.statistics` keys.
- Test: `B/tests/Feature/FaqStatisticsTest.php`.

- **Consumes:** Task 3 range/report; existing `faq-list`, active-account/branch middleware and shared backoffice layout.
- **Produces:** Localized paginated report and usable date controls, with explicit failure status instead of fabricated zeros.
- **Checkpoint:** None. Do not add permissions, grant roles, or broaden branch/account access.

**Actions — production behavior changes:**

**REQUIRED IMPLEMENTATION SKILL:** `test-driven-development`.

- [ ] Add guest/permission/authorized route tests and run B `php vendor/bin/phpunit --do-not-cache-result --filter 'FaqStatisticsTest|ContentManagementLocalizedPageTest'`. RED: new authorized route is missing. Keep all relevant middleware enabled.
- [ ] Add controller, view, route and matching locale keys. GREEN: an active user with `faq-list` sees the report; a user lacking it or with disabled/inactive branch cannot.
- [ ] Add inclusive ranges/pagination query retention, new-filter page reset, valid empty/zero rows, deleted-label escaping and forced report-query failure tests. A database failure must return 503 and contain the localized failure text with no result table.
- [ ] Run the focused group and full backend suite. Inspect keyboard date selection, associated labels/error text and responsive table readability. Refactor only after checks pass.

`FaqStatisticsController.php`:

```php
<?php
namespace App\Http\Controllers;
use App\Services\Faq\FaqReadRange;
use App\Services\Faq\FaqReadReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;
final class FaqStatisticsController extends Controller
{
    public function __construct() { $this->middleware('permission:faq-list'); }
    public function Index(Request $request, FaqReadReport $report)
    {
        $range = FaqReadRange::From($request->query());
        try {
            $rows = $report->Build($range, app()->getLocale());
            $rows->appends(['range' => $range->range, 'start_date' => $range->start_date, 'end_date' => $range->end_date]);
        } catch (Throwable $exception) {
            Log::warning('faq.statistics.failed', ['operation' => 'faq.statistics.read', 'outcome' => 'failed',
                'error_code' => 'FAQ_STATISTICS_UNAVAILABLE', 'correlation_id' => (string) Str::uuid()]);
            return response()->view('faqs.statistics', ['range' => $range, 'rows' => null, 'report_error' => true], 503);
        }
        return view('faqs.statistics', ['range' => $range, 'rows' => $rows, 'report_error' => false]);
    }
}
```

Add `/faqs/statistics` before the existing FAQ dynamic routes, inside the existing `auth` group:

```php
Route::get('/faqs/statistics', [\App\Http\Controllers\FaqStatisticsController::class, 'Index'])->name('faqs.statistics');
```

Within the list's `@can('faq-list')` action area add an ordinary link with the existing `btn btn-outline-primary mr-2` classes, `href="{{ route('faqs.statistics') }}"`, and `__('content-management.faq.statistics.title')` text.

`statistics.blade.php`:

```blade
@extends('layouts.app')
@section('content')
<section class="content-header"><h1>{{ __('content-management.faq.statistics.title') }}</h1></section>
<section class="content"><div class="card"><div class="card-body">
    <p>{{ __('content-management.faq.statistics.metric') }}</p>
    <p>Asia/Bangkok: {{ $range->start_date }} - {{ $range->end_date }}</p>
    <form method="GET" action="{{ route('faqs.statistics') }}" class="mb-4">
        <label for="faq-range">{{ __('content-management.faq.statistics.range') }}</label>
        <select id="faq-range" name="range" class="form-control mb-2">
            @foreach(['7d' => 'seven_days', '30d' => 'thirty_days', 'custom' => 'custom'] as $range_key => $label_key)
                <option value="{{ $range_key }}" @selected(old('range', $range->range) === $range_key)>{{ __('content-management.faq.statistics.'.$label_key) }}</option>
            @endforeach
        </select>
        @foreach(['start_date', 'end_date'] as $date_key)
            <label for="faq-{{ str_replace('_', '-', $date_key) }}">{{ __('content-management.faq.statistics.'.$date_key) }}</label>
            <input class="form-control mb-2" type="date" id="faq-{{ str_replace('_', '-', $date_key) }}" name="{{ $date_key }}"
                value="{{ old($date_key, $range->{$date_key}) }}" aria-describedby="faq-{{ str_replace('_', '-', $date_key) }}-error">
            @error($date_key)<p role="alert" id="faq-{{ str_replace('_', '-', $date_key) }}-error">{{ $message }}</p>@enderror
        @endforeach
        @error('range')<p role="alert">{{ $message }}</p>@enderror
        @error('page')<p role="alert">{{ $message }}</p>@enderror
        <button class="btn btn-primary" type="submit">{{ __('content-management.faq.statistics.apply') }}</button>
    </form>
    @if($report_error)
        <p role="alert" class="alert alert-danger">{{ __('content-management.faq.statistics.unavailable') }}</p>
    @else
        <div class="table-responsive"><table class="table table-bordered" data-faq-statistics="true">
            <thead><tr><th>{{ __('content-management.faq.table.question') }}</th><th>{{ __('content-management.faq.table.category') }}</th><th>{{ __('content-management.faq.statistics.reads') }}</th></tr></thead>
            <tbody>@forelse($rows as $row)
                <tr data-faq-id="{{ $row['faq_reference_id'] }}"><td>{{ $row['question'] }}
                    @if($row['is_deleted'])<span class="badge badge-secondary">{{ __('content-management.faq.statistics.deleted') }}</span>@endif
                </td><td>{{ $row['category'] }}</td><td>{{ $row['read_count'] }}</td></tr>
            @empty
                <tr><td colspan="3">{{ __('content-management.faq.statistics.empty') }}</td></tr>
            @endforelse</tbody>
        </table></div>
        {{ $rows->links() }}
    @endif
</div></div></section>
@endsection
```

The form intentionally submits no `page`; changing range starts at page 1. Only render `aria-describedby` for a date error when its referenced error element exists. Add `aria-invalid="true"` on invalid controls. Existing Laravel validation provides field-specific errors; do not run `Build` after invalid range validation.

Add this `statistics` array within the existing `faq` namespace in English:

```php
'statistics' => [
    'title' => 'FAQ Read Statistics', 'range' => 'Date range', 'seven_days' => 'Last 7 days',
    'thirty_days' => 'Last 30 days', 'custom' => 'Custom dates', 'start_date' => 'Start date',
    'end_date' => 'End date', 'apply' => 'Apply dates', 'reads' => 'Recorded reads',
    'deleted' => 'Deleted question', 'empty' => 'No FAQ records in this range.',
    'metric' => 'First recorded opening per question and website session. Counts do not represent distinct people or completed reading.',
    'unavailable' => 'Read statistics could not be loaded. Please refresh and try again.',
],
```

Add the following array under the same `faq` namespace in `B/lang/th/content-management.php`. Keep existing question/category column translations. Validate key parity and rendered Thai/English pages; no raw translation keys may appear.

```php
'statistics' => [
    'title' => "\u{0E2A}\u{0E16}\u{0E34}\u{0E15}\u{0E34}\u{0E01}\u{0E32}\u{0E23}\u{0E40}\u{0E1B}\u{0E34}\u{0E14}\u{0E2D}\u{0E48}\u{0E32}\u{0E19} FAQ",
    'range' => "\u{0E0A}\u{0E48}\u{0E27}\u{0E07}\u{0E27}\u{0E31}\u{0E19}\u{0E17}\u{0E35}\u{0E48}",
    'seven_days' => "7 \u{0E27}\u{0E31}\u{0E19}\u{0E25}\u{0E48}\u{0E32}\u{0E2A}\u{0E38}\u{0E14}",
    'thirty_days' => "30 \u{0E27}\u{0E31}\u{0E19}\u{0E25}\u{0E48}\u{0E32}\u{0E2A}\u{0E38}\u{0E14}",
    'custom' => "\u{0E01}\u{0E33}\u{0E2B}\u{0E19}\u{0E14}\u{0E27}\u{0E31}\u{0E19}\u{0E17}\u{0E35}\u{0E48}",
    'start_date' => "\u{0E27}\u{0E31}\u{0E19}\u{0E17}\u{0E35}\u{0E48}\u{0E40}\u{0E23}\u{0E34}\u{0E48}\u{0E21}\u{0E15}\u{0E49}\u{0E19}",
    'end_date' => "\u{0E27}\u{0E31}\u{0E19}\u{0E17}\u{0E35}\u{0E48}\u{0E2A}\u{0E34}\u{0E49}\u{0E19}\u{0E2A}\u{0E38}\u{0E14}",
    'apply' => "\u{0E41}\u{0E2A}\u{0E14}\u{0E07}\u{0E1C}\u{0E25}",
    'reads' => "\u{0E22}\u{0E2D}\u{0E14}\u{0E40}\u{0E1B}\u{0E34}\u{0E14}\u{0E2D}\u{0E48}\u{0E32}\u{0E19}\u{0E17}\u{0E35}\u{0E48}\u{0E1A}\u{0E31}\u{0E19}\u{0E17}\u{0E36}\u{0E01}",
    'deleted' => "\u{0E04}\u{0E33}\u{0E16}\u{0E32}\u{0E21}\u{0E17}\u{0E35}\u{0E48}\u{0E25}\u{0E1A}\u{0E41}\u{0E25}\u{0E49}\u{0E27}",
    'empty' => "\u{0E44}\u{0E21}\u{0E48}\u{0E1E}\u{0E1A}\u{0E23}\u{0E32}\u{0E22}\u{0E01}\u{0E32}\u{0E23} FAQ \u{0E43}\u{0E19}\u{0E0A}\u{0E48}\u{0E27}\u{0E07}\u{0E27}\u{0E31}\u{0E19}\u{0E17}\u{0E35}\u{0E48}\u{0E19}\u{0E35}\u{0E49}",
    'metric' => "\u{0E19}\u{0E31}\u{0E1A}\u{0E01}\u{0E32}\u{0E23}\u{0E40}\u{0E1B}\u{0E34}\u{0E14}\u{0E2D}\u{0E48}\u{0E32}\u{0E19}\u{0E04}\u{0E23}\u{0E31}\u{0E49}\u{0E07}\u{0E41}\u{0E23}\u{0E01}\u{0E15}\u{0E48}\u{0E2D}\u{0E04}\u{0E33}\u{0E16}\u{0E32}\u{0E21}\u{0E43}\u{0E19}\u{0E41}\u{0E15}\u{0E48}\u{0E25}\u{0E30} session \u{0E02}\u{0E2D}\u{0E07}\u{0E40}\u{0E27}\u{0E47}\u{0E1A}\u{0E44}\u{0E0B}\u{0E15}\u{0E4C} \u{0E22}\u{0E2D}\u{0E14}\u{0E19}\u{0E35}\u{0E49}\u{0E44}\u{0E21}\u{0E48}\u{0E43}\u{0E0A}\u{0E48}\u{0E08}\u{0E33}\u{0E19}\u{0E27}\u{0E19}\u{0E1A}\u{0E38}\u{0E04}\u{0E04}\u{0E25}\u{0E2B}\u{0E23}\u{0E37}\u{0E2D}\u{0E08}\u{0E33}\u{0E19}\u{0E27}\u{0E19}\u{0E1C}\u{0E39}\u{0E49}\u{0E17}\u{0E35}\u{0E48}\u{0E2D}\u{0E48}\u{0E32}\u{0E19}\u{0E08}\u{0E1A}",
    'unavailable' => "\u{0E42}\u{0E2B}\u{0E25}\u{0E14}\u{0E2A}\u{0E16}\u{0E34}\u{0E15}\u{0E34}\u{0E44}\u{0E21}\u{0E48}\u{0E44}\u{0E14}\u{0E49} \u{0E01}\u{0E23}\u{0E38}\u{0E13}\u{0E32}\u{0E23}\u{0E35}\u{0E40}\u{0E1F}\u{0E23}\u{0E0A}\u{0E41}\u{0E25}\u{0E49}\u{0E27}\u{0E25}\u{0E2D}\u{0E07}\u{0E43}\u{0E2B}\u{0E21}\u{0E48}",
],
```

`B/tests/Feature/FaqStatisticsTest.php`:

```php
<?php
namespace Tests\Feature;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
final class FaqStatisticsTest extends TestCase
{
    use RefreshDatabase;
    private function Actor(bool $can_view): User
    {
        $department = Department::find(Department::HQ_ID);
        if ($department === null) {
            $department = Department::forceCreate(['id' => Department::HQ_ID,
                'name' => 'FAQ Statistics HQ', 'code' => 'FAQ-HQ', 'status' => 1, 'enabled_modules' => null]);
        } else {
            $department->update(['status' => 1, 'enabled_modules' => null]);
        }
        $actor = User::factory()->create(['department_id' => $department->id, 'disable' => 0, 'language_code' => 'en']);
        if ($can_view) { $actor->givePermissionTo(Permission::findOrCreate('faq-list', 'web')); }
        return $actor;
    }
    #[Test] public function ReportRequiresExistingPermissionAndActiveAccount(): void
    {
        $this->getJson('/faqs/statistics')->assertUnauthorized();
        $this->actingAs($this->Actor(false))->get('/faqs/statistics')->assertForbidden();
        $actor = $this->Actor(true);
        $this->actingAs($actor)->get('/faqs/statistics')->assertOk()
            ->assertSee('FAQ Read Statistics')->assertSee('data-faq-statistics="true"', false);
        $actor->update(['disable' => 1]);
        $this->actingAs($actor->refresh())->get('/faqs/statistics')->assertForbidden();
    }
    #[Test] public function InvalidCustomDatesDoNotBecomeAZeroCountReport(): void
    {
        $this->actingAs($this->Actor(true))->getJson('/faqs/statistics?range=custom&start_date=2026-02-30&end_date=2026-03-01')
            ->assertUnprocessable()->assertJsonValidationErrors('start_date');
    }
}
```

## Task 5: Derive identity on the public server and forward safely

**Files:**

- Create: `P/config/faq-analytics.php`.
- Create: `P/app/Services/Faq/FaqReadKey.php`, `P/app/Services/Faq/FaqReadForwarder.php`.
- Create: `P/app/Http/Controllers/FaqReadController.php`.
- Modify: `P/routes/web.php` add numeric recording POST; `P/.env.example` append blank secrets and disabled defaults.
- Modify: `P/lang/en/faq.php`, `P/lang/th/faq.php` add `recording_unavailable`.
- Test: `P/tests/Unit/FaqReadKeyTest.php`, `P/tests/Feature/FaqReadProxyTest.php`.

- **Consumes:** Existing server session/CSRF middleware, backend Task 2 endpoint, existing configured backend base URL.
- **Produces:** Stable per-session/FAQ HMAC, same-origin public adapter, 60/session/minute and 120/IP/minute defaults, no browser secrets.
- **Checkpoint:** Only fake backend requests in automated tests. Provision secrets outside source control during a separately approved rollout; leave the flag disabled.

**Actions — production behavior changes:**

**REQUIRED IMPLEMENTATION SKILL:** `test-driven-development`.

- [ ] Add the known-vector unit test, establish a loadable pure boundary, observe an incorrect digest as RED, then add `FaqReadKey`. The expected vector below was independently calculated with .NET HMACSHA256 during planning.
- [ ] Add public route tests, then B-independent HTTP forwarding code. P `php vendor/bin/phpunit --do-not-cache-result --filter 'FaqReadKeyTest|FaqReadProxyTest'`. RED: no route/forwarding behavior; GREEN: request uses the actual server session and sends exactly two integration fields with the dedicated token.
- [ ] Add flag off, missing/short key, missing token, insecure/malformed backend URL, forged body fields, missing prior session, CSRF failure, cross-origin header, numeric ID bounds, throttling, timeout, upstream 401/429/503 and redirect cases separately. No client credential, upstream body, or raw session ID may appear in the response/log.
- [ ] Verify repeated calls, language changes and different local dates use the same digest; new session/question changes it. Check the HTTP client's certificate verification, redirect policy, timeout options and lack of implicit retry at the fake HTTP boundary. Refactor while green.

`P/config/faq-analytics.php`:

```php
<?php
return [
    'enabled' => (bool) env('FAQ_READ_TRACKING_ENABLED', false),
    'hash_key' => env('FAQ_READ_HASH_KEY', ''),
    'integration_token' => env('FAQ_READ_INTEGRATION_TOKEN', ''),
    'session_requests_per_minute' => (int) env('FAQ_READ_SESSION_REQUESTS_PER_MINUTE', 60),
    'ip_requests_per_minute' => (int) env('FAQ_READ_IP_REQUESTS_PER_MINUTE', 120),
    'connect_timeout_seconds' => (int) env('FAQ_READ_CONNECT_TIMEOUT_SECONDS', 1),
    'timeout_seconds' => (int) env('FAQ_READ_TIMEOUT_SECONDS', 3),
];
```

Append these variables to P `.env.example` with `false`, blank keys/token and the numeric defaults above. Backend and public integration token must match; hash key exists only on public servers and stays stable across ordinary deployments. Rotating the integration token must not rotate the hash key. A hash-key change requires the deliberate session transition already specified, not silent regeneration.

`FaqReadKey.php`:

```php
<?php
namespace App\Services\Faq;
final class FaqReadKey
{
    public static function Derive(int $faq_id, string $session_id, string $hash_key): string
    {
        return hash_hmac('sha256', json_encode(['version' => 1, 'session_id' => $session_id,
            'faq_id' => $faq_id], JSON_THROW_ON_ERROR), $hash_key);
    }
}
```

`FaqReadForwarder.php`:

```php
<?php
namespace App\Services\Faq;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
final class FaqReadForwarder
{
    public function IsConfigured(): bool
    {
        $base_url = (string) config('services.eimaths_backend.base_url');
        $parts = parse_url($base_url);
        return (bool) config('faq-analytics.enabled') && strlen((string) config('faq-analytics.hash_key')) >= 32
            && (string) config('faq-analytics.integration_token') !== '' && is_array($parts)
            && ($parts['scheme'] ?? '') === 'https' && !empty($parts['host'])
            && filter_var($base_url, FILTER_VALIDATE_URL) !== false
            && !isset($parts['user']) && !isset($parts['pass']) && !isset($parts['query']) && !isset($parts['fragment']);
    }
    public function Forward(int $faq_id, string $event_key, string $correlation_id): array
    {
        $base_url = rtrim((string) config('services.eimaths_backend.base_url'), '/');
        try {
            $response = Http::acceptJson()->withToken((string) config('faq-analytics.integration_token'))
                ->withHeaders(['X-Request-Id' => $correlation_id])
                ->connectTimeout(max(1, (int) config('faq-analytics.connect_timeout_seconds')))
                ->timeout(max(1, (int) config('faq-analytics.timeout_seconds')))
                ->withOptions(['allow_redirects' => false, 'verify' => true])
                ->post($base_url.'/api/faq-reads', ['faq_id' => $faq_id, 'event_key' => $event_key]);
        } catch (ConnectionException $exception) {
            $this->Failure($faq_id, $correlation_id, null);
            return ['status' => 503, 'retry_after' => null];
        }
        $status = in_array($response->status(), [204, 404, 409, 422, 429], true) ? $response->status() : 503;
        if ($status !== 204) {
            $this->Failure($faq_id, $correlation_id, $response->status());
        }
        $retry_after = $response->header('Retry-After');
        return ['status' => $status, 'retry_after' => $status === 429 && is_string($retry_after)
            && ctype_digit($retry_after) ? max(1, min(3600, (int) $retry_after)) : null];
    }
    private function Failure(int $faq_id, string $correlation_id, ?int $upstream_status): void
    {
        Log::warning('faq.read.forward_failed', ['operation' => 'faq.read.forward', 'faq_id' => $faq_id,
            'outcome' => 'failed', 'error_code' => 'FAQ_READ_UNAVAILABLE',
            'upstream_status' => $upstream_status, 'correlation_id' => $correlation_id]);
    }
}
```

The configured URL rejects either userinfo field individually, including a username without a password. It is never obtained from browser input. No additional HTTP retry layer is allowed.

`P/app/Http/Controllers/FaqReadController.php`:

```php
<?php
namespace App\Http\Controllers;
use App\Services\Faq\FaqReadForwarder;
use App\Services\Faq\FaqReadKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
final class FaqReadController extends Controller
{
    public function Store(Request $request, string $faq_id, FaqReadForwarder $forwarder)
    {
        if (!$forwarder->IsConfigured()) { return $this->Failure(503); }
        $validated_id = filter_var($faq_id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($validated_id === false || array_diff(array_keys($request->all()), ['_token']) !== []) {
            return $this->Failure(422);
        }
        if (!$request->hasPreviousSession() || !$request->session()->isStarted()) { return $this->Failure(419); }
        $origin = $request->header('Origin');
        if ($origin !== null && $origin !== $request->getSchemeAndHttpHost()) { return $this->Failure(403); }
        $session_id = $request->session()->getId();
        $limits = [
            ['faq-read:session:'.hash('sha256', $session_id), (int) config('faq-analytics.session_requests_per_minute')],
            ['faq-read:ip:'.hash('sha256', (string) $request->ip()), (int) config('faq-analytics.ip_requests_per_minute')],
        ];
        try {
            foreach ($limits as [$limit_key, $limit]) {
                if (RateLimiter::hit($limit_key, 60) > max(1, $limit)) {
                    return $this->Failure(429, max(1, RateLimiter::availableIn($limit_key)));
                }
            }
        } catch (\Throwable $exception) {
            \Illuminate\Support\Facades\Log::warning('faq.read.admission_failed', [
                'operation' => 'faq.read.admission', 'faq_id' => $validated_id, 'error_code' => 'FAQ_READ_UNAVAILABLE',
            ]);
            return $this->Failure(503);
        }
        $event_key = FaqReadKey::Derive($validated_id, $session_id, (string) config('faq-analytics.hash_key'));
        $result = $forwarder->Forward($validated_id, $event_key, (string) Str::uuid());
        return $result['status'] === 204 ? response()->noContent() : $this->Failure($result['status'], $result['retry_after']);
    }
    private function Failure(int $status, ?int $retry_after = null)
    {
        return response()->json(['error_code' => $status === 429 ? 'FAQ_READ_RATE_LIMITED' : 'FAQ_READ_UNAVAILABLE',
            'message' => __('faq.recording_unavailable')], $status,
            $retry_after === null ? [] : ['Retry-After' => (string) $retry_after]);
    }
}
```

Register inside P `routes/web.php` so existing web session/CSRF middleware applies:

```php
Route::post('/faq/{faq_id}/read', [\App\Http\Controllers\FaqReadController::class, 'Store'])
    ->whereNumber('faq_id')->name('faq.read');
```

Do not add this route to a CSRF exemption. Add English `recording_unavailable` = `The opening could not be recorded. You can continue reading.` and its faithful Thai translation to the existing FAQ language files. This message describes background recording only and never replaces page content.

Use this exact value in the Thai dictionary: `'recording_unavailable' => "\u{0E1A}\u{0E31}\u{0E19}\u{0E17}\u{0E36}\u{0E01}\u{0E01}\u{0E32}\u{0E23}\u{0E40}\u{0E1B}\u{0E34}\u{0E14}\u{0E2D}\u{0E48}\u{0E32}\u{0E19}\u{0E44}\u{0E21}\u{0E48}\u{0E44}\u{0E14}\u{0E49} \u{0E04}\u{0E38}\u{0E13}\u{0E22}\u{0E31}\u{0E07}\u{0E2D}\u{0E48}\u{0E32}\u{0E19}\u{0E04}\u{0E33}\u{0E15}\u{0E2D}\u{0E1A}\u{0E15}\u{0E48}\u{0E2D}\u{0E44}\u{0E14}\u{0E49}\u{0E15}\u{0E32}\u{0E21}\u{0E1B}\u{0E01}\u{0E15}\u{0E34}",`

`P/tests/Unit/FaqReadKeyTest.php`:

```php
<?php
namespace Tests\Unit;
use App\Services\Faq\FaqReadKey;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
final class FaqReadKeyTest extends TestCase
{
    #[Test] public function KeyMatchesIndependentVectorAndSeparatesQuestionsAndSessions(): void
    {
        $session_id = 'test-session-aaaaaaaaaaaaaaaaaaaaaaaaaaa';
        $hash_key = 'test-only-faq-hash-key-32-characters';
        $key = FaqReadKey::Derive(11, $session_id, $hash_key);
        $this->assertSame('3ecf686cd1b99efcf72d3e6a0f902045b02097182805062f68c16a22dcf02f8e', $key);
        $this->assertNotSame($key, FaqReadKey::Derive(12, $session_id, $hash_key));
        $this->assertNotSame($key, FaqReadKey::Derive(11, 'different-session', $hash_key));
    }
}
```

`P/tests/Feature/FaqReadProxyTest.php`:

```php
<?php
namespace Tests\Feature;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
final class FaqReadProxyTest extends TestCase
{
    private function EnableTracking(): void
    {
        config(['services.eimaths_backend.base_url' => 'https://backend.example.test',
            'faq-analytics.enabled' => true, 'faq-analytics.hash_key' => 'test-only-faq-hash-key-32-characters',
            'faq-analytics.integration_token' => 'test-only-faq-token']);
        Http::preventStrayRequests();
        $this->withSession(['_token' => 'test-csrf-token']);
        $this->withCookie(config('session.cookie'), app('session')->getId())->withCredentials();
    }
    #[Test] public function ProxySendsNoRawSessionOrClientChosenIdentity(): void
    {
        $this->EnableTracking();
        Http::fake(['https://backend.example.test/api/faq-reads' => Http::response('', 204)]);
        $this->postJson('/faq/11/read')->assertNoContent();
        Http::assertSent(function ($request): bool {
            $payload = $request->data();
            return $request->url() === 'https://backend.example.test/api/faq-reads'
                && $request->hasHeader('Authorization', 'Bearer test-only-faq-token')
                && $payload['faq_id'] === 11 && preg_match('/^[a-f0-9]{64}$/D', $payload['event_key']) === 1
                && count($payload) === 2;
        });
        $this->postJson('/faq/11/read', ['session_id' => 'forged', 'event_key' => str_repeat('a', 64)])->assertUnprocessable();
        Http::assertSentCount(1);
    }
    #[Test] public function CsrfIsEnforcedWithTheRealCookieAndSessionMiddleware(): void
    {
        $this->EnableTracking();
        $this->app->bind(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            fn ($application) => new class($application, $application['encrypter']) extends \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken {
                protected function runningUnitTests() { return false; }
            });
        Http::fake(['https://backend.example.test/api/faq-reads' => Http::response('', 204)]);
        $this->postJson('/faq/11/read')->assertStatus(419);
        Http::assertNothingSent();
        $this->withHeader('X-CSRF-TOKEN', 'test-csrf-token')->postJson('/faq/11/read')->assertNoContent();
        Http::assertSentCount(1);
    }
    #[Test] public function BadIntegrationCredentialsBecomeUnavailableWithoutExposingTheBackendBody(): void
    {
        $this->EnableTracking();
        Http::fake(['https://backend.example.test/api/faq-reads' => Http::response(['secret' => 'never-return-this'], 401)]);
        $this->postJson('/faq/11/read')->assertStatus(503)->assertJsonMissing(['secret' => 'never-return-this']);
    }
}
```

Laravel's testing mode bypasses CSRF by default; the preceding HTTP tests do not prove CSRF. Add a test-local subclass of the installed `Illuminate\Foundation\Http\Middleware\ValidateCsrfToken` overriding `protected function runningUnitTests() { return false; }`, and bind that class for this test only. Send a request without token and expect 419/no HTTP call; send `X-CSRF-TOKEN: test-csrf-token` with the same persisted session cookie and expect 204. Keep real middleware session/cookie handling. Restore container bindings between tests through the normal application lifecycle. Task 7 additionally tests real file-session persistence across fresh application instances.

## Task 6: Submit only qualifying browser openings, with bounded retry

**Files:**

- Create: `P/public/js/faq-read-tracking.js`, `P/resources/views/faq/read-tracking.blade.php`.
- Modify: `P/resources/views/faq.blade.php`, `P/resources/views/faq/detail.blade.php` include the shared tracking partial.
- Modify: `P/tests/browser/support/render-page.php` explicit test-only tracking configuration argument.
- Modify: `P/tests/browser/support/page-fixture.mjs::RenderPage` optional tracking argument forwarding only.
- Create: `P/tests/browser/faq-read-tracking.spec.mjs`.

- **Consumes:** Companion `FAQ_OPENED` event and detail marker; Task 5 same-origin route/configuration.
- **Produces:** Asynchronous opening request, one automatic retry after network/5xx, no background collection when disabled.
- **Checkpoint:** Companion UI/event task must be present before its real interaction tests. All test POSTs are intercepted locally; no real backend/server credentials.

**Actions — production behavior changes:**

**REQUIRED IMPLEMENTATION SKILL:** `test-driven-development`.

- [ ] Extend test fixture options only, then write the browser test below. P `node node_modules/@playwright/test/cli.js test tests/browser/faq-read-tracking.spec.mjs --reporter=line`. RED: opening produces no recording POST.
- [ ] Add the partial/module. GREEN: category/search/close do not post, opening does, repeated opening on the same page is suppressed, and the answer remains available on failure.
- [ ] Separately test visible detail, hidden detail waiting for visibility, 503 then 204 (two attempts), persistent 503 (maximum two), 429/422 (one), aborted/network request (maximum two), navigation cancellation, disabled config and JavaScript-disabled detail readability.
- [ ] Test list-to-detail/server deduplication in Task 7; browser-only request-count tests do not prove durable deduplication. Run the companion browser tests and complete browser suite; refactor while green.

`read-tracking.blade.php`:

```blade
@if(app(\App\Services\Faq\FaqReadForwarder::class)->IsConfigured())
    <script type="application/json" id="faq-read-config">{!! json_encode([
        'csrf_token' => csrf_token(),
    ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) !!}</script>
    @push('scripts')
        <script src="{{ asset('js/faq-read-tracking.js') }}" defer></script>
    @endpush
@endif
```

Include once within each view's `content` section. No secret, event key, timestamp, visitor ID, user account, or integration URL is serialized to JavaScript.

`faq-read-tracking.js`:

```javascript
(() => {
    const RETRY_DELAY_MS = 500;
    const REQUEST_TIMEOUT_MS = 4000;
    const config_element = document.getElementById('faq-read-config');
    if (!config_element) return;
    let tracking_config;
    try { tracking_config = JSON.parse(config_element.textContent); } catch { return; }
    if (typeof tracking_config.csrf_token !== 'string' || tracking_config.csrf_token === '') return;
    const attempted_faq_ids = new Set();
    const pending_controllers = new Set();
    const pending_timers = new Set();
    let is_unloading = false;
    function Delay(callback, milliseconds) {
        const timer = setTimeout(function RunDelayed() { pending_timers.delete(timer); callback(); }, milliseconds);
        pending_timers.add(timer);
        return timer;
    }
    async function SubmitOpening(faq_id, attempt_number) {
        if (is_unloading) return;
        const controller = new AbortController();
        pending_controllers.add(controller);
        const timeout_timer = Delay(function AbortTimedOut() { controller.abort(); }, REQUEST_TIMEOUT_MS);
        let should_retry = false;
        try {
            const response = await fetch(`/faq/${faq_id}/read`, {
                method: 'POST', credentials: 'same-origin', signal: controller.signal,
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json',
                    'X-CSRF-TOKEN': tracking_config.csrf_token }, body: '{}',
            });
            should_retry = response.status >= 500;
        } catch {
            should_retry = true;
        } finally {
            clearTimeout(timeout_timer);
            pending_timers.delete(timeout_timer);
            pending_controllers.delete(controller);
        }
        if (should_retry && attempt_number === 0 && !is_unloading) {
            Delay(function RetryOpening() { void SubmitOpening(faq_id, 1); }, RETRY_DELAY_MS);
        }
    }
    function RecordOpening(faq_id) {
        if (!Number.isSafeInteger(faq_id) || faq_id <= 0 || attempted_faq_ids.has(faq_id) || is_unloading) return;
        attempted_faq_ids.add(faq_id);
        void SubmitOpening(faq_id, 0);
    }
    function HandleOpening(event) { RecordOpening(event.detail?.faq_id); }
    document.addEventListener('FAQ_OPENED', HandleOpening);
    const detail_root = document.querySelector('[data-faq-detail][data-faq-id]');
    function RecordVisibleDetail() {
        if (document.visibilityState !== 'visible') return;
        document.removeEventListener('visibilitychange', RecordVisibleDetail);
        RecordOpening(Number(detail_root?.dataset.faqId));
    }
    if (detail_root) {
        if (document.visibilityState === 'visible') RecordVisibleDetail();
        else document.addEventListener('visibilitychange', RecordVisibleDetail);
    }
    window.addEventListener('pagehide', function StopPendingWork() {
        is_unloading = true;
        document.removeEventListener('FAQ_OPENED', HandleOpening);
        document.removeEventListener('visibilitychange', RecordVisibleDetail);
        for (const timer of pending_timers) clearTimeout(timer);
        for (const controller of pending_controllers) controller.abort();
        pending_timers.clear();
        pending_controllers.clear();
    });
    window.addEventListener('pageshow', function RestoreAfterHistory(event) {
        if (!event.persisted) return;
        is_unloading = false;
        document.addEventListener('FAQ_OPENED', HandleOpening);
        if (detail_root) {
            if (document.visibilityState === 'visible') RecordVisibleDetail();
            else document.addEventListener('visibilitychange', RecordVisibleDetail);
        }
    });
})();
```

The persisted `pageshow` handler restores listeners while preserving attempted IDs. Add a browser back/forward scenario; label a lifecycle-event simulation as such rather than claiming it proves actual browser bfcache behavior.

Fixture changes: add final optional parameter `tracking_enabled = false` to `RenderPage`; pass `'enabled'`/`'disabled'` as a new CLI argument after the existing reCAPTCHA argument. `render-page.php` validates that argument against exactly those two values, configures only test-only `faq-analytics.enabled`, hash key and token, and retains all existing HTTP fakes/restrictions. Old callers keep tracking disabled. Add optional `before_navigation = null` callback to `RenderPage`, invoked after its route handlers are installed and before `page.goto`; the analytics tests install their narrow POST interceptor there so it precedes the helper's abort-all-POST policy. Await the callback. Do not add an unrestricted network option.

`P/tests/browser/faq-read-tracking.spec.mjs`:

```javascript
import { expect, test } from '@playwright/test';
import { RenderPage } from './support/page-fixture.mjs';

test('Only opening an answer records, and a transient failure retries once', async function RecordOnOpen({ page }) {
    let post_count = 0;
    async function InterceptReads(fixture_page) {
        await fixture_page.route('http://eimaths.test/faq/11/read', async function Respond(route) {
            expect(route.request().method()).toBe('POST');
            expect(route.request().postDataJSON()).toEqual({});
            post_count++;
            await route.fulfill({ status: post_count === 1 ? 503 : 204, body: '' });
        });
    }
    await RenderPage(page, '/faq', 'en', 'disabled', true, InterceptReads);
    await page.getByLabel('Search questions and answers').fill('blocks');
    expect(post_count).toBe(0);
    await page.locator('.faq-toggle:visible').click();
    await expect(page.locator('#faq-11')).toBeVisible();
    await expect.poll(() => post_count).toBe(2);
    await page.locator('.faq-toggle:visible').click();
    await page.locator('.faq-toggle:visible').click();
    expect(post_count).toBe(2);
});

test('Visible detail sends a recording without hiding the answer', async function RecordDetail({ page }) {
    let post_count = 0;
    await RenderPage(page, '/faq/how-does-cpa-work', 'en', 'disabled', true, async function Intercept(fixture_page) {
        await fixture_page.route('http://eimaths.test/faq/11/read', async function Respond(route) {
            post_count++;
            await route.fulfill({ status: 429, body: '{}' });
        });
    });
    await expect(page.locator('[data-faq-detail] .faq-answer')).toContainText('blocks');
    await expect.poll(() => post_count).toBe(1);
    await page.clock.install();
    await page.clock.fastForward(5000);
    expect(post_count).toBe(1);
});
```

## Task 7: Prove concurrent persistence and real session reuse in isolation

**Files:**

- Create: `B/tests/Integration/FaqReadConcurrencyTest.php` (explicit opt-in; outside default Unit/Feature suites).
- Create: `P/tests/Feature/FaqReadSessionPersistenceTest.php`.
- Test: Real recorder/slug writer against an exclusive disposable MySQL-compatible database; public adapter across fresh application instances with a test-owned file-session directory.

- **Consumes:** Backend Tasks 1-2, public Task 5, companion slug writer; installed Symfony Process 6.4.33 in B.
- **Produces:** Evidence about concurrency, lost-response replay, first-date ownership and persisted session identity; not an assertion about uninspected deployed infrastructure.
- **Checkpoint:** A separately provisioned, empty, exclusive database with a dedicated account limited to that database must be supplied through the `FAQ_READ_TEST_*` environment. The database name must begin `faq_read_test_`; explicit `FAQ_READ_TEST_ALLOW_SCHEMA=true` is required. Do not fall back to `.env` credentials, use a shared database, run `migrate:fresh`, or install/start a database service without authorization. If unavailable, stop this task and report concurrency verification outstanding; continue unrelated safe work.

**Actions — nonbehavioral test and evidence artifacts:**

- [ ] Create the opt-in test below. It bootstraps through B's existing PHPUnit configuration (which initially forces an in-memory default), then explicitly selects the guarded test connection. Require a clean database before schema writes; apply only the four named migrations and clean up only the test-created objects.
- [ ] Run B `php vendor/bin/phpunit --do-not-cache-result --filter 'FaqReadConcurrencyTest::ConcurrentReceipts' tests/Integration/FaqReadConcurrencyTest.php` with the dedicated environment already configured. Expect exit 0, ten synchronized eight-worker duplicate rounds produce exactly ten receipts/counts; eight independent keys add eight; replay across midnight adds nothing and no empty day; a fresh later key adds one next-day count.
- [ ] Add the concurrent slug case to the same opt-in class: start eight workers using `FaqSlugWriter::Create` with identical English question and the fixture category; require eight distinct nonempty slugs with preserved source data. Verify intended-engine negative-count rejection and restrictive receipt FK. Record database/version/isolation, worker count, iterations, completion statuses and elapsed time without passwords or keys.
- [ ] Add public file-session test below and run P `php vendor/bin/phpunit --do-not-cache-result --filter FaqReadSessionPersistenceTest`. Three fresh application instances must reuse one persisted session and send identical keys; a new session must differ. This covers real session storage, not a synthetic browser localStorage identity.
- [ ] For already-green integration tests, analytically identify the defect each detects (removed receipt uniqueness, missing transaction, date in HMAC, array-only session assumptions). If empirically checking sensitivity, mutate only current-task agent-owned code in the isolated checkout, immediately restore it, and rerun. Do not call mutation evidence original RED/GREEN provenance.
- [ ] Run the real browser interaction tests from Task 6 and combine their evidence with these server/database checks. A static page fixture cannot prove deployed cross-tab behavior. Before live enablement, separately check that two browser tabs sharing the actual configured session store yield one persisted count.

Required environment keys (test credentials are provided securely, never pasted into the plan or command history): `FAQ_READ_TEST_DATABASE`, `FAQ_READ_TEST_HOST`, `FAQ_READ_TEST_PORT`, `FAQ_READ_TEST_USERNAME`, `FAQ_READ_TEST_PASSWORD`, `FAQ_READ_TEST_ALLOW_SCHEMA`. The password may intentionally be empty only for a dedicated local test account; absence and empty are distinguished. Do not print these values.

`B/tests/Integration/FaqReadConcurrencyTest.php`:

```php
<?php
namespace Tests\Integration;
use App\Services\Faq\FaqReadRecorder;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Process\Process;
use Tests\TestCase;
final class FaqReadConcurrencyTest extends TestCase
{
    private function ConfigureConnection(): void
    {
        $database_name = getenv('FAQ_READ_TEST_DATABASE');
        $this->assertSame('true', getenv('FAQ_READ_TEST_ALLOW_SCHEMA'), 'Dedicated test schema opt-in required.');
        $this->assertIsString($database_name);
        $this->assertMatchesRegularExpression('/^faq_read_test_[a-z0-9_]+$/D', $database_name);
        $this->assertFileDoesNotExist(base_path('bootstrap/cache/config.php'));
        foreach (['HOST', 'PORT', 'USERNAME', 'PASSWORD'] as $suffix) {
            $this->assertNotFalse(getenv('FAQ_READ_TEST_'.$suffix), 'Dedicated test connection variable missing.');
        }
        config(['database.connections.faq_read_test' => [
            'driver' => 'mysql', 'host' => getenv('FAQ_READ_TEST_HOST'), 'port' => getenv('FAQ_READ_TEST_PORT'),
            'database' => $database_name, 'username' => getenv('FAQ_READ_TEST_USERNAME'),
            'password' => getenv('FAQ_READ_TEST_PASSWORD'), 'unix_socket' => '', 'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci', 'prefix' => '', 'prefix_indexes' => true, 'strict' => true, 'engine' => 'InnoDB',
        ]]);
        DB::purge('faq_read_test');
        DB::setDefaultConnection('faq_read_test');
    }
    private function RunWorkers(int $faq_id, array $event_keys, string $now, string $mode = 'read'): void
    {
        $barrier_path = tempnam(sys_get_temp_dir(), 'faq-read-barrier-');
        $this->assertIsString($barrier_path);
        unlink($barrier_path);
        $connection_environment = [];
        foreach (['DATABASE', 'HOST', 'PORT', 'USERNAME', 'PASSWORD', 'ALLOW_SCHEMA'] as $suffix) {
            $connection_environment['FAQ_READ_TEST_'.$suffix] = getenv('FAQ_READ_TEST_'.$suffix);
        }
        $processes = [];
        $ready_paths = [];
        try {
            foreach ($event_keys as $worker_number => $event_key) {
                $ready_path = $barrier_path.'.ready-'.$worker_number;
                $ready_paths[] = $ready_path;
                $process = new Process([PHP_BINARY, 'vendor/bin/phpunit', '--do-not-cache-result',
                    '--filter', 'FaqReadConcurrencyTest::Worker', 'tests/Integration/FaqReadConcurrencyTest.php'],
                    base_path(), array_merge($connection_environment, ['FAQ_READ_WORKER_ID' => (string) $faq_id, 'FAQ_READ_WORKER_KEY' => $event_key,
                        'FAQ_READ_WORKER_NOW' => $now, 'FAQ_READ_WORKER_MODE' => $mode, 'FAQ_READ_WORKER_BARRIER' => $barrier_path,
                        'FAQ_READ_WORKER_READY' => $ready_path]), null, 30);
                $process->start();
                $processes[] = $process;
            }
            $deadline = microtime(true) + 20;
            while (count(array_filter($ready_paths, 'is_file')) !== count($ready_paths)) {
                clearstatcache();
                foreach ($processes as $process) {
                    if (!$process->isRunning() && !$process->isSuccessful()) {
                        $this->fail('Worker failed before barrier: '.$process->getOutput().$process->getErrorOutput());
                    }
                }
                $this->assertLessThan($deadline, microtime(true), 'Workers failed to become ready.');
                usleep(10000);
            }
            file_put_contents($barrier_path, 'go');
            foreach ($processes as $process) {
                $this->assertSame(0, $process->wait(), $process->getOutput().$process->getErrorOutput());
            }
        } finally {
            foreach ($processes as $process) { if ($process->isRunning()) $process->stop(1); }
            foreach (array_merge($ready_paths, [$barrier_path]) as $owned_path) {
                if (is_file($owned_path)) unlink($owned_path);
            }
        }
    }
    #[Test] public function ConcurrentReceipts(): void
    {
        $original_connection = config('database.default');
        $this->ConfigureConnection();
        $schema = DB::connection()->getSchemaBuilder();
        $this->assertSame([], $schema->getTableListing(), 'Test database must be empty and exclusive.');
        $applied_migrations = [];
        try {
            foreach (['2026_05_14_000000_create_faqs_table.php', '2026_08_27_000000_add_categories_and_english_to_faqs.php',
                '2026_10_20_000001_add_slug_to_faqs.php', '2026_10_20_000002_create_faq_read_analytics_tables.php'] as $migration_file) {
                $migration = require database_path('migrations/'.$migration_file);
                $migration->up();
                $applied_migrations[] = $migration;
            }
            $category_id = DB::table('faq_categories')->value('id');
            $faq_id = DB::table('faqs')->insertGetId(['category_id' => $category_id, 'question' => 'Concurrent question',
                'question_en' => 'Concurrent question', 'answer' => 'Answer', 'is_published' => true]);
            for ($round = 1; $round <= 10; $round++) {
                $event_key = str_pad(dechex($round), 64, '0', STR_PAD_LEFT);
                $this->RunWorkers($faq_id, array_fill(0, 8, $event_key), '2026-10-20T16:59:59Z');
            }
            $this->assertSame(10, DB::table('faq_read_receipts')->count());
            $this->assertSame(10, (int) DB::table('faq_daily_reads')->sum('read_count'));
            $independent_keys = array_map(fn ($number): string => str_pad(dechex($number), 64, '0', STR_PAD_LEFT), range(100, 107));
            $this->RunWorkers($faq_id, $independent_keys, '2026-10-20T16:59:59Z');
            $this->RunWorkers($faq_id, array_fill(0, 8, str_pad('1', 64, '0', STR_PAD_LEFT)), '2026-10-20T17:00:01Z');
            $this->assertSame(18, DB::table('faq_read_receipts')->count());
            $this->assertSame(18, (int) DB::table('faq_daily_reads')->sum('read_count'));
            $this->assertSame(1, DB::table('faq_daily_reads')->count());
            $this->RunWorkers($faq_id, [str_repeat('f', 64)], '2026-10-20T17:00:01Z');
            $this->assertSame(1, (int) DB::table('faq_daily_reads')->where('read_date', '2026-10-21')->value('read_count'));
            $this->RunWorkers($faq_id, array_fill(0, 8, str_repeat('e', 64)), '2026-10-21T01:00:00Z', 'slug');
            $slugs = DB::table('faqs')->whereNotNull('slug')->pluck('slug');
            $this->assertCount(8, $slugs);
            $this->assertCount(8, $slugs->unique());
            $this->assertContains('concurrent-slug-question', $slugs->all());
            $daily_id = DB::table('faq_daily_reads')->value('id');
            $negative_rejected = false;
            try { DB::table('faq_daily_reads')->where('id', $daily_id)->update(['read_count' => -1]); }
            catch (\Illuminate\Database\QueryException $exception) { $negative_rejected = true; }
            $this->assertTrue($negative_rejected, 'The intended engine must reject negative counts.');
            $deletion_rejected = false;
            try { DB::table('faq_daily_reads')->where('id', $daily_id)->delete(); }
            catch (\Illuminate\Database\QueryException $exception) { $deletion_rejected = true; }
            $this->assertTrue($deletion_rejected, 'A referenced aggregate must not be deleted.');
            $this->assertSame(19, (int) DB::table('faq_daily_reads')->sum('read_count'));
        } finally {
            foreach (array_reverse($applied_migrations) as $migration) { $migration->down(); }
            DB::disconnect('faq_read_test');
            DB::setDefaultConnection($original_connection);
        }
    }
    #[Test] public function Worker(): void
    {
        $this->assertNotFalse(getenv('FAQ_READ_WORKER_KEY'), 'Run the ConcurrentReceipts entry point, not Worker directly.');
        $this->ConfigureConnection();
        $barrier_path = getenv('FAQ_READ_WORKER_BARRIER');
        $ready_path = getenv('FAQ_READ_WORKER_READY');
        $this->assertIsString($barrier_path);
        $this->assertIsString($ready_path);
        $this->assertSame(dirname($barrier_path), dirname($ready_path));
        $this->assertSame(realpath(sys_get_temp_dir()), realpath(dirname($barrier_path)));
        $this->assertStringStartsWith('faq-read-barrier-', basename($barrier_path));
        $this->assertStringStartsWith(basename($barrier_path).'.ready-', basename($ready_path));
        file_put_contents($ready_path, 'ready');
        $deadline = microtime(true) + 25;
        while (!is_file($barrier_path)) {
            clearstatcache();
            $this->assertLessThan($deadline, microtime(true), 'Barrier timed out.');
            usleep(10000);
        }
        CarbonImmutable::setTestNow(CarbonImmutable::parse(getenv('FAQ_READ_WORKER_NOW')));
        try {
            $worker_mode = getenv('FAQ_READ_WORKER_MODE');
            $this->assertContains($worker_mode, ['read', 'slug']);
            if ($worker_mode === 'slug') {
                $category_id = DB::table('faqs')->where('id', (int) getenv('FAQ_READ_WORKER_ID'))->value('category_id');
                app(\App\Services\Faq\FaqSlugWriter::class)->Create(['category_id' => $category_id,
                    'question' => 'Concurrent slug question', 'question_en' => 'Concurrent slug question',
                    'answer' => 'Answer', 'is_published' => true]);
            } else {
                app(FaqReadRecorder::class)->Record((int) getenv('FAQ_READ_WORKER_ID'), getenv('FAQ_READ_WORKER_KEY'));
            }
            $this->addToAssertionCount(1);
        } finally { CarbonImmutable::setTestNow(); DB::disconnect('faq_read_test'); }
    }
}
```

The worker processes receive the dedicated `FAQ_READ_TEST_*` environment explicitly; no user database configuration is inherited as a fallback. On migration failure, report any partially created test-owned tables instead of broad deletion; the next run's empty-database assertion must refuse to reuse leftovers. Do not weaken guards to make a run pass.

Public file-session test procedure, to implement in `FaqReadSessionPersistenceTest` using the actual Laravel session store and kernel:

1. Allocate a unique test-owned directory inside `storage/framework/testing/faq-read-sessions-<random hex>`; create it with no existing contents. Set `session.driver=file`, `session.files` to that directory, cache to array, backend to `https://backend.example.test`, and test-only tracking keys. Use explicit fake responses and `Http::preventStrayRequests()` in each new application instance.
2. Start a real session, set a fixed test CSRF token, save it, and capture its ID. Obtain the encrypted session cookie through the real cookie middleware from a GET response; retain the same configured test APP_KEY across refreshed applications.
3. Refresh the application, restore the isolated configuration/fakes, send `/faq/11/read` with that cookie and CSRF token. Capture the outbound two-field payload. Repeat from a second fresh application with the same cookie, including a different locale; keys must be identical. Refresh again with a newly created/saved session and require a different key.
4. Expire/delete only that test-owned session file, repeat with a newly established session/CSRF and require a new key. A stale token without a valid session must fail CSRF, not mint an accepted read.
5. In `finally`, remove only known files in the verified test-owned directory, then the empty directory. Never change the user's session driver, rotate APP_KEY, or delete shared session files. Verify the test directory resolves beneath the worktree before cleanup.

Complete public persistence test (the separate CSRF test in Task 5 still owns enforcement):

```php
<?php
namespace Tests\Feature;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\FaqFixture;
use Tests\TestCase;
final class FaqReadSessionPersistenceTest extends TestCase
{
    private function ConfigureIsolatedSession(string $session_directory): void
    {
        config(['app.key' => 'base64:MDEyMzQ1Njc4OTAxMjM0NTY3ODkwMTIzNDU2Nzg5MDE=',
            'session.driver' => 'file', 'session.files' => $session_directory, 'cache.default' => 'array',
            'services.eimaths_backend.base_url' => 'https://backend.example.test',
            'faq-analytics.enabled' => true, 'faq-analytics.hash_key' => 'test-only-faq-hash-key-32-characters',
            'faq-analytics.integration_token' => 'test-only-faq-token']);
        Http::preventStrayRequests();
        Http::fake(['https://backend.example.test/api/faqs*' => Http::response([FaqFixture::Record()]),
            'https://backend.example.test/api/faq-reads' => Http::response('', 204)]);
    }
    private function EstablishSession(): array
    {
        $response = $this->get('/faq')->assertOk();
        $cookie_name = config('session.cookie');
        $session_cookie = collect($response->headers->getCookies())->first(
            fn ($cookie): bool => $cookie->getName() === $cookie_name
        );
        $this->assertNotNull($session_cookie);
        return [$cookie_name, $session_cookie->getValue(), app('session')->token(), app('session')->getId()];
    }
    private function SubmitFromFreshApplication(string $session_directory, array $session_details, string $locale): string
    {
        $this->refreshApplication();
        $this->ConfigureIsolatedSession($session_directory);
        [$cookie_name, $encrypted_cookie, $csrf_token, $saved_session_id] = $session_details;
        $session_store = app('session')->driver();
        $session_store->setId($saved_session_id);
        $session_store->start();
        $session_store->put('locale', $locale);
        $session_store->save();
        // Preserve the already encrypted response cookie and include it in JSON requests.
        $this->withUnencryptedCookies([$cookie_name => $encrypted_cookie])->withCredentials()
            ->withHeader('X-CSRF-TOKEN', $csrf_token)->postJson('/faq/11/read')->assertNoContent();
        $outbound = Http::recorded(fn ($request): bool => $request->method() === 'POST')->first();
        $this->assertNotNull($outbound);
        return $outbound[0]->data()['event_key'];
    }
    #[Test] public function SameSavedSessionAcrossFreshApplicationsKeepsOneKey(): void
    {
        $testing_root = storage_path('framework/testing');
        if (!is_dir($testing_root)) mkdir($testing_root, 0700, true);
        $session_directory = $testing_root.'/faq-read-sessions-'.bin2hex(random_bytes(8));
        $this->assertDirectoryDoesNotExist($session_directory);
        mkdir($session_directory, 0700);
        $resolved_root = realpath($testing_root);
        $this->assertStringStartsWith(realpath(base_path()).DIRECTORY_SEPARATOR, $resolved_root);
        $resolved_directory = realpath($session_directory);
        $this->assertStringStartsWith($resolved_root.DIRECTORY_SEPARATOR, $resolved_directory);
        try {
            $this->ConfigureIsolatedSession($session_directory);
            $first_session = $this->EstablishSession();
            $first_key = $this->SubmitFromFreshApplication($session_directory, $first_session, 'th');
            $second_key = $this->SubmitFromFreshApplication($session_directory, $first_session, 'en');
            $third_key = $this->SubmitFromFreshApplication($session_directory, $first_session, 'th');
            $this->assertSame($first_key, $second_key);
            $this->assertSame($first_key, $third_key);
            $this->refreshApplication();
            $this->ConfigureIsolatedSession($session_directory);
            $this->withUnencryptedCookies([$first_session[0] => '']);
            $new_session = $this->EstablishSession();
            $new_key = $this->SubmitFromFreshApplication($session_directory, $new_session, 'th');
            $this->assertNotSame($first_session[3], $new_session[3]);
            $this->assertNotSame($first_key, $new_key);
        } finally {
            foreach (glob($session_directory.'/*') ?: [] as $owned_file) {
                $resolved_file = realpath($owned_file);
                $this->assertIsString($resolved_file);
                $this->assertStringStartsWith($resolved_directory.DIRECTORY_SEPARATOR, $resolved_file);
                $this->assertFileExists($resolved_file);
                unlink($resolved_file);
            }
            rmdir($session_directory);
        }
    }
}
```

The fresh-application helper updates locale through the real file store without regenerating the saved session ID. `withCredentials()` is necessary because Laravel test JSON requests otherwise omit cookies. Add the expired-session/invalid-token case described above as its own test.

This intentionally uses the real persistence boundary instead of asserting that `withSession` arrays or localStorage simulate tabs. The current CLI/static browser fixture has no live HTTP session server; provisioning one is not silently included.

## Task 8: Verify the two-plan integration and prepare safe enablement

**Files:**

- Modify: This plan's execution record after checks run.
- Test: All new backend/public tests, related existing FAQ/localization/metadata tests, both full PHP suites and the public full browser suite.

- **Consumes:** All tasks in both approved plans and specification sections 10-12.
- **Produces:** A reviewed implementation, actual validation record, and explicit operational enablement prerequisites.
- **Checkpoint:** Code approval/execution does not authorize deploying, migrating a live database, sending tracking, changing notice/consent, creating service accounts, committing or pushing. Use already-granted authorizations where applicable; do not repeat them. Keep recording disabled while enablement prerequisites are unresolved.

**Actions — nonbehavioral verification artifact:**

- [ ] Run the focused commands, the two complete PHP suites and full public browser suite; inspect all failures/warnings/skips. Run `git diff --check` in both worktrees and review new files separately. Record exact commands, revision and result; no inferred build/concurrency claims.
- [ ] Verify a real UI opening reaches the adapter with the same key as a detail opening in the same persisted session, then a single durable receipt/count. Separately exercise new sessions, hidden content, publication changes, simultaneous requests and lost acknowledgement replay.
- [ ] Confirm raw identities/keys/secrets are absent from new tables where prohibited, UI, JSON responses and feature logs. No general infrastructure privacy claim is made. Maintain one bounded correlation ID across public forwarding and backend failure logs: generate it on the public server, send `X-Request-Id`, and on the authenticated backend accept only a valid UUID or replace it. Do not forward a visitor-provided header verbatim.
- [ ] Confirm integration-token rotation leaves the hash key unchanged; off flag suppresses browser rendering and rejects direct public writes; an upstream outage leaves answers/search available. Verify actual deployed session/cache drivers, shared state across instances, HTTPS destination, database indexes/constraints and configured budgets before enablement.
- [ ] Deployment sequence: additive backend tables/API/report; companion slug schema/new writer/backfill verification; provision dedicated secrets; deploy public UI/proxy with tracking off; perform isolated integration verification; complete owner notice/retention review; enable only in the approved environment. No deploy commands are authorized by this plan.
- [ ] Rollback: turn tracking off first, restore application behavior, retain slug/analytics columns and tables. Preserve existing recorded history; never run destructive `down()` against collected live data. Keep receipts while active sessions and the approved retention policy require them.
- [ ] Perform a final code review and apply `verification-before-completion` plus `you-should-know`. Report unresolved intended-engine/live-session/operational checks plainly; do not label local tests production readiness.

## Requirement-to-task trace

| Approved requirement | Owner and proof |
| --- | --- |
| First FAQ opening per website session, all reading surfaces/languages/dates | Tasks 1, 5-7: key vector, real session reuse, UI event and DB uniqueness |
| Durable deduplication and atomic count | Tasks 1, 7: rollback fault, simultaneous workers, replay, database constraints |
| Date ownership at backend acceptance in Bangkok | Tasks 1, 3, 7: midnight and date-range fixtures |
| Historical deleted/unpublished reads and minimal retention | Tasks 1, 3: no live cascade, current/snapshot labels, history survives delete |
| Default 30d, 7d/custom, zero rows, sort, pagination | Tasks 3-4: range/report and route/UI tests |
| Existing FAQ staff access | Task 4: permission, disabled account and inactive-branch tests with middleware enabled |
| Dedicated authenticated integration and abuse controls | Tasks 2, 5: token, payload, CSRF, quota and redirect/TLS tests |
| Recording fails softly; report failures are explicit | Tasks 2, 4-6: fake connection/DB failures, visible answers, report 503 |
| No raw session/visitor secrets in feature storage/logs/UI | Tasks 1, 2, 5, 8: schema/payload/log inspection and tests |
| Controlled enablement/rollback and no historical reconstruction | Tasks 5, 8: disabled defaults, additive rollout, owner review checkpoint |

## Execution record and approval

Planning verified existing route/middleware ordering, permission/branch boundaries, schema/model fields, HTTP/session APIs, report query primitives, CLI versions/help and related test conventions. The independent HMAC vector was computed locally. No application test, concurrent worker, browser scenario, migration, build or live request has run during plan preparation.

The disposable MySQL-compatible test environment and actual deployed session/cache/notice/retention arrangements remain unverified. Their checks are explicitly scoped execution/enablement checkpoints, not missing product decisions. No new consent model or production infrastructure is assumed.

Await approval of this completed plan together with `0003-faq-search-seo.md` and the execution method. Recommended: Native execution by the current agent followed by a final independent review. Alternative: per-task independent implementer/reviewer cycles, with more agent context and coordination. Carry the user's selection into `git-worktrees` and `executing-plans`; do not start worktree preparation before approval/method selection.

Planning review on 2026-10-20: both plans were checked against the approved specification, current repository interfaces, naming/access boundaries and their requirement-to-task matrices. All 70 declared file references resolved either to inspected existing files or explicitly planned new files; no create target already existed. The final syntax-only check parsed 46 PHP blocks with complete-input `token_get_all(..., TOKEN_PARSE)` and checked four JavaScript blocks with `node --input-type=module --check`, with zero failures. Markdown fences/tables/task contracts, English document content, LF encoding and final newlines were checked. These checks do not execute Blade, application behavior, HTTP, database transactions or browser scenarios.

An initial Windows PHP CLI lint attempt through standard input reported a parse error for the long concurrency example. The final full-input PHP parser check succeeded and verified the consumed byte count; no application code was changed in response. Runtime behavior remains unverified until execution of the approved tests.
