# FAQ Search and SEO Implementation Plan

> **REQUIRED EXECUTION SKILL:** After plan approval and execution-method selection, use `git-worktrees`, then `executing-plans` with the selected method for task-by-task execution.

- **Goal:** Visitors can search displayed FAQ questions/answers and open stable, server-rendered individual FAQ URLs with correct metadata and sitemap entries.
- **Architecture:** Backend owns publication, localization, stable slugs, and content APIs. The public Laravel application validates and presents that content; browser filtering operates on rendered text. Reading analytics belongs to the companion plan.
- **Tech Stack:** Backend Laravel 10.50.2/PHPUnit 10.5.63; public Laravel 12.58.0/PHPUnit 11.5.55; PHP 8.2.12 available locally; Blade, existing Tailwind styling, plain browser JavaScript, Playwright 1.63.0.
- **Source:** `docs/specs/0003-faq-search-seo-analytics-design.md`, approved in conversation on 2026-10-20; the two-plan split was subsequently approved.
- **Companion:** `docs/plans/0004-faq-read-analytics.md`.
- **Status:** Proposed implementation plan. No implementation, migrations, tests, deployment, commit, or execution-method selection is authorized by the existence of this file.

## Global constraints

Copied requirements from the approved specification:

> Preserve existing content, categories, publication controls, Thai/English fallback, editorial ordering, permissions, and unrelated changes. Reuse both applications and existing UI styles. No external analytics account or search service is required.

> Content GETs, sitemap generation, metadata rendering, and crawlers merely retrieving HTML never write analytics.

> Tests must not use live backend services, migrate user databases, or send real tracking.

Implement only this plan and the approved companion. Do not create a new site, replace the frameworks, add an external search service, change the privacy notice, publish, or run production migrations. Do not introduce a manual slug editor, redirects for edited questions, charts, exports, or a glossary/ADR. Keep all documentation in English. Use PascalCase new methods/functions, snake_case local identifiers and fields, UPPER_CASE constants, and kebab-case ordinary files. Preserve framework/PSR-4 filenames, framework methods, existing public names, and protocol-required keys such as JSON-LD `@type`.

All behavior changes require the `test-driven-development` skill and its `writing-good-tests.md` reference. Execute one RED/GREEN cycle at a time. Resolve setup errors before accepting a failure as RED. The code blocks below are implementation instructions, not executed code or proof of correctness. Copy production changes only after the owning behavioral failure is observed. Follow each focused pass with its stated regression checks; record actual output rather than the expected output in this plan.

## Verified baseline and commands

`B/` means the backend repository `eiMaths/`; `P/` means the sibling public repository `eimaths_th_new/`. These are path prefixes for this document, not new directories. Resolve them to the approved isolated checkouts during execution.

- Reviewed backend HEAD `4fb62623`, public HEAD `2851b91`; backend has only the approved specification untracked before planning, public is clean.
- Existing writer: `B/app/Http/Controllers/FaqController.php::Store`. Inspected application/routes contain no other FAQ creation call; historical import migrations use query-builder writes and are preserved.
- Existing APIs, tests, views, middleware, sanitizer, session configuration, canonical layout, sitemap and browser fixtures were inspected. `B/phpunit.xml` forces an in-memory SQLite database. `P/phpunit.xml` selects in-memory SQLite and array sessions. Neither is evidence of production concurrency.
- PHP CLI includes `mbstring`, `intl`, `dom`, `pdo_mysql` and `pdo_sqlite`. Both `vendor/bin/phpunit` paths and the public Playwright CLI exist. CLI version/help commands ran successfully. Application tests have not run during planning.
- Backend `.github/workflows/tests.yml` invokes PHPUnit. No public `.github` directory was present in this checkout. Both repositories have no cached `bootstrap/cache/config.php` at inspection time.

Run commands from their named repository. Use the installed binaries; do not download tooling:

```powershell
# B: focused tests and then the required full PHP suite
php vendor/bin/phpunit --do-not-cache-result --filter Faq
php vendor/bin/phpunit --do-not-cache-result
# P: focused PHP tests, focused browser tests, then full suites
php vendor/bin/phpunit --do-not-cache-result --filter 'Faq|CanonicalMetadata|ContactLinksFromContent'
node node_modules/@playwright/test/cli.js test tests/browser/faq-search.spec.mjs --reporter=line
php vendor/bin/phpunit --do-not-cache-result
node node_modules/@playwright/test/cli.js test --reporter=line
```

The new test paths below do not exist yet; create them in their owning RED step. The command syntax and test discovery are verified, not future test outcomes. Before executing suites, recheck that config caching cannot override the isolated test database. Retain `Http::preventStrayRequests()` and explicit fakes for new HTTP tests. Do not run `composer run setup`: it includes migrations against configured databases. Existing assets are loaded directly from `public/js`; no build change is needed for these scripts. A build may only be reported if actually run.

## Cross-plan contract and ordering

Execute this plan before the companion's browser-integration task. Backend analytics/storage/report work can be implemented and tested independently against existing FAQ IDs. This plan has no dependency on analytics tables, credentials or endpoints.

The public list produces `.faq-item[data-faq-id]`, `.faq-toggle`, and `.faq-panel` with the existing `faq-{id}` answer IDs. The new detail root is `[data-faq-detail][data-faq-id]`. A positive integer backend ID is the only analytics identity. Legacy entries without valid IDs or slugs remain readable; omit detail links and analytics identity for those entries.

The browser list module dispatches `FAQ_OPENED` on `document` **only** after an explicit closed-to-open answer transition, with `detail: { faq_id: <positive integer> }`. Search, category reveal, close, render and GET do not dispatch it. The companion listens without replacing search behavior. The custom event's uppercase name and snake_case payload follow the requested naming rules; DOM attributes remain kebab-case.

Backend detail contract: `GET /api/faqs/{slug}?locale=th|en`, one object with the existing list-element fields plus `slug`; 404 for unknown or nonpublic question/category. The existing list stays an array with additive `slug`. Public detail: `GET /faq/{slug}`, route name `faq.detail`. Thai is queryless; English uses `?lang=en`; no valid explicit language on detail means Thai even if session language is English. Existing `/faq` continues honoring session language.

## Task 1: Persist stable slugs and backfill existing questions safely

**Files:**

- Create: `B/database/migrations/2026_10_20_000001_add_slug_to_faqs.php`.
- Create: `B/app/Services/Faq/FaqSlugWriter.php`.
- Create: `B/app/Console/Commands/BackfillFaqSlugs.php`.
- Modify: `B/app/Http/Controllers/FaqController.php::Store`.
- Test: `B/tests/Feature/FaqSlugTest.php`.

- **Consumes:** Existing `faqs`, `Faq::create`, `FaqController::Store`, and command auto-discovery in `app/Console/Kernel.php::commands`.
- **Produces:** Nullable unique `faqs.slug`; `FaqSlugWriter::Create(array $attributes): Faq`; `Assign(int $faq_id): string`; `faq:backfill-slugs`.
- **Checkpoint:** None for isolated implementation after plan approval. Running the command on a user/staging/production database requires the separately approved rollout context. No automatic production backfill.

**Actions — production behavior changes:**

**REQUIRED IMPLEMENTATION SKILL:** `test-driven-development`.

- [ ] First write `SchemaAddsNullableUniqueSlugs` below; run `php vendor/bin/phpunit --do-not-cache-result --filter FaqSlugTest` in B. RED is the false column-existence assertion. Add only the migration and rerun.
- [ ] Add the writer/backfill tests one behavior at a time. If a class/command is missing, add only the empty loadable boundary before rerunning; RED must then be the missing slug/backfill effect. Add the corresponding writer code and run the same command, expecting exit 0.
- [ ] Replace only `Faq::create($validated_faq);` in `Store` with `app(\App\Services\Faq\FaqSlugWriter::class)->Create($validated_faq);`. Keep validation, permission, redirect, and existing fields unchanged. Add the management-route test using the actor fixture in Task 1's test instructions below.
- [ ] Rerun `FaqSlugTest`, `FaqApiTest`, `FaqLocalizationTest`, and `ContentManagementLocalizedPageTest`. Verify creation failure rolls the new record back, text/category/publication edits preserve the slug, and backfill preserves every non-slug column. Refactor only while these checks remain green.

