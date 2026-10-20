# Backend Localized FAQ Slugs Implementation Plan

> **REQUIRED EXECUTION SKILL:** After approval of this plan and its companion, use git-worktrees, then executing-plans with the preserved Native execution method.

- **Goal:** Authorized FAQ saves atomically maintain independent Thai/English current slugs and one-year aliases; compatible APIs resolve current/old URLs without resetting read statistics.
- **Architecture:** Backend owns title normalization, ID/revision allocation, current/alias persistence, publication checks, stable legacy API compatibility, backfill, and expiry deletion. The public application consumes the exact API contract below through plan 0006.
- **Tech Stack:** Laravel 10.50.2, PHP 8.2.12, PHPUnit 10.5.63, existing MySQL/MariaDB runtime, in-memory SQLite functional tests, Symfony Process for isolated engine concurrency checks.
- **Source:** [Approved design 0004](../specs/0004-faq-localized-slug-history-design.md), SHA256 594B41F825B68929467B0FC569EFF257907B94FF634B004349FAA41CE7400479. The user approved the written specification and subsequently the backend/public two-plan split on 2026-10-20.
- **Companion:** [Public localized URLs plan 0006](0006-faq-localized-urls-public.md).
- **Execution method:** Native, carried forward from the explicit user selection recorded in 0003-0004-faq-execution-record.md. The current agent implements; independent final review follows. Preserve the user's main-branch preference; consult git-worktrees without overriding that preference or discarding existing changes.
- **Status:** Proposed plan awaiting approval. This document does not execute code, tests, migrations, commands, commits, server changes, or production activation.

## Global Constraints

Copied from the approved design:

> Preserve FAQ IDs, read statistics, receipt deduplication, permissions, content, categories, publication rules, ordering, search, and the existing accordion interaction.

> The one-year retention decision applies only to URL aliases, not analytics records.

> Resolve only the exact (locale, decoded slug) key in faq_slug_routes.

> Visits, repeated backfills, subsequent edits, and publication toggles never renew its lifetime.

> Do not prescribe a blanket feature-flag rollback after exposure or drop tables/reset revisions.

The authoritative design defines the remaining contracts. One year is the Bangkok calendar anniversary, clamped on February 29, stored as UTC with microseconds. Current rows never expire. All editor saves are atomic; a failed allocation preserves the previous content and routes. Existing faqs.slug values remain stable. A generated URL always ends in its FAQ ID and a per-language, never-reused positive revision. Empty English titles use Thai fallback; equivalent normalized titles do not add history.

Use PascalCase new named functions/methods, snake_case local/serialized/database identifiers, UPPER_CASE constants/ENV, PSR-4 PHP filenames, and existing framework-required method names. Write documentation in English. No manual slug editor, history UI, analytics pruning, external service, unrelated repair, or automatic commit/push is in scope.

Every production change follows test-driven-development and writing-good-tests.md. Code blocks are future instructions, not executed implementation or evidence. Copy each production block only after the owning real behavioral assertion fails. Resolve setup/class/fixture errors before accepting RED. Keep one RED/GREEN cycle at a time; complete files may contain regression cases that already pass.

## Verified baseline and commands

B/ denotes the existing backend eiMaths directory; P/ denotes sibling eimaths_th_new. These are document prefixes, not directories to create. Both plans live under B/docs/plans. Backend HEAD is 893a312f987b2a5c019e1cf5c96a04bbcc0d3c44; public HEAD is 266e96624ac03a1ee74ec42e4c09628df636919a. Before plan creation, only approved design 0004 was untracked in B; P was clean.

Verified: existing writer Create/Assign, controller Store/Update/ApiIndex/ApiShow, model localization, serializer, console command auto-discovery, both kernels, FAQ migrations, test fixtures, PHP/ PHPUnit versions, CLI test discovery by existing file, Carbon's addYearNoOverflow/toISOString, request validation macros, locking-query methods, exception rendering, and test PSR-4 loading. New target paths below were checked absent; existing target files/symbols were read. PHP configuration caching is currently absent in both repositories. Future outcomes below are expectations, not test results.

Run B commands from its root in PowerShell:

~~~powershell
$faq_php = 'C:\xampp\php\php.exe'
& $faq_php vendor/bin/phpunit --do-not-cache-result tests/Feature/FaqLocalizedSchemaTest.php
& $faq_php vendor/bin/phpunit --do-not-cache-result --filter 'Faq|ContentLocalization'
& $faq_php vendor/bin/phpunit --do-not-cache-result
~~~

The first path is created in Task 1; installed CLI syntax and existing-file discovery were verified without running application tests during planning. Before execution, confirm phpunit.xml still forces APP_ENV=testing, DB_CONNECTION=sqlite, DB_DATABASE=:memory:, array cache/sessions, and no cached configuration overrides isolation.

Historical limitation, not a fresh baseline: execution record 0003-0004 reports the main backend full suite at 1121 tests / 35904 assertions / 1 error / 64 failures / 3 skips, with all 46 FAQ/content-localization cases passing. Earlier user approval allowed scoped work with unrelated failures. Run the full suite again at execution completion, report every current failure and changed identity, and never call that suite passing unless exit code is zero. Do not repair unrelated failures without authorization.

## Cross-plan contract and order

This plan can develop its behavior independently with an isolated database. Plan 0006 can develop against complete HTTP fakes while backend work proceeds, but public activation waits for this plan's API/backfill checks.

- Each project has its own FAQ_LOCALIZED_URLS_ENABLED=false, separate from read tracking.
- Legacy GET /api/faqs and GET /api/faqs/{legacy_slug} keep stable slug and existing public fields. Active backend adds localized_slugs with th/en current values, nullable only during initialization.
- GET /api/faq-pages/{faq_id}?locale=th|en returns the complete public record with both valid current slugs.
- GET /api/faq-pages/resolve/{slug}?locale=th|en returns requested_slug, locale, match_type=current|alias, alias_expires_at (null for current; future UTC ISO timestamp for alias), and faq containing the complete record.
- Resolver miss/expired/nonpublic: 404. Known owner with incomplete/inconsistent current metadata or unavailable service: 503. No fallback through another locale or faqs.slug.
- New API 404/503 responses use error_code/message (FAQ_NOT_FOUND or FAQ_CONTENT_UNAVAILABLE); responses/errors carry no-store. No backend HTTP redirect is introduced.
- Public side builds the 301 Location from its configured origin and the selected current slug. Neither plan changes the FAQ/session read key.

## Task 1: Add inert configuration and additive URL metadata

**Files:**

- Create: B/config/faq-urls.php.
- Create: B/database/migrations/2026_10_20_000003_create_faq_localized_slug_tables.php.
- Modify: B/.env.example, append the new nonsecret flag once.
- Modify: B/phpunit.xml, inside php, force the new flag false.
- Test: B/tests/Feature/FaqLocalizedSchemaTest.php.

- **Consumes:** Existing faqs.id and migration auto-discovery.
- **Produces:** Two indexed/FK-protected metadata tables; inert default-false configuration.
- **Checkpoint:** None for isolated tests after plan approval. Do not run migrations on a user or production database.

**Actions — production behavior changes:**

**REQUIRED IMPLEMENTATION SKILL:** Use test-driven-development.

- [ ] Write the complete test below. Run the Task 1 command above; expect a failed hasTable assertion because the new tables do not exist, not a database/setup error.
- [ ] Add the exact configuration/migration below and the two flag lines described after them.
- [ ] Rerun the same command; expect exit 0, new tables/columns, preserved legacy column, and default-off activation.
- [ ] Run B/tests/Feature/FaqSlugTest.php; expect its existing disabled-writer behavior unchanged.
- [ ] Refactor only with these checks GREEN.

~~~php
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class FaqLocalizedSchemaTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function MetadataIsAdditiveAndActivationDefaultsOff(): void
    {
        $this->assertTrue(Schema::hasTable('faq_localized_slug_states'));
        $this->assertTrue(Schema::hasTable('faq_slug_routes'));
        $this->assertTrue(Schema::hasColumn('faqs', 'slug'));
        $this->assertFalse(config('faq-urls.enabled'));
        foreach (['faq_id', 'locale', 'last_revision', 'source_base', 'initialized_at'] as $column) {
            $this->assertTrue(Schema::hasColumn('faq_localized_slug_states', $column));
        }
        foreach (['faq_id', 'locale', 'slug', 'revision', 'current_marker', 'retired_at', 'expires_at'] as $column) {
            $this->assertTrue(Schema::hasColumn('faq_slug_routes', $column));
        }
    }
}
~~~

Complete B/config/faq-urls.php:

~~~php
<?php

return [
    'enabled' => (bool) env('FAQ_LOCALIZED_URLS_ENABLED', false),
];
~~~

Complete migration:

~~~php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('faq_localized_slug_states', function (Blueprint $table): void {
            $table->foreignId('faq_id')->constrained('faqs')->cascadeOnDelete();
            $table->string('locale', 2);
            $table->unsignedBigInteger('last_revision');
            $table->string('source_base', 120);
            $table->dateTime('initialized_at', 6);
            $table->primary(['faq_id', 'locale'], 'faq_localized_state_primary');
        });
        Schema::create('faq_slug_routes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('faq_id')->constrained('faqs')->cascadeOnDelete();
            $table->string('locale', 2);
            $table->string('slug', 255);
            $table->unsignedBigInteger('revision')->nullable();
            $table->unsignedTinyInteger('current_marker')->nullable();
            $table->dateTime('retired_at', 6)->nullable();
            $table->dateTime('expires_at', 6)->nullable();
            $table->unique(['locale', 'slug'], 'faq_route_identity_unique');
            $table->unique(['faq_id', 'locale', 'current_marker'], 'faq_current_route_unique');
            $table->index(['expires_at', 'id'], 'faq_route_expiry_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faq_slug_routes');
        Schema::dropIfExists('faq_localized_slug_states');
    }
};
~~~

Append FAQ_LOCALIZED_URLS_ENABLED=false to B/.env.example without changing or printing existing credentials. Add this element inside phpunit.xml's existing php element:

~~~xml
<env name="FAQ_LOCALIZED_URLS_ENABLED" value="false" force="true"/>
~~~

## Task 2: Create complete localized identities atomically

**Files:**

- Create: B/app/Services/Faq/FaqLocalizedSlugs.php.
- Create: B/app/Services/Faq/UnavailableFaqUrls.php.
- Create: B/tests/Support/FaqSlugFixture.php.
- Modify: B/app/Services/Faq/FaqSlugWriter.php at Create and Assign; add Commit.
- Modify: B/lang/en/content-management.php and B/lang/th/content-management.php at faq.messages.
- Test: B/tests/Feature/FaqLocalizedCreationTest.php.

