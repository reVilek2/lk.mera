<?php

namespace App\Providers;

use Carbon\Carbon;
use Config;
use Illuminate\Support\ServiceProvider;
use Jenssegers\Date\Date;
use App\Models\Document;
use App\Observers\DocumentObserver;
use App\Models\File;
use App\Observers\FileObserver;
use App\Services\Page;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        setlocale(LC_ALL, Config::get('app.lc_all'));
        Carbon::setLocale(Config::get('app.locale'));
        Date::setlocale(Config::get('app.locale'));

        Document::observe(DocumentObserver::class);
        File::observe(FileObserver::class);

        Page::setTitle();
    }
}