Migration content:

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void
    {
        Schema::table('faqs', function (Blueprint $table): void {
            $table->string('slug', 255)->nullable()->unique('faqs_slug_unique');
        });
    }
    public function down(): void
    {
        Schema::table('faqs', function (Blueprint $table): void {
            $table->dropUnique('faqs_slug_unique');
            $table->dropColumn('slug');
        });
    }
};
```

`down()` exists for disposable migration tests only. The approved operational rollback retains the column.

`B/app/Services/Faq/FaqSlugWriter.php`:

```php
<?php
namespace App\Services\Faq;
use App\Models\Faq;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
final class FaqSlugWriter
{
    private const BASE_LENGTH = 120;
    public function Create(array $attributes): Faq
    {
        return DB::transaction(function () use ($attributes): Faq {
            $faq = Faq::create($attributes);
            $this->Assign((int) $faq->id);
            return $faq->refresh();
        }, 3);
    }
    public function Assign(int $faq_id): string
    {
        return DB::transaction(function () use ($faq_id): string {
            $faq = Faq::query()->lockForUpdate()->findOrFail($faq_id);
            if (is_string($faq->slug) && $faq->slug !== '') {
                return $faq->slug;
            }
            $source = filled($faq->question_en) ? $faq->question_en : $faq->question;
            $base = mb_strtolower(strip_tags($source), 'UTF-8');
            $base = preg_replace('/[^\p{L}\p{M}\p{N}]+/u', '-', $base) ?? '';
            $base = trim(mb_substr(trim($base, '-'), 0, self::BASE_LENGTH, 'UTF-8'), '-');
            $base = $base === '' ? 'faq-'.$faq_id : $base;
            for ($collision_number = 0; ; $collision_number++) {
                $candidate = $base.($collision_number === 0 ? '' : '-'.$faq_id);
                if ($collision_number > 1) {
                    $candidate .= '-'.($collision_number - 1);
                }
                try {
                    // A savepoint isolates a collision; only this unique column is changed.
                    DB::transaction(function () use ($faq_id, $candidate): void {
                        DB::table('faqs')->where('id', $faq_id)->update(['slug' => $candidate]);
                    });
                    return $candidate;
                } catch (UniqueConstraintViolationException $exception) {
                    // A unique failure from this slug-only update selects the next candidate.
                    // Other query failures propagate and roll back creation/backfill for this row.
                }
            }
        }, 3);
    }
}
```

`B/app/Console/Commands/BackfillFaqSlugs.php`:

```php
<?php
namespace App\Console\Commands;
use App\Services\Faq\FaqSlugWriter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
final class BackfillFaqSlugs extends Command
{
    protected $signature = 'faq:backfill-slugs';
    protected $description = 'Assign missing FAQ slugs without changing content or timestamps';
    public function handle(FaqSlugWriter $slug_writer): int
    {
        $assigned_count = 0;
        foreach (DB::table('faqs')->where(function ($query): void {
            $query->whereNull('slug')->orWhere('slug', '');
        })->orderBy('id')->lazyById(200, 'id') as $faq_row) {
            $slug_writer->Assign((int) $faq_row->id);
            $assigned_count++;
        }
        $this->info('Assigned FAQ slugs: '.$assigned_count);
        return self::SUCCESS;
    }
}
```

`B/tests/Feature/FaqSlugTest.php` (add methods in separate RED/GREEN cycles):

```php
<?php
namespace Tests\Feature;
use App\Models\Faq;
use App\Models\FaqCategory;
use App\Services\Faq\FaqSlugWriter;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
final class FaqSlugTest extends TestCase
{
    use RefreshDatabase;
    private function Attributes(string $question = 'Original question'): array
    {
        return ['category_id' => FaqCategory::query()->firstOrFail()->id,
            'question' => $question, 'question_en' => 'How does CPA work?',
            'answer' => 'Answer', 'position' => 1, 'is_published' => true];
    }
    #[Test] public function SchemaAddsNullableUniqueSlugs(): void
    {
        $this->assertTrue(Schema::hasColumn('faqs', 'slug'));
        $first = Faq::create($this->Attributes());
        $second = Faq::create($this->Attributes());
        DB::table('faqs')->where('id', $first->id)->update(['slug' => 'same']);
        $this->expectException(UniqueConstraintViolationException::class);
        DB::table('faqs')->where('id', $second->id)->update(['slug' => 'same']);
    }
    #[Test] public function CreationUsesEnglishAndResolvesCollisions(): void
    {
        $writer = app(FaqSlugWriter::class);
        $first = $writer->Create($this->Attributes());
        $second = $writer->Create($this->Attributes());
        $this->assertSame('how-does-cpa-work', $first->slug);
        $this->assertSame('how-does-cpa-work-'.$second->id, $second->slug);
        $first->update(['question_en' => 'Changed', 'is_published' => false]);
        $this->assertSame('how-does-cpa-work', $writer->Assign($first->id));
    }
    #[Test] public function EmptySourceFallsBackToIdAndBackfillPreservesRows(): void
    {
        $faq = Faq::create(array_replace($this->Attributes('?!'), ['question_en' => null]));
        $before = (array) DB::table('faqs')->find($faq->id);
        $this->artisan('faq:backfill-slugs')->assertExitCode(0);
        $after = (array) DB::table('faqs')->find($faq->id);
        $this->assertSame('faq-'.$faq->id, $after['slug']);
        unset($before['slug'], $after['slug']);
        $this->assertSame($before, $after);
        $slugs = DB::table('faqs')->orderBy('id')->pluck('slug', 'id')->all();
        $this->artisan('faq:backfill-slugs')->assertExitCode(0);
        $this->assertSame($slugs, DB::table('faqs')->orderBy('id')->pluck('slug', 'id')->all());
    }
}
```

Before finishing this task add separately failing cases for Unicode letters/marks, a 121-character source (base is 120), a preoccupied `base-{id}` candidate (uses `base-{id}-1`), and concurrent identical English titles. Use independent literal expected strings. For the existing management route, copy the verified active-HQ/Admin/`faq-create` setup from `tests/Feature/Localization/ContentManagementLocalizedPageTest.php::setUp`, post the existing required fields to `/faqs`, and assert the persisted slug. Keep branch middleware enabled. Simulate a slug-write database failure at the database boundary and assert the surrounding creation transaction leaves no added FAQ. These are required acceptance cases, not permission to edit unrelated tests.

## Task 2: Expose one public FAQ with the existing localized contract

**Files:**

- Create: `B/app/Services/Faq/FaqPublicContent.php`.
- Modify: `B/app/Http/Controllers/FaqController.php::ApiIndex`; add `ApiShow`.
- Modify: `B/routes/api.php` immediately after existing FAQ list route.
- Test: `B/tests/Feature/FaqDetailApiTest.php`; retain `FaqApiTest` and `FaqLocalizationTest`.

- **Consumes:** Task 1 slugs; existing `LocalizedQuestion`, `LocalizedAnswer`, category localization and publication flags.
- **Produces:** Additive list `slug` and detail object at `/api/faqs/{slug}`.
- **Checkpoint:** None.

**Actions — production behavior changes:**

**REQUIRED IMPLEMENTATION SKILL:** `test-driven-development`.

- [ ] Write the route test below. B command: `php vendor/bin/phpunit --do-not-cache-result --filter 'FaqDetailApiTest|FaqApiTest|FaqLocalizationTest'`. RED: detail returns 404 for the published fixture rather than 200.
- [ ] Add the service and controller/route changes. GREEN: localized list and detail agree, with no private fields or analytics payloads.
- [ ] Add question-hidden, category-hidden, unknown-slug and invalid-locale cases one at a time; expect 404, 404, 404 and Thai fallback respectively. Run the same command after every case/change.
- [ ] Refactor the shared serializer only after these comparisons pass. Do not remove fields or change list ordering.

`B/app/Services/Faq/FaqPublicContent.php`:

```php
<?php
namespace App\Services\Faq;
use App\Models\Faq;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
final class FaqPublicContent
{
    public function Query(): Builder
    {
        return Faq::query()->select('faqs.*')->with('category')
            ->join('faq_categories', 'faq_categories.id', '=', 'faqs.category_id')
            ->where('faqs.is_published', true)->where('faq_categories.is_published', true)
            ->orderBy('faq_categories.position')->orderBy('faqs.position')->orderByDesc('faqs.created_at');
    }
    public function Serialize(Faq $faq, string $locale): array
    {
        $answer = $faq->LocalizedAnswer($locale);
        return ['id' => $faq->id, 'slug' => $faq->slug,
            'question' => $faq->LocalizedQuestion($locale), 'answer' => $answer,
            'answer_html' => Str::markdown($answer), 'position' => $faq->position,
            'is_published' => $faq->is_published, 'published_at' => $faq->published_at,
            'created_at' => $faq->created_at, 'updated_at' => $faq->updated_at,
            'category' => ['id' => $faq->category->id, 'slug' => $faq->category->slug,
                'name' => $faq->category->LocalizedName($locale),
                'description' => $faq->category->LocalizedDescription($locale),
                'position' => $faq->category->position]];
    }
}
```

Replace `ApiIndex` and add `ApiShow` in the existing controller (the fully qualified names avoid changing unrelated imports):

```php
public function ApiIndex(Request $request)
{
    $locale = in_array($request->query('locale'), ['th', 'en'], true) ? $request->query('locale') : 'th';
    $content = app(\App\Services\Faq\FaqPublicContent::class);
    return response()->json($content->Query()->get()->map(
        fn (Faq $faq): array => $content->Serialize($faq, $locale)
    ));
}
public function ApiShow(Request $request, string $slug)
{
    $locale = in_array($request->query('locale'), ['th', 'en'], true) ? $request->query('locale') : 'th';
    $content = app(\App\Services\Faq\FaqPublicContent::class);
    $faq = $content->Query()->where('faqs.slug', $slug)->firstOrFail();
    return response()->json($content->Serialize($faq, $locale));
}
```

Add `Route::get('/faqs/{slug}', [FaqController::class, 'ApiShow']);` in `routes/api.php`. Encoded path separators must not be accepted as a slug; do not add a catch-all `.*` route constraint.

`B/tests/Feature/FaqDetailApiTest.php`:

```php
<?php
namespace Tests\Feature;
use App\Models\Faq;
use App\Models\FaqCategory;
use App\Services\Faq\FaqSlugWriter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
final class FaqDetailApiTest extends TestCase
{
    use RefreshDatabase;
    #[Test] public function DetailMatchesPublishedListAndRejectsHiddenContent(): void
    {
        $category = FaqCategory::create(['name' => 'Primary category', 'name_en' => 'General',
            'slug' => 'detail-fixture', 'is_published' => true]);
        $faq = app(FaqSlugWriter::class)->Create(['category_id' => $category->id,
            'question' => 'Primary question', 'question_en' => 'English question',
            'answer' => 'Primary answer', 'answer_en' => '**English answer**', 'is_published' => true]);
        $list = $this->getJson('/api/faqs?locale=en')->assertOk()->json();
        $listed = collect($list)->firstWhere('id', $faq->id);
        $detail = $this->getJson('/api/faqs/'.$faq->slug.'?locale=en')->assertOk()
            ->assertJsonPath('question', 'English question')->json();
        $this->assertSame($listed, $detail);
        $this->assertArrayNotHasKey('source_reference', $detail);
        $category->update(['is_published' => false]);
        $this->getJson('/api/faqs/'.$faq->slug)->assertNotFound();
        $category->update(['is_published' => true]);
        $faq->update(['is_published' => false]);
        $this->getJson('/api/faqs/'.$faq->slug)->assertNotFound();
        $this->getJson('/api/faqs/unknown-fixture')->assertNotFound();
    }
}
```

## Task 3: Validate public content and share safe answer presentation

**Files:**

- Create: `P/app/Services/Faq/FaqContentClient.php`.
- Create: `P/resources/views/faq/answer.blade.php`.
- Create: `P/tests/Support/FaqFixture.php`.
- Modify: `P/app/Http/Controllers/FaqController.php::index` (retain `GroupFaqsByCategory`).
- Modify: `P/resources/views/faq.blade.php` answer rendering and JSON-LD preparation.
- Test: `P/tests/Feature/FaqContentBoundaryTest.php`; existing `FaqPageTest`, `ContactLinksFromContentTest`.

- **Consumes:** Backend list contract; `BlogHtmlSanitizer::Sanitize`, `ContactLinks::Html` and `ContactLinks::Text`.
- **Produces:** Validated FAQ arrays with `safe_answer_html`, `plain_answer`, `has_rich_answer`, and optional `detail_url`; content failures produce 503.
- **Checkpoint:** None. Do not change the sanitizer or blog behavior.

**Actions — production behavior changes:**

**REQUIRED IMPLEMENTATION SKILL:** `test-driven-development`.

- [ ] Add the content-failure test below and run P `php vendor/bin/phpunit --do-not-cache-result --filter 'FaqContentBoundaryTest|FaqPageTest|ContactLinksFromContentTest'`. RED: the old controller returns an empty 200 for upstream failure.
- [ ] Add the client and replace `index` with the code below. GREEN: outages/malformed collections are 503; a valid empty array remains 200; legacy FAQ rendering stays available.
- [ ] Add the unsafe-answer case, first prove executable DOM content remains in the old FAQ answer, then introduce the shared partial and sanitized schema mapping. GREEN: no injected script/event URL survives; plain text stays escaped; legitimate contact links work.
- [ ] Add explicit timeout, malformed row, numeric query parameter, null category, missing ID/slug, and invalid slug cases using separate fakes. Repeat the focused command. Refactor without changing behavior.

`P/tests/Support/FaqFixture.php`:

```php
<?php
namespace Tests\Support;
final class FaqFixture
{
    public static function Record(array $overrides = []): array
    {
        return array_replace_recursive([
            'id' => 11, 'slug' => 'how-does-cpa-work', 'question' => 'How does CPA work?',
            'answer' => 'Learn with blocks.', 'answer_html' => '<p>Learn with <strong>blocks</strong>.</p>',
            'position' => 1, 'is_published' => true, 'published_at' => '2026-09-01T00:00:00Z',
            'created_at' => '2026-09-01T00:00:00Z', 'updated_at' => '2026-09-02T00:00:00Z',
            'category' => ['id' => 5, 'slug' => 'teaching-methods', 'name' => 'Teaching Methods',
                'description' => null, 'position' => 1],
        ], $overrides);
    }
}
```

`P/app/Services/Faq/FaqContentClient.php`:

```php
<?php
namespace App\Services\Faq;
use App\Support\BlogHtmlSanitizer;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
final class FaqContentClient
{
    public function Index(string $locale): array
    {
        $records = $this->Fetch('/api/faqs', $locale);
        if (!is_array($records) || !array_is_list($records)) {
            $this->Unavailable('faq.list.shape');
        }
        return array_map(fn ($record): array => $this->Prepare($record, $locale), $records);
    }
    public function Show(string $slug, string $locale): array
    {
        $record = $this->Prepare($this->Fetch('/api/faqs/'.rawurlencode($slug), $locale, true), $locale);
        if ($record['id'] === null || $record['slug'] !== $slug || ($record['is_published'] ?? null) !== true) {
            $this->Unavailable('faq.detail.shape');
        }
        return $record;
    }
    private function Fetch(string $path, string $locale, bool $is_detail = false): mixed
    {
        $base_url = rtrim((string) config('services.eimaths_backend.base_url'), '/');
        $base_parts = parse_url($base_url);
        if (filter_var($base_url, FILTER_VALIDATE_URL) === false || !is_array($base_parts)
            || !in_array($base_parts['scheme'] ?? '', ['http', 'https'], true)
            || isset($base_parts['user']) || isset($base_parts['pass'])
            || isset($base_parts['query']) || isset($base_parts['fragment'])) {
            $this->Unavailable('faq.content.configuration');
        }
        try {
            $response = Http::connectTimeout(2)->timeout(10)->withOptions(['allow_redirects' => false])
                ->get($base_url.$path, ['locale' => $locale]);
        } catch (ConnectionException $exception) {
            $this->Unavailable('faq.content.connection');
        }
        if ($is_detail && $response->status() === 404) {
            abort(404);
        }
        if (!$response->successful()) {
            $this->Unavailable('faq.content.response');
        }
        return $response->json();
    }
    private function Prepare(mixed $record, string $locale): array
    {
        if (!is_array($record) || !isset($record['question']) || !is_string($record['question'])
            || trim($record['question']) === ''
            || (isset($record['answer']) && !is_string($record['answer']))
            || (isset($record['answer_html']) && !is_string($record['answer_html']))
            || (!isset($record['answer']) && !isset($record['answer_html']))
            || (isset($record['category']) && !is_array($record['category']))
            || (isset($record['is_published']) && $record['is_published'] !== true)) {
            $this->Unavailable('faq.content.shape');
        }
        foreach (['position'] as $integer_field) {
            if (isset($record[$integer_field]) && !is_int($record[$integer_field])) {
                $this->Unavailable('faq.content.shape');
            }
        }
        foreach (['created_at', 'updated_at', 'published_at'] as $date_field) {
            if (isset($record[$date_field]) && !is_string($record[$date_field])) {
                $this->Unavailable('faq.content.shape');
            }
        }
        if (($record['category'] ?? null) === []) $record['category'] = null;
        if (isset($record['category'])) {
            if ((isset($record['category']['id']) && !is_int($record['category']['id']) && !is_string($record['category']['id']))
                || (isset($record['category']['position']) && !is_int($record['category']['position']))) {
                $this->Unavailable('faq.category.shape');
            }
            if (!is_string($record['category']['name'] ?? null)
                || !is_string($record['category']['slug'] ?? null)
                || (isset($record['category']['description']) && !is_string($record['category']['description']))) {
                $this->Unavailable('faq.category.shape');
            }
        }
        $record['id'] = is_int($record['id'] ?? null) && $record['id'] > 0 ? $record['id'] : null;
        $slug = $record['slug'] ?? null;
        $record['slug'] = is_string($slug) && mb_strlen($slug) <= 255
            && preg_match('/^[\p{L}\p{M}\p{N}]+(?:-[\p{L}\p{M}\p{N}]+)*$/u', $slug) ? $slug : null;
        $record['has_rich_answer'] = isset($record['answer_html']) && $record['answer_html'] !== '';
        $record['safe_answer_html'] = $record['has_rich_answer']
            ? BlogHtmlSanitizer::Sanitize($record['answer_html']) : '';
        $plain_source = $record['has_rich_answer']
            ? preg_replace('/<\/?(?:p|div|li|h[1-6]|br)\b[^>]*>/i', ' ', $record['safe_answer_html'])
            : ($record['answer'] ?? '');
        $record['plain_answer'] = trim(preg_replace('/\s+/u', ' ', html_entity_decode(
            $record['has_rich_answer'] ? strip_tags($plain_source) : $plain_source,
            ENT_QUOTES | ENT_HTML5, 'UTF-8'
        )) ?? '');
        $record['detail_url'] = $record['id'] !== null && $record['slug'] !== null
            ? '/faq/'.rawurlencode($record['slug']).($locale === 'en' ? '?lang=en' : '') : null;
        return $record;
    }
    private function Unavailable(string $operation): never
    {
        Log::warning('faq.content.unavailable', ['operation' => $operation, 'error_code' => 'FAQ_CONTENT_UNAVAILABLE']);
        abort(503, 'FAQ information is temporarily unavailable. Please try again.');
    }
}
```

Once Task 4 creates language files, change the safe 503 message to `__('faq.unavailable')`. Do not log exception objects, response bodies, request parameters, or credentials. Validate a nonempty configured base URL before calling HTTP and return the same safe 503 if missing; do not resolve an empty URL against a default host.

Replace public `index` only:

```php
public function index()
{
    $content = app(\App\Services\Faq\FaqContentClient::class)->Index(app()->getLocale());
    $faq_categories = $this->GroupFaqsByCategory(collect($content));
    return view('faq', compact('faq_categories'));
}
```

Remove the now-unused `Http` and `Throwable` imports from this controller. Preserve `GroupFaqsByCategory` including legacy General-category handling and ordering. In its filter only, replace `! empty($faq['question'])` with `isset($faq['question']) && is_string($faq['question']) && trim($faq['question']) !== ''` so the valid question string `0` is not silently dropped; add that regression case.

`P/resources/views/faq/answer.blade.php`:

```blade
@if($faq['has_rich_answer'])
    <div class="faq-answer leading-relaxed">{!! \App\Support\ContactLinks::Html($faq['safe_answer_html']) !!}</div>