- **Consumes:** Task 1 schema/config, existing Faq localization, creation transaction, stable legacy allocator.
- **Produces:** FaqLocalizedSlugs::Initialize(Faq): bool and Read(Collection, bool): array; existing Create/Assign remain compatible while disabled.
- **Checkpoint:** None for in-memory tests. Real database use is deferred to the guarded Task 3 checkpoint.

**Actions — production behavior changes:**

**REQUIRED IMPLEMENTATION SKILL:** Use test-driven-development.

- [ ] Create the fixture and creation test exactly below. Run: & $faq_php vendor/bin/phpunit --do-not-cache-result tests/Feature/FaqLocalizedCreationTest.php. Expect missing state/current-route assertion failures because the existing writer ignores activation.
- [ ] Add the feature service and safe exception; replace only the three shown writer members, preserving BASE_LENGTH/imports and unrelated code.
- [ ] Add the two exact localized error entries below.
- [ ] Rerun the creation file; expect exit 0 for all title cases, reserved collisions, finite exhaustion/rollback, duplicate titles, and disabled legacy behavior.
- [ ] Rerun FaqSlugTest and FaqDetailApiTest with the same PHPUnit command/file syntax; expect their legacy writer/API assertions GREEN.
- [ ] Refactor without changing URLs, candidate bounds, or transaction boundaries.

Complete fixture:

~~~php
<?php

namespace Tests\Support;

use App\Models\Department;
use App\Models\Faq;
use App\Models\FaqCategory;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;

trait FaqSlugFixture
{
    private function Attributes(array $overrides = []): array
    {
        return array_replace([
            'category_id' => FaqCategory::query()->firstOrFail()->id,
            'question' => 'Primary question',
            'question_en' => 'English question',
            'answer' => 'Answer',
            'answer_en' => 'English answer',
            'position' => 0,
            'is_published' => true,
        ], $overrides);
    }

    private function Editor(): User
    {
        $department = Department::find(Department::HQ_ID);
        if ($department === null) {
            Department::forceCreate(['id' => Department::HQ_ID, 'name' => 'FAQ Test HQ',
                'code' => 'FAQ-TEST-HQ', 'status' => 1, 'enabled_modules' => null]);
        } else {
            $department->update(['status' => 1, 'enabled_modules' => null]);
        }
        $editor = User::factory()->create(['department_id' => Department::HQ_ID,
            'disable' => 0, 'password' => Hash::make('test-only-password')]);
        foreach (['faq-create', 'faq-edit'] as $permission_name) {
            $editor->givePermissionTo(Permission::findOrCreate($permission_name, 'web'));
        }
        return $editor;
    }

    private function CurrentSlug(Faq $faq, string $locale): string
    {
        return (string) \Illuminate\Support\Facades\DB::table('faq_slug_routes')
            ->where('faq_id', $faq->id)->where('locale', $locale)
            ->where('current_marker', 1)->value('slug');
    }
}
~~~

Complete creation test:

~~~php
<?php

namespace Tests\Feature;

use App\Models\Faq;
use App\Services\Faq\FaqSlugWriter;
use App\Services\Faq\UnavailableFaqUrls;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\FaqSlugFixture;
use Tests\TestCase;

final class FaqLocalizedCreationTest extends TestCase
{
    use RefreshDatabase, FaqSlugFixture;

    public static function Titles(): array
    {
        return [
            'separate titles' => ['Primary question', 'English question', 'primary-question', 'english-question'],
            'fallback and punctuation' => ['?!!', null, 'faq', 'faq'],
            'zero remains text' => ['0', '0', '0', '0'],
            'unicode' => ["\u{0E04}\u{0E33}\u{0E16}\u{0E32}\u{0E21}", null,
                "\u{0E04}\u{0E33}\u{0E16}\u{0E32}\u{0E21}", "\u{0E04}\u{0E33}\u{0E16}\u{0E32}\u{0E21}"],
            'markup and normalization' => ['<b>PRIMARY</b>  question!', 'ENGLISH question?', 'primary-question', 'english-question'],
            'long base' => [str_repeat('a', 121), null, str_repeat('a', 120), str_repeat('a', 120)],
        ];
    }

    #[Test, DataProvider('Titles')]
    public function CreationProducesIndependentLocalizedRoutes(string $primary, ?string $english, string $primary_base, string $english_base): void
    {
        config(['faq-urls.enabled' => true]);
        $faq = app(FaqSlugWriter::class)->Create($this->Attributes(['question' => $primary, 'question_en' => $english]));
        $this->assertSame(2, DB::table('faq_localized_slug_states')->where('faq_id', $faq->id)->count());
        $this->assertSame($primary_base.'-'.$faq->id.'-1', $this->CurrentSlug($faq, 'th'));
        $this->assertSame($english_base.'-'.$faq->id.'-1', $this->CurrentSlug($faq, 'en'));
        $this->assertSame($english_base.'-'.$faq->id.'-1', $faq->refresh()->slug);
        $duplicate = app(FaqSlugWriter::class)->Create($this->Attributes(['question' => $primary, 'question_en' => $english]));
        $this->assertNotSame($this->CurrentSlug($faq, 'th'), $this->CurrentSlug($duplicate, 'th'));
    }

    #[Test]
    public function ExistingLegacyValuesReserveCandidatesAndExhaustionRollsBack(): void
    {
        config(['faq-urls.enabled' => true]);
        $faq = Faq::create($this->Attributes());
        Faq::forceCreate($this->Attributes(['slug' => 'primary-question-'.$faq->id.'-1']));
        app(FaqSlugWriter::class)->Assign($faq->id);
        $this->assertSame('primary-question-'.$faq->id.'-2', $this->CurrentSlug($faq, 'th'));

        $pending = Faq::create($this->Attributes());
        for ($revision = 1; $revision <= 32; $revision++) {
            Faq::forceCreate($this->Attributes(['slug' => 'primary-question-'.$pending->id.'-'.$revision]));
        }
        try {
            app(FaqSlugWriter::class)->Assign($pending->id);
            $this->fail('Reserved candidates must not be reused.');
        } catch (UnavailableFaqUrls $exception) {
            $this->assertSame('faq.urls.allocation_exhausted', $exception->operation);
        }
        $this->assertSame(0, DB::table('faq_slug_routes')->where('faq_id', $pending->id)->count());
        $this->assertSame(0, DB::table('faq_localized_slug_states')->where('faq_id', $pending->id)->count());
        $this->assertNull($pending->refresh()->slug);
    }

    #[Test]
    public function DisabledCreationRetainsTheLegacyContract(): void
    {
        config(['faq-urls.enabled' => false]);
        $faq = app(FaqSlugWriter::class)->Create($this->Attributes());
        $this->assertSame('english-question', $faq->slug);
        $this->assertSame(0, DB::table('faq_slug_routes')->where('faq_id', $faq->id)->count());
    }
}
~~~

Complete safe exception (lowercase report/render are Laravel callback contracts):

~~~php
<?php

namespace App\Services\Faq;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

final class UnavailableFaqUrls extends RuntimeException
{
    public function __construct(public readonly string $operation = 'faq.urls.unavailable')
    {
        parent::__construct('FAQ URL metadata is temporarily unavailable.');
    }

    public function report(): void
    {
        Log::error('faq.urls.unavailable', [
            'operation' => $this->operation,
            'error_code' => 'FAQ_CONTENT_UNAVAILABLE',
        ]);
    }

    public function render(Request $request): Response
    {
        if ($request->is('api/*')) {
            return response()->json(['error_code' => 'FAQ_CONTENT_UNAVAILABLE',
                'message' => 'FAQ information is temporarily unavailable.'], 503)
                ->header('Cache-Control', 'no-store');
        }
        return response(__('content-management.faq.messages.save_unavailable'), 503)
            ->header('Cache-Control', 'no-store');
    }
}
~~~

Complete initial FaqLocalizedSlugs service:

~~~php
<?php

namespace App\Services\Faq;

