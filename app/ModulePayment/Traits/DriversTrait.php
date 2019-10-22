<?php
namespace App\ModulePayment\Traits;

trait DriversTrait
{
    public function getPaymentMethod($type)
    {
        $map = [
            self::PAYMENT_TYPE_CARD         => 'bank_card',
            self::PAYMENT_TYPE_CASH         => 'cash',
            self::PAYMENT_TYPE_MOBILE       => 'mobile_balance',
            self::PAYMENT_TYPE_QIWI         => 'qiwi',
            self::PAYMENT_TYPE_SBERBANK     => 'sberbank',
            self::PAYMENT_TYPE_YANDEX_MONEY => 'yandex_money',
            self::PAYMENT_TYPE_ALFABANK     => 'alfabank',
        ];
        return isset($map[$type]) ? $map[$type] : $map[self::PAYMENT_TYPE_CARD];
    }

    private function substrCardNum(string $val)
    {
        return substr($val, 0, 4);
    }

}