@else
    <div class="faq-answer"><p class="whitespace-pre-line leading-relaxed">{!! \App\Support\ContactLinks::Text((string) ($faq['answer'] ?? '')) !!}</p></div>
@endif
```

Replace the old answer conditional in `faq.blade.php` with `@include('faq.answer', ['faq' => $faq])`. Replace only the schema entity preparation with:

```php
$faq_schema_entities = collect($faq_categories)->flatMap(fn (array $category): array => $category['faqs'])
    ->filter(fn (array $faq): bool => $faq['plain_answer'] !== '')
    ->map(fn (array $faq): array => ['@type' => 'Question', 'name' => $faq['question'],
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['plain_answer']]])->values()->all();
```

Set the existing JSON encoding flags to `JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR`. This prevents a `</script>` sequence from closing the JSON-LD block; do not promise search rich results.

`P/tests/Feature/FaqContentBoundaryTest.php`:

```php
<?php
namespace Tests\Feature;
use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\FaqFixture;
use Tests\TestCase;
final class FaqContentBoundaryTest extends TestCase
{
    #[Test] public function FailureIsNotAnEmptySuccessfulPage(): void
    {
        config(['services.eimaths_backend.base_url' => 'https://backend.example.test']);
        Http::preventStrayRequests();
        Http::fake(['https://backend.example.test/api/faqs*' => Http::response([], 503)]);
        $this->get('/faq')->assertStatus(503);
        Http::fake(['https://backend.example.test/api/faqs*' => Http::response(['unexpected' => true])]);
        $this->get('/faq')->assertStatus(503);
        Http::fake(['https://backend.example.test/api/faqs*' => Http::response([])]);
        $this->get('/faq')->assertOk();
    }
    #[Test] public function UnsafeMarkupCannotEscapeTheAnswerOrJsonLd(): void
    {
        config(['services.eimaths_backend.base_url' => 'https://backend.example.test']);
        Http::preventStrayRequests();
        Http::fake(['https://backend.example.test/api/faqs*' => Http::response([FaqFixture::Record([
            'answer_html' => '<p>Call 061-620-8666</p><img src="/missing" onerror="alert(1)"><script>alert(2)</script><a href="javascript:alert(3)">Unsafe</a>',
            'question' => 'Question </script><script>alert(4)</script>',
        ])])]);
        $html = $this->get('/faq')->assertOk()->getContent();
        $document = new DOMDocument();
        @$document->loadHTML('<?xml encoding="UTF-8">'.$html);
        $xpath = new DOMXPath($document);
        $this->assertSame(0, $xpath->query('//*[@id="faq-11"]//script | //*[@id="faq-11"]//*[@onerror]')->length);
        $this->assertSame(0, $xpath->query('//*[@id="faq-11"]//a[starts-with(@href,"javascript:")]')->length);
        $this->assertSame(1, $xpath->query('//*[@id="faq-11"]//a[@href="tel:+66616208666"]')->length);
        foreach ($xpath->query('//script[@type="application/ld+json"]') as $script) {
            $this->assertIsArray(json_decode($script->textContent, true, 512, JSON_THROW_ON_ERROR));
        }
    }
}
```

## Task 4: Render individual pages with stable language and metadata

**Files:**

- Modify: `P/routes/web.php` beside `faq` route; `P/app/Http/Controllers/FaqController.php` add `Show`.
- Modify: `P/resources/views/layouts/app.blade.php` at `$seo_page_url` only.
- Create: `P/resources/views/faq/detail.blade.php`, `P/public/css/faq.css`, `P/lang/en/faq.php`, `P/lang/th/faq.php`.
- Modify: `P/resources/views/faq.blade.php` question/link markup.
- Test: `P/tests/Feature/FaqDetailPageTest.php`.

- **Consumes:** Task 3 content client/answer partial; existing configured-origin metadata layout and language-switch route.
- **Produces:** Server-rendered answer, encoded stable links, locale-specific title/description/canonical/OG URL, stable queryless Thai content.
- **Checkpoint:** None. Do not modify global `SetLocale` or unrelated page metadata.

**Actions — production behavior changes:**

**REQUIRED IMPLEMENTATION SKILL:** `test-driven-development`.

- [ ] Write the route test below. Run P `php vendor/bin/phpunit --do-not-cache-result --filter 'FaqDetailPageTest|FaqPageTest|CanonicalMetadataTest|ContactLinksFromContentTest'`. RED: detail route is 404.
- [ ] Add route, controller, partial, language files and narrow layout override. GREEN: answer is in the initial response and canonical remains on the configured origin.
- [ ] Separately test a stored English session with queryless detail (Thai), explicit English, invalid/array `lang` (Thai), English fallback content, `?utm_source=x` exclusion, a Unicode slug, 404 and upstream 503. Repeat the focused command; no GET may post a read.
- [ ] Verify existing list locale and language-switch behavior remain unchanged; refactor only after tests pass.

Add route: `Route::get('/faq/{slug}', [FaqController::class, 'Show'])->name('faq.detail');`.

Add controller method:

```php
public function Show(\Illuminate\Http\Request $request, string $slug)
{
    $locale = in_array($request->query('lang'), ['th', 'en'], true) ? $request->query('lang') : 'th';
    app()->setLocale($locale);
    $faq = app(\App\Services\Faq\FaqContentClient::class)->Show($slug, $locale);
    $page_title = $faq['question'].' | eiMaths Thailand';
    $page_description = mb_substr($faq['plain_answer'], 0, 160, 'UTF-8');
    $canonical_page_url = rtrim((string) config('seo.canonical_origin'), '/').'/faq/'.rawurlencode($faq['slug']);
    return view('faq.detail', compact('faq', 'page_title', 'page_description', 'canonical_page_url'));
}
```

In the layout immediately after computing `$seo_page_url`, before computing `$seo_current_url`, insert:

```php
$seo_page_url = trim($__env->yieldContent('canonical_page_url', $seo_page_url));
```

`P/resources/views/faq/detail.blade.php`:

```blade
@extends('layouts.app')
@section('title', $page_title)
@section('meta_description', $page_description)
@section('og_title', $page_title)
@section('og_description', $page_description)
@section('canonical_page_url', $canonical_page_url)
@section('og_image', asset('images/faq-hero-bg.png'))
@section('content')
<section class="bg-orange-50 px-4 py-16">
    <article class="mx-auto max-w-4xl rounded-2xl border border-orange-100 bg-white p-6 md:p-10"
        data-faq-detail="true" data-faq-id="{{ $faq['id'] }}">
        <a class="inline-block rounded p-3 text-orange-700 underline focus-visible:outline focus-visible:outline-2"
            href="{{ route('faq', app()->getLocale() === 'en' ? ['lang' => 'en'] : []) }}">{{ __('faq.back') }}</a>
        <p class="mt-4 text-gray-600">{{ $faq['category']['name'] ?? '' }}</p>
        <h1 class="my-6 text-3xl font-bold text-gray-900">{{ $faq['question'] }}</h1>
        @include('faq.answer', ['faq' => $faq])
    </article>