use App\Models\Faq;
use Carbon\CarbonImmutable;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class FaqLocalizedSlugs
{
    private const LOCALES = ['th', 'en'];
    private const MAX_CANDIDATES = 32;

    private function Base(string $question): string
    {
        $base = mb_strtolower(strip_tags($question), 'UTF-8');
        $base = preg_replace('/[^\p{L}\p{M}\p{N}]+/u', '-', $base) ?? '';
        $base = trim(mb_substr(trim($base, '-'), 0, 120, 'UTF-8'), '-');
        return $base === '' ? 'faq' : $base;
    }

    private function Deadline(CarbonImmutable $retired_at): CarbonImmutable
    {
        return $retired_at->setTimezone('Asia/Bangkok')->addYearNoOverflow()->utc();
    }

    private function Stamp(CarbonImmutable $instant): string
    {
        return $instant->utc()->format('Y-m-d H:i:s.u');
    }

    private function PositiveInteger(mixed $number): int
    {
        $text = (string) $number;
        if (!preg_match('/^[1-9][0-9]*$/D', $text)
            || (string) (int) $text !== $text || (int) $text < 1) {
            throw new UnavailableFaqUrls('faq.urls.integer');
        }
        return (int) $text;
    }

    public function Read(Collection $faqs, bool $require_complete = true): array
    {
        if ($faqs->isEmpty()) return [];
        $faq_ids = $faqs->pluck('id')->all();
        $states = DB::table('faq_localized_slug_states')->whereIn('faq_id', $faq_ids)->sharedLock()->get()->groupBy('faq_id');
        $routes = DB::table('faq_slug_routes')->whereIn('faq_id', $faq_ids)
            ->where('current_marker', 1)->sharedLock()->get()->groupBy('faq_id');
        $result = [];
        foreach ($faqs as $faq) {
            $faq_states = $states->get($faq->id, collect());
            $faq_routes = $routes->get($faq->id, collect());
            if ($faq_states->isEmpty() && $faq_routes->isEmpty() && !$require_complete) {
                $result[$faq->id] = ['th' => null, 'en' => null];
                continue;
            }
            $result[$faq->id] = $this->ValidateState($faq, $faq_states, $faq_routes, true);
        }
        return $result;
    }

    private function ValidateState(Faq $faq, Collection $states, Collection $routes, bool $check_sources): array
    {
        if ($states->count() !== 2 || $routes->count() !== 2
            || $states->pluck('locale')->sort()->values()->all() !== ['en', 'th']
            || $routes->pluck('locale')->sort()->values()->all() !== ['en', 'th']
            || !is_string($faq->slug) || $faq->slug === '') {
            throw new UnavailableFaqUrls('faq.urls.state');
        }
        $states = $states->keyBy('locale');
        $routes = $routes->keyBy('locale');
        $slugs = [];
        foreach (self::LOCALES as $locale) {
            $state = $states[$locale];
            $route = $routes[$locale];
            $revision = $this->PositiveInteger($state->last_revision);
            if (!is_string($state->source_base) || $state->source_base === ''
                || mb_strlen($state->source_base) > 120
                || $this->PositiveInteger($route->revision) !== $revision
                || $route->slug !== $state->source_base.'-'.$faq->id.'-'.$revision
                || $route->retired_at !== null || $route->expires_at !== null
                || ($check_sources && $state->source_base !== $this->Base($faq->LocalizedQuestion($locale)))) {
                throw new UnavailableFaqUrls('faq.urls.state');
            }
            $slugs[$locale] = $route->slug;
        }
        return $slugs;
    }

    private function Allocate(Faq $faq, string $locale, string $base, int $previous_revision): array
    {
        for ($attempt = 0; $attempt < self::MAX_CANDIDATES; $attempt++) {
            if ($previous_revision === PHP_INT_MAX) {
                throw new UnavailableFaqUrls('faq.urls.revision_overflow');
            }
            $revision = ++$previous_revision;
            $candidate = $base.'-'.$faq->id.'-'.$revision;
            if (DB::table('faqs')->where('slug', $candidate)->exists()) continue;
            try {
                $route_id = DB::transaction(fn (): int => DB::table('faq_slug_routes')->insertGetId([
                    'faq_id' => $faq->id, 'locale' => $locale, 'slug' => $candidate,
                    'revision' => $revision, 'current_marker' => 1,
                    'retired_at' => null, 'expires_at' => null,
                ]));
                return ['id' => $route_id, 'slug' => $candidate, 'revision' => $revision];
            } catch (UniqueConstraintViolationException $exception) {
                $message = $exception->getMessage();
                if (!str_contains($message, 'faq_route_identity_unique')
                    && !str_contains($message, 'faq_slug_routes.locale, faq_slug_routes.slug')) {
                    throw $exception;
                }
            }
        }
        throw new UnavailableFaqUrls('faq.urls.allocation_exhausted');
    }

    public function Initialize(Faq $faq): bool
    {
        if (DB::transactionLevel() < 1) throw new UnavailableFaqUrls('faq.urls.transaction');
        $states = DB::table('faq_localized_slug_states')->where('faq_id', $faq->id)->get();
        if ($states->isNotEmpty()) {
            $this->Read(collect([$faq]));
            return false;
        }
        if (DB::table('faq_slug_routes')->where('faq_id', $faq->id)->exists()) {
            throw new UnavailableFaqUrls('faq.urls.partial_initialization');
        }
        $now = CarbonImmutable::now('UTC');
        $current = [];
        foreach (self::LOCALES as $locale) {
            $base = $this->Base($faq->LocalizedQuestion($locale));
            $current[$locale] = $this->Allocate($faq, $locale, $base, 0);
            DB::table('faq_localized_slug_states')->insert([
                'faq_id' => $faq->id, 'locale' => $locale,
                'last_revision' => $current[$locale]['revision'],
                'source_base' => $base, 'initialized_at' => $this->Stamp($now),
            ]);
        }
        if (!is_string($faq->slug) || $faq->slug === '') {
            DB::table('faqs')->where('id', $faq->id)->update(['slug' => $current['en']['slug']]);
            $faq->setAttribute('slug', $current['en']['slug']);
        }
        foreach (self::LOCALES as $locale) {
            if ($faq->slug === $current[$locale]['slug']) continue;
            DB::table('faq_slug_routes')->insert([
                'faq_id' => $faq->id, 'locale' => $locale, 'slug' => $faq->slug,
                'revision' => null, 'current_marker' => null,
                'retired_at' => $this->Stamp($now),
                'expires_at' => $this->Stamp($this->Deadline($now)),
            ]);
        }
        return true;
    }
}
~~~

Replace FaqSlugWriter::Create/Assign and add Commit (class-member block):

~~~php
public function Create(array $attributes): Faq
{
    return $this->Commit(function () use ($attributes): Faq {
        $faq = Faq::create($attributes);
        $this->Assign((int) $faq->id);
        return $faq->refresh();
    });
}

public function Assign(int $faq_id): string
{
    return $this->Commit(function () use ($faq_id): string {
        $faq = Faq::query()->lockForUpdate()->findOrFail($faq_id);
        if (config('faq-urls.enabled', false)) {
            app(FaqLocalizedSlugs::class)->Initialize($faq);
            return (string) $faq->slug;
        }
        if (is_string($faq->slug) && $faq->slug !== '') return $faq->slug;
        $source = filled($faq->question_en) ? $faq->question_en : $faq->question;
        $base = mb_strtolower(strip_tags($source), 'UTF-8');
        $base = preg_replace('/[^\p{L}\p{M}\p{N}]+/u', '-', $base) ?? '';
        $base = trim(mb_substr(trim($base, '-'), 0, self::BASE_LENGTH, 'UTF-8'), '-');
        $base = $base === '' ? 'faq-'.$faq_id : $base;
        for ($collision_number = 0; ; $collision_number++) {
            $candidate = $base.($collision_number === 0 ? '' : '-'.$faq_id);
            if ($collision_number > 1) $candidate .= '-'.($collision_number - 1);
            try {
                DB::transaction(function () use ($faq_id, $candidate): void {
                    DB::table('faqs')->where('id', $faq_id)->update(['slug' => $candidate]);
                });
                return $candidate;
            } catch (UniqueConstraintViolationException $exception) {
                // Preserve the legacy slug-only allocation behavior while disabled.
            }
        }
    });
}

private function Commit(\Closure $changes): mixed
{
    try {
        return DB::transaction($changes, 3);
    } catch (\PDOException $exception) {
        if (!config('faq-urls.enabled', false)) throw $exception;
        throw new UnavailableFaqUrls('faq.urls.write');
    }
}
~~~

Add only these keys inside each existing faq.messages array:

~~~php
// English
'save_unavailable' => 'The FAQ could not be saved. Please try again.',
// Thai (Unicode escapes keep this planning document's prose in English).
'save_unavailable' => "\u{E1A}\u{E31}\u{E19}\u{E17}\u{E36}\u{E01}\u{E04}\u{E33}\u{E16}\u{E32}\u{E21}\u{E44}\u{E21}\u{E48}\u{E44}\u{E14}\u{E49} \u{E01}\u{E23}\u{E38}\u{E13}\u{E32}\u{E25}\u{E2D}\u{E07}\u{E43}\u{E2B}\u{E21}\u{E48}\u{E2D}\u{E35}\u{E01}\u{E04}\u{E23}\u{E31}\u{E49}\u{E07}",
~~~

## Task 3: Serialize title edits and preserve immutable alias deadlines

**Files:**

- Modify: B/app/Services/Faq/FaqLocalizedSlugs.php, add Synchronize.
- Modify: B/app/Services/Faq/FaqSlugWriter.php, add Update.
- Modify: B/app/Http/Controllers/FaqController.php at Update.
- Create: B/tests/Support/FaqSlugDatabaseProfile.php.
- Create: B/tests/Support/faq-slug-worker.php.
- Test: B/tests/Feature/FaqLocalizedEditingTest.php.
- Test: B/tests/Integration/FaqSlugConcurrencyTest.php.

- **Consumes:** Task 2 initialization, existing validated editor route, and its existing permission middleware.
- **Produces:** FaqSlugWriter::Update(int, array): Faq and transactional per-language retirement/revision advancement.
- **Checkpoint:** Before the intended-engine check, obtain a verified dedicated local test profile: explicit schema opt-in, empty schema named faq_slug_test_*, non-root test-only user, loopback host, nondefault port, and test-only credentials. No such profile is assumed to exist. Do not discover or reuse production/.env/root credentials. If unavailable, stop this checkpoint and report intended-engine verification unavailable; do not mark it passed or provision against an unverified service.

**Actions — production behavior changes:**

**REQUIRED IMPLEMENTATION SKILL:** Use test-driven-development.

- [ ] Write the complete feature test and guarded engine harness below before adding the edit implementation.
- [ ] Run the editing feature file; expect the HTTP PUT succeeds but its current-slug assertion remains the initial version, proving missing edit behavior. Fix setup issues before proceeding.
- [ ] With a valid test-only profile, run the exact engine command below before implementing edit behavior. Expect the real existing controller saves titles but last_revision stays 1 rather than 9 after the first eight workers. Worker/bootstrap errors are not RED.
- [ ] Add the exact Synchronize and Update members and replace the controller Update below.
- [ ] Rerun both commands; expect exit 0, separate/fallback changes, unchanged equivalent-title saves, rollback on a route failure/overflow, and three rounds of eight committed concurrent transitions with no missing revision/history.
- [ ] Rerun creation and the existing FaqSlugTest. Inspect the unchanged permission constructor and confirm the actual HTTP feature test still uses an authorized editor.
- [ ] Record engine/version/isolation and all three round outcomes. This finite stress check is evidence under those conditions, not proof that every concurrency defect is impossible.
- [ ] Refactor with focused checks GREEN.

~~~powershell
& $faq_php vendor/bin/phpunit --do-not-cache-result tests/Feature/FaqLocalizedEditingTest.php
& $faq_php vendor/bin/phpunit --do-not-cache-result --filter ConcurrentLocalizedEditsPreserveEveryCommittedRevision tests/Integration/FaqSlugConcurrencyTest.php
~~~

The profile consumes FAQ_SLUG_TEST_ALLOW_SCHEMA, DATABASE, HOST, PORT, USERNAME, PASSWORD (each full name has prefix FAQ_SLUG_TEST_). Values are operator-supplied test inputs, not missing code placeholders. Verify table/view emptiness and ownership before allowing the harness to create/drop its five named migration objects. Provisioning commands are deliberately not invented before the actual local runtime/profile is inspected. Continue independent fake-HTTP work only with the unavailable engine check explicitly recorded; production readiness remains unverified.

Add this member to FaqLocalizedSlugs:

