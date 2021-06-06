<?php

namespace App\ModulePayment\Drivers\Paykeeper;

use App\ModulePayment\Interfaces\PaymentServiceInterface;
use App\ModulePayment\Traits\DriversTrait;
use App\Services\PhoneNormalizer;
use Illuminate\Contracts\Support\Arrayable;

class PaykeeperDriver implements PaymentServiceInterface
{
    use DriversTrait;

    public $payment_id;

    public function __construct($config)
    {
        $this->setConfig($config);
    }

    /**
     * Pay
     *
     * @param  int  $orderId
     * @param  int  $paymentId
     * @param  float  $amount
     * @param  int|string  $currency
     * @param  string  $successReturnUrl
     * @param  string  $failReturnUrl
     * @param  string  $description
     * @param  array  $extraParams
     * @param  Arrayable  $receipt
     *
     * @return string
     * @throws \Exception
     */
    public function regularPayment(
        $orderId,
        $amount,
        $paymentType = self::PAYMENT_TYPE_CARD,
        $description = '',
        $successReturnUrl = '',
        $failReturnUrl = '',
        $notificationUrl = '',
        $extraParams = []
    ) {
        $currency = self::CURRENCY_RUR_ISO;

        $user = \Auth::user();
        $contact = null;
        $email = $user->email ?? null;
        $phone = $user->phone ? (new PhoneNormalizer())->normalize($user->phone) : null;

        $data = array(
            "pay_amount"   => $amount,
            "clientid"     => $contact,
            "orderid"      => $orderId,
            "client_email" => $email,
            "service_name" => $description,
            "client_phone" => $phone
        );

        $payment_response = $this->getTransport()->createPayment($data);

        $this->setResponse($payment_response);
        $this->payment_id = $this->getResponseParam('invoice_id');
        return $payment_response;
    }

    /**
     * @param $amount
     * @param  string  $paymentType
     * @param  string  $description
     * @param $idempotencyKey
     * @param  array  $metadata
     * @return mixed
     * @throws \Exception
     */
    public function fastPayment(
        $orderId,
        $amount,
        $card_id,
        $description,
        $notificationUrl,
        $extraParams = []
    ) {
        throw new \Exception('method not supported');
    }

    /**
     * @param $payment_method_id
     * @return mixed
     */
    public function getPayInfo($payment_method_id)
    {
        $payment_response = $this->getTransport()->getPayInfo($payment_method_id);
        $this->setResponse($payment_response);

        return $payment_response;
    }

    /**
     * Prepare response on notification request
     *
     * @param  int  $reponseCode
     *
     * @return string
     */
    public function getNotificationResponse($reponseCode = null)
    {
        $status = 200;
        if ($reponseCode && $status == $reponseCode) {
            $status = $reponseCode;

            $id = request('id');

            return \response('OK'.md5($id.$this->config['secret']), $status);
        }

        return \response('ERROR', 500);
    }

    /**
     * Get name of payment service
     *
     * @return string
     */
    public function getName()
    {
        return 'paykeeper';
    }

    /**
     * Get payment id
     *
     * @return string
     */
    public function getPaymentId()
    {
        return $this->payment_id;
    }

    /**
     * Get payment id
     *
     * @return string
     */
    public function getPayLink()
    {
        return $this->getResponseParam('invoice_url');
    }

    /**
     * Get amount
     *
     * @return string
     */
    public function getAmount()
    {
        return $this->getResponseParam('sum');
    }

    public function validateNotification($notification)
    {
        $order_id = $notification['orderid'] ?? null;
        $id = $notification['id'] ?? null;
        $key = $notification['key'] ?? null;
        $email = $notification['clientid'] ?? null;
        $sum = $notification['sum'] ?? null;

        /* проверка цифровой подписи */
        return $key !== md5($id.$sum.$email.$order_id.$this->config['secret']);
    }
}