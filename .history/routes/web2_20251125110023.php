<?php

use App\Http\Controllers\AffiliateAuthController;
use App\Http\Controllers\AffiliateConfigurationsController;
use App\Http\Controllers\AffiliateController;
use App\Http\Controllers\AffiliateTransactionController;
use App\Http\Controllers\AssignQuizToStudentController;
use App\Http\Controllers\CommissionListControler;
use App\Http\Controllers\ConfigWithdrawController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\EiFormController;
use App\Http\Controllers\eLeanController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LineApiController;
use App\Http\Controllers\TelegramSettingController;
use App\Http\Controllers\LineController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\QuestionQuizzesController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\QuizzesController;
use App\Http\Controllers\QuestionBulkController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\TestSystemQuestionsController;
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
use App\Models\affiliateConfigCommission;
use App\Models\Contact;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\Affiliate\Entities\AffiliateConfiguration;

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


Route::group(['middleware' => ['auth', 'affiliate.redirect']], function () {
    Route::post('/student_by_level', [App\Http\Controllers\DashboardController::class, 'dashboard_student_by_level'])->name('dashboard.student_by_level');
    Route::post('/student_by_centre', [App\Http\Controllers\DashboardController::class, 'dashboard_student_by_centre'])->name('dashboard.student_by_centre');
    Route::post('/teacher_by_centre', [App\Http\Controllers\DashboardController::class, 'dashboard_teacher_by_centre'])->name('dashboard.teacher_by_centre');
    Route::post('/study_by_centre', [App\Http\Controllers\DashboardController::class, 'today_study_student_by_centre'])->name('dashboard.study_by_centre');
    Route::post('/receipt_sum_by_date', [App\Http\Controllers\DashboardController::class, 'receipt_sum_by_date'])->name('dashboard.receipt_sum_by_date');
    Route::post('/receipt_sum_by_month', [App\Http\Controllers\DashboardController::class, 'receipt_sum_by_month'])->name('dashboard.receipt_sum_by_month');
    Route::post('/receipt_sum_by_year', [App\Http\Controllers\DashboardController::class, 'receipt_sum_by_year'])->name('dashboard.receipt_sum_by_year');
    Route::post('/student_status', [App\Http\Controllers\DashboardController::class, 'dashboard_student_status'])->name('dashboard.student_status');
    Route::post('/daily_study', [App\Http\Controllers\DashboardController::class, 'daily_study'])->name('dashboard.daily_study');


    Route::get('/generate-password-system', [App\Http\Controllers\HomeController::class, 'generate'])->name('generate.system');
    Route::post('/generate-save', [App\Http\Controllers\HomeController::class, 'generate_save'])->name('generate.system.save');
    Route::get('/generate-password-student', [App\Http\Controllers\ContactController::class, 'gennerateStudent'])->name('generate.student');
    Route::post('/generate-password-student-save', [App\Http\Controllers\ContactController::class, 'gennerateStudentSave'])->name('generate.student.save');



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
    // Route::PUT('questions/update/{question}', [QuestionController::class, 'update'])->name('questions.update');
    Route::post('questions/update/{question}', [QuestionController::class, 'update'])->name('questions.update');
    Route::delete('questions/{id}', [QuestionController::class, 'destroy'])->name('questions.destroy');
    Route::get('questions/list', [QuizzesController::class, 'rec'])->name('questions.list');

    Route::get('quizzes', [QuizzesController::class, 'index'])->name('quizzes');
    Route::get('quizzes/create', [QuizzesController::class, 'create'])->name('quizzes.create');
    Route::post('quizzes/store', [QuizzesController::class, 'store'])->name('quizzes.store');
    Route::get('quizzes/edit/{question}', [QuizzesController::class, 'edit'])->name('quizzes.edit');
    Route::put('quizzes/update/{question}', [QuizzesController::class, 'update'])->name('quizzes.update');
    Route::delete('quizzes/{id}', [QuizzesController::class, 'destroy'])->name('quizzes.destroy');
    Route::get('/quizzes/{id}', [QuizzesController::class, 'show'])->name('quizzes.show');

    Route::post('questions/bulk-update', [QuestionBulkController::class, 'bulkUpdate'])->name('questions.bulk.update');


    Route::post('assign-quiz', [AssignQuizToStudentController::class, 'assignQuiz'])->name('assignQuiz');
    Route::get('assign', [AssignQuizToStudentController::class, 'index'])->name('assign');


    // Route::get('questions-quizzes', [QuestionQuizzesController::class, 'index'])->name('questions-quizzes');
    // Route::get('questions-quizzes/create', [QuestionQuizzesController::class, 'create'])->name('questions-quizzes.create');
    Route::resource('questions-quizzes', QuestionQuizzesController::class)->names([
        'index' => 'questions-quizzes.index',
        'create' => 'questions-quizzes.create',
        'store' => 'questions-quizzes.store',
        'show' => 'questions-quizzes.show',
        'edit' => 'questions-quizzes.edit',
        'update' => 'questions-quizzes.update',
        'destroy' => 'questions-quizzes.destroy',
        // 'selected' => 'questions-quizzes.selected',
    ]);
    Route::get('questions-quizzes/selected/{id}', [QuestionQuizzesController::class, 'selectedQuestions'])->name('questions-quizzes.selected');

    Route::get('demo', [ContactController::class, 'demo_list'])->name('demo.list');
    Route::get('demo/code', [ContactController::class, 'demo_code'])->name('demo.code');
    Route::post('demo/store', [ContactController::class, 'demo_store'])->name('demo.store');

    // Route::get('quiz/{quiz}', [QuizController::class, 'show'])->name('quiz.show');
    // Route::post('quiz/{quiz}/next/{currentQuestionIndex}', [QuizController::class, 'nextQuestion'])->name('quiz.next');

    ###################################################################################################
    // Route::get('quiz-home', [HomeController::class, 'home3'])->name('home3');
    // Route::get('quiz-home/{level}', [HomeController::class, 'homeLevel'])->name('home.level');
    // Route::get('quiz-home/{level}/{term}', [HomeController::class, 'homeTerm'])->name('home.term');
    // Route::post('quiz/{quiz}/submit', [QuizController::class, 'submit'])->name('quiz.submit');



    // Route::get('quiz/{quiz}', [HomeController::class, 'show'])->name('quiz.show');

    //course list

    // line api setting

    Route::resource('/line-api', LineApiController::class);
    Route::post('/line-api/update-status/{id}', [LineApiController::class, 'updateStatus'])->name('line-api.updateStatus');

    // telegram setting
    Route::resource('/telegram-setting', TelegramSettingController::class);
    Route::put('/telegram-setting/{id}/status', [TelegramSettingController::class, 'updateStatus'])->name('telegram-setting.updateStatus');
    Route::post('/telegram-setting/{id}/test', [TelegramSettingController::class, 'testNotification'])->name('telegram-setting.test');

    Route::get('/course/categories', [CourseController::class, 'categories'])->name('categories.index');
    Route::post('/course/categories-store', [CourseController::class, 'categoriesStore'])->name('categories.store');

    Route::get('/course/categories/edit/{id}', [CourseController::class, 'categoriesEdit'])->name('categories.edit');
    Route::delete('/course/categories/delete/{id}', [CourseController::class, 'categoriesDelete'])->name('category.delete');
    Route::post('/course/categories/update', [CourseController::class, 'categoriesUpdate'])->name('categories.update');
    Route::post('/categories/status-update', [CourseController::class, 'category_status_update'])->name('category.status_update');


    Route::get('/course/list', [CourseController::class, 'courselList'])->name('course.list');
    Route::get('/course/list-data', [CourseController::class, 'getAllCourseData'])->name('course.data');
    Route::post('/courses/update-status', [CourseController::class, 'updateStatus'])->name('courses.updateStatus');
    Route::post('/course/add', [CourseController::class, 'store'])->name('course.store');
    // Route to fetch course details for editing
    Route::get('/course/edit/{id}', [CourseController::class, 'edit'])->name('course.edit');

    // Route to update the course
    Route::put('/course/update/{id}', [CourseController::class, 'update'])->name('course.update');
    Route::delete('/courses/{id}', [CourseController::class, 'destroy'])->name('course.destroy');
    // Route::get('/course/list', 'CourseSettingController@getAllCourse')->name('getAllCourse')->middleware('RoutePermissionCheck:getAllCourse');

    Route::get('/course/course-level/list', [CourseController::class, 'courseLevelList'])->name('course.level.list');
    Route::post('/course/course-level/list/store', [CourseController::class, 'courseLevelStore'])->name('course.level.store');
    Route::get('/course/course-level/edit/{id}', [CourseController::class, 'courseLevelEdit'])->name('course.level.edit');
    Route::post('/course/course-level/update', [CourseController::class, 'courseLevelUpdate'])->name('course.level.update');
    Route::post('/course/course-level/update-status', [CourseController::class, 'courseLevelUpdateStatus'])->name('course.level.update_status');
    Route::get('/course/course-level/get-data', [CourseController::class, 'getCourseLevelsData'])->name('course.levels.data');

    Route::delete('/courses/course-level/destroy/{id}', [CourseController::class, 'courseLevelDestroy'])->name('course.level.destroy');

    Route::get('/courses/pending/index', [CourseController::class, 'coursesPendingIndex'])->name('courses.pending.index');
    Route::get('/courses/pending/details/{id}', [CourseController::class, 'getCoursePendingDetails'])->name('courses.pending.details');
    Route::post('/courses/pending/confirm/{id}', [CourseController::class, 'coursesPendingConfirm'])->name('courses.pending.confirm');
    Route::post('/courses/pending/cancel/{id}', [CourseController::class, 'coursesPendingCancel'])->name('courses.pending.cancel');
    Route::post('/courses/pending/reset/{id}', [CourseController::class, 'coursesPendingReset'])->name('courses.pending.reset');
    Route::get('/courses/pending/new-student/{id}', [CourseController::class, 'coursesPendingNewStd'])->name('courses.pending.new.student');
    Route::post('/courses/pending/new-student/store', [CourseController::class, 'coursesPendingNewStdStore'])->name('courses.pending.new.student.store');
    Route::get('/courses/pending/fetch/slip/{id}', [CourseController::class, 'fetchSlip'])->name('courses.pending.fetch.receipt');
    Route::get('/courses/pending/getCheckRefStudnet', [CourseController::class, 'getCheckRefStudnet'])->name('courses.pending.getCheckRefStudnet');

    Route::get('/commission-list', [CommissionListControler::class, 'index'])->name('commission-list.index');
    Route::post('/commission/update-status', [CommissionListControler::class, 'updateStatus'])->name('commission-list.updateStatus');
    Route::post('/commission-list/view', [CommissionListControler::class, 'show'])->name('commission-list.view');
    Route::get('/affiliate-configurations', [AffiliateConfigurationsController::class, 'index'])->name('affiliate-configurations.index');
    Route::post('/update-commission-config', [AffiliateConfigurationsController::class, 'update'])->name('affiliate-configurations.update');

    Route::get('/config-withdraw', [ConfigWithdrawController::class, 'index'])->name('config-withdraw.index');
    Route::post('/config-withdraw/approve', [ConfigWithdrawController::class, 'approve_withdraw'])->name('config_withdraw.approve');
    Route::post('/commission/updateConfigComission', [AffiliateConfigurationsController::class, 'updateConfigComission'])->name('commission.updateConfigComission');
    Route::post('/approve-withdraws', [ConfigWithdrawController::class, 'approveWithdraws'])->name('withdraw.approve-all');
    Route::post('/config-withdraw/view', [ConfigWithdrawController::class, 'show'])->name('config-withdraw.view');


    //feedback routes
    Route::get('/feedback', [FeedbackController::class, 'index'])->name('feedback.index');
    Route::get('/feedback/create', [FeedbackController::class, 'create'])->name('feedback.create');
    Route::post('/feedback', [FeedbackController::class, 'store'])->name('feedback.store');
    Route::get('/feedback/{feedback}', [FeedbackController::class, 'show'])->name('feedback.show');
    Route::post('/feedback/{feedback}/update-status', [FeedbackController::class, 'updateStatus'])->name('feedback.updateStatus');

});

