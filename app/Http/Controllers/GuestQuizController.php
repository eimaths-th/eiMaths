<?php

namespace App\Http\Controllers;

use App\Models\FactionAnswer;
use App\Models\GuestAnswer;
use App\Models\GuestQuizzer;
use App\Models\GuestTest;
use App\Models\ImageOption;
use App\Models\Option;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class GuestQuizController extends Controller
{
    /**
     * แสดงฟอร์มลงทะเบียน Guest
     */
    public function showRegister()
    {
        // ถ้า login อยู่แล้ว redirect ไป home
        if (Auth::guard('guest_quiz')->check()) {
            return redirect()->route('guest.quiz.home');
        }

        return view('guest-quiz.register');
    }

    /**
     * บันทึกข้อมูล Guest + auto login
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'nickname' => 'nullable|string|max:255',
            'grade' => 'required|string|max:50',
            'school' => 'nullable|string|max:255',
            'email' => 'required|email|unique:guest_quizzers,email',
            'phone' => 'required|string|max:20',
        ], [
            'first_name.required' => __('students.login.validation.first_name_required'),
            'last_name.required' => __('students.login.validation.last_name_required'),
            'grade.required' => __('students.login.validation.grade_required'),
            'email.required' => __('students.login.validation.email_required'),
            'email.email' => __('students.login.validation.email_invalid'),
            'email.unique' => __('students.login.validation.email_unique'),
            'phone.required' => __('students.login.validation.phone_required'),
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // สร้าง Guest account (password = phone)
        $guest = GuestQuizzer::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'nickname' => $request->nickname,
            'grade' => $request->grade,
            'school' => $request->school,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->phone),
            'expires_at' => now()->addDays(10),
        ]);

        // Auto login
        Auth::guard('guest_quiz')->login($guest);

        return redirect()->route('guest.quiz.home');
    }

    /**
     * Guest Login
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'guest_email' => 'required|email',
            'guest_password' => 'required|string',
        ], [
            'guest_email.required' => __('students.login.validation.email_required'),
            'guest_email.email' => __('students.login.validation.email_invalid'),
            'guest_password.required' => __('students.login.validation.guest_password_required'),
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $guest = GuestQuizzer::where('email', $request->guest_email)->first();

        if (!$guest || !Hash::check($request->guest_password, $guest->password)) {
            return back()->withErrors(['guest_error' => __('students.login.validation.guest_invalid_credentials')])->withInput();
        }

        // ตรวจหมดอายุ
        if ($guest->isExpired()) {
            // ลบข้อมูลการสอบ (เก็บข้อมูลส่วนตัว)
            $guest->deleteTestData();

            return redirect()->route('guest.quiz.expired');
        }

        // Login
        Auth::guard('guest_quiz')->login($guest);

        return redirect()->route('guest.quiz.home');
    }

    /**
     * Guest Logout
     */
    public function logout(Request $request)
    {
        Auth::guard('guest_quiz')->logout();
        $request->session()->forget(['guest_answersOfQuestions', 'guest_quizStartTime', 'guest_startTimeInSeconds']);

        return redirect()->route('student.login');
    }

    /**
     * หน้าหมดอายุ
     */
    public function expired()
    {
        return view('guest-quiz.expired');
    }

    /**
     * หน้า Home — แสดง Quiz ที่ is_free = true
     */
    public function home()
    {
        $guest = Auth::guard('guest_quiz')->user();

        // กำหนดเงื่อนไขการมองเห็นข้อสอบตามชั้นเรียนที่สมัคร
        $allowedLevels = [];
        switch ($guest->grade) {
            case 'K1':
            case 'K2':
            case 'P1':
                $allowedLevels = ['K1', 'K2', 'P1', 'P2'];
                break;
            case 'P2':
                $allowedLevels = ['P1', 'P2', 'P3'];
                break;
            case 'P3':
                $allowedLevels = ['P2', 'P3', 'P4'];
                break;
            case 'P4':
                $allowedLevels = ['P3', 'P4', 'P5'];
                break;
            case 'P5':
            case 'P6':
                $allowedLevels = ['P4', 'P5', 'P6'];
                break;
            default:
                // กรณีอื่นๆ (ถ้ามี) ให้เห็นระดับใกล้เคียง หรือเห็นทั้งหมด (ตั้งเป็นค่า default)
                $allowedLevels = [$guest->grade];
                break;
        }

        $freeQuizzes = Quiz::whereHas('questions')
            ->where('is_free', true)
            ->whereIn('level', $allowedLevels)
            ->orderBy('level', 'asc')
            ->orderBy('id', 'desc')
            ->withCount('questions')
            ->get()
            ->groupBy('level');

        $freeQuizzesAll = Quiz::whereHas('questions')
            ->where('is_free', true)
            ->whereIn('level', $allowedLevels)
            ->orderBy('id', 'desc')
            ->withCount('questions')
            ->get();

        $header = 'eiMaths Free Quiz';
        $daysRemaining = now()->diffInDays($guest->expires_at, false);

        return view('guest-quiz.home', compact('freeQuizzes', 'freeQuizzesAll', 'header', 'guest', 'daysRemaining'));
    }

    /**
     * แสดงข้อสอบ — reuse quiz-show view
     */
    public function show(Quiz $quiz, Request $request)
    {
        // ตรวจว่า Quiz นี้เป็น free หรือไม่
        if (!$quiz->is_free) {
            abort(403, 'ข้อสอบนี้ไม่ใช่ข้อสอบฟรี');
        }

        $currentQuestionIndex = (int) $request->input('currentQuestionIndex', 0);
        $questions = $quiz->questions();
        $questionsCount = $questions->count();
        $currentQuestion = $questions->skip($currentQuestionIndex)->first();
        $correctAnswer = $this->getCorrectAnswer($currentQuestion);

        if (!$currentQuestion) {
            abort(404, 'No questions found for this quiz');
        }

        // Check and reset session if quiz_id has changed
        $answersOfQuestions = session('guest_answersOfQuestions', []);
        if (!isset($answersOfQuestions['quiz_id']) || $answersOfQuestions['quiz_id'] != $quiz->id) {
            session()->forget(['guest_quizStartTime', 'guest_startTimeInSeconds', 'guest_answersOfQuestions']);
            $answersOfQuestions = ['quiz_id' => $quiz->id];
            session()->put('guest_answersOfQuestions', $answersOfQuestions);
        }

        // Store start time if not set
        if (!session()->has('guest_quizStartTime')) {
            session(['guest_quizStartTime' => now()->timestamp]);
        }
        if (!session()->has('guest_startTimeInSeconds')) {
            session(['guest_startTimeInSeconds' => now()->timestamp]);
        }

        $quizStartTime = session('guest_quizStartTime');
        $startTimeInSeconds = session('guest_startTimeInSeconds');

        session()->put('guest_quizStartTime', $quizStartTime);
        session()->put('guest_startTimeInSeconds', $startTimeInSeconds);

        // Answer session
        $answers = session()->get('guest_answersOfQuestions', []);
        $getAnserSessionIndex = $answers[$currentQuestionIndex] ?? null;

        $getAnserSessionIndexWritten = null;
        $getAnserSessionIndexWhole = null;
        $getAnserSessionIndexNumerator = null;
        $getAnserSessionIndexDenominator = null;

        if (!empty($getAnserSessionIndex['answer'])) {
            $getAnserSessionIndexWritten = $getAnserSessionIndex['answer'] ?? null;

            if (is_array($getAnserSessionIndex['answer'])) {
                $getAnserSessionIndexWhole = $getAnserSessionIndex['answer']['whole'] ?? null;
                $getAnserSessionIndexNumerator = $getAnserSessionIndex['answer']['numerator'] ?? null;
                $getAnserSessionIndexDenominator = $getAnserSessionIndex['answer']['denominator'] ?? null;
            }
        }

        // Route variables for the view (เพื่อให้ view รู้ว่าเป็น Guest)
        $isGuest = true;
        $submitRoute = route('guest.quiz.submit', $quiz);
        $saveAnswerRoute = route('guest.quiz.saveAnswer');
        $nextRouteName = 'guest.quiz.next';

        return view('home.quiz-show', compact(
            'quiz',
            'getAnserSessionIndex',
            'currentQuestionIndex',
            'questionsCount',
            'currentQuestion',
            'answersOfQuestions',
            'correctAnswer',
            'quizStartTime',
            'startTimeInSeconds',
            'getAnserSessionIndexWhole',
            'getAnserSessionIndexNumerator',
            'getAnserSessionIndexDenominator',
            'getAnserSessionIndexWritten',
            'isGuest',
            'submitRoute',
            'saveAnswerRoute',
            'nextRouteName',
        ))->with('header', 'Quiz: ' . $quiz->title);
    }

    /**
     * บันทึกคำตอบใน Session (AJAX)
     */
    public function saveAnswer(Request $request)
    {
        $currentQuestionIndex = (int) $request->input('currentQuestionIndex', 0);
        $answerOfQuestion = $request->input("answerOfQuestion.$currentQuestionIndex");
        $currentQuestionId = (int) $request->input('currentQuestionId');
        $currentQuestionType = $request->input('currentQuestionType');
        $currentQuestionFracType = $request->input('currentQuestionFracType');
        $currentQuestionFracAnswerType = $request->input('currentQuestionFracAnswerType');

        $question = Question::find($currentQuestionId);
        $correctAnswer = $question ? $question->correct_answer : null;
        $answersOfQuestions = session()->get('guest_answersOfQuestions', []);

        if ($currentQuestionType === 'fraction') {
            if ($currentQuestionFracType === 'written') {
                $currentQuestionFracWhole = $request->input('currentQuestionFracWhole');
                $currentQuestionFracNumerator = $request->input('currentQuestionFracNumerator');
                $currentQuestionFracDenominator = $request->input('currentQuestionFracDenominator');

                $answersOfQuestions[$currentQuestionIndex] = [
                    'question_id' => $currentQuestionId,
                    'type' => $currentQuestionType,
                    'frac_type' => $currentQuestionFracType,
                    'answer' => [
                        'whole' => $currentQuestionFracWhole ?? null,
                        'numerator' => $currentQuestionFracNumerator,
                        'denominator' => $currentQuestionFracDenominator,
                    ],
                    'answer_type' => $currentQuestionFracAnswerType,
                ];
            } else {
                $answersOfQuestions[$currentQuestionIndex] = [
                    'question_id' => $currentQuestionId,
                    'answer' => $answerOfQuestion,
                    'frac_type' => $currentQuestionFracType,
                    'type' => $currentQuestionType,
                ];
            }
        } else {
            $answersOfQuestions[$currentQuestionIndex] = [
                'question_id' => $currentQuestionId,
                'answer' => $answerOfQuestion,
                'frac_type' => $currentQuestionFracType,
                'type' => $currentQuestionType,
            ];
        }

        session()->put('guest_answersOfQuestions', $answersOfQuestions);

        $quiz = Quiz::find($request->input('quiz_id'));
        $changeToIndex = $request->input('changeToQuestionIndex');
        $quizStartTime = $request->input('quizStartTime');

        return response()->json([
            'quiz' => $quiz,
            'changeToIndex' => $changeToIndex,
            'quizStartTime' => $quizStartTime,
        ]);
    }

    /**
     * ข้อถัดไป
     */
    public function nextQuestion(Request $request, Quiz $quiz)
    {
        $currentQuestionIndex = (int) $request->input('currentQuestionIndex', 0);
        $answerOfQuestion = $request->input("answerOfQuestion.$currentQuestionIndex");
        $currentQuestionId = (int) $request->input('currentQuestionId');
        $currentQuestionType = $request->input('currentQuestionType');
        $currentQuestionFracType = $request->input('currentQuestionFracType');
        $currentQuestionFracAnswerType = $request->input('currentQuestionFracAnswerType');

        $answersOfQuestions = session()->get('guest_answersOfQuestions', []);

        if ($currentQuestionType === 'fraction') {
            if ($currentQuestionFracType === 'written') {
                $currentQuestionFracWhole = $request->input('currentQuestionFracWhole');
                $currentQuestionFracNumerator = $request->input('currentQuestionFracNumerator');
                $currentQuestionFracDenominator = $request->input('currentQuestionFracDenominator');

                $answersOfQuestions[$currentQuestionIndex] = [
                    'question_id' => $currentQuestionId,
                    'type' => $currentQuestionType,
                    'frac_type' => $currentQuestionFracType,
                    'answer' => [
                        'whole' => $currentQuestionFracWhole ?? null,
                        'numerator' => $currentQuestionFracNumerator,
                        'denominator' => $currentQuestionFracDenominator,
                    ],
                    'answer_type' => $currentQuestionFracAnswerType,
                ];
            } else {
                $answersOfQuestions[$currentQuestionIndex] = [
                    'question_id' => $currentQuestionId,
                    'answer' => $answerOfQuestion,
                    'frac_type' => $currentQuestionFracType,
                    'type' => $currentQuestionType,
                ];
            }
        } else {
            $answersOfQuestions[$currentQuestionIndex] = [
                'question_id' => $currentQuestionId,
                'answer' => $answerOfQuestion,
                'frac_type' => $currentQuestionFracType,
                'type' => $currentQuestionType,
            ];
        }

        session()->put('guest_answersOfQuestions', $answersOfQuestions);

        $questionsCount = $quiz->questions()->count();
        if ($currentQuestionIndex >= $questionsCount - 1) {
            return $this->submit($request, $quiz);
        }

        return redirect()->route('guest.quiz.show', [
            'quiz' => $quiz,
            'currentQuestionIndex' => $currentQuestionIndex + 1,
        ])->withInput($request->except('_token'));
    }

    /**
     * Submit — สร้าง GuestTest + GuestAnswer
     */
    public function submit(Request $request, Quiz $quiz)
    {
        $answersOfQuestions = session('guest_answersOfQuestions', []);
        $startTimeInSeconds = session('guest_startTimeInSeconds', now()->timestamp);
        $timeSpent = now()->timestamp - $startTimeInSeconds;
        $result = 0;

        $guest = Auth::guard('guest_quiz')->user();

        // Get all questions in quiz
        $allQuestions = $quiz->questions()->get();

        // Create GuestTest record
        $guestTest = GuestTest::create([
            'guest_quizzer_id' => $guest->id,
            'quiz_id' => $quiz->id,
            'result' => 0,
            'total_questions' => $allQuestions->count(),
            'time_spent' => $timeSpent,
            'ip_address' => $request->ip(),
        ]);

        // Build a lookup of answered questions by question_id
        $answeredByQuestionId = [];
        foreach ($answersOfQuestions as $key => $answerData) {
            if (!is_array($answerData) || !isset($answerData['question_id'])) continue;
            $answeredByQuestionId[(int) $answerData['question_id']] = $answerData;
        }

        // Loop through ALL questions and create answers
        foreach ($allQuestions as $question) {
            $answerData = $answeredByQuestionId[$question->id] ?? null;

            if ($answerData) {
                // Answered question — check if correct
                $type = $answerData['type'];
                $textAnswerFactions = null;

                if ($type === 'fraction') {
                    $correct = $this->checkAnswerFactions($question, $answerData);
                    $textAnswerFactions = $this->textAnswerFactions($question, $answerData);
                } else {
                    $correct = $this->checkAnswer($question, $answerData);
                }

                if ($correct) {
                    $result++;
                }

                GuestAnswer::create([
                    'guest_test_id' => $guestTest->id,
                    'question_id' => $question->id,
                    'option_id' => $answerData['type'] === 'options' ? $answerData['answer'] : null,
                    'image_id' => $answerData['type'] === 'image' ? $answerData['answer'] : null,
                    'written_answer' => $answerData['type'] === 'written' ? $answerData['answer'] : null,
                    'fraction' => $answerData['type'] === 'fraction' ? ($textAnswerFactions ?? null) : null,
                    'correct' => $correct ? 1 : 0,
                ]);
            } else {
                // Unanswered question — mark as incorrect
                GuestAnswer::create([
                    'guest_test_id' => $guestTest->id,
                    'question_id' => $question->id,
                    'option_id' => null,
                    'image_id' => null,
                    'written_answer' => null,
                    'fraction' => null,
                    'correct' => 0,
                ]);
            }
        }

        // Update result
        $guestTest->update(['result' => $result]);

        // Clear session
        session()->forget(['guest_quizStartTime', 'guest_startTimeInSeconds', 'guest_answersOfQuestions']);

        return redirect()->route('guest.quiz.result', ['guestTest' => $guestTest]);
    }

    /**
     * แสดงผลสอบ — ✅/❌ เท่านั้น ไม่แสดงเฉลย
     */
    public function result(GuestTest $guestTest)
    {
        $guest = Auth::guard('guest_quiz')->user();

        // ตรวจว่าเป็นผลสอบของ Guest คนนี้
        if ($guestTest->guest_quizzer_id !== $guest->id) {
            abort(403, 'ไม่มีสิทธิ์ดูผลสอบนี้');
        }

        $results = GuestAnswer::where('guest_test_id', $guestTest->id)
            ->with('question.options')
            ->get();

        $questionsCount = $guestTest->quiz->questions()->count();

        return view('guest-quiz.result', compact('guestTest', 'questionsCount', 'results'));
    }

    // ============================================
    // Private helper methods (reuse จาก QuizController)
    // ============================================

    private function textAnswerFactions(Question $question, array $answerData)
    {
        $textAnswerFactions = '';
        if ($answerData['frac_type'] === 'written') {
            if ($answerData['answer_type'] === 'mixed') {
                $numerator = $answerData['answer']['whole'] * $answerData['answer']['denominator'] + $answerData['answer']['numerator'];
                $denominator = $answerData['answer']['denominator'];
                $textAnswerFactions = "MixFrac($numerator,$denominator)";
            } else {
                $numerator = $answerData['answer']['numerator'];
                $denominator = $answerData['answer']['denominator'];
                $textAnswerFactions = "Frac($numerator,$denominator)";
            }
        } else {
            $textAnswerFactions = $answerData['answer'];
        }

        return $textAnswerFactions;
    }

    private function checkAnswerFactions(Question $question, array $answerData)
    {
        return match ($answerData['frac_type']) {
            'options' => optional(FactionAnswer::find($answerData['answer']))->correct,
            'written' => $this->checkWrittenFraction($question, $answerData),
            default => false,
        };
    }

    private function checkWrittenFraction(Question $question, array $answerData)
    {
        $correctFraction = $question->fractions()->first();

        if (!$correctFraction) {
            return false;
        }

        $whole = $answerData['answer']['whole'] ?? 0;
        $numerator = $answerData['answer']['numerator'] ?? null;
        $denominator = $answerData['answer']['denominator'] ?? null;

        if ($answerData['answer_type'] === 'mixed') {
            $userNumerator = ((int)$denominator * (int)$whole) + (int)$numerator;

            return (
                $userNumerator == $correctFraction->numerator &&
                $denominator == $correctFraction->denominator
            );
        } else {
            return (
                (int)$numerator == $correctFraction->numerator &&
                (int)$denominator == $correctFraction->denominator
            );
        }
    }

    private function checkAnswer(Question $question, array $answerData)
    {
        return match ($answerData['type']) {
            'options' => optional(Option::find($answerData['answer']))->correct,
            'written' => strtolower(trim($answerData['answer'])) === strtolower(trim($question->written_answer)),
            'image' => optional(ImageOption::find($answerData['answer']))->correct,
            default => false,
        };
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
}
