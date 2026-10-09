<?php

namespace App\Domain\Ebook;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

final class DocumentValidator
{
    public function validate(array $document, int $studentId, object $page): array
    {
        abort_if(strlen(json_encode($document)) > 1500000, 422, 'หน้านี้มีชิ้นงานมากเกินไป');
        $rules = [
            'schema' => 'required|integer|in:1',
            'objects' => 'present|array|max:400',
            'objects.*' => 'array:id,type,x,y,w,h,rotation,color,strokeWidth,points,text,sourcePageId,sourceRect',
            'objects.*.id' => 'required|uuid|distinct',
            'objects.*.type' => ['required', Rule::in(['pen', 'highlight', 'text', 'rect', 'ellipse', 'clip'])],
            'objects.*.x' => 'required|numeric|between:-1,2', 'objects.*.y' => 'required|numeric|between:-1,2',
            'objects.*.w' => 'required|numeric|between:0.001,2', 'objects.*.h' => 'required|numeric|between:0.001,2',
            'objects.*.rotation' => 'required|numeric|between:-360,360',
            'objects.*.color' => 'required|integer|between:0,4294967295',
            'objects.*.strokeWidth' => 'required|numeric|between:0.0001,0.1',
            'objects.*.text' => 'sometimes|nullable|string|max:500',
            'objects.*.points' => 'sometimes|array|max:5000',
            'objects.*.points.*' => 'array|size:2',
            'objects.*.points.*.*' => 'numeric|between:0,1',
            'objects.*.sourcePageId' => 'sometimes|integer|min:1',
            'objects.*.sourceRect' => 'sometimes|array|size:4',
            'objects.*.sourceRect.*' => 'numeric|between:0,1',
        ];
        $valid = Validator::make($document, $rules)->validate();
        $pointCount = 0;
        foreach ($valid['objects'] as $object) {
            $pointCount += count($object['points'] ?? []);
            if (in_array($object['type'], ['pen', 'highlight'], true)) {
                abort_unless(count($object['points'] ?? []) >= 1, 422);
            }
            if ($object['type'] === 'text') {
                abort_unless(isset($object['text']) && trim($object['text']) !== '', 422);
            }
            if ($object['type'] === 'clip') {
                abort_unless(isset($object['sourcePageId'], $object['sourceRect']), 422);
                $source = app(BookAccess::class)->page($studentId, $object['sourcePageId']);
                abort_unless($source->book_id === $page->book_id && $source->version_id === $page->version_id, 422, 'ตัดปะได้ภายในหนังสือฉบับเดียวกัน');
                [$x, $y, $w, $h] = $object['sourceRect'];
                abort_unless($w > 0 && $h > 0 && $x + $w <= 1.001 && $y + $h <= 1.001, 422);
            }
        }
        abort_if($pointCount > 60000, 422);
        return $valid;
    }
}
