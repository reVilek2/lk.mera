<?php
namespace App\ModulePayment\Drivers\Yandex;

use Alcohol\ISO4217;
use App\Models\User;
use App\ModulePayment\Interfaces\PaymentServiceInterface;
use App\ModulePayment\Interfaces\PaymentTransportInterface;
use App\ModulePayment\PaymentProvider;
use App\Services\PhoneNormalizer;
use Illuminate\Support\Arr;
use App\ModulePayment\Traits\DriversTrait;

/**
 * Payment service. Pay, Check, etc
 */
class YandexDriver implements PaymentServiceInterface
{
    use DriversTrait;

    /**
     * @var array
     */
    protected $paymentData;

    public function __construct($config)
    {
        $this->setConfig($config);
    }

    /**
     * Get name of payment service
     *
     * @return string
     */
    public function getName()
    {
        return PaymentProvider::PAYMENT_YANDEX;
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
        $currency = self::CURRENCY_RUR_ISO;
        if (is_numeric($currency)) {
            $cur = (new ISO4217())->getByNumeric($currency);
            $currency = $cur['alpha3'];
        }
        $amount = number_format($amount, 2, '.', '');
        $paymentType = $this->getPaymentMethod($paymentType);

        $user = \Auth::user();
        $contact = null;
        $email = $user->email ?? null;
        $phone = $user->phone ? (new PhoneNormalizer())->normalize($user->phone) : null;
        $contact = $email ?? $phone;

        $save_payment_method = $metadata['save_card'] ?? false;

        $params = [
            'amount'    => [
                'value'    => $amount,
                'currency' => $currency,
            ],
            'metadata'  => [
                'orderId' => $orderId
            ],
            'confirmation'   => [
                'type'       => 'redirect',
                'return_url' => $successReturnUrl,
            ],
            'payment_method_data' => [
                'type' => $paymentType,
            ],
            'save_payment_method' => $save_payment_method,
            'description'         => $description,
            'capture'             => true, //двух этапное списание (false - это включено)
        ];

        if ($contact) {
            $items[] = new YandexReceiptItem(
                'Пополнение баланса',
                1,
                $amount,
                YandexReceiptItem::TAX_NO_VAT,
                $currency,
                YandexReceiptItem::PAYMENT_SUBJECT_PAYMENT,
                YandexReceiptItem::PAYMENT_MODE_FULL_PAYMENT
            );
            $receipt = new YandexReceipt($contact, $items);
            $params['receipt'] = $receipt->toArray();
        }

        $params = array_merge($params, $extraParams);
        $paymentData = $this->getTransport()->createPayment($params);
        $this->setPaymentData($paymentData);

        return $paymentData;
    }

    public function fastPayment($orderId,
                                $amount,
                                $card_id,
                                $description,
                                $extraParams = [])
    {
        $currency = self::CURRENCY_RUR_ISO;
        if (is_numeric($currency)) {
            $cur = (new ISO4217())->getByNumeric($currency);
            $currency = $cur['alpha3'];
        }
        $amount = number_format($amount, 2, '.', '');
        $idempotencyKey = $metadata['idempotency_key'] ?? null;
        $params = [
            'amount'    => [
                'value'    => $amount,
                'currency' => $currency,
            ],
            'metadata'  => [
                'orderId' => $orderId
            ],
            'payment_method_id'   => $card_id,
            'description'         => $description,
            'capture'             => true, //двух этапное списание (false - это включено)
        ];

        $params = array_merge($params, $extraParams);
        return $this->getTransport()->createPayment($params, $idempotencyKey);
    }

    public function getPayLink($paymentData)
    {
        return $this->getPaymentUrl();
    }

    /**
     * @param $payment_method_id
     * @return mixed
     */
    public function getPayInfo($payment_method_id)
    {
        $paymentData = $this->getTransport()->getPayInfo($payment_method_id);
        $this->setPaymentData($paymentData);

        return $paymentData;
    }

    /**
     * Parse data
     *
     * @param array $data
     *
     * @return mixed
     */
    public function setPaymentData($data)
    {
        $data['DateTime'] = date('Y-m-d H:i:s');
        $this->paymentData = $data;
        return $this;
    }


    /**
     * @param $reponseCode
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function getNotificationResponse($reponseCode = null)
    {
        $status = 200;
        if ($reponseCode) {
            $status = $reponseCode;
        }
        return \response('ok', $status);
    }

    /**
     * Get response param by name
     *
     * @param string $name
     * @param string $default
     *
     * @return mixed|string
     */
    public function getPaymentParam($name, $default = '')
    {
        return Arr::get($this->paymentData ?? [], $name, $default);
    }
    /**
     * Get order ID
     *
     * @return string
     */
    public function getOrderId()
    {
        return $this->getPaymentParam('metadata.orderId');
    }
    /**
     * Get operation status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->getPaymentParam('status');
    }
    /**
     * Is payment succeed
     *
     * @return bool
     */
    public function isSuccess()
    {
        return $this->getPaymentParam('status') === 'succeeded';
    }
    /**
     * Get payment id
     *
     * @return string
     */
    public function getPaymentId()
    {
        return $this->getPaymentParam('id');
    }
    /**
     * Get payment id
     *
     * @return string
     */
    public function getPaymentMethodId()
    {
        return $this->getPaymentParam('payment_method.id');
    }
    /**
     * Get payment provider
     *
     * @return string
     */
    public function getPaymentMethodType()
    {
        return $this->getPaymentParam('payment_method.type');
    }
    /**
     * Get transaction amount
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->getPaymentParam('amount.value');
    }

    /**
     * Get transaction paid
     */
    public function getPaid()
    {
        return $this->getPaymentParam('paid');
    }

    public function getPaymentUrl()
    {
        return $this->getPaymentParam('confirmation.confirmation_url');
    }

    /**
     * Get PAn
     *
     * @return string
     */
    public function getPan()
    {
        $first = $this->getPaymentParam('payment_method.first6', null);
        if ($first) {
            $first = $this->substrCardNum($first).' ';
        } else {
            $first = '**** ';
        }

        return $first . '**** ****' . $this->getPaymentParam('payment_method.last4');
    }

    /**
     * Get param by name
     *
     * @param string $name
     *
     * @param string $default
     * @return mixed
     */
    public function getParam($name, $default = '')
    {
        return $this->getPaymentParam($name, $default);
    }

    /**
     * Get payment datetime
     *
     * @return string
     */
    public function getDateTime()
    {
        return $this->getPaymentParam('DateTime');
    }

}
