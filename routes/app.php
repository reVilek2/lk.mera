<?php
Route::get('/', function (){
    if (Auth::check()) {
        return redirect()->route('reports');
    }
    return redirect()->route('login');
})->name('home');
// profile
Route::get('/profile', 'ProfileController@index')->name('profile');
Route::post('/profile/{user}', 'ProfileController@update')->name('profile.user.update');
Route::post('/profile/{user}/avatar', 'ProfileController@updateAvatar')->name('profile.avatar.update');
Route::post('/profile/password/{user}', 'ProfileController@updatePassword')->name('profile.password.update');
// reports
Route::get('/reports', 'DocumentController@index')->name('reports');
// documents
Route::get('/documents', 'UserController@documentIndex')->name('documents');
Route::post('/documents', 'UserController@documentCreate')->name('document.create');
Route::get('/document/{file}', 'UserController@fileAction')->name('document');
// finances
Route::get('/finances', 'FinanceController@index')->name('finances');

// Only admin
Route::middleware(['role:admin'])->group(function () {
    Route::post('/documents/{document}/change-status', 'DocumentController@changeStatus')->name('documents.change.status');
    Route::post('/documents/{document}/delete', 'DocumentController@delete')->name('documents.delete');
    Route::post('/users/{user}/change-balance', 'UserController@changeBalance')->name('change.balance');
    Route::post('/profile/fast-confirm-phone/{user}', 'ProfileController@fastConfirmPhone')->name('profile.fast.confirm.phone');
    Route::post('/profile/fast-confirm-email/{user}', 'ProfileController@fastConfirmEmail')->name('profile.fast.confirm.email');
});

// Only manager or admin
Route::middleware(['role:admin|manager'])->group(function () {
    Route::get('/users', 'UserController@index')->name('users');
    Route::get('/users/{user}', 'UserController@show')->name('users.show');
    Route::post('/users/{user}/attach-manager', 'UserController@attachManager')->name('attach.manager');

    Route::post('/reports', 'DocumentController@create')->name('documents.create');
});

// Only manager or admin or client
Route::middleware(['role:admin|manager|client'])->group(function () {
    Route::get('/reports/{document}/files/{file}', 'DocumentController@documentFile')->name('reports.files');
    Route::post('/documents/{document}/set-signed', 'DocumentController@setSigned')->name('documents.set.signed');
    Route::post('/documents/{document}/set-paid', 'DocumentController@setPaid')->name('documents.set.paid');
});

// Only client or user
Route::middleware(['role:client|user'])->group(function () {
    Route::get('/finances/payment', 'PaymentController@index')->name('payment');
    Route::post('/finances/payment', 'PaymentController@create')->name('payment.create');
    Route::post('/finances/payment/pay-fast', 'PaymentController@payFast')->name('payment.payFast');
    Route::get('/finances/check-payment', 'PaymentController@checkPayment')->name('payment.check');
    Route::post('/finances/payment/set-card-default', 'PaymentController@setCardDefault')->name('finances.setCardDefault');
    Route::post('/finances/payment/remove-payment-pending', 'PaymentController@removePaymentPending')->name('finances.removePaymentPending');
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
Route::get('/service-text-notification/mark-as-read',function() {
    if (!Auth::user()) {
        return response()->json(['error' => 'User not authorized.'], 200);
    }
    Auth::user()->unreadServiceTextNotificationMessages->markAsRead();

    return response()->json(['status' => 'success'], 200);
});

//Recommendations
Route::get('/recommendations', 'RecommendationsController@index')->name('recommendations');
Route::post('/recommendations', 'RecommendationsController@create')->name('recommendation.create');
Route::post('/recommendations/{recommendation}/client-resolve', 'RecommendationsController@clientResolve')->name('recommendation.clientResolve');
