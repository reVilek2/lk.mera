<?php
namespace App\Services;

use App\Exceptions\BillingException;
use App\Exceptions\DocumentException;
use App\Models\BillingAccount;
use App\Models\BillingAccountType;
use App\Models\BillingOperation;
use App\Models\BillingOperationType;
use App\Models\Document;
use App\Models\Transaction;
use App\Models\TransactionStatus;
use App\Models\TransactionType;
use App\Models\User;
use App\ModulePayment\Interfaces\ModelPaymentInterface;
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
     * @param array $metaData
     * @return mixed
     */
    public function makeTransaction(User $user, int $amount, string $type, string $comment = null, array $metaData = [])
    {
        $initiatorUser = Auth::user() ?? $user;

        $transactionType = $this->getTransactionTypeByCode($type);
        $transactionStatus = $this->getTransactionStatusByCode(TransactionStatus::WAITING);

        $accountTypes = $this->getAccountTypesByTransactionCode($type);
        $operationType = $this->getOperationTypeByTransactionCode($type);
        $receiver_acc = $this->getOrCreateAccountByCode($user, $accountTypes['receiver']);
        $sender_acc = $this->getOrCreateAccountByCode($user, $accountTypes['sender']);

        $user_balance = $user->balance;
        $meta_data = $metaData;
        $meta_data['balance'] = MoneyAmount::toHumanize($user_balance);
        $meta_data['balance_external'] = MoneyAmount::toExternal($user_balance);

        $transaction = Transaction::create([
            'initiator_user_id' => $initiatorUser->id,
            'user_id' => $user->id,
            'status_id' => $transactionStatus->id,
            'type_id' => $transactionType->id,
            'amount' => $amount,
            'comment' => $comment,
            'operation' => $operationType,
            'meta_data' => $meta_data,
            'receiver_acc_id' => $receiver_acc->id,
            'sender_acc_id' => $sender_acc->id,
        ]);

        return $transaction;
    }

    /**
     * @param \App\Models\Transaction $transaction
     * @return \App\Models\Transaction
     * @throws \Exception
     */
    public function runTransaction(Transaction $transaction)
    {
        try {
            if ($transaction->getStatusCode() === TransactionStatus::PENDING) { // исполнить можно только статус "pending"
                $receiverAccount = BillingAccount::whereId($transaction->receiver_acc_id)->first();
                $senderAccount = BillingAccount::whereId($transaction->sender_acc_id)->first();

                // делаем двойную проводку для истории перемешения средств
                $this->makeOperation($transaction, $receiverAccount, $senderAccount);
                // отнимаем от отправителя
                $this->subtractBalanceAmount($transaction->amount, $senderAccount);
                // добавляем получателю
                $this->appendBalanceAmount($transaction->amount, $receiverAccount);

                $transaction->setStatus(TransactionStatus::SUCCESS);

                $transaction->save();
            }
        }
        catch(\Exception $e) {
            // помечаем транзакцию как проваленную
            $transaction->setStatus(TransactionStatus::ERROR);
            $transaction->save();
            throw $e;
        }

        return $transaction;
    }

    /**
     * @param \App\Models\Transaction $transaction
     * @return \App\Models\Transaction
     * @throws \Exception
     */
    public function cancelTransactionOrRollback(Transaction $transaction)
    {
        // Старт транзакции!
        DB::beginTransaction();

        try {
            $transaction = $this->cancelTransaction($transaction);
            $transaction->save();
        }
        catch(\Exception $e) {
            // Откат
            DB::rollback();
            // помечаем транзакцию как проваленную
            $transaction->setStatus(TransactionStatus::ERROR);
            $transaction->save();
            throw $e;
        }

        // Если всё хорошо - фиксируем
        DB::commit();

        return $transaction;
    }

    /**
     * @param \App\Models\Transaction $transaction
     * @return \App\Models\Transaction
     */
    public function cancelTransaction(Transaction $transaction)
    {
        // перевести в статус "canceled" можно только из статуса "pending"
        if ($transaction->getStatusCode() === TransactionStatus::PENDING || $transaction->getStatusCode() === TransactionStatus::WAITING) {
            $transaction->setStatus(TransactionStatus::CANCEL);
        }

        return $transaction;
    }

    /**
     * @param \App\Models\Transaction $transaction
     * @param \App\Models\BillingAccount $receiverAccount
     * @param \App\Models\BillingAccount $senderAccount
     */
    private function makeOperation(Transaction $transaction, BillingAccount $receiverAccount, BillingAccount $senderAccount)
    {
        $outgoingOperationType = BillingOperationType::whereCode(BillingOperationType::OUTGOING)->first();
        $incomingOperationType = BillingOperationType::whereCode(BillingOperationType::INCOMING)->first();

        if (!$outgoingOperationType) {
            throw BillingException::unknownBillingOperationType(BillingOperationType::OUTGOING);
        }
        if (!$incomingOperationType) {
            throw BillingException::unknownBillingOperationType(BillingOperationType::INCOMING);
        }

        $amount = $transaction->amount;
        $amountExternal = MoneyAmount::toExternal($amount);
        $senderBalanceExternal = MoneyAmount::toExternal($senderAccount->balance);
        $receiverBalanceExternal = MoneyAmount::toExternal($receiverAccount->balance);

        // исходящий платеж
        BillingOperation::create([
            'account_id' => $senderAccount->id,
            'transaction_id' => $transaction->id,
            'type_id' => $outgoingOperationType->id,
            'amount' => $amount,
            'balance' => MoneyAmount::toReadable($senderBalanceExternal - $amountExternal),
        ]);
        // входящий платеж
        BillingOperation::create([
            'account_id' => $receiverAccount->id,
            'transaction_id' => $transaction->id,
            'type_id' => $incomingOperationType->id,
            'amount' => $amount,
            'balance' => MoneyAmount::toReadable($receiverBalanceExternal + $amountExternal),
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
     * Получаем счет по типу счета или создаем новый счет с указанным типом
     *
     * @param \App\Models\User $user
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
     * Проверка что сумма достаточная на
     *
     * @param \App\Models\User $user
     * @param int $amount
     * @return bool
     */
    public function checkAmountOnBalance(User $user, int $amount)
    {
        $user = $user->fresh();
        $accountBalance = $user->accountBalance()->first();
        $balance = $accountBalance ? $accountBalance->balance : 0;

        return $balance >= $amount;
    }

    /**
     * Получение недостающей суммы
     *
     * @param \App\Models\User $user
     * @param int $amount
     * @return bool
     */
    public function calculateMissingAmount(User $user, int $amount)
    {
        $user = $user->fresh();
        $accountBalance = $user->accountBalance()->first();
        $balance = $accountBalance ? $accountBalance->balance : 0;
        $externalBalance = MoneyAmount::toExternal($balance);
        $externalAmount = MoneyAmount::toExternal($amount);
        $missingAmount = 0;
        if ($externalBalance < $externalAmount) {
            $missingAmount = MoneyAmount::toReadable($externalAmount - $externalBalance);
        }
        return $missingAmount;
    }

    /**
     * @param string $code
     * @return mixed
     */
    public function getTransactionTypeByCode(string $code)
    {
        $transactionType = TransactionType::whereCode($code)->first();
        if (!$transactionType) {
            throw BillingException::unknownTransactionType($code);
        }

        return $transactionType;
    }

    /**
     * @param string $code
     * @return mixed
     */
    public function getTransactionStatusByCode(string $code)
    {
        $transactionStatus = TransactionStatus::whereCode($code)->first();
        if (!$transactionStatus) {
            throw BillingException::unknownTransactionStatus($code);
        }

        return $transactionStatus;
    }

    /**
     * @param string $code
     * @return mixed
     */
    public function getAccountTypeByCode(string $code)
    {
        $billingAccountType = BillingAccountType::whereCode($code)->first();
        if (!$billingAccountType) {
            throw BillingException::unknownBillingAccountType($code);
        }

        return $billingAccountType;
    }

    /**
     * Ручная оплата документа
     *
     * @param \App\Models\Document $document
     * @param bool $is_need_status_signed
     * @return \App\Models\Document|null
     * @throws \Exception
     */
    public function manualPaymentDocument(Document $document, $is_need_status_signed = false)
    {
        return $this->payDocumentFromUserBalance($document, $is_need_status_signed, true);
    }

    /**
     * @param \App\Models\Document $document
     * @param bool $is_need_status_signed
     * @param bool $is_need_deposit
     * @return \App\Models\Document|null
     * @throws \Exception
     */
    public function payDocumentFromUserBalance(Document $document, $is_need_status_signed = false, $is_need_deposit = false)
    {
        if ($document->getTransaction()) {
            throw DocumentException::issetDocumentTransaction();
        }

        $client = $document->client;
        $signed = $document->signed;
        $currUser = Auth::user() ?? $client;

        if ($is_need_status_signed) {
            $document->signed = 1;
            $signed = 1;
            $document->save();
            // log в историю
            $document->history()->create([
                'user_id' => $currUser->id,
                'signed' => $signed,
                'paid' => $document->paid,
            ]);
        }

        try {
            if ($is_need_deposit) {
                /*
                 * транзакция на пополнение баланса
                 */
                $comment = 'Ручное пополнение баланса в счет документа "' . $document->name . '" от ' . humanize_date($document->created_at, 'd.m.Y');
                $transaction_deposit = $this->makeTransaction(
                    $client,
                    (int)$document->amount,
                    TransactionType::MANUAL_IN,
                    $comment
                );
                $transaction_deposit->setStatus(TransactionStatus::PENDING);// переключаем статус для исполнения
                // исполнение транзакции
                $this->runTransaction($transaction_deposit);
            }

            if (!$this->checkAmountOnBalance($client, (int) $document->amount)) {
                throw BillingException::notEnoughFunds();
            }

            /*
             * транзакция на оплату документа
             */
            $comment = 'Оплата документа "' . $document->name . '" от ' . humanize_date($document->created_at, 'd.m.Y');
            $transaction = $this->makeTransaction(
                $client,
                (int) $document->amount,
                TransactionType::SERVICE_IN,
                $comment
            );
            $transaction->setStatus(TransactionStatus::PENDING);// переключаем статус для исполнения
            // исполнение транзакции
            $this->runTransaction($transaction);

            $document->transaction()->associate($transaction);
            $document->save();

            // меняем статус оплаты документу
            $paid = 1;
            $signed = 1;
            $document->paid = $paid;
            $document->signed = $signed;
            $document->save();
            // log в историю
            $document->history()->create([
                'user_id' => $currUser->id,
                'signed' => $signed,
                'paid' => $paid,
            ]);
        }
        catch(\Exception $e) {

            throw $e;
        }

        return $document->fresh();
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
            TransactionType::YANDEX_IN => [
                'sender' => BillingAccountType::KASSA_YANDEX,
                'receiver' => BillingAccountType::BALANCE,
            ],
            TransactionType::YANDEX_OUT => [
                'sender' => BillingAccountType::BALANCE,
                'receiver' => BillingAccountType::KASSA_YANDEX,
            ],
            TransactionType::TINKOFF_IN => [
                'sender' => BillingAccountType::TINKOFF,
                'receiver' => BillingAccountType::BALANCE,
            ],
            TransactionType::TINKOFF_OUT => [
                'sender' => BillingAccountType::BALANCE,
                'receiver' => BillingAccountType::TINKOFF,
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

    /**
     * Получаем тип операции (входящия/исходящия)
     *
     * @param string $transactionCode
     * @return mixed
     */
    public function getOperationTypeByTransactionCode(string $transactionCode)
    {
        $operationTypeMapping = $this->getOperationTypeMapping();
        if (!array_key_exists($transactionCode, $operationTypeMapping)) {

            throw BillingException::unknownTransactionOperationTypeInMapping($transactionCode);
        }
        return $operationTypeMapping[$transactionCode];
    }

    /**
     * Массив сопоставления типа транзакции с типом счетов
     *
     * @return array
     */
    public function getOperationTypeMapping()
    {
        return [
            TransactionType::MANUAL_IN => Transaction::INCOMING,
            TransactionType::MANUAL_OUT => Transaction::OUTGOING,
            TransactionType::YANDEX_IN => Transaction::INCOMING,
            TransactionType::YANDEX_OUT => Transaction::OUTGOING,
            TransactionType::TINKOFF_IN => Transaction::INCOMING,
            TransactionType::TINKOFF_OUT => Transaction::OUTGOING,
            TransactionType::SERVICE_IN => Transaction::OUTGOING,
            TransactionType::SERVICE_OUT => Transaction::INCOMING,
        ];
    }

    /**
     * @param \App\Models\Transaction $transaction
     * @return \App\Models\Document|null
     */
    public function checkAndPayDocumentIfRequiredByTransaction(Transaction $transaction)
    {
        if ($transaction && $transaction->getStatusCode() === TransactionStatus::SUCCESS) {
            /** @var array $metaData */
            $metaData = $transaction->meta_data;
            if (array_key_exists('document', $metaData)) {
                $document = Document::whereId((int) $metaData['document'])->first();
                if ($document && !$document->getTransaction()) { // если еще нет транзакции
                    if ($this->checkAmountOnBalance($document->client, (int) $document->amount)) {
                        try {
                            return $this->payDocumentFromUserBalance($document);
                        } catch (\Exception $e) {
                            info('checkPayment: '.$e->getMessage());
                        }
                    }
                }
            }
        }

        return null;
    }
}
