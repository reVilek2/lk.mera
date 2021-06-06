<?php


namespace App\ModulePayment\Processing\Methods;


use App\Models\TransactionStatus;
use App\Models\TransactionType;
use App\Models\User;
use App\ModulePayment\Interfaces\ModelPaymentInterface;
use App\ModulePayment\Interfaces\PaymentServiceInterface;
use App\ModulePayment\Models\Payment;
use App\ModulePayment\Processing\Method;
use App\Notifications\ServiceTextNotification;
use Auth;
use BillingService;
use DB;
use Exception;
use Illuminate\Support\Carbon;
use MoneyAmount;

class PaykeeperMethod extends Method
{
    const STATUS_CREATED = 'created';
    const STATUS_SENT = 'sent';
    const STATUS_PAID = 'paid';
    const STATUS_EXPIRED = 'expired';

    /**
     * @param $amount
     * @param $paymentType
     * @param $description
     * @param $idempotencyKey
     * @param  array  $metadata
     * @return Payment
     * @throws Exception
     */
    public function makePaymentTransaction(
        $amount,
        $paymentType,
        $description,
        $idempotencyKey,
        $metadata = []
    ) {
        // Старт транзакции!
        DB::beginTransaction();

        try {
            /*
             * транзакция на пополнение баланса
             */
            $comment = !empty($description) ? $description : 'Пополнение баланса через "Paykeeper"';
            $transaction_deposit = BillingService::makeTransaction(
                Auth::user(),
                (int) $amount,
                TransactionType::PAYKEEPER_IN,
                $comment,
                $metadata
            );

            $payment = Payment::create([
                'idempotency_key'     => $idempotencyKey,
                'source'              => 'paykeeper',
                'amount'              => $amount,
                'payment_type'        => Payment::TYPE_PAYMENT,
                'payment_method_type' => $paymentType,
                'status'              => ModelPaymentInterface::STATUS_PENDING,
                'description'         => $comment,
                'user_id'             => Auth::user()->id,
                'transaction_id'      => $transaction_deposit->id,
            ]);

            // Если всё хорошо - фиксируем
            DB::commit();

            return $payment;

        } catch (Exception $e) {
            // Откат
            DB::rollback();
            throw $e;
        }
    }

    public function updatePaymentTransaction(Payment $payment, PaymentServiceInterface $driver)
    {
        if (!$payment) {
            info('updatePaymentTransaction: $payment not instanceof ModelPaymentInterface');
            return null;
        }

        $payment_id = $driver->getPaymentId();

        if ($payment_id && !empty($payment_id)) {
            $payment->payment_id = $payment_id;

            $payment->save();
        } else {

            info('updatePaymentTransaction: Ошибка при обработке уведомлений paykeeper, идентификатор платежа пустой.');
        }

        return $payment;
    }

    /**
     * @param  Payment  $payment
     * @param  PaymentServiceInterface  $driver
     * @return Payment
     * @throws Exception
     */
    public function processPayment($payment, $driver)
    {
        $payment_id = $driver->getPaymentId();

        if (!empty($payment_id) && $payment_id) {
            $transaction = $payment->getTransaction();
            $backup = $driver->getResponse();
            $api_data = $driver->getPayInfo($payment_id);
            $driver->setResponse($backup);
            $api_status = $this->nomalizeStatus($api_data['status']);
            if ($payment->getStatus() !== $api_status) { // если статус изменился

                switch ($api_status) { // статус из ответа
                    case Payment::STATUS_CANCELED:
                        $payment->setStatus(Payment::STATUS_CANCELED);
                        $payment->save();
                        BillingService::cancelTransactionOrRollback($transaction);

                        break;
                    case Payment::STATUS_SUCCEEDED:
                        $payment->setStatus(Payment::STATUS_SUCCEEDED);
                        $payment->save();

                        if ($transaction->getStatusCode() === TransactionStatus::WAITING) {
                            // получаем сумму из платежа
                            $amount = $driver->getAmount();

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
                                    'text'       => 'Баланс пополнен: +'.MoneyAmount::toHumanize($amount),
                                    'created_at' => humanize_date(Carbon::now('Europe/Moscow'), 'd F, H:i'),
                                ]));
                            } catch (\Exception $e) {
                                info('processPayment: Ошибка при отправке уведомления.');
                            }
                        } else {

                            info('processPayment: Ошибка при обработке уведомлений paykeeper, транзакция находится не в надлежашем статусе');
                        }
                        break;
                }
            }
        } else {
            info('processPayment: Ошибка при обработке уведомлений paykeeper, идентификатор платежа пустой.');
        }

        return $payment;
    }

    public function processNotificationRequest($notification, $driver)
    {
        try {
            $driver->setResponse($notification);
            $order_id = $notification['orderid'];

            if ($order_id && !empty($order_id)) {
                $payment = Payment::whereIdempotencyKey($order_id)->first();
                $driver->payment_id = $payment->payment_id;
                if (!$payment) {
                    return false;
                }

                $payment = $this->processPayment($payment, $driver);
                if ($payment->status === ModelPaymentInterface::STATUS_SUCCEEDED) {
                    // проверяем нужно ли оплачивать документ
                    BillingService::checkAndPayDocumentIfRequiredByTransaction($payment->getTransaction());
                }
            } else {

                throw new Exception('Ошибка при обработке уведомлений paykeeper, идентификатор платежа пустой.');
            }
        } catch (Exception $e) {
            info('paykeeper notify process: '.$e->getMessage(),$e->getTrace());

            return false;
        }

        return true;
    }

    public function nomalizeStatus($paykeeper_status)
    {
        $normalized_status = ModelPaymentInterface::STATUS_PENDING;

        switch ($paykeeper_status) {
            case self::STATUS_CREATED:
            case self::STATUS_SENT:
                $normalized_status = ModelPaymentInterface::STATUS_PENDING;
                break;
            case self::STATUS_EXPIRED:
                $normalized_status = ModelPaymentInterface::STATUS_CANCELED;
                break;
            case self::STATUS_PAID:
                $normalized_status = ModelPaymentInterface::STATUS_SUCCEEDED;
                break;
        }

        return $normalized_status;
    }
}