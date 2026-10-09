<?php

namespace App\Domain\Ebook;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class BookCatalog
{
    /** Expand the existing registration sets; do not create another book-code master. */
    public function volumes(): Collection
    {
        $sets = DB::table('bookuses as b')->join('levels as l', 'l.id', '=', 'b.level_id')
            ->join('terms as t', 't.id', '=', 'b.term_id')->where('b.status', 1)->orderBy('b.id')
            ->get(['b.*', 'l.name as level', 't.name as term']);
        $volumes = [];
        foreach ($sets as $set) {
            foreach ($this->codes($set->name) as $code) {
                $key = $set->level_id.':'.$set->term_id.':'.$code;
                $volumes[$key] ??= [
                    'key' => $key, 'code' => $code, 'level_id' => (int) $set->level_id,
                    'term_id' => (int) $set->term_id, 'level' => $set->level, 'term' => $set->term,
                    'catalog_ids' => [], 'sets' => [],
                ];
                $volumes[$key]['catalog_ids'][] = (int) $set->id;
                $volumes[$key]['sets'][] = $set->name;
            }
        }
        return collect($volumes)->values();
    }

    public function selected(string $key): array
    {
        $volumes = $this->volumes();
        $volume = $volumes->firstWhere('key', $key);
        if (!$volume || $volumes->where('code', $volume['code'])->count() !== 1) {
            throw ValidationException::withMessages(['catalog_key' => __('ebooks.messages.catalog_invalid')]);
        }
        return $volume;
    }

    private function codes(string $name): array
    {
        $name = strtoupper(trim($name));
        if (preg_match('/^([A-Z]+)([0-9]+)\s*-\s*\1([0-9]+)$/', $name, $m)) {
            $first = (int) $m[2]; $last = (int) $m[3];
            if ($last < $first || $last - $first > 200) { return []; }
            $width = strlen($m[2]);
            return array_map(fn ($n) => $m[1].str_pad((string) $n, $width, '0', STR_PAD_LEFT), range($first, $last));
        }
        return preg_match('/^[A-Z]+[0-9]+$/', $name) ? [$name] : [];
    }
}
