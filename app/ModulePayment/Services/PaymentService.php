<?php
namespace App\ModulePayment\Services;

use App\ModulePayment\Interfaces\PaymentServiceInterface;
use App\ModulePayment\Interfaces\ModelPaymentInterface;

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
     * Available processings
     *
     * @var array
     */
    private $processings = [];
    /**
     * Current processing
     *
     * @var PaymentServiceInterface
     */
    private $currentProcessing;

    /**
     * Name of current processing
     *
     * @var string
     */
    private $currentProcessingName;

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
        $this->currentProcessingName = $name;

        $this->makeCurrentDriver($name);

        $this->setCurrentProcessing($name);

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
        $this->currentDriver = \App::make($this->getDriver($name));

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
     * Set processings
     *
     * @param array $processings
     *
     * @return $this
     */
    public function setProcessings(array $processings)
    {
        $this->processings = $processings;

        return $this;
    }

    /**
     * Get processings
     *
     * @return array
     */
    public function getProcessings()
    {
        return $this->processings;
    }
    /**
     * Set current driver
     *
     * @param string $name
     *
     * @return $this
     */
    public function setCurrentProcessing($name)
    {
        $this->currentProcessingName = $name;

        $this->makeCurrentProcessing($name);

        return $this;
    }

    /**
     * Get driver by name
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getProcessing($name)
    {
        return $this->getProcessings()[$name];
    }

    /**
     * Build driver
     *
     * @param string $name
     *
     * @return $this
     */
    protected function makeCurrentProcessing($name)
    {
        $this->currentProcessing = \App::make($this->getProcessing($name));

        return $this;
    }

    /**
     * Get current driver name
     *
     * @return PaymentServiceInterface
     */
    public function getCurrentProcessing()
    {
        return $this->currentProcessing;
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
    public function regularPayment( $orderId,
                                    $amount,
                                    $paymentType = self::PAYMENT_TYPE_CARD,
                                    $description = '',
                                    $successReturnUrl = '',
                                    $failReturnUrl = '',
                                    $notificationUrl = '',
                                    $extraParams = [])
    {
        return $this->getCurrentDriver()->regularPayment(
            $orderId,
            $amount,
            $paymentType,
            $description,
            $successReturnUrl,
            $failReturnUrl,
            $notificationUrl,
            $extraParams
        );
    }

    public function fastPayment($orderId,
                                $amount,
                                $card_id,
                                $description = '',
                                $extraParams = [])
    {
        return $this->getCurrentDriver()->fastPayment(
            $orderId,
            $amount,
            $card_id,
            $description,
            $extraParams
        );
    }

    public function makePaymentTransaction($amount,
                                           $paymentType = self::PAYMENT_TYPE_CARD,
                                           $description = '',
                                           $idempotencyKey,
                                           $metadata = [])
    {
        $paymentType = $this->getCurrentDriver()->getPaymentMethod($paymentType);

        return $this->getCurrentProcessing()->makePaymentTransaction(
            $amount,
            $paymentType,
            $description,
            $idempotencyKey,
            $metadata);
    }

    public function updatePaymentTransaction($payment)
    {
        $driver = $this->getCurrentDriver();

        return $this->getCurrentProcessing()->updatePaymentTransaction($payment, $driver);
    }

    public function getPaymentByUniqueKey($key)
    {
        return $this->getCurrentProcessing()->getPaymentByUniqueKey($key);
    }

    public function getPayLink()
    {
        return $this->getCurrentDriver()->getPayLink();
    }

    public function checkPayment($payment)
    {
        if (!$payment || !$payment instanceof ModelPaymentInterface) {
            return null;
        }

        $driver = $this->getCurrentDriver();
        $driver->getPayInfo($payment->payment_id);

        return $this->getCurrentProcessing()->processPayment($payment, $driver);
    }

    /**
     * Обработка уведомлений от платежной системы
     *
     * @param $request
     * @return mixed
     */
    public function processNotificationRequest($notification)
    {
        $driver = $this->getCurrentDriver();
        if($driver->validateNotification($notification)){
            return false;
        }

        return $this->getCurrentProcessing()->processNotificationRequest($notification, $driver);
    }

    /**
     * Ответ платежной системе что мы получили уведомление
     *
     * @param int $reponseCode
     *
     * @return string
     */
    public function getNotificationResponse($reponseCode = null)
    {
        return $this->getCurrentDriver()->getNotificationResponse($reponseCode);
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
        return $this->getCurrentProcessing()->uniqid();
    }
}