</section>
@endsection
```

Keep typography for `.faq-answer` shared between list/detail by moving the existing answer-only CSS rules from `faq.blade.php` to `P/public/css/faq.css`, loading that stylesheet on both views via `@push('styles')`. Preserve the list-only hero rule. This file movement is behavior-preserving; first run `ContactLinksFromContentTest`, then recheck rendering and mobile/zoom presentation. Do not redesign unrelated colors or layout.

Add `faq-question` to the existing question span. On each `.faq-item`, add `data-faq-id` only when `detail_url !== null`, which requires both a valid ID and a valid slug. Legacy entries without slugs must not emit analytics identity. Insert a separate anchor after the answer toggle and before its panel:

```blade
@if($faq['detail_url'] !== null)
    <a class="mx-5 mb-3 inline-block rounded p-3 text-sm text-orange-700 underline focus-visible:outline focus-visible:outline-2"
        href="{{ $faq['detail_url'] }}">{{ __('faq.open_page') }}</a>
@endif
```

Create these locale dictionaries. Unicode escapes keep the English planning document readable while producing Thai UI strings in PHP:

```php
<?php
// P/lang/en/faq.php
return [
    'search_label' => 'Search questions and answers', 'clear' => 'Clear search',
    'result_count' => ':count questions found', 'no_results' => 'No matching questions. Try another search or category.',
    'categories' => 'FAQ categories', 'open_page' => 'Open question page', 'back' => 'Back to all questions',
    'unavailable' => 'FAQ information is temporarily unavailable. Please try again.',
];
```

```php
<?php
// P/lang/th/faq.php
return [
    'search_label' => "\u{0E04}\u{0E49}\u{0E19}\u{0E2B}\u{0E32}\u{0E04}\u{0E33}\u{0E16}\u{0E32}\u{0E21}\u{0E41}\u{0E25}\u{0E30}\u{0E04}\u{0E33}\u{0E15}\u{0E2D}\u{0E1A}",
    'clear' => "\u{0E25}\u{0E49}\u{0E32}\u{0E07}\u{0E04}\u{0E33}\u{0E04}\u{0E49}\u{0E19}",
    'result_count' => ":count \u{0E04}\u{0E33}\u{0E16}\u{0E32}\u{0E21}",
    'no_results' => "\u{0E44}\u{0E21}\u{0E48}\u{0E1E}\u{0E1A}\u{0E04}\u{0E33}\u{0E16}\u{0E32}\u{0E21} \u{0E25}\u{0E2D}\u{0E07}\u{0E04}\u{0E33}\u{0E04}\u{0E49}\u{0E19}\u{0E2B}\u{0E23}\u{0E37}\u{0E2D}\u{0E2B}\u{0E21}\u{0E27}\u{0E14}\u{0E2B}\u{0E21}\u{0E39}\u{0E48}\u{0E2D}\u{0E37}\u{0E48}\u{0E19}",
    'categories' => "\u{0E2B}\u{0E21}\u{0E27}\u{0E14}\u{0E04}\u{0E33}\u{0E16}\u{0E32}\u{0E21}",
    'open_page' => "\u{0E40}\u{0E1B}\u{0E34}\u{0E14}\u{0E2B}\u{0E19}\u{0E49}\u{0E32}\u{0E04}\u{0E33}\u{0E16}\u{0E32}\u{0E21}",
    'back' => "\u{0E01}\u{0E25}\u{0E31}\u{0E1A}\u{0E44}\u{0E1B}\u{0E04}\u{0E33}\u{0E16}\u{0E32}\u{0E21}\u{0E17}\u{0E31}\u{0E49}\u{0E07}\u{0E2B}\u{0E21}\u{0E14}",
    'unavailable' => "\u{0E44}\u{0E21}\u{0E48}\u{0E2A}\u{0E32}\u{0E21}\u{0E32}\u{0E23}\u{0E16}\u{0E42}\u{0E2B}\u{0E25}\u{0E14}\u{0E04}\u{0E33}\u{0E16}\u{0E32}\u{0E21}\u{0E44}\u{0E14}\u{0E49} \u{0E01}\u{0E23}\u{0E38}\u{0E13}\u{0E32}\u{0E25}\u{0E2D}\u{0E07}\u{0E43}\u{0E2B}\u{0E21}\u{0E48}",
];
```

`P/tests/Feature/FaqDetailPageTest.php`:

```php
<?php
namespace Tests\Feature;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\FaqFixture;
use Tests\TestCase;
final class FaqDetailPageTest extends TestCase
{
    #[Test] public function SharedDetailIgnoresStoredEnglishAndHasItsOwnCanonical(): void
    {
        config(['services.eimaths_backend.base_url' => 'https://backend.example.test',
            'seo.canonical_origin' => 'https://canonical.example.test']);
        Http::preventStrayRequests();
        Http::fake(['https://backend.example.test/api/faqs/how-does-cpa-work*' => Http::response(FaqFixture::Record())]);
        $this->withSession(['locale' => 'en'])->get('/faq/how-does-cpa-work?utm_source=fixture')
            ->assertOk()->assertSee('<html lang="th">', false)->assertSee('Learn with', false)
            ->assertSee('href="https://canonical.example.test/faq/how-does-cpa-work"', false);
        Http::assertSent(fn ($request): bool => $request->url() === 'https://backend.example.test/api/faqs/how-does-cpa-work?locale=th');
        Http::assertNotSent(fn ($request): bool => $request->method() === 'POST');
        $this->get('/faq/how-does-cpa-work?lang=en')->assertOk()
            ->assertSee('<html lang="en">', false)
            ->assertSee('href="https://canonical.example.test/faq/how-does-cpa-work?lang=en"', false);
    }
}
```

## Task 5: Search rendered content and preserve accessible accordion behavior

**Files:**

- Create: `P/public/js/faq-search.js`.
- Modify: `P/resources/views/faq.blade.php` search/filter markup and existing inline accordion script.
- Modify: `P/tests/browser/support/render-page.php` FAQ fixture allowlist and HTTP fakes.
- Create: `P/tests/browser/faq-search.spec.mjs`.

- **Consumes:** Task 3 safe answer partial, Task 4 localized UI strings and stable links; existing `RenderPage` fixture helper.
- **Produces:** Literal normalized substring filtering with category intersection, result count/empty state, accurate accordion state and the `FAQ_OPENED` event contract.
- **Checkpoint:** None. Keep fixture network isolation; the static fixture is not evidence of session persistence.

**Actions — production behavior changes:**

**REQUIRED IMPLEMENTATION SKILL:** `test-driven-development`.

- [ ] Extend only the fixture support required to render FAQ pages; run the existing FAQ PHP tests. Add the first browser assertion below and run P `node node_modules/@playwright/test/cli.js test tests/browser/faq-search.spec.mjs --reporter=line`. RED: no search interaction/result exists; fixture errors must be fixed first.
- [ ] Add markup and module below, removing the old inline FAQ script so listeners are registered once. GREEN: answer-only matches reveal headings but do not open answers or emit reads.
- [ ] Add separate behavior checks for keyboard Enter/Space, close/reopen, switching category after search, zero results, clearing, fullwidth/case/whitespace normalization, literal `[.*]` and `<script>` query, 200 Unicode-code-point limit, empty query, localized fallback, and a legacy entry with a numeric ID but no slug (no link, no analytics identity/event). Rerun the same browser file after each behavior change.
- [ ] Run PHP regressions and the complete browser suite. Inspect 375px and desktop viewports at 200% zoom for focus visibility, tap targets, overflow and readable text. The fixture's `.hidden` rule tests state, not actual Tailwind styling; visual verification needs the existing app assets and must be recorded separately.

Above the existing filter controls insert:

```blade
<div class="mx-auto mb-6 max-w-5xl">
    <label class="mb-2 block font-bold" for="faq-search">{{ __('faq.search_label') }}</label>
    <div class="flex gap-3">
        <input id="faq-search" type="search" maxlength="400" autocomplete="off"
            class="min-w-0 flex-1 rounded border border-orange-300 p-3 focus-visible:outline focus-visible:outline-2">
        <button id="faq-clear" type="button" class="rounded border border-orange-300 px-4 py-3 focus-visible:outline focus-visible:outline-2">{{ __('faq.clear') }}</button>
    </div>
    <p id="faq-result-count" class="mt-3" role="status" aria-live="polite" aria-atomic="true"
        data-count-template="{{ __('faq.result_count', ['count' => '__COUNT__']) }}"></p>
    <p id="faq-no-results" class="hidden py-6">{{ __('faq.no_results') }}</p>