~~~php
public function Synchronize(Faq $faq): void
{
    if (DB::transactionLevel() < 1) throw new UnavailableFaqUrls('faq.urls.transaction');
    $states = DB::table('faq_localized_slug_states')->where('faq_id', $faq->id)->get();
    if ($states->isEmpty()) {
        $this->Initialize($faq);
        return;
    }
    $routes = DB::table('faq_slug_routes')->where('faq_id', $faq->id)
        ->where('current_marker', 1)->get();
    $this->ValidateState($faq, $states, $routes, false);
    $states = $states->keyBy('locale');
    $routes = $routes->keyBy('locale');
    $now = CarbonImmutable::now('UTC');
    foreach (self::LOCALES as $locale) {
        $base = $this->Base($faq->LocalizedQuestion($locale));
        $state = $states[$locale];
        if ($base === $state->source_base) continue;
        DB::table('faq_slug_routes')->where('id', $routes[$locale]->id)->update([
            'current_marker' => null, 'retired_at' => $this->Stamp($now),
            'expires_at' => $this->Stamp($this->Deadline($now)),
        ]);
        $current = $this->Allocate($faq, $locale, $base, $this->PositiveInteger($state->last_revision));
        DB::table('faq_localized_slug_states')->where('faq_id', $faq->id)->where('locale', $locale)->update([
            'source_base' => $base, 'last_revision' => $current['revision'],
        ]);
    }
}
~~~

Add this member to FaqSlugWriter:

~~~php
public function Update(int $faq_id, array $attributes): Faq
{
    return $this->Commit(function () use ($faq_id, $attributes): Faq {
        $faq = Faq::query()->lockForUpdate()->findOrFail($faq_id);
        $attributes['published_at'] = $attributes['is_published']
            ? ($faq->published_at ?? now()) : null;
        $faq->fill($attributes)->save();
        if (config('faq-urls.enabled', false)) {
            app(FaqLocalizedSlugs::class)->Synchronize($faq);
        }
        return $faq->refresh();
    });
}
~~~

Replace FaqController::Update, retaining its permission registration and ValidateFaq:

~~~php
public function Update(Request $request, Faq $faq)
{
    $validated_faq = $this->ValidateFaq($request, $faq);
    $validated_faq['position'] = $validated_faq['position'] ?? 0;
    app(\App\Services\Faq\FaqSlugWriter::class)->Update((int) $faq->id, $validated_faq);
    return redirect()->route('faqs.index')->with('success', __('content-management.faq.messages.updated'));
}
~~~

Complete editing test:

~~~php
<?php

namespace Tests\Feature;

use App\Services\Faq\FaqSlugWriter;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\FaqSlugFixture;
use Tests\TestCase;

final class FaqLocalizedEditingTest extends TestCase
{
    use RefreshDatabase, FaqSlugFixture;