Route::group(['middleware' => ['auth.userOrStudent', 'affiliate.redirect']], function () {
    Route::get('quiz-home', [eLeanController::class, 'home'])->name('home3');
    // Route::get('/quiz/save-answer', [QuizController::class, 'saveAnswer'])->name('quiz.saveAnswer');
    Route::post('/quiz/save-answer', [QuizController::class, 'saveAnswer'])->name('quiz.saveAnswer');
    Route::post('quiz/{quiz}/next/{currentQuestionIndex}', [QuizController::class, 'nextQuestion'])->name('quiz.next');


    Route::get('quiz-home/{level}', [eLeanController::class, 'homeLevel'])->name('home.level');
    Route::get('quiz-home/{level}/{term}', [eLeanController::class, 'homeTerm'])->name('home.term');
    Route::get('quiz-home/{level}/{term}/{section}', [eLeanController::class, 'homeSection'])->name('home.section');
    Route::get('quiz/{quiz}', [QuizController::class, 'show'])->name('quiz.show');
    Route::post('quiz/{quiz}/submit', [QuizController::class, 'submit'])->name('quiz.submit');
    Route::get('results/{test}', [ResultController::class, 'show'])->name('results.show');
    Route::get('myresults', [eLeanController::class, 'myResult'])->name('myresults');
    Route::get('leaderboard/{quiz_slug?}', [eLeanController::class, 'leaderboard'])->name('leaderboard');
    Route::post('submit-leaderboard', [eLeanController::class, 'submitLeaderboard'])->name('submit.leaderboard');
    Route::get('tests/{quiz_slug?}', [eLeanController::class, 'tests'])->name('tests');
    Route::post('check-answer', [eLeanController::class, 'checkAnswer'])->name('check.answer');
});

