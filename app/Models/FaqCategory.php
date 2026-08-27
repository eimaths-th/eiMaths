<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FaqCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_en',
        'description',
        'description_en',
        'slug',
        'position',
        'is_published',
    ];

    protected $casts = [
        'position' => 'integer',
        'is_published' => 'boolean',
    ];

    public function faqs(): HasMany
    {
        return $this->hasMany(Faq::class, 'category_id');
    }

    public function LocalizedName(string $locale): string
    {
        return $locale === 'en' && filled($this->name_en) ? $this->name_en : $this->name;
    }

    public function LocalizedDescription(string $locale): ?string
    {
        if ($locale === 'en' && filled($this->description_en)) {
            return $this->description_en;
        }

        return $this->description;
    }
}
