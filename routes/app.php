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
Route::get('chat/{chat}', 'ChatsController@chatHistory')->name('chat.read');
Route::post('chat/{chat}', 'ChatsController@sendMessage')->name('message.send');
Route::get('chat/{chatid}/mark-as-read', function($chatid) {
    if (!Auth::user()) {
        return response()->json(['error' => 'User not authorized.'], 200);
    }
    $chat = ChatService::getChatById($chatid);
    if ($chat) {
        ChatService::markChatAsRead($chat, Auth::user());
        return response()->json(['status' => 'success'], 200);
    }
    return response()->json(['status' => 'error'], 200);
});
Route::get('/notification/mark-as-read',function() {
    if (!Auth::user()) {
        return response()->json(['error' => 'User not authorized.'], 200);
    }
    Auth::user()->unreadNotificationMessages->markAsRead();

    return response()->json(['status' => 'success'], 200);
});
