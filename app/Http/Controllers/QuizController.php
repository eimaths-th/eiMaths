<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Contact;
use App\Models\ImageOption;
use App\Models\Option;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\Test;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;


//new version

class QuizController extends Controller
{
    public function saveAnswer(Request $request)
    {
        dd($request->all());
        $answersOfQuestions = session()->get('answersOfQuestions', []);

        $answersOfQuestions[$request->currentQuestionIndex] = [
            'question_id' => $request->question_id,
            'answer' => $request->answer,
            'type' => $request->type,
        ];

        session()->put('answersOfQuestions', $answersOfQuestions);

        return response()->json(['message' => 'Answer saved successfully']);
    }
    public function show(Quiz $quiz, Request $request)
    {
        // dd($request->all());
        $currentQuestionIndex = (int) $request->input('currentQuestionIndex', 0);
        $questions = $quiz->questions();
        $questionsCount = $questions->count();
        $currentQuestion = $questions->skip($currentQuestionIndex)->first();

        if (!$currentQuestion) {
            abort(404, 'No questions found for this quiz');
        }

        // Store start time in session if not set
        // Store start time if not set
        if (!session()->has('quizStartTime')) {
            session(['quizStartTime' => now()->timestamp]);
        }
        if (!session()->has('startTimeInSeconds')) {
            session(['startTimeInSeconds' => now()->timestamp]);
        }

        $quizStartTime = session('quizStartTime');
        $startTimeInSeconds = session('startTimeInSeconds');
        session()->put('quizStartTime', session('quizStartTime', now()->timestamp));
        session()->put('startTimeInSeconds', session('startTimeInSeconds', now()->timestamp));

        $answersOfQuestions = session('answersOfQuestions', []);
        $correctAnswer = $this->getCorrectAnswer($currentQuestion);

        return view('home.quiz-show', compact(
            'quiz',
            'currentQuestionIndex',
            'questionsCount',
            'currentQuestion',
            'answersOfQuestions',
            'correctAnswer',
            'quizStartTime',
            'startTimeInSeconds',
        ))->with('header', 'Quiz: ' . $quiz->title);
    }

    public function nextQuestion(Request $request, Quiz $quiz)
    {
        dd($request->all());
        $currentQuestionIndex = (int) $request->input('currentQuestionIndex', 0);
        $answerOfQuestion = $request->input("answerOfQuestion.$currentQuestionIndex");
        $currentQuestionId = $request->input('currentQuestionId');
        $currentQuestionType = $request->input('currentQuestionType');
        $currentQuestionFracType = $request->input('currentQuestionFracType');
        $currentQuestionFracAnswerType = $request->input('currentQuestionFracAnswerType');
        // Store answers in session
        $answersOfQuestions = session()->get('answersOfQuestions', []);
        if ($currentQuestionType === 'fraction') {

            # code...
            if ($currentQuestionFracType === 'written') {
                # code...
                $currentQuestionFracNumerator = $request->input('currentQuestionFracNumerator');
                $currentQuestionFracDenominator = $request->input('currentQuestionFracDenominator');
                if ($request->input('currentQuestionFracWhole')) {
                    # code...
                    $whole  = $request->input('currentQuestionFracWhole');
                    $currentQuestionFracNumerator = $whole * $currentQuestionFracDenominator + $currentQuestionFracNumerator;
                }
                $answersOfQuestions[$currentQuestionIndex] = [
                    'question_id' => $currentQuestionId,
                    'type' => $currentQuestionType,
                    'frac_type' => $currentQuestionFracType,
                    'answer' => [
                        'numerator' => $currentQuestionFracNumerator,
                        'denominator' => $currentQuestionFracDenominator,
                    ],
                    'answer_type' => $currentQuestionFracAnswerType,
                ];
            } else {
                # code...
                $answersOfQuestions[$currentQuestionIndex] = [
                    'question_id' => $currentQuestionId,
                    'answer' => $answerOfQuestion,
                    'type' => $currentQuestionType,
                ];
            }
        } else {
            # code...
            $answersOfQuestions[$currentQuestionIndex] = [
                'question_id' => $currentQuestionId,
                'answer' => $answerOfQuestion,
                'type' => $currentQuestionType,
            ];
        }

        session()->put('answersOfQuestions', $answersOfQuestions);

        // Redirect to the next question or submit if it was the last one
        $questionsCount = $quiz->questions()->count();
        if ($currentQuestionIndex >= $questionsCount - 1) {
            return $this->submit($request, $quiz);
        }

        return redirect()->route('quiz.show', [
            'quiz' => $quiz,
            'currentQuestionIndex' => $currentQuestionIndex + 1
        ])->withInput($request->except('_token'));
    }

