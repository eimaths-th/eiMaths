<?php

use App\Http\Controllers\CreditNoteController;
use App\Http\Controllers\CreditNoteExportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'credit-note.correlation', 'credit-note.feature', 'credit-note.responses'])
    ->prefix('credit-notes')
    ->group(function (): void {
        Route::get('/', [CreditNoteController::class, 'Index'])
            ->middleware('permission:credit-note-list')->name('credit-notes.index');
        Route::get('/{credit_note}/print', [\App\Http\Controllers\CreditNoteDocumentController::class, 'PrintDocument'])
            ->middleware('permission:credit-note-show')->whereNumber('credit_note')->name('credit-notes.print');
        Route::get('/{credit_note}/pdf', [\App\Http\Controllers\CreditNoteDocumentController::class, 'PdfDocument'])
            ->middleware('permission:credit-note-show')->whereNumber('credit_note')->name('credit-notes.pdf');
        Route::get('/export', [CreditNoteExportController::class, 'Export'])
            ->middleware('permission:credit-note-list')->name('credit-notes.export');
        Route::get('/{credit_note}', [CreditNoteController::class, 'Show'])
            ->middleware('permission:credit-note-show')->whereNumber('credit_note')->name('credit-notes.show');
        Route::put('/{credit_note}', [CreditNoteController::class, 'Update'])
            ->middleware('permission:credit-note-edit')->whereNumber('credit_note')->name('credit-notes.update');
        Route::post('/{credit_note}/issue', [CreditNoteController::class, 'Issue'])
            ->middleware(['credit-note.feature:financial', 'permission:credit-note-issue'])->whereNumber('credit_note')->name('credit-notes.issue');
        Route::post('/{credit_note}/cancel', [CreditNoteController::class, 'Cancel'])
            ->middleware(['credit-note.feature:financial', 'permission:credit-note-cancel'])->whereNumber('credit_note')->name('credit-notes.cancel');
    });

Route::middleware(['auth', 'credit-note.correlation', 'credit-note.feature', 'credit-note.responses'])
    ->group(function (): void {
        Route::post('/receipts/{receipt}/credit-notes', [CreditNoteController::class, 'Store'])
            ->middleware('permission:credit-note-create')->whereNumber('receipt')->name('credit-notes.store');
    });