// Route::group(['middleware' => ['auth','student.auth']], function () {
//     Route::get('quiz-home', [eLeanController::class, 'home'])->name('home3');
// });
Route::get('/students-login', [App\Http\Controllers\ContactController::class, 'studentsLogin'])->name('student.login');
Route::post('/students-login-check', [App\Http\Controllers\ContactController::class, 'studentsCheckLogin'])->name('student.check');
Route::post('/students-logout', [App\Http\Controllers\ContactController::class, 'studentsLogout'])->name('student.logout');
Route::get('/normalize', [App\Http\Controllers\HomeController::class, 'normalizeNumber1'])->name('normalize');
Route::get('thank-you', [ContactController::class, 'thank_you'])->name('demo.thank_you');


Route::get('/affiliate', [AffiliateController::class, 'fontend'])->name('affiliate.index');
Route::get('/courses', [AffiliateController::class, 'courses'])->name('affiliate.courses');
// Route::get('/courses', [CourseController::class, 'coursePageSection'])->name('courses.page.section');

Route::get('/affiliate/registration', [AffiliateAuthController::class, 'showRegistrationFrom'])->name('affiliate.registration');
Route::post('/affiliate/register', [AffiliateAuthController::class, 'register'])->name('affiliate.register');
Route::get('/affiliate/login', [AffiliateAuthController::class, 'login'])->name('affiliate.login');

