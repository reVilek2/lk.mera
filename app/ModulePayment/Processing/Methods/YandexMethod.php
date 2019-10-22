<?php
namespace App\ModulePayment\Processing\Methods;

use App\ModulePayment\Processing\Method;
use App\ModulePayment\Models\Payment;
use App\ModulePayment\Models\PaymentCard;
use App\ModulePayment\Interfaces\ModelPaymentInterface;
use App\Models\TransactionStatus;
use App\Models\TransactionType;
use App\Models\User;
use Illuminate\Support\Str;
use App\Notifications\ServiceTextNotification;
use DB;
use BillingService;
use MoneyAmount;

class YandexMethod extends Method
{
    public function makePaymentTransaction($amount,
                                           $paymentType,
                                           $description = '',
                                           $idempotencyKey,
                                           $metadata = [])
    {
        // Старт транзакции!
        DB::beginTransaction();

        try {
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

            $payment = Payment::create([
                'idempotency_key' => $idempotencyKey,
                'source' => 'yandex',
                'amount' => $amount,
                'payment_type' => Payment::TYPE_PAYMENT,
                'payment_method_type' => $paymentType,
                'status' => ModelPaymentInterface::STATUS_PENDING,
                'description' => $comment,
                'user_id' => \Auth::user()->id,
                'transaction_id' => $transaction_deposit->id,
            ]);

            // Если всё хорошо - фиксируем
            DB::commit();

            return $payment;

        } catch (\Exception $e) {
            // Откат
            DB::rollback();
            throw $e;
        }
    }

    public function updatePaymentTransaction($payment, $driver)
    {
        if (!$payment) {
            info('updatePaymentTransaction: $payment not instanceof ModelPaymentInterface');
            return null;
        }

        $payment_id = $driver->getPaymentId();
        $payment_method_meta = $driver->getParam('payment_method', null);

        if ($payment_id && !empty($payment_id)) {
            $payment->payment_id = $payment_id;
            $payment->payment_method_meta = $payment_method_meta;

            $payment->save();
        } else {

            info('updatePaymentTransaction: Ошибка при обработке уведомлений yandex, идентификатор платежа пустой.');
        }

        return $payment;
    }

    /**
     * @param ModelPaymentInterface $payment
     * @param array $paymentData
     * @return ModelPaymentInterface
     * @throws \Exception
     */
    public function processPayment(ModelPaymentInterface $payment)
    {
        $payment_id = $this->getPaymentId();
        if ($payment_id && !empty($payment_id)) {
            if ($payment->getStatus() !== $this->getStatus()) { // если статус изменился

                /** @var \App\Models\Transaction $transaction */
                $transaction = $payment->getTransaction();
                $payment_method_meta = $this->getParam('payment_method');

                switch ($this->getStatus()) { // статус из ответа
                    case Payment::STATUS_CANCELED:
                        $payment->payment_method_meta = $payment_method_meta;
                        $payment->setStatus(Payment::STATUS_CANCELED);
                        $payment->save();
                        BillingService::cancelTransactionOrRollback($transaction);

                        break;
                    case Payment::STATUS_SUCCEEDED:
                        if ($payment->payment_method_type === $this->getPaymentMethod(self::PAYMENT_TYPE_CARD)) {// если оплата картой
                            $pan = $this->getPan();
                            $cardType = $this->getParam('payment_method.cardType', null);
                            if ($this->getParam('payment_method.saved', false)) {// если нужно сохранить карту
                                /** @var User $user */
                                $user = $payment->getUser();
                                (new PaymentCard)->saveCard($user, [
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
                        $payment->setStatus(Payment::STATUS_SUCCEEDED);
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
                $payment = Payment::wherePaymentId($payment_id)->first();
                if (!$payment) {
                    return false;
                }
                $payment = $this->processPayment($payment, $request);
                if ($payment->status === ModelPaymentInterface::STATUS_SUCCEEDED) {
                    // проверяем нужно ли оплачивать документ
                    BillingService::checkAndPayDocumentIfRequiredByTransaction($payment->getTransaction());
                }
            } else {

                throw new \Exception('Ошибка при обработке уведомлений yandex, идентификатор платежа пустой.');
            }
        } catch (\Exception $e) {

            info('yandex notify process: '.$e->getMessage());
            return false;
        }

        return true;
    }

}
