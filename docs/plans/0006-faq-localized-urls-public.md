# Public Localized FAQ URLs Implementation Plan

> **REQUIRED EXECUTION SKILL:** After approval of this plan and its companion, use git-worktrees, then executing-plans with the preserved Native execution method.

- **Goal:** FAQ links use independent Thai/English current slugs, old links redirect directly to the latest same-language question, and language switching keeps the same FAQ on detail and accordion pages.
- **Architecture:** The public application validates backend-owned current/alias metadata, renders existing sanitized content, constructs redirects from its configured origin, and supplies paired SEO/navigation URLs. It does not allocate revisions, store aliases, or alter read deduplication.
- **Tech Stack:** Laravel 12.58.0, PHP 8.2.12, PHPUnit 11.5.55, existing Blade/vanilla JavaScript, Playwright 1.63.0, Node 24.21.0, Chrome channel.
- **Source:** [Approved design 0004](../specs/0004-faq-localized-slug-history-design.md), SHA256 594B41F825B68929467B0FC569EFF257907B94FF634B004349FAA41CE7400479. The user approved that written specification and the backend/public plan split on 2026-10-20.
- **Companion:** [Backend localized slugs plan 0005](0005-faq-localized-slugs-backend.md).
- **Execution method:** Native, preserved from the explicit selection in 0003-0004-faq-execution-record.md. The current agent implements; an independent final review follows the integrated changes. Preserve the user's main-branch preference and unrelated work when applying git-worktrees.
- **Status:** Proposed plan awaiting approval. Code and command blocks describe future execution; this plan does not execute application code, tests, migrations, production changes, commits, or activation.

## Global Constraints

Copied from the approved specification:

> With the public flag disabled, retain existing FAQ behavior.

> Keep one open answer and history.replaceState behavior without a document navigation.

> Closing, searching, clearing, or changing category restores the exact original list address and associated language-link state.

> Preserve existing accessibility, no-JavaScript heading links, and the absence of a separate "Open question page" control.

> No GET, redirect, sitemap, or language-switch operation records a read; existing qualifying browser events still deduplicate by unchanged FAQ ID/session.

> No new tests have been run or implementation correctness claimed by this specification.

Production deployment is a later authorized operation and is not included in either implementation plan. Backend plan 0005 owns atomic bilingual renaming, Thai fallback when the English title is empty, monotonic ID/revision suffixes, one-calendar-year aliases measured independently from retirement, no deadline renewal, and daily physical pruning. The public application treats exact upstream 404 as missing and malformed/unavailable/unexpected-redirect content as 503. It never rescues a missing localized URL through another locale or the legacy endpoint.

Keep content sanitization, configured HTTP timeouts/no-follow behavior, publication checks, FAQ IDs, session/read statistics, search/category interaction, and unrelated metadata/navigation unchanged. Use existing framework-required naming; new named functions/methods use PascalCase, local identifiers and serialized fields use snake_case, and ENV uses UPPER_CASE. Document prose is English. No manual slug editor, extra page-opening control, unrelated visual redesign, analytics retention change, external service, commit/push, or production execution is included.

Every production task uses test-driven-development and writing-good-tests.md. Copy its implementation only after a behavioral assertion has failed for the intended missing behavior. Class-loading, fixture, environment, or network setup errors are not RED. Fix such setup first. Code syntax checked while writing a plan is not runtime test evidence.

## Verified baseline and commands

B/ means C:\Users\user\Desktop\eimaths\eiMaths; P/ means sibling eimaths_th_new. These are documentation prefixes, not new directories. Both plans live in B/docs/plans. B HEAD is 893a312f987b2a5c019e1cf5c96a04bbcc0d3c44; P HEAD is 266e96624ac03a1ee74ec42e4c09628df636919a.

Verified relevant symbols: FaqContentClient::Index/Show/Fetch/Prepare/Unavailable, FaqController::Show, the language.switch closure, SitemapController::CreateFaqEntries/FaqModifiedDate/CreateAbsoluteUrl, the layout's canonical/hreflang/desktop/mobile anchors, FAQ article attributes, InitializeFaqSearch/SetAnswerUrl, both browser fixture helpers, phpunit.xml, and bootstrap Exceptions::respond. All existing files below were read and new target paths checked absent. Composer autoload-dev maps the Tests namespace to tests/. Installed PHP/Node/test-runner versions and CLI discovery on existing tests were checked; Chrome exists at C:\Program Files\Google\Chrome\Application\chrome.exe. Configuration cache files were absent during planning.

Run P commands from its repository root in PowerShell:

~~~powershell
$faq_php = 'C:\xampp\php\php.exe'
$env:PHP_BINARY = $faq_php
$env:PLAYWRIGHT_CHANNEL = 'chrome'
& $faq_php vendor/bin/phpunit --do-not-cache-result tests/Feature/FaqLocalizedDefaultTest.php
node node_modules/@playwright/test/cli.js test tests/browser/faq-url.spec.mjs --list
~~~

The new PHP test path exists only after Task 1. Existing-file discovery and runner syntax were verified without running application tests during planning. At execution, verify testing isolation in phpunit.xml (SQLite :memory:, array session/cache, forced flag false) and absence of cached configuration before any test. Do not print .env values.

Historical results in 0003-0004-faq-execution-record.md report P at 254 tests / 1760 assertions and 77 browser cases passing; they are not a fresh baseline. Record new execution results and failures. Backend historical unrelated failures are described by plan 0005 and do not authorize unrelated repairs.

## Cross-plan contract and order

This plan can be developed against HTTP fakes independently of B. Production exposure requires backend plan 0005 schema, writer, backfill, and API readiness first. Do not enable the public flag against uninitialized metadata.

- Each app has its own FAQ_LOCALIZED_URLS_ENABLED=false, independent of analytics.
- Existing GET /api/faqs?locale=th|en returns the existing array/public fields and stable legacy slug, plus localized_slugs with both current slugs when enabled.
- New GET /api/faq-pages/{faq_id}?locale=th|en returns a complete published record with that exact positive ID and both current slugs.
- New GET /api/faq-pages/resolve/{slug}?locale=th|en returns requested_slug, locale, match_type=current|alias, alias_expires_at, and faq. A current match equals the selected current slug with null expiry; an alias differs and has a future UTC ISO expiry.
- Both generated current slugs encode the returned FAQ ID and positive revision. Backend owns exact registry lookup and expiry; no public fallback to faqs.slug is allowed in active mode.
- Missing/expired/nonpublic is 404; incomplete state/unavailability is 503. New API errors have error_code/message. API responses/errors and public alias redirects carry no-store. The adapter never follows an upstream HTTP redirect.
- Thai URLs omit lang; English URLs include only ?lang=en. Use the public configured canonical origin, not Host or an API URL. Alias redirects are 301; identity language switches are temporary redirects.
- Task 2 owns list preparation, Task 3 consumes the resolver, Task 4 consumes identity lookup, and Task 5 consumes paired current URLs. Test fakes use the exact shared wire contract.
- After both plans, independently review the integrated changes once. No real server deployment or scheduler availability is established by fixture tests.

## Task 1: Keep localized URLs inert until explicit activation

**Files:**

- Create: P/config/faq-urls.php.
- Modify: P/.env.example, append the nonsecret flag once.
- Modify: P/phpunit.xml, inside php, force the new flag false.
- Test: P/tests/Feature/FaqLocalizedDefaultTest.php.

- **Consumes:** Existing Laravel configuration and PHPUnit environment isolation.
- **Produces:** config('faq-urls.enabled') default false, independent of tracking.
- **Checkpoint:** None after plan approval; do not edit a real .env or activate either application.

**Actions — production behavior changes:**

**REQUIRED IMPLEMENTATION SKILL:** Use test-driven-development.

- [ ] Create the complete test below. Run the Task 1 PHP command above; expect assertFalse to fail because config('faq-urls.enabled') is null before the configuration exists.
- [ ] Create the complete configuration below. Append FAQ_LOCALIZED_URLS_ENABLED=false to .env.example with the English comment shown. Add the exact XML line inside phpunit.xml's php element.
- [ ] Rerun Task 1; expect exit 0 and default false even if a developer environment has opted in.
- [ ] Run the existing FaqDetailPageTest.php and FaqContentBoundaryTest.php with PHPUnit; expect their old behavior unchanged.
- [ ] Refactor only with these checks GREEN.

