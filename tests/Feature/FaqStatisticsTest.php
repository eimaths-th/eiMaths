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
    protected function tearDown(): void
    {
        \Carbon\CarbonImmutable::setTestNow();
        parent::tearDown();
    }
    private function ClearOnlyIsolatedFaqs(): void
    {
        $this->assertSame('sqlite', \Illuminate\Support\Facades\DB::connection()->getDriverName());
        $this->assertSame(':memory:', \Illuminate\Support\Facades\DB::connection()->getDatabaseName());
        \Illuminate\Support\Facades\DB::table('faqs')->delete();
    }
    private function Actor(bool $can_view): User
    {
        $department = Department::find(Department::HQ_ID);
        if ($department === null) {
            $department = Department::forceCreate(['id' => Department::HQ_ID,
                'name' => 'FAQ Statistics HQ', 'code' => 'FAQ-HQ', 'status' => 1, 'enabled_modules' => null]);
        } else {
            $department->update(['status' => 1, 'enabled_modules' => null]);
        }
        $actor = User::factory()->create(['department_id' => $department->id, 'disable' => 0, 'language_code' => 'en', 'password' => \Illuminate\Support\Facades\Hash::make('test-only-password')]);
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
    #[Test] public function InvalidHtmlFiltersRenderFieldErrorsWithoutQueryingTheReport(): void
    {
        $this->actingAs($this->Actor(true));
        foreach ([null, '/faqs/statistics?range=7d'] as $referer) {
            if ($referer !== null) $this->withHeader('Referer', url($referer));
            \Illuminate\Support\Facades\DB::enableQueryLog();
            try {
                $response = $this->get('/faqs/statistics?range=custom&start_date=2026-02-30&end_date=2026-03-01')
                    ->assertStatus(422)->assertDontSee('data-faq-statistics="true"', false);
                $queries = \Illuminate\Support\Facades\DB::getQueryLog();
                $this->assertSame([], array_values(array_filter($queries, fn ($query): bool => str_contains($query['query'], 'faq_daily_reads'))));
                $document = new \DOMDocument();
                @$document->loadHTML('<?xml encoding="UTF-8">'.$response->getContent());
                $xpath = new \DOMXPath($document);
                $this->assertSame('true', $xpath->evaluate('string(//input[@name="start_date"]/@aria-invalid)'));
                $this->assertSame('2026-02-30', $xpath->evaluate('string(//input[@name="start_date"]/@value)'));
                $this->assertGreaterThan(0, $xpath->query('//*[@id="faq-start-date-error" and @role="alert"]')->length);
            } finally {
                \Illuminate\Support\Facades\DB::disableQueryLog();
                \Illuminate\Support\Facades\DB::flushQueryLog();
            }
        }
    }
    #[Test] public function InactiveBranchCannotReadGlobalStatistics(): void
    {
        $actor = $this->Actor(true);
        $department = Department::create(['name' => 'Inactive statistics branch', 'code' => 'FAQ-STATS-INACTIVE', 'status' => 0]);
        $actor->update(['department_id' => $department->id]);
        $this->actingAs($actor->refresh())->get('/faqs/statistics')->assertForbidden();
    }
    #[Test] public function DefaultRangesPaginationAndFilterResetAreUsable(): void
    {
        \Carbon\CarbonImmutable::setTestNow(\Carbon\CarbonImmutable::parse('2026-10-20T01:00:00Z'));
        $this->ClearOnlyIsolatedFaqs();
        $category = \App\Models\FaqCategory::query()->firstOrFail();
        for ($faq_id = 401; $faq_id <= 421; $faq_id++) {
            \App\Models\Faq::forceCreate(['id' => $faq_id, 'category_id' => $category->id, 'question' => 'Question '.$faq_id,
                'answer' => 'Answer', 'is_published' => false]);
        }
        $this->actingAs($this->Actor(true));
        $this->get('/faqs/statistics')->assertOk()->assertViewHas('range', fn ($range): bool => $range->range === '30d'
            && $range->start_date === '2026-09-21' && $range->end_date === '2026-10-20');
        $html = $this->get('/faqs/statistics?range=7d')->assertOk()->getContent();
        $document = new \DOMDocument();
        @$document->loadHTML('<?xml encoding="UTF-8">'.$html);
        $xpath = new \DOMXPath($document);
        $this->assertSame(20, $xpath->query('//table[@data-faq-statistics]//tbody/tr')->length);
        $this->assertSame(0, $xpath->query('//form//input[@name="page"] | //form//select[@name="page"]')->length);
        $next_link = $xpath->evaluate('string(//a[@rel="next"]/@href)');
        parse_str(parse_url($next_link, PHP_URL_QUERY), $parameters);
        $this->assertSame(['range' => '7d', 'start_date' => '2026-10-14', 'end_date' => '2026-10-20', 'page' => '2'], $parameters);
        $this->get('/faqs/statistics?range=7d&page=2')->assertOk()->assertViewHas('rows', fn ($rows): bool => $rows->currentPage() === 2 && $rows->count() === 1);
        $this->get('/faqs')->assertOk()->assertSee(route('faqs.statistics'));
    }
    #[Test] public function DeletedSnapshotIsEscapedAndAValidEmptyReportIsNotAnError(): void
    {
        $this->ClearOnlyIsolatedFaqs();
        $this->actingAs($this->Actor(true));
        $this->get('/faqs/statistics')->assertOk()->assertSee('No FAQ records in this range.');
        $category = \App\Models\FaqCategory::query()->firstOrFail();
        $category->update(['is_published' => true]);
        $faq = \App\Models\Faq::create(['category_id' => $category->id, 'question' => '<script>unsafe()</script>',
            'question_en' => '<script>unsafe()</script>', 'answer' => 'Answer', 'is_published' => true]);
        app(\App\Services\Faq\FaqReadRecorder::class)->Record($faq->id, str_repeat('a', 64));
        $faq_id = $faq->id;
        $faq->delete();
        $html = $this->get('/faqs/statistics')->assertOk()->assertSee('&lt;script&gt;unsafe()&lt;/script&gt;', false)->getContent();
        $document = new \DOMDocument();
        @$document->loadHTML('<?xml encoding="UTF-8">'.$html);
        $xpath = new \DOMXPath($document);
        $this->assertSame(0, $xpath->query('//tr[@data-faq-id="'.$faq_id.'"]//script')->length);
        $this->assertSame('1', trim($xpath->evaluate('string(//tr[@data-faq-id="'.$faq_id.'"]/td[last()])')));
        $this->assertStringContainsString('Deleted question', $xpath->evaluate('string(//tr[@data-faq-id="'.$faq_id.'"])'));
    }
    #[Test] public function QueryFailureShowsAnExplicitUnavailableReportWithoutATable(): void
    {
        $this->actingAs($this->Actor(true));
        $this->assertSame(':memory:', \Illuminate\Support\Facades\DB::connection()->getDatabaseName());
        \Illuminate\Support\Facades\Log::spy();
        \Illuminate\Support\Facades\Schema::rename('faq_daily_reads', 'faq_daily_reads_failure_fixture');
        try {
            $this->get('/faqs/statistics')->assertStatus(503)
                ->assertSee('Read statistics could not be loaded. Please refresh and try again.')
                ->assertDontSee('data-faq-statistics="true"', false);
            \Illuminate\Support\Facades\Log::shouldHaveReceived('warning')->once()->withArgs(function ($message, $context): bool {
                return $message === 'faq.statistics.failed' && count($context) === 4
                    && array_diff(array_keys($context), ['operation', 'outcome', 'error_code', 'correlation_id']) === [];
            });
        } finally {
            \Illuminate\Support\Facades\Schema::rename('faq_daily_reads_failure_fixture', 'faq_daily_reads');
        }
    }
    #[Test] public function StatisticsTranslationsHaveMatchingKeysAndRenderBothLocales(): void
    {
        $english = \Illuminate\Support\Facades\Lang::get('content-management.faq.statistics', [], 'en');
        $thai = \Illuminate\Support\Facades\Lang::get('content-management.faq.statistics', [], 'th');
        $this->assertIsArray($english);
        $this->assertIsArray($thai);
        $this->assertSame(array_keys($english), array_keys($thai));
        $actor = $this->Actor(true);
        $this->actingAs($actor)->get('/faqs/statistics')->assertOk()->assertSee('FAQ Read Statistics')->assertDontSee('content-management.faq.statistics.');
        $actor->update(['language_code' => 'th']);
        $this->actingAs($actor->refresh())->get('/faqs/statistics')->assertOk()
            ->assertSee("\u{0E2A}\u{0E16}\u{0E34}\u{0E15}\u{0E34}\u{0E01}\u{0E32}\u{0E23}\u{0E40}\u{0E1B}\u{0E34}\u{0E14}\u{0E2D}\u{0E48}\u{0E32}\u{0E19} FAQ")
            ->assertDontSee('content-management.faq.statistics.');
    }
}