    public function submit(Request $request, Quiz $quiz)
    {
        $answersOfQuestions = session('answersOfQuestions', []);

        dd($request->all(), $answersOfQuestions);
        $startTimeInSeconds = session('startTimeInSeconds', now()->timestamp);
        $timeSpent = now()->timestamp - $startTimeInSeconds;
        $result = 0;

        // Identify the user
        $user = Auth::guard('student')->user() ?? Auth::user();
        $typeUser = $user instanceof Contact ? 'student' : 'user';
        $typeReturn = ($user instanceof Contact && optional($user->contact)->type === 'demo') ? 0 : 1;

        // Create Test record
        $test = Test::create([
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
            'result' => 0,
            'ip_address' => $request->ip(),
            'time_spent' => $timeSpent,
            'type_user' => $typeUser,
        ]);

        // Process answers and store results
        foreach ($answersOfQuestions as $answerData) {
            $question = Question::find($answerData['question_id']);
            $correct = $this->checkAnswer($question, $answerData);

            if ($correct) {
                $result++;
            }

            Answer::create([
                'user_id' => $user->id,
                'test_id' => $test->id,
                'question_id' => $question->id,
                'option_id' => $answerData['type'] === 'options' ? $answerData['answer'] : null,
                'image_id' => $answerData['type'] === 'image' ? $answerData['answer'] : null,
                'written_answer' => $answerData['type'] === 'written' ? $answerData['answer'] : null,
                'correct' => $correct ? 1 : 0,
                'type_user' => $typeUser,
            ]);
        }

        // Update test result
        $test->update(['result' => $result]);

        // Clear session
        session()->forget(['quizStartTime', 'startTimeInSeconds', 'answersOfQuestions']);

        return $typeReturn == 0
            ? redirect()->route('demo.thank_you')
            : redirect()->route('results.show', ['test' => $test]);
    }

    private function getCorrectAnswer(Question $question)
    {
        return match ($question->type) {
            'options' => optional($question->options()->where('correct', true)->first())->id,
            'image' => optional($question->images()->where('correct', true)->first())->id,
            'written' => $question->written_answer,
            default => null,
        };
    }

    private function checkAnswer(Question $question, array $answerData)
    {
        return match ($answerData['type']) {
            'options' => optional(Option::find($answerData['answer']))->correct,
            'written' => strtolower($answerData['answer']) === strtolower($question->written_answer),
            'image' => optional(ImageOption::find($answerData['answer']))->correct,
            default => false,
        };
    }
}



//next version
// class QuizController extends Controller
// {
//     public function show(Quiz $quiz, Request $request)
//     {
//         $currentQuestionIndex = $request->input('currentQuestionIndex', 0);
//         $questionsCount = $quiz->questions()->count();
//         $currentQuestion = $quiz->questions()->skip($currentQuestionIndex)->first();

//         if (!$currentQuestion) {
//             abort(404, 'No questions found for this quiz');
//         }

        // // Store start time if not set
        // if (!session()->has('quizStartTime')) {
        //     session(['quizStartTime' => now()->timestamp]);
        // }
        // if (!session()->has('startTimeInSeconds')) {
        //     session(['startTimeInSeconds' => now()->timestamp]);
        // }

//         $quizStartTime = session('quizStartTime');
//         $startTimeInSeconds = session('startTimeInSeconds');

//         // Get correct answer based on question type
//         $correctAnswer = $this->getCorrectAnswer($currentQuestion);