    protected function setUp(): void
    {
        parent::setUp();
        config(['faq-urls.enabled' => true]);
        CarbonImmutable::setTestNow('2026-10-20T09:00:00Z');
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    #[Test]
    public function EditingUpdatesOnlyChangedLanguageAndPreservesEarlierDeadlines(): void
    {
        $this->actingAs($this->Editor());
        $faq = app(FaqSlugWriter::class)->Create($this->Attributes());
        $legacy_slug = $faq->slug;
        $first_primary = 'primary-question-'.$faq->id.'-1';
        $first_english = 'english-question-'.$faq->id.'-1';
        $this->put('/faqs/'.$faq->id, $this->Attributes(['question' => 'New primary']))->assertRedirect(route('faqs.index'));
        $this->assertSame('new-primary-'.$faq->id.'-2', $this->CurrentSlug($faq, 'th'));
        $this->assertSame($first_english, $this->CurrentSlug($faq, 'en'));
        $first_deadline = DB::table('faq_slug_routes')->where('locale', 'th')->where('slug', $first_primary)->value('expires_at');
        $this->assertSame('2027-10-20 09:00:00.000000', $first_deadline);
        CarbonImmutable::setTestNow('2026-11-20T09:00:00Z');
        $this->put('/faqs/'.$faq->id, $this->Attributes(['question' => 'Primary question', 'question_en' => 'New English']))->assertRedirect();
        $this->assertSame('primary-question-'.$faq->id.'-3', $this->CurrentSlug($faq, 'th'));
        $this->assertSame('new-english-'.$faq->id.'-2', $this->CurrentSlug($faq, 'en'));
        $this->assertSame($first_deadline, DB::table('faq_slug_routes')->where('locale', 'th')->where('slug', $first_primary)->value('expires_at'));
        $before = DB::table('faq_slug_routes')->where('faq_id', $faq->id)->orderBy('id')->get()->toJson();
        $this->put('/faqs/'.$faq->id, $this->Attributes(['question' => 'PRIMARY  question!', 'question_en' => 'New English', 'answer' => 'Revised answer']))->assertRedirect();
        $this->assertSame($before, DB::table('faq_slug_routes')->where('faq_id', $faq->id)->orderBy('id')->get()->toJson());
        $this->assertSame($legacy_slug, $faq->refresh()->slug);
    }

    #[Test]
    public function EmptyEnglishFollowsThaiAndBangkokLeapAnniversaryIsClamped(): void
    {
        $this->actingAs($this->Editor());
        $faq = app(FaqSlugWriter::class)->Create($this->Attributes(['question_en' => null]));
        CarbonImmutable::setTestNow('2028-02-28T18:00:00Z');
        $this->put('/faqs/'.$faq->id, $this->Attributes(['question' => 'Changed primary', 'question_en' => null]))->assertRedirect();
        $this->assertSame('changed-primary-'.$faq->id.'-2', $this->CurrentSlug($faq, 'th'));
        $this->assertSame('changed-primary-'.$faq->id.'-2', $this->CurrentSlug($faq, 'en'));
        $deadlines = DB::table('faq_slug_routes')->where('faq_id', $faq->id)->whereNotNull('revision')
            ->whereNull('current_marker')->pluck('expires_at')->all();
        $this->assertSame(['2029-02-27 18:00:00.000000', '2029-02-27 18:00:00.000000'], $deadlines);
    }

    #[Test]
    public function FailedRouteWriteRollsBackTheEditorialSaveAndHistory(): void
    {
        $this->actingAs($this->Editor());
        $faq = app(FaqSlugWriter::class)->Create($this->Attributes());
        $routes_before = DB::table('faq_slug_routes')->where('faq_id', $faq->id)->orderBy('id')->get()->toJson();
        DB::unprepared("CREATE TRIGGER faq_localized_failure BEFORE INSERT ON faq_slug_routes BEGIN SELECT RAISE(ABORT, 'test-only route failure'); END");
        try {
            $this->put('/faqs/'.$faq->id, $this->Attributes(['question' => 'Must roll back']))->assertStatus(503);
        } finally {
            DB::unprepared('DROP TRIGGER faq_localized_failure');
        }
        $this->assertSame('Primary question', $faq->refresh()->question);
        $this->assertSame($routes_before, DB::table('faq_slug_routes')->where('faq_id', $faq->id)->orderBy('id')->get()->toJson());
    }

    #[Test]
    public function RevisionOverflowDoesNotSaveTheChangedTitle(): void
    {
        $this->actingAs($this->Editor());
        $faq = app(FaqSlugWriter::class)->Create($this->Attributes());
        DB::table('faq_localized_slug_states')->where('faq_id', $faq->id)->where('locale', 'th')
            ->update(['last_revision' => PHP_INT_MAX]);
        DB::table('faq_slug_routes')->where('faq_id', $faq->id)->where('locale', 'th')->where('current_marker', 1)
            ->update(['revision' => PHP_INT_MAX, 'slug' => 'primary-question-'.$faq->id.'-'.PHP_INT_MAX]);
        $this->put('/faqs/'.$faq->id, $this->Attributes(['question' => 'Overflow title']))->assertStatus(503);
        $this->assertSame('Primary question', $faq->refresh()->question);
        $this->assertSame(1, DB::table('faq_slug_routes')->where('faq_id', $faq->id)->where('locale', 'th')->where('current_marker', 1)->count());
    }
}
~~~

Complete isolated profile trait:

~~~php
<?php

namespace Tests\Support;

use Illuminate\Support\Facades\DB;

trait FaqSlugDatabaseProfile
{
    private function ConfigureSlugDatabase(): void
    {
        if (is_file(base_path('bootstrap/cache/config.php'))) {
            throw new \RuntimeException('Cached application configuration is not allowed.');
        }
        if (getenv('FAQ_SLUG_TEST_ALLOW_SCHEMA') !== 'true') {
            throw new \RuntimeException('Dedicated schema opt-in is required.');
        }
        $database_name = getenv('FAQ_SLUG_TEST_DATABASE');
        $host = getenv('FAQ_SLUG_TEST_HOST');
        $port = getenv('FAQ_SLUG_TEST_PORT');
        $username = getenv('FAQ_SLUG_TEST_USERNAME');
        $password = getenv('FAQ_SLUG_TEST_PASSWORD');
        if (!is_string($database_name) || !preg_match('/^faq_slug_test_[a-z0-9_]+$/D', $database_name)
            || !in_array($host, ['127.0.0.1', '::1'], true)
            || !is_string($port) || !preg_match('/^[1-9][0-9]{0,4}$/D', $port)
            || (int) $port > 65535 || in_array((int) $port, [3306, 33060], true)
            || !is_string($username) || trim($username) === '' || strtolower(trim($username)) === 'root'
            || !is_string($password)) {
            throw new \RuntimeException('Invalid isolated test connection profile.');
        }
        config(['database.connections.faq_slug_test' => [
            'driver' => 'mysql', 'host' => $host, 'port' => $port,
            'database' => $database_name, 'username' => $username, 'password' => $password,
            'unix_socket' => '', 'charset' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '', 'prefix_indexes' => true, 'strict' => true, 'engine' => 'InnoDB',
        ], 'faq-urls.enabled' => true]);
        DB::purge('faq_slug_test');
        DB::setDefaultConnection('faq_slug_test');
        \Illuminate\Support\Facades\Schema::clearResolvedInstance('db.schema');
    }
}
~~~

Complete CLI-only worker (it calls the existing controller's application write boundary; it is not an authorization test):

~~~php
<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

if (PHP_SAPI !== 'cli' || is_file(dirname(__DIR__, 2).'/bootstrap/cache/config.php')) exit(1);
putenv('APP_ENV=testing');
putenv('DB_CONNECTION=sqlite');
putenv('DB_DATABASE=:memory:');
putenv('SESSION_DRIVER=array');
putenv('CACHE_DRIVER=array');
require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$worker = new class {
    use \Tests\Support\FaqSlugDatabaseProfile;

    public function Run(): void
    {
        $this->ConfigureSlugDatabase();
        $barrier = getenv('FAQ_SLUG_WORKER_BARRIER');
        $ready = getenv('FAQ_SLUG_WORKER_READY');
        $number = getenv('FAQ_SLUG_WORKER_NUMBER');
        $round = getenv('FAQ_SLUG_WORKER_ROUND');
        $faq_id = getenv('FAQ_SLUG_WORKER_FAQ_ID');
        if (!is_string($barrier) || !is_string($ready)
            || dirname($barrier) !== dirname($ready)
            || realpath(sys_get_temp_dir()) !== realpath(dirname(dirname($barrier)))
            || !preg_match('/^faq-slug-barrier-[a-f0-9]{32}$/D', basename(dirname($barrier)))
            || basename($barrier) !== 'go' || !preg_match('/^ready-[0-7]$/D', basename($ready))
            || !is_string($number) || !preg_match('/^[0-7]$/D', $number)
            || !is_string($round) || !preg_match('/^[1-3]$/D', $round)
            || !is_string($faq_id) || !preg_match('/^[1-9][0-9]*$/D', $faq_id)) {
            throw new \RuntimeException('Invalid worker boundary.');
        }
        $faq = \App\Models\Faq::query()->findOrFail((int) $faq_id);
        file_put_contents($ready, 'ready');
        $deadline = microtime(true) + 20;
        while (!is_file($barrier)) {
            clearstatcache();
            if (microtime(true) >= $deadline) throw new \RuntimeException('Worker barrier timed out.');
            usleep(10000);
        }
        // Invoke the existing application write boundary; authorization is covered by feature tests.
        $request = Request::create('/faqs/'.$faq->id, 'PUT', [
            'category_id' => $faq->category_id, 'question' => 'Worker '.$round.' '.$number,
            'question_en' => 'English question', 'answer' => 'Answer',
            'position' => 0, 'is_published' => true,
        ]);
        app(\App\Http\Controllers\FaqController::class)->Update($request, $faq);
    }
};
$exit_status = 0;
try {
    $worker->Run();
} catch (\Throwable $exception) {
    fwrite(STDERR, 'FAQ slug worker failed ('.get_class($exception).').'.PHP_EOL);
    $exit_status = 1;
} finally {
    if (config('database.connections.faq_slug_test') !== null) DB::disconnect('faq_slug_test');
}
exit($exit_status);
~~~

Complete intended-engine test:

~~~php
<?php

namespace Tests\Integration;

use App\Services\Faq\FaqSlugWriter;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Process\Process;
use Tests\Support\FaqSlugDatabaseProfile;
use Tests\TestCase;

final class FaqSlugConcurrencyTest extends TestCase
{
    use FaqSlugDatabaseProfile;

    public function createApplication(): \Illuminate\Foundation\Application
    {
        if (is_file(dirname(__DIR__, 2).'/bootstrap/cache/config.php')) {
            throw new \RuntimeException('Refusing cached application configuration.');
        }
        return parent::createApplication();
    }

    #[Test]
    public function ConcurrentLocalizedEditsPreserveEveryCommittedRevision(): void
    {
        $original_connection = config('database.default');
        $migrations = [];
        try {
            $this->ConfigureSlugDatabase();
            $schema = DB::connection()->getSchemaBuilder();
            $this->assertSame([], $schema->getTableListing(), 'Dedicated schema must be empty.');
            $this->assertSame([], $schema->getViews(), 'Dedicated schema must have no views.');
            foreach ([
                '2026_05_14_000000_create_faqs_table.php',
                '2026_08_27_000000_add_categories_and_english_to_faqs.php',
                '2026_10_20_000001_add_slug_to_faqs.php',
                '2026_10_20_000002_create_faq_read_analytics_tables.php',
                '2026_10_20_000003_create_faq_localized_slug_tables.php',
            ] as $filename) {
                $migration = require database_path('migrations/'.$filename);
                $migration->up();
                $migrations[] = $migration;
            }
            foreach (DB::select('SHOW TABLE STATUS') as $table) {
                $this->assertSame('InnoDB', $table->Engine);
            }
            $runtime = DB::selectOne('SELECT VERSION() AS version, @@SESSION.sql_mode AS sql_mode');
            $this->assertTrue(str_contains($runtime->sql_mode, 'STRICT_TRANS_TABLES')
                || str_contains($runtime->sql_mode, 'STRICT_ALL_TABLES'));
            $isolation = DB::select("SHOW SESSION VARIABLES WHERE Variable_name IN ('transaction_isolation', 'tx_isolation')");
            fwrite(STDERR, json_encode(['operation' => 'faq.urls.test_environment',
                'database_version' => $runtime->version, 'isolation' => collect($isolation)->pluck('Value')->first(),
                'rounds' => 3, 'workers_per_round' => 8], JSON_THROW_ON_ERROR).PHP_EOL);
            $faq = app(FaqSlugWriter::class)->Create([
                'category_id' => DB::table('faq_categories')->value('id'),
                'question' => 'Primary question', 'question_en' => 'English question',
                'answer' => 'Answer', 'position' => 0, 'is_published' => true,
            ]);
            $english_before = DB::table('faq_slug_routes')->where('faq_id', $faq->id)
                ->where('locale', 'en')->where('current_marker', 1)->value('slug');
            for ($round = 1; $round <= 3; $round++) {
                $this->RunWorkers((int) $faq->id, $round);
                $this->assertSame(1 + 8 * $round, (int) DB::table('faq_localized_slug_states')
                    ->where('faq_id', $faq->id)->where('locale', 'th')->value('last_revision'));
                $this->assertSame(8 * $round, DB::table('faq_slug_routes')->where('faq_id', $faq->id)
                    ->where('locale', 'th')->whereNotNull('revision')->whereNull('current_marker')->count());
                $this->assertSame(2, DB::table('faq_slug_routes')->where('faq_id', $faq->id)
                    ->where('current_marker', 1)->count());
                $this->assertSame($english_before, DB::table('faq_slug_routes')->where('faq_id', $faq->id)
                    ->where('locale', 'en')->where('current_marker', 1)->value('slug'));
            }
        } catch (\Throwable $exception) {
            if ($exception instanceof \PHPUnit\Framework\AssertionFailedError) throw $exception;
            $this->fail('Isolated slug concurrency check failed ('.get_class($exception).'); raw details withheld.');
        } finally {
            try {
                foreach (array_reverse($migrations) as $migration) $migration->down();
            } finally {
                if (config('database.connections.faq_slug_test') !== null) DB::disconnect('faq_slug_test');
                DB::setDefaultConnection($original_connection);
                \Illuminate\Support\Facades\Schema::clearResolvedInstance('db.schema');
            }
        }
    }

    private function RunWorkers(int $faq_id, int $round): void
    {
        $directory = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR
            .'faq-slug-barrier-'.bin2hex(random_bytes(16));
        $this->assertTrue(mkdir($directory, 0700));
        $barrier = $directory.DIRECTORY_SEPARATOR.'go';
        $processes = [];
        $ready_paths = [];
        $environment = ['APP_ENV' => 'testing', 'DB_CONNECTION' => 'sqlite',
            'DB_DATABASE' => ':memory:', 'SESSION_DRIVER' => 'array', 'CACHE_DRIVER' => 'array'];
        foreach (['ALLOW_SCHEMA', 'DATABASE', 'HOST', 'PORT', 'USERNAME', 'PASSWORD'] as $suffix) {
            $environment['FAQ_SLUG_TEST_'.$suffix] = getenv('FAQ_SLUG_TEST_'.$suffix);
        }
        try {
            for ($number = 0; $number < 8; $number++) {
                $ready = $directory.DIRECTORY_SEPARATOR.'ready-'.$number;
                $ready_paths[] = $ready;
                $process = new Process([PHP_BINARY, 'tests/Support/faq-slug-worker.php'], base_path(),
                    array_merge($environment, ['FAQ_SLUG_WORKER_FAQ_ID' => (string) $faq_id,
                        'FAQ_SLUG_WORKER_NUMBER' => (string) $number, 'FAQ_SLUG_WORKER_ROUND' => (string) $round,
                        'FAQ_SLUG_WORKER_BARRIER' => $barrier, 'FAQ_SLUG_WORKER_READY' => $ready]), null, 30);
                $processes[] = $process;
                $process->start();
            }
            $deadline = microtime(true) + 20;
            while (count(array_filter($ready_paths, 'is_file')) !== 8) {
                clearstatcache();
                foreach ($processes as $process) {
                    $process->checkTimeout();
                    if (!$process->isRunning() && !$process->isSuccessful()) {
                        $this->fail('A worker failed before the barrier; raw output withheld.');
                    }
                }
                $this->assertLessThan($deadline, microtime(true), 'Worker startup timed out.');
                usleep(10000);
            }
            file_put_contents($barrier, 'go');
            foreach ($processes as $process) {
                $this->assertSame(0, $process->wait(), 'Worker failed; raw output withheld.');
            }
        } finally {
            foreach ($processes as $process) if ($process->isRunning()) $process->stop(1);
            foreach (array_merge($ready_paths, [$barrier]) as $path) if (is_file($path)) unlink($path);
            $this->assertSame(realpath(sys_get_temp_dir()), realpath(dirname($directory)));
            $this->assertMatchesRegularExpression('/^faq-slug-barrier-[a-f0-9]{32}$/D', basename($directory));
            rmdir($directory);
        }
    }
}
~~~

## Task 4: Backfill safely and delete expired aliases daily

**Files:**

- Modify: B/app/Console/Commands/BackfillFaqSlugs.php at handle/description.
- Create: B/app/Console/Commands/PruneFaqSlugHistory.php.
- Modify: B/app/Console/Kernel.php at schedule.
- Modify: B/tests/Feature/FaqSlugTest.php at BackfillPreservesContentTimestampsAndExistingSlugs.
- Test: B/tests/Feature/FaqSlugMaintenanceTest.php.

- **Consumes:** Task 2 Initialize and Task 3 revision state; existing command auto-discovery.
- **Produces:** Restart-safe metadata backfill; bounded, idempotent expiry pruning; daily 02:30 Bangkok registration.
- **Checkpoint:** Isolated tests only. Server cron/Scheduler execution is not established by registration and remains a deployment check.

**Actions — production behavior changes:**

**REQUIRED IMPLEMENTATION SKILL:** Use test-driven-development.

- [ ] Write the maintenance test below. Run its file; expect missing localized-state assertions and the absent owned CLI registration assertion to fail. Do not accept a command-not-found exception as the only RED evidence.
- [ ] Replace the backfill command and add the exact pruning command below.
- [ ] Add the scheduler entry shown below without changing existing affiliate schedules.
- [ ] Update the one legacy test expectation shown below for the explicitly changed missing-slug CLI initialization format; existing nonempty legacy values still must be unchanged.
- [ ] Run maintenance, FaqSlugTest, creation, and editing files; expect exit 0, byte-equivalent existing content/timestamps, rerun no-ops, nonzero partial-state failure, independent deadlines, bounded deletion, and retained counters/current URLs.
- [ ] Refactor with those checks GREEN. Never run these commands on the user's configured database during implementation.

~~~php
<?php

namespace Tests\Feature;

use App\Models\Faq;
use App\Services\Faq\FaqSlugWriter;
use Carbon\CarbonImmutable;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\FaqSlugFixture;
use Tests\TestCase;

final class FaqSlugMaintenanceTest extends TestCase
{
    use RefreshDatabase, FaqSlugFixture;

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    #[Test]
    public function BackfillPreservesExistingDataAndDoesNotRenewAliases(): void
    {
        CarbonImmutable::setTestNow('2026-10-20T09:00:00Z');
        $faq = Faq::forceCreate($this->Attributes(['slug' => 'existing-link', 'is_published' => false]));
        $before = (array) DB::table('faqs')->find($faq->id);
        $this->artisan('faq:backfill-slugs')->assertExitCode(0);
        $this->assertSame(2, DB::table('faq_localized_slug_states')->where('faq_id', $faq->id)->count());
        $this->assertSame($before, (array) DB::table('faqs')->find($faq->id));
        $routes_before = DB::table('faq_slug_routes')->where('faq_id', $faq->id)->orderBy('id')->get()->toJson();
        CarbonImmutable::setTestNow('2026-11-20T09:00:00Z');
        $this->artisan('faq:backfill-slugs')->assertExitCode(0);
        $this->assertSame($routes_before, DB::table('faq_slug_routes')->where('faq_id', $faq->id)->orderBy('id')->get()->toJson());
        DB::table('faq_localized_slug_states')->where('faq_id', $faq->id)->where('locale', 'en')->delete();
        $this->artisan('faq:backfill-slugs')->assertExitCode(1);
        $this->assertSame($routes_before, DB::table('faq_slug_routes')->where('faq_id', $faq->id)->orderBy('id')->get()->toJson());
    }

    #[Test]
    public function PruningRemovesOnlyExpiredAliasesAndKeepsRevisionState(): void
    {
        $this->assertArrayHasKey('faq:prune-slug-history', Artisan::all());
        config(['faq-urls.enabled' => true]);
        CarbonImmutable::setTestNow('2026-10-20T09:00:00Z');
        $faq = app(FaqSlugWriter::class)->Create($this->Attributes());
        app(FaqSlugWriter::class)->Update($faq->id, $this->Attributes(['question' => 'Changed primary']));
        $states_before = DB::table('faq_localized_slug_states')->where('faq_id', $faq->id)->orderBy('locale')->get()->toJson();
        $current_before = DB::table('faq_slug_routes')->where('faq_id', $faq->id)->where('current_marker', 1)->orderBy('locale')->get()->toJson();
        $content_before = (array) DB::table('faqs')->find($faq->id);
        CarbonImmutable::setTestNow('2027-10-20T08:59:59.999999Z');
        $this->artisan('faq:prune-slug-history')->assertExitCode(0);
        $this->assertGreaterThan(0, DB::table('faq_slug_routes')->where('faq_id', $faq->id)->whereNull('current_marker')->count());
        CarbonImmutable::setTestNow('2027-10-20T09:00:00.000000Z');
        $this->artisan('faq:prune-slug-history')->assertExitCode(0);
        $this->artisan('faq:prune-slug-history')->assertExitCode(0);
        $this->assertSame(0, DB::table('faq_slug_routes')->where('faq_id', $faq->id)->whereNull('current_marker')->count());
        $this->assertSame($states_before, DB::table('faq_localized_slug_states')->where('faq_id', $faq->id)->orderBy('locale')->get()->toJson());
        $this->assertSame($current_before, DB::table('faq_slug_routes')->where('faq_id', $faq->id)->where('current_marker', 1)->orderBy('locale')->get()->toJson());
        $this->assertSame($content_before, (array) DB::table('faqs')->find($faq->id));
        app(FaqSlugWriter::class)->Update($faq->id, $this->Attributes());
        $this->assertSame('primary-question-'.$faq->id.'-3', $this->CurrentSlug($faq, 'th'));
        $this->assertSame(0, DB::table('faq_daily_reads')->where('faq_reference_id', $faq->id)->count());
    }

    #[Test]
    public function CleanupIsRegisteredAtTheApprovedTimeAndHonorsActivation(): void
    {
        $events = collect(app(Schedule::class)->events())->filter(
            fn ($event): bool => str_contains((string) $event->command, 'faq:prune-slug-history'));
        $this->assertCount(1, $events);
        $event = $events->first();
        $this->assertSame('30 2 * * *', $event->expression);
        $this->assertSame('Asia/Bangkok', $event->timezone);
        config(['faq-urls.enabled' => false]);
        $this->assertFalse($event->filtersPass(app()));
    }
}
~~~

Complete BackfillFaqSlugs replacement:

~~~php
<?php

namespace App\Console\Commands;

use App\Models\Faq;
use App\Services\Faq\FaqLocalizedSlugs;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class BackfillFaqSlugs extends Command
{
    protected $signature = 'faq:backfill-slugs';
    protected $description = 'Initialize missing localized FAQ URLs without changing content or timestamps';

    public function handle(FaqLocalizedSlugs $localized_slugs): int
    {
        $initialized_count = 0;
        $assigned_count = 0;
        try {
            foreach (Faq::query()->orderBy('id')->lazyById(200) as $faq_row) {
                $result = DB::transaction(function () use ($faq_row, $localized_slugs): array {
                    $faq = Faq::query()->lockForUpdate()->findOrFail($faq_row->id);
                    $was_missing = !is_string($faq->slug) || $faq->slug === '';
                    $initialized = $localized_slugs->Initialize($faq);
                    return ['initialized' => $initialized, 'assigned' => $was_missing];
                }, 3);
                if ($result['initialized']) $initialized_count++;
                if ($result['assigned']) $assigned_count++;
            }
        } catch (\Throwable $exception) {
            Log::error('faq.urls.backfill_failed', ['error_code' => 'FAQ_CONTENT_UNAVAILABLE',
                'exception_class' => get_class($exception), 'initialized_count' => $initialized_count]);
            $this->error('FAQ URL initialization failed. Inspect the feature diagnostic before retrying.');
            return self::FAILURE;
        }
        $this->info('Assigned FAQ slugs: '.$assigned_count);
        $this->info('Initialized FAQ localizations: '.$initialized_count);
        return self::SUCCESS;
    }
}
~~~

Complete pruning command:

~~~php
<?php

namespace App\Console\Commands;

use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class PruneFaqSlugHistory extends Command
{
    protected $signature = 'faq:prune-slug-history';
    protected $description = 'Delete expired FAQ URL aliases without changing current URLs or analytics';

    public function handle(): int
    {
        $deleted_count = 0;
        $cutoff = CarbonImmutable::now('UTC')->format('Y-m-d H:i:s.u');
        try {
            do {
                $route_ids = DB::table('faq_slug_routes')->whereNull('current_marker')
                    ->where('expires_at', '<=', $cutoff)->orderBy('expires_at')->orderBy('id')
                    ->limit(500)->pluck('id');
                if ($route_ids->isEmpty()) break;
                $deleted_count += DB::table('faq_slug_routes')->whereIn('id', $route_ids)
                    ->whereNull('current_marker')->where('expires_at', '<=', $cutoff)->delete();
            } while (true);
        } catch (\Throwable $exception) {
            Log::error('faq.urls.prune_failed', ['error_code' => 'FAQ_CONTENT_UNAVAILABLE',
                'exception_class' => get_class($exception), 'deleted_count' => $deleted_count]);
            $this->error('FAQ URL history cleanup failed. Retry after resolving the diagnostic.');
            return self::FAILURE;
        }
        $this->info('Deleted expired FAQ URL aliases: '.$deleted_count);
        return self::SUCCESS;
    }
}
~~~

Append inside Console Kernel::schedule:

~~~php
$schedule->command('faq:prune-slug-history')
    ->dailyAt('02:30')->timezone('Asia/Bangkok')->withoutOverlapping()
    ->when(fn (): bool => (bool) config('faq-urls.enabled', false));
~~~

In the existing missing-slug backfill test, replace only its fallback expectation with:

~~~php
$this->assertSame('faq-'.$faq->id.'-1', $after['slug']);
~~~

## Task 5: Add localized metadata without breaking the legacy API

**Files:**

- Modify: B/app/Services/Faq/FaqPublicContent.php at Serialize.
- Modify: B/app/Http/Controllers/FaqController.php at ApiIndex and ApiShow.
- Test: B/tests/Feature/FaqLocalizedProjectionTest.php.

- **Consumes:** LocalizedSlugs::Read, existing publication query and stable faqs.slug.
- **Produces:** The legacy record plus additive localized_slugs while active, with batched metadata reads.
- **Checkpoint:** None for in-memory HTTP tests. Keep the public feature disabled until Task 6 passes.

**Actions — production behavior changes:**

**REQUIRED IMPLEMENTATION SKILL:** Use test-driven-development.

- [ ] Write the test below and run its file. Expect a missing localized_slugs assertion on the existing real API, not an invented response.
- [ ] Replace Serialize and the two controller members below; preserve Query, root array, existing fields/order, permissions, and locale fallback.
- [ ] Rerun projection, FaqApiTest and FaqDetailApiTest; expect exit 0, matching list/detail objects, stable legacy identifier, nullable rollout metadata, and a bounded number of route-table queries.
- [ ] Refactor with the same checks GREEN.

~~~php
<?php

namespace Tests\Feature;

use App\Models\Faq;
use App\Services\Faq\FaqSlugWriter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\FaqSlugFixture;
use Tests\TestCase;

final class FaqLocalizedProjectionTest extends TestCase
{
    use RefreshDatabase, FaqSlugFixture;

    #[Test]
    public function LegacyEndpointsKeepTheirStableIdentifierAndBatchLocalizedMetadata(): void
    {
        config(['faq-urls.enabled' => true]);
        $faq = app(FaqSlugWriter::class)->Create($this->Attributes());
        $legacy = $faq->slug;
        app(FaqSlugWriter::class)->Update($faq->id, $this->Attributes(['question_en' => 'Changed English']));
        for ($number = 0; $number < 4; $number++) {
            app(FaqSlugWriter::class)->Create($this->Attributes(['question' => 'Other '.$number]));
        }
        DB::enableQueryLog();
        DB::flushQueryLog();
        $records = $this->getJson('/api/faqs?locale=en')->assertOk()->json();
        $queries = DB::getQueryLog();
        DB::disableQueryLog();
        $record = collect($records)->firstWhere('id', $faq->id);
        $this->assertSame($legacy, $record['slug']);
        $this->assertSame('changed-english-'.$faq->id.'-2', $record['localized_slugs']['en']);
        $this->assertLessThanOrEqual(2, count(array_filter($queries,
            fn (array $query): bool => str_contains(strtolower($query['query']), 'faq_slug_routes'))));
        $detail = $this->getJson('/api/faqs/'.$legacy.'?locale=en')->assertOk()->json();
        $this->assertSame($record, $detail);
        config(['faq-urls.enabled' => false]);
        $this->getJson('/api/faqs/'.$legacy.'?locale=en')->assertOk()->assertJsonMissingPath('localized_slugs');
    }

    #[Test]
    public function UninitializedLegacyRowsRemainReadableUntilPublicActivation(): void
    {
        $faq = Faq::forceCreate($this->Attributes(['slug' => 'uninitialized-legacy']));
        config(['faq-urls.enabled' => true]);
        $this->getJson('/api/faqs/'.$faq->slug)->assertOk()
            ->assertJsonPath('localized_slugs.th', null)->assertJsonPath('localized_slugs.en', null);
        $this->getJson('/api/faqs/'.$faq->slug.'?locale[]=en')->assertOk()
            ->assertJsonPath('question', 'Primary question');
    }
}
~~~

Replace FaqPublicContent::Serialize; keep its existing imports/Query:

~~~php
public function Serialize(Faq $faq, string $locale, ?array $localized_slugs = null): array
{
    $answer = $faq->LocalizedAnswer($locale);
    $record = ['id' => $faq->id, 'slug' => $faq->slug,
        'question' => $faq->LocalizedQuestion($locale), 'answer' => $answer,
        'answer_html' => Str::markdown($answer), 'position' => $faq->position,
        'is_published' => $faq->is_published, 'published_at' => $faq->published_at,
        'created_at' => $faq->created_at, 'updated_at' => $faq->updated_at,
        'category' => ['id' => $faq->category->id, 'slug' => $faq->category->slug,
            'name' => $faq->category->LocalizedName($locale),
            'description' => $faq->category->LocalizedDescription($locale),
            'position' => $faq->category->position]];
    if (config('faq-urls.enabled', false)) {
        if ($localized_slugs === null) throw new UnavailableFaqUrls('faq.urls.projection');
        $record['localized_slugs'] = $localized_slugs;
    }
    return $record;
}
~~~

Replace the two API controller members:

~~~php
public function ApiIndex(Request $request)
{
    $locale = in_array($request->query('locale'), ['th', 'en'], true) ? $request->query('locale') : 'th';
    $content = app(\App\Services\Faq\FaqPublicContent::class);
    if (!config('faq-urls.enabled', false)) {
        return response()->json($content->Query()->get()->map(
            fn (Faq $faq): array => $content->Serialize($faq, $locale)));
    }
    try {
        $records = \Illuminate\Support\Facades\DB::transaction(function () use ($content, $locale): array {
            $faqs = $content->Query()->sharedLock()->get();
            $slugs = app(\App\Services\Faq\FaqLocalizedSlugs::class)->Read($faqs, false);
            return $faqs->map(fn (Faq $faq): array => $content->Serialize($faq, $locale, $slugs[$faq->id]))->all();
        }, 3);
        return response()->json($records);
    } catch (\PDOException $exception) {
        throw new \App\Services\Faq\UnavailableFaqUrls('faq.urls.list');
    }
}

public function ApiShow(Request $request, string $slug)
{
    $locale = in_array($request->query('locale'), ['th', 'en'], true) ? $request->query('locale') : 'th';
    $content = app(\App\Services\Faq\FaqPublicContent::class);
    if (!config('faq-urls.enabled', false)) {
        $faq = $content->Query()->where('faqs.slug', $slug)->firstOrFail();
        return response()->json($content->Serialize($faq, $locale));
    }
    try {
        $record = \Illuminate\Support\Facades\DB::transaction(function () use ($content, $locale, $slug): array {
            $faq = $content->Query()->where('faqs.slug', $slug)->sharedLock()->firstOrFail();
            $slugs = app(\App\Services\Faq\FaqLocalizedSlugs::class)->Read(collect([$faq]), false);
            return $content->Serialize($faq, $locale, $slugs[$faq->id]);
        }, 3);
        return response()->json($record);
    } catch (\PDOException $exception) {
        throw new \App\Services\Faq\UnavailableFaqUrls('faq.urls.legacy_detail');
    }
}
~~~

## Task 6: Resolve localized pages and aliases at a consistent read boundary

**Files:**

- Create: B/app/Services/Faq/FaqPageResolver.php.
- Create: B/app/Http/Controllers/FaqPageController.php.
- Create: B/app/Http/Middleware/PreventFaqPageCaching.php.
- Modify: B/routes/api.php next to existing FAQ routes.
- Modify: B/app/Http/Kernel.php, append the focused cache middleware to the global middleware array.
- Test: B/tests/Feature/FaqLocalizedApiTest.php.

- **Consumes:** Tasks 2-5 current/alias registry, validated public projection, and shared ownership locks.
- **Produces:** The two new API routes and exact envelope/error/cache contracts consumed by plan 0006.
- **Checkpoint:** None for isolated tests. No new API endpoint follows redirects or returns an externally supplied destination URL.

**Actions — production behavior changes:**

**REQUIRED IMPLEMENTATION SKILL:** Use test-driven-development.

- [ ] Write the complete API test below. Run its file; expect the new owned endpoint's current-resolution assertion to receive 404 rather than 200 because the route is absent.
- [ ] Add the resolver, thin controller, focused caching middleware, route definitions and middleware entry below.
- [ ] Rerun the API file; expect exit 0 for current/alias/identity envelopes, direct-to-latest resolution, exact one-year boundaries without pruning, same-slug cross-language expiry, and hidden/deleted/incomplete/disabled cases.
- [ ] Run all focused FAQ PHP tests. Confirm legacy list/detail still pass and no GET inserts analytics.
- [ ] Refactor with focused tests GREEN.

The resolver first finds the owner, then locks the joined FAQ/category before re-reading the route. Its metadata/category reads are locking current reads, avoiding a stale MVCC snapshot established by the locator lookup. The writer locks the same FAQ first. This is the consistent-read mechanism; do not replace it with unrelated independent lookups.

~~~php
<?php

namespace Tests\Feature;

use App\Models\Faq;
use App\Services\Faq\FaqSlugWriter;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\FaqSlugFixture;
use Tests\TestCase;

final class FaqLocalizedApiTest extends TestCase
{
    use RefreshDatabase, FaqSlugFixture;

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    #[Test]
    public function CurrentAndRepeatedAliasesResolveToTheLatestSameLanguage(): void
    {
        config(['faq-urls.enabled' => true]);
        CarbonImmutable::setTestNow('2026-10-20T09:00:00Z');
        $faq = app(FaqSlugWriter::class)->Create($this->Attributes());
        $original = 'primary-question-'.$faq->id.'-1';
        app(FaqSlugWriter::class)->Update($faq->id, $this->Attributes(['question' => 'Second primary']));
        app(FaqSlugWriter::class)->Update($faq->id, $this->Attributes(['question' => 'Third primary']));
        foreach ([$original, 'second-primary-'.$faq->id.'-2'] as $old_slug) {
            $response = $this->getJson('/api/faq-pages/resolve/'.$old_slug.'?locale=th')->assertOk()
                ->assertJsonPath('match_type', 'alias')
                ->assertJsonPath('faq.id', $faq->id)
                ->assertJsonPath('faq.localized_slugs.th', 'third-primary-'.$faq->id.'-3')
                ->assertJsonPath('alias_expires_at', '2027-10-20T09:00:00.000000Z');
            $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        }
        $this->getJson('/api/faq-pages/resolve/third-primary-'.$faq->id.'-3')->assertOk()
            ->assertJsonPath('match_type', 'current')->assertJsonPath('alias_expires_at', null);
        $this->getJson('/api/faq-pages/'.$faq->id.'?locale=en')->assertOk()
            ->assertJsonPath('question', 'English question');
        $this->getJson('/api/faq-pages/resolve/'.$original.'?locale=en')->assertNotFound()
            ->assertJsonPath('error_code', 'FAQ_NOT_FOUND');
        CarbonImmutable::setTestNow('2027-10-20T08:59:59.999999Z');
        $this->getJson('/api/faq-pages/resolve/'.$original)->assertOk();
        CarbonImmutable::setTestNow('2027-10-20T09:00:00Z');
        $this->getJson('/api/faq-pages/resolve/'.$original)->assertNotFound();
        $this->getJson('/api/faq-pages/resolve/'.$original.'?locale=en')->assertNotFound();
    }

    #[Test]
    public function ExpiredEnglishFallbackDoesNotResolveThroughTheThaiCurrentSlug(): void
    {
        config(['faq-urls.enabled' => true]);
        CarbonImmutable::setTestNow('2026-10-20T09:00:00Z');
        $faq = app(FaqSlugWriter::class)->Create($this->Attributes(['question_en' => null]));
        $shared_slug = 'primary-question-'.$faq->id.'-1';
        app(FaqSlugWriter::class)->Update($faq->id, $this->Attributes(['question_en' => 'Actual translation']));
        CarbonImmutable::setTestNow('2027-10-20T09:00:00Z');
        $this->getJson('/api/faq-pages/resolve/'.$shared_slug.'?locale=en')->assertNotFound();
        $this->getJson('/api/faq-pages/resolve/'.$shared_slug)->assertOk()
            ->assertJsonPath('match_type', 'current');
    }

    #[Test]
    public function HiddenDeletedDisabledAndIncompleteStatesKeepDistinctFailures(): void
    {
        config(['faq-urls.enabled' => true]);
        $faq = app(FaqSlugWriter::class)->Create($this->Attributes());
        $slug = $this->CurrentSlug($faq, 'th');
        $faq->category->update(['is_published' => false]);
        $this->getJson('/api/faq-pages/resolve/'.$slug)->assertNotFound();
        $this->getJson('/api/faq-pages/'.$faq->id)->assertNotFound();
        $faq->category->update(['is_published' => true]);
        $faq->update(['is_published' => false]);
        $this->getJson('/api/faq-pages/resolve/'.$slug)->assertNotFound();
        $faq->update(['is_published' => true]);
        DB::table('faq_localized_slug_states')->where('faq_id', $faq->id)->where('locale', 'en')->delete();
        $this->getJson('/api/faq-pages/'.$faq->id)->assertStatus(503)
            ->assertJsonPath('error_code', 'FAQ_CONTENT_UNAVAILABLE');
        config(['faq-urls.enabled' => false]);
        $this->getJson('/api/faq-pages/'.$faq->id)->assertStatus(503);
        config(['faq-urls.enabled' => true]);
        $faq->delete();
        $this->getJson('/api/faq-pages/resolve/'.$slug)->assertNotFound();
        $this->getJson('/api/faq-pages/resolve/invalid%2Fsegment')->assertNotFound();
        $this->getJson('/api/faq-pages/0')->assertNotFound();
        $this->getJson('/api/faq-pages/999999999999999999999999999999')->assertNotFound();
        $this->assertSame(0, DB::table('faq_daily_reads')->count());
    }
}
~~~

Complete resolver:

~~~php
<?php

namespace App\Services\Faq;

use App\Models\Faq;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

final class FaqPageResolver
{
    public function __construct(private readonly FaqPublicContent $content,
        private readonly FaqLocalizedSlugs $localized_slugs)
    {
    }

    public function ById(int $faq_id, string $locale): array
    {
        return DB::transaction(function () use ($faq_id, $locale): array {
            $faq = $this->content->Query()->where('faqs.id', $faq_id)->without('category')->sharedLock()->first();
            abort_if($faq === null, 404);
            return $this->Record($faq, $locale);
        }, 3);
    }

    private function Record(Faq $faq, string $locale): array
    {
        $faq->setRelation('category', \App\Models\FaqCategory::query()->whereKey($faq->category_id)->sharedLock()->firstOrFail());
        $slugs = $this->localized_slugs->Read(collect([$faq]));
        return $this->content->Serialize($faq, $locale, $slugs[$faq->id]);
    }

    public function Resolve(string $slug, string $locale): array
    {
        abort_unless(mb_strlen($slug) <= 255
            && preg_match('/^[\p{L}\p{M}\p{N}]+(?:-[\p{L}\p{M}\p{N}]+)*$/uD', $slug), 404);
        return DB::transaction(function () use ($slug, $locale): array {
            $locator = DB::table('faq_slug_routes')->where('locale', $locale)->where('slug', $slug)->first();
            abort_if($locator === null || $locator->slug !== $slug, 404);
            // Acquire the same ownership lock as writers before reading authoritative route state.
            $faq = $this->content->Query()->where('faqs.id', $locator->faq_id)->without('category')->sharedLock()->first();
            abort_if($faq === null, 404);
            $route = DB::table('faq_slug_routes')->where('faq_id', $faq->id)
                ->where('locale', $locale)->where('slug', $slug)->sharedLock()->first();
            abort_if($route === null || $route->slug !== $slug, 404);
            $is_current = $route->current_marker !== null;
            $deadline = null;
            if ($is_current) {
                if ((int) $route->current_marker !== 1 || $route->retired_at !== null || $route->expires_at !== null) {
                    throw new UnavailableFaqUrls('faq.urls.current_route');
                }
            } else {
                if (!is_string($route->retired_at) || !is_string($route->expires_at)) {
                    throw new UnavailableFaqUrls('faq.urls.alias_times');
                }
                $deadline = CarbonImmutable::parse($route->expires_at, 'UTC');
                abort_if(CarbonImmutable::now('UTC')->greaterThanOrEqualTo($deadline), 404);
            }
            $record = $this->Record($faq, $locale);
            $canonical_slug = $record['localized_slugs'][$locale];
            if (($is_current && $canonical_slug !== $slug) || (!$is_current && $canonical_slug === $slug)) {
                throw new UnavailableFaqUrls('faq.urls.resolution_identity');
            }
            return ['requested_slug' => $slug, 'locale' => $locale,
                'match_type' => $is_current ? 'current' : 'alias',
                'alias_expires_at' => $deadline?->toISOString(), 'faq' => $record];
        }, 3);
    }
}
~~~

Complete controller:

~~~php
<?php

namespace App\Http\Controllers;

use App\Services\Faq\FaqPageResolver;
use App\Services\Faq\UnavailableFaqUrls;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

final class FaqPageController extends Controller
{
    public function __construct(private readonly FaqPageResolver $resolver)
    {
    }

    public function ById(Request $request, string $faq_id): JsonResponse
    {
        return $this->Respond(function () use ($request, $faq_id): array {
            abort_unless(preg_match('/^[1-9][0-9]*$/D', $faq_id)
                && (string) (int) $faq_id === $faq_id, 404);
            return $this->resolver->ById((int) $faq_id, $this->Locale($request));
        });
    }

    public function Resolve(Request $request, string $slug): JsonResponse
    {
        return $this->Respond(fn (): array => $this->resolver->Resolve($slug, $this->Locale($request)));
    }

    private function Locale(Request $request): string
    {
        return in_array($request->query('locale'), ['th', 'en'], true) ? $request->query('locale') : 'th';
    }

    private function Respond(\Closure $read): JsonResponse
    {
        if (!config('faq-urls.enabled', false)) throw new UnavailableFaqUrls('faq.urls.disabled');
        try {
            return response()->json($read())->header('Cache-Control', 'no-store');
        } catch (HttpExceptionInterface $exception) {
            throw $exception;
        } catch (UnavailableFaqUrls $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            throw new UnavailableFaqUrls('faq.urls.resolve');
        }
    }
}
~~~

Complete cache middleware (only the new API namespace is affected):

~~~php
<?php

namespace App\Http\Middleware;

use App\Services\Faq\UnavailableFaqUrls;
use Closure;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

final class PreventFaqPageCaching
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->is('api/faq-pages/*')) return $next($request);
        try {
            $response = $next($request);
        } catch (HttpExceptionInterface $exception) {
            $response = app(ExceptionHandler::class)->render($request, $exception);
        } catch (\Throwable $exception) {
            $safe_exception = $exception instanceof UnavailableFaqUrls
                ? $exception : new UnavailableFaqUrls('faq.urls.http');
            $safe_exception->report();
            $response = $safe_exception->render($request);
        }
        if ($response->getStatusCode() === 404) {
            $response = response()->json(['error_code' => 'FAQ_NOT_FOUND', 'message' => 'FAQ not found.'], 404);
        } elseif ($response->getStatusCode() >= 500) {
            $response = response()->json(['error_code' => 'FAQ_CONTENT_UNAVAILABLE',
                'message' => 'FAQ information is temporarily unavailable.'], 503);
        }
        $response->headers->set('Cache-Control', 'no-store');
        return $response;
    }
}
~~~

