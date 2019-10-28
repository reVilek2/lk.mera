<?php
namespace App\ModulePayment\Drivers\Tinkoff;

use Alcohol\ISO4217;
use App\Models\User;
use Illuminate\Contracts\Support\Arrayable;
use App\Services\PhoneNormalizer;
use Illuminate\Support\Arr;
use App\ModulePayment\Traits\DriversTrait;
use App\ModulePayment\Interfaces\PaymentServiceInterface;
use App\ModulePayment\Interfaces\PaymentTransportInterface;

/**
 * Payment service. Pay, Check, etc
 * @package AlpinaDigital\Services
 */
class TinkoffDriver implements PaymentServiceInterface
{
    use DriversTrait;

    /**
     * TinkoffMerchantAPI object
     *
     * @var PaymentTransportInterface
     */
    private $transport;

    /**
     * Module config
     *
     * @var array
     */
    private $config;

    /**
     * Notification info
     *
     * @var array
     */
    protected $response;

    public function __construct($config)
    {
        $this->setConfig($config);
    }

    /**
     * Pay
     *
     * @param int        $orderId
     * @param int        $paymentId
     * @param float      $amount
     * @param int|string $currency
     * @param string     $successReturnUrl
     * @param string     $failReturnUrl
     * @param string     $description
     * @param array      $extraParams
     * @param Arrayable  $receipt
     *
     * @return string
     * @throws \Exception
     */
    public function regularPayment($orderId,
                                   $amount,
                                   $paymentType = self::PAYMENT_TYPE_CARD,
                                   $description = '',
                                   $successReturnUrl = '',
                                   $failReturnUrl = '',
                                   $notificationUrl = '',
                                   $extraParams = [])
    {
        $currency = self::CURRENCY_RUR_ISO;
        $amount = number_format($amount * 100, 2, '.', '');

        $data = [
            'OrderId'           => $orderId,
            'Amount'            => $amount,
            'Currency'          => $currency,
            'Description'       => $description,
            'SuccessURL'        => $successReturnUrl,
            'NotificationURL'   => $notificationUrl
        ];

        $user = \Auth::user();
        $contact = null;
        $email = $user->email ?? null;
        $phone = $user->phone ? (new PhoneNormalizer())->normalize($user->phone) : null;

        if ($email || $phone) {
            $items[] = new ReceiptItem(
                'Пополнение баланса',
                1,
                $amount,
                ReceiptItem::TAX_NO_VAT,
                $currency
            );

            $receipt = new Receipt($phone, $email , $items, Receipt::TAX_SYSTEM_COMMON);

            $data['Receipt'] = $receipt->toArray();
        }

        if(isset($extraParams['save_card']) && $extraParams['save_card'] === true){
            $data['Recurrent'] = 'Y';
            $data['CustomerKey'] = $user->id;
        }

        $payment_response = $this->getTransport()->createPayment($data)->response;

        $this->setResponse($payment_response);

        return $payment_response ;
    }

    public function fastPayment($orderId,
                                $amount,
                                $card_id,
                                $description,
                                $notificationUrl,
                                $extraParams = [])
    {
        $currency = self::CURRENCY_RUR_ISO;
        $amount = number_format($amount * 100, 2, '.', '');

        $data = [
            'OrderId'           => $orderId,
            'Amount'            => $amount,
            'Currency'          => $currency,
            'Description'       => $description,
            'NotificationURL'   => $notificationUrl,
        ];

        $user = \Auth::user();
        $contact = null;
        $email = $user->email ?? null;
        $phone = $user->phone ? (new PhoneNormalizer())->normalize($user->phone) : null;

        if ($email || $phone) {
            $items[] = new ReceiptItem(
                'Пополнение баланса',
                1,
                $amount,
                ReceiptItem::TAX_NO_VAT,
                $currency
            );

            $receipt = new Receipt($phone, $email , $items, Receipt::TAX_SYSTEM_COMMON);

            $data['Receipt'] = $receipt->toArray();
        }
        $payment_response = $this->getTransport()->createPayment($data)->response;

        $data = [
            'PaymentId'           => $payment_response['PaymentId'],
            'TerminalKey'         => $this->config['merchantId'],
            'RebillId'            => $card_id,
        ];
        $payment_response = $this->getTransport()->createCharge($data)->response;
        $this->setResponse($payment_response);

        return $payment_response ;
    }

    /**
     * @param $payment_method_id
     * @return mixed
     */
    public function getPayInfo($payment_method_id)
    {
        $data = [
            'PaymentId' => $payment_method_id,
        ];

        $payment_response = $this->getTransport()->getPayInfo($data)->response;
        $this->setResponse($payment_response);

        return $payment_response;
    }