~~~php
<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class FaqLocalizedDefaultTest extends TestCase
{
    #[Test]
    public function LocalizedUrlsDefaultToDisabled(): void
    {
        $this->assertFalse(config('faq-urls.enabled'));
    }
}
~~~

Complete P/config/faq-urls.php:

~~~php
<?php

return [
    'enabled' => (bool) env('FAQ_LOCALIZED_URLS_ENABLED', false),
];
~~~

Append once to P/.env.example:

~~~dotenv
# Enable only after backend localized FAQ metadata and APIs are ready.
FAQ_LOCALIZED_URLS_ENABLED=false
~~~

Add inside P/phpunit.xml's php element:

~~~xml
<env name="FAQ_LOCALIZED_URLS_ENABLED" value="false" force="true"/>
~~~

## Task 2: Validate paired current URLs at the content boundary

**Files:**

- Create: P/tests/Support/FaqLocalizedFixture.php.
- Modify: P/app/Services/Faq/FaqContentClient.php at Prepare; add ValidSlug, ValidLocalizedSlug, and ApplyLocalizedUrls.
- Test: P/tests/Feature/FaqLocalizedClientTest.php.

- **Consumes:** Existing Fetch/Prepare sanitization and the backend list/public record shape.
- **Produces:** Validated localized_detail_urls and language_switch_urls on every active record; detail_url selects the requested locale.
- **Checkpoint:** None. Missing or inconsistent localized metadata must fail closed as 503, not fall back to legacy URLs.

**Actions — production behavior changes:**

**REQUIRED IMPLEMENTATION SKILL:** Use test-driven-development.

- [ ] Create the complete fixture and test below, without adapter changes. Run:
~~~powershell
& $faq_php vendor/bin/phpunit --do-not-cache-result tests/Feature/FaqLocalizedClientTest.php
~~~

- [ ] Expect ListUsesValidatedCurrentLanguagePaths to fail on the old legacy detail_url; do not count missing fixtures/classes as RED.
- [ ] Add exactly the three methods below to FaqContentClient. Replace only the end of Prepare, starting at its existing detail_url assignment, with the supplied ending. Preserve Fetch, Show, answer sanitization, and existing shape/date/category validation.
- [ ] Rerun the command; expect exit 0, validated ID/revision suffixes for both languages, uninitialized/mismatched metadata rejected, and flag-off legacy tolerance preserved.
- [ ] Run FaqContentBoundaryTest.php and FaqDetailPageTest.php; expect exit 0. Refactor only with checks GREEN.

Complete fixture:

~~~php
<?php

namespace Tests\Support;

final class FaqLocalizedFixture
{
    public static function Record(array $overrides = []): array
    {
        return array_replace_recursive(FaqFixture::Record(), [
            'question' => 'English question',
            'localized_slugs' => ['th' => 'primary-question-11-1', 'en' => 'english-question-11-1'],
        ], $overrides);
    }

    public static function Resolution(string $requested_slug, string $locale = 'en',
        string $match_type = 'alias', array $record_overrides = []): array
    {
        return ['requested_slug' => $requested_slug, 'locale' => $locale, 'match_type' => $match_type,
            'alias_expires_at' => $match_type === 'alias' ? '2027-10-20T09:00:00.000000Z' : null,
            'faq' => self::Record(array_replace(['question' => $locale === 'th' ? 'Primary question' : 'English question'], $record_overrides))];
    }
}
~~~

Complete test:

~~~php
<?php

namespace Tests\Feature;

use App\Services\Faq\FaqContentClient;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Tests\Support\FaqFixture;
use Tests\Support\FaqLocalizedFixture;
use Tests\TestCase;

final class FaqLocalizedClientTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['faq-urls.enabled' => true,
            'services.eimaths_backend.base_url' => 'https://backend.example.test']);
        CarbonImmutable::setTestNow('2026-10-20T09:00:00Z');
        Http::preventStrayRequests();
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    #[Test]
    public function ListUsesValidatedCurrentLanguagePaths(): void
    {
        Http::fake(['https://backend.example.test/api/faqs*' => Http::response([FaqLocalizedFixture::Record()])]);
        $client = app(FaqContentClient::class);
        $this->assertSame('/faq/primary-question-11-1', $client->Index('th')[0]['detail_url']);
        $this->assertSame('/faq/english-question-11-1?lang=en', $client->Index('en')[0]['detail_url']);
        $this->assertSame('/language/en?faq_id=11', $client->Index('th')[0]['language_switch_urls']['en']);
    }

    #[Test]
    public function AnUninitializedOrWrongIdentityNeverFallsBackToLegacy(): void
    {
        $invalid_records = [
            FaqLocalizedFixture::Record(['localized_slugs' => ['th' => null]]),
            FaqLocalizedFixture::Record(['localized_slugs' => ['en' => 'english-question-12-1']]),
            FaqLocalizedFixture::Record(['localized_slugs' => ['th' => 'https-evil-test-11-0']]),
        ];
        $responses = Http::sequence();
        foreach ($invalid_records as $record) $responses->push([$record]);
        Http::fake(['https://backend.example.test/api/faqs*' => $responses]);
        foreach ($invalid_records as $record) {
            $this->ExpectStatus(fn () => app(FaqContentClient::class)->Index('th'), 503);
        }
        Http::assertSentCount(count($invalid_records));
    }

    #[Test]
    public function DisabledListKeepsItsExistingPermissiveLegacyBoundary(): void
    {
        config(['faq-urls.enabled' => false]);
        Http::fake(['https://backend.example.test/api/faqs*' => Http::response([FaqFixture::Record(['slug' => null])])]);
        $this->assertNull(app(FaqContentClient::class)->Index('en')[0]['detail_url']);
    }

    private function ExpectStatus(\Closure $read, int $status): void
    {
        try {
            $read();
            $this->fail('Expected the adapter to reject invalid upstream content.');
        } catch (HttpExceptionInterface $exception) {
            $this->assertSame($status, $exception->getStatusCode());
        }
    }
}
~~~

Add these private adapter methods:

~~~php
private function ValidSlug(mixed $slug): bool
{
    return is_string($slug) && mb_strlen($slug) <= 255
        && preg_match('/^[\p{L}\p{M}\p{N}]+(?:-[\p{L}\p{M}\p{N}]+)*$/uD', $slug) === 1;
}

private function ValidLocalizedSlug(mixed $slug, int $faq_id): bool
{
    return $this->ValidSlug($slug)
        && preg_match('/-([1-9][0-9]*)-([1-9][0-9]*)$/D', $slug, $parts) === 1
        && $parts[1] === (string) $faq_id
        && filter_var($parts[2], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) !== false;
}

private function ApplyLocalizedUrls(array $record, string $locale): array
{
    if ($record['id'] === null || !$this->ValidSlug($record['slug'])
        || ($record['is_published'] ?? null) !== true
        || !is_array($record['localized_slugs'] ?? null)) {
        $this->Unavailable('faq.localized.shape');
    }
    $record['localized_detail_urls'] = [];
    $record['language_switch_urls'] = [];
    foreach (['th', 'en'] as $target_locale) {
        $slug = $record['localized_slugs'][$target_locale] ?? null;
        if (!$this->ValidLocalizedSlug($slug, $record['id'])) {
            $this->Unavailable('faq.localized.identity');
        }
        $record['localized_detail_urls'][$target_locale] = '/faq/'.rawurlencode($slug)
            .($target_locale === 'en' ? '?lang=en' : '');
        $record['language_switch_urls'][$target_locale] = route('language.switch',
            ['locale' => $target_locale, 'faq_id' => $record['id']], false);
    }
    $record['detail_url'] = $record['localized_detail_urls'][$locale];
    return $record;
}
~~~

Replace only Prepare's existing final detail_url assignment and return with:

~~~php
$record['detail_url'] = $record['id'] !== null && $record['slug'] !== null
    ? '/faq/'.rawurlencode($record['slug']).($locale === 'en' ? '?lang=en' : '') : null;
if (config('faq-urls.enabled', false)) {
    $record = $this->ApplyLocalizedUrls($record, $locale);
}
return $record;
~~~

## Task 3: Resolve current and retired URLs without legacy fallback

**Files:**

- Modify: P/app/Services/Faq/FaqContentClient.php; add ResolveSlug and ValidateExpiry.
- Modify: P/app/Http/Controllers/FaqController.php at Show.
- Modify: P/bootstrap/app.php inside the existing withExceptions callback.
- Test: P/tests/Feature/FaqLocalizedUrlTest.php.

