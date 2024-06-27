<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Option;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\Test;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public array $answersOfQuestions = [];
    // public array $answersOfWrittens = [];

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
        // dd($currentQuestion);
        return view('home.quiz-show', [
            'quiz' => $quiz,
            'currentQuestionIndex' => $currentQuestionIndex,
            'questionsCount' => $questionsCount,
            'currentQuestion' => $currentQuestion,
            'startTimeInSeconds' => $startTimeInSeconds,
            'answersOfQuestions' => $this->answersOfQuestions,
            // 'answersOfWrittens' => $this->answersOfWrittens,
            'header' => $header,
        ]);
    }

    public function nextQuestion(Request $request, Quiz $quiz)
    {

        // dd([
        //     'request' => $request->all(),
        //     // 'answersOfQuestions' => $answersOfQuestions,
        //     // 'answersOfWrittens' => $answersOfWrittens
        // ]);
        $currentQuestionIndex = $request->input('currentQuestionIndex', 0);
        $questionsCount = $quiz->questions()->count();
        $answerOfQuestion = $request->input('answerOfQuestion.' . $currentQuestionIndex);

        $currentQuestionId = $request->input('currentQuestionId');
        $currentQuestionType = $request->input('currentQuestionType');
        $answerOfWritten = $request->input('answerOfWritten.' . $currentQuestionId);

        $answersOfQuestions = session()->get('answersOfQuestions', []);
        $answersOfQuestions[$currentQuestionIndex]['question_id'] = $currentQuestionId;
        $answersOfQuestions[$currentQuestionIndex]['answer'] = $answerOfQuestion;
        $answersOfQuestions[$currentQuestionIndex]['type'] = $currentQuestionType;
        session()->put('answersOfQuestions', $answersOfQuestions);

        // $answersOfWrittens = session()->get('answersOfWrittens', []);
        // $answersOfWrittens[$currentQuestionId] = $answerOfWritten;
        // session()->put('answersOfWrittens', $answersOfWrittens);



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
        $startTimeInSeconds = session('startTimeInSeconds');
        $time_spent = now()->timestamp - $startTimeInSeconds;
        $result = 0;

        // Create a new test record
        $test = Test::create([
            'user_id' => auth()->id(),
            'quiz_id' => $quiz->id,
            'result' => 0,
            'ip_address' => request()->ip(),
            'time_spent' => $time_spent
        ]);

        // Process multiple-choice answers
        $answersOfQuestions = session()->get('answersOfQuestions', []);
        // dd($answersOfQuestions);
        foreach ($answersOfQuestions as $key => $answerData) {
            $question = Question::find($answerData['question_id']);
            $answer = $answerData['answer'];
            if ($answerData['type'] === 'options') {
                // Check if the selected option is correct
                $option = Option::find($answer);
                $correct = !empty($option) && $option->correct;
            } elseif ($answerData['type'] === 'written') {
                // Check if the written answer matches the correct answer
                $correct = !empty($answer) && strtolower($answer) === strtolower($question->written_answer);
            }

            if ($correct) {
                $result++;
            }

            Answer::create([
                'user_id' => auth()->id(),
                'test_id' => $test->id,
                'question_id' => $question->id,
                'option_id' => $answerData['type'] === 'options' ? $answer : null,
                'written_answer' => $answerData['type'] === 'written' ? $answer : null,
                'correct' => $correct ? 1 : 0
            ]);
        }
        // foreach ($answersOfQuestions as $key => $optionId) {
        //     $question = $quiz->questions[$key];
        //     $correct = !empty($optionId) && Option::find($optionId)->correct;

        //     if ($correct) {
        //         $result++;
        //     }

        //     Answer::create([
        //         'user_id' => auth()->id(),
        //         'test_id' => $test->id,
        //         'question_id' => $question->id,
        //         'option_id' => $optionId,
        //         'correct' => $correct ? 1 : 0
        //     ]);
        // }

        // Process written answers
        // $answersOfWrittens = session()->get('answersOfWrittens', []);
        // foreach ($answersOfWrittens as $questionId => $writtenAnswer) {
        //     $question = $quiz->questions->find($questionId);
        //     // $question = Question::find($questionId);
        //     $correct = $writtenAnswer === $question->written_answer;

        //     if ($correct) {
        //         $result++;
        //     }

        //     Answer::create([
        //         'user_id' => auth()->id(),
        //         'test_id' => $test->id,
        //         'question_id' => $questionId,
        //         'written_answer' => $writtenAnswer,
        //         'correct' => $correct ? 1 : 0
        //     ]);
        // }

        // Update the result of the test
        $test->update([
            'result' => $result
        ]);

        // Clear session data after submission
        session()->forget('startTimeInSeconds');
        session()->forget('answersOfQuestions');
        session()->forget('answersOfWrittens');

        return redirect()->route('results.show', ['test' => $test]);
    }


    // public function submit(Request $request, Quiz $quiz)
    // {
    //     $startTimeInSeconds = session('startTimeInSeconds');

    //     $time_spent = now()->timestamp - $startTimeInSeconds;
    //     $result = 0;

    //     // dd($request);

    //     $test = Test::create([
    //         'user_id' => auth()->id(),
    //         'quiz_id' => $quiz->id,
    //         'result' => 0,
    //         'ip_address' => request()->ip(),
    //         'time_spent' => $time_spent
    //     ]);



    //     foreach ($this->answersOfQuestions as $key => $optionId) {
    //         if (!empty($optionId) && Option::find($optionId)->correct) {
    //             $result++;
    //             Answer::create([
    //                 'user_id' => auth()->id(),
    //                 'test_id' => $test->id,
    //                 'question_id' => $quiz->questions[$key]->id,
    //                 'option_id' => $optionId,
    //                 'correct' => 1
    //             ]);
    //         } else {
    //             Answer::create([
    //                 'user_id' => auth()->id(),
    //                 'test_id' => $test->id,
    //                 'option_id' => $optionId,
    //                 'question_id' => $quiz->questions[$key]->id,
    //                 'correct' => 0
    //             ]);
    //         }
    //     }

    //     $test->update([
    //         'result' => $result
    //     ]);

    //     // Clear answersOfQuestions property and startTimeInSeconds session after submission
    //     session()->forget('startTimeInSeconds');
    //     session()->forget('answersOfQuestions');
    //     session()->forget('answersOfWrittens');
    //     $this->answersOfQuestions = [];
    //     $this->answersOfWrittens = [];

    //     return redirect()->route('results.show', ['test' => $test]);
    // }
}
