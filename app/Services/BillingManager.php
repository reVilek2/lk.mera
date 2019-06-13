<?php
namespace App\Services;

use App\Exceptions\BillingException;
use App\Models\BillingAccount;
use App\Models\BillingAccountType;
use App\Models\BillingOperation;
use App\Models\BillingOperationType;
use App\Models\Transaction;
use App\Models\TransactionStatus;
use App\Models\TransactionType;
use App\Models\User;
use Auth;
use DB;
use MoneyAmount;

class BillingManager
{
    /**
     * Создание Транзакции
     *
     * @param \App\Models\User $user
     * @param int $amount
     * @param string $type
     * @param string $comment
     * @return mixed
     */
    public function makeTransaction(User $user, int $amount, string $type, string $comment = null)
    {
        $transactionType = $this->getTransactionTypeByCode($type);
        $transactionStatus = $this->getTransactionStatusByCode(TransactionStatus::PENDING);
        $accountTypes = $this->getAccountTypesByTransactionCode($type);

        $receiver_acc = $this->getOrCreateAccountByCode($user, $accountTypes['receiver']);
        $sender_acc = $this->getOrCreateAccountByCode($user, $accountTypes['sender']);

        $transaction = Transaction::create([
            'status_id' => $transactionStatus->id,
            'type_id' => $transactionType->id,
            'user_id' => Auth::user()->id,
            'amount' => $amount,
            'comment' => $comment,
            'receiver_acc_id' => $receiver_acc->id,
            'sender_acc_id' => $sender_acc->id,
        ]);

        return $transaction;
    }

    /**
     * @param Transaction $transaction
     * @return Transaction
     * @throws \Exception
     */
    public function runTransaction(Transaction $transaction)
    {
        // Старт транзакции!
        DB::beginTransaction();

        try {

            $receiverAccount = BillingAccount::whereId($transaction->receiver_acc_id)->first();
            $senderAccount = BillingAccount::whereId($transaction->sender_acc_id)->first();

            // делаем двойную проводку для истории перемешения средств
            $this->makeOperation($transaction, $receiverAccount, $senderAccount);
            // отнимаем от отправителя
            $this->subtractBalanceAmount($transaction->amount, $senderAccount);
            // добавляем получателю
            $this->appendBalanceAmount($transaction->amount, $receiverAccount);

            $transaction->setStatus(TransactionStatus::SUCCESS);
        }
        catch(\Exception $e) {
            // Откат
            DB::rollback();
            // помечаем транзакцию как проваленную
            $transaction->setStatus(TransactionStatus::ERROR);

            throw $e;
        }

        // Если всё хорошо - фиксируем
        DB::commit();

        return $transaction;
    }

    private function makeOperation(Transaction $transaction, BillingAccount $receiverAccount, BillingAccount $senderAccount)
    {
        $outgoingOperationType = BillingOperationType::whereCode(BillingOperationType::OUTGOING)->firstOrFail();
        $incomingOperationType = BillingOperationType::whereCode(BillingOperationType::INCOMING)->firstOrFail();
        $amount = $transaction->amount;

        // исходящий платеж
        BillingOperation::create([
            'account_id' => $senderAccount->id,
            'transaction_id' => $transaction->id,
            'type_id' => $outgoingOperationType->id,
            'amount' => $amount,
        ]);
        // входящий платеж
        BillingOperation::create([
            'account_id' => $receiverAccount->id,
            'transaction_id' => $transaction->id,
            'type_id' => $incomingOperationType->id,
            'amount' => $amount,
        ]);
    }

    private function appendBalanceAmount(int $amount, BillingAccount $account)
    {
        $balance = MoneyAmount::toExternal($account->balance);
        $amount = MoneyAmount::toExternal($amount);

        $account->balance = MoneyAmount::toReadable($balance + $amount);
        $account->save();

        return $account;
    }
    private function subtractBalanceAmount(int $amount, BillingAccount $account)
    {
        $balance = MoneyAmount::toExternal($account->balance);
        $amount = MoneyAmount::toExternal($amount);

        $account->balance = MoneyAmount::toReadable($balance - $amount);
        $account->save();

        return $account;
    }

    /**
     * Получаем типы счетов
     *
     * @param string $transactionCode
     * @return mixed
     */
    public function getAccountTypesByTransactionCode(string $transactionCode)
    {
        $accountTypeMapping = $this->getAccountTypeMapping();
        if (!array_key_exists($transactionCode, $accountTypeMapping)) {

            throw BillingException::unknownTransactionTypeInMapping($transactionCode);
        }
        return $accountTypeMapping[$transactionCode];
    }

    /**
     * Получаем счет по типу счета или создаем новый счет с указанным типом
     *
     * @param User $user
     * @param string $code
     * @return mixed
     */
    public function getOrCreateAccountByCode(User $user, string $code)
    {
        $accountType = $this->getAccountTypeByCode($code);
        $account = BillingAccount::firstOrCreate([
            'user_id' => $user->id,
            'acc_type_id' => $accountType->id
        ]);

        return $account;
    }

    /**
     * @param string $code
     * @return mixed
     */
    public function getTransactionTypeByCode(string $code)
    {
        return TransactionType::whereCode($code)->firstOrFail();
    }

    /**
     * @param string $code
     * @return mixed
     */
    public function getTransactionStatusByCode(string $code)
    {
        return TransactionStatus::whereCode($code)->firstOrFail();
    }

    /**
     * @param string $code
     * @return mixed
     */
    public function getAccountTypeByCode(string $code)
    {
        return BillingAccountType::whereCode($code)->firstOrFail();
    }

    /**
     * Массив сопоставления типа транзакции с типом счетов
     *
     * @return array
     */
    public function getAccountTypeMapping()
    {
        return [
            TransactionType::MANUAL_IN => [
                'sender' => BillingAccountType::VIRTUAL,
                'receiver' => BillingAccountType::BALANCE,
            ],
            TransactionType::MANUAL_OUT => [
                'sender' => BillingAccountType::BALANCE,
                'receiver' => BillingAccountType::VIRTUAL,
            ],
            TransactionType::CARD_IN => [
                'sender' => BillingAccountType::VIRTUAL,
                'receiver' => BillingAccountType::BALANCE,
            ],
            TransactionType::CARD_OUT => [
                'sender' => BillingAccountType::BALANCE,
                'receiver' => BillingAccountType::VIRTUAL,
            ],
            TransactionType::SERVICE_IN => [
                'sender' => BillingAccountType::BALANCE,
                'receiver' => BillingAccountType::SERVICE,
            ],
            TransactionType::SERVICE_OUT => [
                'sender' => BillingAccountType::SERVICE,
                'receiver' => BillingAccountType::BALANCE,
            ],
        ];
    }
}