- **Consumes:** Task 2 prepared record; exact new resolver envelope; existing Fetch with HTTP redirects disabled.
- **Produces:** Current SSR detail, direct configured-origin 301 for aliases, clean error semantics and no-store error/alias responses.
- **Checkpoint:** None for fake HTTP. Do not call production APIs or activate localized routing.

**Actions — production behavior changes:**

**REQUIRED IMPLEMENTATION SKILL:** Use test-driven-development.

- [ ] Write the complete test below. Run:
~~~powershell
& $faq_php vendor/bin/phpunit --do-not-cache-result --filter OldUrlRedirectsDirectlyWithinTheConfiguredOrigin tests/Feature/FaqLocalizedUrlTest.php
~~~

- [ ] Expect status 200 instead of 301 from the still-legacy Show path. The fake includes the old endpoint deliberately so the failure is behavioral rather than stray network.
- [ ] Add the two adapter methods, replace only FaqController::Show, and replace the empty Exceptions callback body with the exact response callback below. Keep the existing callback signature, middleware registration, routes, and index/grouping logic.
- [ ] Run the complete new file, then FaqDetailPageTest.php and FaqContentBoundaryTest.php; expect exit 0. Tests freeze UTC time, reject expiry equality/invalid calendar date/contradictory identity, preserve upstream 404, and map 302/503 to safe 503 without Location.
- [ ] Confirm GET paths make no POST and redirect URLs exclude unrelated query parameters. Refactor only with these checks GREEN.

~~~php
<?php

namespace Tests\Feature;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\FaqFixture;
use Tests\Support\FaqLocalizedFixture;
use Tests\TestCase;

final class FaqLocalizedUrlTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['faq-urls.enabled' => true, 'seo.canonical_origin' => 'https://canonical.example.test',
            'services.eimaths_backend.base_url' => 'https://backend.example.test']);
        CarbonImmutable::setTestNow('2026-10-20T09:00:00Z');
        Http::preventStrayRequests();
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    #[Test]
    public function OldUrlRedirectsDirectlyWithinTheConfiguredOrigin(): void
    {
        Http::fake([
            'https://backend.example.test/api/faq-pages/resolve/*' =>
                Http::response(FaqLocalizedFixture::Resolution('how-does-cpa-work')),
            'https://backend.example.test/api/faqs/*' => Http::response(FaqFixture::Record()),
        ]);
        $response = $this->get('/faq/how-does-cpa-work?lang=en&utm_source=old')
            ->assertStatus(301)->assertRedirect('https://canonical.example.test/faq/english-question-11-1?lang=en');
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        Http::assertSentCount(1);
        Http::assertNotSent(fn ($request): bool => $request->method() === 'POST');
    }

    #[Test]
    public function CurrentDetailRendersAndMissingLanguageKeepsThai(): void
    {
        Http::fake([
            'https://backend.example.test/api/faq-pages/resolve/primary-question-11-1*' =>
                Http::response(FaqLocalizedFixture::Resolution('primary-question-11-1', 'th', 'current')),
            'https://backend.example.test/api/faqs/*' => Http::response(FaqFixture::Record()),
        ]);
        $this->withSession(['locale' => 'en'])->get('/faq/primary-question-11-1?lang[]=en')
            ->assertOk()->assertSee('<html lang="th">', false)->assertSee('Learn with', false);
    }

    #[Test]
    public function MalformedRequestSlugIsNotContentAndDoesNotCallUpstream(): void
    {
        $response = $this->get('/faq/not%20a%20slug')->assertNotFound();
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        Http::assertNothingSent();
    }

    #[Test]
    public function ResolutionRejectsContradictoryIdentityExpiryAndUnexpectedRedirects(): void
    {
        $base = FaqLocalizedFixture::Resolution('how-does-cpa-work');
        $invalid = [
            array_replace($base, ['requested_slug' => 'another-slug']),
            array_replace($base, ['locale' => 'th']),
            array_replace($base, ['match_type' => 'unknown']),
            array_replace($base, ['alias_expires_at' => null]),
            array_replace($base, ['alias_expires_at' => '2026-10-20T09:00:00.000000Z']),
            array_replace($base, ['alias_expires_at' => '2027-02-30T09:00:00.000000Z']),
            array_replace_recursive($base, ['faq' => ['localized_slugs' => ['en' => 'wrong-owner-12-1']]]),
        ];
        $responses = Http::sequence();
        foreach ($invalid as $payload) $responses->push($payload);
        foreach ([404, 503, 302] as $status) {
            $responses->push([], $status, $status === 302 ? ['Location' => 'https://untrusted.example.test/'] : []);
        }
        Http::fake([
            'https://backend.example.test/api/faq-pages/resolve/*' => $responses,
            'https://backend.example.test/api/faqs/*' => Http::response(FaqFixture::Record()),
        ]);
        foreach ($invalid as $payload) {
            $response = $this->get('/faq/how-does-cpa-work?lang=en')->assertStatus(503);
            $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
            $response->assertHeaderMissing('Location');
        }
        foreach ([404, 503, 302] as $status) {
            $response = $this->get('/faq/how-does-cpa-work?lang=en')->assertStatus($status === 404 ? 404 : 503);
            $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
            $response->assertHeaderMissing('Location');
        }
    }
}
~~~

Add the following adapter methods:

~~~php
public function ResolveSlug(string $slug, string $locale): array
{
    if (!$this->ValidSlug($slug)) abort(404);
    $resolution = $this->Fetch('/api/faq-pages/resolve/'.rawurlencode($slug), $locale, true);
    if (!is_array($resolution) || ($resolution['requested_slug'] ?? null) !== $slug
        || ($resolution['locale'] ?? null) !== $locale
        || !in_array($resolution['match_type'] ?? null, ['current', 'alias'], true)
        || !array_key_exists('alias_expires_at', $resolution)) {
        $this->Unavailable('faq.resolve.shape');
    }
    $record = $this->Prepare($resolution['faq'] ?? null, $locale);
    $current_slug = $record['localized_slugs'][$locale];
    if ($resolution['match_type'] === 'current') {
        if ($slug !== $current_slug || $resolution['alias_expires_at'] !== null) {
            $this->Unavailable('faq.resolve.current');
        }
    } else {
        if ($slug === $current_slug) $this->Unavailable('faq.resolve.alias');
        $this->ValidateExpiry($resolution['alias_expires_at']);
    }
    $resolution['faq'] = $record;
    return $resolution;
}

private function ValidateExpiry(mixed $expiry): void
{
    if (!is_string($expiry)
        || preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}(?:\.[0-9]{1,6})?Z$/D', $expiry) !== 1) {
        $this->Unavailable('faq.resolve.expiry');
    }
    try {
        $instant = \Carbon\CarbonImmutable::parse($expiry, 'UTC');
    } catch (\Throwable $exception) {
        $this->Unavailable('faq.resolve.expiry');
    }
    if ($instant->format('Y-m-d\TH:i:s') !== substr($expiry, 0, 19)
        || $instant->lessThanOrEqualTo(\Carbon\CarbonImmutable::now('UTC'))) {
        $this->Unavailable('faq.resolve.expiry');
    }
}
~~~

Replace Show:

~~~php
public function Show(\Illuminate\Http\Request $request, string $slug)
{
    $locale = in_array($request->query('lang'), ['th', 'en'], true) ? $request->query('lang') : 'th';
    app()->setLocale($locale);
    $client = app(\App\Services\Faq\FaqContentClient::class);
    $origin = rtrim((string) config('seo.canonical_origin'), '/');
    $faq_canonical_urls = null;
    $faq_language_switch_urls = null;
    if (config('faq-urls.enabled', false)) {
        $resolution = $client->ResolveSlug($slug, $locale);
        $faq = $resolution['faq'];
        if ($resolution['match_type'] === 'alias') {
            return redirect()->to($origin.$faq['detail_url'], 301)->header('Cache-Control', 'no-store');
        }
        $faq_canonical_urls = [
            'th' => $origin.$faq['localized_detail_urls']['th'],
            'en' => $origin.$faq['localized_detail_urls']['en'],
        ];
        $faq_language_switch_urls = $faq['language_switch_urls'];
        $canonical_page_url = $origin.'/faq/'.rawurlencode($faq['localized_slugs'][$locale]);
    } else {
        $faq = $client->Show($slug, $locale);
        $canonical_page_url = $origin.'/faq/'.rawurlencode($faq['slug']);
    }
    $page_title = $faq['question'].' | eiMaths Thailand';
    $page_description = mb_substr($faq['plain_answer'], 0, 160, 'UTF-8');
    return view('faq.detail', compact('faq', 'page_title', 'page_description',
        'canonical_page_url', 'faq_canonical_urls', 'faq_language_switch_urls'));
}
~~~

