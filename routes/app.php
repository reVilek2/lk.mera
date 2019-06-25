<?php
Route::get('/', function (){
    if (Auth::check()) {
        return redirect()->route('documents');
    }
    return redirect()->route('login');
});
// profile
Route::get('/profile', 'ProfileController@index')->name('profile');
Route::put('/profile/{user}', 'ProfileController@update')->name('profile.user.update');
Route::post('/profile/{user}/avatar', 'ProfileController@updateAvatar')->name('profile.avatar.update');
Route::put('/profile/password/{user}', 'ProfileController@updatePassword')->name('profile.password.update');
// documents
Route::get('/documents', 'DocumentController@index')->name('documents');
// finances
Route::get('/finances', 'FinanceController@index')->name('finances');
Route::get('/finances/payment', 'PaymentController@index')->name('payment');
Route::post('/finances/payment', 'PaymentController@create')->name('payment.create');
Route::get('/finances/check-payment', 'PaymentController@checkPayment')->name('payment.check');

// Only admin
Route::middleware(['role:admin'])->group(function () {
    Route::post('/documents/{document}/change-status', 'DocumentController@changeStatus')->name('documents.change.status');
    Route::post('/users/{user}/change-balance', 'UserController@changeBalance')->name('change.balance');
});

// Only manager or admin
Route::middleware(['role:admin|manager'])->group(function () {
    Route::get('/users', 'UserController@index')->name('users');
    Route::get('/users/{user}', 'UserController@show')->name('users.show');
    Route::post('/users/{user}/attach-manager', 'UserController@attachManager')->name('attach.manager');

    Route::post('/documents', 'DocumentController@create')->name('documents.create');
});

// Only manager or admin or client
Route::middleware(['role:admin|manager|client'])->group(function () {
    Route::get('/documents/{document}/files/{file}', 'DocumentController@documentFile')->name('documents.files');
    Route::get('/documents/{document}/paid', 'DocumentController@documentPaid')->name('documents.paid');
    Route::post('/documents/{document}/set-signed', 'DocumentController@setSigned')->name('documents.set.signed');
    Route::post('/documents/{document}/set-paid', 'DocumentController@setPaid')->name('documents.set.paid');
});



// ------ Chats and notification ------ //

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
