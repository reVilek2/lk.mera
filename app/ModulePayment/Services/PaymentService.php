<?php
namespace App\ModulePayment\Services;

use App\ModulePayment\Interfaces\PaymentServiceInterface;

class PaymentService implements PaymentServiceInterface
{
    /**
     * Available drivers
     *
     * @var array
     */
    private $drivers = [];
    /**
     * Current driver
     *
     * @var PaymentServiceInterface
     */
    private $currentDriver;
    /**
     * Name of current driver
     *
     * @var string
     */
    private $currentDriverName;

    /**
     * Set drivers
     *
     * @param array $drivers
     *
     * @return $this
     */
    public function setDrivers(array $drivers)
    {
        $this->drivers = $drivers;

        return $this;
    }

    /**
     * Get drivers
     *
     * @return array
     */
    public function getDrivers()
    {
        return $this->drivers;
    }
    /**
     * Set current driver
     *
     * @param string $name
     *
     * @return $this
     */
    public function setCurrentDriver($name)
    {
        $this->currentDriverName = $name;

        $this->makeCurrentDriver($name);

        return $this;
    }

    /**
     * Get driver by name
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getDriver($name)
    {
        return $this->getDrivers()[$name];
    }

    /**
     * Build driver
     *
     * @param string $name
     *
     * @return $this
     */
    protected function makeCurrentDriver($name)
    {
        $this->currentDriver = \App::make($this->getDriver($name), [
            'config' => config('payment.' . $name),
        ]);

        return $this;
    }

    /**
     * Get current driver name
     *
     * @return PaymentServiceInterface
     */
    public function getCurrentDriver()
    {
        return $this->currentDriver;
    }

    /**
     * Get name of current driver
     *
     * @return string
     */
    public function getDriverName()
    {
        return $this->currentDriverName;
    }

    /**
     * Pay
     *
     * @param float $amount
     * @param string $paymentType
     * @param string $description
     * @param string $successReturnUrl
     * @param array $metadata
     * @param array $extraParams
     * @return string
     */
    public function regularPayment($amount,
                                  $paymentType = self::PAYMENT_TYPE_CARD,
                                  $description = '',
                                  $successReturnUrl = '',
                                  $metadata = [],
                                  $extraParams = [])
    {
        return $this->getCurrentDriver()->regularPayment(
            $amount,
            $paymentType,
            $description,
            $successReturnUrl,
            $metadata,
            $extraParams
        );
    }

    public function fastPayment($amount,
                                $card_id,
                                $description = '',
                                $metadata = [],
                                $extraParams = [])
    {
        return $this->getCurrentDriver()->fastPayment(
            $amount,
            $card_id,
            $description,
            $metadata,
            $extraParams
        );
    }

    public function makePaymentTransaction($amount,
                                           $paymentType = self::PAYMENT_TYPE_CARD,
                                           $description = '',
                                           $idempotencyKey,
                                           $metadata = [])
    {
        return $this->getCurrentDriver()->makePaymentTransaction(
            $amount,
            $paymentType,
            $description,
            $idempotencyKey,
            $metadata);
    }

    public function updatePaymentTransaction($payment, $paymentData)
    {
        return $this->getCurrentDriver()->updatePaymentTransaction($payment, $paymentData);
    }

    public function getPaymentByUniqueKey($key)
    {
        return $this->getCurrentDriver()->getPaymentByUniqueKey($key);
    }

    public function getPayLink($paymentData)
    {
        return $this->getCurrentDriver()->getPayLink($paymentData);
    }

    public function checkPayment($payment)
    {
        return $this->getCurrentDriver()->checkPayment($payment);
    }

    /**
     * Обработка уведомлений от платежной системы
     *
     * @param $request
     * @return mixed
     */
    public function processNotificationRequest($request)
    {
        return $this->getCurrentDriver()->processNotificationRequest($request);
    }

    /**
     * Ответ платежной системе что мы получили уведомление
     *
     * @param int $errorCode
     *
     * @return string
     */
    public function getNotificationResponse($errorCode = null)
    {
        return $this->getCurrentDriver()->getNotificationResponse($errorCode);
    }

    /**
     * @param $payment_id
     * @return mixed
     */
    public function getPayInfo($payment_id)
    {
        return $this->getCurrentDriver()->getPayInfo($payment_id);
    }

    public function uniqid()
    {
        return $this->getCurrentDriver()->uniqid();
    }
}