Replace the empty body of bootstrap/app.php's existing withExceptions callback with:

~~~php
$exceptions->respond(function (\Symfony\Component\HttpFoundation\Response $response) {
    $request = request();
    if (config('faq-urls.enabled', false) && $request->isMethod('GET')
        && ($request->is('faq/*')
            || ($request->routeIs('language.switch') && $request->query->has('faq_id')))) {
        $response->headers->set('Cache-Control', 'no-store');
    }
    return $response;
});
~~~

## Task 4: Switch language by the same FAQ identity

**Files:**

- Modify: P/app/Services/Faq/FaqContentClient.php; add ById.
- Modify: P/routes/web.php at the existing language.switch closure only.
- Test: P/tests/Feature/FaqLocalizedLanguageTest.php.

- **Consumes:** Task 2 validation; GET /api/faq-pages/{faq_id}; existing Session language handling.
- **Produces:** Fresh same-FAQ temporary canonical redirect with stored target locale; unchanged non-FAQ navigation.
- **Checkpoint:** None. Explicit malformed, missing, hidden, or unavailable FAQ context must not fall back to the referer.

**Actions — production behavior changes:**

**REQUIRED IMPLEMENTATION SKILL:** Use test-driven-development.

- [ ] Create the complete test below. Run the new file. Expect the current route to redirect to the referer's old slug with a changed language query, failing the expected same-ID/current-English destination.
- [ ] Add ById and replace only the language.switch route closure with the supplied code. Preserve all other routes and imports.
- [ ] Rerun:
~~~powershell
& $faq_php vendor/bin/phpunit --do-not-cache-result tests/Feature/FaqLocalizedLanguageTest.php
& $faq_php vendor/bin/phpunit --do-not-cache-result tests/Feature/FaqLocalizedUrlTest.php
~~~

- [ ] Expect exit 0; scalar/array/overflow IDs rejected, exact returned identity enforced, 404/503 preserved, unrelated navigation unchanged, and no GET-induced read POST.
- [ ] Refactor only with checks GREEN.

~~~php
<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\FaqLocalizedFixture;
use Tests\TestCase;

final class FaqLocalizedLanguageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['faq-urls.enabled' => true, 'seo.canonical_origin' => 'https://canonical.example.test',
            'services.eimaths_backend.base_url' => 'https://backend.example.test']);
        Http::preventStrayRequests();
    }

    #[Test]
    public function FaqContextChangesToTheSameQuestionAndSetsSessionLanguage(): void
    {
        Http::fake(['https://backend.example.test/api/faq-pages/11*' =>
            Http::response(FaqLocalizedFixture::Record())]);
        $this->from('/faq/primary-question-11-1')->get('/language/en?faq_id=11')
            ->assertRedirect('https://canonical.example.test/faq/english-question-11-1?lang=en')
            ->assertSessionHas('locale', 'en');
        $this->from('/faq/english-question-11-1?lang=en')->get('/language/th?faq_id=11')
            ->assertRedirect('https://canonical.example.test/faq/primary-question-11-1')
            ->assertSessionHas('locale', 'th');
        Http::assertNotSent(fn ($request): bool => $request->method() === 'POST');
    }

    #[Test]
    public function InvalidHiddenAndUnavailableIdentitiesDoNotFallBackToReferer(): void
    {
        foreach (['0', '-1', '999999999999999999999999999999', 'text', ''] as $faq_id) {
            $this->from('/faq')->get('/language/en?faq_id='.$faq_id)->assertNotFound();
        }
        $this->get('/language/en?faq_id[]=11')->assertNotFound();
        Http::fake(['https://backend.example.test/api/faq-pages/11*' => Http::sequence()
            ->push([], 404)->push([], 503)
            ->push(FaqLocalizedFixture::Record(['id' => 12,
                'localized_slugs' => ['th' => 'primary-question-12-1', 'en' => 'english-question-12-1']]))]);
        foreach ([404, 503] as $status) {
            $response = $this->from('/faq')->get('/language/en?faq_id=11')->assertStatus($status);
            $response->assertHeaderMissing('Location');
        }
        $this->get('/language/en?faq_id=11')->assertStatus(503);
    }

    #[Test]
    public function UnrelatedLanguageNavigationKeepsTheExistingRoute(): void
    {
        $this->from('/about')->get('/language/en')->assertRedirect('/about?lang=en')
            ->assertSessionHas('locale', 'en');
        Http::assertNothingSent();
    }
}
~~~

Add to the adapter:

~~~php
public function ById(int $faq_id, string $locale): array
{
    $record = $this->Prepare($this->Fetch('/api/faq-pages/'.$faq_id, $locale, true), $locale);
    if ($record['id'] !== $faq_id || ($record['is_published'] ?? null) !== true) {
        $this->Unavailable('faq.identity.shape');
    }
    return $record;
}
~~~

Replace only the language.switch definition:

~~~php
Route::get('/language/{locale}', function (\Illuminate\Http\Request $request, $locale) {
    if (in_array($locale, ['th', 'en'], true)) {
        if (config('faq-urls.enabled', false) && $request->query->has('faq_id')) {
            $faq_id = $request->query('faq_id');
            abort_unless(is_string($faq_id) && preg_match('/^[1-9][0-9]*$/D', $faq_id)
                && (string) (int) $faq_id === $faq_id, 404);
            $faq = app(\App\Services\Faq\FaqContentClient::class)->ById((int) $faq_id, $locale);
            Session::put('locale', $locale);
            return redirect()->to(rtrim((string) config('seo.canonical_origin'), '/').$faq['detail_url'])
                ->header('Cache-Control', 'no-store');
        }
        Session::put('locale', $locale);
        return redirect()->to(url()->query(url()->previous(), ['lang' => $locale]));
    }
    return redirect()->back();
})->name('language.switch');
~~~

## Task 5: Render paired FAQ metadata, detail language links, and sitemap entries

**Files:**

- Modify: P/resources/views/layouts/app.blade.php at the head's seo_current_url setup, the three hreflang tags, and four language anchor opening tags.
- Modify: P/app/Http/Controllers/SitemapController.php at CreateFaqEntries.
- Test: P/tests/Feature/FaqLocalizedSeoTest.php.

- **Consumes:** Task 3 faq_canonical_urls/faq_language_switch_urls and Task 2 localized_detail_urls; existing timestamp validation.
- **Produces:** Thai/English current canonical pairs, Thai x-default, matching Open Graph URL, both desktop/mobile same-ID links, and two current sitemap entries per FAQ.
- **Checkpoint:** None. Preserve all unrelated layout, static/blog sitemap entries, actual dates, and safe FAQ dependency failure behavior.

**Actions — production behavior changes:**

**REQUIRED IMPLEMENTATION SKILL:** Use test-driven-development.

- [ ] Create the complete test below and run it. Expect the old head to point the Thai alternate at the English slug and the sitemap to contain only the legacy URL. The current detail already resolves under Task 3, so this RED owns metadata/navigation.
- [ ] Immediately after the existing seo_current_url assignment in the layout's head @php block, insert the exact setup below.
- [ ] Replace only the three alternate tags with the supplied tags.
- [ ] At both desktop and mobile language controls, replace the opening anchor line for Thai and English with the respective line below. Keep all remaining attributes, classes, text, children, closing tags, dropdown behavior, and accessibility as they are. Each opening line intentionally continues onto the existing attributes.
- [ ] Replace only CreateFaqEntries with the supplied method.
- [ ] Run:
~~~powershell
& $faq_php vendor/bin/phpunit --do-not-cache-result tests/Feature/FaqLocalizedSeoTest.php
& $faq_php vendor/bin/phpunit --do-not-cache-result tests/Feature/CanonicalMetadataTest.php
& $faq_php vendor/bin/phpunit --do-not-cache-result tests/Feature/FaqSitemapTest.php
~~~

