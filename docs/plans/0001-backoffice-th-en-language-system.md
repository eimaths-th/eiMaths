# TH/EN Backoffice Language System Implementation Plan

> **REQUIRED EXECUTION SKILL:** After the user approves this plan, use 'executing-plans' for task-by-task execution.

- **Status:** Approved and implemented in the current main worktree; changes remain uncommitted until explicitly requested.
- **Goal:** Authenticated staff can switch between Thai and English in the current top bar, retain their choice across sessions, and use the shared shell, Dashboard, and Students Management in the selected language.
- **Architecture:** Use Laravel translation namespaces, a request-scoped locale middleware, one authenticated preference endpoint, and the existing users.language_code column. The views and AJAX responses consume the same request locale. This is one dependent feature, not separate independently deployable subsystems.
- **Tech Stack:** Laravel 10, PHP 8.2.12 locally, Blade, Bootstrap 4/AdminLTE 3, jQuery, DataTables, existing date/time pickers, ApexCharts/ECharts, PHPUnit 10.5.63.
- **Source:** 'docs/specs/0001-backoffice-th-en-language-system-design.md', approved in the conversation on 2026-09-18.
- **Workspace:** Continue in the existing 'main' checkout as the user previously requested. Preserve unrelated changes. Do not create a commit or push without a new request.

## Global Constraints

The following requirements come from the approved specification:

- "Support exactly two interface locales: 'en' and 'th'."
- "Persist the authenticated user's preference in 'users.language_code'."
- "Fall back safely to English when the stored value is absent, invalid, or unsupported."
- "Translate the shared navigation/top bar/footer plus Dashboard and Students Management labels in the first slice."
- "Preserve existing Affiliate locale behavior and existing public/API contracts."
- "No migration is required because the existing 'users.language_code' column is reused."
- "Keep domain data, student names, course names, and other user-entered values unchanged."

Additional established conversation constraints:

- Retain the current design, sidebar grouping, header spacing, permissions, and modal close behavior.
- Keep study dates in their existing serialized formats and check-in/history times in 24-hour format. Keep Asia/Bangkok as the business timezone.
- Keep manual discontinuation, renewal flags, lesson accounting, and learning-history creation unchanged.
- Documentation prose is English. Thai translation values are application content.
- Follow existing framework method/file names where required; new application actions/helpers use PascalCase, local variables and translation keys use snake_case.
- Use 'test-driven-development' for the new middleware, preference endpoint, and localized AJAX behavior. Do not add tests that merely count translation calls or mirror static markup.

## Verified Starting State

Inspected revision: 'ef3032ac' on 'main'. Before this plan, the only untracked artifact was the approved specification.

### Integration points

- 'app/Http/Kernel.php': the web group starts the session before branch enforcement; the explicit middleware priority also orders session/auth/branch handling.
- 'routes/web.php': the top authenticated group contains staff routes; 'home' is the existing dashboard fallback.
- 'config/auth.php': 'web' uses User; 'student' and 'guest_quiz' use different models. The preference feature must use the web guard explicitly.
- 'app/Models/User.php': language_code is already fillable.
- 'database/migrations/2024_11_20_144913_add_language_code_to_users_table.php': nullable language_code defaults to en.
- 'app/Http/Middleware/SetAffiliateLocale.php': restores the previous locale in finally and defaults unsupported/missing Affiliate preferences to th.
- 'app/Http/Middleware/EnforceBranchContext.php': disabled users, missing branch assignments, and inactive branches are rejected. Do not exempt the new endpoint.
- 'app/Support/Branch/BranchRouteAccess.php': controllers outside its permission map retain their own authorization. The new self-preference endpoint needs authenticated ownership, not a new administrator permission.
- 'resources/views/layouts/header.blade.php' and 'public/css/app-topbar.css': current header design and spacing.
- 'resources/views/layouts/app.blade.php': already sets HTML lang and Content-Language from app()->getLocale().
- 'resources/views/home.blade.php' includes 'resources/views/script.blade.php' for its active dashboard JavaScript; the later demonstration script in home.blade.php is commented out.
- Students Management routes include contacts, guest.report, discontinued, contacts.graduated, histories.index, parent.index, and ei_form.backEndIndex. Demo Students is feature-flagged off by default; do not enable it.
- Student List includes new/edit/course/receipt/check-in/discontinuation/book-receipt modals. AJAX action labels and renewal text are produced in ContactController.
- Existing translation and PHPUnit tests use Tests\TestCase, #[Test], RefreshDatabase for database tests, and PascalCase test names in the recent features.

### Baseline checks actually run

~~~text
php vendor/bin/phpunit --filter 'DashboardCentreScopeTest|FastHistoryTest|StudentRenewalStatusTest' --no-progress --do-not-cache-result
Exit 0: 28 tests, 122 assertions.

php vendor/bin/phpunit --filter 'AffiliateLocaleTest|AffiliateTranslationStructureTest' --no-progress --do-not-cache-result
Exit 1: 7 tests, 34 assertions, 4 existing failures.

node --test tests/JavaScript/credit-note-revenue.test.mjs tests/JavaScript/pending-work-alert.test.mjs tests/JavaScript/pending-work-source-integration.test.mjs
Exit 0: 13 tests.
~~~

The four existing Affiliate failures are:

1. EnglishAndThaiAffiliateTranslationLeavesAndPlaceholdersMatch: legal section headings are used as different associative keys in the two locales.
2. AffiliateDefaultsToThaiWhenPreferenceIsMissing: expected 200, received 403.
3. AuthenticatedEnglishPreferenceWins: expected 200, received 403.
4. UnsupportedPreferenceFallsBackToThai: expected 200, received 403.

These failures predate implementation. Their complete causes have not been diagnosed here. Do not weaken the existing tests or change Affiliate authorization to obtain a green run. Add a focused middleware-composition test for the compatibility contract and compare the broader baseline again at handoff. The specification's all-green Affiliate criterion cannot be claimed met while these failures remain.

Use '--do-not-cache-result' for local PHPUnit runs: the first baseline run could not write the existing '.phpunit.result.cache'. Tests themselves ran; the no-cache rerun avoided this warning.

## Task 1: Resolve a Web User's Locale Without Leaking Request State

**Files:**

- Create: 'config/ui-localization.php'
- Create: 'app/Http/Middleware/SetUiLocale.php'
- Modify: 'app/Http/Kernel.php' at $middlewareGroups['web'] and $middlewarePriority
- Test: 'tests/Unit/Localization/UiLocaleMiddlewareTest.php'

