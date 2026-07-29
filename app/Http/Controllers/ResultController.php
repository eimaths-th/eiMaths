<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\ContactQuiz;
use App\Models\Test;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class ResultController extends Controller
{
    public function show(Test $test): View
    {
        // Back-office users (Admin/Manager) may review solution videos on result pages.
        $canViewSolutionVideos = Auth::guard('web')->check();

        if (Auth::guard('student')->check()) {
            $studentId = (int) Auth::guard('student')->id();

            abort_unless(
                $test->type_user === 'student' && (int) $test->user_id === $studentId,
                403
            );

            $canViewSolutionVideos = ContactQuiz::query()
                ->where('contact_id', $studentId)
                ->where('quiz_id', $test->quiz_id)
                ->exists();
        }

        $questions_count = $test->quiz->questions()->count();

        $results = Answer::where('test_id', $test->id)
            ->with('question.options')
            ->get();

        $header = 'Results';

        return view('home.result', compact(
            'test',
            'questions_count',
            'results',
            'header',
            'canViewSolutionVideos'
        ));
    }
}