- [ ] Expect exit 0: paired clean canonical/alternate/OG URLs, two language controls per target language, encoded Unicode, actual updated_at dates, no aliases, 503 for incomplete/date-invalid FAQ metadata, and existing non-FAQ metadata unchanged.
- [ ] Refactor only with checks GREEN.

~~~php
<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\FaqFixture;
use Tests\Support\FaqLocalizedFixture;
use Tests\TestCase;

final class FaqLocalizedSeoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['faq-urls.enabled' => true, 'seo.canonical_origin' => 'https://canonical.example.test',
            'services.eimaths_backend.base_url' => 'https://backend.example.test']);
        Http::preventStrayRequests();
    }

    #[Test]
    public function DetailHasItsOwnCanonicalAndPairedAlternatesAndLanguageLinks(): void
    {
        Http::fake([
            'https://backend.example.test/api/faq-pages/resolve/*' => Http::response(
                FaqLocalizedFixture::Resolution('english-question-11-1', 'en', 'current')),
            'https://backend.example.test/api/faqs/*' => Http::response(FaqFixture::Record(['slug' => 'english-question-11-1'])),
        ]);
        $html = $this->get('/faq/english-question-11-1?lang=en&utm_source=ignored')->assertOk()->getContent();
        $document = new \DOMDocument();
        @$document->loadHTML('<?xml encoding="UTF-8">'.$html);
        $xpath = new \DOMXPath($document);
        $this->assertSame('https://canonical.example.test/faq/english-question-11-1?lang=en',
            $xpath->evaluate('string(//link[@rel="canonical"]/@href)'));
        $this->assertSame('https://canonical.example.test/faq/primary-question-11-1',
            $xpath->evaluate('string(//link[@hreflang="th"]/@href)'));
        $this->assertSame('https://canonical.example.test/faq/english-question-11-1?lang=en',
            $xpath->evaluate('string(//link[@hreflang="en"]/@href)'));
        $this->assertSame('https://canonical.example.test/faq/primary-question-11-1',
            $xpath->evaluate('string(//link[@hreflang="x-default"]/@href)'));
        $this->assertSame(2, $xpath->query('//a[@data-faq-language="th" and @href="/language/th?faq_id=11"]')->length);
        $this->assertSame(2, $xpath->query('//a[@data-faq-language="en" and @href="/language/en?faq_id=11"]')->length);
        $this->assertSame('https://canonical.example.test/faq/english-question-11-1?lang=en',
            $xpath->evaluate('string(//meta[@property="og:url"]/@content)'));
    }

    #[Test]
    public function SitemapContainsBothCurrentUrlsWithRealDatesAndNoAliases(): void
    {
        Http::fake([
            'https://backend.example.test/api/faqs*' => Http::response([FaqLocalizedFixture::Record()]),
            'https://backend.example.test/api/blogs*' => Http::response([]),
        ]);
        $xml = $this->get('/sitemap.xml')->assertOk()->getContent();
        $document = simplexml_load_string($xml);
        $document->registerXPathNamespace('s', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        foreach ([
            'https://canonical.example.test/faq/primary-question-11-1',
            'https://canonical.example.test/faq/english-question-11-1?lang=en',
        ] as $location) {
            $nodes = $document->xpath('//s:url[s:loc="'.$location.'"]');
            $this->assertCount(1, $nodes);
            $this->assertSame('2026-09-02', (string) $nodes[0]->lastmod);
        }
        $this->assertStringNotContainsString('/faq/how-does-cpa-work', $xml);
        Http::assertNotSent(fn ($request): bool => $request->method() === 'POST');
    }

    #[Test]
    public function UnicodePairIsEncodedAndInvalidSitemapMetadataIsUnavailable(): void
    {
        $thai_slug = "\u{0E04}\u{0E33}\u{0E16}\u{0E32}\u{0E21}-11-1";
        Http::fake([
            'https://backend.example.test/api/faqs*' => Http::sequence()
                ->push([FaqLocalizedFixture::Record(['localized_slugs' => ['th' => $thai_slug]])])
                ->push([FaqLocalizedFixture::Record(['updated_at' => 'tomorrow'])])
                ->push([FaqLocalizedFixture::Record(['localized_slugs' => ['en' => null]])]),
            'https://backend.example.test/api/blogs*' => Http::response([]),
        ]);
        $this->get('/sitemap.xml')->assertOk()->assertSee('/faq/%E0%B8%84%E0%B8%B3%E0%B8%96%E0%B8%B2%E0%B8%A1-11-1', false);
        $this->get('/sitemap.xml')->assertStatus(503);
        $this->get('/sitemap.xml')->assertStatus(503);
    }
}
~~~

Insert head setup:

~~~php
$seo_alternate_urls = ['th' => $seo_page_url.'?lang=th', 'en' => $seo_page_url.'?lang=en'];
$seo_default_url = $seo_page_url;
if (is_array($faq_canonical_urls ?? null)) {
    $seo_current_url = $faq_canonical_urls[app()->getLocale()];
    $seo_alternate_urls = $faq_canonical_urls;
    $seo_default_url = $faq_canonical_urls['th'];
}
~~~

Replace alternate tags:

~~~blade
<link rel="alternate" hreflang="th" href="{{ $seo_alternate_urls['th'] }}">
<link rel="alternate" hreflang="en" href="{{ $seo_alternate_urls['en'] }}">
<link rel="alternate" hreflang="x-default" href="{{ $seo_default_url }}">
~~~

Replace each Thai anchor opening line (two occurrences):

~~~blade
<a data-faq-language="th" href="{{ $faq_language_switch_urls['th'] ?? route('language.switch', 'th') }}"
~~~

Replace each English anchor opening line (two occurrences):

~~~blade
<a data-faq-language="en" href="{{ $faq_language_switch_urls['en'] ?? route('language.switch', 'en') }}"
~~~

Replace CreateFaqEntries only:

~~~php
private function CreateFaqEntries(): array
{
    $entries = [];
    foreach (app(\App\Services\Faq\FaqContentClient::class)->Index('th') as $faq) {
        if ($faq['id'] === null || $faq['slug'] === null) continue;
        $modified_at = $faq['updated_at'] ?? null;
        if (!is_string($modified_at) || trim($modified_at) === '') abort(503, __('faq.unavailable'));
        $date = $this->FaqModifiedDate($modified_at);
        $paths = config('faq-urls.enabled', false)
            ? array_values($faq['localized_detail_urls'])
            : ['/faq/'.rawurlencode($faq['slug'])];
        foreach ($paths as $path) {
            $entries[] = ['location' => $this->CreateAbsoluteUrl($path),
                'last_modified_at' => $date, 'change_frequency' => 'monthly', 'priority' => '0.6'];
        }
    }
    return $entries;
}
~~~

## Task 6: Preserve existing fixtures while enabling real HTTP redirect checks

**Files:**

- Modify: P/tests/browser/support/render-page.php.
- Modify: P/tests/browser/support/page-fixture.mjs at RenderPage, retaining existing imports/constants.
- Test: P/tests/browser/faq-url.spec.mjs, unchanged characterization coverage.

- **Consumes:** Existing fixture CLI/RenderPage signatures, Tasks 2-5 HTTP contracts and real PHP routes/controllers.
- **Produces:** Optional seventh RenderPage argument for localized fixtures; real PHP response status/headers/body fulfillment including 301/302, with legacy fixture behavior preserved.
- **Checkpoint:** Test-support artifact only. No production changes in this task. All fixture HTTP is fake; no real backend, cookie persistence, rendering fidelity, or production readiness is established.

**Actions — nonbehavioral artifacts:**

- [ ] Before changing helpers, run:
~~~powershell
node node_modules/@playwright/test/cli.js test tests/browser/faq-url.spec.mjs
~~~

- [ ] Expect the existing 10 legacy cases to pass. Resolve a fixture/environment problem before editing; record that baseline.
- [ ] Replace render-page.php with the complete file below. Replace only the exported RenderPage function in page-fixture.mjs with the complete function below, retaining its existing imports, constants, and content-type map.
- [ ] Validate syntax and rerun characterization:
~~~powershell
& $faq_php -l tests/browser/support/render-page.php
node --check tests/browser/support/page-fixture.mjs
node node_modules/@playwright/test/cli.js test tests/browser/faq-url.spec.mjs
~~~

