<?php
namespace App\ModuleSms;

use App\ModuleSms\Drivers\SmsRuDriver;
use App\ModuleSms\Drivers\SmsRuTransport;
use App\ModuleSms\Facades\SmsFacade;
use App\ModuleSms\Services\SmsService;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;

class SmsProvider extends ServiceProvider
{
    const SMS_RU ='sms_ru';

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
        $loader->alias('SmsService', SmsFacade::class);
    }

    /**
     * Register application services.
     *
     * @return void
     */
    public function register()
    {
        // регистрация drivers
        $this->app->bind(SmsRuDriver::class, function ($app) {
            return (new SmsRuDriver(config('sms.' . self::SMS_RU)))->setTransport(
                new SmsRuTransport(
                    config('sms.' . self::SMS_RU .'.api_id'),
                    config('sms.' . self::SMS_RU .'.is_test')
                )
            );
        });

        // регистрация сервиса
        $facade = (new SmsService())->setDrivers([
            self::SMS_RU  => SmsRuDriver::class,
        ])->setCurrentDriver(config('sms.default_driver'));

        $this->app->instance('SmsService', $facade);
        $this->app->instance(SmsFacade::class, $facade);
    }
}