<?php

namespace App\Http\Controllers\Affiliate;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AffiliateGuideController extends Controller
{
    private const CURRICULUM_GROUPS = [
        'kindergarten' => [
            ['slug' => 'k1', 'code' => 'K1', 'pages' => 7],
            ['slug' => 'k2', 'code' => 'K2', 'pages' => 6],
        ],
        'primary' => [
            ['slug' => 'p1', 'code' => 'P1', 'pages' => 7],
            ['slug' => 'p2', 'code' => 'P2', 'pages' => 4],
            ['slug' => 'p3', 'code' => 'P3', 'pages' => 4],
            ['slug' => 'p4', 'code' => 'P4', 'pages' => 4],
            ['slug' => 'p5', 'code' => 'P5', 'pages' => 4],
            ['slug' => 'p6', 'code' => 'P6', 'pages' => 3],
        ],
    ];

    public function Index(): View
    {
        return view('affiliate.guide.index', [
            'curriculum_groups' => self::CURRICULUM_GROUPS,
            'curriculum_count' => array_sum(array_map('count', self::CURRICULUM_GROUPS)),
        ]);
    }

    public function Document(): BinaryFileResponse
    {
        $document_path = resource_path('documents/affiliate/understanding-summary.pdf');

        abort_unless(is_file($document_path), 404);

        return response()->file($document_path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="understanding-summary.pdf"',
        ]);
    }

    public function Curriculum(string $level): BinaryFileResponse
    {
        $curriculum = $this->FindCurriculum(strtolower($level));

        abort_unless($curriculum !== null, 404);

        $document_path = resource_path('documents/affiliate/curricula/' . $curriculum['code'] . '.pdf');

        abort_unless(is_file($document_path), 404);

        return response()->file($document_path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $curriculum['code'] . '.pdf"',
        ]);
    }

    private function FindCurriculum(string $level): ?array
    {
        foreach (self::CURRICULUM_GROUPS as $curricula) {
            foreach ($curricula as $curriculum) {
                if ($curriculum['slug'] === $level) {
                    return $curriculum;
                }
            }
        }

        return null;
    }
}
