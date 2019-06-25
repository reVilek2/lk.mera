<?php
namespace App\ModulePayment\Drivers;

use Alcohol\ISO4217;
use App\Models\TransactionStatus;
use App\Models\TransactionType;
use App\ModulePayment\Interfaces\ModelPaymentInterface;
use App\ModulePayment\Interfaces\PaymentServiceInterface;
use App\ModulePayment\Interfaces\PaymentTransportInterface;
use App\ModulePayment\Models\YandexPayment;
use BillingService;
use DB;
use Illuminate\Support\Arr;

/**
 * Payment service. Pay, Check, etc
 */
class YandexDriver implements PaymentServiceInterface
{
    const MAX_UNIQUE_RECURSION = 20;

    /**
     * Module config
     *
     * @var array
     */
    private $config;

    /**
     * @var array
     */
    protected $paymentData;
    /**
     * @var PaymentTransportInterface
     */
    private $transport;

    public function __construct($config)
    {
        $this->setConfig($config);
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
    public function getPaymentData($amount,
                                   $paymentType = self::PAYMENT_TYPE_CARD,
                                   $description = '',
                                   $successReturnUrl = '',
                                   $metadata = [],
                                   $extraParams = [])
    {
        $currency = self::CURRENCY_RUR_ISO;
        if (is_numeric($currency)) {
            $cur = (new ISO4217())->getByNumeric($currency);
            $currency = $cur['alpha3'];
        }
        $paymentType = $this->getPaymentMethod($paymentType);
        $params = [
            'amount'              => [
                'value'    => $amount,
                'currency' => $currency,
            ],
            'metadata'            => $metadata,
            'confirmation'        => [
                'type'       => 'redirect',
                'return_url' => $successReturnUrl,
            ],
            'payment_method_data' => [
                'type' => $paymentType,
            ],
            'description'         => $description,
            'capture'             => true, //двух этапное списание (false - это включено)
        ];
        if ($paymentType === self::PAYMENT_TYPE_QIWI && isset($extraParams['phone'])) {
            $params['payment_method_data']['phone'] = $extraParams['phone'];
        }

        $params = array_merge($params, $extraParams);
        return $this->getTransport()->getPaymentData($params, $idempotencyKey = $params['metadata']['idempotency_key'] ?? null);
    }

    /**
     * @param $paymentData
     * @return mixed
     * @throws \Exception
     */
    public function makePayment($paymentData)
    {
        // Старт транзакции!
        DB::beginTransaction();

        $this->setPaymentData($paymentData);
        try {
            $amount = $this->getAmount();
            $paid = $this->getPaid();
            if (empty($paid)) {
                $paid = false;
            }
            $payment_id = $this->getPaymentId();
            $payment_method_type = $this->getPaymentMethodType();
            $payment_meta = $this->getParam('payment_method');
            $status = $this->getStatus();
            $description = $this->getParam('description');
            $idempotency_key = $this->getParam('metadata.idempotency_key');
            if(!$idempotency_key || empty($idempotency_key)) {
                $idempotency_key = $payment_id;
            }

            /*
             * транзакция на пополнение баланса
             */
            $comment = !empty($description) ? $description : 'Пополнение баланса через "Yandex Kassa"';
            $transaction_deposit = BillingService::makeTransaction(
                \Auth::user(),
                (int) $amount,
                TransactionType::YANDEX_IN,
                $comment
            );

            $yandexPayment = YandexPayment::create([
                'idempotency_key' => $idempotency_key,
                'amount' => $amount,
                'paid' => $paid,
                'payment_id' => $payment_id,
                'payment_type' => YandexPayment::TYPE_PAYMENT,
                'payment_method_type' => $payment_method_type,
                'payment_meta' => $payment_meta,
                'status' => $status,
                'description' => $comment,
                'user_id' => \Auth::user()->id,
                'transaction_id' => $transaction_deposit->id,
            ]);

            // Если всё хорошо - фиксируем
            DB::commit();

            return $yandexPayment;

        } catch (\Exception $e) {
            // Откат
            DB::rollback();
            throw $e;
        }
    }

    public function getPaymentByUniqueKey($key)
    {
        return YandexPayment::whereIdempotencyKey($key)->first();
    }

    public function getPayLink($paymentData)
    {
        $this->setPaymentData($paymentData);
        return $this->getPaymentUrl();
    }

    /**
     * @param $payment_method_id
     * @return mixed
     */
    public function getPayInfo($payment_method_id)
    {
        return $this->getTransport()->getPayInfo($payment_method_id);
    }

    /**
     * @param $payment
     * @return null|ModelPaymentInterface
     * @throws \Exception
     */
    public function checkPayment($payment)
    {
        if (!$payment || !$payment instanceof ModelPaymentInterface) {
            return null;
        }

        $paymentData = $this->getTransport()->getPayInfo($payment->payment_id);
        $payment = $this->processPayment($payment, $paymentData);

        return $payment;
    }

    /**
     * @param ModelPaymentInterface $payment
     * @param array $paymentData
     * @return ModelPaymentInterface
     * @throws \Exception
     */
    public function processPayment(ModelPaymentInterface $payment, $paymentData = [])
    {
        $this->setPaymentData($paymentData);
        $payment_id = $this->getPaymentId();
        if ($payment_id && !empty($payment_id)) {
            /** @var \App\Models\Transaction $transaction */
            $transaction = $payment->getTransaction();

            if ($payment->getStatus() !== $this->getStatus()) { // если статус изменился
                switch ($this->getStatus()) { // статус из ответа
                    case YandexPayment::STATUS_CANCELED:
                        $payment->setStatus(YandexPayment::STATUS_CANCELED);
                        $payment->save();
                        BillingService::cancelTransactionOrRollback($transaction);

                        break;
                    case YandexPayment::STATUS_SUCCEEDED:
                        $payment->setStatus(YandexPayment::STATUS_SUCCEEDED);
                        $payment->save();
                        if ($transaction->getStatusCode() === TransactionStatus::WAITING) {
                            // получаем сумму из платежа
                            $amount = $this->getAmount();
                            $transaction->amount = $amount; // ставим сумму из платеже для случая если пользователь оплатил частичную сумму
                            $transaction->setStatus(TransactionStatus::PENDING); // переключаем статус для исполнения
                            $transaction->save();
                            BillingService::runTransactionOrRollback($transaction->refresh());

                            // сообщение при успешном пополнении баланса
                            session()->flash('balance-message', 'replenished');
                        } else {

                            info('Ошибка при обработке уведомлений yandex, транзакция находится не в надлежашем статусе');
                        }
                        break;
                }
            }
        } else {

            info('Ошибка при обработке уведомлений yandex, идентификатор платежа пустой.');
        }

        return $payment;
    }

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
     * Обработка уведомлений
     * @param $request
     * @return bool
     * @throws \Exception
     */
    public function processNotificationRequest($request)
    {
        try {
            $this->setPaymentData($request['object'] ?? []);
            $payment_id = $this->getPaymentId();
            if (!$payment_id && !empty($payment_id)) {
                $payment = YandexPayment::wherePaymentId($payment_id)->first();
                $this->processPayment($payment, $request['object']);
            } else {

                throw new \Exception('Ошибка при обработке уведомлений yandex, идентификатор платежа пустой.');
            }
        } catch (\Exception $e) {

            info('NotificationRequest: '.$e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * @param $errorCode
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function getNotificationResponse($errorCode = null)
    {
        $status = 200;
        if ($errorCode) {
            $status = $errorCode;
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
        return $this->getPaymentParam('payment_method.card.first6') . '******' . $this->getPaymentParam('payment_method.card.last4');
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
        return $this->getPaymentParam($name);
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
     * Set transport
     *
     * @param PaymentTransportInterface $transport
     *
     * @return $this
     */
    public function setTransport(PaymentTransportInterface $transport)
    {
        $this->transport = $transport;
        return $this;
    }

    /**
     * Get name of payment service
     *
     * @return string
     */
    public function getName()
    {
        return 'yandex';
    }

    /**
     * Get payment type for Yandex by constant value
     *
     * @param string $type
     *
     * @return string
     */
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

    /**
     * @param int $recursion_level
     * @return mixed
     * @throws \Exception
     */
    public function uniqid($recursion_level = 0)
    {
        if ($recursion_level >= self::MAX_UNIQUE_RECURSION) {

            throw new \Exception('Превышен допестимы уровень рекурсии при создании уникального ключа.');
        }

        $uniqid = $this->getTransport()->uniqid();
        if (YandexPayment::whereIdempotencyKey($uniqid)->first()) {
            $this->uniqid(++$recursion_level);
        }
        return $uniqid;
    }
}