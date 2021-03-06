<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Transaction;
use App\Models\TransactionStatus;
use App\ModulePayment\Interfaces\ModelPaymentInterface;
use App\ModulePayment\Interfaces\PaymentServiceInterface;
use App\ModulePayment\Models\PaymentCard;
use App\Services\Page;
use Auth;
use BillingService;
use Illuminate\Http\Request;
use MoneyAmount;
use PayService;
use Validator;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        Page::setTitle('Оплата услуг');
        Page::setDescription('Пополнение баланса');
        $user = Auth::user();
        $payment_cards = $user->paymentCards()->get();
        $document = null;
        if ($request->has('document')) {
            $document = Document::whereId((int) $request->input('document'))->whereClientId($user->id)->first();
        }

        return view('payments.index', [
            'payment_cards' => $payment_cards,
            'document' => $document,
        ]);
    }

    /**
     * Оплата через платежнкю систему
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        Page::setTitle('Оплата услуг');
        Page::setDescription('Пополнение баланса');

        $user = Auth::user();
        $amount = $request->input('amount');
        $paymentType = $request->input('payment_type');
        $saveCard = $request->input('save_card');
        $document = null;
        if ($request->has('document')) {
            $document = Document::whereId((int) $request->input('document'))->whereClientId($user->id)->first();
        }

        $validation = Validator::make([
            'save_card'=> false,
            'payment_type'=> $paymentType,
            'amount'=> $amount
        ], [
            'save_card'=> 'required|boolean',
            'payment_type'=> 'required|string',
            'amount'=> 'required|regex:/^\d*(\.\d+)?$/',
        ]);

        $errors = $validation->errors();
        $errors = json_decode($errors);
        if ($validation->passes()) {
            if (!array_key_exists($paymentType, PaymentServiceInterface::WHITE_LIST_TYPE)) {
                return response()->json([
                    'status'=>'error',
                    'errors' => ['payment_type'=> ['Не удалось распознать тип платежа']]
                ], 200);
            }

            try {
                $idempotency_key = PayService::uniqid();
                $description = '';

                $metadata = [];
                if ($document) {
                    $missingAmount = BillingService::calculateMissingAmount($document->client, (int) $document->amount);
                    $externalMissingAmount = MoneyAmount::toExternal($missingAmount);
                    $externalAmount = MoneyAmount::toExternal($amount);
                    // прикрепляем документ если оплата не меньше чем нужно для оплаты документа
                    if ($externalAmount >= $externalMissingAmount) {
                        $metadata['document'] = $document->id;
                    }
                }

                /** @var ModelPaymentInterface $payment */
                $payment = PayService::makePaymentTransaction( // создаем транзакции
                    $amount,
                    $paymentType,
                    $description,
                    $idempotency_key,
                    $metadata
                );


                $metadata['save_card'] = $saveCard;
                $paymentData = PayService::regularPayment(
                    $idempotency_key,
                    $amount,
                    $paymentType,
                    $description,
                    $successReturnUrl = route('payment.check').'?pay_key='.$idempotency_key,
                    '',
                    route('tinkoff.payment.notify'),
                    $metadata
                );

                PayService::updatePaymentTransaction($payment); // обновляем транзакции

                $payLink  = PayService::getPayLink();

                return response()->json([
                    'status'=>'success',
                    'pay_link' => $payLink,
                ], 200);
            }
            catch (\Exception $e) {
                return response()->json([
                    'status'=>'exception',
                    'message' => $e->getMessage()
                ], 200);
            }
        } else {
            return response()->json([
                'status'=>'error',
                'errors' => $errors
            ], 200);
        }
    }

    /**
     * Проверка платежа
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function checkPayment(Request $request)
    {
        Page::setTitle('Проверка платежа');
        Page::setDescription('Проверка платежа');

        $pay_key = $request->input('pay_key', null);
        if ($pay_key) {
            $document = null;
            $document_transaction_status = null;
            /** @var ModelPaymentInterface $payment */
            $payment = PayService::getPaymentByUniqueKey($pay_key);
            if ($payment && $payment->status === ModelPaymentInterface::STATUS_PENDING) {
                $payment = PayService::checkPayment($payment);
                if ($payment->status === ModelPaymentInterface::STATUS_SUCCEEDED) {
                    // проверяем нужно ли оплачивать документ
                    $document = BillingService::checkAndPayDocumentIfRequiredByTransaction($payment->getTransaction());
                    if ($document) {
                        /** @var Transaction $document_transaction */
                        $document_transaction = $document->getTransaction();
                        $document_transaction_status = $document_transaction ? $document_transaction->getStatusCode() : null;
                    }
                }
            }

            if ($request->ajax()){
                return response()->json([
                    'status'=>'success',
                    'payment_status' => $payment->status,
                    'document' => $document,
                    'document_transaction_status' => $document_transaction_status,
                    'client' => $document ? $document->client : null,
                ], 200);
            }
        } else {
            if ($request->ajax()){
                return response()->json([
                    'status'=>'exception',
                    'message' => 'Не предоставлен уникальный ключ'
                ], 200);
            }
        }

        return redirect()->route('finances');
    }

    /**
     * Оплата в один клик через платежнкю систему
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function payFast(Request $request)
    {
        Page::setTitle('Быстрый платеж');
        Page::setDescription('Быстрый платеж');

        $currUser = Auth::user();
        if (!$currUser) {
            abort(403);
        }

        $card_id = $request->input('card_id');
        $amount = $request->input('amount');

        $document = null;
        if ($request->has('document')) {
            $document = Document::whereId((int) $request->input('document'))->whereClientId($currUser->id)->first();
        }
        $validation = Validator::make(['amount' => $amount], [
            'amount'=> 'required|regex:/^\d*(\.\d+)?$/',
        ]);

        $errors = $validation->errors();
        $errors = json_decode($errors);

        if ($validation->passes()) {
            $paymentCard = PaymentCard::whereId($card_id)->where('user_id', $currUser->id)->first();
            $idempotency_key = PayService::uniqid();

            if (!$paymentCard) {
                return response()->json([
                    'status'=>'exception',
                    'message' => 'Карта не найдена.',
                    'pay_key' => $idempotency_key,
                ], 200);
            }

            try {
                $description = '';
                $metadata = [];
                if ($document && !$document->getTransaction()) {
                    $missingAmount = BillingService::calculateMissingAmount($document->client, (int) $document->amount);
                    $externalMissingAmount = MoneyAmount::toExternal($missingAmount);
                    $externalAmount = MoneyAmount::toExternal($amount);
                    // прикрепляем документ если оплата не меньше чем нужно для оплаты документа
                    if ($externalAmount >= $externalMissingAmount) {
                        $metadata['document'] = $document->id;
                    }
                }
                /** @var ModelPaymentInterface $payment */
                $payment = PayService::makePaymentTransaction( // создаем транзакции
                    $amount,
                    PaymentServiceInterface::PAYMENT_TYPE_CARD,
                    $description,
                    $idempotency_key,
                    $metadata
                );

                $paymentData = PayService::fastPayment(
                    $idempotency_key,
                    $amount,
                    $paymentCard->card_id,
                    $description,
                    route('tinkoff.payment.notify'),
                    $metadata
                );

                PayService::updatePaymentTransaction($payment); // обновляем транзакции

                return response()->json([
                    'status'=>'success',
                    'pay_key' => $idempotency_key,
                ], 200);

            }
            catch (\Exception $e) {
                return response()->json([
                    'status'=>'exception',
                    'message' => $e->getMessage(),
                    'pay_key' => $idempotency_key,
                ], 200);
            }
        } else {
            return response()->json([
                'status'=>'error',
                'errors' => $errors
            ], 200);
        }
    }

    public function setCardDefault(Request $request)
    {
        Page::setTitle('Установка карты по умолчанию');
        Page::setDescription('Установка карты по умолчанию');

        $currUser = Auth::user();
        if (!$currUser) {
            abort(403);
        }

        $card_id = $request->input('card_id');
        $paymentCard = PaymentCard::whereId($card_id)->where('user_id', $currUser->id)->first();
        $allUserCards = $currUser->paymentCards()->get();

        if (!$paymentCard) {
            return response()->json([
                'status'=>'exception',
                'message' => 'Карта не найдена.'
            ], 200);
        }
        try {
            foreach ($allUserCards as $userCard) {
                $userCard->card_default = $userCard->id === $paymentCard->id;
                $userCard->save();
            }

            return response()->json([
                'status'=>'success',
                'paymentCards' => $currUser->paymentCards()->get(),
            ], 200);
        }
        catch (\Exception $e) {
            return response()->json([
                'status'=>'exception',
                'message' => $e->getMessage()
            ], 200);
        }
    }

    public function removePaymentPending(Request $request)
    {
        Page::setTitle('Удаление платежа в статусе Pending');
        Page::setDescription('Удаление платежа в статусе Pending');

        $pay_key = $request->input('pay_key', null);
        if ($pay_key) {
            /** @var ModelPaymentInterface $payment */
            $payment = PayService::getPaymentByUniqueKey($pay_key);
            $transaction = $payment ? $payment->getTransaction() : null;
            if (!$payment || !$transaction || ($payment && $payment->status !== ModelPaymentInterface::STATUS_PENDING) || ($transaction && $transaction->getStatusCode() !== TransactionStatus::WAITING) ) {
                abort(403);
            }

            $transaction->delete();
            $payment->delete();

            return response()->json([
                'status'=>'success',
            ], 200);
        } else {
            abort(404);
        }
    }
}