Add the two routes near existing FAQ routes, retaining those routes unchanged:

~~~php
Route::get('/faq-pages/resolve/{slug}', [\App\Http\Controllers\FaqPageController::class, 'Resolve']);
Route::get('/faq-pages/{faq_id}', [\App\Http\Controllers\FaqPageController::class, 'ById'])->whereNumber('faq_id');
~~~

Append inside Http Kernel's existing global middleware array, not its priority or authorization arrays:

~~~php
\App\Http\Middleware\PreventFaqPageCaching::class,
~~~

## Task 7: Record verification and publish the cross-plan readiness evidence

**Files:**

- Create: B/docs/plans/0005-0006-faq-localized-urls-execution-record.md.
- Test: B/tests/Feature/FaqLocalizedSchemaTest.php.
- Test: B/tests/Feature/FaqLocalizedCreationTest.php.
- Test: B/tests/Feature/FaqLocalizedEditingTest.php.
- Test: B/tests/Feature/FaqSlugMaintenanceTest.php.
- Test: B/tests/Feature/FaqLocalizedProjectionTest.php.
- Test: B/tests/Feature/FaqLocalizedApiTest.php.
- Test: B/tests/Integration/FaqSlugConcurrencyTest.php.

- **Consumes:** Completed Tasks 1-6 and actual fresh test outputs.
- **Produces:** An English execution record with RED/GREEN provenance, code revision, focused/full results, engine check status, and the exact API contract available to plan 0006.
- **Checkpoint:** Stop readiness claims for any new/unexplained failure, unsafe test configuration, or missing intended-engine evidence. Preserve the accepted unrelated baseline as a disclosed limitation, never a passing suite. No production execution or commit is included.

