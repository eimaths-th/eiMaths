<?php

namespace App\Http\Controllers;

use App\Models\Test;
use App\Models\Answer;
use Illuminate\Contracts\View\View;

class ResultController extends Controller
{
    public function show(Test $test): View
    {
        $questions_count = $test->quiz->questions()->count();

        $results = Answer::where('test_id', $test->id)
            ->with('question.options')
            ->get();
        // dd($results);
        $header = 'Results';
        return view('home.result', compact('test', 'questions_count', 'results','header'));
    }
}
