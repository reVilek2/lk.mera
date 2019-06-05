<?php
namespace App\Services;


class MoneyAmount
{
    const PRECISION = 10000;

    public function toExternal($amount)
    {
        return $amount * self::PRECISION;
    }

    public function toReadable($amount)
    {
        return $amount / self::PRECISION;
    }

    function toHumanize($number)
    {
        $tmp = explode(',', $number);
        $out = number_format($tmp[0], 0, '.', ' '). ' руб.';
        if (isset($tmp[1])) $out .= ' '.$tmp[1].' коп.';

        return $out;
    }
}