- [ ] Expect exit 0 and the same 10 legacy cases passing. Inspect git diff for preservation of the original legacy data, fake origins, fixed testing key, array cache/session, SQLite :memory:, public asset containment checks, external-request blocking, and all old positional arguments.
- [ ] New mode renders every allowed document request through the real PHP HTTP kernel, returns its status/Location, and uses exact URL queries. The PHP CLI uses fresh array sessions on each document; persistent-cookie/session behavior is verified by PHP feature/session tests, not claimed from this browser fixture. The fixture does not test a real backend or CDN styling.

Complete PHP fixture:

~~~php
<?php

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Tests\Support\FaqFixture;
use Tests\Support\FaqLocalizedFixture;

if (PHP_SAPI !== 'cli') exit(1);

$page_target = $argv[1] ?? '/';
$locale = $argv[2] ?? 'en';
$recaptcha_version = $argv[3] ?? 'disabled';
$tracking_mode = $argv[4] ?? 'disabled';
$localized_mode = $argv[5] ?? 'disabled';
$page_parts = parse_url($page_target);
$page_path = is_array($page_parts) ? ($page_parts['path'] ?? '') : '';
$legacy_paths = ['/', '/free-trial', '/contact', '/privacy-policy',
    '/line/liff/free-trial', '/detail/unsafe-body', '/faq', '/faq/how-does-cpa-work'];
$localized_paths = array_merge($legacy_paths, ['/faq/primary-question-11-1',
    '/faq/english-question-11-1', '/faq/primary-trial-12-1',
    '/faq/english-trial-12-1', '/language/th', '/language/en']);
if (!in_array($tracking_mode, ['enabled', 'disabled'], true)
    || !in_array($localized_mode, ['enabled', 'disabled'], true)
    || !in_array($recaptcha_version, ['disabled', 'v2', 'v3'], true)
    || !in_array($locale, ['en', 'th'], true)
    || !is_array($page_parts) || isset($page_parts['scheme']) || isset($page_parts['host'])
    || isset($page_parts['fragment']) || !str_starts_with($page_target, '/')
    || !in_array($page_path, $localized_mode === 'enabled' ? $localized_paths : $legacy_paths, true)
    || ($localized_mode === 'disabled' && $page_target !== $page_path)) {
    fwrite(STDERR, "Unsupported browser fixture.\n");
    exit(1);
}

$repository_root = dirname(__DIR__, 3);
require $repository_root.'/vendor/autoload.php';
$application = require $repository_root.'/bootstrap/app.php';
$application->make(ConsoleKernel::class)->bootstrap();
config([
    'app.locale' => $locale,
    'faq-urls.enabled' => $localized_mode === 'enabled',
    'faq-analytics.enabled' => $tracking_mode === 'enabled',
    'faq-analytics.hash_key' => 'test-only-faq-hash-key-32-characters',
    'faq-analytics.integration_token' => 'test-only-faq-token',
    'session.driver' => 'array',
    'cache.default' => 'array',
    'database.default' => 'sqlite',
    'database.connections.sqlite.database' => ':memory:',
    'services.recaptcha.enabled' => $recaptcha_version !== 'disabled',
    'services.recaptcha.version' => $recaptcha_version === 'disabled' ? 'v3' : $recaptcha_version,
    'services.recaptcha.v2.site_key' => 'test-v2-site-key',
    'services.recaptcha.v3.site_key' => 'test-v3-site-key',
    'services.line_oa.liff_id' => 'isolated-browser-liff',
    'services.eimaths_backend.base_url' => 'https://backend.example.test',
]);
$http_fakes = [
    'https://backend.example.test/api/blogs/unsafe-body' => Http::response([
        'title' => 'Article body', 'slug' => 'unsafe-body',
        'content' => '<p>Safe <strong>formatting</strong></p><img src="/missing-image.jpg" onerror="window.blogBodyExecuted=true"><script>window.blogScriptExecuted=true</script>',
        'created_at' => '2026-09-30T00:00:00Z',
    ]),
];
if ($localized_mode === 'enabled') {
    config(['seo.canonical_origin' => 'http://eimaths.test']);
    CarbonImmutable::setTestNow('2026-10-20T09:00:00Z');
    $records = [];
    foreach (['th', 'en'] as $record_locale) {
        $records[$record_locale] = [
            FaqLocalizedFixture::Record([
                'question' => $record_locale === 'th' ? 'Primary question' : 'English question',
                'answer_html' => '<p>Learn with <strong>blocks</strong>.</p><p>Literal [.*] &lt;script&gt;</p>',
            ]),
            FaqLocalizedFixture::Record([
                'id' => 12, 'slug' => 'trial-lessons',
                'question' => $record_locale === 'th' ? 'Primary trial' : 'English trial',
                'localized_slugs' => ['th' => 'primary-trial-12-1', 'en' => 'english-trial-12-1'],
                'answer' => 'Weekdays.', 'answer_html' => '<p>Weekdays.</p>',
                'category' => ['id' => 6, 'slug' => 'enrolment', 'name' => 'Enrolment', 'position' => 2],
            ]),
        ];
    }
    $request_locale = static function ($upstream_request): string {
        parse_str(parse_url($upstream_request->url(), PHP_URL_QUERY) ?? '', $query);
        return ($query['locale'] ?? null) === 'en' ? 'en' : 'th';
    };
    $http_fakes['https://backend.example.test/api/faq-pages/resolve/*'] =
        static function ($upstream_request) use ($records, $request_locale) {
            $requested_locale = $request_locale($upstream_request);
            $slug = rawurldecode(basename(parse_url($upstream_request->url(), PHP_URL_PATH)));
            foreach ($records[$requested_locale] as $record) {
                if ($slug === $record['localized_slugs'][$requested_locale] || $slug === $record['slug']) {
                    $is_current = $slug === $record['localized_slugs'][$requested_locale];
                    return Http::response([
                        'requested_slug' => $slug, 'locale' => $requested_locale,
                        'match_type' => $is_current ? 'current' : 'alias',
                        'alias_expires_at' => $is_current ? null : '2027-10-20T09:00:00.000000Z',
                        'faq' => $record,
                    ]);
                }
            }
            return Http::response(['error_code' => 'FAQ_NOT_FOUND', 'message' => 'FAQ not found.'], 404);
        };
    $http_fakes['https://backend.example.test/api/faq-pages/*'] =
        static function ($upstream_request) use ($records, $request_locale) {
            $faq_id = basename(parse_url($upstream_request->url(), PHP_URL_PATH));
            foreach ($records[$request_locale($upstream_request)] as $record) {
                if ((string) $record['id'] === $faq_id) return Http::response($record);
            }
            return Http::response(['error_code' => 'FAQ_NOT_FOUND', 'message' => 'FAQ not found.'], 404);
        };
    $http_fakes['https://backend.example.test/api/faqs*'] =
        static fn ($upstream_request) => Http::response($records[$request_locale($upstream_request)]);
} else {
    $http_fakes['https://backend.example.test/api/faqs/how-does-cpa-work*'] =
        Http::response(FaqFixture::Record());
    $http_fakes['https://backend.example.test/api/faqs*'] = Http::response([
        FaqFixture::Record(['answer_html' => '<p>Learn with <strong>blocks</strong>.</p><p>Literal [.*] &lt;script&gt;</p>']),
        FaqFixture::Record(['id' => 12, 'slug' => 'trial-lessons', 'question' => 'When are trial lessons?',
            'answer' => 'Weekdays.', 'answer_html' => '<p>Weekdays.</p>',
            'category' => ['id' => 6, 'slug' => 'enrolment', 'name' => 'Enrolment', 'position' => 2]]),
        FaqFixture::Record(['id' => 14, 'slug' => null, 'question' => 'Legacy question',
            'answer_html' => '<p>Legacy answer.</p>']),
        FaqFixture::Record(['id' => 15, 'slug' => 'plain-entities', 'question' => 'Plain entities',
            'answer_html' => '', 'answer' => 'Use &lt; and &amp; literally.']),
    ]);
}
$http_fakes['*'] = Http::response([]);
Http::preventStrayRequests();
Http::fake($http_fakes);
$kernel = $application->make(HttpKernel::class);
$target = $localized_mode === 'enabled' ? $page_target
    : $page_path.($page_path === '/faq/how-does-cpa-work' ? '?lang='.$locale : '');
