<?php
// Роуты не требующие авторизации!!!
Route::get('/clear-cache', function () {
    Artisan::call('cache:clear');
    Artisan::call('view:clear');
    Artisan::call('optimize:clear');
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    Artisan::call('storage:link');

    return 'all cache cleared';
});
Route::post('/refresh-csrf', function (){
    return csrf_token();
});

Route::post('/yandex-kassa-notify', 'PaymentNotifyController@yandex')->name('yandex.kassa.notify');
Route::get('/yandex-kassa-notify', 'PaymentNotifyController@yandex')->name('yandex.kassa.notify');
Route::post('/tinkoff-payment-notify', 'PaymentNotifyController@tinkoff')->name('tinkoff.payment.notify');
Route::get('/tinkoff-payment-notify', 'PaymentNotifyController@tinkoff')->name('tinkoff.payment.notify');
Route::post('/paykeeper-payment-notify', 'PaymentNotifyController@paykeeper')->name('paykeeper.payment.notify');
Route::get('/paykeeper-payment-notify', 'PaymentNotifyController@paykeeper')->name('paykeeper.payment.notify');
