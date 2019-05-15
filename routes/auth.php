<?php
// User Auth Routes...
Route::get('/login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('/login', 'Auth\LoginController@login')->name('login');
Route::get('/logout', 'Auth\LoginController@logout')->name('logout');
// User Registration Routes...
Route::get('/register', 'Auth\RegisterController@showRegistrationForm')->name('register');
Route::post('/register', 'Auth\RegisterController@register')->name('register');
// Email Routes...
Route::get('/email/confirm/{token}', 'Auth\EmailController@emailConfirm')->name('email.confirm');
Route::get('/email/confirm-info/{email}', 'Auth\EmailController@emailInformation')->name('email.confirm.info');