//         return view('home.quiz-show', [
//             'quiz' => $quiz,
//             'currentQuestionIndex' => $currentQuestionIndex,
//             'questionsCount' => $questionsCount,
//             'currentQuestion' => $currentQuestion,
//             'startTimeInSeconds' => $startTimeInSeconds,
//             'answersOfQuestions' => session('answersOfQuestions', []),
//             'correctAnswer' => $correctAnswer,
//             'quizStartTime' => $quizStartTime,
//             'header' => 'Quiz: ' . $quiz->title,
//         ]);
//     }

//     public function nextQuestion(Request $request, Quiz $quiz)
//     {
//         $currentQuestionIndex = $request->input('currentQuestionIndex', 0);
//         $currentQuestionId = $request->input('currentQuestionId');
//         $currentQuestionType = $request->input('currentQuestionType');
//         $answerOfQuestion = $request->input('answerOfQuestion.' . $currentQuestionIndex);

//         // Store answers in session
//         $answersOfQuestions = session()->get('answersOfQuestions', []);
//         $answersOfQuestions[$currentQuestionIndex] = [
//             'question_id' => $currentQuestionId,
//             'answer' => $answerOfQuestion,
//             'type' => $currentQuestionType,
//         ];
//         session()->put('answersOfQuestions', $answersOfQuestions);

//         // Get total questions count
//         $questionsCount = $quiz->questions()->count();
//         if ($currentQuestionIndex >= $questionsCount - 1) {
//             return $this->submit($request, $quiz);
//         }

//         return redirect()->route('quiz.show', [
//             'quiz' => $quiz,
//             'currentQuestionIndex' => $currentQuestionIndex + 1,
//             'quizStartTime' => session('quizStartTime'),
//         ])->withInput($request->except('_token'));
//     }

//     public function submit(Request $request, Quiz $quiz)
//     {
//         $startTimeInSeconds = session('startTimeInSeconds');
//         $timeSpent = now()->timestamp - $startTimeInSeconds;
//         $answersOfQuestions = session()->get('answersOfQuestions', []);
//         $result = 0;

//         // Identify user
//         $user = Auth::guard('student')->check() ? Auth::guard('student')->user() : Auth::user();
//         $typeUser = $user instanceof Contact ? 'student' : 'user';
//         $typeReturn = ($user instanceof Contact && optional($user->contact)->type === 'demo') ? 0 : 1;

//         // Create Test record
//         $test = Test::create([
//             'user_id' => $user->id,
//             'quiz_id' => $quiz->id,
//             'result' => 0,
//             'ip_address' => request()->ip(),
//             'time_spent' => $timeSpent,
//             'type_user' => $typeUser,
//         ]);

//         // Process answers
//         foreach ($answersOfQuestions as $answerData) {
//             $question = Question::find($answerData['question_id']);
//             $correct = $this->checkAnswer($question, $answerData);

//             if ($correct) {
//                 $result++;
//             }

//             Answer::create([
//                 'user_id' => $user->id,
//                 'test_id' => $test->id,
//                 'question_id' => $question->id,
//                 'option_id' => $answerData['type'] === 'options' ? $answerData['answer'] : null,
//                 'image_id' => $answerData['type'] === 'image' ? $answerData['answer'] : null,
//                 'written_answer' => $answerData['type'] === 'written' ? $answerData['answer'] : null,
//                 'correct' => $correct ? 1 : 0,
//                 'type_user' => $typeUser,
//             ]);
//         }

//         // Update test result
//         $test->update(['result' => $result]);

//         // Clear session
//         session()->forget(['quizStartTime', 'startTimeInSeconds', 'answersOfQuestions']);

//         return $typeReturn == 0 ? redirect()->route('demo.thank_you') : redirect()->route('results.show', ['test' => $test]);
//     }

//     private function getCorrectAnswer(Question $question)
//     {
//         if ($question->type === 'options') {
//             return optional($question->options()->where('correct', true)->first())->id;
//         } elseif ($question->type === 'image') {
//             return optional($question->images()->where('correct', true)->first())->id;
//         } elseif ($question->type === 'written') {
//             return $question->written_answer;
//         }
//         return null;
//     }