- **Consumes:** Request::user('web'), User::language_code, app()->getLocale(), existing SetAffiliateLocale::handle.
- **Produces:** SetUiLocale::handle(Request $request, Closure $next): Response; configuration keys supported_locales, default_locale, labels.
- **Checkpoint:** Implementation-plan approval is required before execution. Once approved, no additional approval is required for this task.

**Production behavior change — REQUIRED IMPLEMENTATION SKILL: 'test-driven-development'.**

- [ ] Add the test below. Run 'php vendor/bin/phpunit tests/Unit/Localization/UiLocaleMiddlewareTest.php --no-progress --do-not-cache-result'; expect a missing SetUiLocale class, not a database/environment failure.
- [ ] Add the configuration and middleware shown below.
- [ ] Insert '\App\Http\Middleware\SetUiLocale::class' immediately after StartSession in both the web group and the priority list. This applies a web-user locale after session availability, before rendering/branch errors, and before the route-scoped Affiliate override.
- [ ] Rerun the test; expect exit 0. Confirm no API-group insertion and no change to SetAffiliateLocale.
- [ ] Keep unsupported stored preferences unchanged in the database; middleware is read-only.

Configuration:

~~~php
<?php

return [
    'supported_locales' => ['en', 'th'],
    'default_locale' => 'en',
    'labels' => [
        'en' => 'English',
        'th' => 'ไทย',
    ],
];
~~~

Middleware:

~~~php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetUiLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $previous_locale = app()->getLocale();

        try {
            app()->setLocale($this->ResolveLocale($request));

            return $next($request);
        } finally {
            app()->setLocale($previous_locale);
        }
    }

    private function ResolveLocale(Request $request): string
    {
        $user_locale = $request->user('web')?->language_code;
        $supported_locales = config('ui-localization.supported_locales');

        return is_string($user_locale) && in_array($user_locale, $supported_locales, true)
            ? $user_locale
            : config('ui-localization.default_locale');
    }
}
~~~

Test:

~~~php
<?php

namespace Tests\Unit\Localization;

use App\Http\Middleware\SetAffiliateLocale;
use App\Http\Middleware\SetUiLocale;
use App\Models\User;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;

class UiLocaleMiddlewareTest extends TestCase
{
    public static function Preferences(): array
    {
        return [
            'english' => ['en', 'en'],
            'thai' => ['th', 'th'],
            'missing' => [null, 'en'],
            'unsupported' => ['ja', 'en'],
            'uppercase' => ['TH', 'en'],
            'empty' => ['', 'en'],
        ];
    }

    #[Test]
    #[DataProvider('Preferences')]
    public function ResolvesPreferenceAndRestoresPreviousLocale(
        ?string $stored_locale,
        string $expected_locale
    ): void {
        $user = new User(['language_code' => $stored_locale]);
        $request = Request::create('/home');
        $request->setUserResolver(static fn ($guard = null) => $user);
        app()->setLocale('fr');

        $response = app(SetUiLocale::class)->handle(
            $request,
            static fn (Request $request) => response(app()->getLocale()),
        );

        $this->assertSame($expected_locale, $response->getContent());
        $this->assertSame('fr', app()->getLocale());
    }

    #[Test]
    public function GuestUsesEnglishAndOnlyTheWebGuardIsConsulted(): void
    {
        $request = Request::create('/home');
        $request->setUserResolver(function ($guard = null) {
            $this->assertSame('web', $guard);

            return null;
        });

        $response = app(SetUiLocale::class)->handle(
            $request,
            static fn (Request $request) => response(app()->getLocale()),
        );

        $this->assertSame('en', $response->getContent());
    }

    #[Test]
    public function RestoresLocaleWhenDownstreamCodeThrows(): void
    {
        $request = Request::create('/home');
        $request->setUserResolver(static fn ($guard = null) => new User(['language_code' => 'th']));
        app()->setLocale('en');

        try {
            app(SetUiLocale::class)->handle($request, function () {
                $this->assertSame('th', app()->getLocale());
                throw new RuntimeException('Downstream failure');
            });
            $this->fail('The exception must propagate.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Downstream failure', $exception->getMessage());
        }

        $this->assertSame('en', app()->getLocale());
    }

    #[Test]
    public function AffiliateOverrideStillDefaultsToThaiAndBothLayersRestoreState(): void
    {
        $user = new User(['language_code' => null]);
        $this->actingAs($user, 'web');
        $request = Request::create('/affiliate');
        $request->setUserResolver(static fn ($guard = null) => $user);
        config(['affiliate.default_locale' => 'th']);
        app()->setLocale('en');

        $response = app(SetUiLocale::class)->handle($request, function (Request $request) {
            $this->assertSame('en', app()->getLocale());

            $response = app(SetAffiliateLocale::class)->handle(
                $request,
                static fn (Request $request) => response(app()->getLocale()),
            );
            $this->assertSame('en', app()->getLocale());

            return $response;
        });

        $this->assertSame('th', $response->getContent());
        $this->assertSame('en', app()->getLocale());
    }
}
~~~

## Task 2: Persist the Current User's Language With a Safe Return Redirect

**Files:**

- Create: 'app/Http/Controllers/UiLocaleController.php'
- Modify: 'routes/web.php' inside the existing top-level auth group
- Test: 'tests/Feature/Localization/UiLocalePreferenceTest.php'

- **Consumes:** Task 1 configuration; web authentication/CSRF; existing branch checks; route('home').
- **Produces:** POST /settings/locale, route name locale.update, UiLocaleController::Update(Request $request): RedirectResponse. Form field: locale. Response: redirect.
- **Checkpoint:** None after plan approval.

**Production behavior change — REQUIRED IMPLEMENTATION SKILL: 'test-driven-development'.**

- [ ] Add the test below; run 'php vendor/bin/phpunit tests/Feature/Localization/UiLocalePreferenceTest.php --no-progress --do-not-cache-result'. Expect locale.update to be undefined before implementation.
- [ ] Add the controller below and the route inside the existing auth group:

~~~php
Route::post('/settings/locale', [\App\Http\Controllers\UiLocaleController::class, 'Update'])
    ->name('locale.update');
~~~

- [ ] Rerun the test and inspect 'php artisan route:list --name=locale.update -v'. Expect exit 0, one POST route, auth and the web pipeline.
- [ ] Preserve branch/account enforcement. Do not register the endpoint in API routes or accept a destination user.
- [ ] A preference change intentionally also becomes visible to existing Affiliate middleware on later requests, because it reads the same existing language_code field. The Affiliate fallback and override rules remain unchanged.

