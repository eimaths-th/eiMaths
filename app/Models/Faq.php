<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Faq extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'question',
        'question_en',
        'answer',
        'answer_en',
        'source_reference',
        'selection_reason',
        'publication_note',
        'position',
        'is_published',
        'published_at',
    ];

    protected $casts = [
        'position' => 'integer',
        'source_reference' => 'integer',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(FaqCategory::class, 'category_id');
    }

    public function LocalizedQuestion(string $locale): string
    {
        return $locale === 'en' && filled($this->question_en) ? $this->question_en : $this->question;
    }

    public function LocalizedAnswer(string $locale): string
    {
        return $locale === 'en' && filled($this->answer_en) ? $this->answer_en : $this->answer;
    }
}
