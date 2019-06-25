<?php
namespace App\ModulePayment;

use App\ModulePayment\Drivers\YandexDriver;
use App\ModulePayment\Drivers\YandexKassa;
use App\ModulePayment\Facades\PaymentFacade;
use App\ModulePayment\Services\PaymentService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\AliasLoader;

/**
 * Facade for providers
 * @package professionalweb\payment
 */
class PaymentProvider extends ServiceProvider
{
    const PAYMENT_YANDEX = 'yandex';

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
        $loader = AliasLoader::getInstance();
        $loader->alias('PayService', PaymentFacade::class);
    }

    /**
     * Register application services.
     *
     * @return void
     */
    public function register()
    {
        // регистрация drivers
        $this->app->bind(YandexDriver::class, function ($app) {
            return (new YandexDriver(config('payment.yandex')))->setTransport(
                new YandexKassa(
                    config('payment.yandex.merchantId'),
                    config('payment.yandex.secretKey')
                )
            );
        });

        // регистрация сервиса
        $facade = (new PaymentService())->setDrivers([
            self::PAYMENT_YANDEX  => YandexDriver::class,
        ])->setCurrentDriver(config('payment.default_driver'));

        $this->app->instance('PayService', $facade);
        $this->app->instance(PaymentFacade::class, $facade);


    }
}