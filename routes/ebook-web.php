<?php

use App\Http\Controllers\EbookAdminController;
use Illuminate\Support\Facades\Route;

Route::prefix('ebook-demo')->name('ebook-demo.')->middleware(\App\Http\Middleware\EnsureLocalEbookDemo::class)->group(function () {
    Route::get('/', [EbookAdminController::class, 'index'])->name('index');
    Route::post('/import', [EbookAdminController::class, 'upload'])->name('import');
    Route::post('/versions/{version}/publish', [EbookAdminController::class, 'publish'])->name('publish')->whereNumber('version');
    Route::get('/versions/{version}/preview', [EbookAdminController::class, 'preview'])->name('preview')->whereNumber('version');
    Route::get('/versions/{version}/image', [EbookAdminController::class, 'previewImage'])->name('preview-image')->whereNumber('version');
});

Route::middleware('auth')->group(function () {
    Route::get('/ebooks', [EbookAdminController::class, 'index'])->name('ebooks.index');
    Route::post('/ebooks/import', [EbookAdminController::class, 'upload'])->name('ebooks.import');
    Route::post('/ebooks/versions/{version}/publish', [EbookAdminController::class, 'publish'])->name('ebooks.publish')->whereNumber('version');
    Route::get('/ebooks/versions/{version}/preview', [EbookAdminController::class, 'preview'])->name('ebooks.preview')->whereNumber('version');
    Route::get('/ebooks/versions/{version}/image', [EbookAdminController::class, 'previewImage'])->name('ebooks.preview-image')->whereNumber('version');
});