Controller:

~~~php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UiLocaleController extends Controller
{
    public function Update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'locale' => ['required', 'string', Rule::in(config('ui-localization.supported_locales'))],
            'user_id' => ['prohibited'],
            'id' => ['prohibited'],
        ]);

        $request->user('web')->forceFill([
            'language_code' => $validated['locale'],
        ])->save();

        $return_url = url()->previous(route('home'));
        $origin_prefix = $request->getSchemeAndHttpHost() . '/';

        if (! str_starts_with($return_url, $origin_prefix)
            || $return_url === route('locale.update')) {
            $return_url = route('home');
        }

        return redirect()->to($return_url);
    }
}
~~~

Test:

~~~php
<?php

namespace Tests\Feature\Localization;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UiLocalePreferenceTest extends TestCase
{
    use RefreshDatabase;

    private User $actor;

    protected function setUp(): void
    {
        parent::setUp();

        $branch = Department::forceCreate([
            'id' => 2,
            'name' => 'Locale Test Branch',
            'code' => 'LOCALE',
            'status' => 1,
            'enabled_modules' => null,
        ]);
        $this->actor = User::factory()->create([
            'department_id' => $branch->id,
            'disable' => 0,
            'language_code' => 'en',
        ]);
    }

    public static function SupportedLocales(): array
    {
        return [['th'], ['en']];
    }

    public static function InvalidLocales(): array
    {
        return [['ja'], ['TH'], [''], [null], [['th']], [42]];
    }

    #[Test]
    #[DataProvider('SupportedLocales')]
    public function PersistsOnlyTheCurrentUsersPreferenceAndReturnsToTheSamePage(string $locale): void
    {
        $other_user = User::factory()->create(['language_code' => 'en']);
        $return_url = route('contacts', ['renewal_status' => 'pending']);

        $this->actingAs($this->actor, 'web')->from($return_url)
            ->post(route('locale.update'), ['locale' => $locale])
            ->assertRedirect($return_url);

        $this->assertSame($locale, $this->actor->fresh()->language_code);
        $this->assertSame('en', $other_user->fresh()->language_code);

        $this->actingAs($this->actor->fresh(), 'web')->get(route('home'))
            ->assertOk()->assertSee('<html lang="' . $locale . '">', false);
    }

    #[Test]
    #[DataProvider('InvalidLocales')]
    public function RejectsUnsupportedPreferencesWithoutWriting(mixed $locale): void
    {
        $this->actingAs($this->actor, 'web')->postJson(route('locale.update'), [
            'locale' => $locale,
        ])->assertUnprocessable()->assertJsonValidationErrors('locale');

        $this->assertSame('en', $this->actor->fresh()->language_code);
    }

    #[Test]
    public function RejectsAnotherUserIdentifier(): void
    {
        $other_user = User::factory()->create(['language_code' => 'en']);

        $this->actingAs($this->actor, 'web')->postJson(route('locale.update'), [
            'locale' => 'th',
            'user_id' => $other_user->id,
        ])->assertUnprocessable()->assertJsonValidationErrors('user_id');

        $this->assertSame('en', $this->actor->fresh()->language_code);
        $this->assertSame('en', $other_user->fresh()->language_code);
    }

    #[Test]
    public function RequiresWebAuthentication(): void
    {
        $this->postJson(route('locale.update'), ['locale' => 'th'])->assertUnauthorized();
        $this->assertSame('en', $this->actor->fresh()->language_code);
    }

    #[Test]
    public function DisabledAccountsRemainForbidden(): void
    {
        $this->actor->update(['disable' => 1]);

        $this->actingAs($this->actor, 'web')->postJson(route('locale.update'), [
            'locale' => 'th',
        ])->assertForbidden();

        $this->assertSame('en', $this->actor->fresh()->language_code);
    }

    #[Test]
    public function ExternalReturnUrlsFallBackToHome(): void
    {
        $this->actingAs($this->actor, 'web')->from('https://outside.example.test/path')
            ->post(route('locale.update'), ['locale' => 'th'])
            ->assertRedirect(route('home'));
    }

    #[Test]
    public function MissingReturnUrlFallsBackToHome(): void
    {
        $this->actingAs($this->actor, 'web')
            ->post(route('locale.update'), ['locale' => 'th'])
            ->assertRedirect(route('home'));
    }

    #[Test]
    public function RequiresCsrfOutsideTheFrameworksTestBypass(): void
    {
        $this->app->bind(VerifyCsrfToken::class, function ($application) {
            return new class($application, $application['encrypter']) extends VerifyCsrfToken
            {
                protected function runningUnitTests()
                {
                    return false;
                }
            };
        });

        $this->actingAs($this->actor, 'web')
            ->post(route('locale.update'), ['locale' => 'th'])
            ->assertStatus(419);

        $this->assertSame('en', $this->actor->fresh()->language_code);
    }
}
~~~

## Task 3: Add Matching Locale-First Translation Catalogs

**Files:**

- Create: 'lang/en/common.php' and 'lang/th/common.php'
- Create: 'lang/en/navigation.php' and 'lang/th/navigation.php'
- Create: 'lang/en/dashboard.php' and 'lang/th/dashboard.php'
- Create: 'lang/en/students.php' and 'lang/th/students.php'
- Test: 'tests/Unit/Localization/UiTranslationStructureTest.php'

- **Consumes:** Visible labels and messages in the exact consumer files identified in Tasks 4–9.
- **Produces:** Matching English/Thai key trees, plain-text values, named placeholders, and widget label arrays.
- **Checkpoint:** None.

**Production behavior change — REQUIRED IMPLEMENTATION SKILL: 'test-driven-development'.**

- [ ] Add the parity test below and run 'php vendor/bin/phpunit tests/Unit/Localization/UiTranslationStructureTest.php --no-progress --do-not-cache-result'; expect missing catalog files.
- [ ] Add both locales together, using the namespace ownership and terminology below. Add leaves as their consumers are migrated; never add an unused second translation store.
- [ ] Run the parity test after each consumer batch. Expect matching non-empty leaves and placeholders, including DataTables tokens.
- [ ] Keep config/app.php locale and fallback_locale at en. Use English fallback when a legacy module has no Thai key.

Test:

~~~php
<?php

namespace Tests\Unit\Localization;