**Actions — nonbehavioral artifact and verification:**

- [ ] Recheck branch/status, test database isolation, and absence of configuration cache; preserve approved documents/unrelated changes.
- [ ] Run the focused command from the baseline section and the guarded engine command with the dedicated profile. Record exact exit codes, engine conditions, failures, warnings/skips and current revision.
- [ ] Run the complete backend PHPUnit suite. Compare every nonpassing test identity/outcome with the historical inventory; report new, changed, and intermittent outcomes rather than only totals.
- [ ] Write the English record with shared sections: Approved scope and execution method; Source/revisions; Isolation; Per-task RED/GREEN; Backend functional results; Intended-engine results; Public functional/browser results; Full-suite limitations; Cross-plan contract review; Independent final review; Deployment prerequisites still unverified; Recovery constraints. Public results and final review remain explicitly pending until plan 0006 completes; if that plan created the shared record first, preserve its evidence and update these sections.
- [ ] Use verification-before-completion and you-should-know before reporting. Independently inspect the final diff and confirm only approved feature files changed.
- [ ] After the public plan's checks, perform the Native method's independent final review of the combined change. Any reviewer findings return to the owning task and affected tests.

Deployment is a later, explicitly authorized checkpoint. The established server paths are /var/www/html/eiMaths and /var/www/html/eimaths_th_new from the user's logs, but process management, production credentials, Scheduler operation, backups, and service versions have not been verified here. Follow design section 8's migration/drain/backfill/backend-first/public-last order. Retain resolver compatibility after exposure; never drop collected metadata or reset counters as a routine rollback.

## Requirement trace and handoff

| Requirement | Owner |
| --- | --- |
| Inert flags, additive schema, uniqueness/FKs | Task 1 |
| Per-language source/normalization, fallback, ID/revision suffix, finite collision allocation | Task 2 |
| Atomic edits, unchanged normalized base, independent deadlines, rollback, serialization | Task 3 |
| Legacy-preserving restart-safe backfill, one-year physical deletion, scheduler | Task 4 |
| Old-client compatibility and batched current metadata | Task 5 |
| Same-language direct resolution, publication, 404/503, no resurrection, safe error/cache policy | Task 6 |
| Fresh full/engine evidence, unchanged analytics contract, disclosed limitations | Tasks 3, 4, 6, 7 |

This plan and companion 0006 require explicit user approval after presentation before execution. The preserved method is Native. Approval of the design or split is not approval of these completed plans. No workspace preparation or implementation starts before both plan approvals.
