<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $categories = require database_path('data/faq_web_selection.php');
        $now = now();

        foreach ($categories as $category) {
            DB::table('faq_categories')->updateOrInsert(
                ['slug' => $category['slug']],
                [
                    'name' => $category['name'],
                    'name_en' => $category['name_en'],
                    'description' => $category['description'],
                    'description_en' => $category['description_en'],
                    'position' => $category['position'],
                    'is_published' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            $category_id = DB::table('faq_categories')->where('slug', $category['slug'])->value('id');

            foreach ($category['faqs'] as $faq) {
                DB::table('faqs')->updateOrInsert(
                    ['source_reference' => $faq['source_reference']],
                    [
                        'category_id' => $category_id,
                        'question' => $faq['question'],
                        'question_en' => $faq['question_en'],
                        'answer' => $faq['answer'],
                        'answer_en' => $faq['answer_en'],
                        'selection_reason' => $faq['selection_reason'],
                        'publication_note' => $faq['publication_note'],
                        'position' => $faq['position'],
                        'is_published' => true,
                        'published_at' => $now,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        $categories = require database_path('data/faq_web_selection.php');
        $source_references = [];

        foreach ($categories as $category) {
            foreach ($category['faqs'] as $faq) {
                $source_references[] = $faq['source_reference'];
            }
        }

        DB::table('faqs')->whereIn('source_reference', $source_references)->delete();

        foreach (array_reverse($categories) as $category) {
            $category_id = DB::table('faq_categories')->where('slug', $category['slug'])->value('id');

            if ($category_id && ! DB::table('faqs')->where('category_id', $category_id)->exists()) {
                DB::table('faq_categories')->where('id', $category_id)->delete();
            }
        }
    }
};
