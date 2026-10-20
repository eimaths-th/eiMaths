<?php

namespace App\Services\Faq;

use App\Models\Faq;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

final class FaqPageResolver
{
    public function __construct(private readonly FaqPublicContent $content,
        private readonly FaqLocalizedSlugs $localized_slugs)
    {
    }

    public function ById(int $faq_id, string $locale): array
    {
        return DB::transaction(function () use ($faq_id, $locale): array {
            $faq = $this->content->Query()->where('faqs.id', $faq_id)->without('category')->sharedLock()->first();
            abort_if($faq === null, 404);
            return $this->Record($faq, $locale);
        }, 3);
    }

    private function Record(Faq $faq, string $locale): array
    {
        $faq->setRelation('category', \App\Models\FaqCategory::query()->whereKey($faq->category_id)->sharedLock()->firstOrFail());
        $slugs = $this->localized_slugs->Read(collect([$faq]));
        return $this->content->Serialize($faq, $locale, $slugs[$faq->id]);
    }

    public function Resolve(string $slug, string $locale): array
    {
        abort_unless(mb_strlen($slug) <= 255
            && preg_match('/^[\p{L}\p{M}\p{N}]+(?:-[\p{L}\p{M}\p{N}]+)*$/uD', $slug), 404);
        return DB::transaction(function () use ($slug, $locale): array {
            $locator = DB::table('faq_slug_routes')->where('locale', $locale)->where('slug', $slug)->first();
            abort_if($locator === null || $locator->slug !== $slug, 404);
            // Acquire the same ownership lock as writers before reading authoritative route state.
            $faq = $this->content->Query()->where('faqs.id', $locator->faq_id)->without('category')->sharedLock()->first();
            abort_if($faq === null, 404);
            $route = DB::table('faq_slug_routes')->where('faq_id', $faq->id)
                ->where('locale', $locale)->where('slug', $slug)->sharedLock()->first();
            abort_if($route === null || $route->slug !== $slug, 404);
            $is_current = $route->current_marker !== null;
            $deadline = null;
            if ($is_current) {
                if ((int) $route->current_marker !== 1 || $route->retired_at !== null || $route->expires_at !== null) {
                    throw new UnavailableFaqUrls('faq.urls.current_route');
                }
            } else {
                if (!is_string($route->retired_at) || !is_string($route->expires_at)) {
                    throw new UnavailableFaqUrls('faq.urls.alias_times');
                }
                $deadline = CarbonImmutable::parse($route->expires_at, 'UTC');
                abort_if(CarbonImmutable::now('UTC')->greaterThanOrEqualTo($deadline), 404);
            }
            $record = $this->Record($faq, $locale);
            $canonical_slug = $record['localized_slugs'][$locale];
            if (($is_current && $canonical_slug !== $slug) || (!$is_current && $canonical_slug === $slug)) {
                throw new UnavailableFaqUrls('faq.urls.resolution_identity');
            }
            return ['requested_slug' => $slug, 'locale' => $locale,
                'match_type' => $is_current ? 'current' : 'alias',
                'alias_expires_at' => $deadline?->toISOString(), 'faq' => $record];
        }, 3);
    }
}
