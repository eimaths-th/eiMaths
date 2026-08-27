<?php

namespace Tests\Unit;

use App\Models\Faq;
use App\Models\FaqCategory;
use PHPUnit\Framework\TestCase;

class FaqLocalizationTest extends TestCase
{
    public function test_faq_and_category_use_english_when_available(): void
    {
        $category = new FaqCategory([
            'name' => 'ทั่วไป',
            'name_en' => 'General',
            'description' => 'คำอธิบายภาษาไทย',
            'description_en' => 'English description',
        ]);
        $faq = new Faq([
            'question' => 'คำถามภาษาไทย',
            'question_en' => 'English question',
            'answer' => 'คำตอบภาษาไทย',
            'answer_en' => 'English answer',
        ]);

        $this->assertSame('General', $category->LocalizedName('en'));
        $this->assertSame('English description', $category->LocalizedDescription('en'));
        $this->assertSame('English question', $faq->LocalizedQuestion('en'));
        $this->assertSame('English answer', $faq->LocalizedAnswer('en'));
    }

    public function test_faq_and_category_fall_back_to_thai(): void
    {
        $category = new FaqCategory([
            'name' => 'ทั่วไป',
            'description' => 'คำอธิบายภาษาไทย',
        ]);
        $faq = new Faq([
            'question' => 'คำถามภาษาไทย',
            'answer' => 'คำตอบภาษาไทย',
        ]);

        $this->assertSame('ทั่วไป', $category->LocalizedName('en'));
        $this->assertSame('คำอธิบายภาษาไทย', $category->LocalizedDescription('en'));
        $this->assertSame('คำถามภาษาไทย', $faq->LocalizedQuestion('en'));
        $this->assertSame('คำตอบภาษาไทย', $faq->LocalizedAnswer('en'));
    }

    public function test_selected_faq_data_contains_six_categories_and_eighteen_english_translations(): void
    {
        $categories = require dirname(__DIR__, 2).'/database/data/faq_web_selection.php';
        $faqs = collect($categories)->flatMap(fn (array $category): array => $category['faqs']);
        $source_references = $faqs->pluck('source_reference');

        $this->assertCount(6, $categories);
        $this->assertCount(18, $faqs);
        $this->assertCount(18, $source_references->unique());
        $this->assertTrue($faqs->every(fn (array $faq): bool => filled($faq['question_en']) && filled($faq['answer_en'])));
    }
}