use Illuminate\Support\Arr;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UiTranslationStructureTest extends TestCase
{
    public static function Catalogs(): array
    {
        return [['common'], ['navigation'], ['dashboard'], ['students']];
    }

    #[Test]
    #[DataProvider('Catalogs')]
    public function LocalesHaveMatchingNonEmptyLeavesAndPlaceholders(string $catalog): void
    {
        $english_path = lang_path('en/' . $catalog . '.php');
        $thai_path = lang_path('th/' . $catalog . '.php');
        $this->assertFileExists($english_path);
        $this->assertFileExists($thai_path);
        $english = Arr::dot(require $english_path);
        $thai = Arr::dot(require $thai_path);
        ksort($english);
        ksort($thai);
        $this->assertNotEmpty($english);
        $this->assertSame(array_keys($english), array_keys($thai));

        foreach ($english as $key => $english_text) {
            $this->assertIsString($english_text, $key);
            $this->assertIsString($thai[$key], $key);
            $this->assertNotSame('', trim($english_text), $key);
            $this->assertNotSame('', trim($thai[$key]), $key);
            $this->assertSame(
                $this->Placeholders($english_text),
                $this->Placeholders($thai[$key]),
                $catalog . '.' . $key,
            );
        }
    }

    private function Placeholders(string $text): array
    {
        preg_match_all('/:[a-zA-Z_][a-zA-Z0-9_]*|_[A-Z]+_/', $text, $matches);
        $placeholders = array_values(array_unique($matches[0]));
        sort($placeholders);

        return $placeholders;
    }
}
~~~

### Catalog ownership

| Namespace | Subgroups and content |
| --- | --- |
| common | actions, fields, feedback, permission_denied, language, datatable, calendar |
| navigation | header, footer, menu; every currently rendered sidebar item, including links to modules whose internal pages are outside this slice |
| dashboard | title, subtitle, filters, metrics, charts, series, date_ranges |
| students | titles, fields, actions, renewal, check_in, discontinue, history, parents, guest_reports, eiform, book_receipts, feedback |

Use English intent-based snake_case keys, never translated text as associative keys. Preserve third-party DataTables property names where the consumer requires them. Calendar day/month arrays use the same numeric indexes in both locales.

### Required Shared Terminology

| Key | English | Thai |
| --- | --- | --- |
| common.language.label | Interface language | ภาษาที่แสดง |
| common.language.current | Current language | ภาษาปัจจุบัน |
| common.actions.search | Search | ค้นหา |
| common.actions.save | Save | บันทึก |
| common.actions.cancel | Cancel | ยกเลิก |
| common.actions.confirm | Confirm | ยืนยัน |
| common.actions.close | Close | ปิด |
| common.actions.edit | Edit | แก้ไข |
| common.actions.delete | Delete | ลบ |
| common.actions.delete_all | Delete All | ลบทั้งหมด |
| common.actions.export_excel | Export Excel | ส่งออก Excel |
| common.fields.centre | Centre | สาขา |
| common.fields.department | Department | แผนก |
| common.fields.date | Date | วันที่ |
| common.fields.time | Time | เวลา |
| common.permission_denied | You do not have permission | คุณไม่มีสิทธิ์ใช้งาน |
| navigation.menu.dashboard | Dashboard | ภาพรวม |
| navigation.menu.students_management | Students Management | จัดการนักเรียน |
| navigation.menu.students_list | Students List | รายชื่อนักเรียน |
| navigation.menu.study_histories | Study Histories | ประวัติการเรียน |
| navigation.menu.discontinued_students | Discontinued Students | นักเรียนที่ไม่ต่อคลาส |
| navigation.menu.graduated_students | Graduated Students | นักเรียนที่จบหลักสูตร |
| navigation.menu.parents | Parents | ผู้ปกครอง |
| navigation.menu.guest_reports | Guest Quiz Reports | รายงานผู้ทดลองทำแบบทดสอบ |
| navigation.menu.eiform | eiForm Registrations | ใบสมัคร eiForm |
| dashboard.title | eiMaths Dashboard | ภาพรวม eiMaths |
| dashboard.subtitle | Statistics and reporting | ระบบจัดการข้อมูลสถิติและรายงาน |
| students.titles.list | Student List | รายชื่อนักเรียน |
| students.titles.histories | History List | ประวัติการเรียน |
| students.titles.discontinued | Discontinued Student | นักเรียนที่ไม่ต่อคลาส |
| students.titles.graduated | Graduated Student List | นักเรียนที่จบหลักสูตร |
| students.titles.parents | Parent List | ผู้ปกครอง |
| students.titles.guest_reports | Guest Quizzers List | รายชื่อผู้ทดลองทำแบบทดสอบ |
| students.titles.eiform | eiForm | ใบสมัคร eiForm |
| students.actions.new_student | New Student | เพิ่มนักเรียน |
| students.actions.new_receipt | New Receipt | สร้างใบเสร็จ |
| students.actions.check_in | Check in | เช็กชื่อ |
| students.actions.not_continuing | Not continuing | ไม่ต่อคลาส |
| students.actions.view_course | View Course | ดูคอร์สเรียน |
| students.actions.restart | Restart | กลับมาเรียน |
| students.renewal.pending | Pending renewal | รอต่อคลาส |
| students.renewal.course_expired | Course expired | คอร์สหมดอายุ |
| students.renewal.course_exhausted | Course exhausted | เรียนครบจำนวนครั้ง |
| students.renewal.summary | Attended: :attended, free: :free, remaining: :remaining / :total | เรียนไปแล้ว :attended ฟรี :free เหลือ :remaining / :total |
| students.check_in.confirm_title | Confirm Check in | ยืนยันการเช็กชื่อ |
| students.check_in.study_date | Study date | วันที่เรียน |
| students.check_in.start_time | Start time | เวลาเริ่ม |
| students.check_in.end_time | End time | เวลาสิ้นสุด |
| students.check_in.remaining_after | Remaining after check in | จำนวนครั้งคงเหลือหลังเช็กชื่อ |
| students.discontinue.confirm_title | Confirm not continuing | ยืนยันไม่ต่อคลาส |
| students.discontinue.default_reason | Not continuing class | ไม่ต่อคลาส |

All remaining active field labels, help text, accessibility labels, and defined user-facing feedback in the listed consumers receive matching leaves under the owning subgroup. Preserve their meaning; do not translate record contents, database values, route names, permission names, status/error codes, selectors, file/export schemas, or text inside commented-out examples.

### Encoding contract for every consumer

