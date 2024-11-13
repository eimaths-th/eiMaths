<?php

use Illuminate\Support\Facades\Route;


Route::get('/affiliate', 'AffiliateController@frontend')->name('affiliate.frontend');

Route::get('/my-affiliate', 'AffiliateController@index')->name('student.my_affiliate.index');

Route::prefix('affiliate')->as('affiliate.')->middleware(['auth', 'verified'])->group(function () {
    //affiliate
    Route::resource('my_affiliate', 'AffiliateController')->except(['destroy']);

    //add_paypal_account
    Route::post('add-or-update-paypal-account', 'AffiliateController@addOrUpdatePaypalAccount')->name('add_or_update_paypal_account');


    //withdraw_request
    Route::resource('withdraw_request', 'AffiliateTransactionController')->except(['destroy']);
    Route::post('destroy/withdraw_request', 'AffiliateTransactionController@destroy')->name('withdraw_request.destroy');
    Route::post('balance-transfer-to-wallet', 'AffiliateTransactionController@balanceTransferToWallet')->name('balance_transfer_to_wallet');
    Route::get('commission', 'AffiliateTransactionController@commission')->name('commission')->middleware('RoutePermissionCheck:affiliate.commission');
    Route::get('commission-data', 'AffiliateTransactionController@commissionData')->name('commissionData')->middleware('RoutePermissionCheck:affiliate.commission');
    Route::get('pending-withdraw', 'AffiliateTransactionController@pendingWithdraw')->name('pending_withdraw');
    Route::get('pending-withdraw/datatable', 'AffiliateTransactionController@pendingWithdrawDatatable')->name('pending_withdraw.datatable');
    Route::get('confirm-withdraw/{id}', 'AffiliateTransactionController@confirmWithdraw')->name('confirm_withdraw');
    Route::get('complete-withdraw', 'AffiliateTransactionController@completeWithdraw')->name('complete_withdraw');
    Route::get('complete-withdraw/datatable', 'AffiliateTransactionController@completeWithdrawDatatable')->name('complete_withdraw.datatable');
    Route::get('pending-commission/approved', 'AffiliateTransactionController@pendingCommissionApproved')->name('pending_commission.approved');

    Route::get('/affiliate-edit', 'AffiliateController@frontendEdit')->name('frontend.edit');

    //configuration
    Route::get('configurations', 'AffiliateController@configurationIndex')->name('configurations.update');
    Route::post('configurations', 'AffiliateController@configurationUpdate');
    //affiliate user
    Route::get('users', 'AffiliateUserController@index')->name('users.index');
    Route::get('users/datatable', 'AffiliateUserController@datatable')->name('users.datatable');
    Route::get('user/approved/{id}', 'AffiliateUserController@approved')->name('users.approved');
    Route::get('user/request', 'AffiliateUserController@userrequest')->name('users.request');
    Route::get('/dashboard', 'AffiliateUserController@dashboard')->name('dashboard');

});

Route::prefix('affiliate')->as('affiliate.')->middleware(['guest'])->group(function () {
    Route::get('registration', 'AffiliateAuthController@showRegistrationFrom')->name('registration');
    Route::post('register', 'AffiliateAuthController@register')->name('register');
});
