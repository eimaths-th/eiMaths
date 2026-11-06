<?php

namespace App\Domain\Elearning;

use App\Models\{Answer, Test};

final class QuizResultReview
{
    public function Build(Test $test, bool $can_view_solution_videos): array
    {
        return [
            'test' => $test,
            'questions_count' => $test->quiz->questions()->count(),
            'results' => Answer::where('test_id', $test->id)->with('question.options')->get(),
            'header' => 'Results',
            'canViewSolutionVideos' => $can_view_solution_videos,
        ];
    }
}
