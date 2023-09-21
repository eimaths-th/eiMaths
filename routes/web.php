<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;

use App\Services\FileUploadService;
use Illuminate\Http\Request;


/* Route::get('/', function () {
    return view('home');
}); */

//Auth::routes();

Route::group(['middleware' => ['auth']], function () {
    //Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    //Route::get('/roles/store', [RoleController::class, 'store'])->name('roles.store');
    Route::get('/roles/edit/{id}', [RoleController::class, 'edit'])->name('roles.edit2');
    Route::put('/roles/save/{id}', [RoleController::class, 'update'])->name('roles.save');
    //Route::delete('/roles/destroy', [RoleController::class, 'destroy'])->name('roles.destroy');
    Route::post('/roles/destroy_all', [RoleController::class, 'destroy_all'])->name('roles.destroy_all');
    Route::resource('users', UserController::class);
    Route::get('/logout', [UserController::class, 'perform'])->name('logout.perform');
    Route::get('/users/edit/{id}', [UserController::class, 'edit'])->name('users.edit2');
    Route::put('/users/save/{id}', [UserController::class, 'update'])->name('users.save');
    Route::post('/users/destroy_all', [UserController::class, 'destroy_all'])->name('users.destroy_all');
    Route::get('/users/find/{type}/{position}', [UserController::class, 'find'])->name('users.find');
    Route::resource('roles', RoleController::class);

    Route::get('/contacts', [App\Http\Controllers\ContactController::class, 'index'])->name('contacts');
    Route::get('/graduated', [App\Http\Controllers\ContactController::class, 'graduated'])->name('contacts.graduated');
    Route::get('/contacts/running/{centre}', [App\Http\Controllers\ContactController::class, 'create'])->name('contacts.running');
    Route::post('/contacts/store', [App\Http\Controllers\ContactController::class, 'store'])->name('contacts.store');
    Route::get('/contacts/edit/{id}', [App\Http\Controllers\ContactController::class, 'edit'])->name('contacts.edit');
    Route::put('/contacts/save/{id}', [App\Http\Controllers\ContactController::class, 'update'])->name('contacts.save');
    Route::delete('/contacts/destroy', [App\Http\Controllers\ContactController::class, 'destroy'])->name('contacts.destroy');
    Route::post('/contacts/destroy_all', [App\Http\Controllers\ContactController::class, 'destroy_all'])->name('contacts.destroy_all');

    Route::get('/discontinued', [App\Http\Controllers\DiscontinueController::class, 'index'])->name('discontinued');
    Route::put('/discontinued/restart', [App\Http\Controllers\DiscontinueController::class, 'restart'])->name('discontinued.restart');
    Route::post('/discontinued/destroy_all', [App\Http\Controllers\DiscontinueController::class, 'restart_all'])->name('discontinued.restart_all');

    Route::get('/termfee', [App\Http\Controllers\TermfeeController::class, 'index'])->name('termfee');
    Route::get('/termfee/edit/{id}', [App\Http\Controllers\TermfeeController::class, 'edit'])->name('termfee.edit');
    Route::put('/termfee/save/{id}', [App\Http\Controllers\TermfeeController::class, 'update'])->name('termfee.save');

    Route::get('/parameter', [App\Http\Controllers\ParameterController::class, 'index'])->name('parameter');
    Route::get('/parameter/edit/{id}', [App\Http\Controllers\ParameterController::class, 'edit'])->name('parameter.edit');
    Route::put('/parameter/save/{id}', [App\Http\Controllers\ParameterController::class, 'update'])->name('parameter.save');


    Route::get('/departments', [App\Http\Controllers\DepartmentController::class, 'index'])->name('departments');
    Route::post('/departments/store', [App\Http\Controllers\DepartmentController::class, 'store'])->name('departments.store');
    Route::get('/departments/edit/{id}', [App\Http\Controllers\DepartmentController::class, 'edit'])->name('departments.edit');
    Route::put('/departments/save/{id}', [App\Http\Controllers\DepartmentController::class, 'update'])->name('departments.save');
    Route::delete('/departments/destroy', [App\Http\Controllers\DepartmentController::class, 'destroy'])->name('departments.destroy');
    Route::post('/departments/destroy_all', [App\Http\Controllers\DepartmentController::class, 'destroy_all'])->name('departments.destroy_all');
    Route::get('/departments/find/{type}/{department}', [App\Http\Controllers\DepartmentController::class, 'find'])->name('departments.find');

    Route::get('/positions', [App\Http\Controllers\PositionController::class, 'index'])->name('positions');
    Route::post('/positions/store', [App\Http\Controllers\PositionController::class, 'store'])->name('positions.store');
    Route::get('/positions/edit/{id}', [App\Http\Controllers\PositionController::class, 'edit'])->name('positions.edit');
    Route::put('/positions/save/{id}', [App\Http\Controllers\PositionController::class, 'update'])->name('positions.save');
    Route::delete('/positions/destroy', [App\Http\Controllers\PositionController::class, 'destroy'])->name('positions.destroy');
    Route::post('/positions/destroy_all', [App\Http\Controllers\PositionController::class, 'destroy_all'])->name('positions.destroy_all');

    Route::get('/products', [App\Http\Controllers\ProductController::class, 'index'])->name('products');
    Route::post('/products/store', [App\Http\Controllers\ProductController::class, 'store'])->name('products.store');
    Route::get('/products/edit/{id}', [App\Http\Controllers\ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/save/{id}', [App\Http\Controllers\ProductController::class, 'update'])->name('products.save');
    Route::delete('/products/destroy', [App\Http\Controllers\ProductController::class, 'destroy'])->name('products.destroy');
    Route::post('/products/destroy_all', [App\Http\Controllers\ProductController::class, 'destroy_all'])->name('products.destroy_all');

    Route::get('/level/find/{type}/{level}', [App\Http\Controllers\ContactController::class, 'lfind'])->name('level.find');
    Route::get('/term/find/{type}/{level}/{term}', [App\Http\Controllers\ContactController::class, 'tfind'])->name('term.find');

    Route::get('/orders', [App\Http\Controllers\OrderController::class, 'index'])->name('orders');
    Route::get('/orders/running', [App\Http\Controllers\OrderController::class, 'create'])->name('orders.running');
    Route::post('/orders/store', [App\Http\Controllers\OrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/log/{id}', [App\Http\Controllers\OrderController::class, 'log'])->name('orders.log');
    Route::get('orders/edit/{id}', [App\Http\Controllers\OrderController::class, 'edit'])->name('orders.edit');
    Route::put('orders/confirm', [App\Http\Controllers\OrderController::class, 'confirm'])->name('orders.confirm');
    Route::put('/orders/save/{id}', [App\Http\Controllers\OrderController::class, 'update'])->name('orders.save');
    Route::delete('/orders/destroy', [App\Http\Controllers\OrderController::class, 'destroy'])->name('orders.destroy');
    Route::post('/orders/destroy_all', [App\Http\Controllers\OrderController::class, 'destroy_all'])->name('orders.destroy_all');
    Route::put('/orders/delete/img/{rid}/{rid2}', [App\Http\Controllers\OrderController::class, 'deleteImg'])->name('orders.deleteimg');
    Route::get('/orders/show/{id}/{param}', [App\Http\Controllers\OrderController::class, 'show'])->name('orders.show');

    Route::get('/receipts', [App\Http\Controllers\ReceiptController::class, 'index'])->name('receipts');
    Route::get('/receipts/show/{id}/{param}', [App\Http\Controllers\ReceiptController::class, 'show'])->name('receipts.show');
    Route::get('/receipts_pending', [App\Http\Controllers\ReceiptController::class, 'pending'])->name('receipts_pending');
    Route::get('/receipts_pending_term', [App\Http\Controllers\ReceiptController::class, 'pending_term'])->name('receipts_pending_term');
    Route::put('/receipts/generate', [App\Http\Controllers\ReceiptController::class, 'generate'])->name('receipts.generate');

    Route::get('/invoices', [App\Http\Controllers\InvoiceController::class, 'index'])->name('invoices');
    Route::get('/invoices/show/{id}/{param}', [App\Http\Controllers\InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/invoices_pending', [App\Http\Controllers\InvoiceController::class, 'pending'])->name('invoices_pending');
    Route::put('/invoices/generate', [App\Http\Controllers\InvoiceController::class, 'generate'])->name('invoices.generate');

    //file
    Route::post('/file/upload', function (Request $request) {
        $result = FileUploadService::fileStore($request);
        return response()->json(['success' => $result]);
    })->name('file.upload');

    Route::post('/file/upload/delete', function (Request $request) {
        $result = FileUploadService::fileDestroy($request);
        return $result;
    })->name('file.delete');

    Route::post('/file/upload/get', function (Request $request) {
        $result = FileUploadService::fileGetName($request);
        return $result;
    })->name('file.get');


    Route::get('/', [App\Http\Controllers\ContactController::class, 'index']);
    Route::get('/home', [App\Http\Controllers\ContactController::class, 'index'])->name('home');
});


Auth::routes();
