<?php
namespace App\ModulePayment;

use App\ModulePayment\Drivers\Yandex\YandexDriver;
use App\ModulePayment\Drivers\Yandex\YandexKassa;
use App\ModulePayment\Drivers\Tinkoff\TinkoffDriver;
use App\ModulePayment\Drivers\Tinkoff\TinkoffProtocol;
use App\ModulePayment\Processing\Methods\YandexMethod;
use App\ModulePayment\Processing\Methods\TinkoffMethod;
use App\ModulePayment\Facades\PaymentFacade;
use App\ModulePayment\Services\PaymentService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\AliasLoader;

class PaymentProvider extends ServiceProvider
{
    const PAYMENT_YANDEX = 'yandex';
    const PAYMENT_TINKOFF = 'tinkoff';

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

        $this->app->bind(TinkoffDriver::class, function ($app) {
            return (new TinkoffDriver(config('payment.tinkoff')))->setTransport(
                new TinkoffProtocol(
                    config('payment.tinkoff.merchantId'),
                    config('payment.tinkoff.secretKey')
                )
            );
        });

        // регистрация сервиса
        $facade = (new PaymentService())
            ->setDrivers([
                self::PAYMENT_YANDEX  => YandexDriver::class,
                self::PAYMENT_TINKOFF  => TinkoffDriver::class,
            ])
            ->setProcessings([
                self::PAYMENT_YANDEX  => YandexMethod::class,
                self::PAYMENT_TINKOFF  => TinkoffMethod::class,
            ])
            ->setCurrentDriver(config('payment.default_driver'));

        $this->app->instance('PayService', $facade);
        $this->app->instance(PaymentFacade::class, $facade);


    }
}
