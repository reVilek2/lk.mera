<?php
namespace App\ModulePayment\Processing;

use App\ModulePayment\Models\Payment;
use Illuminate\Support\Str;

abstract class Method
{
    const MAX_UNIQ_RECURSION = 20;

    /**
     * @param int $recursion_level
     * @return mixed
     * @throws \Exception
     */
    public function uniqid($recursion_level = 0)
    {
        if ($recursion_level >= self::MAX_UNIQ_RECURSION) {
            throw new \Exception('Превышен допустимый уровень рекурсии при создании уникального ключа.');
        }

        $uniqid = (string) Str::uuid();
        if (Payment::whereIdempotencyKey($uniqid)->first()) {
            $this->uniqid(++$recursion_level);
        }

        return $uniqid;
    }

    public function getPaymentByUniqueKey($key)
    {
        return Payment::whereIdempotencyKey($key)->first();
    }
}
