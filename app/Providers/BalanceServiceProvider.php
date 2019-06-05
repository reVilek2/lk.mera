<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class BalanceServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap application services.
     *
     * @return void
     */
    public function boot()
    {

    }

    /**
     * Register application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerMoneyAmount();
    }

    /**
     * Registers Chat.
     *
     * @return void
     */
    private function registerMoneyAmount()
    {
        $this->app->bind('MoneyAmount', function () {
            return $this->app->make(\App\Services\MoneyAmount::class);
        });
    }
}
