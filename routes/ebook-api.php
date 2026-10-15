<?php

use App\Http\Controllers\Api\EbookAuthController;
use App\Http\Controllers\Api\EbookController;
use App\Http\Middleware\AuthenticateEbook;
use Illuminate\Support\Facades\Route;

Route::prefix('ebook/v1')->withoutMiddleware(['throttle:api'])->group(function () {
    Route::post('login', [EbookAuthController::class, 'login'])->middleware('throttle:ebook-login');
    Route::middleware([AuthenticateEbook::class, 'throttle:ebook-api'])->group(function () {
        Route::get('me', [EbookAuthController::class, 'me']);
        Route::post('logout', [EbookAuthController::class, 'logout'])->name('ebook.api.logout');
        Route::get('books', [EbookController::class, 'index']);
        Route::post('books/{book}/open', [EbookController::class, 'open'])->whereNumber('book');
        Route::get('pages/{page}/image', [EbookController::class, 'image'])->whereNumber('page');
        Route::get('pages/{page}/work', [EbookController::class, 'work'])->whereNumber('page');
        Route::put('pages/{page}/work', [EbookController::class, 'save'])->whereNumber('page');
        Route::post('events', [EbookController::class, 'events']);
    });
});
