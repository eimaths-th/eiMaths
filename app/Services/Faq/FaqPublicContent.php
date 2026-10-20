<?php
namespace App\Services\Faq;
use App\Models\Faq;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
final class FaqPublicContent
{
    public function Query(): Builder
    {
        return Faq::query()->select('faqs.*')->with('category')
            ->join('faq_categories', 'faq_categories.id', '=', 'faqs.category_id')
            ->where('faqs.is_published', true)->where('faq_categories.is_published', true)
            ->orderBy('faq_categories.position')->orderBy('faqs.position')->orderByDesc('faqs.created_at');
    }
    public function Serialize(Faq $faq, string $locale): array
    {
        $answer = $faq->LocalizedAnswer($locale);
        return ['id' => $faq->id, 'slug' => $faq->slug,
            'question' => $faq->LocalizedQuestion($locale), 'answer' => $answer,
            'answer_html' => Str::markdown($answer), 'position' => $faq->position,
            'is_published' => $faq->is_published, 'published_at' => $faq->published_at,
            'created_at' => $faq->created_at, 'updated_at' => $faq->updated_at,
            'category' => ['id' => $faq->category->id, 'slug' => $faq->category->slug,
                'name' => $faq->category->LocalizedName($locale),
                'description' => $faq->category->LocalizedDescription($locale),
                'position' => $faq->category->position]];
    }
}