- Blade text and attributes: escaped '{{ __('students.actions.check_in') }}'.
- PHP-generated HTML: 'e(__('students.actions.check_in'))'; keep markup outside translation strings.
- Blade-generated JavaScript: '{{ \Illuminate\Support\Js::from(__('students.check_in')) }}' or a single translated string passed through Js::from. Do not insert translated strings inside JavaScript quotes.
- Runtime values: use named placeholders server-side; in JavaScript replace named placeholder tokens and insert through textContent/jQuery.text, not unescaped innerHTML.
- DataTables tokens such as _MENU_, _START_, _END_, _TOTAL_, and _MAX_ remain unchanged.
- Repeated controls use common.actions/common.fields, rather than copied leaves in each screen.

## Task 4: Add the Switcher and Translate the Existing Application Shell

**Files:**

- Create: 'resources/views/layouts/partials/language-switcher.blade.php'
- Modify: 'resources/views/layouts/header.blade.php' at app-topbar__right and visible/header accessibility labels
- Modify: 'resources/views/layouts/menu.blade.php' at rendered labels and menu tooltips
- Modify: 'resources/views/layouts/footer.blade.php' at copyright/back-to-top text
- Modify: 'resources/views/layouts/app.blade.php' at updateClock and ezAlerts default labels
- Modify: 'public/css/app-topbar.css' with the scoped styles below
- Modify: Task 3 common/navigation catalogs
- Test: 'tests/Feature/Localization/UiLocalizedPagesTest.php' (Task 5 contains the complete test)

- **Consumes:** locale.update, config supported_locales/labels, current app locale, existing Bootstrap styles.
- **Produces:** Non-JavaScript POST switcher, current-language state, translated shell labels on the existing design.
- **Checkpoint:** None.

**Production behavior change — REQUIRED IMPLEMENTATION SKILL: 'test-driven-development'.**

- [ ] Add the page test from Task 5, initially run only '--filter SharedShellHasAnAccessibleLanguageSwitcher'. Expect no ui-language-switcher form before the change.
- [ ] Include the partial as a list item at the beginning of app-topbar__right, visible on small screens; show it only for a web-authenticated user.
- [ ] Use this partial:

~~~blade
<form method="POST" action="{{ route('locale.update') }}"
    class="ui-language-switcher" aria-label="{{ __('common.language.label') }}">
    @csrf
    @foreach (config('ui-localization.supported_locales') as $locale)
        @php($is_current = app()->getLocale() === $locale)
        <button type="submit" name="locale" value="{{ $locale }}"
            class="btn btn-sm {{ $is_current ? 'btn-primary' : 'btn-light' }}"
            lang="{{ $locale }}" aria-pressed="{{ $is_current ? 'true' : 'false' }}">
            @if ($is_current)
                <i class="fas fa-check" aria-hidden="true"></i>
            @endif
            {{ config('ui-localization.labels.' . $locale) }}
        </button>
    @endforeach
</form>
~~~

- [ ] Append only these scoped layout styles, keeping existing left-side gaps and header/sidebar z-index:

~~~css
.ui-language-switcher {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    margin: 0;
}

.ui-language-switcher .btn {
    min-height: 42px;
    min-width: 44px;
    white-space: nowrap;
}

.ui-language-switcher .btn:focus-visible {
    outline: 3px solid #1266ab;
    outline-offset: 2px;
}
~~~

- [ ] Translate header context labels, accessible labels, full-screen, membership text, profile/sign-out, sidebar labels, footer copyright/back-to-top, and the Date/Time words in updateClock.
- [ ] Preserve the existing clock's calendar/timezone and 24-hour formatting; this task changes labels only, not its date representation.
- [ ] Preserve menu routes, @can conditions, module flags, IDs, event bindings, and modal dismiss attributes.
- [ ] Feature-owned notification contents continue to use their existing module wording in this slice; their polling/events and content translations are outside the static shell contract.
- [ ] Run the switcher test, then the page tests as their consumers are completed. At this task, only shell assertions must be green; the unimplemented page translations remain explicitly red.

## Task 5: Translate Dashboard Labels and Existing Widget Text

**Files:**

- Modify: 'resources/views/home.blade.php' at active content markup
- Modify: 'resources/views/script.blade.php' at InitializeDateRangePicker, InitializeSelect2, active chart options/series and visible feedback
- Modify: Task 3 common/dashboard catalogs
- Test: 'tests/Feature/Localization/UiLocalizedPagesTest.php'
- Regression: 'tests/Unit/DashboardCentreScopeTest.php'
- Regression: 'tests/JavaScript/credit-note-revenue.test.mjs'

- **Consumes:** Current request locale, existing numeric dashboard responses and filter state.
- **Produces:** Localized headings, filters, metric names, chart legends/axis labels and calendar controls without changing calculations.
- **Checkpoint:** None.

**Production behavior change — REQUIRED IMPLEMENTATION SKILL: 'test-driven-development'.**

- [ ] Run the test below filtered to DashboardRendersInBothLocales; expect the Thai heading assertion to fail before view changes.
- [ ] Replace the dashboard title/subtitle, Centre/Date Range/Years, placeholders, Search/Reset labels, metric cards and chart titles with catalog keys.
- [ ] Translate chart series names: total receipts, total income, issued/cancelled credit notes, net revenue, active/discontinued/graduated students, and study totals. Keep numeric response keys and branch/level names unchanged.
- [ ] Replace hardcoded Thai calendar labels/range names in InitializeDateRangePicker using localized common.calendar and dashboard.date_ranges objects. Keep 'DD/MM/YYYY', stored ISO start/end dates, and date math unchanged.
- [ ] Keep Select2 settings and chart resize behavior unchanged. Supply translated placeholders and captions through Js::from.
- [ ] Run 'php vendor/bin/phpunit --filter "DashboardRendersInBothLocales|SharedShellHasAnAccessibleLanguageSwitcher|DashboardCentreScopeTest" --no-progress --do-not-cache-result' and 'node --test tests/JavaScript/credit-note-revenue.test.mjs'; expect exit 0.
- [ ] Do not translate/activate the commented demonstration scripts in home.blade.php or alter DashboardController queries.

Complete shared page test (StudentsManagementPagesRenderInBothLocales becomes green after Tasks 6–9):

~~~php
<?php

namespace Tests\Feature\Localization;

