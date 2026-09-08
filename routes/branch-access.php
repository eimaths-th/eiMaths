<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\BranchTransferController;

Route::middleware(['auth', 'affiliate.redirect'])->group(function () {
    Route::post('/crm/leads/{lead}/branch', [\App\Http\Controllers\CrmLeadController::class, 'assignBranch'])->name('crm.leads.branch');
    Route::get('/roles/template/{id}', [RoleController::class, 'template'])->name('roles.template');
    Route::get('/branch-transfers', [BranchTransferController::class, 'index'])->name('branch-transfers.index');
    Route::get('/branch-transfers/targets', [BranchTransferController::class, 'targets'])->name('branch-transfers.targets');
    Route::post('/branch-transfers', [BranchTransferController::class, 'store'])->name('branch-transfers.store');
});

// These URLs are also rewritten through Laravel by public/.htaccess.
foreach (['file_upload' => 'file_upload', 'file_store' => 'file_store', 'img/payment_slips' => 'payment_slips', 'storage/attachments' => 'attachments', 'feedback_images' => 'feedback_images'] as $path => $folder) {
    Route::get('/'.$path.'/{filename}', function (\Illuminate\Http\Request $request, string $filename) use ($folder) {
        return app(\App\Http\Controllers\PrivateBranchFileController::class)->show($request, $folder, $filename);
    })->name('branch-files.'.$folder);
}
