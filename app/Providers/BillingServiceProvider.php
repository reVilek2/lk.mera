<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class BillingServiceProvider extends ServiceProvider
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
        $this->registerBilling();
    }

    /**
     * Registers MoneyAmount.
     *
     * @return void
     */
    private function registerMoneyAmount()
    {
        $this->app->bind('MoneyAmount', function () {
            return $this->app->make(\App\Services\MoneyAmountManager::class);
        });
    }

    /**
     * Registers Billing.
     *
     * @return void
     */
    private function registerBilling()
    {
        $this->app->bind('BillingService', function () {
            return $this->app->make(\App\Services\BillingManager::class);
        });
    }
}
