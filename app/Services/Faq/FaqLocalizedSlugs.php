<?php

namespace App\Services\Faq;

use App\Models\Faq;
use Carbon\CarbonImmutable;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class FaqLocalizedSlugs
{
    private const LOCALES = ['th', 'en'];
    private const MAX_CANDIDATES = 32;

    private function Base(string $question): string
    {
        $base = mb_strtolower(strip_tags($question), 'UTF-8');
        $base = preg_replace('/[^\p{L}\p{M}\p{N}]+/u', '-', $base) ?? '';
        $base = trim(mb_substr(trim($base, '-'), 0, 120, 'UTF-8'), '-');
        return $base === '' ? 'faq' : $base;
    }

    private function Deadline(CarbonImmutable $retired_at): CarbonImmutable
    {
        return $retired_at->setTimezone('Asia/Bangkok')->addYearNoOverflow()->utc();
    }

    private function Stamp(CarbonImmutable $instant): string
    {
        return $instant->utc()->format('Y-m-d H:i:s.u');
    }

    private function PositiveInteger(mixed $number): int
    {
        $text = (string) $number;
        if (!preg_match('/^[1-9][0-9]*$/D', $text)
            || (string) (int) $text !== $text || (int) $text < 1) {
            throw new UnavailableFaqUrls('faq.urls.integer');
        }
        return (int) $text;
    }

    public function Read(Collection $faqs, bool $require_complete = true): array
    {
        if ($faqs->isEmpty()) return [];
        $faq_ids = $faqs->pluck('id')->all();
        $states = DB::table('faq_localized_slug_states')->whereIn('faq_id', $faq_ids)->sharedLock()->get()->groupBy('faq_id');
        $routes = DB::table('faq_slug_routes')->whereIn('faq_id', $faq_ids)
            ->where('current_marker', 1)->sharedLock()->get()->groupBy('faq_id');
        $result = [];
        foreach ($faqs as $faq) {
            $faq_states = $states->get($faq->id, collect());
            $faq_routes = $routes->get($faq->id, collect());
            if ($faq_states->isEmpty() && $faq_routes->isEmpty() && !$require_complete) {
                $result[$faq->id] = ['th' => null, 'en' => null];
                continue;
            }
            $result[$faq->id] = $this->ValidateState($faq, $faq_states, $faq_routes, true);
        }
        return $result;
    }

    private function ValidateState(Faq $faq, Collection $states, Collection $routes, bool $check_sources): array
    {
        if ($states->count() !== 2 || $routes->count() !== 2
            || $states->pluck('locale')->sort()->values()->all() !== ['en', 'th']
            || $routes->pluck('locale')->sort()->values()->all() !== ['en', 'th']
            || !is_string($faq->slug) || $faq->slug === '') {
            throw new UnavailableFaqUrls('faq.urls.state');
        }
        $states = $states->keyBy('locale');
        $routes = $routes->keyBy('locale');
        $slugs = [];
        foreach (self::LOCALES as $locale) {
            $state = $states[$locale];
            $route = $routes[$locale];
            $revision = $this->PositiveInteger($state->last_revision);
            if (!is_string($state->source_base) || $state->source_base === ''
                || mb_strlen($state->source_base) > 120
                || $this->PositiveInteger($route->revision) !== $revision
                || $route->slug !== $state->source_base.'-'.$faq->id.'-'.$revision
                || $route->retired_at !== null || $route->expires_at !== null
                || ($check_sources && $state->source_base !== $this->Base($faq->LocalizedQuestion($locale)))) {
                throw new UnavailableFaqUrls('faq.urls.state');
            }
            $slugs[$locale] = $route->slug;
        }
        return $slugs;
    }

    private function Allocate(Faq $faq, string $locale, string $base, int $previous_revision): array
    {
        for ($attempt = 0; $attempt < self::MAX_CANDIDATES; $attempt++) {
            if ($previous_revision === PHP_INT_MAX) {
                throw new UnavailableFaqUrls('faq.urls.revision_overflow');
            }
            $revision = ++$previous_revision;
            $candidate = $base.'-'.$faq->id.'-'.$revision;
            if (DB::table('faqs')->where('slug', $candidate)->exists()) continue;
            try {
                $route_id = DB::transaction(fn (): int => DB::table('faq_slug_routes')->insertGetId([
                    'faq_id' => $faq->id, 'locale' => $locale, 'slug' => $candidate,
                    'revision' => $revision, 'current_marker' => 1,
                    'retired_at' => null, 'expires_at' => null,
                ]));
                return ['id' => $route_id, 'slug' => $candidate, 'revision' => $revision];
            } catch (UniqueConstraintViolationException $exception) {
                $message = $exception->getMessage();
                if (!str_contains($message, 'faq_route_identity_unique')
                    && !str_contains($message, 'faq_slug_routes.locale, faq_slug_routes.slug')) {
                    throw $exception;
                }
            }
        }
        throw new UnavailableFaqUrls('faq.urls.allocation_exhausted');
    }

    public function Initialize(Faq $faq): bool
    {
        if (DB::transactionLevel() < 1) throw new UnavailableFaqUrls('faq.urls.transaction');
        $states = DB::table('faq_localized_slug_states')->where('faq_id', $faq->id)->get();
        if ($states->isNotEmpty()) {
            $this->Read(collect([$faq]));
            return false;
        }
        if (DB::table('faq_slug_routes')->where('faq_id', $faq->id)->exists()) {
            throw new UnavailableFaqUrls('faq.urls.partial_initialization');
        }
        $now = CarbonImmutable::now('UTC');
        $current = [];
        foreach (self::LOCALES as $locale) {
            $base = $this->Base($faq->LocalizedQuestion($locale));
            $current[$locale] = $this->Allocate($faq, $locale, $base, 0);
            DB::table('faq_localized_slug_states')->insert([
                'faq_id' => $faq->id, 'locale' => $locale,
                'last_revision' => $current[$locale]['revision'],
                'source_base' => $base, 'initialized_at' => $this->Stamp($now),
            ]);
        }
        if (!is_string($faq->slug) || $faq->slug === '') {
            DB::table('faqs')->where('id', $faq->id)->update(['slug' => $current['en']['slug']]);
            $faq->setAttribute('slug', $current['en']['slug']);
        }
        foreach (self::LOCALES as $locale) {
            if ($faq->slug === $current[$locale]['slug']) continue;
            DB::table('faq_slug_routes')->insert([
                'faq_id' => $faq->id, 'locale' => $locale, 'slug' => $faq->slug,
                'revision' => null, 'current_marker' => null,
                'retired_at' => $this->Stamp($now),
                'expires_at' => $this->Stamp($this->Deadline($now)),
            ]);
        }
        return true;
    }

    public function Synchronize(Faq $faq): void
    {
        if (DB::transactionLevel() < 1) throw new UnavailableFaqUrls('faq.urls.transaction');
        $states = DB::table('faq_localized_slug_states')->where('faq_id', $faq->id)->get();
        if ($states->isEmpty()) {
            $this->Initialize($faq);
            return;
        }
        $routes = DB::table('faq_slug_routes')->where('faq_id', $faq->id)
            ->where('current_marker', 1)->get();
        $this->ValidateState($faq, $states, $routes, false);
        $states = $states->keyBy('locale');
        $routes = $routes->keyBy('locale');
        $now = CarbonImmutable::now('UTC');
        foreach (self::LOCALES as $locale) {
            $base = $this->Base($faq->LocalizedQuestion($locale));
            $state = $states[$locale];
            if ($base === $state->source_base) continue;
            DB::table('faq_slug_routes')->where('id', $routes[$locale]->id)->update([
                'current_marker' => null, 'retired_at' => $this->Stamp($now),
                'expires_at' => $this->Stamp($this->Deadline($now)),
            ]);
            $current = $this->Allocate($faq, $locale, $base, $this->PositiveInteger($state->last_revision));
            DB::table('faq_localized_slug_states')->where('faq_id', $faq->id)->where('locale', $locale)->update([
                'source_base' => $base, 'last_revision' => $current['revision'],
            ]);
        }
    }
}
