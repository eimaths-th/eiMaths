<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\APIController;



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

//Route::middleware('cors')->post('/applogin', [APIController::class, 'login']);
Route::middleware('auth.apikey')->group(function () {
    Route::post('/applogin', [APIController::class, 'login']);
    Route::get('/searchdepartment', [APIController::class, 'searchDepartment']);
    Route::get('/searchstudent', [APIController::class, 'searchStudent']);
    Route::get('/searchsubject', [APIController::class, 'searchSubject']);
    Route::get('/searchterm', [APIController::class, 'searchTerm']);
    Route::get('/searchbook', [APIController::class, 'searchBook']);
    Route::post('/savehistories', [APIController::class, 'saveHistories'])->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
    Route::post('/savefile', [APIController::class, 'saveFile'])->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
});
