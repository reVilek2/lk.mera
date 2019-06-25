<?php
namespace App\ModulePayment\Interfaces;

interface PaymentTransportInterface
{
    /**
     * Create Payment
     *
     * @param null $idempotencyKey
     * @param mixed $params
     *
     * @return string
     */
    public function getPaymentData($params, $idempotencyKey = null);
    /**
     * Prepare parameters
     *
     * @param array $params
     *
     * @return array
     */
    public function prepareParams($params);

    /**
     * get Info
     * @param $payment_id
     * @return mixed
     */
    public function getPayInfo($payment_id);
}