<?php
Route::get('/', function (){
    if (Auth::check()) {
        return redirect()->route('profile');
    }
    return redirect()->route('login');
});
Route::get('/profile', 'ProfileController@index')->name('profile');
Route::resource('user', 'UserController')
    ->only(['update'])
    ->names([
        'update' => 'user.update',
    ]);
Route::resource('user-password', 'UserChangePasswordController')
    ->only(['update'])
    ->names([
        'update' => 'user-password.update',
    ]);
