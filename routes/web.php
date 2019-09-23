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

Route::post('/yandex-kassa-notify', 'YandexNotifyController@index')->name('yandex.kassa.notify');
Route::get('/yandex-kassa-notify', 'YandexNotifyController@index')->name('yandex.kassa.notify');

Route::get('testmail', function () {
    $recommendation = App\Models\Recommendation::find(77);
    return new \App\Mail\RecommendationAccepted($recommendation);
});
