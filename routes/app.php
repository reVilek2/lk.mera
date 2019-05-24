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
Route::resource('user-avatar', 'UserAvatarController')
    ->only(['update'])
    ->names([
        'update' => 'user-avatar.update',
    ]);
Route::resource('user-password', 'UserChangePasswordController')
    ->only(['update'])
    ->names([
        'update' => 'user-password.update',
    ]);

Route::get('chat', 'ChatsController@index')->name('chat');
Route::get('chat/{user}', 'ChatsController@chatHistory')->name('chat.read');
Route::post('chat/{user}', 'ChatsController@sendMessage')->name('message.send');
//Route::get('messages', 'ChatsController@fetchMessages');
//Route::post('messages', 'ChatsController@sendMessage');