use App\Models\Department;
use App\Models\User;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UiLocalizedPagesTest extends TestCase
{
    use RefreshDatabase;

    private User $actor;

    protected function setUp(): void
    {
        parent::setUp();

        $branch = Department::forceCreate([
            'id' => 2,
            'name' => 'Locale Test Branch',
            'code' => 'LOCALE',
            'status' => 1,
            'enabled_modules' => null,
        ]);
        $role = Role::findOrCreate('BranchAdmin', 'web');
        foreach (['student-list', 'student-edit', 'history-list', 'parent-list'] as $permission) {
            $role->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        }
        $this->actor = User::factory()->create([
            'department_id' => $branch->id,
            'disable' => 0,
            'language_code' => 'en',
        ]);
        $this->actor->assignRole($role);
    }

    public static function Locales(): array
    {
        return [
            ['en', 'eiMaths Dashboard'],
            ['th', 'ภาพรวม eiMaths'],
        ];
    }

    #[Test]
    public function SharedShellHasAnAccessibleLanguageSwitcher(): void
    {
        $this->actor->update(['language_code' => 'th']);

        $response = $this->actingAs($this->actor, 'web')->get(route('home'));
        $response->assertOk()
            ->assertSee('<html lang="th">', false)
            ->assertSee('class="ui-language-switcher"', false)
            ->assertSee('action="' . route('locale.update') . '"', false)
            ->assertSee('name="_token"', false)
            ->assertSee('name="locale" value="th"', false)
            ->assertSee('name="locale" value="en"', false)
            ->assertSee('lang="th" aria-pressed="true"', false)
            ->assertSee('lang="en" aria-pressed="false"', false)
            ->assertSee('ภาษาที่แสดง')
            ->assertSee('Locale Test Branch');
    }

    #[Test]
    #[DataProvider('Locales')]
    public function DashboardRendersInBothLocales(string $locale, string $heading): void
    {
        $this->actor->update(['language_code' => $locale]);

        $response = $this->actingAs($this->actor, 'web')->get(route('home'));
        $response->assertOk()
            ->assertSee('<html lang="' . $locale . '">', false)
            ->assertSee($heading)
            ->assertSee('Locale Test Branch')
            ->assertDontSee('dashboard.title')
            ->assertDontSee('navigation.menu.dashboard');

        $this->AssertContentHeading($response->getContent(), $heading);
    }

    public static function StudentPages(): array
    {
        $pages = [
            ['contacts', 'Student List', 'รายชื่อนักเรียน'],
            ['histories.index', 'History List', 'ประวัติการเรียน'],
            ['discontinued', 'Discontinued Student', 'นักเรียนที่ไม่ต่อคลาส'],
            ['contacts.graduated', 'Graduated Student List', 'นักเรียนที่จบหลักสูตร'],
            ['parent.index', 'Parent List', 'ผู้ปกครอง'],
            ['guest.report', 'Guest Quizzers List', 'รายชื่อผู้ทดลองทำแบบทดสอบ'],
            ['ei_form.backEndIndex', 'eiForm', 'ใบสมัคร eiForm'],
        ];
        $cases = [];
        foreach ($pages as [$route_name, $english_heading, $thai_heading]) {
            $cases[] = [$route_name, 'en', $english_heading];
            $cases[] = [$route_name, 'th', $thai_heading];
        }

        return $cases;
    }

    #[Test]
    #[DataProvider('StudentPages')]
    public function StudentsManagementPagesRenderInBothLocales(
        string $route_name,
        string $locale,
        string $heading
    ): void {
        $this->actor->update(['language_code' => $locale]);

        $response = $this->actingAs($this->actor, 'web')->get(route($route_name));
        $response->assertOk()
            ->assertSee('<html lang="' . $locale . '">', false)
            ->assertSee($heading)
            ->assertDontSee('students.titles.');

        $this->AssertContentHeading($response->getContent(), $heading);
    }

    private function AssertContentHeading(string $html, string $expected_heading): void
    {
        $document = new DOMDocument();
        $previous_errors = libxml_use_internal_errors(true);
        try {
            $document->loadHTML('<?xml encoding="UTF-8" ?>' . $html);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous_errors);
        }

        $xpath = new DOMXPath($document);
        $heading_nodes = $xpath->query(
            '//div[contains(concat(" ", normalize-space(@class), " "), " content-wrapper ")]//h3'
        );
        $headings = [];
        foreach ($heading_nodes as $heading_node) {
            $headings[] = trim(preg_replace('/\s+/u', ' ', $heading_node->textContent));
        }

        $this->assertContains($expected_heading, $headings);
    }
}
~~~

## Task 6: Translate Student List and Its Included Dialogs

**Files:**

- Modify: 'resources/views/contacts/index.blade.php'
- Modify: 'resources/views/contacts/create.blade.php'
- Modify: 'resources/views/contacts/create_demo.blade.php' (already included by the Student List)
- Modify: 'resources/views/contacts/edit.blade.php'
- Modify: 'resources/views/contacts/course.blade.php'
- Modify: 'resources/views/contacts/receipt.blade.php'
- Modify: 'resources/views/contacts/fast-history.blade.php'
- Modify: 'resources/views/contacts/discontinue.blade.php'
- Modify: 'resources/views/contacts/script.blade.php'
- Modify: 'resources/views/contacts/book-receipts/status.blade.php'
- Modify: 'resources/views/contacts/book-receipts/modal.blade.php'
- Modify: 'resources/views/contacts/book-receipts/script.blade.php'
- Modify: Task 3 common/students catalogs
- Test: 'tests/Feature/Localization/UiLocalizedPagesTest.php'
- Regression: 'tests/Feature/Contact/FastHistoryTest.php'
- Regression: 'tests/Feature/Contact/StudentRenewalStatusTest.php'

- **Consumes:** Task 3 translation/encoding contract and unchanged contact/action response schemas.
- **Produces:** Translated Student List filters, table headers, dialogs, client feedback and DataTables controls.
- **Checkpoint:** None.

**Production behavior change — REQUIRED IMPLEMENTATION SKILL: 'test-driven-development'.**

- [ ] Run the contacts datasets in StudentsManagementPagesRenderInBothLocales before editing; expect the Thai body heading to be absent.
- [ ] Migrate the index labels/filter choices, all included dialog labels/options/help/accessibility text, and script-generated feedback in separate reviewable file batches. Use common actions/fields and the students subgroup ownership from Task 3.
- [ ] Include title, labels, buttons, and validation fallback text for both Check in and Not continuing.
- [ ] Keep selectors, option values, data attributes, payload field names, status values, permissions, dismissal handlers, and Ajax request URLs unchanged.
- [ ] Keep '.fast-history-time' on the existing timepicker with 'timeFormat: HH:mm:ss'. Reuse current date/time libraries; do not introduce locale-dependent serialization or a new package.
- [ ] In the discontinuation dialog, use an empty free-text input with a localized default-reason placeholder. Reset it to empty when opened; retain the existing canonical 'Not continuing class' fallback in the submitted payload/backend. User-entered nonempty reasons remain untouched.
- [ ] Replace each DataTables language block with the same common.datatable translations, preserving the spinner markup separately and all _TOKEN_ values.
- [ ] Translate received/total book status and book-dialog messages with named count placeholders. Keep book status codes, timestamps and shipment links unchanged.
- [ ] Run the contacts page datasets, parity test, FastHistoryTest and StudentRenewalStatusTest; expect exit 0.
- [ ] Do not edit or enable the separate disabled demo list, student login, credential generation, Excel export schemas, or receipt calculation logic.

