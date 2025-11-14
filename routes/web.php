<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\EiFormController;
use App\Http\Controllers\FooterSettingController;
use App\Http\Controllers\HistoriesController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ParentController;
use App\Http\Controllers\PermissionsController;
use App\Http\Controllers\PromotionImageController;
use App\Http\Controllers\QrCodePaymentController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TeachingPeriodController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\ImageUploadController;
use App\Http\Controllers\QuestionBulkController;

use App\Services\FileUploadService;
use Illuminate\Http\Request;



Route::group(['middleware' => ['auth']], function () {
    //Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    //Route::get('/roles/store', [RoleController::class, 'store'])->name('roles.store');
    Route::get('/roles/edit/{id}', [RoleController::class, 'edit'])->name('roles.edit2');
    Route::put('/roles/save/{id}', [RoleController::class, 'update'])->name('roles.save');
    //Route::delete('/roles/destroy', [RoleController::class, 'destroy'])->name('roles.destroy');
    Route::post('/roles/destroy_all', [RoleController::class, 'destroy_all'])->name('roles.destroy_all');
    Route::resource('users', UserController::class);
    Route::get('/users/admin/affiliate', [UserController::class,  'affiliateUser'])->name('users.affiliate');
    Route::DELETE('/users/admin/affiliate/destroy', [UserController::class,  'destroyAffiliateUser'])->name('users.affiliate.destroy');

    Route::get('/logout', [UserController::class, 'perform'])->name('logout.perform');
    Route::get('/users/edit/{id}', [UserController::class, 'edit'])->name('users.edit2');
    Route::put('/users/save/{id}', [UserController::class, 'update'])->name('users.save');
    Route::post('/users/destroy_all', [UserController::class, 'destroy_all'])->name('users.destroy_all');
    Route::get('/users/find/{type}/{position}', [UserController::class, 'find'])->name('users.find');
    Route::resource('roles', RoleController::class);
    Route::resource('permission', PermissionsController::class);

    Route::get('/contacts', [App\Http\Controllers\ContactController::class, 'index'])->name('contacts');
    Route::get('/graduated', [App\Http\Controllers\ContactController::class, 'graduated'])->name('contacts.graduated');
    Route::get('/contacts/running/{centre}', [App\Http\Controllers\ContactController::class, 'create'])->name('contacts.running');
    Route::post('/contacts/store', [App\Http\Controllers\ContactController::class, 'store'])->name('contacts.store');
    Route::post('/contacts/receipt', [App\Http\Controllers\ContactController::class, 'receipt'])->name('contacts.receipt');
    Route::get('/contacts/edit/{id}', [App\Http\Controllers\ContactController::class, 'edit'])->name('contacts.edit');
    Route::get('/contacts/getRef/{id}', [App\Http\Controllers\ContactController::class, 'getRef'])->name('contacts.getRef');
    Route::get('/contacts/course/{id}', [App\Http\Controllers\ContactController::class, 'get_course'])->name('contacts.course');
    Route::put('/contacts/save/{id}', [App\Http\Controllers\ContactController::class, 'update'])->name('contacts.save');
    Route::delete('/contacts/destroy', [App\Http\Controllers\ContactController::class, 'destroy'])->name('contacts.destroy');
    Route::post('/contacts/destroy_all', [App\Http\Controllers\ContactController::class, 'destroy_all'])->name('contacts.destroy_all');
    Route::get('contacts/{id}/fast-history', [ContactController::class, 'fastHistory'])->name('contacts.fastHistory');
    Route::get('/contacts/export-by-centre', [App\Http\Controllers\ContactController::class, 'exportContactsByCentre'])->name('contacts.exportByCentre');
    Route::get('/contacts/get-by-centre', [App\Http\Controllers\ContactController::class, 'getContactsByCentre'])->name('contacts.getByCentre');


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
    Route::get('/products/running/{centre}', [App\Http\Controllers\ProductController::class, 'create'])->name('products.running');
    Route::post('/products/store', [App\Http\Controllers\ProductController::class, 'store'])->name('products.store');
    Route::get('/products/find/{id}', [App\Http\Controllers\ProductController::class, 'pfind'])->name('products.find');
    Route::get('/products/edit/{id}', [App\Http\Controllers\ProductController::class, 'edit'])->name('products.edit');
    Route::get('/products/barcode/{id}', [App\Http\Controllers\ProductController::class, 'barcode'])->name('products.barcode');
    Route::get('/products/qbarcode/{id}', [App\Http\Controllers\ProductController::class, 'qbarcode'])->name('products.qbarcode');
    Route::put('/products/save/{id}', [App\Http\Controllers\ProductController::class, 'update'])->name('products.save');
    Route::put('/products/add_stock/{id}', [App\Http\Controllers\ProductController::class, 'add_stock'])->name('products.add_stock');
    Route::put('/products/rm_stock/{id}', [App\Http\Controllers\ProductController::class, 'rm_stock'])->name('products.rm_stock');
    Route::delete('/products/destroy', [App\Http\Controllers\ProductController::class, 'destroy'])->name('products.destroy');
    Route::post('/products/destroy_all', [App\Http\Controllers\ProductController::class, 'destroy_all'])->name('products.destroy_all');
    Route::get('/products/stock', [App\Http\Controllers\ProductController::class, 'getStock'])->name('products.stock');
    Route::get('/products/des/{centre}/{type}', [App\Http\Controllers\ProductController::class, 'getDes'])->name('products.des');
    Route::get('/products/print', [App\Http\Controllers\ProductController::class, 'printProduct'])->name('products.print');

    Route::get('/level/find/{type}/{level}', [App\Http\Controllers\ContactController::class, 'lfind'])->name('level.find');
    Route::get('/term/find/{type}/{level}/{term}', [App\Http\Controllers\ContactController::class, 'tfind'])->name('term.find');
    Route::get('/scoin', [App\Http\Controllers\ContactController::class, 'sCoin'])->name('term.scoin');

    Route::get('/orders', [App\Http\Controllers\OrderController::class, 'index'])->name('orders');
    Route::get('/orders/running', [App\Http\Controllers\OrderController::class, 'create'])->name('orders.running');
    Route::post('/orders/store', [App\Http\Controllers\OrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/log/{id}', [App\Http\Controllers\OrderController::class, 'log'])->name('orders.log');
    Route::get('orders/edit/{id}', [App\Http\Controllers\OrderController::class, 'edit'])->name('orders.edit');
    Route::get('orders/cancle/{id}', [App\Http\Controllers\OrderController::class, 'cancle'])->name('orders.cancle');
    Route::put('orders/confirm', [App\Http\Controllers\OrderController::class, 'confirm'])->name('orders.confirm');
    Route::put('/orders/save/{id}', [App\Http\Controllers\OrderController::class, 'update'])->name('orders.save');
    Route::delete('/orders/destroy', [App\Http\Controllers\OrderController::class, 'destroy'])->name('orders.destroy');
    Route::post('/orders/destroy_all', [App\Http\Controllers\OrderController::class, 'destroy_all'])->name('orders.destroy_all');
    Route::put('/orders/delete/img/{rid}/{rid2}', [App\Http\Controllers\OrderController::class, 'deleteImg'])->name('orders.deleteimg');
    Route::get('/orders/show/{id}/{param}', [App\Http\Controllers\OrderController::class, 'show'])->name('orsders.show');

    Route::get('/receipts', [App\Http\Controllers\ReceiptController::class, 'index'])->name('receipts');
    Route::get('/receipts/show/{id}/{param}', [App\Http\Controllers\ReceiptController::class, 'show'])->name('receipts.show');
    Route::get('/receipts/price_text/{price}', [App\Http\Controllers\ReceiptController::class, 'price_text'])->name('receipts.price_text');
    Route::put('/receipts/save/{id}', [App\Http\Controllers\ReceiptController::class, 'update'])->name('receipts.save');
    Route::get('/receipts_pending', [App\Http\Controllers\ReceiptController::class, 'pending'])->name('receipts_pending');
    Route::get('/receipts_pending_term', [App\Http\Controllers\ReceiptController::class, 'pending_term'])->name('receipts_pending_term');
    Route::put('/receipts/generate', [App\Http\Controllers\ReceiptController::class, 'generate'])->name('receipts.generate');
    Route::put('/receipts/delete/img/{rid}/{rid2}', [App\Http\Controllers\ReceiptController::class, 'deleteImg'])->name('receipts.deleteimg');

    Route::get('/invoices', [App\Http\Controllers\InvoiceController::class, 'index'])->name('invoices');
    Route::get('/invoices/show/{id}/{param}', [App\Http\Controllers\InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/invoices_pending', [App\Http\Controllers\InvoiceController::class, 'pending'])->name('invoices_pending');
    Route::put('/invoices/generate', [App\Http\Controllers\InvoiceController::class, 'generate'])->name('invoices.generate');

    // Route::get('/histories', [App\Http\Controllers\HistoriesController::class, 'index'])->name('histories');
    Route::resource('histories', HistoriesController::class);
    Route::get('/student/find/{type}/{std_id}', [App\Http\Controllers\HistoriesController::class, 'stdfind'])->name('student.find');
    Route::post('/histories/destroy_all', [App\Http\Controllers\HistoriesController::class, 'destroy_all'])->name('histories.destroy_all');
    Route::get('/histories/log/{id}', [App\Http\Controllers\HistoriesController::class, 'log'])->name('histories.log');

    Route::delete('/histories/{id}', [App\Http\Controllers\HistoriesController::class, 'destroy'])->name('histories.destroys');

    Route::get('create/{centre}', [App\Http\Controllers\HistoriesController::class, 'create'])->name('create');
    Route::get('/histories/running/{centre}', [App\Http\Controllers\HistoriesController::class, 'create'])->name('histories.running');
    Route::put('/history/update/{id}', [App\Http\Controllers\HistoriesController::class, 'update'])->name('history.update');

    Route::post('/history/upload', [App\Http\Controllers\HistoryStudentImgController::class, 'store'])->name('history.upload');
    Route::post('/history/upload/delete', [App\Http\Controllers\HistoryStudentImgController::class, 'destroy'])->name('history.delete');

    Route::get('/histories/show/{id}', [App\Http\Controllers\HistoriesController::class, 'show'])->name('history.show');

    Route::resource('qrcode_payment', QrCodePaymentController::class);
    Route::resource('TeachingPeriod', TeachingPeriodController::class);
    Route::resource('promotion-img', PromotionImageController::class);
    Route::resource('footer-setting', FooterSettingController::class);
    //createOrUpdate
    Route::post('/footer-setting/createOrUpdate', [FooterSettingController::class, 'createOrUpdate'])->name('footer-setting.createOrUpdate');

    Route::get('TeachingPeriod/find/{department_id}/{day}', [TeachingPeriodController::class, 'find'])->name('TeachingPeriod.find');

    Route::post('/promotion-images/update-status', [PromotionImageController::class, 'updateStatus'])->name('promotion-images.updateStatus');
    Route::post('/promotion-images/update-status2', [PromotionImageController::class, 'updateStatus2'])->name('promotion-images.updateStatus2');
    Route::post('/promotion-images/disable-all', [PromotionImageController::class, 'disableAll'])->name('promotion-images.disableAll');
    Route::post('/quizzes/{quiz}/order', [QuestionBulkController::class, 'updateOrder'])->name('quizzes.order.update');

    //parent route
    Route::resource('parent', ParentController::class);
    Route::delete('/parent/{id}', [App\Http\Controllers\ParentController::class, 'destroy'])->name('parents.destroys');
    Route::get('create/{centre}', [App\Http\Controllers\ParentController::class, 'create'])->name('create');
    Route::get('/parent/running/{centre}', [App\Http\Controllers\ParentController::class, 'create'])->name('parents.running');
    Route::put('/parent/update/{id}', [App\Http\Controllers\ParentController::class, 'update'])->name('parents.update');

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

    /* Route::get('/', [App\Http\Controllers\ContactController::class, 'index']);
    Route::get('/home', [App\Http\Controllers\ContactController::class, 'index'])->name('home'); */
    // Route::get('/', [HomeController::class, 'index']);

    Route::get('/', function () {
        $user = Auth::user();
        if ($user && $user->hasRole('Affiliate-user') || $user->hasRole('User')) {
            return redirect()->route('welcome');
        }
        return redirect()->route('home');

    });

    Route::get('/welcome', [HomeController::class, 'welcome'])->name('welcome');

    // Route::get('/', function () {
    //     $user = Auth::user();

    //     // Check if the user is logged in and has the 'Affiliate_user' role
    //     if ($user && $user->hasRole('Affiliate_user')) {
    //         return redirect()->route('my_affiliate.index');
    //     }
    //     return redirect()->route('home.index'); // Adjust this as needed
    // });
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::post('/contact/export', [ContactController::class, 'exportContact'])->name('contact.export');
    Route::post('/reciept/export', [ReceiptController::class, 'exportReciept'])->name('reciept.export');

    Route::get('/ei_form', [EiFormController::class, 'backEndIndex'])->name('ei_form.backEndIndex');

    // Blogs
    Route::resource('blogs', BlogController::class);

    // Image Upload
    Route::post('/upload/image', [ImageUploadController::class, 'upload'])->name('upload.image');
});

Route::get('/teacher', function () {
    return redirect()->to('https://teacher.eimaths-th.com/');
});


Auth::routes();
Route::get('/auth/google', [App\Http\Controllers\GoogleController::class, 'loginWithGoogle'])->name('google.login');
Route::get('/auth/google/callback', [App\Http\Controllers\GoogleController::class, 'callbackFromGoogle'])->name('google.callback');
Route::get('/blank', [App\Http\Controllers\HomeController::class, 'blank'])->name('blank');
