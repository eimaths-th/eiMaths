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
