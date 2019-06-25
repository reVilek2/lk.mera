<?php
namespace App\ModulePayment\Interfaces;

interface PaymentServiceInterface
{
    /**
     * Rubles
     */
    const CURRENCY_RUR = 'RUB';
    const CURRENCY_RUR_ISO = 643;

    const PAYMENT_TYPE_CARD = 'card';
    const PAYMENT_TYPE_YANDEX_MONEY = 'yandex.money';
    const PAYMENT_TYPE_CASH = 'cash';
    const PAYMENT_TYPE_MOBILE = 'mobile';
    const PAYMENT_TYPE_QIWI = 'qiwi';
    const PAYMENT_TYPE_SBERBANK = 'sberbank';
    const PAYMENT_TYPE_ALFABANK = 'alfabank';

    const WHITE_LIST_TYPE = [
        self::PAYMENT_TYPE_CARD => true,
        self::PAYMENT_TYPE_YANDEX_MONEY => true,
    ];
}