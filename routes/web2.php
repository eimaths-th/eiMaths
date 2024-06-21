<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\QuizzesController;
use App\Http\Controllers\ResultController;
use App\Http\Livewire\Admin\AdminForm;
use App\Http\Livewire\Admin\AdminList;
use App\Http\Livewire\Admin\Tests\TestList;
use App\Http\Livewire\Counter;
use App\Http\Livewire\Front\Leaderboard;
use App\Http\Livewire\Front\Results\ResultList;
use App\Http\Livewire\Question\QuestionForm;
use App\Http\Livewire\Question\QuestionList;
use App\Http\Livewire\Quiz\QuizForm;
use App\Http\Livewire\Quiz\QuizList;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::group(['middleware' => ['auth']], function () {
    Route::post('/student_by_level', [App\Http\Controllers\DashboardController::class, 'dashboard_student_by_level'])->name('dashboard.student_by_level');
    Route::post('/student_by_centre', [App\Http\Controllers\DashboardController::class, 'dashboard_student_by_centre'])->name('dashboard.student_by_centre');
    Route::post('/teacher_by_centre', [App\Http\Controllers\DashboardController::class, 'dashboard_teacher_by_centre'])->name('dashboard.teacher_by_centre');
    Route::post('/study_by_centre', [App\Http\Controllers\DashboardController::class, 'today_study_student_by_centre'])->name('dashboard.study_by_centre');
    Route::post('/receipt_sum_by_date', [App\Http\Controllers\DashboardController::class, 'receipt_sum_by_date'])->name('dashboard.receipt_sum_by_date');
    Route::post('/student_status', [App\Http\Controllers\DashboardController::class, 'dashboard_student_status'])->name('dashboard.student_status');
    Route::post('/daily_study', [App\Http\Controllers\DashboardController::class, 'daily_study'])->name('dashboard.daily_study');


    Route::get('/generate-password', [App\Http\Controllers\HomeController::class, 'generate'])->name('generate');
    Route::post('/generate-save', [App\Http\Controllers\HomeController::class, 'generate_save'])->name('generate.save');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Route::get('/index2', [HomeController::class, 'index2'])->name('home2');

    // Route::get('myresults', ResultList::class)->name('myresults');
    // Route::get('leaderboard', Leaderboard::class)->name('leaderboard');

    // Route::get('Counter', Counter::class)->name('Counter');
    // Route::get('Counter', Counter::class)->name('Counter');

    // Route::get('questions', QuestionList::class)->name('questions');
    // Route::get('questions/create', QuestionForm::class)->name('question.create');
    // Route::get('questions/{question}', QuestionForm::class)->name('question.edit');

    // Route::get('quizzes', QuizList::class)->name('quizzes');
    // Route::get('quizzes/create', QuizForm::class)->name('quiz.create');
    // Route::get('quizzes/{quiz}/edit', QuizForm::class)->name('quiz.edit');

    Route::get('admins', AdminList::class)->name('admins');
    Route::get('admins/create', AdminForm::class)->name('admin.create');

    // Route::get('tests', TestList::class)->name('tests');
    //#################################################################
    Route::get('questions', [QuestionController::class, 'index'])->name('questions');
    Route::get('questions/create', [QuestionController::class, 'create'])->name('questions.create');
    Route::post('questions/store', [QuestionController::class, 'store'])->name('questions.store');
    Route::get('questions/edit/{question}', [QuestionController::class, 'edit'])->name('questions.edit');
    Route::PUT('questions/update/{question}', [QuestionController::class, 'update'])->name('questions.update');
    Route::delete('questions/{id}', [QuestionController::class, 'destroy'])->name('questions.destroy');
    Route::get('questions/list', [QuizzesController::class, 'rec'])->name('questions.list');

    Route::get('quizzes', [QuizzesController::class, 'index'])->name('quizzes');
    Route::get('quizzes/create', [QuizzesController::class, 'create'])->name('quizzes.create');
    Route::post('quizzes/store', [QuizzesController::class, 'store'])->name('quizzes.store');
    Route::get('quizzes/edit/{question}', [QuizzesController::class, 'edit'])->name('quizzes.edit');
    Route::put('quizzes/update/{question}', [QuizzesController::class, 'update'])->name('quizzes.update');
    Route::delete('quizzes/{id}', [QuizzesController::class, 'destroy'])->name('quizzes.destroy');
    Route::get('quiz/{quiz}', [QuizController::class, 'show'])->name('quiz.show');
    Route::post('quiz/{quiz}/next/{currentQuestionIndex}', [QuizController::class, 'nextQuestion'])->name('quiz.next');

    // Route::get('questions/list', [QuizzesController::class, 'list'])->name('questions.list');
    // Route::get('questions/list', [QuizzesController::class, 'list'])->name('questions.list');
    ###################################################################################################
    Route::get('quiz-home', [HomeController::class, 'home3'])->name('home3');
    // Route::post('/quiz-submit', [HomeController::class, 'submit'])->name('quiz.submit');
    // Route::post('/quiz/{quiz}/next', [QuizController::class, 'nextQuestion'])->name('quiz.next');
    Route::post('quiz/{quiz}/submit', [QuizController::class, 'submit'])->name('quiz.submit');

    Route::get('results/{test}', [ResultController::class, 'show'])->name('results.show');
    Route::get('leaderboard/{quiz_slug?}', [HomeController::class, 'leaderboard'])->name('leaderboard');
    Route::post('submit-leaderboard', [HomeController::class, 'submitLeaderboard'])->name('submit.leaderboard');
    Route::get('myresults',[HomeController::class, 'myResult'])->name('myresults');
    Route::get('tests/{quiz_slug?}', [HomeController::class, 'tests'])->name('tests');

    // Route::get('quiz/{quiz}', [HomeController::class, 'show'])->name('quiz.show');


});


Route::get('manon', function (Request $request) {
    return $request;
});