//     private function checkAnswer(Question $question, array $answerData)
//     {
//         if ($answerData['type'] === 'options') {
//             return optional(Option::find($answerData['answer']))->correct;
//         } elseif ($answerData['type'] === 'written') {
//             return strtolower($answerData['answer']) === strtolower($question->written_answer);
//         } elseif ($answerData['type'] === 'image') {
//             return optional(ImageOption::find($answerData['answer']))->correct;
//         }
//         return false;
//     }
// }

// old version
// class QuizController extends Controller
// {
//     public array $answersOfQuestions = [];

//     public function show(Quiz $quiz, Request $request)
//     {

//         // dd($request->all());    
//         $currentQuestionIndex = $request->input('currentQuestionIndex', 0);
//         $questionsCount = $quiz->questions()->count();
//         $currentQuestion = $quiz->questions()->skip($currentQuestionIndex)->first();
        

//         // dd($currentQuestion);

//         // Store start time in session if it's not already set
//         if (!session()->has('startTimeInSeconds')) {
//             session(['startTimeInSeconds' => now()->timestamp]);
//         }
//         if (!session()->has('quizStartTime')) {
//             session(['quizStartTime' => now()->timestamp]);
//         }
//         $startTimeInSeconds = session('startTimeInSeconds');

//         if ($request->input('quizStartTime')) {
//             $quizStartTime = $request->input('quizStartTime');
//         } else {
//             $quizStartTime = session('quizStartTime');
//         }

//         if (!$currentQuestion) {
//             abort(404, 'No questions found for this quiz');
//         }

//         $correctAnswer = '';
//         // $currentQuestion = Question::find($currentQuestionId);

//         if ($currentQuestion->type === 'options') {
//             $correctAnswers = $currentQuestion->options()->where('correct', true)->first();
//             $correctAnswer = $correctAnswers->id;
//         } elseif ($currentQuestion->type === 'image') {
//             $correctAnswers = $currentQuestion->images()->where('correct', true)->first();
//             $correctAnswer = $correctAnswers->id;
//         } elseif ($currentQuestion->type === 'written') {
//             $correctAnswer = $currentQuestion->written_answer;
//         }

//         $header = 'Quiz: ' . $quiz->title;
//         // if (condition) {
//         //     # code...
//         // }
//         // $quizStartTime = '';
        
//         // dd($quizStartTime);
//         session()->forget('quizStartTime');

//         return view('home.quiz-show', [
//             'quiz' => $quiz,
//             'currentQuestionIndex' => $currentQuestionIndex,
//             'questionsCount' => $questionsCount,
//             'currentQuestion' => $currentQuestion,
//             'startTimeInSeconds' => $startTimeInSeconds,
//             'answersOfQuestions' => $this->answersOfQuestions,
//             // 'answersOfWrittens' => $this->answersOfWrittens,
//             'correctAnswer' => $correctAnswer,
//             'quizStartTime' => $quizStartTime,

//             'header' => $header,
//         ]);
//     }

//     public function nextQuestion(Request $request, Quiz $quiz)
//     {
//         // dd($request->all());
    
//         $quizStartTime = $request->input('quizStartTime');
//         $currentQuestionIndex = $request->input('currentQuestionIndex', 0);
//         $questionsCount = $quiz->questions()->count();
//         $answerOfQuestion = $request->input('answerOfQuestion.' . $currentQuestionIndex);

//         $currentQuestionId = $request->input('currentQuestionId');
//         $currentQuestionType = $request->input('currentQuestionType');
//         $answerOfWritten = $request->input('answerOfWritten.' . $currentQuestionId);

//         $answersOfQuestions = session()->get('answersOfQuestions', []);
//         $answersOfQuestions[$currentQuestionIndex]['question_id'] = $currentQuestionId;
//         $answersOfQuestions[$currentQuestionIndex]['answer'] = $answerOfQuestion;
//         $answersOfQuestions[$currentQuestionIndex]['type'] = $currentQuestionType;
//         session()->put('answersOfQuestions', $answersOfQuestions);

