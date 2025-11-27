<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\APIController;
use App\Http\Controllers\Api\ParentAuthController;



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

//Route::middleware('cors')->post('/applogin', [APIController::class, 'login']);
// Parent Authentication Routes
Route::prefix('parent')->group(function () {
    Route::post('/login', [ParentAuthController::class, 'login']);
    Route::post('/register', [ParentAuthController::class, 'register']);
    Route::post('/google-login', [ParentAuthController::class, 'googleLogin']);
    Route::post('/facebook-login', [ParentAuthController::class, 'facebookLogin']);
    Route::post('/apple-login', [ParentAuthController::class, 'appleLogin']);
    Route::post('/password-reset', [ParentAuthController::class, 'passwordReset']);
    Route::post('/password-reset-confirm', [ParentAuthController::class, 'passwordResetConfirm']);
    Route::post('/get-student', [ParentAuthController::class, 'getStudent']);
    Route::post('/feedback', [ParentAuthController::class, 'feedback']);
    Route::post('/check-student', [ParentAuthController::class, 'checkstd']);
    Route::post('/check-user', [ParentAuthController::class, 'checkUser']);
    Route::post('/senMailParent', [ParentAuthController::class, 'senMailParent']);
    //logout
    Route::post('/logout', [ParentAuthController::class, 'logout']);

    // Protected routes - require authentication
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [ParentAuthController::class, 'logout']);
        // Add more protected routes here as needed
    });
});

Route::middleware('auth.apikey')->group(function () {
    Route::post('/applogin', [APIController::class, 'login']);
    Route::post('/app-google-login', [APIController::class, 'googleLogin']);
    Route::get('/searchdepartment', [APIController::class, 'searchDepartment']);
    Route::get('/searchstudent', [APIController::class, 'searchStudent']);
    Route::get('/searchsubject', [APIController::class, 'searchSubject']);
    Route::get('/searchterm', [APIController::class, 'searchTerm']);
    Route::get('/searchbook', [APIController::class, 'searchBook']);
    Route::post('/savehistories', [APIController::class, 'saveHistories'])->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
    Route::post('/savefile', [APIController::class, 'saveFile'])->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
});

// Revenue Summary API

Route::get('/revenue-summary-by-branch', [App\Http\Controllers\ReceiptController::class, 'GetRevenueSummaryByBranch']);