$request = Request::create('http://eimaths.test'.$target, 'GET');
$response = $kernel->handle($request);
if ($localized_mode === 'enabled') {
    $headers = ['Content-Type' => $response->headers->get('Content-Type', 'text/html; charset=UTF-8')];
    foreach (['Location', 'Cache-Control'] as $header) {
        if ($response->headers->has($header)) $headers[$header] = $response->headers->get($header);
    }
    echo json_encode(['status' => $response->getStatusCode(), 'headers' => $headers,
        'body' => $response->getContent()], JSON_THROW_ON_ERROR);
} else {
    if ($response->getStatusCode() !== 200) {
        fwrite(STDERR, 'Fixture response status: '.$response->getStatusCode()."\n");
        exit(1);
    }
    echo $response->getContent();
}
$kernel->terminate($request, $response);
CarbonImmutable::setTestNow();
~~~

Complete replacement RenderPage function (existing module imports/constants remain):

~~~javascript
export async function RenderPage(page, page_path = '/', locale = 'en', recaptcha_version = 'disabled', tracking_enabled = false, before_navigation = null, localized_urls_enabled = false) {
    async function RenderHttp(target) {
        const { stdout: rendered_output } = await EXEC_FILE(
            process.env.PHP_BINARY ?? 'php',
            ['-d', 'display_errors=stderr', 'tests/browser/support/render-page.php',
                target, locale, recaptcha_version, tracking_enabled ? 'enabled' : 'disabled',
                localized_urls_enabled ? 'enabled' : 'disabled'],
            {
                cwd: REPOSITORY_ROOT,
                maxBuffer: 4 * 1024 * 1024,
                env: {
                    ...process.env,
                    APP_ENV: 'testing', APP_URL: 'http://eimaths.test',
                    APP_KEY: 'base64:MDEyMzQ1Njc4OTAxMjM0NTY3ODkwMTIzNDU2Nzg5MDE=',
                    DB_CONNECTION: 'sqlite', DB_DATABASE: ':memory:',
                    SESSION_DRIVER: 'array', CACHE_STORE: 'array', MAIL_MAILER: 'array',
                },
            },
        );
        return localized_urls_enabled ? JSON.parse(rendered_output)
            : { status: 200, headers: { 'Content-Type': 'text/html' }, body: rendered_output };
    }
    const legacy_response = localized_urls_enabled ? null : await RenderHttp(page_path);
    const localized_paths = new Set(['/faq', '/faq/how-does-cpa-work',
        '/faq/primary-question-11-1', '/faq/english-question-11-1',
        '/faq/primary-trial-12-1', '/faq/english-trial-12-1', '/language/th', '/language/en']);

    await page.route('**/*', async function FulfillFixture(route) {
        const request = route.request();
        const request_url = new URL(request.url());
        if (request_url.hostname === 'cdn.tailwindcss.com') {
            await route.fulfill({ contentType: 'text/javascript', body: 'window.tailwind = {};' });
            return;
        }
        if (request_url.hostname === 'unpkg.com' && request_url.pathname.startsWith('/lucide')) {
            await route.fulfill({ contentType: 'text/javascript', body: 'window.lucide = { createIcons() {} };' });
            return;
        }
        if (request_url.origin !== 'http://eimaths.test' || request.method() !== 'GET') {
            await route.abort();
            return;
        }
        if (request.resourceType() === 'document'
            && (localized_urls_enabled ? localized_paths.has(request_url.pathname) : request_url.pathname === page_path)) {
            const response = localized_urls_enabled
                ? await RenderHttp(request_url.pathname + request_url.search) : legacy_response;
            const body = (response.headers['Content-Type'] ?? '').includes('text/html')
                ? response.body.replace('</head>', '<style>.hidden { display: none !important; }</style></head>')
                : response.body;
            await route.fulfill({ status: response.status, headers: response.headers, body });
            return;
        }
        const asset_path = resolve(PUBLIC_ROOT, '.' + decodeURIComponent(request_url.pathname));
        const content_type = CONTENT_TYPES[extname(asset_path).toLowerCase()];
        if (!asset_path.startsWith(PUBLIC_ROOT + sep) || !content_type) {
            await route.fulfill({ status: 404, body: '' });
            return;
        }
        try {
            await route.fulfill({ contentType: content_type, body: await read_file(asset_path) });
        } catch (error) {
            if (error.code !== 'ENOENT') throw error;
            await route.fulfill({ status: 404, body: '' });
        }
    });
    if (before_navigation) await before_navigation(page);
    await page.goto('http://eimaths.test' + page_path, { waitUntil: 'domcontentloaded' });
    // Class-state checks remain isolated from the external Tailwind CDN; this is not visual QA.
}
~~~

## Task 7: Keep accordion language controls attached to the open FAQ

**Files:**

- Modify: P/public/js/faq-search.js at InitializeFaqSearch and SetAnswerUrl.
- Modify: P/resources/views/faq.blade.php inside each FAQ article opening tag.
- Test: P/tests/browser/faq-localized-urls.spec.mjs.

- **Consumes:** Task 5 header data markers; Task 2 per-record language_switch_urls; characterized Task 6 fixtures.
- **Produces:** Same-ID links while open; exact original address/language links restored when closed/filtered; unchanged single-open-answer and replaceState behavior.
- **Checkpoint:** None after helper characterization. Test response routing must work before accepting a DOM behavior failure as RED.

**Actions — production behavior changes:**

**REQUIRED IMPLEMENTATION SKILL:** Use test-driven-development.

- [ ] Create the complete new browser test below. Run:
~~~powershell
node node_modules/@playwright/test/cli.js test tests/browser/faq-localized-urls.spec.mjs
~~~

- [ ] Expect the four viewport/language accordion cases to reach their current localized URL, then fail because language header links still lack faq_id=11. Task 5 supplies both markers and Task 6 supplies complete metadata; setup errors and failed route fulfillment are not acceptable RED. The alias case may already pass.
- [ ] Immediately after const list_url in InitializeFaqSearch, insert the snapshot below. Replace only SetAnswerUrl with the supplied function. Inside the existing FAQ article opening tag, before class, insert the exact Blade attributes below. Keep all existing data-search-answer/data-faq-id/data-faq-url/class attributes, no-script heading links, events, search/filter logic, and read tracking unchanged.
- [ ] Rerun the new file, then:
~~~powershell
node --check public/js/faq-search.js
node node_modules/@playwright/test/cli.js test tests/browser/faq-url.spec.mjs tests/browser/faq-search.spec.mjs tests/browser/faq-read-tracking.spec.mjs tests/browser/faq-localized-urls.spec.mjs
~~~

- [ ] Expect exit 0. The six new cases verify desktop/mobile widths, both languages, exact link restoration, no history growth/document navigation on opening, same-FAQ language destinations, and real PHP 301 fulfillment. Existing URL/search/read cases remain GREEN. The language test follows the application's href with page.goto because dropdown visibility is outside this fixture's class-state scope; it verifies the destination/route, not physical dropdown click handling.
- [ ] Refactor only with checks GREEN.

Complete test file:

~~~javascript
import { expect, test } from '@playwright/test';
import { RenderPage } from './support/page-fixture.mjs';

for (const locale of ['th', 'en']) {
    for (const width of [1280, 390]) {
        test('Localized accordion keeps language context ' + locale + ' ' + width, async function AccordionLanguage({ page }) {
            await page.setViewportSize({ width, height: 900 });
            await RenderPage(page, '/faq', locale, 'disabled', false, null, true);
            const original_links = await page.locator('a[data-faq-language]').evaluateAll(function OriginalLinks(links) {
                return links.map(function Href(link) { return link.getAttribute('href'); });
            });
            const original_history = await page.evaluate(function HistoryLength() { return history.length; });
            const document_requests = [];
            page.on('request', function ObserveDocuments(request) {
                if (request.resourceType() === 'document') document_requests.push(request.url());
            });
            await page.locator('#faq-search').fill('blocks');
            const toggle = page.locator('.faq-item[data-faq-id="11"] .faq-toggle');
            await toggle.click();
            await expect(page).toHaveURL('http://eimaths.test/faq/' +
                (locale === 'en' ? 'english-question-11-1?lang=en' : 'primary-question-11-1'));
            const english_links = page.locator('a[data-faq-language="en"]');
            await expect(english_links).toHaveCount(2);
            for (const link of await english_links.all()) {
                await expect(link).toHaveAttribute('href', '/language/en?faq_id=11');
            }
            expect(await page.evaluate(function HistoryLength() { return history.length; })).toBe(original_history);
            expect(document_requests).toEqual([]);
            await toggle.click();
            await expect(page).toHaveURL('http://eimaths.test/faq');
            expect(await page.locator('a[data-faq-language]').evaluateAll(function RestoredLinks(links) {
                return links.map(function Href(link) { return link.getAttribute('href'); });
            })).toEqual(original_links);
            await toggle.click();
            await page.locator('#faq-clear').click();
            await expect(page).toHaveURL('http://eimaths.test/faq');
            expect(await page.locator('a[data-faq-language]').evaluateAll(function RestoredLinks(links) {
                return links.map(function Href(link) { return link.getAttribute('href'); });
            })).toEqual(original_links);
            await expect(page.locator('.faq-item > a')).toHaveCount(0);
        });
    }
}

