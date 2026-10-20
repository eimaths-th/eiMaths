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