Route::post('/affiliate/logout', [AffiliateAuthController::class, 'logout'])->name('affiliate.logout');

Route::post('/affiliate/my_affiliate/add-or-update-paypal-account', [AffiliateAuthController::class, 'addOrUpdatePaypalAccount'])->name('add_or_update_paypal_account');
// In your web.php routes file
Route::post('/courses/pending', [CourseController::class, 'coursesPendingStore'])->name('courses.pending.store');
Route::get('/courses/findPeriodOptions/{department_id}/{day}', [AffiliateController::class, 'findPeriodOptions'])->name('courses.findPeriodOptions');
Route::post('/payment/upload', [CourseController::class, 'uploadSlip'])->name('payment.upload');
Route::POST('/senMailconfirm', [CourseController::class, 'senMail'])->name('senMailconfirm');


// Route::get('/affiliate/my_affiliate', )->name('my_affiliate.index');
Route::resource('/affiliate/my_affiliate', AffiliateController::class)->except(['destroy']);
Route::resource('/affiliate/my_affiliate/withdraw', AffiliateTransactionController::class)->except(['destroy']);

// Route::resource('withdraw_request', 'AffiliateTransactionController')->except(['destroy']);
Route::post('destroy/withdraw_request', [AffiliateTransactionController::class, 'destroy'])->name('withdraw.destroy');
Route::post('balance-transfer-to-wallet', [AffiliateTransactionController::class, 'balanceTransferToWallet'])->name('balance_transfer_to_wallet');



// Route::get('thank-you', [ContactController::class, 'thank_you'])->name('demo.thank_you');
// Route::post('/webhook/line', [LineController::class, 'webhook'])->name('line.webhook');
Route::post('/webhook/line', [LineController::class, 'webhook'])->name('line.webhook');

Route::get('manon', function (Request $request) {
    return $request;
});


route::get('ei-form', [EiFormController::class, 'index'])->name('ei-form.index');
route::post('ei-form/store', [EiFormController::class, 'store'])->name('ei-form.store');

// test route
Route::get('/test-system-questions', [TestSystemQuestionsController::class, 'index'])->name('ts.questions.index');
Route::get('/test-system-questions/create', [TestSystemQuestionsController::class, 'create'])->name('ts.questions.create');
Route::get('/test-system-questions/view', [TestSystemQuestionsController::class, 'view'])->name('ts.questions.view');
Route::post('/test-system-questions', [TestSystemQuestionsController::class, 'store'])->name('ts.questions.store');
Route::get('/test-system-questions/emptyView', [TestSystemQuestionsController::class, 'emptyView'])->name('ts.questions.emptyView');