</div>
```

The script caps at 200 code points; native `maxlength=400` permits 200 surrogate pairs without allowing unlimited browser input. Replace filter-group `role="tablist"` with `role="group"`, use `aria-label="{{ __('faq.categories') }}"`, and replace button `aria-selected` with `aria-pressed`. Keep category/answer buttons and their `aria-controls`. Add a visible focus outline to answer/filter buttons without suppressing existing outlines. Keep the existing article classes; add `data-search-answer="{{ $faq['plain_answer'] }}"` and, only when `$faq['detail_url'] !== null`, `data-faq-id="{{ $faq['id'] }}"`. No unescaped content belongs in attributes.

Load once with `@push('scripts') <script src="{{ asset('js/faq-search.js') }}" defer></script> @endpush` (use normal multiline Blade formatting).

`P/public/js/faq-search.js`:

```javascript
(() => {
    const MAX_QUERY_CHARACTERS = 200;
    function Normalize(text) {
        return text.normalize('NFKC').toLocaleLowerCase(document.documentElement.lang || 'th')
            .trim().replace(/\s+/gu, ' ');
    }
    function InitializeFaqSearch() {
        const search_input = document.getElementById('faq-search');
        if (!search_input) return;
        const clear_button = document.getElementById('faq-clear');
        const result_status = document.getElementById('faq-result-count');
        const no_results = document.getElementById('faq-no-results');
        const category_sections = Array.from(document.querySelectorAll('.faq-category'));
        const filter_buttons = Array.from(document.querySelectorAll('.faq-filter'));
        const faq_records = Array.from(document.querySelectorAll('.faq-item')).map(function CreateRecord(article) {
            return { article, question: Normalize(article.querySelector('.faq-question')?.textContent ?? ''),
                answer: Normalize(article.getAttribute('data-search-answer') ?? ''),
                category: article.closest('.faq-category').getAttribute('data-faq-category') };
        });
        let selected_category = 'all';
        let previous_query = null;
        function SetCategoryExpanded(toggle, is_expanded) {
            toggle.setAttribute('aria-expanded', String(is_expanded));
            document.getElementById(toggle.getAttribute('aria-controls'))?.classList.toggle('hidden', !is_expanded);
            toggle.querySelector('.faq-category-icon')?.classList.toggle('rotate-180', !is_expanded);
        }
        function CloseAnswers() {
            for (const record of faq_records) {
                const toggle = record.article.querySelector('.faq-toggle');
                toggle.setAttribute('aria-expanded', 'false');
                record.article.querySelector('.faq-panel').classList.add('hidden');
                toggle.querySelector('i')?.classList.replace('fa-minus', 'fa-plus');
            }
        }
        function ApplyFilters(force = false) {
            const truncated_query = Array.from(search_input.value).slice(0, MAX_QUERY_CHARACTERS).join('');
            if (search_input.value !== truncated_query) search_input.value = truncated_query;
            const query = Normalize(truncated_query);
            if (!force && query === previous_query) return;
            previous_query = query;
            CloseAnswers();
            let result_count = 0;
            const matching_categories = new Set();
            for (const record of faq_records) {
                const is_match = (selected_category === 'all' || selected_category === record.category)
                    && (query === '' || record.question.includes(query) || record.answer.includes(query));
                record.article.classList.toggle('hidden', !is_match);
                if (is_match) { result_count++; matching_categories.add(record.category); }
            }
            for (const section of category_sections) {
                const is_visible = matching_categories.has(section.getAttribute('data-faq-category'));
                section.classList.toggle('hidden', !is_visible);
                SetCategoryExpanded(section.querySelector('.faq-category-toggle'), is_visible && query !== '');
            }
            result_status.textContent = result_status.dataset.countTemplate.replace('__COUNT__', String(result_count));
            no_results.classList.toggle('hidden', result_count !== 0);
        }
        for (const section of category_sections) {
            const toggle = section.querySelector('.faq-category-toggle');
            toggle.addEventListener('click', function ToggleCategory() {
                SetCategoryExpanded(toggle, toggle.getAttribute('aria-expanded') !== 'true');
            });
        }
        for (const record of faq_records) {
            const toggle = record.article.querySelector('.faq-toggle');
            toggle.addEventListener('click', function ToggleAnswer() {
                const will_open = toggle.getAttribute('aria-expanded') !== 'true';
                CloseAnswers();
                if (!will_open) return;
                toggle.setAttribute('aria-expanded', 'true');
                record.article.querySelector('.faq-panel').classList.remove('hidden');
                toggle.querySelector('i')?.classList.replace('fa-plus', 'fa-minus');
                const faq_id = Number(record.article.dataset.faqId);
                if (Number.isSafeInteger(faq_id) && faq_id > 0) {
                    document.dispatchEvent(new CustomEvent('FAQ_OPENED', { detail: { faq_id } }));
                }
            });
        }
        for (const button of filter_buttons) {
            button.addEventListener('click', function SelectCategory() {
                selected_category = button.dataset.category;
                for (const filter_button of filter_buttons) {
                    const is_active = filter_button === button;
                    filter_button.setAttribute('aria-pressed', String(is_active));
                    for (const class_name of ['bg-orange-500', 'text-white', 'shadow-md']) {
                        filter_button.classList.toggle(class_name, is_active);
                    }
                    for (const class_name of ['bg-white', 'text-gray-700', 'border', 'border-orange-200']) {
                        filter_button.classList.toggle(class_name, !is_active);
                    }
                }
                ApplyFilters(true);
            });
        }
        search_input.addEventListener('input', function SearchChanged() { ApplyFilters(); });
        clear_button.addEventListener('click', function ClearSearch() {
            search_input.value = '';
            ApplyFilters(true);
            search_input.focus();
        });
        ApplyFilters(true);
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', InitializeFaqSearch, { once: true });
    } else {
        InitializeFaqSearch();
    }
})();
```

For text separated by block elements, index an answer string that matches rendered words: use the prepared `plain_answer` as an escaped `data-search-answer` on the answer article and read it in `CreateRecord` instead of unseparated `textContent`. Add `data-search-answer="{{ $faq['plain_answer'] }}"`; use `Normalize(article.getAttribute('data-search-answer') ?? '')`. This preserves text between paragraphs and excludes contact-link decoration. Never insert query text with `innerHTML`.

Fixture changes in `tests/browser/support/render-page.php` are exact and narrow:

1. Add `/faq` and `/faq/how-does-cpa-work` to the existing path allowlist; keep every existing entry and reCAPTCHA validation.
2. Before the existing wildcard fake, add a detail fake returning `\Tests\Support\FaqFixture::Record()` and a list fake returning that record plus `Record(['id' => 12, 'slug' => 'trial-lessons', 'question' => 'When are trial lessons?', 'answer' => 'Weekdays.', 'answer_html' => '<p>Weekdays.</p>', 'category' => ['id' => 6, 'slug' => 'enrolment', 'name' => 'Enrolment', 'position' => 2]])`.
3. For the FAQ detail fixture's kernel request only, append `?lang=` followed by the already-validated `$locale`. Do not change browser-helper origin restrictions, the catch-all fake, or production routes for test support.
4. All browser POSTs remain aborted by default. The companion adds explicit test interceptors only for its recording requests.

`P/tests/browser/faq-search.spec.mjs`:

```javascript
import { expect, test } from '@playwright/test';
import { RenderPage } from './support/page-fixture.mjs';

