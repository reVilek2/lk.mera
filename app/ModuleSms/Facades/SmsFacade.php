<?php namespace App\ModuleSms\Facades;

use Illuminate\Support\Facades\Facade;

class SmsFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     * @codeCoverageIgnore
     */
    protected static function getFacadeAccessor()
    {
        return 'SmsService';
    }
}