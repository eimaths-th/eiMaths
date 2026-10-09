<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\APIController;
use App\Http\Controllers\Api\CrmLeadApiController;
use App\Http\Controllers\Api\ParentAuthController;
use App\Http\Controllers\Api\N8nLineCrmController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\TrackingLinkController;

require __DIR__.'/ebook-api.php';



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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/blogs', [App\Http\Controllers\BlogController::class, 'api_show']);
Route::get('/blogs/{slug}', [App\Http\Controllers\BlogController::class, 'api_show_by_slug']);
Route::get('/faqs', [FaqController::class, 'ApiIndex']);
Route::get('/public/tracking-links/{shortCode}', [TrackingLinkController::class, 'resolve'])
    ->where('shortCode', '[A-Za-z0-9]+')
    ->middleware('throttle:120,1');

//Route::middleware('cors')->post('/applogin', [APIController::class, 'login']);
// Parent Authentication Routes
Route::prefix('parent')->group(function () {
    Route::post('/login', [ParentAuthController::class, 'login'])->middleware('throttle:10,1');
    Route::post('/password-reset', [ParentAuthController::class, 'passwordReset'])->middleware(['auth:sanctum', \App\Http\Middleware\AuthenticateParent::class]);
    Route::post('/get-student', [ParentAuthController::class, 'getStudent'])->middleware(['auth:sanctum', \App\Http\Middleware\AuthenticateParent::class]);
    Route::post('/feedback', [ParentAuthController::class, 'feedback'])->middleware(['auth:sanctum', \App\Http\Middleware\AuthenticateParent::class]);
    Route::post('/check-student', [\App\Http\Controllers\Api\ParentRegistrationController::class, 'requestCode'])->middleware('throttle:3,10');
    Route::post('/check-user', [\App\Http\Controllers\Api\ParentRegistrationController::class, 'register'])->middleware('throttle:10,10');

    // Protected routes - require authentication
    Route::middleware(['auth:sanctum', \App\Http\Middleware\AuthenticateParent::class])->group(function () {
        Route::post('/logout', [ParentAuthController::class, 'logout']);
        // Add more protected routes here as needed
    });
});

Route::middleware(['auth:sanctum', \App\Http\Middleware\AuthenticateStaffApi::class])->group(function () {
    Route::get('/searchdepartment', [APIController::class, 'searchDepartment']);
    Route::get('/searchstudent', [APIController::class, 'searchStudent']);
    Route::get('/searchsubject', [APIController::class, 'searchSubject']);
    Route::get('/searchterm', [APIController::class, 'searchTerm']);
    Route::get('/searchbook', [APIController::class, 'searchBook']);
    Route::post('/savehistories', [APIController::class, 'saveHistories'])->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
    Route::post('/savefile', [APIController::class, 'saveFile'])->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
});

// Revenue Summary API

Route::get('/revenue-summary-by-branch', [App\Http\Controllers\ReceiptController::class, 'GetRevenueSummaryByBranch'])->middleware(['auth:sanctum', 'permission:receipt-list']);
Route::get('/revenue-summary-by-branch-monthly', [App\Http\Controllers\ReceiptController::class, 'GetRevenueSummaryByBranchMonthly'])->middleware(['auth:sanctum', 'permission:receipt-list']);

Route::post('/n8n/line/lead', [N8nLineCrmController::class, 'handleLead'])->middleware([\App\Http\Middleware\AuthenticateCrmIntegration::class, 'throttle:30,1']);
Route::get('/n8n/line/follow-up-leads', [N8nLineCrmController::class, 'followUpLeads'])->middleware([\App\Http\Middleware\AuthenticateCrmIntegration::class, 'throttle:30,1']);
Route::post('/line/free-trial/liff-lead', [CrmLeadApiController::class, 'storeLiffLead'])
    ->middleware('throttle:60,1');

Route::prefix('n8n/marketing/leads')->middleware('throttle:30,1')->group(function () {
    Route::post('/', [CrmLeadApiController::class, 'store']);
    Route::get('/follow-up-due', [CrmLeadApiController::class, 'followUpDue']);
    Route::patch('/{lead}/status', [CrmLeadApiController::class, 'updateStatus']);
    Route::post('/{lead}/notes', [CrmLeadApiController::class, 'addNote']);
});

Route::post('/applogin', [APIController::class, 'login'])->middleware('throttle:10,1');
Route::post('/app-google-login', [APIController::class, 'googleLogin'])->middleware('throttle:10,1');

Route::get('/public/branches', [\App\Http\Controllers\Api\PublicBranchController::class, 'index'])->middleware('throttle:60,1');
