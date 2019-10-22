<?php
namespace App\ModulePayment\Processing\Methods;

use App\ModulePayment\Processing\Method;
use App\ModulePayment\Models\Payment;
use App\ModulePayment\Interfaces\ModelPaymentInterface;
use App\Models\TransactionStatus;
use App\Models\TransactionType;
use App\Models\User;
use Illuminate\Support\Carbon;
use App\Notifications\ServiceTextNotification;
use DB;
use BillingService;
use MoneyAmount;

class TinkoffMethod extends Method
{
    const TINKOFF_STATUS_NEW = 'NEW';
    const TINKOFF_STATUS_CANCELED = 'CANCELED';
    const TINKOFF_STATUS_REJECTED = 'REJECTED';
    const TINKOFF_STATUS_CONFIRMED = 'CONFIRMED';

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
            $comment = !empty($description) ? $description : 'Пополнение баланса через "Tinkoff"';
            $transaction_deposit = BillingService::makeTransaction(
                \Auth::user(),
                (int) $amount,
                TransactionType::TINKOFF_IN,
                $comment,
                $metadata
            );

            $payment = Payment::create([
                'idempotency_key' => $idempotencyKey,
                'source' => 'tinkoff',
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

        if ($payment_id && !empty($payment_id)) {
            $payment->payment_id = $payment_id;

            $payment->save();
        } else {

            info('updatePaymentTransaction: Ошибка при обработке уведомлений tinkoff, идентификатор платежа пустой.');
        }

        return $payment;
    }

    /**
     * @param $payment
     * @param array $paymentData
     * @return ModelPaymentInterface
     * @throws \Exception
     */
    public function processPayment($payment, $driver)
    {
        $payment_id = $driver->getPaymentId();
        info($payment_id);

        if (!empty($payment_id) && $payment_id) {
            $transaction = $payment->getTransaction();

            $api_status = $this->nomalizeStatus($driver->getStatus());

            info($api_status);
            info($driver->getParam('RebillId'));

            /*Проверить на наличие сохраненной карты*/
            if(Payment::STATUS_SUCCEEDED == $api_status && $driver->getParam('RebillId')){
                $pan = $driver->getParam('Pan');
                $exp_date = $driver->getParam('ExpDate');
                preg_match('/^(\d\d)(\d\d)$/', $exp_date, $matches);
                $user = $payment->getUser();
                (new \App\ModulePayment\Models\PaymentCard)->saveCard($user, [
                    'source' => 'tinkoff',
                    'card_id' => $driver->getParam('RebillId'),
                    'year' => isset($matches[2]) ? '20'.$matches[2] : null,
                    'month' => isset($matches[1]) ? $matches[1] : null,
                    'pan' => $pan,
                ]);

                $payment->description = 'Пополнение картой: '.$pan;
                $transaction->save();
            }

            if ( $payment->getStatus() !== $api_status ) { // если статус изменился
                /** @var \App\Models\Transaction $transaction */

                switch ( $api_status ) { // статус из ответа
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
                                    'text' => 'Баланс пополнен: +' . MoneyAmount::toHumanize($amount),
                                    'created_at' => humanize_date(Carbon::now('Europe/Moscow'), 'd F, H:i'),
                                ]));
                            } catch (\Exception $e) {
                                info('processPayment: Ошибка при отправке уведомления.');
                            }
                        } else {

                            info('processPayment: Ошибка при обработке уведомлений tinkoff, транзакция находится не в надлежашем статусе');
                        }
                        break;
                }
            }
        } else {
            info('processPayment: Ошибка при обработке уведомлений tinkoff, идентификатор платежа пустой.');
        }

        return $payment;
    }

    public function processNotificationRequest($notification, $driver)
    {
        try {
            $driver->setResponse($notification);

            info($driver->getStatus());
            if($driver->getStatus() != 'CONFIRMED'){
                return true;
            }

            $payment_id = $driver->getPaymentId();
            if ($payment_id && !empty($payment_id)) {
                $payment = Payment::wherePaymentId($payment_id)->first();
                if (!$payment) {
                    return false;
                }

                $payment = $this->processPayment($payment, $driver);
                if ($payment->status === ModelPaymentInterface::STATUS_SUCCEEDED) {
                    // проверяем нужно ли оплачивать документ
                    BillingService::checkAndPayDocumentIfRequiredByTransaction($payment->getTransaction());
                }
            } else {

                throw new \Exception('Ошибка при обработке уведомлений tinkoff, идентификатор платежа пустой.');
            }
        } catch (\Exception $e) {

            info('tinkoff notify process: '.$e->getMessage());
            return false;
        }

        return true;
    }

    public function nomalizeStatus($tinkoff_status){

        $nomalized_status = ModelPaymentInterface::STATUS_PENDING;

        switch($tinkoff_status){
            case self::TINKOFF_STATUS_CANCELED:
            case self::TINKOFF_STATUS_REJECTED:
                $nomalized_status = ModelPaymentInterface::STATUS_CANCELED;
                break;
            case self::TINKOFF_STATUS_CONFIRMED:
                $nomalized_status = ModelPaymentInterface::STATUS_SUCCEEDED;
                break;
        }

        return $nomalized_status;
    }
}
