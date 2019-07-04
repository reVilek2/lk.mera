<?php

namespace App\Providers;

use App\Services\MobileDetectManager;
use Illuminate\Support\ServiceProvider;

class MobileDetectProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(MobileDetectManager::class);
    }
}