## Task 7: Translate Student AJAX Action Labels and Check-In Feedback

**Files:**

- Modify: 'app/Http/Controllers/ContactController.php' at checkRemaining, FormatRenewalRemaining, index/graduated action columns, fastHistoryPreview, fastHistory, MarkDiscontinued, and UI message literals in store/update/destroy flows consumed by Student List
- Modify: 'app/Services/FastHistoryService.php' at user-facing exception/validation literals
- Modify: 'app/Http/Controllers/BookReceiptController.php' at success text and BookReceiptErrorResponse
- Modify: Task 3 common/students catalogs
- Test: 'tests/Feature/Contact/StudentRenewalStatusTest.php'
- Test: 'tests/Feature/Contact/FastHistoryTest.php'

- **Consumes:** Request locale from Task 1; existing renewal_status, renewal_reason, success/data/error response contracts.
- **Produces:** Localized presentation/message fields while preserving machine-readable values and business behavior.
- **Checkpoint:** None.

**Production behavior change — REQUIRED IMPLEMENTATION SKILL: 'test-driven-development'.**

- [ ] Add the test below to StudentRenewalStatusTest, using its verified createStudent, createHistories and studentListRows helpers. Run '--filter ThaiStudentListKeepsRenewalCodesAndLocalizesActions'; expect untranslated action/renewal text.
- [ ] Translate the text fragments in FormatRenewalRemaining and checkRemaining. Keep numeric counts identical. Escape text fragments before concatenating the existing badge markup.
- [ ] In action-column callbacks, concatenate escaped common/students translations around existing markup. Do not change Gate::allows checks or rawColumns.
- [ ] Translate FastHistoryService's human-readable exception strings at construction. Preserve FastHistoryException's message/status_code signature, all selection/time validation and transaction/duplicate-check logic.
- [ ] Translate ContactController's check-in/discontinuation success/failure messages and BookReceiptController's display messages. Keep stored discontinued_reason defaults and error/status codes unchanged.
- [ ] Add the second test below to FastHistoryTest. Run both modified feature test files plus UiTranslationStructureTest; expect exit 0.
- [ ] Ensure English translation values for existing English messages retain their current wording to preserve existing consumers/tests.

Additional renewal test:

~~~php
#[Test]
public function ThaiStudentListKeepsRenewalCodesAndLocalizesActions(): void
{
    $this->actor->update(['language_code' => 'th']);
    [$student, $book] = $this->createStudent('ST-TH-RENEWAL', 1, 0);
    $this->createHistories($student, $book, 1);

    $row = $this->studentListRows()->firstWhere('id', $student->id);

    $this->assertNotNull($row);
    $this->assertSame('pending', $row['renewal_status']);
    $this->assertSame('course_exhausted', $row['renewal_reason']);
    $this->assertSame(0, $row['renewal_remaining']);
    $this->assertStringContainsString('รอต่อคลาส', $row['remaining']);
    $this->assertStringContainsString('เช็กชื่อ', $row['action']);
    $this->assertStringContainsString('ไม่ต่อคลาส', $row['action']);
    $this->assertSame(0, (int) $student->fresh()->discontinued);
}
~~~

Additional check-in test:

~~~php
#[Test]
public function ThaiCheckInRecordsOneHistoryAndTranslatesItsDuplicateError(): void
{
    $this->actor->update(['language_code' => 'th']);
    [$student] = $this->createStudentWithTwoCourses();

    $this->postJson(route('contacts.fastHistory', $student->id))
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'บันทึกประวัติการเรียนเรียบร้อยแล้ว');

    $this->postJson(route('contacts.fastHistory', $student->id))
        ->assertStatus(422)
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'นักเรียนเช็กชื่อคอร์สนี้ในวันนี้แล้ว');

    $this->assertDatabaseCount('histrories', 1);
}
~~~

Required message leaves for that test:

~~~php
// lang/en/students.php, under check_in:
'recorded' => 'Learning history recorded successfully.',
'duplicate' => 'This student has already checked in for this course today.',

// lang/th/students.php, under check_in:
'recorded' => 'บันทึกประวัติการเรียนเรียบร้อยแล้ว',
'duplicate' => 'นักเรียนเช็กชื่อคอร์สนี้ในวันนี้แล้ว',
~~~

## Task 8: Translate Study Histories and Preserve Its 24-Hour Workflow

**Files:**

- Modify: 'resources/views/histories/index.blade.php'
- Modify: 'resources/views/histories/create.blade.php'
- Modify: 'resources/views/histories/edit.blade.php'
- Modify: 'resources/views/histories/view.blade.php'
- Modify: 'resources/views/histories/log.blade.php'
- Modify: 'resources/views/histories/script.blade.php'
- Modify: 'app/Http/Controllers/HistoriesController.php' at index action labels, store/update feedback, destroy/destroy_all feedback and visible log labels
- Modify: Task 3 common/students catalogs
- Test: 'tests/Feature/Localization/UiLocalizedPagesTest.php'
- Regression: 'tests/Feature/Contact/FastHistoryTest.php'
- Regression: 'tests/Feature/Contact/StudentRenewalStatusTest.php'

- **Consumes:** Same translation/encoding contract, existing histories API and date/time widget configuration.
- **Produces:** Translated history listing/create/edit/view/log presentation and feedback.
- **Checkpoint:** None.

**Production behavior change — REQUIRED IMPLEMENTATION SKILL: 'test-driven-development'.**

- [ ] Run the histories datasets in StudentsManagementPagesRenderInBothLocales; expect the Thai title to fail before translation.
- [ ] Translate active headings, filters, columns, create/edit/view/log dialogs, buttons and script feedback using students.history and common.
- [ ] Use the shared DataTables/calendar label objects without changing current serialized dates, times, order/quantity calculations, upload actions or history status logic.
- [ ] Translate presentation messages in HistoriesController while retaining its response shapes and status codes. Do not rewrite existing audit records or saved descriptions.
- [ ] Run the histories page datasets, parity test and the two contact regression test files; expect exit 0.
- [ ] Check manually during final UI verification that start/end times remain 24-hour and create/edit controls still use the existing picker.

