<?php
namespace App\ModulePayment\Traits;

use Illuminate\Support\Str;

trait TransportTrait
{

    public function getPayInfo($payment_id)
    {
        try {
            return $this->getState($payment_id);
        }
        catch (\Exception $exception) {

            throw YandexPaymentException::parse($exception);
        }
    }
}
