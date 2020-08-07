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
    Route::get('/shippingagent', 'ShippingagentController@index')->name('shippingagent.index');
    Route::post('/shippingagent/shippingagent_option_store', 'ShippingagentController@shippingagent_option_store')->name('shippingagent.store');

    Route::get('/shippingagent/configs', 'ShippingagentController@configs')->name('shippingagent.configs');
    Route::post('/shippingagent/configs/store', 'ShippingagentController@config_store')->name('shippingagent.configs.store');

    Route::get('/shippingagent/users', 'ShippingagentController@users')->name('shippingagent.users');
    Route::get('/shippingagent/verification/{id}', 'ShippingagentController@show_verification_request')->name('shippingagent_users.show_verification_request');

    Route::get('/shippingagent/approve/{id}', 'ShippingagentController@approve_user')->name('shippingagent_user.approve');
	Route::get('/shippingagent/reject/{id}', 'ShippingagentController@reject_user')->name('shippingagent_user.reject');

    Route::post('/shippingagent/approved', 'ShippingagentController@updateApproved')->name('shippingagent_user.approved');

    Route::post('/shippingagent/payment_modal', 'ShippingagentController@payment_modal')->name('shippingagent_user.payment_modal');
    Route::post('/shippingagent/pay/store', 'ShippingagentController@payment_store')->name('shippingagent_user.payment_store');

    Route::get('/shippingagent/payments/show/{id}', 'ShippingagentController@payment_history')->name('shippingagent_user.payment_history');
    Route::get('/refferal/users', 'ShippingagentController@refferal_users')->name('refferals.users');

});

//FrontEnd
Route::get('/shippingagent', 'ShippingagentController@apply_for_shippingagent')->name('shippingagent.apply');
Route::post('/shippingagent/store', 'ShippingagentController@store_shippingagent_user')->name('shippingagent.store_shippingagent_user');
Route::get('/shippingagent/user', 'ShippingagentController@user_index')->name('shippingagent.user.index');

Route::get('/shippingagent/payment/settings', 'ShippingagentController@payment_settings')->name('shippingagent.payment_settings');
Route::post('/shippingagent/payment/settings/store', 'ShippingagentController@payment_settings_store')->name('shippingagent.payment_settings_store');