## Task 9: Complete Remaining Active Students Management Screens

**Files:**

- Modify: 'resources/views/discontinued/index.blade.php', 'resources/views/discontinued/course.blade.php', 'resources/views/discontinued/script.blade.php'
- Modify: 'resources/views/graduated/index.blade.php', 'resources/views/graduated/create.blade.php', 'resources/views/graduated/edit.blade.php', 'resources/views/graduated/script.blade.php'
- Modify: 'resources/views/parents/index.blade.php', 'resources/views/parents/create.blade.php', 'resources/views/parents/edit.blade.php', 'resources/views/parents/script.blade.php'
- Modify: 'resources/views/guest-quiz/report.blade.php'
- Modify: 'resources/views/eiform/backend/index.blade.php', 'resources/views/eiform/backend/js.blade.php'
- Modify: 'app/Http/Controllers/DiscontinueController.php' at index/restart/restart_all display text
- Modify: 'app/Http/Controllers/ParentController.php' at index action labels and store/update feedback
- Modify: 'app/Http/Controllers/GuestReportController.php' at index status/action display text
- Modify: 'app/Http/Controllers/EiFormController.php' at backEndIndex category label only
- Modify: Task 3 common/students catalogs
- Test: 'tests/Feature/Localization/UiLocalizedPagesTest.php'

- **Consumes:** Locale middleware, common actions/fields/widget text, existing route/permission/data contracts.
- **Produces:** Localized labels for every active Students Management menu destination.
- **Checkpoint:** None.

**Production behavior change — REQUIRED IMPLEMENTATION SKILL: 'test-driven-development'.**

Execute one screen family at a time:

- [ ] Run its corresponding StudentsManagementPagesRenderInBothLocales datasets before changes; expect missing Thai body text.
- [ ] Translate active text/attributes/dialogs and client feedback in its exact files using the owning subgroup, with the same encoding rules as Task 3.
- [ ] Use the explicit title keys from Task 3. The current Parents heading is the copied text 'Hitory List'; its intended localized title is 'Parent List'. Body-heading assertions must inspect h3 elements inside content-wrapper so a translated sidebar label cannot make an untranslated page pass.
- [ ] Translate controller-generated buttons/status/category captions for the same screen; preserve raw status/option codes and dynamic record contents.
- [ ] Retain permissions and confirmation behavior for restart/delete actions. Keep parent account/password behavior, guest expiry logic and eiForm category values unchanged.
- [ ] Rerun that screen's datasets and parity checks; expect exit 0 before moving to the next family.
- [ ] After the last family, run the entire UiLocalizedPagesTest; expect both locales to render all seven routes successfully.
- [ ] Exclude public registration, guest quiz-taking/results, parent login, legal documents, Excel/PDF documents and other modules from this implementation.

## Task 10: Verify the Integrated Language Journey and Record the Result

**Files:**

- Modify: this plan's checkboxes and final verification notes only after evidence is collected.
- Inspect: all changed implementation files, the specification and regression results.

- **Consumes:** Tasks 1–9.
- **Produces:** A verified implementation handoff with explicit test/UI coverage and baseline failures.
- **Checkpoint:** Stop only if a new failure requires work outside this feature or a required external authority is missing. Do not hide the four existing Affiliate failures. No commit/push is included.

**Nonbehavioral verification artifact.**

- [ ] Run the following focused command once after the final implementation edit:

~~~powershell
php vendor/bin/phpunit --filter 'UiLocaleMiddlewareTest|UiLocalePreferenceTest|UiTranslationStructureTest|UiLocalizedPagesTest|DashboardCentreScopeTest|FastHistoryTest|StudentRenewalStatusTest' --no-progress --do-not-cache-result
~~~

Expected: exit 0, all new language and previously passing targeted regression tests pass.

- [ ] Run 'node --test tests/JavaScript/credit-note-revenue.test.mjs'. Expected: exit 0; dashboard finance series computation remains unchanged.
- [ ] Rerun the recorded Affiliate command. Expected comparison: the same four baseline failures only; no additional failures. Separately report that the new middleware-composition test passes. Do not describe this as an all-green Affiliate suite.
- [ ] Run 'php artisan view:cache', then 'php artisan view:clear' to leave generated views rebuildable. Expected: both exit 0. These commands are present in the verified local artisan command list.
- [ ] Run 'php artisan route:list --name=locale.update -v' and inspect the one authenticated POST route.
- [ ] Run 'git diff --check' and inspect 'git status --short --branch' plus the final diff. Expected: no whitespace errors; all changes limited to this plan.
- [ ] If an authenticated local browser is available, switch EN -> TH -> EN, reload and visit Dashboard, Student List, Histories and a second Students Management page. Verify the active switcher, query-string return location, translated AJAX rows, 24-hour controls and modal close/Cancel.
- [ ] Check desktop and a narrow viewport: existing sidebar/menu spacing retained, language controls visible, no horizontal overflow, keyboard focus visible. Do not create/delete real student histories or change a student's discontinuation state merely to verify translations.
- [ ] Restore the browser user's original language after the UI check. If the browser/session is unavailable, report UI interaction as not verified instead of inventing a result.
- [ ] Update this document with actual commands/results and any remaining limitations; use 'verification-before-completion' before the final report.

## Coverage and Handoff

| Approved requirement | Owning tasks | Evidence |
| --- | --- | --- |
| Persisted TH/EN preference | 1–2 | Middleware and endpoint tests |
| English fallback and locale isolation | 1 | Null/invalid/guest/exception tests |
| Authentication, ownership, CSRF, safe redirect | 2 | Endpoint feature tests |
| Current design and accessible switcher | 4, 10 | Rendered form test and browser inspection |
| Matching locale-first catalogs | 3 | Leaf/placeholder parity test |
| Shared shell and Dashboard | 4–5 | Rendered pages, finance regression, browser |
| Student List, dialogs and AJAX | 6–7 | Page, renewal and check-in tests |
| Histories and remaining student pages | 8–9 | Both-locale route datasets and regression |
| 24-hour times and business rules | 6–8, 10 | Existing FastHistoryTest and browser inspection |
| Affiliate compatibility | 1, 10 | Nested middleware test and explicit baseline comparison |
| No schema migration or data rewrite | 1–2, 10 | Diff inspection and unchanged migrations |

The specification is approved; this implementation plan has not yet been presented for approval. After plan approval, use the required execution workflow while honoring the user's existing instruction to work on main. No additional routine approvals are needed for the tasks above.