    public function getPaymentLink($orderId,
        $paymentId,
        $amount,
        $currency = self::CURRENCY_RUR_ISO,
        $paymentType = self::PAYMENT_TYPE_CARD,
        $successReturnUrl = '',
        $failReturnUrl = '',
        $description = '',
        $extraParams = [],
        $receipt = null){}

    /**
     * @param $amount
     * @param string $paymentType
     * @param string $description
     * @param $idempotencyKey
     * @param array $metadata
     * @return mixed
     * @throws \Exception
     */

    /**
     * Get configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Set driver configuration
     *
     * @param array $config
     *
     * @return $this
     */
    public function setConfig($config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Parse notification
     *
     * @param array $data
     *
     * @return mixed
     */
    public function setResponse($data)
    {
        $data['DateTime'] = date('Y-m-d H:i:s');
        $this->response = $data;

        return $this;
    }

    /**
     * Get response param by name
     *
     * @param string $name
     * @param string $default
     *
     * @return mixed|string
     */
    public function getResponseParam($name, $default = '')
    {
        return isset($this->response[$name]) ? $this->response[$name] : $default;
    }

    /**
     * Get order ID
     *
     * @return string
     */
    public function getOrderId()
    {
        return $this->getResponseParam('OrderId');
    }

    /**
     * Get operation status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->getResponseParam('Status');
    }

    /**
     * Is payment succeed
     *
     * @return bool
     */
    public function isSuccess()
    {
        return $this->getResponseParam('Success', 'false') === 'true';
    }

    /**
     * Get transaction ID
     *
     * @return string
     */
    public function getTransactionId()
    {
        return $this->getResponseParam('PaymentId');
    }

    /**
     * Get transaction amount
     *
     * @return float
     */
    public function getAmount()
    {
        $amount = $this->getResponseParam('Amount') / 100;
        return $amount;
    }

    /**
     * Get error code
     *
     * @return int
     */
    public function getErrorCode()
    {
        return $this->getResponseParam('ErrorCode');
    }

    /**
     * Get payment provider
     *
     * @return string
     */
    public function getProvider()
    {
        return 'tinkoff';
    }

    /**
     * Get PAn
     *
     * @return string
     */
    public function getPan()
    {
        return $this->getResponseParam('Pan');
    }

    /**
     * Get payment datetime
     *
     * @return string
     */
    public function getDateTime()
    {
        return $this->getResponseParam('DateTime');
    }

    /**
     * Set transport/protocol wrapper
     *
     * @param PaymentTransportInterface $protocol
     *
     * @return $this
     */
    public function setTransport(PaymentTransportInterface $protocol)
    {
        $this->transport = $protocol;

        return $this;
    }

    /**
     * Get transport
     *
     * @return PaymentTransportInterface
     */
    public function getTransport()
    {
        return $this->transport;
    }

    /**
     * Prepare response on notification request
     *
     * @param int $reponseCode
     *
     * @return string
     */
    public function getNotificationResponse($reponseCode = null)
    {
        $status = 200;
        if ($reponseCode && $status == $reponseCode) {
            $status = $reponseCode;
            return \response('OK', $status);
        }

        return \response('ERROR', 500);
    }

    /**
     * Prepare response on check request
     *
     * @param int $errorCode
     *
     * @return string
     */
    public function getCheckResponse($errorCode = null)
    {
        return $this->getTransport()->getNotificationResponse($this->response, $errorCode);
    }

    /**
     * Get last error code
     *
     * @return int
     */
    public function getLastError()
    {
        return 0;
    }

    /**
     * Get param by name
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getParam($name)
    {
        return $this->getResponseParam($name);
    }

    /**
     * Get name of payment service
     *
     * @return string
     */
    public function getName()
    {
        return 'tinkoff';
    }

    /**
     * Get payment id
     *
     * @return string
     */
    public function getPaymentId()
    {
        return $this->getResponseParam('PaymentId');
    }

    /**
     * Get payment id
     *
     * @return string
     */
    public function getPayLink()
    {
        return $this->getResponseParam('PaymentURL');
    }

    public function validateNotification($notification){
        $notification['Password'] = $this->config['secretKey'];
        ksort($notification);

        $check_string = '';
        foreach($notification as $key => $value){
            if($key == 'Token'){
                continue;
            }

            $check_string .= $value;
        }

        $expected_hash = hash ('sha256', $check_string);

        return $expected_hash == $notification['Token'];
    }
}
