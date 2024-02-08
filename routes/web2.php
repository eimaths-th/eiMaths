<?php

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
    Route::post('/daily_study', [App\Http\Controllers\DashboardController::class, 'daily_study'])->name('dashboard.daily_study');
});


Route::get('manon', function (Request $request) {
    return $request;
});
