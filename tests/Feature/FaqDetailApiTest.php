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
        $this->getJson('/api/faqs/'.$faq->slug.'?locale=unknown')->assertOk()->assertJsonPath('question', 'Primary question');
        $this->getJson('/api/faqs/'.$faq->slug.'?locale[]=en')->assertOk()->assertJsonPath('question', 'Primary question');
        $this->getJson('/api/faqs/invalid%2Fsegment')->assertNotFound();
        $this->assertArrayNotHasKey('source_reference', $detail);
        $category->update(['is_published' => false]);
        $this->getJson('/api/faqs/'.$faq->slug)->assertNotFound();
        $category->update(['is_published' => true]);
        $faq->update(['is_published' => false]);
        $this->getJson('/api/faqs/'.$faq->slug)->assertNotFound();
        $this->getJson('/api/faqs/unknown-fixture')->assertNotFound();
    }
}
