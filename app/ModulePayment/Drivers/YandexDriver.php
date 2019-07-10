<?php
namespace App\ModulePayment\Drivers;

use Alcohol\ISO4217;
use App\Models\TransactionStatus;
use App\Models\TransactionType;
use App\Models\User;
use App\ModulePayment\Interfaces\ModelPaymentInterface;
use App\ModulePayment\Interfaces\PaymentServiceInterface;
use App\ModulePayment\Interfaces\PaymentTransportInterface;
use App\ModulePayment\Models\YandexPayment;
use App\ModulePayment\PaymentProvider;
use App\Notifications\ServiceTextNotification;
use App\Services\PhoneNormalizer;
use BillingService;
use DB;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use MoneyAmount;

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
    public function regularPayment($amount,
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
        $amount = number_format($amount, 2, '.', '');
        $paymentType = $this->getPaymentMethod($paymentType);


        $user = \Auth::user();
        $contact = null;
        $email = $user->email ?? null;
        $phone = $user->phone ? (new PhoneNormalizer())->normalize($user->phone) : null;
        $contact = $email ?? $phone;

        $save_payment_method = $metadata['save_card'] ?? false;
        $idempotencyKey = $metadata['idempotency_key'] ?? null;
        $params = [
            'amount'    => [
                'value'    => $amount,
                'currency' => $currency,
            ],
            'metadata'  => $metadata,
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
        return $this->getTransport()->createPayment($params, $idempotencyKey);
    }

    public function fastPayment($amount,
                                $card_id,
                                $description = '',
                                $metadata = [],
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
            'metadata'            => $metadata,
            'payment_method_id'   => $card_id,
            'description'         => $description,
            'capture'             => true, //двух этапное списание (false - это включено)
        ];

        $params = array_merge($params, $extraParams);
        return $this->getTransport()->createPayment($params, $idempotencyKey);
    }

    /**
     * @param $amount
     * @param string $paymentType
     * @param string $description
     * @param $idempotencyKey
     * @param array $metadata
     * @return mixed
     * @throws \Exception
     */
    public function makePaymentTransaction($amount,
                                           $paymentType = self::PAYMENT_TYPE_CARD,
                                           $description = '',
                                           $idempotencyKey,
                                           $metadata = [])
    {
        // Старт транзакции!
        DB::beginTransaction();

        try {
            $paymentType = $this->getPaymentMethod($paymentType);
            /*
             * транзакция на пополнение баланса
             */
            $comment = !empty($description) ? $description : 'Пополнение баланса через "Yandex Kassa"';
            $transaction_deposit = BillingService::makeTransaction(
                \Auth::user(),
                (int) $amount,
                TransactionType::YANDEX_IN,
                $comment,
                $metadata
            );

            $yandexPayment = YandexPayment::create([
                'idempotency_key' => $idempotencyKey,
                'amount' => $amount,
                'payment_type' => YandexPayment::TYPE_PAYMENT,
                'payment_method_type' => $paymentType,
                'status' => ModelPaymentInterface::STATUS_PENDING,
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

    public function updatePaymentTransaction($payment, $paymentData)
    {
        if (!$payment || !$payment instanceof ModelPaymentInterface) {
            info('updatePaymentTransaction: $payment not instanceof ModelPaymentInterface');
            return null;
        }

        $this->setPaymentData($paymentData);
        $payment_id = $this->getPaymentId();
        $payment_method_meta = $this->getParam('payment_method', null);

        if ($payment_id && !empty($payment_id)) {
            $payment->payment_id = $payment_id;
            $payment->payment_method_meta = $payment_method_meta;

            $payment->save();
        } else {

            info('updatePaymentTransaction: Ошибка при обработке уведомлений yandex, идентификатор платежа пустой.');
        }

        return $payment;
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
            if ($payment->getStatus() !== $this->getStatus()) { // если статус изменился

                /** @var \App\Models\Transaction $transaction */
                $transaction = $payment->getTransaction();
                $payment_method_meta = $this->getParam('payment_method');

                switch ($this->getStatus()) { // статус из ответа
                    case YandexPayment::STATUS_CANCELED:
                        $payment->payment_method_meta = $payment_method_meta;
                        $payment->setStatus(YandexPayment::STATUS_CANCELED);
                        $payment->save();
                        BillingService::cancelTransactionOrRollback($transaction);

                        break;
                    case YandexPayment::STATUS_SUCCEEDED:
                        if ($payment->payment_method_type === $this->getPaymentMethod(self::PAYMENT_TYPE_CARD)) {// если оплата картой
                            $pan = $this->getPan();
                            $cardType = $this->getParam('payment_method.cardType', null);
                            if ($this->getParam('payment_method.saved', false)) {// если нужно сохранить карту
                                /** @var User $user */
                                $user = $payment->getUser();
                                (new \App\ModulePayment\Models\PaymentCard)->saveCard($user, [
                                    'card_id' => $this->getPaymentMethodId(),
                                    'year' => $this->getParam('payment_method.expiryYear', null),
                                    'month' => $this->getParam('payment_method.expiryMonth', null),
                                    'type' => $cardType,
                                    'first' => $this->getParam('payment_method.first6', null),
                                    'last' => $this->getParam('payment_method.last4', null),
                                    'pan' => $pan,
                                ]);
                            }

                            $payment->description = $cardType ? 'Пополнение картой: '. $cardType .' '.$pan : 'Пополнение картой: '.$pan;
                        }
                        $payment->payment_method_meta = $payment_method_meta;
                        $payment->setStatus(YandexPayment::STATUS_SUCCEEDED);
                        $payment->save();

                        if ($transaction->getStatusCode() === TransactionStatus::WAITING) {
                            // получаем сумму из платежа
                            $amount = $this->getAmount();
                            if ($payment->payment_method_type === $this->getPaymentMethod(self::PAYMENT_TYPE_CARD)) {// если оплата картой
                                $pan = $this->getPan();
                                $cardType = $this->getParam('payment_method.cardType', null);
                                $transaction->comment = $cardType ? 'Пополнение картой: '. $cardType .' '.$pan : 'Пополнение картой: '.$pan;
                            }
                            $transaction->amount = $amount; // ставим сумму из платеже для случая если пользователь оплатил частичную сумму
                            $transaction->setStatus(TransactionStatus::PENDING); // переключаем статус для исполнения
                            $transaction->save();
                            BillingService::runTransaction($transaction->refresh());

                            try {
                                // сообщение при успешном пополнении баланса
                                session()->flash('balance-message', 'replenished');
                                // Уведомление
                                /** @var User $receiver */
                                $receiver = $transaction->getUser();
                                $receiver->notify(new ServiceTextNotification($receiver, [
                                    'text' => 'Баланс пополнен: +' . MoneyAmount::toHumanize($amount),
                                    'created_at' => humanize_date(Carbon::now('Europe/Moscow'), 'd F, H:i'),
                                ]));
                            } catch (\Exception $e) {
                                info('processPayment: Ошибка при отправке уведомления.');
                            }
                        } else {

                            info('processPayment: Ошибка при обработке уведомлений yandex, транзакция находится не в надлежашем статусе');
                        }
                        break;
                }
            }
        } else {

            info('processPayment: Ошибка при обработке уведомлений yandex, идентификатор платежа пустой.');
        }

        return $payment;
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
            $this->setPaymentData($request ?? []);
            $payment_id = $this->getPaymentId();
            if ($payment_id && !empty($payment_id)) {
                $payment = YandexPayment::wherePaymentId($payment_id)->first();
                if (!$payment) {
                    throw new \Exception('Не найден платеж в системе.');
                }
                $this->processPayment($payment, $request);
            } else {

                throw new \Exception('Ошибка при обработке уведомлений yandex, идентификатор платежа пустой.');
            }
        } catch (\Exception $e) {

            info('yandex notify process: '.$e->getMessage());
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

    private function substrCardNum(string $val)
    {
        return substr($val, 0, 4);
    }
}