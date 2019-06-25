<?php
namespace App\ModulePayment\Facades;

use Illuminate\Support\Facades\Facade;

class PaymentFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     * @codeCoverageIgnore
     */
    protected static function getFacadeAccessor()
    {
        return 'PayService';
    }
}