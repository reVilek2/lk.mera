<?php
// User Auth Routes...
Route::get('/login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('/login', 'Auth\LoginController@login')->name('login');
Route::get('/logout', 'Auth\LoginController@logout')->name('logout');

// User auth with social
Route::get('/social/facebook', 'Auth\SocialController@facebookLogin')->name('login.facebook.login');
Route::get('/social/facebook-callback', 'Auth\SocialController@facebookCallback')->name('login.facebook.callback');

Route::get('/social/google', 'Auth\SocialController@googleLogin')->name('login.google.login');
Route::get('/social/google-callback', 'Auth\SocialController@googleCallback')->name('login.google.callback');

// User Registration Routes...
Route::get('/register', 'Auth\RegisterController@showRegistrationForm')->name('register');
Route::get('/success-registration/{email}', 'Auth\RegisterController@successRegistrationByEmail')->name('successRegistrationByEmail');
Route::post('/register', 'Auth\RegisterController@register')->name('register');
// Email Routes...
Route::get('/email/confirm/{token}', 'Auth\EmailController@emailConfirm')->name('email.confirm');
Route::get('/email/confirm-info/{email}', 'Auth\EmailController@emailInformation')->name('email.confirm.info');
Route::post('/email/code-resend/{phone}', 'Auth\EmailController@codeResend')->name('email.code.resend');
// Phone Routes...
Route::get('/phone/confirm-info/{phone}', 'Auth\PhoneController@phoneInformation')->name('phone.confirm.info');
Route::get('/phone/confirm/{phone}', 'Auth\PhoneController@phoneConfirmForm')->name('phone.confirm');
Route::post('/phone/confirm/{phone}', 'Auth\PhoneController@phoneConfirm')->name('phone.confirm');
Route::post('/phone/code-resend/{phone}', 'Auth\PhoneController@codeResend')->name('phone.code.resend');
// Password forgot
Route::get('/password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.forgot');
Route::post('/password/phone', 'Auth\ForgotPasswordController@sendResetCode')->name('password.phone');
Route::get('/password/code/{phone}', 'Auth\ForgotPasswordController@code')->name('password.code');
Route::post('/password/code/{phone}', 'Auth\ForgotPasswordController@checkCode')->name('password.code.check');
Route::get('/password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
Route::post('/password/reset', 'Auth\ResetPasswordController@reset')->name('password.update');