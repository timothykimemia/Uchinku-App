<?php

/*
|--------------------------------------------------------------------------
| Shippingagent Routes
|--------------------------------------------------------------------------
|
| Here is where you can register admin routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//Admin
Route::group(['prefix' =>'admin', 'middleware' => ['auth', 'admin']], function(){
    Route::get('/shippingagent', 'AffiliateController@index')->name('shippingagent.index');
    Route::post('/shippingagent/affiliate_option_store', 'AffiliateController@affiliate_option_store')->name('shippingagent.store');

    Route::get('/shippingagent/configs', 'AffiliateController@configs')->name('shippingagent.configs');
    Route::post('/shippingagent/configs/store', 'AffiliateController@config_store')->name('shippingagent.configs.store');

    Route::get('/shippingagent/users', 'AffiliateController@users')->name('shippingagent.users');
    Route::get('/shippingagent/verification/{id}', 'AffiliateController@show_verification_request')->name('affiliate_users.show_verification_request');

    Route::get('/shippingagent/approve/{id}', 'AffiliateController@approve_user')->name('affiliate_user.approve');
	Route::get('/shippingagent/reject/{id}', 'AffiliateController@reject_user')->name('affiliate_user.reject');

    Route::post('/shippingagent/approved', 'AffiliateController@updateApproved')->name('affiliate_user.approved');

    Route::post('/shippingagent/payment_modal', 'AffiliateController@payment_modal')->name('affiliate_user.payment_modal');
    Route::post('/shippingagent/pay/store', 'AffiliateController@payment_store')->name('affiliate_user.payment_store');

    Route::get('/shippingagent/payments/show/{id}', 'AffiliateController@payment_history')->name('affiliate_user.payment_history');
    Route::get('/refferal/users', 'AffiliateController@refferal_users')->name('refferals.users');

});

//FrontEnd
Route::get('/shippingagent', 'AffiliateController@apply_for_affiliate')->name('shippingagent.apply');
Route::post('/shippingagent/store', 'AffiliateController@store_affiliate_user')->name('shippingagent.store_affiliate_user');
Route::get('/shippingagent/user', 'AffiliateController@user_index')->name('shippingagent.user.index');

Route::get('/shippingagent/payment/settings', 'AffiliateController@payment_settings')->name('shippingagent.payment_settings');
Route::post('/shippingagent/payment/settings/store', 'AffiliateController@payment_settings_store')->name('shippingagent.payment_settings_store');