//         $correctAnswer = '';
//         $currentQuestion = Question::find($currentQuestionId);

//         if ($currentQuestionType === 'options') {

//             $correctAnswers = $currentQuestion->options()->where('correct', true)->first();
//             $correctAnswer = $correctAnswers->id;
//         } elseif ($currentQuestionType === 'image') {

//             $correctAnswers = $currentQuestion->images()->where('correct', true)->first();
//             $correctAnswer = $correctAnswers->id;
//         } elseif ($currentQuestionType === 'written') {

//             $correctAnswer = $currentQuestion->written_answer;
//         }

//         if ($currentQuestionIndex >= $questionsCount - 1) {
//             return $this->submit($request, $quiz);
//         }

//         $nextQuestionIndex = $currentQuestionIndex + 1;
 
//         return redirect()->route('quiz.show', [
//             'quiz' => $quiz,
//             'currentQuestionIndex' => $nextQuestionIndex,
//             'correctAnswer' => $correctAnswer,
//             'quizStartTime' => $quizStartTime,
//         ])->withInput($request->except('_token'));
//     }

//     public function submit(Request $request, Quiz $quiz)
//     {
//         dd($request->all());
    
//         $startTimeInSeconds = session('startTimeInSeconds');
//         $time_spent = now()->timestamp - $startTimeInSeconds;
//         $result = 0;

//         $type_return = '';
//         if (Auth::guard('student')->check()) {
//             $user_id = Auth::guard('student')->id();
//             $type_user = 'student';
//             $checkTypeStd = Contact::find($user_id);
//             if ($checkTypeStd && $checkTypeStd->type === 'demo') {
//                 $type_return = 0;
//             } else {
//                 $type_return = 1;
//             }
//         } elseif (Auth::check()) {
//             $user_id = Auth::id();
//             $type_user = 'user';
//             $type_return = 1;
//         } else {
//         }

//         $test = Test::create([
//             'user_id' => $user_id,
//             'quiz_id' => $quiz->id,
//             'result' => 0,
//             'ip_address' => request()->ip(),
//             'time_spent' => $time_spent,
//             'type_user' => $type_user,
//         ]);

//         // Process multiple-choice answers
//         $answersOfQuestions = session()->get('answersOfQuestions', []);
//         // dd($answersOfQuestions);
//         foreach ($answersOfQuestions as $key => $answerData) {
//             $question = Question::find($answerData['question_id']);
//             $answer = $answerData['answer'];
//             if ($answerData['type'] === 'options') {
//                 // Check if the selected option is correct
//                 $option = Option::find($answer);
//                 $correct = !empty($option) && $option->correct;
//             } elseif ($answerData['type'] === 'written') {
//                 // Check if the written answer matches the correct answer
//                 $correct = !empty($answer) && strtolower($answer) === strtolower($question->written_answer);
//             } elseif ($answerData['type'] === 'image') {
//                 # code...
//                 $option = ImageOption::find($answer);
//                 $correct = !empty($option) && $option->correct;
//             }

//             if ($correct) {
//                 $result++;
//             }

//             Answer::create([
//                 'user_id' => $user_id,
//                 'test_id' => $test->id,
//                 'question_id' => $question->id,
//                 'option_id' => $answerData['type'] === 'options' ? $answer : null,
//                 'image_id' => $answerData['type'] === 'image' ? $answer : null,
//                 'written_answer' => $answerData['type'] === 'written' ? $answer : null,
//                 'correct' => $correct ? 1 : 0,
//                 'type_user' => $type_user,

//             ]);
//         }

//         // Update the result of the test
//         $test->update([
//             'result' => $result
//         ]);

//         // Clear session data after submission
//         session()->forget('quizStartTime');
//         session()->forget('startTimeInSeconds');
//         session()->forget('answersOfQuestions');
//         session()->forget('answersOfWrittens');

//         if ($type_return == 0) {
//             # code...
//             return redirect()->route('demo.thank_you');
//         } else {
//             # code...
//             return redirect()->route('results.show', ['test' => $test]);
//         }
//     }
// }
