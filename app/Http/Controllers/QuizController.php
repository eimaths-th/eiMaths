<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Option;
use App\Models\Quiz;
use App\Models\Test;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public array $answersOfQuestions = [];

    public function show(Quiz $quiz, Request $request)
    {
        $currentQuestionIndex = $request->input('currentQuestionIndex', 0);
        $questionsCount = $quiz->questions()->count();
        $currentQuestion = $quiz->questions()->skip($currentQuestionIndex)->first();

        // Store start time in session if it's not already set
        if (!session()->has('startTimeInSeconds')) {
            session(['startTimeInSeconds' => now()->timestamp]);
        }
        $startTimeInSeconds = session('startTimeInSeconds');

        if (!$currentQuestion) {
            abort(404, 'No questions found for this quiz');
        }

        $header = 'Quiz: ' . $quiz->title;

        return view('home.quiz-show', [
            'quiz' => $quiz,
            'currentQuestionIndex' => $currentQuestionIndex,
            'questionsCount' => $questionsCount,
            'currentQuestion' => $currentQuestion,
            'startTimeInSeconds' => $startTimeInSeconds,
            'answersOfQuestions' => $this->answersOfQuestions, // Pass $answersOfQuestions to the view
            'header' => $header,
        ]);
    }

    public function nextQuestion(Request $request, Quiz $quiz)
    {
        $currentQuestionIndex = $request->input('currentQuestionIndex', 0);
        $questionsCount = $quiz->questions()->count();
        $answerOfQuestion = $request->input('answerOfQuestion.' . $currentQuestionIndex);

        // Retrieve and update answers from the session
        $this->answersOfQuestions = session()->get('answersOfQuestions', []);
        $this->answersOfQuestions[$currentQuestionIndex] = $answerOfQuestion;
        session()->put('answersOfQuestions', $this->answersOfQuestions);

        if ($currentQuestionIndex >= $questionsCount - 1) {
            return $this->submit($request, $quiz);
        }

        $nextQuestionIndex = $currentQuestionIndex + 1;

        return redirect()->route('quiz.show', [
            'quiz' => $quiz,
            'currentQuestionIndex' => $nextQuestionIndex,
        ])->withInput($request->except('_token'));
    }

    public function submit(Request $request, Quiz $quiz)
    {
        // Retrieve start time from session
        $startTimeInSeconds = session('startTimeInSeconds');

        $time_spent = now()->timestamp - $startTimeInSeconds;
        $result = 0;

        $test = Test::create([
            'user_id' => auth()->id(),
            'quiz_id' => $quiz->id,
            'result' => 0,
            'ip_address' => request()->ip(),
            'time_spent' => $time_spent
        ]);

        foreach ($this->answersOfQuestions as $key => $optionId) {
            if (!empty($optionId) && Option::find($optionId)->correct) {
                $result++;
                Answer::create([
                    'user_id' => auth()->id(),
                    'test_id' => $test->id,
                    'question_id' => $quiz->questions[$key]->id,
                    'option_id' => $optionId,
                    'correct' => 1
                ]);
            } else {
                Answer::create([
                    'user_id' => auth()->id(),
                    'test_id' => $test->id,
                    'option_id' => $optionId,
                    'question_id' => $quiz->questions[$key]->id,
                    'correct' => 0
                ]);
            }
        }

        $test->update([
            'result' => $result
        ]);

        // Clear answersOfQuestions property and startTimeInSeconds session after submission
        session()->forget('startTimeInSeconds');
        session()->forget('answersOfQuestions');
        $this->answersOfQuestions = [];

        return redirect()->route('results.show', ['test' => $test]);
    }
}