test('Answer search reveals headings without counting a read', async function SearchAnswer({ page }) {
    await RenderPage(page, '/faq');
    await page.evaluate(function ObserveOpenings() {
        window.faq_openings = [];
        document.addEventListener('FAQ_OPENED', function RecordOpening(event) { window.faq_openings.push(event.detail.faq_id); });
    });
    await page.getByLabel('Search questions and answers').fill(' BLOCKS ');
    await expect(page.locator('.faq-item:visible')).toHaveCount(1);
    await expect(page.locator('.faq-toggle:visible')).toContainText('How does CPA work?');
    await expect(page.locator('#faq-11')).toBeHidden();
    expect(await page.evaluate(() => window.faq_openings)).toEqual([]);
    await page.locator('.faq-toggle:visible').focus();
    await page.keyboard.press('Enter');
    await expect(page.locator('#faq-11')).toBeVisible();
    await expect(page.locator('.faq-toggle:visible')).toHaveAttribute('aria-expanded', 'true');
    expect(await page.evaluate(() => window.faq_openings)).toEqual([11]);
    await page.keyboard.press('Enter');
    await expect(page.locator('#faq-11')).toBeHidden();
    expect(await page.evaluate(() => window.faq_openings)).toEqual([11]);
});

test('Category and query intersect, and clear restores closed categories', async function CombineFilters({ page }) {
    await RenderPage(page, '/faq');
    await page.getByLabel('Search questions and answers').fill('blocks');
    await page.locator('.faq-filter[data-category="enrolment"]').click();
    await expect(page.locator('.faq-item:visible')).toHaveCount(0);
    await expect(page.locator('#faq-no-results')).toBeVisible();
    await page.getByRole('button', { name: 'Clear search', exact: true }).click();
    await expect(page.locator('.faq-category[data-faq-category="enrolment"]')).toBeVisible();
    await expect(page.locator('.faq-category-panel:visible')).toHaveCount(0);
    await expect(page.getByLabel('Search questions and answers')).toBeFocused();
});
```

## Task 6: Include public FAQ URLs in the sitemap without hiding failures

**Files:**

- Modify: `P/app/Http/Controllers/SitemapController.php::Index`; add `CreateFaqEntries`.
- Modify: `P/tests/Feature/CanonicalMetadataTest.php::MetadataAndSitemapUseOneConfiguredOrigin` HTTP fixture routing only.
- Create: `P/tests/Feature/FaqSitemapTest.php`.

- **Consumes:** Task 3 validated Thai list, approved canonical origin and actual FAQ `updated_at`.
- **Produces:** One queryless encoded URL per published question, unchanged existing static/blog entries, temporary 503 for failed/malformed FAQ dependencies.
- **Checkpoint:** None.

**Actions — production behavior changes:**

**REQUIRED IMPLEMENTATION SKILL:** `test-driven-development`.

- [ ] Add sitemap test below; P `php vendor/bin/phpunit --do-not-cache-result --filter 'FaqSitemapTest|CanonicalMetadataTest'`. RED: the FAQ URL is absent.
- [ ] Add the method and merge it after static/blog entries. GREEN: exact FAQ URL and modification date appear once, and static/blog entries remain.
- [ ] Add empty list (200), API failure (503), malformed collection (503), invalid/missing `updated_at` for a slug-bearing entry (503), legacy missing slug (omit only that entry), and encoded Unicode cases. Never substitute today's date for an invalid FAQ date. Run the same command after each case.
- [ ] Refactor and rerun the public PHP suite. Any existing sitemap fixture using one wildcard blog payload must be narrowed to `/api/blogs*` plus `/api/faqs*` with a valid FAQ/empty fixture; preserve its original assertions.

Add the following private method to `SitemapController`; append `$this->CreateFaqEntries()` to the existing `array_merge` in `Index`:

```php
private function CreateFaqEntries(): array
{
    $entries = [];
    foreach (app(\App\Services\Faq\FaqContentClient::class)->Index('th') as $faq) {
        if ($faq['id'] === null || $faq['slug'] === null) {
            continue;
        }
        $modified_at = $faq['updated_at'] ?? null;
        if (!is_string($modified_at) || trim($modified_at) === '') {
            abort(503, __('faq.unavailable'));
        }
        $date = $this->FaqModifiedDate($modified_at);
        $entries[] = ['location' => $this->CreateAbsoluteUrl('/faq/'.rawurlencode($faq['slug'])),
            'last_modified_at' => $date, 'change_frequency' => 'monthly', 'priority' => '0.6'];
    }
    return $entries;
}
```

Add this private helper to the same controller; it accepts actual ISO dates/timestamps and rejects relative phrases, impossible calendar dates and malformed time/offset components. Backend timestamps are UTC ISO strings; retain their actual calendar date. Keep `CreateDateString` for blog behavior unchanged.

```php
private function FaqModifiedDate(string $modified_at): string
{
    $pattern = '/^([0-9]{4})-([0-9]{2})-([0-9]{2})(?:T(?:[01][0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9](?:\.[0-9]{1,6})?(?:Z|[+-](?:(?:0[0-9]|1[0-3]):[0-5][0-9]|14:00)))?$/D';
    if (preg_match($pattern, $modified_at, $parts) !== 1
        || !checkdate((int) $parts[2], (int) $parts[3], (int) $parts[1])) {
        abort(503, __('faq.unavailable'));
    }
    return $parts[1].'-'.$parts[2].'-'.$parts[3];
}
```

`P/tests/Feature/FaqSitemapTest.php`:

```php
<?php
namespace Tests\Feature;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\FaqFixture;
use Tests\TestCase;
final class FaqSitemapTest extends TestCase
{
    #[Test] public function SitemapIncludesStableFaqDateAndRejectsDependencyFailure(): void
    {
        config(['services.eimaths_backend.base_url' => 'https://backend.example.test',
            'seo.canonical_origin' => 'https://canonical.example.test']);
        Http::preventStrayRequests();
        Http::fake(['https://backend.example.test/api/faqs*' => Http::response([FaqFixture::Record()]),
            'https://backend.example.test/api/blogs*' => Http::response([])]);
        $xml = $this->get('/sitemap.xml')->assertOk()->getContent();
        $document = simplexml_load_string($xml);
        $document->registerXPathNamespace('s', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $faq_nodes = $document->xpath('//s:url[s:loc="https://canonical.example.test/faq/how-does-cpa-work"]');
        $this->assertCount(1, $faq_nodes);
        $this->assertSame('2026-09-02', (string) $faq_nodes[0]->lastmod);
        $this->assertStringContainsString('<loc>https://canonical.example.test/faq</loc>', $xml);
        Http::assertNotSent(fn ($request): bool => $request->method() === 'POST');
        Http::fake(['https://backend.example.test/api/faqs*' => Http::response([], 503),
            'https://backend.example.test/api/blogs*' => Http::response([])]);
        $this->get('/sitemap.xml')->assertStatus(503);
    }
}
```

## Task 7: Verify compatibility and record deployment order

**Files:**

- Modify: This plan's execution record below, only after checks actually run.
- Test: All PHP/browser tests named above and the two complete PHP suites/public browser suite.

- **Consumes:** Tasks 1-6 and the approved specification's rollout constraints.
- **Produces:** Evidence for implementation behavior, remaining limitations and a reviewed deployment sequence.
- **Checkpoint:** Do not deploy, run live backfill, commit, or push without the applicable authorization. Analytics remains controlled by the companion's off-by-default flag.

**Actions — nonbehavioral verification artifact:**

- [ ] Run the focused commands and complete suites listed in this plan; require exit 0 and inspect warnings/skips. Run `git diff --check` in both worktrees; require no whitespace errors. Review new/untracked file contents as well as tracked diffs.
- [ ] Confirm initial HTML contains answers, links preserve language/slug, Unicode paths round-trip, altered questions keep links, hidden content disappears from public surfaces, and no content GET emits a write.
- [ ] Confirm migrations only add nullable unique slugs; the backfill is bounded, resumable and preserves timestamps/content. Deployment order is backend schema/new writer/API, drain old writers, backfill/verify missing/duplicate slugs, then public pages/sitemap. Preserve the column on application rollback.
- [ ] Record commands, output, checked revisions, visual coverage and any remaining production-configuration unknowns here. Do not mark engine-level slug collision proof complete from sequential SQLite tests. The companion's isolated database concurrency exercise also covers simultaneous slug assignment when a disposable intended-engine database is available.
- [ ] Apply `verification-before-completion`, `you-should-know`, and a final code review. Address only in-scope findings; report unrelated failures separately.

## Requirement-to-task trace

| Approved requirement | Owning tasks and checks |
| --- | --- |
| Question/answer search, category intersection, ordering, counts, empty state | Task 5, browser behavior cases |
| Accordion behavior and accessible state | Task 5, keyboard/open/close and event tests |
| Stable unique slugs, backfill and compatibility | Tasks 1-2, slug and API tests |
| Locale and Thai fallback | Tasks 2-4, API/detail/list regression tests |
| Safe rich/plain HTML and JSON-LD | Task 3, DOM/security/contact-link tests; Task 5 browser execution check |
| Initial HTML, self canonicals, metadata, alternates | Task 4, detail/metadata tests |
| Published sitemap entries with real dates | Task 6, XML tests and unchanged static/blog checks |
| 404 versus upstream 503 versus valid empty | Tasks 2-4 and 6, explicit failure fixtures |
| Analytics independence and event integration | Cross-plan contract; Task 5 emits only qualifying events; companion tests delivery |
| No live side effects and safe rollout | Tasks 1 and 7, isolated tests and deployment checkpoint |

## Execution record and approval

Planning evidence: paths, existing symbols/contracts, local dependency versions, CLI help, test conventions and relevant integration boundaries were inspected. No application test, migration, browser scenario, build or production check was executed while preparing this plan. Future expected results are not observed results.

Execution method remains for the user to select at handoff. Recommended: Native execution by the current agent, with a final independent review, because both plans touch the same FAQ presentation and HTTP boundaries. Per-task independent execution is also available and adds a separate implementer/reviewer cycle for each task with more context/coordination overhead. Do not prepare worktrees or implement until this completed plan and the companion are approved and the method is selected.

Planning review on 2026-10-20: both plans were checked against the approved specification, current repository interfaces, naming/access boundaries and their requirement-to-task matrices. All 70 declared file references resolved either to inspected existing files or explicitly planned new files; no create target already existed. The final syntax-only check parsed 46 PHP blocks with complete-input `token_get_all(..., TOKEN_PARSE)` and checked four JavaScript blocks with `node --input-type=module --check`, with zero failures. Markdown fences/tables/task contracts, English document content, LF encoding and final newlines were checked. These checks do not execute Blade, application behavior, HTTP, database transactions or browser scenarios.
