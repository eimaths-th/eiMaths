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
        $this->assertArrayHasKey('localized_slugs', $record);
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
            ->assertJsonStructure(['localized_slugs' => ['th', 'en']])
            ->assertJsonPath('localized_slugs.th', null)->assertJsonPath('localized_slugs.en', null);
        $this->getJson('/api/faqs/'.$faq->slug.'?locale[]=en')->assertOk()
            ->assertJsonPath('question', 'Primary question');
    }
}
