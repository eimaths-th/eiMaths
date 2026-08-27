<?php

namespace Tests\Feature;

use App\Models\Faq;
use App\Models\FaqCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FaqApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_selected_excel_faqs_are_imported_with_categories_and_english_content(): void
    {
        $this->assertDatabaseCount('faq_categories', 7);
        $this->assertDatabaseCount('faqs', 18);
        $this->assertDatabaseHas('faq_categories', [
            'slug' => 'curriculum-and-age-appropriateness',
            'name_en' => 'Curriculum and Age Appropriateness',
        ]);
        $this->assertDatabaseHas('faqs', [
            'source_reference' => 100,
            'position' => 3,
            'is_published' => true,
        ]);
    }

    public function test_api_returns_english_content_and_category_when_requested(): void
    {
        $faq = Faq::where('source_reference', 1)->firstOrFail();

        $response = $this->getJson('/api/faqs?locale=en');

        $response
            ->assertOk()
            ->assertJsonFragment([
                'id' => $faq->id,
                'question' => 'Why should a three-year-old start learning mathematics at eimaths?',
            ])
            ->assertJsonFragment([
                'slug' => 'curriculum-and-age-appropriateness',
                'name' => 'Curriculum and Age Appropriateness',
            ]);
    }

    public function test_api_falls_back_to_thai_when_english_content_is_missing(): void
    {
        $category = FaqCategory::create([
            'name' => 'หมวดทดสอบ',
            'slug' => 'fallback-test',
            'position' => 99,
            'is_published' => true,
        ]);
        $faq = Faq::create([
            'category_id' => $category->id,
            'question' => 'คำถามภาษาไทย',
            'answer' => 'คำตอบภาษาไทย',
            'position' => 1,
            'is_published' => true,
            'published_at' => now(),
        ]);

        $this->getJson('/api/faqs?locale=en')
            ->assertOk()
            ->assertJsonFragment([
                'id' => $faq->id,
                'question' => 'คำถามภาษาไทย',
                'answer' => 'คำตอบภาษาไทย',
            ])
            ->assertJsonFragment([
                'slug' => 'fallback-test',
                'name' => 'หมวดทดสอบ',
            ]);
    }

    public function test_api_excludes_unpublished_categories(): void
    {
        $category = FaqCategory::create([
            'name' => 'หมวดที่ปิด',
            'slug' => 'hidden-category',
            'position' => 99,
            'is_published' => false,
        ]);
        Faq::create([
            'category_id' => $category->id,
            'question' => 'คำถามที่ไม่ควรแสดง',
            'answer' => 'คำตอบที่ไม่ควรแสดง',
            'position' => 1,
            'is_published' => true,
            'published_at' => now(),
        ]);

        $this->getJson('/api/faqs')
            ->assertOk()
            ->assertJsonMissing(['slug' => 'hidden-category'])
            ->assertJsonMissing(['question' => 'คำถามที่ไม่ควรแสดง']);
    }
}
