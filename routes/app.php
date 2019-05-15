<?php
Route::get('/', function (){
    if (Auth::check()) {
        return redirect()->route('profile');
    }
    return redirect()->route('login');
});
Route::get('/profile', 'ProfileController@index')->name('profile');