test('Language click from an open answer and detail stays on the same FAQ', async function NavigateLanguages({ page }) {
    await RenderPage(page, '/faq', 'th', 'disabled', false, null, true);
    await page.locator('#faq-search').fill('blocks');
    await page.locator('.faq-item[data-faq-id="11"] .faq-toggle').click();
    // The href is produced by the application; route fulfillment executes the real PHP language route.
    const target = await page.locator('a[data-faq-language="en"]').first().getAttribute('href');
    expect(target).toBe('/language/en?faq_id=11');
    await page.goto('http://eimaths.test' + target);
    await expect(page).toHaveURL('http://eimaths.test/faq/english-question-11-1?lang=en');
    await expect(page.locator('[data-faq-detail][data-faq-id="11"]')).toBeVisible();
    const primary_target = await page.locator('a[data-faq-language="th"]').first().getAttribute('href');
    expect(primary_target).toBe('/language/th?faq_id=11');
    await page.goto('http://eimaths.test' + primary_target);
    await expect(page).toHaveURL('http://eimaths.test/faq/primary-question-11-1');
    await expect(page.locator('[data-faq-detail][data-faq-id="11"]')).toBeVisible();
});

test('An earlier English URL reaches the current English detail through a real 301', async function AliasNavigation({ page }) {
    const statuses = [];
    await RenderPage(page, '/faq/how-does-cpa-work?lang=en', 'en', 'disabled', false,
        async function WatchResponse(fixture_page) {
            fixture_page.on('response', function ObserveOldResponse(response) {
                if (new URL(response.url()).pathname === '/faq/how-does-cpa-work') statuses.push(response.status());
            });
        }, true);
    await expect(page).toHaveURL('http://eimaths.test/faq/english-question-11-1?lang=en');
    expect(statuses).toEqual([301]);
    await expect(page.locator('[data-faq-detail][data-faq-id="11"]')).toBeVisible();
});
~~~

Insert after const list_url:

~~~javascript
const language_links = Array.from(document.querySelectorAll('a[data-faq-language]')).map(function SnapshotLanguageLink(link) {
    return { link, locale: link.getAttribute('data-faq-language'), original_href: link.getAttribute('href') };
});
~~~

Replace SetAnswerUrl:

~~~javascript
function SetAnswerUrl(record = null) {
    const detail_url = record?.article.dataset.faqUrl;
    const next_url = new URL(detail_url ?? list_url, list_url);
    if (window.location.href !== next_url.href) {
        window.history.replaceState(window.history.state, '', next_url.href);
    }
    for (const language_link of language_links) {
        const target = record?.article.getAttribute('data-faq-language-' + language_link.locale);
        language_link.link.setAttribute('href', target ?? language_link.original_href);
    }
}
~~~

Insert inside each existing FAQ article opening tag, before class:

~~~blade
@if(isset($faq['language_switch_urls']))
    data-faq-language-th="{{ $faq['language_switch_urls']['th'] }}"
    data-faq-language-en="{{ $faq['language_switch_urls']['en'] }}"
@endif
~~~

## Task 8: Record integrated verification and hand off activation separately

**Files:**

- Modify: B/docs/plans/0005-0006-faq-localized-urls-execution-record.md, created by backend Task 7; if public verification finishes first, create the same single shared record with the exact headings below.
- Test: P/tests/Feature/FaqLocalizedDefaultTest.php.
- Test: P/tests/Feature/FaqLocalizedClientTest.php.
- Test: P/tests/Feature/FaqLocalizedUrlTest.php.
- Test: P/tests/Feature/FaqLocalizedLanguageTest.php.
- Test: P/tests/Feature/FaqLocalizedSeoTest.php.
- Test: P/tests/Feature/FaqContentBoundaryTest.php.
- Test: P/tests/Feature/FaqDetailPageTest.php.
- Test: P/tests/Feature/FaqSitemapTest.php.
- Test: P/tests/Feature/FaqReadProxyTest.php.
- Test: P/tests/Feature/FaqReadSessionPersistenceTest.php.
- Test: P/tests/Feature/FaqSessionRenewalTest.php.
- Test: P/tests/browser/faq-localized-urls.spec.mjs.
- Test: P/tests/browser/faq-url.spec.mjs.
- Test: P/tests/browser/faq-search.spec.mjs.
- Test: P/tests/browser/faq-read-tracking.spec.mjs.

- **Consumes:** Both plans' task evidence, real commands/results, the approved specification, and current repository diffs.
- **Produces:** One English execution record, final independent review, and a clear implementation/activation boundary.
- **Checkpoint:** Do not claim integration/deployment complete from fakes. Production operations and the intended-engine/scheduler environment remain separately authorized and evidenced checkpoints; unresolved required checks remain explicit.

**Actions — nonbehavioral artifacts:**

- [ ] Run final P PHP and browser suites:
~~~powershell
& $faq_php vendor/bin/phpunit --do-not-cache-result
node node_modules/@playwright/test/cli.js test
git diff --check
git status --short
~~~

- [ ] Expect exit 0 for full P PHP/browser suites and diff check. Record actual counts, commands, environment, failures/skips, and whether each assertion exercised active or disabled mode. If a command fails, diagnose within scope; do not silently relabel historical results or broaden into unrelated repairs.
- [ ] With both implementations present, trace the real B serializer/envelope against the P fixtures/adapter, ensure both revisions and expiry semantics match, verify no new server-side GET invokes read tracking, and inspect analytics/schema diffs for unchanged IDs, counters, receipts, and read keys. This static contract check supplements rather than replaces intended-engine tests or any later real HTTP deployment smoke check.
- [ ] Coordinate one independent final review of both diffs against spec 0004 after implementation, as required by Native execution. Address relevant findings with TDD and rerun affected checks. If review capability is unavailable, report that required review is incomplete rather than marking completion.
- [ ] Update the shared record in English, preserving all backend evidence and using these shared headings if creating it: Approved scope and execution method; Source/revisions; Isolation; Per-task RED/GREEN; Backend functional results; Intended-engine results; Public functional/browser results; Full-suite limitations; Cross-plan contract review; Independent final review; Deployment prerequisites still unverified; Recovery constraints. Record each required result and limitation, not anticipated outcomes.
- [ ] Validate the record and scoped diffs:
~~~powershell
git -C ../eiMaths diff --check
git diff --check
Get-Content -LiteralPath '../eiMaths/docs/plans/0005-0006-faq-localized-urls-execution-record.md'
~~~

- [ ] Expect both diff checks exit 0, one English record with traceable observed results, and no production commands executed. Inspect git status in both repositories to preserve unrelated work.
- [ ] Handoff order, without executing it here: deploy B with flag off; apply approved additive migrations; drain old writers and enable the new writer under the backend plan's controlled checkpoint; backfill every FAQ; verify current pairs and API/error/no-store behavior; deploy P while its flag is off; validate readiness and enable P only under separate authorization; verify the real scheduler invokes pruning daily at 02:30 Asia/Bangkok. Exact production commands depend on the authorized server context and are not guessed here.
- [ ] After localized links are exposed, retain backend resolver/schema/revision state during recovery; do not prescribe a blanket flag-off rollback that breaks existing links. Preserve aliases until their own one-year deadlines, read data, and current revisions. Old cached redirects cannot be invalidated merely by this change.
- [ ] Present actual implementation evidence and remaining operational checks. Do not commit/push, alter production .env, prune real data, deploy, or claim those steps completed under this plan.

## Approval boundary

Approval of design 0004 and the two-plan split does not approve implementation. Present plans 0005 and 0006 together and obtain explicit approval of both current artifacts. Preserve Native execution; do not ask for method selection again. Until plan approval, no workspace preparation, implementation edits, application tests, migrations, or activation are permitted.
